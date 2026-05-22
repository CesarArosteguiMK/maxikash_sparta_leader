<?php
/** @var ?array $avance_bucket_payload */
/** @var ?string $avance_bucket_error */
/** @var string $avance_bucket_initial_json */
$avanceError = isset($avance_bucket_error) ? (string) $avance_bucket_error : '';
?>
<div class="avance-bucket container-fluid py-3 px-2 px-md-3">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <h4 class="mb-0 text-primary d-flex align-items-center flex-wrap">
            <i class="fa-solid fa-chart-line me-2" aria-hidden="true"></i>
            <span>Avance Bucket</span>
            <span id="ab-corte-badge" class="badge rounded-pill bg-label-primary ms-2">-</span>
        </h4>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <label class="ab-corte-control mb-0">
                <span>Corte</span>
                <select id="ab-corte" class="form-select form-select-sm"></select>
            </label>
            <span id="ab-status" class="badge <?= is_array($avance_bucket_payload ?? null) ? 'bg-label-success' : 'bg-label-danger'; ?>">
                <?= is_array($avance_bucket_payload ?? null) ? 'Servicio: activo' : 'Servicio: no disponible'; ?>
            </span>
            <button type="button" id="ab-refresh" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-rotate me-1"></i>Actualizar
            </button>
            <a href="/analitica/comparativas" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-arrow-left me-1"></i>Volver
            </a>
        </div>
    </div>

    <?php if ($avanceError !== ''): ?>
        <div class="alert alert-warning shadow-sm" id="ab-alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>
            <?= htmlspecialchars($avanceError, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php else: ?>
        <div class="alert alert-warning shadow-sm d-none" id="ab-alert"></div>
    <?php endif; ?>

    <div class="card shadow-sm border ab-card-main overflow-hidden">
        <div class="card-body border-bottom py-3 ab-toolbar">
            <div class="ab-summary-grid">
                <div class="ab-kpi">
                    <div class="ab-kpi-label">Total creditos</div>
                    <div class="ab-kpi-value" id="ab-total">0</div>
                </div>
                <div class="ab-kpi">
                    <div class="ab-kpi-label">Mejoran bucket</div>
                    <div class="ab-kpi-value text-success" id="ab-mejoran">0</div>
                </div>
                <div class="ab-kpi">
                    <div class="ab-kpi-label">Sin cambio</div>
                    <div class="ab-kpi-value text-primary" id="ab-igual">0</div>
                </div>
                <div class="ab-kpi">
                    <div class="ab-kpi-label">Empeoran bucket</div>
                    <div class="ab-kpi-value text-danger" id="ab-empeoran">0</div>
                </div>
            </div>
        </div>

        <div class="ab-content">
            <div class="ab-resumen-grid">
                <section class="ab-panel">
                    <h5 class="ab-card-title">Bucket Inicio Creditos</h5>
                    <div id="ab-resumen-inicio"></div>
                </section>
                <section class="ab-panel">
                    <h5 class="ab-card-title">Bucket Inicio %</h5>
                    <div id="ab-resumen-inicio-pct"></div>
                </section>
            </div>

            <section class="ab-panel ab-matrix-panel">
                <h5 class="ab-card-title">Matriz de avance bucket</h5>
                <div class="table-responsive ab-table-wrap">
                    <table class="table table-sm table-bordered ab-table mb-0" id="ab-matriz-creditos"></table>
                </div>
            </section>

            <section class="ab-panel ab-matrix-panel">
                <h5 class="ab-card-title">Matriz de avance bucket %</h5>
                <div class="table-responsive ab-table-wrap">
                    <table class="table table-sm table-bordered ab-table mb-0" id="ab-matriz-porcentajes"></table>
                </div>
            </section>
        </div>
    </div>
</div>

<style>
.ab-card-main {
    border-radius: 8px;
}
.ab-content {
    background: #eef1f5;
    display: grid;
    grid-template-columns: minmax(240px, 300px) minmax(0, 1fr);
    gap: .7rem;
    align-items: start;
    padding: .75rem;
}
.ab-toolbar {
    background: #fff;
}
.ab-corte-control {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    color: #0b2d4a;
    font-size: .82rem;
    font-weight: 600;
}
.ab-corte-control .form-select {
    min-width: 92px;
}
.ab-summary-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(160px, 1fr));
    gap: .6rem;
}
.ab-kpi {
    background: #fff;
    border: 1px solid #e5e8ef;
    border-radius: 6px;
    padding: .6rem .75rem;
}
.ab-kpi-label {
    color: #516074;
    font-size: .78rem;
    font-weight: 700;
    text-transform: uppercase;
}
.ab-kpi-value {
    color: #0b2d4a;
    font-size: 1.35rem;
    font-weight: 700;
    line-height: 1.15;
}
.ab-card-title {
    color: #0b2d4a;
    font-size: .92rem;
    margin: 0 0 .45rem;
}
.ab-resumen-grid {
    display: grid;
    grid-column: 1;
    grid-row: 1 / span 2;
    grid-template-columns: 1fr;
    gap: 2rem;
    margin-bottom: 0;
}
.ab-panel {
    background: #fff;
    border: 1px solid #e5e8ef;
    border-radius: 6px;
    padding: .6rem;
    box-shadow: 0 .125rem .5rem rgba(67, 89, 113, .08);
}
.ab-matrix-panel + .ab-matrix-panel {
    margin-top: .7rem;
}
.ab-matrix-panel {
    grid-column: 2;
    min-width: 0;
}
.ab-res-row {
    display: grid;
    grid-template-columns: minmax(130px, 1fr) 84px;
    gap: .35rem;
    align-items: center;
    padding: .25rem 0;
    border-bottom: 1px solid #e5e8ef;
    color: #0b2d4a;
    font-size: .72rem;
}
.ab-res-row:last-child {
    border-bottom: 0;
    font-weight: 700;
}
.ab-table {
    color: #0b2d4a;
    font-size: .68rem;
    table-layout: fixed;
    min-width: 0;
    width: 100%;
    border-color: #d8deea !important;
}
.ab-table th {
    background: #e7e3fb;
    border-bottom: 1px solid #b7a7ff;
    color: #0b2d4a;
    font-weight: 600;
    white-space: normal;
    line-height: 1.15;
}
.ab-table td,
.ab-table th {
    padding: .22rem .2rem !important;
    vertical-align: middle;
    border-color: #dfe5ef !important;
}
.ab-table td:first-child,
.ab-table th:first-child {
    position: sticky;
    left: 0;
    z-index: 2;
    background: #fff;
    min-width: 118px;
    width: 118px;
    font-weight: 600;
}
.ab-table th:first-child {
    z-index: 3;
    background: #e7e3fb;
}
.ab-table-wrap {
    border: 1px solid #d8deea;
    border-radius: 6px;
    overflow: auto;
}
.ab-table tbody tr:not(.ab-total-row) td {
    border-bottom: 1px solid #e5e8ef;
}
.ab-table .ab-total-row td {
    border-top: 1px solid #7a6cff;
    font-weight: 700;
}
.ab-cell-zero {
    color: #9aa5b5;
}
.ab-cell-improve {
    background: rgba(40, 199, 111, .1);
}
.ab-cell-worse {
    background: rgba(234, 84, 85, .08);
}
@media (max-width: 991.98px) {
    .ab-content {
        grid-template-columns: 1fr;
    }
    .ab-summary-grid {
        grid-template-columns: repeat(2, minmax(140px, 1fr));
    }
    .ab-resumen-grid {
        grid-column: auto;
        grid-row: auto;
        grid-template-columns: 1fr;
        gap: 1rem;
        margin-bottom: 0;
    }
    .ab-matrix-panel {
        grid-column: auto;
    }
}
@media (max-width: 575.98px) {
    .ab-summary-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
(function () {
    const initialData = <?= $avance_bucket_initial_json ?? 'null'; ?>;
    const endpoint = '/analitica/getAvanceBucketJson';
    const fmtInt = new Intl.NumberFormat('es-MX', { maximumFractionDigits: 0 });
    const fmtPct = new Intl.NumberFormat('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    function el(id) { return document.getElementById(id); }
    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[ch]));
    }
    function setStatus(ok, msg) {
        const status = el('ab-status');
        if (!status) return;
        status.className = 'badge ' + (ok ? 'bg-label-success' : 'bg-label-danger');
        status.textContent = msg || (ok ? 'Servicio: activo' : 'Servicio: no disponible');
    }
    function selectedCorte() {
        const select = el('ab-corte');
        return select && select.value ? select.value : '';
    }
    function syncCorteOptions(data) {
        const select = el('ab-corte');
        if (!select || !data) return;
        const options = Array.isArray(data.corte_opciones) && data.corte_opciones.length
            ? data.corte_opciones
            : ['07:30', '09:30', '11:30', '13:30', '14:30', '16:30', '18:30', '20:30', '23:50'];
        const current = data.corte || select.value || '';
        if (!select.options.length) {
            select.innerHTML = options.map(corte => '<option value="' + escapeHtml(corte) + '">' + escapeHtml(corte) + '</option>').join('');
        }
        select.value = current;
    }
    function showError(message) {
        const box = el('ab-alert');
        if (!box) return;
        box.className = 'alert alert-warning shadow-sm';
        box.innerHTML = '<i class="fa-solid fa-triangle-exclamation me-2"></i>' + escapeHtml(message);
    }
    function hideError() {
        const box = el('ab-alert');
        if (!box) return;
        box.className = 'alert alert-warning shadow-sm d-none';
        box.textContent = '';
    }
    function showLoading() {
        if (typeof Swal === 'undefined') return;
        Swal.fire({
            title: 'Cargando datos',
            text: 'Consultando Avance Bucket...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading()
        });
    }
    function closeLoading() {
        if (typeof Swal !== 'undefined' && Swal.isVisible()) Swal.close();
    }
    function bucketOrder(buckets) {
        const out = {};
        (buckets || []).forEach(b => { out[b.bucket] = Number(b.orden || 0); });
        return out;
    }
    function shortBucket(bucket) {
        return String(bucket || '')
            .replace('a) Current', 'Current')
            .replace('b) 1 a 7 dias', '1 a 7')
            .replace('c) 8 a 14 dias', '8 a 14')
            .replace('d) 15 a 21 dias', '15 a 21')
            .replace('e) 22 a 30 dias', '22 a 30')
            .replace('f) 31 a 60 dias', '31 a 60')
            .replace('g) 61 a 90 dias', '61 a 90')
            .replace('h) 91 a 120 dias', '91 a 120')
            .replace('i) 121+ dias', '121+');
    }
    function renderResumen(targetId, rows, total, mode) {
        const box = el(targetId);
        if (!box) return;
        const isPct = mode === 'pct';
        const html = (rows || []).map(row => (
            '<div class="ab-res-row">' +
                '<span>' + escapeHtml(row.bucket || '') + '</span>' +
                '<span class="text-end">' + (isPct ? fmtPct.format(Number(row.porcentaje || 0)) + '%' : fmtInt.format(Number(row.valor || 0))) + '</span>' +
            '</div>'
        )).join('');
        box.innerHTML = html + '<div class="ab-res-row"><span>Total</span><span class="text-end">' + (isPct ? '100.00%' : fmtInt.format(Number(total || 0))) + '</span></div>';
    }
    function formatValue(value, isPct) {
        const n = Number(value || 0);
        if (n === 0) return '-';
        return isPct ? fmtPct.format(n) + '%' : fmtInt.format(n);
    }
    function renderMatriz(targetId, matrix, buckets, isPct) {
        const table = el(targetId);
        if (!table || !matrix) return;
        const order = bucketOrder(buckets);
        const headers = (buckets || []).map(b => '<th class="text-end">' + escapeHtml(shortBucket(b.bucket)) + '</th>').join('');
        const body = (matrix.filas || []).map(row => {
            const cells = (buckets || []).map(col => {
                const value = row.celdas ? row.celdas[col.bucket] : 0;
                const cls = Number(value || 0) === 0 ? 'ab-cell-zero' : (order[col.bucket] < order[row.bucket] ? 'ab-cell-improve' : (order[col.bucket] > order[row.bucket] ? 'ab-cell-worse' : ''));
                return '<td class="text-end ' + cls + '">' + formatValue(value, isPct) + '</td>';
            }).join('');
            return '<tr><td>' + escapeHtml(row.bucket || '') + '</td>' + cells + '<td class="text-end fw-bold">' + formatValue(row.total, isPct) + '</td></tr>';
        }).join('');
        const totals = (buckets || []).map(col => '<td class="text-end">' + formatValue(matrix.totales_columnas ? matrix.totales_columnas[col.bucket] : 0, isPct) + '</td>').join('');
        table.innerHTML = '<thead><tr><th>Bucket Inicio</th>' + headers + '<th class="text-end">Total</th></tr></thead>' +
            '<tbody>' + body + '<tr class="ab-total-row"><td>Total</td>' + totals + '<td class="text-end">' + formatValue(matrix.total, isPct) + '</td></tr></tbody>';
    }
    function render(data) {
        if (!data || data.success === false) {
            setStatus(false);
            showError((data && data.mensaje) ? data.mensaje : 'No se pudieron cargar los datos.');
            return;
        }
        hideError();
        syncCorteOptions(data);
        setStatus(true);
        const corteBadge = el('ab-corte-badge');
        if (corteBadge) {
            corteBadge.textContent = (data.dia_corte ? data.dia_corte + ' ' : '') + (data.corte || '');
        }
        el('ab-total').textContent = fmtInt.format(Number(data.total || 0));
        const ind = data.indicadores || {};
        el('ab-mejoran').textContent = fmtInt.format(Number(ind.mejoran || 0));
        el('ab-igual').textContent = fmtInt.format(Number(ind.igual || 0));
        el('ab-empeoran').textContent = fmtInt.format(Number(ind.empeoran || 0));
        renderResumen('ab-resumen-inicio', data.resumen_inicio, data.total, 'count');
        renderResumen('ab-resumen-inicio-pct', data.resumen_inicio, data.total, 'pct');
        renderMatriz('ab-matriz-creditos', data.matriz_creditos, data.buckets, false);
        renderMatriz('ab-matriz-porcentajes', data.matriz_porcentajes, data.buckets, true);
    }
    async function refresh() {
        const btn = el('ab-refresh');
        const select = el('ab-corte');
        if (btn) btn.disabled = true;
        if (select) select.disabled = true;
        showLoading();
        try {
            const suffix = selectedCorte() ? '?corte=' + encodeURIComponent(selectedCorte()) : '';
            const res = await fetch(endpoint + suffix, { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            render(data);
        } catch (e) {
            setStatus(false);
            showError('Error de red al consultar Avance Bucket.');
        } finally {
            if (btn) btn.disabled = false;
            if (select) select.disabled = false;
            closeLoading();
        }
    }
    el('ab-refresh')?.addEventListener('click', refresh);
    el('ab-corte')?.addEventListener('change', refresh);
    render(initialData);
})();
</script>
