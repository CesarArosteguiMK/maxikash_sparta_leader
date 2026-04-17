<?php
/** @var ?string $comp_error */
/** @var ?array $comp_payload */
/** @var string $comp_fecha_min */
/** @var string $comp_fecha_max */
$cErr = isset($comp_error) ? (string) $comp_error : '';
$cMin = isset($comp_fecha_min) ? htmlspecialchars((string) $comp_fecha_min, ENT_QUOTES, 'UTF-8') : '';
$cMax = isset($comp_fecha_max) ? htmlspecialchars((string) $comp_fecha_max, ENT_QUOTES, 'UTF-8') : '';
$comp_ok_inicio = ($cErr === '' && isset($comp_payload) && is_array($comp_payload));
$compInitialJson = json_encode($comp_payload ?? null, JSON_UNESCAPED_UNICODE);
if ($compInitialJson === false) {
    $compInitialJson = 'null';
}
?>
<div class="comp-av container-fluid py-3 px-2 px-md-3">
    <!-- Título + Volver: fuera de la tarjeta / tabla -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3 comp-av-page-header">
        <h4 class="mb-0 text-primary comp-av-heading d-flex align-items-center flex-wrap">
            <i class="fa-solid fa-chart-column me-2" aria-hidden="true"></i>
            <span>Comparativas — Avance por cortes</span>
        </h4>
        <div class="d-flex flex-wrap align-items-center gap-2 flex-shrink-0">
            <span id="comp-api-status" class="badge <?= $comp_ok_inicio ? 'bg-label-success' : 'bg-label-danger'; ?> align-middle"
                  title="Estado del endpoint de datos en esta aplicación">
                <?= $comp_ok_inicio ? 'Servicio: activo' : 'Servicio: no disponible'; ?>
            </span>
            <a href="/reporteria/comparativas" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-arrow-left me-1"></i>Volver
            </a>
        </div>
    </div>

    <div class="card shadow-sm border comp-av-card overflow-hidden">
        <div id="comp-av-export-root" class="comp-av-export-root bg-body">
        <div class="card-body border-bottom py-3 comp-av-toolbar">
            <div class="d-flex flex-wrap align-items-end justify-content-between gap-3">
                <div class="comp-av-logo-toolbar">
                    <img src="/assets/img/Logotipo-Maxikash-Outline.webp"
                         alt="Maxikash"
                         class="comp-av-logo"
                         width="260"
                         height="65"
                         loading="lazy"
                         decoding="async">
                </div>
                <div class="d-flex flex-wrap align-items-end gap-3 ms-auto justify-content-end text-end comp-av-toolbar-dia">
                    <span class="badge rounded-pill bg-label-primary border border-primary-subtle shadow-sm px-3 py-2 small fw-semibold align-self-center comp-dia-badge" id="comp-dia-badge">—</span>
                    <div class="text-start">
                        <label for="comp-fecha-ref" class="form-label small text-muted mb-1">Día a comparar</label>
                        <select class="form-select form-select-sm fw-semibold shadow-none" id="comp-fecha-ref" style="min-width: 13.5rem;"
                                title="Seleccione el día (lunes a hoy de la semana actual)"<?= $comp_ok_inicio ? '' : ' disabled'; ?>>
                            <option value="">Cargando días…</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="comp-av-table-stack">
            <div class="comp-av-chips-row">
                <table class="comp-av-chips-table" aria-hidden="true">
                    <colgroup>
                        <col style="width:9%">
                        <col style="width:9%">
                        <col style="width:9%">
                        <col style="width:9%">
                        <col style="width:9%">
                        <col style="width:9%">
                        <col style="width:8%">
                        <col style="width:9.5%">
                        <col style="width:9.5%">
                        <col style="width:9.5%">
                        <col style="width:9.5%">
                    </colgroup>
                    <tbody>
                        <tr>
                            <td colspan="2" class="comp-chip-cell">
                                <span class="badge rounded-pill comp-chip-pill comp-chip-pill-hist">
                                    Historial: <strong id="comp-chip-s2">—</strong>
                                </span>
                                <i class="fa-solid fa-chevron-right comp-chip-arrow" aria-hidden="true"></i>
                            </td>
                            <td colspan="2" class="comp-chip-cell">
                                <span class="badge rounded-pill comp-chip-pill comp-chip-pill-hist">
                                    Historial: <strong id="comp-chip-s1">—</strong>
                                </span>
                                <i class="fa-solid fa-chevron-right comp-chip-arrow" aria-hidden="true"></i>
                            </td>
                            <td colspan="2" class="comp-chip-cell">
                                <span class="badge rounded-pill comp-chip-pill comp-chip-pill-act">
                                    Actual: <strong id="comp-chip-sa">—</strong>
                                </span>
                            </td>
                            <td colspan="5" class="comp-chip-actions">
                                <button type="button" class="btn btn-primary btn-sm comp-btn-refresh" id="comp-btn-refresh"<?= $comp_ok_inicio ? '' : ' disabled'; ?>>
                                    <i class="fa-solid fa-rotate me-1"></i>Actualizar
                                </button>
                                <span class="small fw-semibold comp-av-actualizado ms-2" id="comp-ultima-act">—</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="comp-sin-servicio" class="<?= $comp_ok_inicio ? 'd-none' : ''; ?> alert alert-warning rounded-0 border-0 border-bottom mb-0 py-3 text-center small">
                <i class="fa-solid fa-plug-circle-xmark me-2"></i>
                No hay datos: el servicio de reporte no respondió o no está disponible. Cuando la base esté accesible, pulse <strong>Actualizar</strong>.
            </div>

            <div id="comp-table-area" class="<?= $comp_ok_inicio ? '' : 'd-none'; ?>">
                <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0 comp-av-table" style="font-size:0.78rem;">
                    <colgroup>
                        <col style="width:9%">
                        <col style="width:9%">
                        <col style="width:9%">
                        <col style="width:9%">
                        <col style="width:9%">
                        <col style="width:9%">
                        <col style="width:8%">
                        <col style="width:9.5%">
                        <col style="width:9.5%">
                        <col style="width:9.5%">
                        <col style="width:9.5%">
                    </colgroup>
                    <thead class="comp-av-thead">
                        <tr>
                            <th colspan="2" class="text-center small text-secondary comp-th-hist comp-sep-r" id="comp-hdr-s1">—</th>
                            <th colspan="2" class="text-center small text-secondary comp-th-hist comp-sep-r" id="comp-hdr-s2">—</th>
                            <th colspan="2" class="text-center small text-success comp-th-act comp-sep-r">Semana actual</th>
                            <th rowspan="2" class="text-center align-middle small text-primary comp-th-time comp-sep-r" id="comp-hdr-dia">—</th>
                            <th colspan="2" class="text-center small comp-th-var comp-sep-r" id="comp-hdr-var1">Variación vs —</th>
                            <th colspan="2" class="text-center small comp-th-var" id="comp-hdr-var2">Variación vs —</th>
                        </tr>
                        <tr>
                            <th class="text-end small comp-subcell">Créditos</th>
                            <th class="text-end small comp-subcell comp-sep-r">Cobrado</th>
                            <th class="text-end small comp-subcell">Créditos</th>
                            <th class="text-end small comp-subcell comp-sep-r">Cobrado</th>
                            <th class="text-end small bg-label-success comp-subcell" style="--bs-bg-opacity:.2">Créditos</th>
                            <th class="text-end small bg-label-success comp-subcell comp-sep-r" style="--bs-bg-opacity:.2">Cobrado</th>
                            <th class="text-end small comp-th-var-sub comp-subcell">Dif.</th>
                            <th class="text-end small comp-th-var-sub comp-subcell comp-sep-r">%</th>
                            <th class="text-end small comp-th-var-sub comp-subcell">Dif.</th>
                            <th class="text-end small comp-th-var-sub comp-subcell">%</th>
                        </tr>
                    </thead>
                    <tbody id="comp-tbody">
                        <tr><td colspan="11" class="text-center text-muted py-2">Cargando…</td></tr>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
        </div>

        <div class="card-body border-top py-2 py-md-3 text-center bg-body">
            <button type="button" class="btn btn-outline-secondary btn-sm" id="comp-btn-descargar-png"<?= $comp_ok_inicio ? '' : ' disabled'; ?>
                    title="PNG: logo, chips de semana y tabla. Sin día/Viernes, sin Actualizar ni hora Actualizado">
                <i class="fa-solid fa-download me-1" aria-hidden="true"></i>Descargar imagen (PNG)
            </button>
        </div>

        <div class="card-footer d-none comp-av-footer"></div>
    </div>
</div>

<style>
/* Tema claro (por defecto): tabla con bloques de color suaves */
.comp-av-page-header { min-width: 0; }
.comp-av-toolbar-dia { min-width: 0; }
.comp-av .comp-av-actualizado {
    color: var(--bs-success-text-emphasis) !important;
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
.comp-av .comp-dia-badge {
    letter-spacing: 0.02em;
    line-height: 1.2;
}
.comp-av-heading { line-height: 1.25; letter-spacing: -0.02em; }
.comp-av-export-root { overflow: hidden; }
/* Bordes de la tabla: mismos tokens que .table / .table-bordered del tema (gris claro) */
.comp-av-table {
    --bs-table-border-color: var(--bs-gray-200);
    --comp-sep-color: var(--bs-gray-300);
    border-collapse: collapse;
    border: 1px solid var(--bs-gray-400) !important;
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
/* Celdas más compactas (tema Sneat deja table-sm con mucho padding) */
.comp-av-table > :not(caption) > * > * {
    padding: 0.38rem 0.55rem !important;
    vertical-align: middle;
    line-height: 1.25;
}
.comp-av-table > tbody > tr > td {
    height: 2.25rem;
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
.comp-av-table-stack #comp-table-area .table-responsive {
    border: 0 !important;
    border-radius: 0 !important;
    overflow: hidden;
}
.comp-av .comp-av-chips-row {
    background-color: color-mix(in srgb, var(--bs-secondary-bg) 70%, var(--bs-white));
    border-bottom: 1px solid var(--bs-gray-400) !important;
    margin-bottom: 0;
}
.comp-av-chips-table {
    width: 100%;
    table-layout: fixed;
    border-collapse: collapse;
    margin: 0;
}
.comp-av-chips-table td {
    padding: 0.45rem 0.5rem;
    vertical-align: middle;
    position: relative;
}
.comp-av-chips-table .comp-chip-cell {
    text-align: center;
    white-space: nowrap;
}
.comp-av-chips-table .comp-chip-cell .comp-chip-pill {
    display: inline-block;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    vertical-align: middle;
}
.comp-av-chips-table .comp-chip-cell .comp-chip-arrow {
    position: absolute;
    right: -0.35rem;
    top: 50%;
    transform: translateY(-50%);
    z-index: 2;
    font-size: 0.7rem;
    color: var(--bs-primary);
    opacity: 0.55;
    line-height: 1;
    pointer-events: none;
}
.comp-av-chips-table .comp-chip-actions {
    text-align: right;
    white-space: nowrap;
    padding-right: 0.75rem !important;
}
.comp-av .btn.btn-primary.comp-btn-refresh.btn-sm {
    --bs-btn-padding-y: 0.2rem;
    --bs-btn-padding-x: 0.45rem;
    --bs-btn-font-size: 0.72rem;
    line-height: 1.2;
}
.comp-av .btn.btn-primary.comp-btn-refresh.btn-sm .fa-rotate {
    font-size: 0.7em;
    vertical-align: -0.05em;
}
.comp-av-table-stack .comp-av-table > thead.comp-av-thead > tr:first-child > th {
    border-top-width: 0 !important;
}
.comp-av .comp-chip-pill {
    padding: 0.4rem 0.8rem;
    font-size: 0.78rem;
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
.comp-av .comp-chip-arrow {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0 0.1rem;
    font-size: 1.05rem;
    font-weight: 600;
    line-height: 1;
    user-select: none;
}
/* Sin table-light: bordes explícitos (var() del tema a veces no llega al thead en Sneat) */
.comp-av-table > thead.comp-av-thead > tr > th {
    font-size: 0.66rem;
    font-weight: 700;
    letter-spacing: 0.02em;
    text-transform: uppercase;
    white-space: nowrap;
    border-style: solid !important;
    border-width: 1px !important;
    border-color: var(--bs-gray-200) !important;
}
.comp-av-table tbody td {
    background-clip: padding-box;
}
.comp-av-table > thead.comp-av-thead > tr:first-child > th {
    border-bottom-color: var(--bs-gray-200) !important;
}
.comp-av-table > thead.comp-av-thead > tr:nth-child(2) > th {
    border-top-color: var(--bs-gray-200) !important;
}
.comp-av-table > thead.comp-av-thead .comp-subcell {
    border-left: 1px solid var(--comp-sep-color) !important;
    border-right: 1px solid var(--comp-sep-color) !important;
}
.comp-av-table .comp-sep-r {
    border-right-width: 1px !important;
    border-right-color: var(--comp-sep-color) !important;
}
/* Fila 1 thead: borde izquierdo entre bloques de semana (mismo tono que .comp-sep-r en tbody) */
.comp-av-table > thead.comp-av-thead > tr:first-child > th.comp-th-hist + th.comp-th-hist,
.comp-av-table > thead.comp-av-thead > tr:first-child > th.comp-th-hist + th.comp-th-act {
    border-left: 1px solid var(--comp-sep-color) !important;
}
.comp-av .comp-th-hist {
    background-color: color-mix(in srgb, var(--bs-secondary-bg) 78%, var(--bs-white)) !important;
    color: var(--bs-secondary-text-emphasis) !important;
}
.comp-av .comp-th-act {
    background-color: color-mix(in srgb, var(--bs-success) 16%, var(--bs-white)) !important;
    color: var(--bs-success-text-emphasis) !important;
}
.comp-av .comp-th-time {
    background-color: color-mix(in srgb, var(--bs-primary) 12%, var(--bs-white)) !important;
    color: var(--bs-primary) !important;
    min-width: 4.8rem;
    width: 4.8rem;
}
.comp-av .comp-th-var {
    background-color: color-mix(in srgb, var(--bs-purple) 19%, var(--bs-white)) !important;
    color: var(--bs-purple) !important;
}
.comp-av .comp-th-var-sub {
    background-color: color-mix(in srgb, var(--bs-purple) 13%, var(--bs-white)) !important;
    color: var(--bs-purple) !important;
}
.comp-av-table tbody td.comp-col-time {
    text-align: center;
    background-color: color-mix(in srgb, var(--bs-primary) 9%, var(--bs-white));
    font-weight: 600;
    color: var(--bs-primary);
    white-space: nowrap;
    font-size: 0.74rem;
    line-height: 1.15;
}
.comp-av-table tbody td.comp-col-var {
    background-color: color-mix(in srgb, var(--bs-purple) 10%, var(--bs-white));
}
.comp-av-table tbody tr.comp-tr-noche td.comp-col-cob {
    background-color: color-mix(in srgb, var(--bs-success) 10%, var(--bs-white));
    font-style: italic;
}
.comp-num-empty { color: var(--bs-secondary-color) !important; }
.comp-av .comp-pos { color: var(--bs-success) !important; font-weight: 500; }
.comp-av .comp-neg { color: var(--bs-danger) !important; font-weight: 500; }

/* Modo oscuro: tokens del tema (dark-mode.css + Bootstrap en body.dark-mode) */
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
body.dark-mode .comp-av .comp-dia-badge {
    border-color: color-mix(in srgb, var(--bs-primary) 45%, var(--bs-border-color)) !important;
    box-shadow: none !important;
}
body.dark-mode .comp-av-toolbar .form-label,
body.dark-mode .comp-av-footer { color: var(--bs-secondary-color) !important; }
body.dark-mode .comp-av-toolbar .form-control {
    background-color: var(--bs-tertiary-bg);
    border-color: var(--bs-border-color);
    color: var(--bs-body-color);
}
body.dark-mode .comp-av-toolbar .form-select {
    background-color: var(--bs-tertiary-bg);
    border-color: var(--bs-border-color);
    color: var(--bs-body-color);
}
body.dark-mode .comp-av .comp-av-actualizado {
    color: var(--bs-success) !important;
}
body.dark-mode .comp-av-chips-row {
    background-color: var(--bs-body-bg) !important;
    border-bottom-color: var(--bs-border-color) !important;
}
body.dark-mode .comp-av-table-stack {
    border-color: var(--bs-border-color);
    margin: 0.5rem 1rem 1rem;
}
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
body.dark-mode .comp-av-table {
    --bs-table-border-color: var(--bs-gray-500);
    --comp-sep-color: color-mix(in srgb, var(--bs-gray-300) 68%, var(--bs-white));
    color: var(--bs-body-color);
    --bs-table-bg: transparent;
    border-collapse: collapse;
    border-color: var(--bs-border-color) !important;
}
body.dark-mode .comp-av-table-stack #comp-table-area .table-responsive {
    border: 0 !important;
}
body.dark-mode .comp-av-table .comp-sep-r {
    border-right-color: var(--comp-sep-color) !important;
}
body.dark-mode .comp-av-table > thead.comp-av-thead {
    --bs-table-bg: var(--bs-tertiary-bg);
    --bs-table-color: var(--bs-body-color);
    color: var(--bs-body-color);
}
body.dark-mode .comp-av-table > thead.comp-av-thead > tr > th {
    border-color: var(--bs-gray-500) !important;
}
body.dark-mode .comp-av-table > thead.comp-av-thead > tr:first-child > th {
    border-bottom-color: var(--bs-gray-500) !important;
}
body.dark-mode .comp-av-table > thead.comp-av-thead > tr:nth-child(2) > th {
    border-top-color: var(--bs-gray-500) !important;
}
body.dark-mode .comp-av-table > thead.comp-av-thead .comp-subcell {
    border-left-color: var(--comp-sep-color) !important;
    border-right-color: var(--comp-sep-color) !important;
}
body.dark-mode .comp-av .comp-th-act {
    background-color: var(--bs-secondary-bg) !important;
    color: var(--bs-success) !important;
}
body.dark-mode .comp-av .comp-th-time {
    background-color: var(--bs-secondary-bg) !important;
    color: var(--bs-link-color) !important;
}
body.dark-mode .comp-av .comp-th-var,
body.dark-mode .comp-av .comp-th-var-sub {
    background-color: color-mix(in srgb, var(--bs-purple) 28%, var(--bs-secondary-bg)) !important;
    color: var(--bs-purple) !important;
}
body.dark-mode .comp-av-table tbody tr:nth-child(odd) { background-color: var(--bs-secondary-bg); }
body.dark-mode .comp-av-table tbody tr:nth-child(even) { background-color: var(--bs-body-bg); }
body.dark-mode .comp-av-table tbody td.comp-col-time {
    background-color: var(--bs-tertiary-bg);
    color: var(--bs-primary);
}
body.dark-mode .comp-av-table tbody td.comp-col-var {
    background-color: color-mix(in srgb, var(--bs-purple) 16%, var(--bs-tertiary-bg));
}
body.dark-mode .comp-av-table tbody tr.comp-tr-noche td.comp-col-cob {
    background-color: var(--bs-tertiary-bg);
    color: var(--bs-success);
}
body.dark-mode .comp-av .comp-pos { color: var(--bs-success) !important; }
body.dark-mode .comp-av .comp-neg { color: var(--bs-danger) !important; }
body.dark-mode .comp-av .comp-num-empty { color: var(--bs-secondary-color) !important; }
body.dark-mode #comp-sin-servicio {
    background-color: var(--bs-secondary-bg) !important;
    color: var(--bs-warning);
    border-color: var(--bs-border-color) !important;
}
</style>

<script>
(function () {
    const COMP_OK_INICIO = <?= $comp_ok_inicio ? 'true' : 'false' ?>;
    const COMP_INITIAL = <?= $compInitialJson ?>;
    const FETCH_URL = '/reporteria/getComparativasAvanceSemanalJson';
    const HORAS_LABEL = ['07:30 a.m.','09:30 a.m.','11:30 a.m.','01:30 p.m.','02:30 p.m.','04:30 p.m.','06:30 p.m.','08:30 p.m.','11:50 p.m.'];

    let refreshTimer = null;
    function startAutoRefresh() {
        if (refreshTimer) return;
        refreshTimer = setInterval(cargar, 5 * 60 * 1000);
    }
    function stopAutoRefresh() {
        if (refreshTimer) { clearInterval(refreshTimer); refreshTimer = null; }
    }

    function setApiStatus(state) {
        const el = document.getElementById('comp-api-status');
        if (!el) return;
        if (state === 'check') {
            el.textContent = 'Servicio: comprobando…';
            el.className = 'badge bg-label-warning align-middle';
            el.title = 'Consultando el endpoint de datos…';
        } else if (state === 'ok') {
            el.textContent = 'Servicio: activo';
            el.className = 'badge bg-label-success align-middle';
            el.title = 'El endpoint respondió correctamente';
        } else {
            el.textContent = 'Servicio: no disponible';
            el.className = 'badge bg-label-danger align-middle';
            el.title = 'No hay respuesta válida del endpoint (revisa conexión o permisos)';
        }
    }

    function setServicioDisponible(ok) {
        const area = document.getElementById('comp-table-area');
        const off = document.getElementById('comp-sin-servicio');
        const inp = document.getElementById('comp-fecha-ref');
        const btn = document.getElementById('comp-btn-refresh');
        const btnPng = document.getElementById('comp-btn-descargar-png');
        if (ok) {
            area.classList.remove('d-none');
            off.classList.add('d-none');
            if (inp) inp.disabled = false;
            if (btn) btn.disabled = false;
            if (btnPng) btnPng.disabled = false;
            setApiStatus('ok');
            startAutoRefresh();
        } else {
            area.classList.add('d-none');
            off.classList.remove('d-none');
            if (inp) inp.disabled = true;
            if (btn) btn.disabled = false;
            if (btnPng) btnPng.disabled = true;
            setApiStatus('err');
            stopAutoRefresh();
        }
    }

    function loadHtmlToImage(done) {
        if (window.htmlToImage && typeof window.htmlToImage.toPng === 'function') {
            done(null);
            return;
        }
        var s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/html-to-image@1.11.11/dist/html-to-image.js';
        s.async = true;
        s.onload = function () {
            done((window.htmlToImage && typeof window.htmlToImage.toPng === 'function') ? null : new Error('htmlToImage'));
        };
        s.onerror = function () { done(new Error('script')); };
        document.head.appendChild(s);
    }

    function descargarPngTablero() {
        var root = document.getElementById('comp-av-export-root');
        var btnPng = document.getElementById('comp-btn-descargar-png');
        if (!root || !btnPng || btnPng.disabled) return;
        btnPng.disabled = true;
        loadHtmlToImage(function (errLoad) {
            if (errLoad || !window.htmlToImage || typeof window.htmlToImage.toPng !== 'function') {
                btnPng.disabled = false;
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'No se pudo cargar la utilidad de imagen', text: 'Intente de nuevo o revise su conexión.' });
                } else {
                    alert('No se pudo cargar la utilidad para generar la imagen.');
                }
                return;
            }
            var opts = {
                pixelRatio: 2,
                cacheBust: true,
                filter: function (node) {
                    if (!(node instanceof HTMLElement)) return true;
                    if (node.classList.contains('comp-av-toolbar-dia')) return false;
                    if (node.classList.contains('comp-chip-actions')) return false;
                    return true;
                }
            };
            window.htmlToImage.toPng(root, opts).then(function (dataUrl) {
                var inp = document.getElementById('comp-fecha-ref');
                var fv = (inp && inp.value) ? inp.value : 'tablero';
                var name = 'comparativa-avance-cortes-' + fv + '.png';
                var a = document.createElement('a');
                a.download = name;
                a.href = dataUrl;
                a.click();
                btnPng.disabled = false;
            }).catch(function (e) {
                console.warn('[comparativas] html-to-image:', e);
                btnPng.disabled = false;
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Error al generar la imagen', text: (e && e.message) ? e.message : 'Intente de nuevo.' });
                } else {
                    alert('Error al generar la imagen.');
                }
            });
        });
    }

    function mondayOfWeek(d) {
        const dt = new Date(d.getFullYear(), d.getMonth(), d.getDate());
        const day = dt.getDay();
        const diff = day === 0 ? -6 : 1 - day;
        dt.setDate(dt.getDate() + diff);
        return dt;
    }
    function toYMD(d) {
        return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    }
    function fmtFechaISO(iso) {
        if (!iso) return '';
        const p = iso.slice(0, 10).split('-');
        if (p.length !== 3) return iso;
        return p[2] + '/' + p[1] + '/' + p[0];
    }
    function nombreDiaEs(d) {
        const map = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        return map[d.getDay()] || '';
    }
    function buildDiaCompararOptions(preferIso) {
        const inp = document.getElementById('comp-fecha-ref');
        if (!inp) return '';
        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0);
        const mon = mondayOfWeek(hoy);
        const options = [];
        for (let d = new Date(mon.getTime()); d <= hoy; d.setDate(d.getDate() + 1)) {
            const iso = toYMD(d);
            const nombre = nombreDiaEs(d);
            options.push({ value: iso, label: nombre + ' — ' + fmtFechaISO(iso) });
        }
        const selectedActual = inp.value || '';
        const selected = options.some(o => o.value === preferIso) ? preferIso
            : (options.some(o => o.value === selectedActual) ? selectedActual : (options.length ? options[options.length - 1].value : ''));
        inp.innerHTML = options.map(function (o) {
            return '<option value="' + o.value + '"' + (o.value === selected ? ' selected' : '') + '>' + o.label + '</option>';
        }).join('');
        return selected;
    }

    function fmt(n) {
        return (n === null || n === undefined) ? '<span class="comp-num-empty">—</span>' : Math.abs(n).toLocaleString('es-MX');
    }
    function pct(a, r) {
        if (!r) return '<span class="comp-num-empty">—</span>';
        const p = ((a - r) / r * 100).toFixed(1);
        const cls = p >= 0 ? 'comp-pos' : 'comp-neg';
        return '<span class="' + cls + ' small">' + (p >= 0 ? '+' : '') + p + '%</span>';
    }
    function dif(a, r) {
        if (a === undefined || r === undefined) return '<span class="comp-num-empty">—</span>';
        const d = a - r;
        const cls = d > 0 ? 'comp-pos' : (d < 0 ? 'comp-neg' : '');
        return '<span class="' + cls + '">' + (d > 0 ? '+' : '') + d.toLocaleString('es-MX') + '</span>';
    }
    function cobStr(v, esNoche) {
        if (v === null || v === undefined) return '<span class="comp-num-empty">—</span>';
        const cls = v < 0 ? 'comp-neg' : (v > 0 ? 'comp-pos' : '');
        const signo = v > 0 ? '+' : '';
        return '<span class="' + cls + (esNoche ? ' fst-italic' : '') + '">' + signo + Math.abs(v).toLocaleString('es-MX') + '</span>';
    }

    function setStatus(m, ok) { void m; void ok; }

    function render(d) {
        if (!d || !d.semanas || !d.datos) return;
        const s1 = d.semanas[0], s2 = d.semanas[1], sa = d.semana_actual || 'Actual';
        const fechaSel = d.fecha_referencia ? d.fecha_referencia.slice(0, 10) : '';
        buildDiaCompararOptions(fechaSel);

        const badge = document.getElementById('comp-dia-badge');
        if (badge) {
            badge.textContent = d.es_hoy ? (d.dia + ' — Hoy') : (d.dia + ' — ' + fmtFechaISO(d.fecha_referencia));
        }
        document.getElementById('comp-hdr-dia').textContent = d.es_hoy ? d.dia : (d.dia + ' · ' + fmtFechaISO(d.fecha_referencia));
        document.getElementById('comp-hdr-s1').textContent = s1;
        document.getElementById('comp-hdr-s2').textContent = s2;
        /* Variación: columnas intercambiadas (izq = vs semana más reciente histórica, der = vs más antigua) */
        document.getElementById('comp-hdr-var1').textContent = 'Variación vs ' + s2;
        document.getElementById('comp-hdr-var2').textContent = 'Variación vs ' + s1;
        document.getElementById('comp-chip-s2').textContent = s1;
        document.getElementById('comp-chip-s1').textContent = s2;
        document.getElementById('comp-chip-sa').textContent = sa;

        const ck1 = 'creditos_' + s1, cb1 = 'cobrado_' + s1, ck2 = 'creditos_' + s2, cb2 = 'cobrado_' + s2;
        document.getElementById('comp-tbody').innerHTML = d.datos.map(function (row, i) {
            const esNoche = (i === 0);
            const c1 = row[ck1], v1 = row[cb1], c2 = row[ck2], v2 = row[cb2];
            const ca = row.creditos_actual, va = row.cobrado_actual;
            const caStr = (ca === 0 && i > 0) ? '<span class="comp-num-empty">—</span>' : fmt(ca);
            const vaStr = (ca === 0 && i > 0) ? '<span class="comp-num-empty">—</span>' : cobStr(va, esNoche);
            const trCls = esNoche ? ' class="comp-tr-noche"' : '';
            const cobCls = esNoche ? ' comp-col-cob' : '';
            return '<tr' + trCls + '><td class="text-end">' + fmt(c1) + '</td><td class="text-end comp-sep-r' + cobCls + '">' + cobStr(v1, esNoche) + '</td>' +
                '<td class="text-end">' + fmt(c2) + '</td><td class="text-end comp-sep-r' + cobCls + '">' + cobStr(v2, esNoche) + '</td>' +
                '<td class="text-end">' + caStr + '</td><td class="text-end comp-sep-r' + cobCls + '">' + vaStr + '</td>' +
                '<td class="comp-col-time comp-sep-r">' + HORAS_LABEL[i] + (esNoche ? '<br><span class="comp-num-empty" style="font-size:.65rem;font-style:normal">nocturno</span>' : '') + '</td>' +
                '<td class="comp-col-var text-end">' + dif(ca, c2) + '</td><td class="comp-col-var text-end comp-sep-r">' + pct(ca, c2) + '</td>' +
                '<td class="comp-col-var text-end">' + dif(ca, c1) + '</td><td class="comp-col-var text-end">' + pct(ca, c1) + '</td></tr>';
        }).join('');
    }

    async function cargar() {
        const btn = document.getElementById('comp-btn-refresh');
        if (btn) { btn.disabled = true; }
        setApiStatus('check');
        try {
            buildDiaCompararOptions();
            const inp = document.getElementById('comp-fecha-ref');
            const fv = inp && !inp.disabled ? inp.value : '';
            const u = FETCH_URL + (fv ? ('?fecha=' + encodeURIComponent(fv)) : '');
            const r = await fetch(u, { credentials: 'same-origin' });
            const tx = await r.text();
            let d;
            try { d = JSON.parse(tx); } catch (e2) { throw new Error(tx.slice(0, 200)); }
            if (!r.ok) {
                const det = d && d.detail;
                throw new Error(typeof det === 'string' ? det : (det ? JSON.stringify(det) : ('HTTP ' + r.status)));
            }
            render(d);
            setServicioDisponible(true);
            const ua = document.getElementById('comp-ultima-act');
            if (ua) ua.textContent = 'Actualizado: ' + new Date().toLocaleTimeString('es-MX');
        } catch (e) {
            console.warn('[comparativas] Error al cargar:', (e && e.message) ? e.message : e);
            setServicioDisponible(false);
        }
        if (btn) { btn.disabled = false; }
    }

    var compFechaEl = document.getElementById('comp-fecha-ref');
    if (compFechaEl) compFechaEl.addEventListener('change', function () { if (!compFechaEl.disabled) cargar(); });
    document.getElementById('comp-btn-refresh').addEventListener('click', cargar);
    var btnPng0 = document.getElementById('comp-btn-descargar-png');
    if (btnPng0) btnPng0.addEventListener('click', descargarPngTablero);

    const initFecha = COMP_INITIAL && COMP_INITIAL.fecha_referencia ? COMP_INITIAL.fecha_referencia.slice(0, 10) : '';
    buildDiaCompararOptions(initFecha);
    if (COMP_OK_INICIO && COMP_INITIAL) {
        render(COMP_INITIAL);
        setServicioDisponible(true);
        var ua0 = document.getElementById('comp-ultima-act');
        if (ua0) ua0.textContent = 'Actualizado: ' + new Date().toLocaleTimeString('es-MX');
    } else if (COMP_OK_INICIO) {
        cargar();
    } else {
        setServicioDisponible(false);
    }
})();
</script>
