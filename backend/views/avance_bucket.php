<?php
/** @var ?array $avance_bucket_payload */
/** @var ?string $avance_bucket_error */
/** @var string $avance_bucket_initial_json */
/** @var string $avance_bucket_vista */
$avanceError = isset($avance_bucket_error) ? (string) $avance_bucket_error : '';
$nombreUsuario = isset($_SESSION['usuario_nombre'])
    ? htmlspecialchars(strtoupper((string) $_SESSION['usuario_nombre']), ENT_QUOTES, 'UTF-8')
    : 'USUARIO';
$vistaAvanceBucket = isset($avance_bucket_vista) ? (string) $avance_bucket_vista : '';
$mostrarAvanceBucket = in_array($vistaAvanceBucket, ['avance', 'historico', 'estresado'], true);
$esHistoricoAvanceBucket = $vistaAvanceBucket === 'historico';
$esEstresadoAvanceBucket = $vistaAvanceBucket === 'estresado';
$tituloVistaAvanceBucket = $esHistoricoAvanceBucket
    ? 'Historico Avance Bucket'
    : ($esEstresadoAvanceBucket ? 'Bucket estresado' : 'Avance Bucket');
?>
<div class="avance-bucket <?= $mostrarAvanceBucket ? 'container-fluid py-3 px-2 px-md-3' : 'ab-landing-root'; ?>">
    <?php if (!$mostrarAvanceBucket): ?>
    <section class="ab-landing-card mb-4">
        <div class="card">
            <div class="card">
                <div class="card">
                    <div class="row g-0 align-items-center overflow-visible ab-hero-row ab-hero-row--con-mascota ab-hero-block">
                        <div class="col-12 col-md-8 ab-hero-text">
                            <div class="card-body">
                                <h5 class="card-title text-primary mb-3">
                                    HOLA, <?= $nombreUsuario; ?>
                                    <i class="fa-solid fa-chart-simple ms-2 text-primary" aria-hidden="true"></i>
                                </h5>
                                <p class="mb-6 mb-md-0">
                                    Aqui consultas el <strong>Avance Bucket</strong>: una matriz que cruza el
                                    <strong>Bucket de inicio</strong> contra el <strong>cierre ajustado</strong> para identificar
                                    cuantos creditos avanzan, se mantienen o retroceden por corte. Usa el selector para elegir
                                    el horario y revisar la lectura operativa del dia.
                                </p>
                            </div>
                        </div>

                        <div class="col-12 col-md-4 d-flex flex-column justify-content-end align-items-center align-items-md-end ab-hero-mascot-col">
                            <img src="/assets/img/illustrations/comparativas-mascota.png"
                                 class="ab-hero-mascot-floating img-fluid"
                                 width="400"
                                 height="400"
                                 alt="Analitica Maxikash">
                        </div>

                        <div class="row gy-6 mb-6 gx-0 justify-content-start">
                            <div class="col-12 col-lg-4">
                                <div class="card shadow-none bg-label-primary h-100">
                                    <div class="card-body d-flex justify-content-between flex-wrap-reverse">
                                        <div class="mb-0 w-100 app-academy-sm-60 d-flex flex-column justify-content-between text-center text-sm-start">
                                            <div class="card-title">
                                                <h5 class="text-primary mb-2">Avance Bucket</h5>
                                                <p class="text-body w-sm-80 app-academy-xl-100">Entra al tablero de avance por bucket con resumen, matriz de creditos y matriz porcentual por corte.</p>
                                            </div>
                                            <div class="mb-0 mt-3">
                                                <a href="/analitica/avanceBucket/avance" class="btn btn-primary w-100">
                                                    <i class="fa-solid fa-table-cells-large me-1" aria-hidden="true"></i>Ver tablero de avance
                                                </a>
                                            </div>
                                        </div>
                                        <div class="w-100 app-academy-sm-40 d-flex justify-content-center justify-content-sm-end h-px-150 mb-4 mb-sm-0 ab-option-icon ab-option-icon-dark">
                                            <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-lg-4">
                                <div class="card shadow-none bg-label-primary h-100">
                                    <div class="card-body d-flex justify-content-between flex-wrap-reverse">
                                        <div class="mb-0 w-100 app-academy-sm-60 d-flex flex-column justify-content-between text-center text-sm-start">
                                            <div class="card-title">
                                                <h5 class="text-primary mb-2">Historico Avance Bucket</h5>
                                                <p class="text-body w-sm-80 app-academy-xl-100">Revisa el avance por bucket con la misma logica del tablero actual, usando las ultimas 6 semanas del historico.</p>
                                            </div>
                                            <div class="mb-0 mt-3">
                                                <a href="/analitica/avanceBucket/historico" class="btn btn-primary w-100">
                                                    <i class="fa-solid fa-chart-column me-1" aria-hidden="true"></i>Ver historico
                                                </a>
                                            </div>
                                        </div>
                                        <div class="w-100 app-academy-sm-40 d-flex justify-content-center justify-content-sm-end h-px-150 mb-4 mb-sm-0 ab-option-icon ab-option-icon-cyan">
                                            <i class="fa-solid fa-arrow-trend-up" aria-hidden="true"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-lg-4">
                                <div class="card shadow-none bg-label-primary h-100">
                                    <div class="card-body d-flex justify-content-between flex-wrap-reverse">
                                        <div class="mb-0 w-100 app-academy-sm-60 d-flex flex-column justify-content-between text-center text-sm-start">
                                            <div class="card-title">
                                                <h5 class="text-primary mb-2">Bucket estresado</h5>
                                                <p class="text-body w-sm-80 app-academy-xl-100">Consulta la simulacion +1 desde mas_menos, cruzando bucket morosidad real contra cierre actual.</p>
                                            </div>
                                            <div class="mb-0 mt-3">
                                                <a href="/analitica/avanceBucket/estresado" class="btn btn-primary w-100">
                                                    <i class="fa-solid fa-chart-simple me-1" aria-hidden="true"></i>Ver bucket estresado
                                                </a>
                                            </div>
                                        </div>
                                        <div class="w-100 app-academy-sm-40 d-flex justify-content-center justify-content-sm-end h-px-150 mb-4 mb-sm-0 ab-option-icon ab-option-icon-cyan">
                                            <i class="fa-solid fa-arrow-up-right-dots" aria-hidden="true"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php else: ?>

    <div class="ab-report-toolbar d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <h4 class="mb-0 text-primary d-flex align-items-center flex-wrap">
            <i class="fa-solid fa-chart-line me-2" aria-hidden="true"></i>
            <span><?= htmlspecialchars($tituloVistaAvanceBucket, ENT_QUOTES, 'UTF-8'); ?></span>
            <span id="ab-corte-badge" class="badge rounded-pill bg-label-primary ms-2">-</span>
        </h4>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <?php if ($esHistoricoAvanceBucket): ?>
            <label class="ab-corte-control mb-0">
                <span>Semana</span>
                <select id="ab-semana" class="form-select form-select-sm"></select>
            </label>
            <?php endif; ?>
            <?php if (!$esHistoricoAvanceBucket && !$esEstresadoAvanceBucket): ?>
            <label class="ab-corte-control mb-0">
                <span>Corte</span>
                <select id="ab-corte" class="form-select form-select-sm"></select>
            </label>
            <?php endif; ?>
            <span id="ab-status" class="badge <?= is_array($avance_bucket_payload ?? null) ? 'bg-label-success' : 'bg-label-danger'; ?>">
                <?= is_array($avance_bucket_payload ?? null) ? 'Servicio: activo' : 'Servicio: no disponible'; ?>
            </span>
            <button type="button" id="ab-refresh" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-rotate me-1"></i>Actualizar
            </button>
            <a href="/analitica/avanceBucket" class="btn btn-outline-secondary btn-sm">
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
        <div class="ab-content">
            <div class="ab-resumen-grid">
                <section class="ab-panel">
                    <h5 class="ab-card-title" id="ab-resumen-inicio-title">Bucket Inicio Creditos</h5>
                    <div id="ab-resumen-inicio"></div>
                </section>
                <section class="ab-panel">
                    <h5 class="ab-card-title" id="ab-resumen-pct-title">Bucket Inicio %</h5>
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
                <h5 class="ab-card-title" id="ab-matriz-secundaria-title">Matriz de avance bucket %</h5>
                <div class="table-responsive ab-table-wrap">
                    <table class="table table-sm table-bordered ab-table mb-0" id="ab-matriz-porcentajes"></table>
                </div>
            </section>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.ab-landing-card {
    overflow: visible;
}
.ab-option-icon {
    font-size: 6rem;
}
.ab-option-icon-dark {
    color: #2b3b59;
}
.ab-option-icon-cyan {
    color: #12bedb;
}
.ab-hero-block {
    position: relative;
    z-index: 0;
    overflow: visible;
    --ab-mascot-max-w: 280px;
    --ab-mascot-max-h: min(300px, 44vh);
    --ab-mascot-translate-x: -6rem;
    --ab-mascot-translate-y: 3rem;
}
.ab-hero-text .card-body {
    padding-bottom: 1.25rem !important;
    padding-top: 2rem !important;
}
.ab-hero-mascot-col {
    padding-top: 1rem;
    padding-bottom: 2rem;
}
.ab-hero-mascot-floating {
    display: block;
    object-fit: contain;
    object-position: bottom center;
    filter: drop-shadow(0 10px 28px rgba(26, 82, 168, .12));
}
@media (min-width: 768px) {
    .ab-hero-row.ab-hero-row--con-mascota {
        align-items: stretch;
        min-height: 23rem;
        padding-bottom: 5rem;
    }
    .ab-hero-text {
        position: relative;
        z-index: 2;
    }
    .ab-hero-text .card-body {
        padding-top: 2rem !important;
        padding-bottom: 1.5rem !important;
        padding-right: .5rem;
    }
    .ab-hero-mascot-col {
        position: relative;
        min-height: 0;
        padding: 0;
        align-self: stretch;
    }
    .ab-hero-mascot-floating {
        position: relative;
        z-index: 1;
        width: auto;
        height: auto;
        max-width: var(--ab-mascot-max-w, 280px);
        max-height: var(--ab-mascot-max-h, 300px);
        margin: 0 0 0 auto;
        object-position: bottom right;
        transform: translate(var(--ab-mascot-translate-x, -6rem), var(--ab-mascot-translate-y, 3rem));
    }
}
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
    white-space: nowrap;
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
    white-space: normal;
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
    .ab-option-icon {
        font-size: 3.5rem;
    }
    .ab-content {
        grid-template-columns: 1fr;
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
    .avance-bucket {
        padding-left: .35rem !important;
        padding-right: .35rem !important;
    }
    .ab-hero-row.ab-hero-row--con-mascota {
        min-height: 15rem;
    }
    .ab-hero-text .card-body {
        text-align: center;
        padding-top: 2rem !important;
        padding-bottom: 1rem !important;
        padding-left: 1rem !important;
        padding-right: 1rem !important;
    }
    .ab-hero-mascot-col {
        align-items: center !important;
        padding-top: 0;
    }
    .ab-hero-mascot-floating {
        margin: 0 auto;
        max-width: min(58vw, 200px);
        max-height: min(32vh, 200px);
        width: auto;
        height: auto;
        object-position: bottom center;
        transform: translateY(var(--ab-mascot-translate-y, 3rem));
    }
    .avance-bucket .ab-report-toolbar {
        align-items: stretch !important;
    }
    .avance-bucket h4 {
        width: 100%;
        font-size: 1.05rem;
        line-height: 1.25;
    }
    .avance-bucket h4 .badge {
        margin-left: 0 !important;
        margin-top: .35rem;
        width: max-content;
    }
    .avance-bucket .ab-report-toolbar > .d-flex {
        width: 100%;
        display: grid !important;
        grid-template-columns: 1fr 1fr;
        gap: .45rem !important;
    }
    .ab-corte-control {
        grid-column: 1 / -1;
        width: 100%;
        justify-content: space-between;
    }
    .ab-corte-control .form-select {
        flex: 1;
        min-width: 0;
    }
    #ab-status {
        grid-column: 1 / -1;
        justify-self: stretch;
        text-align: center;
    }
    #ab-refresh,
    .avance-bucket a.btn {
        width: 100%;
    }
    .ab-content {
        padding: .5rem;
        gap: .55rem;
    }
    .ab-resumen-grid {
        gap: .55rem;
    }
    .ab-panel {
        padding: .5rem;
    }
    .ab-matrix-panel + .ab-matrix-panel {
        margin-top: .55rem;
    }
    .ab-card-title {
        font-size: .86rem;
    }
    .ab-res-row {
        font-size: .68rem;
        grid-template-columns: minmax(120px, 1fr) 76px;
    }
    .ab-table-wrap {
        -webkit-overflow-scrolling: touch;
    }
    .ab-table {
        min-width: 920px;
        font-size: .64rem;
        table-layout: auto;
    }
    .ab-table th:not(:first-child),
    .ab-table td:not(:first-child) {
        min-width: 74px;
        white-space: nowrap;
    }
    .ab-table td:first-child,
    .ab-table th:first-child {
        min-width: 104px;
        width: 104px;
    }
}
</style>

<script>
(function () {
    const initialData = <?= $avance_bucket_initial_json ?? 'null'; ?>;
    const isHistorico = <?= $esHistoricoAvanceBucket ? 'true' : 'false'; ?>;
    const isEstresado = <?= $esEstresadoAvanceBucket ? 'true' : 'false'; ?>;
    const endpoint = isEstresado
        ? '/analitica/getAvanceBucketEstresadoJson'
        : (isHistorico ? '/analitica/getAvanceBucketHistoricoJson' : '/analitica/getAvanceBucketJson');
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
    function selectedSemana() {
        const select = el('ab-semana');
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
    function syncSemanaOptions(data) {
        if (!isHistorico) return;
        const select = el('ab-semana');
        if (!select || !data) return;
        const semanas = Array.isArray(data.semanas) ? data.semanas : [];
        const current = data.semana || select.value || '';
        select.innerHTML = semanas.map(semana => '<option value="' + escapeHtml(semana) + '">' + escapeHtml(semana) + '</option>').join('');
        if (current) {
            select.value = current;
        }
        select.disabled = semanas.length === 0;
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
            text: isEstresado ? 'Consultando Bucket estresado...' : (isHistorico ? 'Consultando historico de Avance Bucket...' : 'Consultando Avance Bucket...'),
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
        const rowLabel = matrix.row_label || 'Bucket Inicio';
        const totalLabel = matrix.total_label || 'Creditos';
        const headers = (buckets || []).map(b => '<th class="text-end">' + escapeHtml(shortBucket(b.bucket)) + '</th>').join('');
        const body = (matrix.filas || []).map(row => {
            const cells = (buckets || []).map(col => {
                const value = row.celdas ? row.celdas[col.bucket] : 0;
                const cls = Number(value || 0) === 0 ? 'ab-cell-zero' : (order[col.bucket] < order[row.bucket] ? 'ab-cell-improve' : (order[col.bucket] > order[row.bucket] ? 'ab-cell-worse' : ''));
                return '<td class="text-end ' + cls + '">' + formatValue(value, isPct) + '</td>';
            }).join('');
            return '<tr><td>' + escapeHtml(row.bucket || '') + '</td><td class="text-end fw-bold">' + formatValue(row.total, isPct) + '</td>' + cells + '</tr>';
        }).join('');
        const totals = (buckets || []).map(col => '<td class="text-end">' + formatValue(matrix.totales_columnas ? matrix.totales_columnas[col.bucket] : 0, isPct) + '</td>').join('');
        table.innerHTML = '<thead><tr><th>' + escapeHtml(rowLabel) + '</th><th class="text-end">' + escapeHtml(totalLabel) + '</th>' + headers + '</tr></thead>' +
            '<tbody>' + body + '<tr class="ab-total-row"><td>Total</td><td class="text-end">' + formatValue(matrix.total, isPct) + '</td>' + totals + '</tr></tbody>';
    }
    function render(data) {
        if (!data || data.success === false) {
            setStatus(false);
            showError((data && data.mensaje) ? data.mensaje : 'No se pudieron cargar los datos.');
            return;
        }
        hideError();
        syncCorteOptions(data);
        syncSemanaOptions(data);
        setStatus(true);
        const rowLabel = data.row_label || 'Bucket Inicio';
        const totalLabel = data.total_label || 'Creditos';
        const resumenInicioTitle = el('ab-resumen-inicio-title');
        const resumenPctTitle = el('ab-resumen-pct-title');
        const matrizSecundariaTitle = el('ab-matriz-secundaria-title');
        if (resumenInicioTitle) resumenInicioTitle.textContent = rowLabel + ' ' + totalLabel;
        if (resumenPctTitle) resumenPctTitle.textContent = rowLabel + ' %';
        if (matrizSecundariaTitle) matrizSecundariaTitle.textContent = data.matriz_secundaria_titulo || 'Matriz de avance bucket %';
        const corteBadge = el('ab-corte-badge');
        if (corteBadge) {
            const corteLabel = (data.dia_corte ? data.dia_corte + ' ' : '') + (data.corte || '');
            corteBadge.textContent = isEstresado ? (data.origen || 'mas_menos') : (isHistorico && data.semana ? data.semana : corteLabel);
        }
        const matrizCreditos = data.matriz_creditos || {};
        const matrizPorcentajes = data.matriz_porcentajes || {};
        matrizCreditos.row_label = rowLabel;
        matrizCreditos.total_label = totalLabel;
        matrizPorcentajes.row_label = rowLabel;
        matrizPorcentajes.total_label = totalLabel;
        const matrizInvertida = data.matriz_invertida || null;
        if (matrizInvertida) {
            matrizInvertida.row_label = 'Cierre Actual';
            matrizInvertida.total_label = totalLabel;
        }
        renderResumen('ab-resumen-inicio', data.resumen_inicio, data.total, 'count');
        renderResumen('ab-resumen-inicio-pct', data.resumen_inicio, data.total, 'pct');
        renderMatriz('ab-matriz-creditos', matrizCreditos, data.buckets, false);
        if (matrizInvertida) {
            renderMatriz('ab-matriz-porcentajes', matrizInvertida, data.buckets, false);
        } else {
            renderMatriz('ab-matriz-porcentajes', matrizPorcentajes, data.buckets, true);
        }
    }
    async function refresh() {
        const btn = el('ab-refresh');
        const select = el('ab-corte');
        if (btn) btn.disabled = true;
        if (select) select.disabled = true;
        showLoading();
        try {
            const params = new URLSearchParams();
            if (!isHistorico && !isEstresado && selectedCorte()) params.set('corte', selectedCorte());
            if (isHistorico && selectedSemana()) params.set('semana', selectedSemana());
            const suffix = params.toString() ? '?' + params.toString() : '';
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
    el('ab-semana')?.addEventListener('change', refresh);
    render(initialData);
})();
</script>
