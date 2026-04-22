<?php
/**
 * Histórico primeros pagos por semana (tbl_segundometro_histo) — mismo criterio de métricas que Lunes de cierre.
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
                <p id="pphSubtitulo" class="text-muted mb-0 small">—</p>
            </div>
            <div class="ch-est-fp-rango" style="max-width: 28rem; width: 100%;">
                <label for="pphPeriodo" class="form-label small text-muted mb-0">
                    <i class="fa fa-calendar-alt me-1" aria-hidden="true"></i>Periodo (rango de fechas)
                </label>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <input type="text" id="pphPeriodo" readonly
                        class="form-control form-control-sm flex-grow-1 ch-est-fp-input"
                        style="min-width: 12rem; max-width: 19.5rem; cursor: pointer; user-select: none;"
                        placeholder="Cargando semanas..." autocomplete="off"
                        title="Cartera martes a domingo. Semana pasada por defecto y hasta 3 anteriores.">
                    <a href="/analitica/PrimerosPagos" class="btn btn-outline-secondary btn-sm flex-shrink-0"
                       title="Volver a Primeros pagos">
                        <i class="fa fa-arrow-left me-1"></i>Volver
                    </a>
                </div>
            </div>
        </div>

        <div id="pphAlert" class="alert alert-danger d-none" role="alert"></div>

        <div id="pphMain" class="d-none">
            <div id="pph-card-main" class="row g-3 mb-3 align-items-start">
                <div class="col-12 col-md-auto">
                    <div class="card border shadow-sm">
                        <div class="card-body py-2 px-3">
                            <div class="fw-semibold text-body">Semana seleccionada</div>
                            <p class="text-muted small mb-2"><strong>Etiqueta:</strong> <span id="pphSemanaLabel">—</span></p>
                            <hr class="my-2">
                            <p class="text-muted mb-0" style="font-size:.8rem;">
                                Primer vencimiento (mín. en rango martes–domingo):
                                <strong class="text-primary" id="pphLunesPv">—</strong>
                                &nbsp;·&nbsp;
                                Corte:
                                <code id="pphCorteLabel" class="text-info">—</code>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-lg-2">
                    <div class="card text-center h-100">
                        <div class="card-body py-2">
                            <div class="text-muted" style="font-size:.68rem;text-transform:uppercase;letter-spacing:.5px;">
                                Créditos
                            </div>
                            <div class="fw-bold text-primary" style="font-size:1.5rem;" id="pphStatTotal">—</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-lg-2">
                    <div class="card text-center h-100">
                        <div class="card-body py-2">
                            <div class="text-muted" style="font-size:.68rem;text-transform:uppercase;letter-spacing:.5px;">
                                Nacieron Current
                            </div>
                            <div class="fw-bold text-success" style="font-size:1.5rem;" id="pphStatNacCur">—</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-lg-2">
                    <div class="card text-center h-100">
                        <div class="card-body py-2">
                            <div class="text-muted" style="font-size:.68rem;text-transform:uppercase;letter-spacing:.5px;">
                                Nacieron 1–7d
                            </div>
                            <div class="fw-bold text-danger" style="font-size:1.5rem;" id="pphStatNac17">—</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-lg-2">
                    <div class="card text-center h-100">
                        <div class="card-body py-2">
                            <div class="text-muted" style="font-size:.68rem;text-transform:uppercase;letter-spacing:.5px;">
                                Pend. 1ª pago
                            </div>
                            <div class="fw-bold text-warning" style="font-size:1.5rem;" id="pphStatPendPp">—</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-12 col-md-6">
                    <div class="card h-100 mb-0">
                        <div class="card-header py-2 d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <span class="fw-semibold" style="font-size:.82rem;">
                                <i class="fa fa-egg text-primary me-1"></i>
                                Distribución de nacimiento
                            </span>
                            <span class="badge bg-label-warning"><i class="fa fa-globe me-1"></i>Global</span>
                        </div>
                        <div class="card-body py-2">
                            <div class="row row-cols-2 g-2" id="pphStatsNacimientoTop"></div>
                            <div id="pphNacimientoGlobalResumen" class="mt-3 mb-0" style="display:none;">
                                <div class="d-flex rounded-pill overflow-hidden border" style="height:0.82rem;background:rgba(0,0,0,.06);border-color:rgba(0,0,0,.1) !important;" role="group" aria-label="Distribución global Current vs 1-7 días">
                                    <div id="pphNacBarCurrent" class="d-flex align-items-center justify-content-center bg-success text-white fw-semibold flex-shrink-0 overflow-hidden"
                                         role="progressbar" style="width:0%;min-width:0;font-size:.58rem;line-height:1;padding:0 2px;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                        <span id="pphNacPctCurrent"></span>
                                    </div>
                                    <div id="pphNacBar17" class="d-flex align-items-center justify-content-center bg-danger text-white fw-semibold flex-shrink-0 overflow-hidden"
                                         role="progressbar" style="width:0%;min-width:0;font-size:.58rem;line-height:1;padding:0 2px;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                        <span id="pphNacPct17"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="row row-cols-2 g-2 mt-2" id="pphStatsNacimientoRest"></div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="card h-100 mb-0">
                        <div class="card-header py-2 d-flex flex-wrap align-items-center gap-1">
                            <span class="fw-semibold text-body d-inline-flex flex-wrap align-items-center gap-1" style="font-size:.78rem;line-height:1.35;">
                                <i class="fa fa-chart-pie text-primary flex-shrink-0 me-1"></i>
                                <span>Distribución de corte:</span>
                                <span id="pphDistribCorteFecha" class="fw-semibold text-muted">—</span>
                                <span class="text-muted">·</span>
                                <span>Corte:</span>
                                <code id="pphDistribCorteLbl" class="text-info mb-0" style="font-size:.78rem;">—</code>
                            </span>
                        </div>
                        <div class="card-body py-2">
                            <div class="row row-cols-2 g-2" id="pphStatsCorte"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-12 col-xl-7 d-flex">
                    <div class="card shadow-sm flex-fill d-flex flex-column">
                        <div class="card-header py-2">
                            <span class="fw-semibold" style="font-size:.82rem;">
                                <i class="fa fa-chart-column text-primary me-1"></i>Gráfica de nacimiento
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="position-relative w-100" style="min-height:260px;">
                                <canvas id="pphChartNacimiento"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xl-5 d-flex">
                    <div class="card shadow-sm flex-fill d-flex flex-column">
                        <div class="card-header py-2">
                            <span class="fw-semibold" style="font-size:.82rem;">
                                <i class="fa fa-chart-pie text-primary me-1"></i>Gráfica de corte
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="position-relative w-100" style="min-height:260px;">
                                <canvas id="pphChartCorte"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header py-2">
                    <span class="fw-semibold" style="font-size:.82rem;">
                        <i class="fa fa-ranking-star text-danger me-1"></i>
                        Seguimiento por jerarquía
                        <span class="text-muted fw-normal" style="font-size:.72rem;">
                            — peor seguimiento primero
                        </span>
                    </span>
                </div>
                <div class="card-body" id="pphStatsJerarquia"></div>
            </div>
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
        'e) 61+ dias':     { cls: 'bg-label-secondary', icon: 'fa-skull-crossbones',     short: '61+d'    },
    };
    var BUCKET_ORDER = Object.keys(BUCKET_META);
    var BUCKET_NAC_TOP = ['a) Current', 'b) 1 a 7 dias'];
    var pphState = { semanas: [], semanaSeleccionada: '' };
    var pphCharts = { nac: null, corte: null };

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

    function destroyCharts() {
        if (pphCharts.nac) {
            try { pphCharts.nac.destroy(); } catch (e1) {}
            pphCharts.nac = null;
        }
        if (pphCharts.corte) {
            try { pphCharts.corte.destroy(); } catch (e2) {}
            pphCharts.corte = null;
        }
    }

    function pctOf(n, totalRegs) {
        var t = parseInt(totalRegs, 10);
        var v = parseInt(n, 10);
        if (isNaN(t) || t <= 0 || isNaN(v)) return 0;
        return Math.round((v / t) * 100);
    }

    function renderNacimientoYCorte(d) {
        var totalRegs = parseInt(d.total, 10) || 0;
        var nd = (d.nacimiento && d.nacimiento.nac_dist) ? d.nacimiento.nac_dist : {};
        var corte = d.corte || {};
        var curAl = parseInt(corte.current_al_corte, 10) || 0;
        var pend = parseInt(corte.pendientes_primeros_pagos, 10) || 0;

        function cardNacHtml(b) {
            var m = BUCKET_META[b] || {};
            var cnt = parseInt(nd[b], 10) || 0;
            if (!cnt) return '';
            var p = pctOf(cnt, totalRegs);
            return (
                '<div class="col">' +
                    '<div class="card text-center h-100 border-0 shadow-sm">' +
                        '<div class="card-body py-2 px-2">' +
                            '<div class="badge ' + (m.cls || 'bg-label-secondary') + ' mb-1" style="font-size:.65rem;">' +
                                '<i class="fa ' + (m.icon || 'fa-question') + ' me-1"></i>' + (m.short || b) +
                            '</div>' +
                            '<div class="fw-bold" style="font-size:1.5rem;">' + fmtInt(cnt) +
                                '<span class="text-muted fw-semibold" style="font-size:1.05rem;margin-left:6px;">(' + p + '%)</span></div>' +
                            '<div class="text-muted" style="font-size:.65rem;">nacieron</div>' +
                        '</div>' +
                    '</div>' +
                '</div>'
            );
        }

        var htmlTop = '';
        var htmlRest = '';
        BUCKET_NAC_TOP.forEach(function (b) { htmlTop += cardNacHtml(b); });
        BUCKET_ORDER.forEach(function (b) {
            if (BUCKET_NAC_TOP.indexOf(b) >= 0) return;
            htmlRest += cardNacHtml(b);
        });
        var elTop = document.getElementById('pphStatsNacimientoTop');
        var elRest = document.getElementById('pphStatsNacimientoRest');
        if (elTop) elTop.innerHTML = htmlTop;
        if (elRest) {
            elRest.innerHTML = htmlRest;
            elRest.classList.toggle('d-none', !String(htmlRest).trim());
        }

        var bar = (d.nacimiento) ? d.nacimiento : {};
        var elWrap = document.getElementById('pphNacimientoGlobalResumen');
        var elPc = document.getElementById('pphNacPctCurrent');
        var elP17 = document.getElementById('pphNacPct17');
        var elBc = document.getElementById('pphNacBarCurrent');
        var elB17 = document.getElementById('pphNacBar17');
        if (elWrap && elPc && elP17 && elBc && elB17) {
            if (bar.mostrar_bar_global) {
                var pC = parseInt(bar.bar_current_pct, 10) || 0;
                var p7 = parseInt(bar.bar_17_pct, 10) || 0;
                elPc.textContent = pC + '%';
                elP17.textContent = p7 + '%';
                elBc.style.width = pC + '%';
                elB17.style.width = p7 + '%';
                elBc.style.visibility = pC > 0 ? '' : 'hidden';
                elB17.style.visibility = p7 > 0 ? '' : 'hidden';
                elBc.setAttribute('aria-valuenow', String(pC));
                elB17.setAttribute('aria-valuenow', String(p7));
                elWrap.style.display = '';
            } else {
                elWrap.style.display = 'none';
            }
        }

        var pCur = pctOf(curAl, totalRegs);
        var pPend = pctOf(pend, totalRegs);
        var htmlCorte = '' +
            '<div class="col">' +
                '<div class="card text-center h-100 border-0 shadow-sm">' +
                    '<div class="card-body py-2 px-2">' +
                        '<div class="badge bg-label-success mb-1" style="font-size:.65rem;">' +
                            '<i class="fa fa-circle-check me-1"></i>Current' +
                        '</div>' +
                        '<div class="fw-bold text-body" style="font-size:1.5rem;">' + fmtInt(curAl) +
                            '<span class="text-muted fw-semibold" style="font-size:1.05rem;margin-left:6px;">(' + pCur + '%)</span></div>' +
                        '<div class="text-muted" style="font-size:.65rem;">al corte</div>' +
                    '</div>' +
                '</div>' +
            '</div>' +
            '<div class="col">' +
                '<div class="card text-center h-100 border-0 shadow-sm">' +
                    '<div class="card-body py-2 px-2">' +
                        '<div class="badge bg-label-warning mb-1" style="font-size:.58rem;white-space:normal;line-height:1.25;max-width:100%;">' +
                            '<i class="fa fa-clock me-1"></i>Pendientes primeros pagos' +
                        '</div>' +
                        '<div class="fw-bold text-body" style="font-size:1.5rem;">' + fmtInt(pend) +
                            '<span class="text-muted fw-semibold" style="font-size:1.05rem;margin-left:6px;">(' + pPend + '%)</span></div>' +
                        '<div class="text-muted" style="font-size:.65rem;">por recuperar</div>' +
                    '</div>' +
                '</div>' +
            '</div>';
        var elCorte = document.getElementById('pphStatsCorte');
        if (elCorte) elCorte.innerHTML = htmlCorte;

        var fe = (document.getElementById('pphLunesPv') && document.getElementById('pphLunesPv').textContent)
            ? document.getElementById('pphLunesPv').textContent.trim() : '—';
        var co = (document.getElementById('pphCorteLabel') && document.getElementById('pphCorteLabel').textContent)
            ? document.getElementById('pphCorteLabel').textContent.trim() : '—';
        var elFe = document.getElementById('pphDistribCorteFecha');
        var elCo = document.getElementById('pphDistribCorteLbl');
        if (elFe) elFe.textContent = fe || '—';
        if (elCo) elCo.textContent = co || '—';

        var elJer = document.getElementById('pphStatsJerarquia');
        if (elJer) elJer.innerHTML = d.jerarquia_html || '<p class="text-muted">Sin datos.</p>';
        renderCharts(d, nd, curAl, pend);
    }

    function renderCharts(d, nd, curAl, pend) {
        if (typeof Chart === 'undefined') return;
        destroyCharts();

        var labelsN = BUCKET_ORDER.slice(0);
        var dataN = labelsN.map(function (b) { return parseInt(nd[b], 10) || 0; });
        var pal = ['#28a745', '#ff3e1d', '#ffab00', '#dc3545', '#8592a3'];
        pphCharts.nac = new Chart(document.getElementById('pphChartNacimiento'), {
            type: 'bar',
            data: {
                labels: labelsN,
                datasets: [{
                    label: 'Créditos',
                    data: dataN,
                    backgroundColor: pal
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
                plugins: { legend: { display: false } }
            }
        });

        pphCharts.corte = new Chart(document.getElementById('pphChartCorte'), {
            type: 'doughnut',
            data: {
                labels: ['Current al corte', 'Pendientes primeros pagos'],
                datasets: [{
                    data: [parseInt(curAl, 10) || 0, parseInt(pend, 10) || 0],
                    backgroundColor: ['#28a745', '#ffab00']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }

    function aplicarResumen(respDatos) {
        var d = respDatos || {};
        document.getElementById('pphMain').classList.remove('d-none');
        document.getElementById('pphSemanaLabel').textContent = d.semana || '—';
        document.getElementById('pphLunesPv').textContent = d.lunes_primer_vencimiento || '—';
        document.getElementById('pphCorteLabel').textContent = d.corte_label || '—';
        var st = document.getElementById('pphSubtitulo');
        if (st && d.rango_cartera_texto) {
            st.textContent = String(d.rango_cartera_texto) + ' (martes – domingo)';
            if (d.criterio_fecha === 'lunes_cierre') {
                st.textContent = String(d.rango_cartera_texto) + ' (lunes de cierre)';
            }
        }

        var total = parseInt(d.total, 10) || 0;
        var nd = (d.nacimiento && d.nacimiento.nac_dist) ? d.nacimiento.nac_dist : {};
        var corte = d.corte || {};
        document.getElementById('pphStatTotal').textContent = fmtInt(total);
        document.getElementById('pphStatNacCur').textContent = fmtInt(nd['a) Current'] || 0);
        document.getElementById('pphStatNac17').textContent = fmtInt(nd['b) 1 a 7 dias'] || 0);
        document.getElementById('pphStatPendPp').textContent = fmtInt(corte.pendientes_primeros_pagos || 0);

        renderNacimientoYCorte(d);
    }

    function fmtYmd(d) {
        var y = d.getFullYear();
        var m = String(d.getMonth() + 1).padStart(2, '0');
        var day = String(d.getDate()).padStart(2, '0');
        return y + '-' + m + '-' + day;
    }

    function parseSemanaIso(semanaTxt) {
        var s = String(semanaTxt || '');
        var m = s.match(/(?:semana\s*)?(\d{1,2})\s*[-_\/ ]\s*(\d{4})/i);
        if (!m) return null;
        var week = parseInt(m[1], 10);
        var year = parseInt(m[2], 10);
        if (!week || week < 1 || week > 53 || !year || year < 2000) return null;
        return { week: week, year: year };
    }

    /** Semana operativa de primeros pagos: **martes → domingo** (6 días tras martes). */
    function isoWeekMartesDomingo(isoYear, isoWeek) {
        var jan4 = new Date(isoYear, 0, 4);
        jan4.setHours(12, 0, 0, 0);
        var dow = jan4.getDay() || 7;
        var monday = new Date(jan4);
        monday.setDate(jan4.getDate() - dow + 1 + (isoWeek - 1) * 7);
        var tuesday = new Date(monday);
        tuesday.setDate(monday.getDate() + 1);
        var sunday = new Date(monday);
        sunday.setDate(monday.getDate() + 6);
        return { ini: fmtYmd(tuesday), fin: fmtYmd(sunday) };
    }

    function enriquecerSemanas(rows) {
        var out = [];
        (rows || []).forEach(function (r) {
            var iniSrv = (r.ini != null && String(r.ini).length >= 10) ? String(r.ini).substring(0, 10) : '';
            var finSrv = (r.fin != null && String(r.fin).length >= 10) ? String(r.fin).substring(0, 10) : '';
            if (iniSrv && finSrv) {
                out.push({
                    semana: String(r.semana || ''),
                    registros: parseInt(r.registros, 10) || 0,
                    ini: iniSrv,
                    fin: finSrv
                });
                return;
            }
            var p = parseSemanaIso(r.semana);
            if (!p) return;
            var rg = isoWeekMartesDomingo(p.year, p.week);
            out.push({
                semana: String(r.semana || ''),
                registros: parseInt(r.registros, 10) || 0,
                ini: rg.ini,
                fin: rg.fin
            });
        });
        out.sort(function (a, b) {
            if (a.ini === b.ini) return 0;
            return a.ini > b.ini ? -1 : 1;
        });
        return out.slice(0, 4);
    }

    function buscarSemanaPorRango(ini, fin) {
        for (var i = 0; i < pphState.semanas.length; i++) {
            var w = pphState.semanas[i];
            if (ini >= w.ini && fin <= w.fin) return w;
        }
        return null;
    }

    function cargarResumen() {
        var sem = String(pphState.semanaSeleccionada || '');
        if (!sem) {
            showErr('Seleccione una semana válida.');
            return;
        }
        hideErr();
        fetch('/analitica/getPrimerosPagosHistoricoResumen', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'Front-Request': 'true' },
            body: JSON.stringify({ semana: sem })
        })
            .then(function (r) { return r.text().then(function (t) { var j = null; try { j = JSON.parse(t); } catch (e) { j = null; } return { ok: r.ok, json: j }; }); })
            .then(function (w) {
                var resp = w.json;
                if (!w.ok || !resp || !resp.success || !resp.datos) {
                    var m = (resp && resp.mensaje) ? resp.mensaje : 'No se pudo cargar el resumen.';
                    showErr(m + (resp && resp.error ? ' (' + resp.error + ')' : ''));
                    return;
                }
                aplicarResumen(resp.datos);
            })
            .catch(function () {
                showErr('Error de red al consultar el resumen.');
            });
    }

    function inicializarFiltroPeriodo() {
        var inp = document.getElementById('pphPeriodo');
        if (!inp) return;
        if (!pphState.semanas.length) {
            inp.value = '';
            inp.placeholder = '(sin semanas disponibles)';
            return;
        }
        var weekDefault = pphState.semanas[0];
        pphState.semanaSeleccionada = weekDefault.semana;

        var minDate = pphState.semanas[pphState.semanas.length - 1].ini;
        var maxDate = pphState.semanas[0].fin;
        inp.value = weekDefault.ini + ' a ' + weekDefault.fin;
        var st0 = document.getElementById('pphSubtitulo');
        if (st0) {
            st0.textContent = weekDefault.ini + ' a ' + weekDefault.fin + ' (martes – domingo)';
        }

        if (typeof flatpickr === 'undefined') {
            cargarResumen();
            return;
        }

        if (inp._flatpickr) {
            try { inp._flatpickr.destroy(); } catch (e1) {}
        }

        flatpickr(inp, {
            mode: 'range',
            dateFormat: 'Y-m-d',
            clickOpens: true,
            allowInput: false,
            minDate: minDate,
            maxDate: maxDate,
            disableMobile: true,
            defaultDate: [weekDefault.ini, weekDefault.fin],
            enable: [function (date) {
                var y = fmtYmd(date);
                for (var i = 0; i < pphState.semanas.length; i++) {
                    var w = pphState.semanas[i];
                    if (y >= w.ini && y <= w.fin) return true;
                }
                return false;
            }],
            locale: {
                firstDayOfWeek: 2,
                weekdays: {
                    shorthand: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                    longhand: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado']
                },
                months: {
                    shorthand: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                    longhand: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']
                },
                rangeSeparator: ' a '
            },
            onChange: function (selectedDates, dateStr, instance) {
                if (!selectedDates || selectedDates.length !== 2) {
                    return;
                }
                var ini = fmtYmd(selectedDates[0]);
                var fin = fmtYmd(selectedDates[1]);
                var semanaMatch = buscarSemanaPorRango(ini, fin);
                if (!semanaMatch) {
                    showErr('Solo puedes elegir: semana pasada (por defecto) y las 3 semanas anteriores.');
                    var wBack = pphState.semanas.find(function (x) { return x.semana === pphState.semanaSeleccionada; }) || weekDefault;
                    instance.setDate([wBack.ini, wBack.fin], true);
                    return;
                }
                hideErr();
                pphState.semanaSeleccionada = semanaMatch.semana;
                if (ini !== semanaMatch.ini || fin !== semanaMatch.fin) {
                    instance.setDate([semanaMatch.ini, semanaMatch.fin], true);
                    return;
                }
                cargarResumen();
            }
        });

        cargarResumen();
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

    fetch('/analitica/getPrimerosPagosHistoricoSemanas', { method: 'GET', credentials: 'same-origin', headers: { 'Front-Request': 'true' } })
        .then(function (r) { return r.text().then(function (t) { var j = null; try { j = JSON.parse(t); } catch (e) { j = null; } return { ok: r.ok, json: j }; }); })
        .then(function (w) {
            var resp = w.json;
            if (!w.ok || !resp || !resp.success) {
                showErr((resp && resp.mensaje) ? resp.mensaje : 'No se pudieron listar las semanas.');
                var inp = document.getElementById('pphPeriodo');
                if (inp) inp.placeholder = '(sin datos)';
                return;
            }
            pphState.semanas = enriquecerSemanas(resp.datos || []);
            if (!pphState.semanas.length) {
                showErr('No hay semanas disponibles para el filtro.');
                var inp2 = document.getElementById('pphPeriodo');
                if (inp2) inp2.placeholder = '(sin semanas válidas)';
                return;
            }
            inicializarFiltroPeriodo();
        })
        .catch(function () {
            showErr('Error de red al listar semanas.');
        })
        .finally(function () {
            if (typeof Swal !== 'undefined' && typeof Swal.close === 'function') {
                try { Swal.close(); } catch (eSw) {}
            }
        });

});
</script>
