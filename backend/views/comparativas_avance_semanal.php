<?php
/** @var ?string $comp_error */
/** @var ?array $comp_payload */
/** @var string $comp_fecha_min */
/** @var string $comp_fecha_max */
/** @var bool $comp_placeholder_cdmx GET sin hoy_mx (compat; UI usa comp_esperando_hoy_mx). */
/** @var bool $comp_ok_inicio */
/** @var bool $comp_esperando_hoy_mx Calendario CDMX pendiente del navegador y sin error. */
/** @var bool $comp_ui_datos Tabla/herramientas habilitadas (datos iniciales o placeholder). */
/** @var string $comp_initial_json Fragmento JSON listo para incrustar en JS (sin comillas extra). */
/** @var string $comp_rango_json Objeto JSON {min,max} escapado para JS. */
$cErr = isset($comp_error) ? (string) $comp_error : '';
?>
<div class="comp-av container-fluid py-3 px-2 px-md-3">
    <!-- Título + Volver: fuera de la tarjeta / tabla -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3 comp-av-page-header">
        <h4 class="mb-0 text-primary comp-av-heading d-flex align-items-center flex-wrap">
            <i class="fa-solid fa-chart-column me-2" aria-hidden="true"></i>
            <span>Comparativas — Avance por cortes</span>
        </h4>
        <div class="d-flex flex-wrap align-items-center gap-2 flex-shrink-0">
            <span id="comp-api-status" class="badge <?php
                if ($comp_ok_inicio) {
                    echo 'bg-label-success';
                } elseif ($comp_esperando_hoy_mx) {
                    echo 'bg-label-warning';
                } else {
                    echo 'bg-label-danger';
                }
            ?> align-middle"
                  title="<?= $comp_esperando_hoy_mx ? 'Obteniendo calendario Ciudad de México desde el navegador' : 'Estado del endpoint de datos en esta aplicación'; ?>">
                <?php
                if ($comp_ok_inicio) {
                    echo 'Servicio: activo';
                } elseif ($comp_esperando_hoy_mx) {
                    echo 'Calendario CDMX…';
                } else {
                    echo 'Servicio: no disponible';
                }
                ?>
            </span>
            <a href="/analitica/comparativas" class="btn btn-outline-secondary btn-sm">
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
                                title="Desde el martes de inicio de la semana operativa anterior hasta hoy (calendario Ciudad de México; semana martes–lunes)"<?= $comp_ui_datos ? '' : ' disabled'; ?>>
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
                                <button type="button" class="btn btn-primary btn-sm comp-btn-refresh" id="comp-btn-refresh"<?= $comp_ui_datos ? '' : ' disabled'; ?>>
                                    <i class="fa-solid fa-rotate me-1"></i>Actualizar
                                </button>
                                <span class="small fw-semibold comp-av-actualizado ms-2" id="comp-ultima-act">—</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="comp-sin-servicio" class="<?= $comp_ui_datos ? 'd-none' : ''; ?> alert alert-warning rounded-0 border-0 border-bottom mb-0 py-3 text-center small">
                <i class="fa-solid fa-plug-circle-xmark me-2"></i>
                No hay datos: el servicio de reporte no respondió o no está disponible. Cuando la base esté accesible, pulse <strong>Actualizar</strong>.
                <?php if ($cErr !== ''): ?>
                <p class="small text-secondary mb-0 mt-2 px-2"><strong>Detalle (carga inicial):</strong> <?= htmlspecialchars($cErr, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>
                <p class="small text-muted mb-0 mt-2 px-2">Si el detalle no aclara el fallo, revise el <strong>log de PHP</strong> (líneas <code>Reporteria::comparativasAvanceSemanal</code> o <code>getComparativasAvanceSemanalJson</code>) y en el navegador la pestaña <strong>Red</strong> al pulsar Actualizar (código HTTP y cuerpo de la respuesta).</p>
            </div>

            <div id="comp-table-area" class="<?= $comp_ui_datos ? '' : 'd-none'; ?>">
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
                            <th colspan="2" class="text-center small text-success comp-th-act comp-sep-r" id="comp-hdr-sa">Semana actual</th>
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
    const COMP_INITIAL = <?= $comp_initial_json ?>;
    const COMP_RANGO = <?= $comp_rango_json ?>;
    const FETCH_URL = '/analitica/getComparativasAvanceSemanalJson';
    const HORAS_LABEL = ['07:30 a.m.','09:30 a.m.','11:30 a.m.','01:30 p.m.','02:30 p.m.','04:30 p.m.','06:30 p.m.','08:30 p.m.','11:50 p.m.'];

    let refreshTimer = null;
    /** Último JSON válido (para no ocultar la tabla si falla un refresco puntual). */
    let lastGoodData = (COMP_OK_INICIO && COMP_INITIAL) ? COMP_INITIAL : null;
    let compRango = {
        min: (COMP_INITIAL && COMP_INITIAL.fecha_min) || (COMP_RANGO && COMP_RANGO.min) || '',
        max: (COMP_INITIAL && COMP_INITIAL.fecha_max) || (COMP_RANGO && COMP_RANGO.max) || ''
    };
    /** Evita aplicar respuestas viejas si el usuario cambió de fecha rápido o hubo varias peticiones. */
    let cargarSeq = 0;

    function sleep(ms) {
        return new Promise(function (resolve) { setTimeout(resolve, ms); });
    }

    /** Fecha calendario hoy en Ciudad de México (Intl + America/Mexico_City), sin usar reloj del servidor. */
    function ymdHoyCiudadMexico() {
        try {
            var fmt = new Intl.DateTimeFormat('en-CA', { timeZone: 'America/Mexico_City', year: 'numeric', month: '2-digit', day: '2-digit' });
            var parts = fmt.formatToParts(new Date());
            var y = '', mo = '', da = '';
            for (var i = 0; i < parts.length; i++) {
                if (parts[i].type === 'year') y = parts[i].value;
                if (parts[i].type === 'month') mo = parts[i].value;
                if (parts[i].type === 'day') da = parts[i].value;
            }
            return (y && mo && da) ? (y + '-' + mo + '-' + da) : '';
        } catch (e) {
            return '';
        }
    }

    function sliceYmd(v) {
        if (v == null || v === '') return '';
        var s = String(v).slice(0, 10);
        return /^\d{4}-\d{2}-\d{2}$/.test(s) ? s : '';
    }

    function ymdMax(a, b) {
        a = sliceYmd(a);
        b = sliceYmd(b);
        if (!a) return b;
        if (!b) return a;
        return a >= b ? a : b;
    }

    function ymdMin(a, b) {
        a = sliceYmd(a);
        b = sliceYmd(b);
        if (!a) return b;
        if (!b) return a;
        return a <= b ? a : b;
    }

    function mergeCompRango(minYmd, maxYmd) {
        var nMin = ymdMin(compRango && compRango.min, minYmd);
        var nMax = ymdMax(compRango && compRango.max, maxYmd);
        compRango = { min: nMin || '', max: nMax || '' };
    }

    /**
     * hoy_mx: el mayor Y-m-d entre fuentes (Intl, último JSON del servidor, carga inicial, rango).
     * Evita que un hoy “bajo” (p. ej. 13/04) limite fecha_max y bloquee el lunes actual (20/04) en el combo.
     */
    function hoyMxParaPeticion() {
        var c = [];
        var a = sliceYmd(ymdHoyCiudadMexico());
        if (a) c.push(a);
        if (lastGoodData) {
            a = sliceYmd(lastGoodData.hoy_calendario_cdmx);
            if (a) c.push(a);
        }
        if (COMP_INITIAL) {
            a = sliceYmd(COMP_INITIAL.hoy_calendario_cdmx);
            if (a) c.push(a);
        }
        a = sliceYmd(compRango && compRango.max);
        if (a) c.push(a);
        if (!c.length) return '';
        c.sort();
        return c[c.length - 1];
    }

    function buildComparativaUrl(fv, hoyForzado) {
        var q = [];
        var hoy = ymdMax(sliceYmd(hoyForzado), hoyMxParaPeticion());
        var fecha = sliceYmd(fv);
        if (fecha && (!hoy || fecha > hoy)) hoy = fecha;
        if (hoy) q.push('hoy_mx=' + encodeURIComponent(hoy));
        if (fecha) q.push('fecha=' + encodeURIComponent(fecha));
        return FETCH_URL + (q.length ? ('?' + q.join('&')) : '');
    }

    /**
     * Reintenta la petición ante fallos de red o 5xx (errores transitorios).
     * @returns {Promise<object>}
     */
    async function fetchComparativaJson(fv, hoyForzado) {
        const u = buildComparativaUrl(fv, hoyForzado);
        const intentos = 3;
        var lastErr;
        for (var i = 0; i < intentos; i++) {
            try {
                var r = await fetch(u, { credentials: 'same-origin' });
                var tx = await r.text();
                var d;
                try { d = JSON.parse(tx); } catch (e2) { throw new Error('Respuesta no JSON'); }
                if (!r.ok) {
                    var det = d && d.detail;
                    var msg = typeof det === 'string' ? det : (det ? JSON.stringify(det) : ('HTTP ' + r.status));
                    var err = new Error(msg);
                    if (r.status >= 400 && r.status < 500) err.noRetry = true;
                    throw err;
                }
                return d;
            } catch (e) {
                lastErr = e;
                if (e && e.noRetry) break;
                if (i < intentos - 1) await sleep(350 * (i + 1));
            }
        }
        throw lastErr;
    }
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
        } else if (state === 'degraded') {
            el.textContent = 'Servicio: sin actualizar';
            el.className = 'badge bg-label-warning align-middle';
            el.title = 'No se pudo obtener una lectura nueva; se muestran los últimos datos cargados.';
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

    function fmtFechaISO(iso) {
        if (!iso) return '';
        const p = iso.slice(0, 10).split('-');
        if (p.length !== 3) return iso;
        return p[2] + '/' + p[1] + '/' + p[0];
    }

    /** ISO N: 1=lunes … 7=domingo (misma regla que PHP format('N') con fecha civil en UTC). */
    function isoNDesdeYmd(ymd) {
        var p = (ymd || '').slice(0, 10).split('-').map(Number);
        if (p.length !== 3 || p.some(isNaN)) return 1;
        var dt = new Date(Date.UTC(p[0], p[1] - 1, p[2]));
        var wd = dt.getUTCDay();
        return wd === 0 ? 7 : wd;
    }

    function ymdAddDays(ymd, delta) {
        var p = (ymd || '').slice(0, 10).split('-').map(Number);
        if (p.length !== 3 || p.some(isNaN)) return '';
        var t = Date.UTC(p[0], p[1] - 1, p[2]) + delta * 86400000;
        var dt = new Date(t);
        return dt.getUTCFullYear() + '-' + String(dt.getUTCMonth() + 1).padStart(2, '0') + '-' + String(dt.getUTCDate()).padStart(2, '0');
    }

    /** Primer martes de la semana operativa mar–lun que contiene ymd (igual que PHP martesInicioSemanaNegocio). */
    function martesInicioSemanaNegocioDesdeYmd(ymd) {
        var N = isoNDesdeYmd(ymd);
        var retro = N === 1 ? 6 : (N - 2);
        return ymdAddDays(ymd, -retro);
    }
    /** Días calendario entre minYmd y maxYmd (Y-m-d), mismo criterio que PHP (sin depender de la zona del navegador). */
    function enumerateDiasCalendario(minYmd, maxYmd) {
        const map = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        const ps = (minYmd || '').slice(0, 10).split('-').map(Number);
        const pe = (maxYmd || '').slice(0, 10).split('-').map(Number);
        if (ps.length !== 3 || pe.length !== 3 || ps.some(isNaN) || pe.some(isNaN)) return [];
        let t = Date.UTC(ps[0], ps[1] - 1, ps[2]);
        const endT = Date.UTC(pe[0], pe[1] - 1, pe[2]);
        const out = [];
        while (t <= endT) {
            const dt = new Date(t);
            const iso = dt.getUTCFullYear() + '-' + String(dt.getUTCMonth() + 1).padStart(2, '0') + '-' + String(dt.getUTCDate()).padStart(2, '0');
            out.push({ value: iso, label: (map[dt.getUTCDay()] || '') + ' — ' + fmtFechaISO(iso) });
            t += 86400000;
        }
        return out;
    }
    function buildDiaCompararOptions(preferIso) {
        const inp = document.getElementById('comp-fecha-ref');
        if (!inp) return '';
        const minY = compRango.min || (COMP_RANGO && COMP_RANGO.min) || '';
        const maxY = compRango.max || (COMP_RANGO && COMP_RANGO.max) || '';
        const options = enumerateDiasCalendario(minY, maxY);
        const selectedActual = inp.value || '';
        const selected = options.some(o => o.value === preferIso) ? preferIso
            : (options.some(o => o.value === selectedActual) ? selectedActual : (options.length ? options[options.length - 1].value : ''));
        var maxS = sliceYmd(maxY);
        var martesAct = maxS ? martesInicioSemanaNegocioDesdeYmd(maxS) : '';
        var hayAnt = martesAct && options.some(function (o) { return o.value < martesAct; });
        var hayCur = martesAct && options.some(function (o) { return o.value >= martesAct; });
        var html;
        if (hayAnt && hayCur) {
            var martesAnt = ymdAddDays(martesAct, -7);
            var lunesAnt = ymdAddDays(martesAct, -1);
            var lunesAct = ymdAddDays(martesAct, 6);
            var labAnt = 'Semana anterior (mar–lun): ' + fmtFechaISO(martesAnt) + ' – ' + fmtFechaISO(lunesAnt);
            var labCur = 'Semana en curso (mar–lun): ' + fmtFechaISO(martesAct) + ' – ' + fmtFechaISO(lunesAct);
            html = '<optgroup label="' + labAnt.replace(/&/g, '&amp;').replace(/"/g, '&quot;') + '">';
            options.forEach(function (o) {
                if (o.value < martesAct) {
                    html += '<option value="' + o.value + '"' + (o.value === selected ? ' selected' : '') + '>' + o.label + '</option>';
                }
            });
            html += '</optgroup><optgroup label="' + labCur.replace(/&/g, '&amp;').replace(/"/g, '&quot;') + '">';
            options.forEach(function (o) {
                if (o.value >= martesAct) {
                    html += '<option value="' + o.value + '"' + (o.value === selected ? ' selected' : '') + '>' + o.label + '</option>';
                }
            });
            html += '</optgroup>';
        } else {
            html = options.map(function (o) {
                return '<option value="' + o.value + '"' + (o.value === selected ? ' selected' : '') + '>' + o.label + '</option>';
            }).join('');
        }
        inp.innerHTML = html;
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
    /** % respecto al histórico: (histórico − actual) / histórico (coherente con dif hist−actual en créditos). */
    function pctHistVsActual(actual, hist) {
        if (!hist) return '<span class="comp-num-empty">—</span>';
        const p = ((hist - actual) / hist * 100).toFixed(1);
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
        if (d.fecha_min && d.fecha_max) {
            mergeCompRango(d.fecha_min, d.fecha_max);
        }
        const s1 = d.semanas[0], s2 = d.semanas[1], sa = d.semana_actual || 'Actual';
        const disp = (Array.isArray(d.semanas_display) && d.semanas_display.length >= 3)
            ? d.semanas_display
            : [s1, s2, sa];
        const lblHistAnt = disp[0], lblHistRec = disp[1], lblActualSem = disp[2];
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
        /* Chips (Historial / Actual): rango de periodo; tabla: etiquetas Semana ISO. */
        document.getElementById('comp-chip-s2').textContent = lblHistAnt;
        document.getElementById('comp-chip-s1').textContent = lblHistRec;
        document.getElementById('comp-chip-sa').textContent = lblActualSem;
        var hdrSa = document.getElementById('comp-hdr-sa');
        if (hdrSa) hdrSa.textContent = sa;

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
                '<td class="comp-col-var text-end">' + dif(c2, ca) + '</td><td class="comp-col-var text-end comp-sep-r">' + pctHistVsActual(ca, c2) + '</td>' +
                '<td class="comp-col-var text-end">' + dif(c1, ca) + '</td><td class="comp-col-var text-end">' + pctHistVsActual(ca, c1) + '</td></tr>';
        }).join('');
    }

    async function cargar() {
        const seq = ++cargarSeq;
        const btn = document.getElementById('comp-btn-refresh');
        if (btn) { btn.disabled = true; }
        setApiStatus('check');
        try {
            const inp = document.getElementById('comp-fecha-ref');
            const fv = inp && !inp.disabled ? inp.value : '';
            var d;
            try {
                d = await fetchComparativaJson(fv);
            } catch (eTry) {
                if (seq !== cargarSeq) throw eTry;
                var fh = lastGoodData && sliceYmd(lastGoodData.hoy_calendario_cdmx);
                var hMain = hoyMxParaPeticion();
                if (fh && fh !== hMain) {
                    if (seq !== cargarSeq) throw eTry;
                    d = await fetchComparativaJson(fv, fh);
                } else {
                    throw eTry;
                }
            }
            if (seq !== cargarSeq) return;
            if (!d || !d.semanas || !d.datos) {
                throw new Error('Respuesta incompleta del servidor');
            }
            render(d);
            lastGoodData = d;
            setServicioDisponible(true);
            const ua = document.getElementById('comp-ultima-act');
            if (ua) ua.textContent = 'Actualizado: ' + new Date().toLocaleTimeString('es-MX');
        } catch (e) {
            if (seq !== cargarSeq) return;
            console.warn('[comparativas] Error al cargar:', (e && e.message) ? e.message : e);
            if (lastGoodData && lastGoodData.semanas && lastGoodData.datos) {
                render(lastGoodData);
                setServicioDisponible(true);
                setApiStatus('degraded');
            } else {
                setServicioDisponible(false);
            }
        } finally {
            if (seq === cargarSeq && btn) { btn.disabled = false; }
        }
    }

    var compFechaEl = document.getElementById('comp-fecha-ref');
    if (compFechaEl) compFechaEl.addEventListener('change', function () { if (!compFechaEl.disabled) cargar(); });
    document.getElementById('comp-btn-refresh').addEventListener('click', cargar);
    var btnPng0 = document.getElementById('comp-btn-descargar-png');
    if (btnPng0) btnPng0.addEventListener('click', descargarPngTablero);

    const initFecha = COMP_INITIAL && COMP_INITIAL.fecha_referencia ? COMP_INITIAL.fecha_referencia.slice(0, 10) : '';
    buildDiaCompararOptions(initFecha);
    var mxHoy = ymdHoyCiudadMexico();
    var hoyEnPayload = COMP_INITIAL && COMP_INITIAL.hoy_calendario_cdmx ? String(COMP_INITIAL.hoy_calendario_cdmx).slice(0, 10) : '';
    var debeSincronizar = !mxHoy || !COMP_OK_INICIO || !COMP_INITIAL || (hoyEnPayload !== mxHoy);
    if (!debeSincronizar) {
        render(COMP_INITIAL);
        setServicioDisponible(true);
        var ua0 = document.getElementById('comp-ultima-act');
        if (ua0) ua0.textContent = 'Actualizado: ' + new Date().toLocaleTimeString('es-MX');
    } else {
        cargar();
    }
})();
</script>
