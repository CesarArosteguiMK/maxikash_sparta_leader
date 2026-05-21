<?php
/** @var ?array $comparativo_payload */
/** @var ?string $comparativo_error */
/** @var string $comparativo_initial_json */
$cmpError = isset($comparativo_error) ? (string) $comparativo_error : '';
?>
<div class="cmp930 container-fluid py-3 px-2 px-md-3">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <h4 class="mb-0 text-primary d-flex align-items-center flex-wrap">
            <i class="fa-solid fa-chart-column me-2" aria-hidden="true"></i>
            <span id="cmp930-page-title">Comparativo</span>
        </h4>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <label class="cmp930-corte-control mb-0">
                <span>Corte</span>
                <select id="cmp930-corte" class="form-select form-select-sm"></select>
            </label>
            <span id="cmp930-status" class="badge <?= is_array($comparativo_payload ?? null) ? 'bg-label-success' : 'bg-label-danger'; ?>">
                <?= is_array($comparativo_payload ?? null) ? 'Servicio: activo' : 'Servicio: no disponible'; ?>
            </span>
            <button type="button" id="cmp930-refresh" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-rotate me-1"></i>Actualizar
            </button>
            <a href="/analitica/comparativas" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-arrow-left me-1"></i>Volver
            </a>
        </div>
    </div>

    <?php if ($cmpError !== ''): ?>
        <div class="alert alert-warning shadow-sm" id="cmp930-alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>
            <?= htmlspecialchars($cmpError, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php else: ?>
        <div class="alert alert-warning shadow-sm d-none" id="cmp930-alert"></div>
    <?php endif; ?>

    <div class="cmp930-board">
        <div class="cmp930-grid">
            <section class="card shadow-sm cmp930-panel">
                <div class="card-body">
                    <h5 class="cmp930-title" id="cmp930-title-creditos-pasada">Semana pasada Creditos</h5>
                    <div class="table-responsive">
                        <table class="table table-sm cmp930-table mb-0">
                            <thead>
                            <tr><th>Bucket</th><th class="text-end">Creditos</th><th class="text-end">%</th></tr>
                            </thead>
                            <tbody id="cmp930-creditos-pasada"></tbody>
                        </table>
                    </div>
                </div>
            </section>

            <div class="cmp930-card-wrap">
                <div class="card shadow-sm cmp930-kpi">
                    <div class="card-body text-center">
                        <div class="cmp930-kpi-value text-danger" id="cmp930-kpi-creditos-pasada">0.00%</div>
                        <div class="cmp930-kpi-label">% Creditos<br>Semana Pasada</div>
                    </div>
                </div>
            </div>

            <section class="card shadow-sm cmp930-panel">
                <div class="card-body">
                    <h5 class="cmp930-title" id="cmp930-title-creditos-actual">Semana actual Creditos</h5>
                    <div class="table-responsive">
                        <table class="table table-sm cmp930-table mb-0">
                            <thead>
                            <tr><th>Bucket</th><th class="text-end">Creditos</th><th class="text-end">%</th></tr>
                            </thead>
                            <tbody id="cmp930-creditos-actual"></tbody>
                        </table>
                    </div>
                </div>
            </section>

            <div class="cmp930-card-wrap">
                <div class="card shadow-sm cmp930-kpi">
                    <div class="card-body text-center">
                        <div class="cmp930-kpi-value text-success" id="cmp930-kpi-creditos-actual">0.00%</div>
                        <div class="cmp930-kpi-label">% Creditos<br>Semana Actual</div>
                    </div>
                </div>
            </div>

            <section class="card shadow-sm cmp930-panel">
                <div class="card-body">
                    <h5 class="cmp930-title" id="cmp930-title-capital-pasada">Semana pasada Saldo Capital</h5>
                    <div class="table-responsive">
                        <table class="table table-sm cmp930-table mb-0">
                            <thead>
                            <tr><th>Bucket</th><th class="text-end">Saldo Capital</th><th class="text-end">%</th></tr>
                            </thead>
                            <tbody id="cmp930-capital-pasada"></tbody>
                        </table>
                    </div>
                </div>
            </section>

            <div class="cmp930-card-wrap">
                <div class="card shadow-sm cmp930-kpi">
                    <div class="card-body text-center">
                        <div class="cmp930-kpi-value text-danger" id="cmp930-kpi-capital-pasada">0.00%</div>
                        <div class="cmp930-kpi-label">% Capital Semana<br>Pasada</div>
                    </div>
                </div>
            </div>

            <section class="card shadow-sm cmp930-panel">
                <div class="card-body">
                    <h5 class="cmp930-title" id="cmp930-title-capital-actual">Semana actual Saldo Capital</h5>
                    <div class="table-responsive">
                        <table class="table table-sm cmp930-table mb-0">
                            <thead>
                            <tr><th>Bucket</th><th class="text-end">Saldo Capital</th><th class="text-end">%</th></tr>
                            </thead>
                            <tbody id="cmp930-capital-actual"></tbody>
                        </table>
                    </div>
                </div>
            </section>

            <div class="cmp930-card-wrap">
                <div class="card shadow-sm cmp930-kpi">
                    <div class="card-body text-center">
                        <div class="cmp930-kpi-value text-success" id="cmp930-kpi-capital-actual">0.00%</div>
                        <div class="cmp930-kpi-label">% Capital Semana<br>Actual</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.cmp930-board {
    background: #eef1f5;
    border-radius: 6px;
    padding: 1rem;
}
.cmp930-corte-control {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    color: #0b2d4a;
    font-size: .82rem;
    font-weight: 600;
}
.cmp930-corte-control .form-select {
    min-width: 92px;
}
.cmp930-grid {
    display: grid;
    grid-template-columns: minmax(280px, 1.2fr) minmax(130px, .4fr) minmax(280px, 1.2fr) minmax(130px, .4fr);
    gap: 1rem;
    align-items: center;
}
.cmp930-panel {
    border: 0;
    border-radius: 6px;
}
.cmp930-panel .card-body {
    padding: .85rem;
}
.cmp930-title {
    color: #0b2d4a;
    font-size: 1.08rem;
    line-height: 1.2;
    margin: 0 0 .35rem;
}
.cmp930-table {
    color: #0b2d4a;
    font-size: .82rem;
    table-layout: fixed;
}
.cmp930-table th {
    background: #e7e3fb;
    border-bottom: 1px solid #b7a7ff;
    color: #0b2d4a;
    font-weight: 500;
}
.cmp930-table td,
.cmp930-table th {
    padding: .25rem .35rem;
    vertical-align: middle;
}
.cmp930-table tbody tr:not(.cmp930-total-row) td {
    border-bottom: 1px solid #e5e8ef;
}
.cmp930-table .cmp930-total-row td {
    border-top: 1px solid #7a6cff;
    font-weight: 700;
}
.cmp930-card-wrap {
    display: flex;
    justify-content: center;
}
.cmp930-kpi {
    border: 0;
    border-radius: 6px;
    min-width: 128px;
    width: 100%;
    max-width: 160px;
}
.cmp930-kpi .card-body {
    padding: 1rem .7rem;
}
.cmp930-kpi-value {
    font-size: 1.55rem;
    line-height: 1.1;
    font-weight: 500;
    margin-bottom: .35rem;
}
.cmp930-kpi-label {
    color: #0b2d4a;
    font-size: .85rem;
    line-height: 1.05;
}
@media (max-width: 1199.98px) {
    .cmp930-grid {
        grid-template-columns: minmax(280px, 1fr) minmax(120px, .38fr);
    }
}
@media (max-width: 767.98px) {
    .cmp930-board {
        padding: .75rem;
    }
    .cmp930-grid {
        grid-template-columns: 1fr;
    }
    .cmp930-kpi {
        max-width: none;
    }
}
</style>

<script>
(function () {
    const initialData = <?= $comparativo_initial_json ?? 'null'; ?>;
    const endpoint = '/analitica/getComparativoCierreSemanalJson';

    const fmtInt = new Intl.NumberFormat('es-MX', { maximumFractionDigits: 0 });
    const fmtMoney = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' });
    const fmtPct = new Intl.NumberFormat('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    function el(id) { return document.getElementById(id); }
    function pct(v) { return fmtPct.format(Number(v || 0)) + '%'; }
    function selectedCorte() {
        const select = el('cmp930-corte');
        return select && select.value ? select.value : '14:30';
    }

    function syncCorteOptions(data) {
        const select = el('cmp930-corte');
        if (!select || !data) return;
        const options = Array.isArray(data.corte_opciones) && data.corte_opciones.length
            ? data.corte_opciones
            : ['07:30', '09:30', '11:30', '13:30', '14:30', '16:30', '18:30', '20:30', '23:50'];
        const current = data.corte || select.value || '14:30';

        if (!select.options.length) {
            select.innerHTML = options.map(corte => '<option value="' + escapeHtml(corte) + '">' + escapeHtml(corte) + '</option>').join('');
        }
        select.value = current;
    }

    function renderRows(targetId, metric, isMoney) {
        const tbody = el(targetId);
        if (!tbody || !metric) return;
        const rows = Array.isArray(metric.filas) ? metric.filas : [];
        tbody.innerHTML = rows.map(row => {
            const value = isMoney ? fmtMoney.format(Number(row.valor || 0)) : fmtInt.format(Number(row.valor || 0));
            return '<tr>' +
                '<td>' + escapeHtml(row.bucket || '') + '</td>' +
                '<td class="text-end">' + value + '</td>' +
                '<td class="text-end">' + pct(row.porcentaje) + '</td>' +
                '</tr>';
        }).join('') + '<tr class="cmp930-total-row">' +
            '<td>Total</td>' +
            '<td class="text-end">' + (isMoney ? fmtMoney.format(Number(metric.total || 0)) : fmtInt.format(Number(metric.total || 0))) + '</td>' +
            '<td class="text-end">100.00%</td>' +
            '</tr>';
    }

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[ch]));
    }

    function setStatus(ok, msg) {
        const status = el('cmp930-status');
        if (!status) return;
        status.className = 'badge ' + (ok ? 'bg-label-success' : 'bg-label-danger');
        status.textContent = msg || (ok ? 'Servicio: activo' : 'Servicio: no disponible');
    }

    function showError(message) {
        const box = el('cmp930-alert');
        if (!box) return;
        box.className = 'alert alert-warning shadow-sm';
        box.innerHTML = '<i class="fa-solid fa-triangle-exclamation me-2"></i>' + escapeHtml(message);
    }

    function showWarnings(messages) {
        const box = el('cmp930-alert');
        if (!box) return;
        const list = messages.map(message => '<div>' + escapeHtml(message) + '</div>').join('');
        box.className = 'alert alert-warning shadow-sm';
        box.innerHTML = '<i class="fa-solid fa-triangle-exclamation me-2"></i>' + list;
    }

    function hideError() {
        const box = el('cmp930-alert');
        if (!box) return;
        box.className = 'alert alert-warning shadow-sm d-none';
        box.textContent = '';
    }

    function render(data) {
        if (!data || data.success === false) {
            setStatus(false);
            showError((data && data.mensaje) ? data.mensaje : 'No se pudieron cargar los datos.');
            return;
        }

        syncCorteOptions(data);
        if (Array.isArray(data.advertencias) && data.advertencias.length) {
            showWarnings(data.advertencias);
        } else {
            hideError();
        }
        setStatus(true);
        const corteLabel = (data.dia_corte ? data.dia_corte + ' ' : '') + (data.corte || '14:30');
        el('cmp930-page-title').textContent = 'Comparativo ' + (data.corte || '14:30');
        el('cmp930-title-creditos-pasada').textContent = data.semana_pasada + ' ' + corteLabel + ' Creditos';
        el('cmp930-title-creditos-actual').textContent = data.semana_actual + ' ' + corteLabel + ' Creditos';
        el('cmp930-title-capital-pasada').textContent = data.semana_pasada + ' ' + corteLabel + ' Saldo Capital';
        el('cmp930-title-capital-actual').textContent = data.semana_actual + ' ' + corteLabel + ' Saldo Capital';

        renderRows('cmp930-creditos-pasada', data.creditos && data.creditos.semana_pasada, false);
        renderRows('cmp930-creditos-actual', data.creditos && data.creditos.semana_actual, false);
        renderRows('cmp930-capital-pasada', data.capital && data.capital.semana_pasada, true);
        renderRows('cmp930-capital-actual', data.capital && data.capital.semana_actual, true);

        const t = data.tarjetas || {};
        el('cmp930-kpi-creditos-pasada').textContent = pct(t.creditos_semana_pasada);
        el('cmp930-kpi-creditos-actual').textContent = pct(t.creditos_semana_actual);
        el('cmp930-kpi-capital-pasada').textContent = pct(t.capital_semana_pasada);
        el('cmp930-kpi-capital-actual').textContent = pct(t.capital_semana_actual);
    }

    async function refresh() {
        const btn = el('cmp930-refresh');
        if (btn) btn.disabled = true;
        try {
            const url = endpoint + '?corte=' + encodeURIComponent(selectedCorte());
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            render(data);
        } catch (e) {
            setStatus(false);
            showError('Error de red al consultar el comparativo.');
        } finally {
            if (btn) btn.disabled = false;
        }
    }

    el('cmp930-refresh')?.addEventListener('click', refresh);
    el('cmp930-corte')?.addEventListener('change', refresh);
    render(initialData);
})();
</script>
