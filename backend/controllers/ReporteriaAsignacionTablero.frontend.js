    if (window.history && window.history.replaceState) {
        try {
            var u = new URL(window.location.href);
            if (u.search) {
                window.history.replaceState(null, '', u.pathname);
            }
        } catch (e) { /* noop */ }
    }
    function esc(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }
    function colKindForSubcol(sub) {
        var k = sub && sub.key ? sub.key : '';
        if (k === 'ext') {
            return 'asg-col-ext';
        }
        if (k === 'nom') {
            return 'asg-col-nom';
        }
        if (k === 'pue') {
            return 'asg-col-pue';
        }
        if (k === 'Bucket_Morosidad_Real') {
            return 'asg-col-bucket-sub asg-sep-week-end';
        }
        return 'asg-sep-week-end';
    }
    function chipPillClass(sem) {
        var hl = parseInt(sem.hist_level, 10) || 0;
        if (hl >= 1 && hl <= 3) {
            return 'comp-chip-pill-hist asg-chip-hist-' + hl;
        }
        if (sem.th_class === 'comp-th-act') {
            return 'comp-chip-pill-act';
        }
        return 'comp-chip-pill-fut';
    }
    function cellBase(thClass) {
        return 'asg-cell-' + String(thClass || '').replace(/^comp-th-/, '');
    }
    function armarItemsPaginacion(actual, total) {
        if (total < 1) {
            total = 1;
        }
        if (actual < 1) {
            actual = 1;
        }
        if (actual > total) {
            actual = total;
        }
        if (total <= 9) {
            var out1 = [];
            for (var i = 1; i <= total; i++) {
                out1.push({ type: 'page', num: i });
            }
            return out1;
        }
        var setArr = [1, total];
        function addNum(x) {
            if (x >= 1 && x <= total && setArr.indexOf(x) === -1) {
                setArr.push(x);
            }
        }
        if (actual < 5) {
            for (var j = 1; j <= Math.min(5, total); j++) {
                addNum(j);
            }
        } else if (actual > total - 4) {
            for (var k = Math.max(1, total - 4); k <= total; k++) {
                addNum(k);
            }
        } else {
            for (var m = actual - 2; m <= actual + 2; m++) {
                addNum(m);
            }
        }
        setArr.sort(function (a, b) {
            return a - b;
        });
        var out = [];
        var prev = 0;
        setArr.forEach(function (n) {
            if (prev > 0 && n - prev > 1) {
                out.push({ type: 'gap' });
            }
            out.push({ type: 'page', num: n });
            prev = n;
        });
        return out;
    }
    var state = {
        semanas: [],
        subcols: [],
        filas: [],
        tableroDos: cfg.dosVentanas,
        pagina: 1,
        limite: 10
    };
    function limiteTam() {
        if (state.limite === 'todas' || state.limite === 0) {
            return state.filas.length > 0 ? state.filas.length : 1;
        }
        return parseInt(state.limite, 10) || 10;
    }
    function totalPaginas() {
        var n = state.filas.length;
        var tam = limiteTam();
        if (tam < 1) {
            return 1;
        }
        return Math.max(1, Math.ceil(n / tam));
    }
    function sliceFilas() {
        var tam = limiteTam();
        var tp = totalPaginas();
        var p = Math.min(Math.max(1, state.pagina), tp);
        state.pagina = p;
        var off = (p - 1) * tam;
        return state.filas.slice(off, off + tam);
    }
    function buildColgroup() {
        var cg = document.getElementById('asg-colgroup');
        if (!cg) {
            return;
        }
        var html = '<col class="asg-col-id">';
        for (var si = 0; si < state.semanas.length; si++) {
            state.subcols.forEach(function (sub) {
                var cls = sub.key === 'Bucket_Morosidad_Real' ? 'asg-col-bucket-sub' : 'asg-col-equal';
                html += '<col class="' + esc(cls) + '">';
            });
        }
        if (!state.tableroDos) {
            html += '<col class="asg-col-cambio">';
        }
        cg.innerHTML = html;
    }
    function renderThead() {
        var th = document.getElementById('asg-thead');
        if (!th) {
            return;
        }
        var semanas = state.semanas;
        var subcols = state.subcols;
        var dos = state.tableroDos;
        var nc = subcols.length;
        var h = [];
        h.push('<tr class="asg-thead-chips">');
        h.push('<th rowspan="3" scope="col" class="text-center align-middle small asg-th-id-col comp-sep-r">ID Crédito</th>');
        semanas.forEach(function (sem, si) {
            h.push('<th colspan="' + nc + '" scope="colgroup" class="text-center asg-chip-th comp-sep-week asg-sep-week-end">');
            h.push('<span class="badge rounded-pill comp-chip-pill comp-chip-pill--asg-multiline ' + esc(chipPillClass(sem)) + '" title="Ventana martes a lunes: ' + esc(sem.range || '') + '">');
            h.push(esc(sem.chip_text || ''));
            h.push('</span>');
            if (si < semanas.length - 1) {
                h.push('<i class="fa-solid fa-chevron-right comp-chip-arrow" aria-hidden="true"></i>');
            }
            h.push('</th>');
        });
        if (!dos) {
            h.push('<th rowspan="3" scope="col" class="text-center align-middle small text-secondary fw-bold asg-th-cambio-col">Cambio proyectado</th>');
        }
        h.push('</tr><tr class="asg-thead-semana">');
        semanas.forEach(function (sem) {
            var hl = parseInt(sem.hist_level, 10) || 0;
            var histBg = (hl >= 1 && hl <= 3) ? (' asg-hist-bg-' + hl) : '';
            h.push('<th colspan="' + nc + '" class="text-center small comp-sep-week asg-sep-week-end' + esc(histBg) + ' ' + esc(sem.th_class || '') + '">');
            h.push(esc(sem.label || ''));
            h.push('</th>');
        });
        h.push('</tr><tr>');
        semanas.forEach(function (sem) {
            subcols.forEach(function (sub) {
                var thAct = sem.th_class === 'comp-th-act';
                var hl2 = parseInt(sem.hist_level, 10) || 0;
                var histBg2 = (hl2 >= 1 && hl2 <= 3) ? (' asg-hist-bg-' + hl2) : '';
                var colKind = colKindForSubcol(sub);
                var thCls = 'small comp-subcell ' + (sub.align || '') + histBg2 + ' ' + colKind + (thAct ? ' bg-label-success' : '');
                var thSt = thAct ? ' style="--bs-bg-opacity:.2"' : '';
                h.push('<th class="' + esc(thCls) + '"' + thSt + '>' + esc(sub.text || '') + '</th>');
            });
        });
        h.push('</tr>');
        th.innerHTML = h.join('');
    }
    function renderTbody() {
        var tb = document.getElementById('asg-tbody');
        if (!tb) {
            return;
        }
        var semanas = state.semanas;
        var subcols = state.subcols;
        var dos = state.tableroDos;
        var filas = sliceFilas();
        if (filas.length === 0) {
            var empty = ['<tr><td class="small text-start comp-num-empty asg-td-id-col">—</td>'];
            semanas.forEach(function (sem) {
                subcols.forEach(function (sub) {
                    var hl = parseInt(sem.hist_level, 10) || 0;
                    var histBg = (hl >= 1 && hl <= 3) ? (' asg-hist-bg-' + hl) : '';
                    var colKind = colKindForSubcol(sub);
                    var cb = cellBase(sem.th_class) + histBg + ' ' + colKind;
                    empty.push('<td class="small comp-num-empty ' + esc(sub.align || '') + ' ' + esc(cb) + '">—</td>');
                });
            });
            if (!dos) {
                empty.push('<td class="small text-center align-middle comp-num-empty asg-cambio-cell">—</td>');
            }
            empty.push('</tr>');
            tb.innerHTML = empty.join('');
            return;
        }
        var rows = [];
        filas.forEach(function (fila) {
            var meta = (fila.meta && typeof fila.meta === 'object') ? fila.meta : {};
            var hayCambioProxima = !!meta.hay_cambio_proxima;
            var motivoCambio = String(meta.motivo_cambio || '').trim();
            if (!motivoCambio) {
                motivoCambio = hayCambioProxima ? 'Cambio proyectado en próxima semana' : 'Sin cambios';
            }
            var esCambioInformativo = hayCambioProxima || motivoCambio.toLowerCase() !== 'sin cambios';
            rows.push('<tr>');
            rows.push('<td class="small text-start asg-td-id-col">' + esc(fila.id_credito != null ? fila.id_credito : '—') + '</td>');
            semanas.forEach(function (sem, si) {
                var cellSem = (fila.cells && fila.cells[si]) ? fila.cells[si] : {};
                subcols.forEach(function (sub) {
                    var hl = parseInt(sem.hist_level, 10) || 0;
                    var histBg = (hl >= 1 && hl <= 3) ? (' asg-hist-bg-' + hl) : '';
                    var colKind = colKindForSubcol(sub);
                    var cb = cellBase(sem.th_class) + histBg + ' ' + colKind;
                    var value = '—';
                    if (sub.key === 'ext') {
                        value = String(cellSem.ext || '').trim() || '—';
                    } else if (sub.key === 'nom') {
                        value = String(cellSem.nom || '').trim() || '—';
                    } else if (sub.key === 'pue') {
                        value = String(cellSem.pue || '').trim() || '—';
                    } else if (sub.key === 'Bucket_Morosidad_Real') {
                        value = String(cellSem.Bucket_Morosidad_Real != null ? cellSem.Bucket_Morosidad_Real : '').trim() || '—';
                    }
                    rows.push('<td class="small ' + esc(sub.align || '') + ' ' + esc(cb) + '">' + esc(value) + '</td>');
                });
            });
            if (!dos) {
                rows.push('<td class="small text-center align-middle text-wrap asg-cambio-cell">');
                if (esCambioInformativo) {
                    rows.push('<span class="d-inline-flex align-items-center justify-content-center gap-1 text-wrap" title="' + esc(motivoCambio) + '">');
                    rows.push('<i class="fa-solid fa-arrows-rotate text-primary flex-shrink-0" aria-hidden="true"></i>');
                    rows.push(esc(motivoCambio));
                    rows.push('</span>');
                } else {
                    rows.push('<span class="text-secondary text-wrap d-inline-flex align-items-center justify-content-center gap-1">');
                    rows.push('<i class="fa-regular fa-circle-check flex-shrink-0" aria-hidden="true"></i>');
                    rows.push(esc(motivoCambio));
                    rows.push('</span>');
                }
                rows.push('</td>');
            }
            rows.push('</tr>');
        });
        tb.innerHTML = rows.join('');
    }
    function renderPagNav() {
        var nav = document.getElementById('asg-pag-nav');
        if (!nav) {
            return;
        }
        var tp = totalPaginas();
        var p = state.pagina;
        var primera = p <= 1;
        var ultima = p >= tp;
        var items = armarItemsPaginacion(p, tp);
        var parts = [];
        function btn(label, disabled, pageNum, cls, aria) {
            var c = 'asg-pag-btn' + (cls ? ' ' + cls : '');
            if (disabled) {
                c += ' is-disabled';
            }
            if (disabled) {
                parts.push('<button type="button" class="' + c + '" disabled aria-disabled="true" tabindex="-1">' + label + '</button>');
            } else {
                parts.push('<button type="button" class="' + c + '" data-asg-page="' + pageNum + '"' + (aria ? ' aria-label="' + esc(aria) + '"' : '') + '>' + label + '</button>');
            }
        }
        btn('«', primera, 1, 'asg-pag-btn--icon', 'Primera página');
        btn('‹', primera, p - 1, 'asg-pag-btn--icon', 'Página anterior');
        items.forEach(function (it) {
            if (it.type === 'gap') {
                parts.push('<span class="asg-pag-btn asg-pag-btn--ellipsis" aria-hidden="true">…</span>');
            } else {
                var n = it.num;
                var act = n === p;
                var cl = 'asg-pag-btn asg-pag-btn--num' + (act ? ' is-active' : '');
                if (act) {
                    parts.push('<button type="button" class="' + cl + '" aria-current="page" disabled>' + n + '</button>');
                } else {
                    parts.push('<button type="button" class="' + cl + '" data-asg-page="' + n + '">' + n + '</button>');
                }
            }
        });
        btn('›', ultima, p + 1, 'asg-pag-btn--icon', 'Página siguiente');
        btn('»', ultima, tp, 'asg-pag-btn--icon', 'Última página');
        nav.innerHTML = parts.join('');
        nav.querySelectorAll('button[data-asg-page]').forEach(function (b) {
            b.addEventListener('click', function () {
                var pg = parseInt(b.getAttribute('data-asg-page'), 10);
                if (!isNaN(pg)) {
                    state.pagina = pg;
                    renderTbody();
                    renderPagNav();
                }
            });
        });
    }
    function renderAll() {
        buildColgroup();
        renderThead();
        renderTbody();
        renderPagNav();
    }
    function showError(msg) {
        var el = document.getElementById('asg-error');
        var ld = document.getElementById('asg-loading');
        if (ld) {
            ld.classList.add('d-none');
        }
        if (el) {
            el.textContent = msg;
            el.classList.remove('d-none');
        }
    }
    function showTable() {
        var ld = document.getElementById('asg-loading');
        var er = document.getElementById('asg-error');
        var sc = document.getElementById('asg-table-scroll');
        if (ld) {
            ld.classList.add('d-none');
        }
        if (er) {
            er.classList.add('d-none');
        }
        if (sc) {
            sc.classList.remove('d-none');
        }
    }
    /* Proyección: sin dos_ventanas (3 semanas). Dos ventanas: &dos_ventanas=1 */
    var urlJson = '/reporteria/getAsignacionTableroJson?mostrar=todas' + (cfg.dosVentanas ? '&dos_ventanas=1' : '');
    fetch(urlJson, { credentials: 'same-origin' })
        .then(function (r) {
            if (!r.ok) {
                throw new Error('HTTP ' + r.status);
            }
            return r.json();
        })
        .then(function (data) {
            if (data && data.detail && !data.semanas) {
                throw new Error(String(data.detail));
            }
            state.semanas = Array.isArray(data.semanas) ? data.semanas : [];
            var baseSub = Array.isArray(data.subcols) ? data.subcols : [];
            state.subcols = cfg.dosVentanas
                ? baseSub.concat([{ key: 'Bucket_Morosidad_Real', text: 'Bucket', align: 'text-start' }])
                : baseSub;
            state.filas = Array.isArray(data.filas) ? data.filas : [];
            state.pagina = 1;
            showTable();
            renderAll();
        })
        .catch(function (e) {
            showError('No se pudo cargar el portafolio. ' + (e && e.message ? e.message : ''));
        });
    var sel = document.getElementById('asg-mostrar');
    if (sel) {
        sel.addEventListener('change', function () {
            var v = sel.value;
            state.limite = v === 'todas' ? 'todas' : parseInt(v, 10);
            state.pagina = 1;
            renderTbody();
            renderPagNav();
        });
    }
