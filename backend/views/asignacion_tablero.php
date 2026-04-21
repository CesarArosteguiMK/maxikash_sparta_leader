<?php
/**
 * Tablero Asignación (Proyección y dos ventanas): misma vista; al cargar siempre GET
 * /reporteria/getAsignacionTableroJson?mostrar=todas (+ &dos_ventanas=1 solo en dos ventanas).
 * Paginación en el navegador; URL canónica sin ?pagina / ?mostrar.
 */
$asgTableroDos = !empty($asg_tablero_dos);
$asgBasePath = $asgTableroDos ? '/reporteria/asignacionTableroDos' : '/reporteria/asignacionTablero';
$asgExcelPath = $asgTableroDos ? '/reporteria/descargarAsignacionTableroDosExcel' : '/reporteria/descargarAsignacionTableroExcel';
?>
<div class="comp-av container-fluid py-3 px-2 px-md-3">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3 comp-av-page-header">
        <h4 class="mb-0 text-primary comp-av-heading d-flex align-items-center flex-wrap">
            <i class="fa-solid fa-user-check me-2" aria-hidden="true"></i>
            <span><?= $asgTableroDos ? 'Asignación — Tablero dos ventanas' : 'Asignación — Tablero Proyección'; ?></span>
        </h4>
        <div class="d-flex flex-wrap align-items-center gap-2 flex-shrink-0">
            <a href="/reporteria/asignacion" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-arrow-left me-1"></i>Volver
            </a>
        </div>
    </div>

    <div class="card shadow-sm border comp-av-card overflow-hidden">
        <div class="comp-av-export-root bg-body">
            <div class="card-body border-bottom py-3 comp-av-toolbar">
                <div class="d-flex flex-wrap align-items-end justify-content-between gap-3">
                    <div class="comp-av-logo-toolbar">
                        <img src="/assets/img/Logotipo-Maxikash-Outline.webp" alt="Maxikash" class="comp-av-logo" width="260" height="65" loading="lazy" decoding="async">
                    </div>
                </div>
            </div>

            <div class="comp-av-table-stack">
                <div id="asg-table-area">
                    <div id="asg-loading" class="text-center py-5 px-3">
                        <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando…</span></div>
                        <p class="small text-muted mt-2 mb-0">Cargando portafolio…</p>
                    </div>
                    <div id="asg-error" class="alert alert-danger mx-3 my-2 d-none" role="alert"></div>
                    <div class="table-responsive d-none" id="asg-table-scroll">
                        <table id="asg-table" class="table table-sm table-bordered mb-0 comp-av-table comp-av-table--asg" style="font-size:0.72rem;">
                            <colgroup id="asg-colgroup"></colgroup>
                            <thead class="comp-av-thead" id="asg-thead"></thead>
                            <tbody id="asg-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body border-top py-2 py-md-3 bg-body asg-footer-actions">
            <div class="d-flex flex-column align-items-start gap-2 w-100">
                <a id="asg-btn-descargar-excel" href="<?= htmlspecialchars($asgExcelPath, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-success btn-sm" title="Descarga el portafolio completo en Excel (puede tardar unos segundos).">
                    <i class="fa-solid fa-file-excel me-1" aria-hidden="true"></i>Descargar Excel (.xlsx)
                </a>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-0 asg-form-mostrar">
                    <label for="asg-mostrar" class="form-label small text-secondary mb-0">Mostrar</label>
                    <select class="form-select form-select-sm asg-select-mostrar" id="asg-mostrar" aria-label="Cantidad de filas a mostrar por página">
                        <option value="10" selected>10</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="todas">Todas</option>
                    </select>
                </div>
                <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-end gap-3 w-100 pt-1 asg-footer-pag-wrap">
                    <nav class="asg-pag-nav" id="asg-pag-nav" aria-label="Paginación del tablero de asignación"></nav>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
(function () {
    var cfg = {
        basePath: <?= json_encode($asgBasePath, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
        dosVentanas: <?= json_encode(!empty($asg_tablero_dos)); ?>
    };
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
        var n = state.semanas.length * state.subcols.length;
        for (var i = 0; i < n; i++) {
            html += '<col class="asg-col-equal">';
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
        var h = [];
        h.push('<tr class="asg-thead-chips">');
        h.push('<th rowspan="3" scope="col" class="text-center align-middle small asg-th-id-col comp-sep-r">ID Crédito</th>');
        semanas.forEach(function (sem, si) {
            h.push('<th colspan="3" scope="colgroup" class="text-center asg-chip-th comp-sep-week asg-sep-week-end">');
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
            h.push('<th colspan="3" class="text-center small comp-sep-week asg-sep-week-end' + esc(histBg) + ' ' + esc(sem.th_class || '') + '">');
            h.push(esc(sem.label || ''));
            h.push('</th>');
        });
        h.push('</tr><tr>');
        semanas.forEach(function (sem) {
            subcols.forEach(function (sub) {
                var thAct = sem.th_class === 'comp-th-act';
                var hl2 = parseInt(sem.hist_level, 10) || 0;
                var histBg2 = (hl2 >= 1 && hl2 <= 3) ? (' asg-hist-bg-' + hl2) : '';
                var colKind = sub.key === 'ext' ? 'asg-col-ext' : (sub.key === 'nom' ? 'asg-col-nom' : 'asg-sep-week-end');
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
                    var colKind = sub.key === 'ext' ? 'asg-col-ext' : (sub.key === 'nom' ? 'asg-col-nom' : 'asg-sep-week-end');
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
                    var colKind = sub.key === 'ext' ? 'asg-col-ext' : (sub.key === 'nom' ? 'asg-col-nom' : 'asg-sep-week-end');
                    var cb = cellBase(sem.th_class) + histBg + ' ' + colKind;
                    var value = '—';
                    if (sub.key === 'ext') {
                        value = String(cellSem.ext || '').trim() || '—';
                    } else if (sub.key === 'nom') {
                        value = String(cellSem.nom || '').trim() || '—';
                    } else if (sub.key === 'pue') {
                        value = String(cellSem.pue || '').trim() || '—';
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
            state.subcols = Array.isArray(data.subcols) ? data.subcols : [];
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
})();
</script>


<style>
/* Reutiliza tokens visuales del tablero Comparativas (comp-av-*) + Asignación 3×3 (sin columna Corte) */
.comp-av-page-header { min-width: 0; }
.comp-av-toolbar-dia { min-width: 0; }
.asg-footer-actions .asg-select-mostrar {
    min-width: 7.25rem;
    max-width: 11rem;
}
.comp-av-logo-toolbar {
    display: flex;
    align-items: flex-end;
    padding-bottom: 0.1rem;
}
.comp-av-toolbar .comp-av-logo {
    height: 3.35rem;
    width: auto;
    max-width: min(320px, 62vw);
    object-fit: contain;
    display: block;
}
.comp-av-heading { line-height: 1.25; letter-spacing: -0.02em; }
.comp-av-export-root { overflow: hidden; }
.comp-av .comp-chip-pill-fut {
    border-color: var(--bs-info) !important;
    box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--bs-info) 35%, var(--bs-white));
    color: var(--bs-info) !important;
}
.comp-av .comp-chip-pill-fut strong { color: var(--bs-info-text-emphasis); }

.comp-av-table {
    --bs-table-border-color: var(--bs-gray-200);
    --comp-sep-color: var(--bs-gray-300);
    border-collapse: collapse;
    border: 1px solid var(--bs-gray-400) !important;
}
.comp-av-table.comp-av-table--asg {
    table-layout: fixed;
    width: 100%;
}
.comp-av-table--asg > colgroup > col.asg-col-id {
    width: 7%;
}
.comp-av-table--asg > colgroup > col.asg-col-equal {
    width: 8.555%;
}
.comp-av-table--asg > colgroup > col.asg-col-cambio {
    width: 18%;
}
.comp-av-table--asg > thead.comp-av-thead > tr > th.asg-th-id-col,
.comp-av-table--asg > tbody > tr > td.asg-td-id-col {
    position: sticky;
    left: 0;
    z-index: 5;
    background-color: var(--bs-body-bg);
    box-shadow: 1px 0 0 var(--bs-table-border-color);
    background-clip: padding-box;
}
.comp-av-table--asg > tbody > tr > td.asg-td-id-col {
    z-index: 2;
}
.comp-av-table--asg > thead.comp-av-thead > tr > th.asg-th-id-col {
    font-weight: 700;
    font-size: 0.62rem;
    letter-spacing: 0.02em;
    color: var(--bs-primary);
    border-right: 1px solid var(--bs-gray-300) !important;
}
.comp-av-table--asg > thead.comp-av-thead > tr > th.asg-th-cambio-col {
    min-width: 11rem;
}
.comp-av-table--asg .asg-cambio-cell {
    min-width: 11rem;
    max-width: 17rem;
}
/* Paginación estilo “pastillas” (como referencia de diseño) */
.asg-footer-pag-wrap {
    align-items: center;
}
.asg-pag-nav {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.35rem;
}
.asg-pag-btn {
    cursor: pointer;
    font-family: inherit;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 2.35rem;
    height: 2.35rem;
    padding: 0 0.4rem;
    border-radius: 0.45rem;
    border: 1px solid #d8dee6;
    background: #fff;
    color: #4b5563;
    font-size: 0.8125rem;
    font-weight: 600;
    text-decoration: none;
    line-height: 1;
    transition: background-color 0.15s ease, color 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
}
.asg-pag-btn--icon {
    font-size: 0.95rem;
    font-weight: 700;
    color: #374151;
}
.asg-pag-btn--num {
    min-width: 2.35rem;
}
.asg-pag-btn--ellipsis {
    border-style: dashed;
    color: #9ca3af;
    font-weight: 700;
    cursor: default;
    pointer-events: none;
    background: #fafbfc;
}
.asg-pag-btn:hover:not(.is-disabled):not(.is-active):not(.asg-pag-btn--ellipsis) {
    background: #f3f4f6;
    border-color: #c5ccd6;
    color: #111827;
}
.asg-pag-btn.is-active {
    background: linear-gradient(180deg, #1e4a7a 0%, #153a63 100%);
    color: #fff;
    border-color: #0f3258;
    box-shadow: 0 3px 8px rgba(21, 58, 99, 0.35);
    cursor: default;
    pointer-events: none;
}
.asg-pag-btn.is-disabled {
    opacity: 0.42;
    pointer-events: none;
    cursor: not-allowed;
}
.comp-av-table.table-bordered > :not(caption) > * > * {
    border-style: solid !important;
    border-width: 1px !important;
    border-color: var(--bs-table-border-color) !important;
}
.comp-av-table > thead.comp-av-thead > tr > th,
.comp-av-table > tbody > tr > td {
    border-style: solid !important;
    border-width: 1px !important;
    border-color: var(--bs-table-border-color) !important;
}
.comp-av-table > :not(caption) > * > * {
    padding: 0.35rem 0.45rem !important;
    vertical-align: middle;
    line-height: 1.25;
}
.comp-av-table > tbody > tr > td {
    min-height: 2.1rem;
}
.comp-av-table > thead.comp-av-thead > tr:first-child > th {
    border-bottom-width: 1px !important;
}
.comp-av-table-stack {
    border: 1px solid var(--bs-gray-400);
    border-radius: 0.65rem;
    overflow: hidden;
    margin: 0.5rem 1rem 1rem;
}
.comp-av-table-stack #asg-table-area .table-responsive {
    border: 0 !important;
    border-radius: 0 !important;
    overflow-x: auto;
}
.comp-av-table--asg thead tr.asg-thead-chips > th.asg-chip-th {
    background-color: color-mix(in srgb, var(--bs-secondary-bg) 70%, var(--bs-white));
    font-weight: 400;
    vertical-align: middle;
    padding: 0.42rem 0.35rem !important;
    position: relative;
}
.comp-av-table--asg thead tr.asg-thead-chips > th.asg-chip-th .comp-chip-arrow {
    position: absolute;
    right: -0.08rem;
    top: 50%;
    transform: translateY(-50%);
    z-index: 2;
    font-size: 0.65rem;
    color: var(--bs-primary);
    opacity: 0.55;
    line-height: 1;
    pointer-events: none;
}
.comp-av-table--asg thead tr.asg-thead-chips .comp-chip-pill.comp-chip-pill--asg-multiline {
    white-space: normal;
    max-width: 100%;
    overflow: visible;
    text-overflow: clip;
    line-height: 1.28;
    padding: 0.38rem 0.42rem;
    border-radius: 0.65rem;
    text-align: center;
    display: inline-block;
}
.comp-av-table-stack .comp-av-table > thead.comp-av-thead > tr:first-child > th {
    border-top-width: 0 !important;
}
.comp-av .comp-chip-pill {
    padding: 0.35rem 0.55rem;
    font-size: 0.72rem;
    font-weight: 500;
    border: 1px solid var(--bs-primary-border-subtle);
    color: var(--bs-primary);
    background-color: var(--bs-body-bg);
}
.comp-av .comp-chip-pill strong { font-weight: 700; }
.comp-av .comp-chip-pill-hist strong { color: var(--bs-primary); }
.comp-av .comp-chip-pill-act {
    border-color: var(--bs-success) !important;
    box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--bs-success) 35%, var(--bs-white));
}
.comp-av .comp-chip-pill-act strong { color: var(--bs-success); }
.comp-av-table > thead.comp-av-thead > tr > th {
    font-size: 0.62rem;
    font-weight: 700;
    letter-spacing: 0.02em;
    text-transform: none;
    white-space: normal;
    border-style: solid !important;
    border-width: 1px !important;
    border-color: var(--bs-gray-200) !important;
}
.comp-av-table--asg > thead.comp-av-thead > tr:nth-child(3) > th {
    text-transform: none;
    font-size: 0.6rem;
    border-top-color: var(--bs-gray-200) !important;
}
.comp-av-table--asg > thead.comp-av-thead > tr.asg-thead-semana > th {
    font-size: 0.62rem;
    font-weight: 700;
}
.comp-av-table tbody td {
    background-clip: padding-box;
}
.comp-av-table > thead.comp-av-thead > tr:first-child > th {
    border-bottom-color: var(--bs-gray-200) !important;
}
/* Bordes: dentro de la semana líneas suaves; cierre de semana mismo grosor/color que el resto de la tabla */
.comp-av-table--asg > thead.comp-av-thead .comp-subcell.asg-col-ext {
    border-left: 1px solid color-mix(in srgb, var(--bs-gray-400) 28%, var(--bs-white)) !important;
    border-right: 1px solid color-mix(in srgb, var(--bs-gray-400) 22%, var(--bs-white)) !important;
}
.comp-av-table--asg > thead.comp-av-thead .comp-subcell.asg-col-nom {
    border-left: 1px solid color-mix(in srgb, var(--bs-gray-400) 22%, var(--bs-white)) !important;
    border-right: 1px solid color-mix(in srgb, var(--bs-gray-400) 18%, var(--bs-white)) !important;
}
.comp-av-table--asg > thead.comp-av-thead .comp-subcell.asg-sep-week-end {
    border-left: 1px solid color-mix(in srgb, var(--bs-gray-400) 18%, var(--bs-white)) !important;
    border-right: 1px solid var(--bs-table-border-color) !important;
}
.comp-av-table--asg > tbody > tr > td.asg-col-ext {
    border-right: 1px solid color-mix(in srgb, var(--bs-gray-400) 25%, var(--bs-white)) !important;
}
.comp-av-table--asg > tbody > tr > td.asg-col-nom {
    border-right: 1px solid color-mix(in srgb, var(--bs-gray-400) 18%, var(--bs-white)) !important;
}
.comp-av-table--asg > tbody > tr > td.asg-sep-week-end {
    border-right: 1px solid var(--bs-table-border-color) !important;
}
/* Chips y fila «Semana»: sin borde vertical entre bloques (no el borde exterior de la última semana) */
.comp-av-table--asg > thead.comp-av-thead > tr.asg-thead-chips > th.asg-sep-week-end:not(:last-of-type),
.comp-av-table--asg > thead.comp-av-thead > tr.asg-thead-semana > th.asg-sep-week-end:not(:last-of-type) {
    border-right-width: 0 !important;
    border-right-style: none !important;
}
.comp-av-table--asg > thead.comp-av-thead > tr.asg-thead-chips > th.comp-sep-week + th.comp-sep-week,
.comp-av-table--asg > thead.comp-av-thead > tr.asg-thead-semana > th.comp-sep-week + th.comp-sep-week {
    border-left-width: 0 !important;
    border-left-style: none !important;
}
/* Semanas pasadas: solo el fondo sube contraste con la antigüedad; el texto sigue color normal */
.comp-av-table--asg thead .asg-hist-bg-1,
.comp-av-table--asg tbody td.asg-hist-bg-1 {
    background-color: color-mix(in srgb, var(--bs-gray-300) 38%, var(--bs-white)) !important;
    color: var(--bs-emphasis-color) !important;
}
.comp-av-table--asg thead .asg-hist-bg-2,
.comp-av-table--asg tbody td.asg-hist-bg-2 {
    background-color: color-mix(in srgb, var(--bs-gray-400) 34%, var(--bs-white)) !important;
    color: var(--bs-emphasis-color) !important;
}
.comp-av-table--asg thead .asg-hist-bg-3,
.comp-av-table--asg tbody td.asg-hist-bg-3 {
    background-color: color-mix(in srgb, var(--bs-gray-500) 28%, var(--bs-gray-100)) !important;
    color: var(--bs-emphasis-color) !important;
}
.comp-av .asg-chip-hist-1 { background-color: color-mix(in srgb, var(--bs-gray-300) 40%, var(--bs-body-bg)) !important; color: var(--bs-emphasis-color) !important; }
.comp-av .asg-chip-hist-2 { background-color: color-mix(in srgb, var(--bs-gray-400) 32%, var(--bs-body-bg)) !important; color: var(--bs-emphasis-color) !important; }
.comp-av .asg-chip-hist-3 { background-color: color-mix(in srgb, var(--bs-gray-500) 26%, var(--bs-body-bg)) !important; color: var(--bs-emphasis-color) !important; }

.comp-av .comp-th-act {
    background-color: color-mix(in srgb, var(--bs-success) 16%, var(--bs-white)) !important;
    color: var(--bs-success-text-emphasis) !important;
}
.comp-av .comp-th-fut {
    background-color: color-mix(in srgb, var(--bs-info) 18%, var(--bs-white)) !important;
    color: var(--bs-info-text-emphasis) !important;
}
.comp-av-table tbody td.asg-cell-hist:not([class*="asg-hist-bg"]) {
    background-color: color-mix(in srgb, var(--bs-secondary-bg) 28%, var(--bs-white));
}
.comp-av-table tbody td.asg-cell-act {
    background-color: color-mix(in srgb, var(--bs-success) 8%, var(--bs-white));
}
.comp-av-table tbody td.asg-cell-fut {
    background-color: color-mix(in srgb, var(--bs-info) 10%, var(--bs-white));
}
.comp-num-empty { color: var(--bs-secondary-color) !important; }
.comp-av-table--asg tbody td[class*="asg-hist-bg-"].comp-num-empty {
    color: var(--bs-emphasis-color) !important;
}

body.dark-mode .comp-av-card {
    background-color: var(--bs-secondary-bg);
    border-color: var(--bs-border-color) !important;
    color: var(--bs-body-color);
}
body.dark-mode .comp-av-toolbar {
    background-color: var(--bs-body-bg);
    border-color: var(--bs-border-color) !important;
}
body.dark-mode .comp-av-page-header .comp-av-heading { color: var(--bs-heading-color) !important; }
body.dark-mode .comp-av-page-header .text-primary { color: var(--bs-link-color) !important; }
body.dark-mode .comp-av-toolbar .comp-av-logo { filter: brightness(1.08); }
body.dark-mode .comp-av-toolbar .form-label { color: var(--bs-secondary-color) !important; }
body.dark-mode .comp-av-toolbar .form-select {
    background-color: var(--bs-tertiary-bg);
    border-color: var(--bs-border-color);
    color: var(--bs-body-color);
}
body.dark-mode .comp-av-table--asg thead tr.asg-thead-chips > th.asg-chip-th {
    background-color: var(--bs-body-bg) !important;
}
body.dark-mode .comp-av-table-stack {
    border-color: var(--bs-border-color);
    margin: 0.5rem 1rem 1rem;
}
body.dark-mode .comp-av-table-stack #asg-table-area .table-responsive { border: 0 !important; }
body.dark-mode .comp-av .comp-chip-pill {
    background-color: var(--bs-secondary-bg);
    border-color: var(--bs-border-color);
    color: var(--bs-body-color);
}
body.dark-mode .comp-av .comp-chip-pill-hist strong { color: var(--bs-body-color); }
body.dark-mode .comp-av .comp-chip-pill-act {
    border-color: var(--bs-success) !important;
    box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--bs-success) 35%, var(--bs-secondary-bg));
}
body.dark-mode .comp-av .comp-chip-pill-act strong { color: var(--bs-success); }
body.dark-mode .comp-av .comp-chip-pill-fut {
    border-color: var(--bs-info) !important;
    box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--bs-info) 35%, var(--bs-secondary-bg));
}
body.dark-mode .comp-av .comp-chip-pill-fut strong { color: var(--bs-info); }
body.dark-mode .comp-av-table {
    --bs-table-border-color: var(--bs-gray-500);
    --comp-sep-color: color-mix(in srgb, var(--bs-gray-300) 68%, var(--bs-white));
    color: var(--bs-body-color);
    --bs-table-bg: transparent;
    border-collapse: collapse;
    border-color: var(--bs-border-color) !important;
}
body.dark-mode .comp-av-table .comp-sep-r {
    border-right-color: var(--comp-sep-color) !important;
}
body.dark-mode .comp-av-table > thead.comp-av-thead > tr > th {
    border-color: var(--bs-gray-500) !important;
}
body.dark-mode .comp-av-table > thead.comp-av-thead .comp-subcell {
    border-left-color: var(--comp-sep-color) !important;
    border-right-color: var(--comp-sep-color) !important;
}
body.dark-mode .comp-av-table--asg > thead.comp-av-thead .comp-subcell.asg-col-ext {
    border-left-color: color-mix(in srgb, var(--bs-gray-500) 55%, var(--bs-border-color)) !important;
    border-right-color: color-mix(in srgb, var(--bs-gray-500) 42%, var(--bs-border-color)) !important;
    border-left-width: 1px !important;
    border-right-width: 1px !important;
}
body.dark-mode .comp-av-table--asg > thead.comp-av-thead .comp-subcell.asg-col-nom {
    border-left-color: color-mix(in srgb, var(--bs-gray-500) 42%, var(--bs-border-color)) !important;
    border-right-color: color-mix(in srgb, var(--bs-gray-500) 35%, var(--bs-border-color)) !important;
    border-left-width: 1px !important;
    border-right-width: 1px !important;
}
body.dark-mode .comp-av-table--asg > thead.comp-av-thead .comp-subcell.asg-sep-week-end {
    border-left-color: color-mix(in srgb, var(--bs-gray-500) 35%, var(--bs-border-color)) !important;
    border-right-color: var(--bs-border-color) !important;
    border-left-width: 1px !important;
    border-right-width: 1px !important;
}
body.dark-mode .comp-av-table--asg > thead.comp-av-thead > tr.asg-thead-chips > th.asg-sep-week-end:not(:last-of-type),
body.dark-mode .comp-av-table--asg > thead.comp-av-thead > tr.asg-thead-semana > th.asg-sep-week-end:not(:last-of-type) {
    border-right-width: 0 !important;
    border-right-style: none !important;
}
body.dark-mode .comp-av-table--asg > thead.comp-av-thead > tr.asg-thead-chips > th.comp-sep-week + th.comp-sep-week,
body.dark-mode .comp-av-table--asg > thead.comp-av-thead > tr.asg-thead-semana > th.comp-sep-week + th.comp-sep-week {
    border-left-width: 0 !important;
    border-left-style: none !important;
}
body.dark-mode .comp-av-table--asg > tbody > tr > td.asg-col-ext {
    border-right-color: color-mix(in srgb, var(--bs-gray-500) 40%, var(--bs-border-color)) !important;
    border-right-width: 1px !important;
}
body.dark-mode .comp-av-table--asg > tbody > tr > td.asg-col-nom {
    border-right-color: color-mix(in srgb, var(--bs-gray-500) 32%, var(--bs-border-color)) !important;
    border-right-width: 1px !important;
}
body.dark-mode .comp-av-table--asg > tbody > tr > td.asg-sep-week-end {
    border-right-color: var(--bs-border-color) !important;
    border-right-width: 1px !important;
}
body.dark-mode .comp-av-table--asg > thead.comp-av-thead > tr > th.asg-th-id-col,
body.dark-mode .comp-av-table--asg > tbody > tr > td.asg-td-id-col {
    background-color: var(--bs-body-bg);
    box-shadow: 1px 0 0 var(--bs-border-color);
}
body.dark-mode .comp-av-table--asg > thead.comp-av-thead > tr > th.asg-th-id-col {
    border-right-color: var(--bs-gray-500) !important;
}
body.dark-mode .comp-av .comp-th-act {
    background-color: var(--bs-secondary-bg) !important;
    color: var(--bs-success) !important;
}
body.dark-mode .comp-av-table--asg thead .asg-hist-bg-1,
body.dark-mode .comp-av-table--asg tbody td.asg-hist-bg-1 {
    background-color: color-mix(in srgb, var(--bs-gray-600) 22%, var(--bs-secondary-bg)) !important;
    color: var(--bs-emphasis-color) !important;
}
body.dark-mode .comp-av-table--asg thead .asg-hist-bg-2,
body.dark-mode .comp-av-table--asg tbody td.asg-hist-bg-2 {
    background-color: color-mix(in srgb, var(--bs-gray-600) 32%, var(--bs-secondary-bg)) !important;
    color: var(--bs-emphasis-color) !important;
}
body.dark-mode .comp-av-table--asg thead .asg-hist-bg-3,
body.dark-mode .comp-av-table--asg tbody td.asg-hist-bg-3 {
    background-color: color-mix(in srgb, var(--bs-gray-700) 28%, var(--bs-body-bg)) !important;
    color: var(--bs-emphasis-color) !important;
}
body.dark-mode .comp-av .comp-th-fut {
    background-color: color-mix(in srgb, var(--bs-info) 22%, var(--bs-secondary-bg)) !important;
    color: var(--bs-info) !important;
}
body.dark-mode .comp-av-table:not(.comp-av-table--asg) tbody tr:nth-child(odd) td { background-color: var(--bs-secondary-bg); }
body.dark-mode .comp-av-table:not(.comp-av-table--asg) tbody tr:nth-child(even) td { background-color: var(--bs-body-bg); }
body.dark-mode .comp-av-table tbody td.asg-cell-hist:not([class*="asg-hist-bg"]) {
    background-color: color-mix(in srgb, var(--bs-secondary-bg) 88%, var(--bs-body-bg));
}
body.dark-mode .comp-av-table tbody td.asg-cell-act {
    background-color: color-mix(in srgb, var(--bs-success) 12%, var(--bs-secondary-bg));
}
body.dark-mode .comp-av-table tbody td.asg-cell-fut {
    background-color: color-mix(in srgb, var(--bs-info) 14%, var(--bs-secondary-bg));
}
body.dark-mode .comp-av .asg-chip-hist-1 { background-color: color-mix(in srgb, var(--bs-gray-600) 24%, var(--bs-secondary-bg)) !important; color: var(--bs-emphasis-color) !important; }
body.dark-mode .comp-av .asg-chip-hist-2 { background-color: color-mix(in srgb, var(--bs-gray-600) 34%, var(--bs-secondary-bg)) !important; color: var(--bs-emphasis-color) !important; }
body.dark-mode .comp-av .asg-chip-hist-3 { background-color: color-mix(in srgb, var(--bs-gray-700) 30%, var(--bs-body-bg)) !important; color: var(--bs-emphasis-color) !important; }
body.dark-mode .comp-av .comp-num-empty { color: var(--bs-secondary-color) !important; }
body.dark-mode .comp-av-table--asg tbody td[class*="asg-hist-bg-"].comp-num-empty {
    color: var(--bs-emphasis-color) !important;
}
body.dark-mode .asg-pag-btn {
    background: var(--bs-tertiary-bg);
    border-color: var(--bs-border-color);
    color: var(--bs-body-color);
}
body.dark-mode .asg-pag-btn--icon {
    color: var(--bs-emphasis-color);
}
body.dark-mode .asg-pag-btn--ellipsis {
    background: var(--bs-body-bg);
    border-color: var(--bs-border-color);
    color: var(--bs-secondary-color);
}
body.dark-mode .asg-pag-btn:hover:not(.is-disabled):not(.is-active):not(.asg-pag-btn--ellipsis) {
    background: var(--bs-secondary-bg);
    border-color: var(--bs-border-color);
    color: var(--bs-emphasis-color);
}
body.dark-mode .asg-pag-btn.is-active {
    background: linear-gradient(180deg, #2563a8 0%, #1a4a7a 100%);
    border-color: #143a62;
    color: #fff;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.45);
}
</style>
<script>
(function () {
    var btn = document.getElementById('asg-btn-descargar-excel');
    if (!btn) return;
    var url = btn.getAttribute('href');
    if (!url) return;
    btn.addEventListener('click', function (e) {
        e.preventDefault();
        if (typeof Swal === 'undefined') {
            window.location.href = url;
            return;
        }
        Swal.fire({
            title: 'Descargando Excel',
            html: '<p class="text-body-secondary mb-3 mb-md-4">Por favor espere mientras se genera el archivo con <strong>todo</strong> el portafolio…</p>' +
                '<div class="spinner-border text-success" style="width:3rem;height:3rem;" role="status" aria-hidden="true"></div>' +
                '<span class="visually-hidden">Cargando</span>',
            allowOutsideClick: false,
            showConfirmButton: false,
            customClass: { popup: 'shadow' }
        });
        fetch(url, { credentials: 'same-origin' })
            .then(function (r) {
                if (!r.ok) {
                    throw new Error('HTTP ' + r.status);
                }
                var cd = r.headers.get('Content-Disposition') || '';
                var name = 'Asignacion_Tablero.xlsx';
                var m = cd.match(/filename="([^"]+)"/i) || cd.match(/filename=([^;\s]+)/i);
                if (m && m[1]) {
                    name = m[1].replace(/^["']|["']$/g, '');
                }
                return r.blob().then(function (blob) {
                    return { blob: blob, name: name };
                });
            })
            .then(function (x) {
                Swal.close();
                var u = URL.createObjectURL(x.blob);
                var a = document.createElement('a');
                a.href = u;
                a.download = x.name;
                a.rel = 'noopener';
                document.body.appendChild(a);
                a.click();
                a.remove();
                setTimeout(function () { URL.revokeObjectURL(u); }, 120000);
            })
            .catch(function () {
                Swal.fire({
                    icon: 'error',
                    title: 'No se pudo descargar',
                    text: 'Intente de nuevo o contacte a sistemas si el problema continúa.'
                });
            });
    });
})();
</script>
