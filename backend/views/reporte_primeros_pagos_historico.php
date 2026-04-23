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
                        <span class="small text-muted text-nowrap">Tipo de gráfica</span>
                        <div class="btn-group btn-group-sm flex-wrap" role="group" aria-label="Tipo de gráfica comportamiento" id="pphTipoGraficaComportamiento">
                            <button type="button" class="btn btn-secondary active" data-chart-type="dual_stack">Apiladas</button>
                            <button type="button" class="btn btn-outline-secondary" data-chart-type="grouped">Agrupadas</button>
                            <button type="button" class="btn btn-outline-secondary" data-chart-type="line">Líneas</button>
                        </div>
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
                            Solo en histórico por semana.
                        </div>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <label for="pphSemanaJerarquia" class="visually-hidden">Semana del histórico</label>
                        <select id="pphSemanaJerarquia" class="form-select form-select-sm" style="min-width: 12rem; max-width: 16rem;" title="Semana"></select>
                        <div class="btn-group btn-group-sm flex-wrap" role="group" aria-label="Vista de gráfica" id="pphTipoGraficaJerarquia">
                            <button type="button" class="btn btn-secondary active" data-chart-type="territorial_stack">Territorial</button>
                            <button type="button" class="btn btn-outline-secondary" data-chart-type="territorial_pct">% Efectividad</button>
                        </div>
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
                                <th class="text-end text-success">Cierre semana</th>
                                <th class="text-end text-danger">Cierre 1–7d</th>
                                <th class="text-end">Recuperación 1–7d</th>
                            </tr>
                        </thead>
                        <tbody id="pphTablaComparativoBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    /** Verde Current unificado (nacimiento y cierre); rojo #E74C3C para 1–7d. */
    var PPH_COLOR_VERDE = 'rgba(113, 221, 55, 0.85)';
    var PPH_COLOR_ROJO = '#E74C3C';
    var PPH_BORDE_VERDE = '#5aad2e';
    var PPH_BORDE_ROJO = '#c0392b';
    var PPH_SERIE = {
        nacCur: {
            label: 'Nacimiento current',
            bar: { backgroundColor: PPH_COLOR_VERDE, borderColor: PPH_BORDE_VERDE, borderWidth: 1, borderDash: [] },
            line: { borderColor: '#5aad2e', backgroundColor: 'rgba(113, 221, 55, 0.22)', borderDash: [] }
        },
        cierreSem: {
            label: 'Cierre semanal',
            bar: { backgroundColor: PPH_COLOR_VERDE, borderColor: PPH_BORDE_VERDE, borderWidth: 1, borderDash: [] },
            line: { borderColor: '#5aad2e', backgroundColor: 'rgba(113, 221, 55, 0.22)', borderDash: [7, 4] }
        },
        nac17: {
            label: 'Nacimiento 1–7d',
            bar: { backgroundColor: PPH_COLOR_ROJO, borderColor: PPH_BORDE_ROJO, borderWidth: 1, borderDash: [] },
            line: { borderColor: '#c0392b', backgroundColor: 'rgba(231,76,60,.18)', borderDash: [] }
        },
        cierre17: {
            label: 'Cierre 1–7d',
            bar: { backgroundColor: PPH_COLOR_ROJO, borderColor: PPH_BORDE_ROJO, borderWidth: 1, borderDash: [] },
            line: { borderColor: PPH_COLOR_ROJO, backgroundColor: 'rgba(231,76,60,.18)', borderDash: [5, 3] }
        }
    };
    function pphBarDataset(key, data, stack) {
        var s = PPH_SERIE[key];
        var b = s.bar;
        /** Apiladas: esquinas redondeadas solo afuera; unión verde/rojo recta. Agrupadas: barra suelta redondeada. */
        var borderRadius;
        if (stack == null || stack === '') {
            borderRadius = 6;
        } else if (key === 'nacCur' || key === 'cierreSem') {
            borderRadius = { topLeft: 0, topRight: 0, bottomLeft: 6, bottomRight: 6 };
        } else {
            borderRadius = { topLeft: 6, topRight: 6, bottomLeft: 0, bottomRight: 0 };
        }
        var o = {
            label: s.label,
            data: data,
            backgroundColor: b.backgroundColor,
            borderColor: b.backgroundColor,
            borderWidth: 0,
            borderRadius: borderRadius,
            borderSkipped: false
        };
        if (stack != null && stack !== '') {
            o.stack = stack;
        }
        return o;
    }
    function pphLineDataset(key, data) {
        var s = PPH_SERIE[key];
        var L = s.line;
        return {
            label: s.label,
            data: data,
            borderColor: L.borderColor,
            backgroundColor: L.backgroundColor,
            tension: 0.25,
            fill: false,
            borderWidth: 2,
            borderDash: L.borderDash,
            pointRadius: 3,
            pointHoverRadius: 5
        };
    }
    var pphCharts = { comp: null, jer: null };
    var pphState = { semanas: [] };
    function pphSetTipoGraficaActivo(groupId, value) {
        document.querySelectorAll('#' + groupId + ' [data-chart-type]').forEach(function (btn) {
            var on = btn.getAttribute('data-chart-type') === value;
            btn.classList.toggle('active', on);
            btn.classList.toggle('btn-secondary', on);
            btn.classList.toggle('btn-outline-secondary', !on);
        });
    }
    function pphTipoGraficaComportamiento() {
        var a = document.querySelector('#pphTipoGraficaComportamiento [data-chart-type].active');
        return a ? String(a.getAttribute('data-chart-type') || '') : 'dual_stack';
    }
    function pphTipoGraficaJerarquia() {
        var a = document.querySelector('#pphTipoGraficaJerarquia [data-chart-type].active');
        var v = a ? String(a.getAttribute('data-chart-type') || '') : 'territorial_stack';
        if (v === 'gestores_top') {
            v = 'territorial_stack';
            pphSetTipoGraficaActivo('pphTipoGraficaJerarquia', 'territorial_stack');
        }
        return v;
    }

    /** Etiquetas bajo columnas: modo apilado (Nacimiento | Cierre) o agrupado (4 barras con nombre cada una). */
    var _pphPluginColumnasRegistrado = false;
    function pphRegistrarPluginEtiquetasColumnas() {
        if (_pphPluginColumnasRegistrado || typeof Chart === 'undefined') {
            return;
        }
        _pphPluginColumnasRegistrado = true;
        Chart.register({
            id: 'pphEtiquetasColumnasNacCierre',
            afterDraw: function (chart) {
                var p = chart.options.plugins && chart.options.plugins.pphEtiquetasColumnas;
                if (!p || (p.mode !== 'dual' && p.mode !== 'grouped')) {
                    return;
                }
                var semLabels = p.semanaLabels || chart.data.labels || [];
                var ctx = chart.ctx;
                var area = chart.chartArea;
                if (!area) {
                    return;
                }
                ctx.save();
                ctx.textAlign = 'center';
                ctx.textBaseline = 'top';
                var n = chart.data.labels ? chart.data.labels.length : 0;
                var i;
                if (p.mode === 'dual') {
                    for (i = 0; i < n; i++) {
                        var m0 = chart.getDatasetMeta(0);
                        var m1 = chart.getDatasetMeta(1);
                        if (!m0.data[i] || !m1.data[i]) {
                            continue;
                        }
                        var xN = m0.data[i].x;
                        var xC = m1.data[i].x;
                        var sem = typeof semLabels[i] === 'string' ? semLabels[i] : String(semLabels[i] || chart.data.labels[i] || '');
                        ctx.font = '600 11px system-ui,Segoe UI,Roboto,Helvetica Neue,sans-serif';
                        ctx.fillStyle = '#566a7f';
                        ctx.fillText('Nacimiento', xN, area.bottom + 4);
                        ctx.fillText('Cierre', xC, area.bottom + 4);
                        ctx.font = '500 10px system-ui,Segoe UI,Roboto,Helvetica Neue,sans-serif';
                        ctx.fillStyle = '#a1acb8';
                        ctx.fillText(sem, (xN + xC) / 2, area.bottom + 20);
                    }
                } else if (p.mode === 'grouped') {
                    /** Orden de datasets: nacimiento current, cierre semana, nacimiento 1–7d, cierre 1–7d */
                    var lineas = [
                        ['Nacimiento', 'Current'],
                        ['Cierre', 'semanal'],
                        ['Nacimiento', '1–7d'],
                        ['Cierre', '1–7d']
                    ];
                    for (i = 0; i < n; i++) {
                        var xs = [];
                        var d;
                        for (d = 0; d < 4; d++) {
                            var md = chart.getDatasetMeta(d);
                            if (!md.data[i]) {
                                xs = [];
                                break;
                            }
                            xs.push(md.data[i].x);
                        }
                        if (xs.length !== 4) {
                            continue;
                        }
                        for (d = 0; d < 4; d++) {
                            ctx.font = '600 9px system-ui,Segoe UI,Roboto,Helvetica Neue,sans-serif';
                            ctx.fillStyle = '#566a7f';
                            ctx.fillText(lineas[d][0], xs[d], area.bottom + 4);
                            ctx.font = '500 9px system-ui,Segoe UI,Roboto,Helvetica Neue,sans-serif';
                            ctx.fillStyle = '#697a8d';
                            ctx.fillText(lineas[d][1], xs[d], area.bottom + 15);
                        }
                        var semG = typeof semLabels[i] === 'string' ? semLabels[i] : String(semLabels[i] || chart.data.labels[i] || '');
                        ctx.font = '500 10px system-ui,Segoe UI,Roboto,Helvetica Neue,sans-serif';
                        ctx.fillStyle = '#a1acb8';
                        ctx.fillText(semG, (xs[0] + xs[3]) / 2, area.bottom + 28);
                    }
                }
                ctx.restore();
            }
        });
    }

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
    /** Texto tooltip: agrupadas = solo la barra; apiladas = las dos series del mismo stack (Nacimiento | Cierre). */
    function pphTooltipAfterBody(tooltipItems) {
        if (!tooltipItems || !tooltipItems.length) {
            return '';
        }
        var chart = tooltipItems[0].chart;
        var modo = chart.options.plugins && chart.options.plugins.pphTooltipMode;
        var it0 = tooltipItems[0];
        var idx = it0.dataIndex;
        var yRaw = it0.parsed && it0.parsed.y !== undefined ? it0.parsed.y : it0.raw;
        if (modo === 'grouped') {
            var di = it0.datasetIndex;
            var titles = ['Nacimiento current', 'Cierre semanal', 'Nacimiento 1–7d', 'Cierre 1–7d'];
            var tit = titles[di] != null ? titles[di] : (it0.dataset.label || '');
            return tit + ': ' + fmtInt(yRaw);
        }
        if (modo === 'dual_stack') {
            var leader = it0.datasetIndex;
            var dss = chart.data.datasets;
            var stack = dss[leader].stack;
            var lines = [];
            for (var d = 0; d < dss.length; d++) {
                if (dss[d].stack !== stack) {
                    continue;
                }
                var v = dss[d].data[idx];
                var lab = dss[d].label || '';
                lines.push(lab + ': ' + fmtInt(v));
            }
            return lines.join('\n');
        }
        if (modo === 'line') {
            var lb = it0.dataset.label || '';
            return lb + ': ' + fmtInt(yRaw);
        }
        return '';
    }
    function pphMergeTooltipComportamiento(opts, modo) {
        opts.plugins = opts.plugins || {};
        opts.plugins.pphTooltipMode = modo;
        opts.interaction = { mode: 'nearest', intersect: modo === 'line' ? false : true };
        opts.plugins.tooltip = Object.assign({}, opts.plugins.tooltip, {
            displayColors: false,
            callbacks: {
                title: function () {
                    return '\u200b';
                },
                label: function () {
                    return null;
                },
                footer: function (items) {
                    return pphTooltipAfterBody(items);
                }
            }
        });
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

    function renderChartComportamiento(semanas) {
        if (typeof Chart === 'undefined') return;
        var canvas = document.getElementById('pphChartComportamiento');
        if (!canvas) return;
        destroyChart('comp');
        var tipo = pphTipoGraficaComportamiento();
        if (tipo === 'full_stack' || tipo === 'nacimiento_5') {
            tipo = 'dual_stack';
            pphSetTipoGraficaActivo('pphTipoGraficaComportamiento', 'dual_stack');
        }
        var visibles = semanasVisibles(semanas);
        var ds = datosSeriesSemanas(visibles);
        if (!ds.labels.length) return;

        var datasets;
        var opts = {
            responsive: true,
            maintainAspectRatio: false,
            elements: { bar: { borderWidth: 0 } },
            plugins: {
                legend: { display: false },
                tooltip: {}
            },
            interaction: {}
        };

        if (tipo === 'line') {
            datasets = [
                pphLineDataset('nacCur', ds.nacCur),
                pphLineDataset('cierreSem', ds.curCorte),
                pphLineDataset('nac17', ds.nac17),
                pphLineDataset('cierre17', ds.pend)
            ];
            opts.scales = { y: { beginAtZero: true, ticks: { precision: 0 } } };
            pphMergeTooltipComportamiento(opts, 'line');
            pphCharts.comp = new Chart(canvas, { type: 'line', data: { labels: ds.labels, datasets: datasets }, options: opts });
            return;
        }

        if (tipo === 'grouped') {
            datasets = [
                pphBarDataset('nacCur', ds.nacCur, ''),
                pphBarDataset('cierreSem', ds.curCorte, ''),
                pphBarDataset('nac17', ds.nac17, ''),
                pphBarDataset('cierre17', ds.pend, '')
            ];
            pphRegistrarPluginEtiquetasColumnas();
            opts.layout = { padding: { bottom: 52 } };
            opts.scales = {
                x: {
                    stacked: false,
                    ticks: { display: false },
                    grid: { display: false, drawBorder: false }
                },
                y: { stacked: false, beginAtZero: true, ticks: { precision: 0 } }
            };
            opts.plugins.pphEtiquetasColumnas = { mode: 'grouped', semanaLabels: ds.labels };
            pphMergeTooltipComportamiento(opts, 'grouped');
            pphCharts.comp = new Chart(canvas, { type: 'bar', data: { labels: ds.labels, datasets: datasets }, options: opts });
            return;
        }

        datasets = [
            pphBarDataset('nacCur', ds.nacCur, 'nacimiento'),
            pphBarDataset('cierreSem', ds.curCorte, 'corte'),
            pphBarDataset('nac17', ds.nac17, 'nacimiento'),
            pphBarDataset('cierre17', ds.pend, 'corte')
        ];
        pphRegistrarPluginEtiquetasColumnas();
        opts.layout = { padding: { bottom: 42 } };
        opts.scales = {
            x: {
                stacked: true,
                ticks: { display: false },
                grid: { display: false, drawBorder: false }
            },
            y: { stacked: true, beginAtZero: true, ticks: { precision: 0 } }
        };
        opts.plugins.pphEtiquetasColumnas = { mode: 'dual', semanaLabels: ds.labels };
        pphMergeTooltipComportamiento(opts, 'dual_stack');
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

    /**
     * Territorial: se excluyen filas «Current» sin cartera para no distorsionar;
     * si todas caen ahí pero hay créditos, se agrupan en un solo bucket para que la gráfica no quede vacía.
     */
    function agregarTerritorialParaGrafica(rows) {
        var list = agregarPorCampo(rows, 'Territorial');
        if (list.length > 0 || !rows || !rows.length) {
            return list;
        }
        var total = 0;
        var cob = 0;
        rows.forEach(function (r) {
            total += parseInt(r.total, 10) || 0;
            cob += parseInt(r.cobrados, 10) || 0;
        });
        if (total < 1) {
            return list;
        }
        return [{
            name: '(Current / sin territorial operativo)',
            total: total,
            cobrados: cob,
            pendientes: Math.max(0, total - cob),
            ratio: total > 0 ? cob / total : 0,
            pct: total > 0 ? Math.round(cob / total * 100) : 0
        }];
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
        var tipo = pphTipoGraficaJerarquia();
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

        list = agregarTerritorialParaGrafica(rows);
        if (tipo === 'territorial_pct') {
            if (!list.length) {
                pphCharts.jer = new Chart(canvas, {
                    type: 'bar',
                    data: { labels: ['—'], datasets: [{ label: 'Sin datos', data: [0], backgroundColor: '#dee2e6' }] },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
                });
                return;
            }
            labels = list.map(function (x) { return truncLabel(x.name, 20); });
            opts.indexAxis = 'y';
            opts.elements = { bar: { borderWidth: 0 } };
            opts.scales = {
                x: {
                    max: 100,
                    suggestedMax: 100,
                    beginAtZero: true,
                    ticks: { callback: function (v) { return v + '%'; } }
                },
                y: { offset: true, grid: { display: false } }
            };
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
                        backgroundColor: bg,
                        borderRadius: 6,
                        minBarLength: 6
                    }]
                },
                options: opts
            });
            return;
        }

        if (!list.length) {
            pphCharts.jer = new Chart(canvas, {
                type: 'bar',
                data: { labels: ['—'], datasets: [{ label: 'Sin datos', data: [0], backgroundColor: '#dee2e6' }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
            });
            return;
        }
        labels = list.map(function (x) { return truncLabel(x.name, 20); });
        opts.indexAxis = 'y';
        opts.elements = { bar: { borderWidth: 0 } };
        opts.scales = {
            x: { stacked: true, beginAtZero: true, ticks: { precision: 0 } },
            y: { stacked: true, offset: true, grid: { display: false } }
        };
        pphCharts.jer = new Chart(canvas, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Cobrados',
                        data: list.map(function (x) { return x.cobrados; }),
                        backgroundColor: '#28a745',
                        stack: 't',
                        borderRadius: { topLeft: 0, topRight: 0, bottomLeft: 6, bottomRight: 6 }
                    },
                    {
                        label: 'Pendientes',
                        data: list.map(function (x) { return x.pendientes; }),
                        backgroundColor: '#ffab00',
                        stack: 't',
                        borderRadius: { topLeft: 6, topRight: 6, bottomLeft: 0, bottomRight: 0 }
                    }
                ]
            },
            options: opts
        });
    }

    function pphParseFetchJson(r) {
        return r.text().then(function (t) {
            var j = null;
            try { j = JSON.parse(t); } catch (e) { j = null; }
            return { ok: r.ok, json: j };
        });
    }

    function fusionarJerarquiasEnSemanas(semanas, mapa) {
        if (!mapa || typeof mapa !== 'object' || !Array.isArray(semanas)) return;
        semanas.forEach(function (s) {
            if (!s.disponible || !s.semana) return;
            if (Object.prototype.hasOwnProperty.call(mapa, s.semana) && Array.isArray(mapa[s.semana])) {
                s.jerarquia_agregada = mapa[s.semana];
            }
        });
    }

    function enlazarControlesGraficas() {
        var sc = document.getElementById('pphTipoGraficaComportamiento');
        if (sc && !sc._pphBound) {
            sc._pphBound = true;
            sc.addEventListener('click', function (ev) {
                var btn = ev.target && ev.target.closest ? ev.target.closest('[data-chart-type]') : null;
                if (!btn) return;
                pphSetTipoGraficaActivo('pphTipoGraficaComportamiento', String(btn.getAttribute('data-chart-type') || 'dual_stack'));
                renderChartComportamiento(pphState.semanas);
            });
        }
        var sj = document.getElementById('pphSemanaJerarquia');
        if (sj && !sj._pphBound) {
            sj._pphBound = true;
            sj.addEventListener('change', function () { renderChartJerarquia(); });
        }
        var tj = document.getElementById('pphTipoGraficaJerarquia');
        if (tj && !tj._pphBound) {
            tj._pphBound = true;
            tj.addEventListener('click', function (ev) {
                var btn = ev.target && ev.target.closest ? ev.target.closest('[data-chart-type]') : null;
                if (!btn) return;
                pphSetTipoGraficaActivo('pphTipoGraficaJerarquia', String(btn.getAttribute('data-chart-type') || 'territorial_stack'));
                renderChartJerarquia();
            });
        }
    }

    function marcarJerarquiaCargando() {
        var sel = document.getElementById('pphSemanaJerarquia');
        if (!sel) return;
        sel.disabled = true;
        sel.innerHTML = '<option value="">Cargando jerarquías…</option>';
    }

    function cargarJerarquiasEnSegundoPlano(semanasBase) {
        var semanas = Array.isArray(semanasBase) ? semanasBase : [];
        var etiquetas = semanas
            .filter(function (s) { return s && s.disponible && s.semana; })
            .map(function (s) { return s.semana; });
        if (!etiquetas.length) {
            poblarSelectJerarquia(semanas);
            renderChartJerarquia();
            return;
        }
        fetch('/analitica/getPrimerosPagosHistoricoJerarquias', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'Front-Request': 'true' },
            body: JSON.stringify({ semanas: etiquetas })
        })
            .then(pphParseFetchJson)
            .then(function (wrap) {
                if (!wrap.ok || !wrap.json || !wrap.json.success || !wrap.json.datos) {
                    poblarSelectJerarquia(pphState.semanas);
                    renderChartJerarquia();
                    return;
                }
                fusionarJerarquiasEnSemanas(pphState.semanas, wrap.json.datos);
                poblarSelectJerarquia(pphState.semanas);
                renderChartJerarquia();
            })
            .catch(function () {
                poblarSelectJerarquia(pphState.semanas);
                renderChartJerarquia();
            });
    }

    function cargarComparativo() {
        fetch('/analitica/getPrimerosPagosHistoricoComparativo', {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'Front-Request': 'true' }
        })
            .then(pphParseFetchJson)
            .then(function (wc) {
                var resp = wc.json;
                if (!wc.ok || !resp || !resp.success || !resp.datos) {
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
                enlazarControlesGraficas();
                renderChartComportamiento(semanas);
                marcarJerarquiaCargando();
                cargarJerarquiasEnSegundoPlano(semanas);
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
