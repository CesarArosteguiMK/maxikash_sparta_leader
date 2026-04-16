<?php
/** @var string $fechaIniDefault Y-m-d */
/** @var string $fechaFinDefault Y-m-d */
/** @var string $datosInicialesJson */
$hoyCvFp = new \DateTimeImmutable('today');
$dowCvFp = (int) $hoyCvFp->format('N');
$lunCvFp = $hoyCvFp->modify('-' . ($dowCvFp - 1) . ' days');
$fechaIniDefault = isset($fechaIniDefault) && is_string($fechaIniDefault) && $fechaIniDefault !== ''
    ? $fechaIniDefault
    : $lunCvFp->format('Y-m-d');
$fechaFinDefault = isset($fechaFinDefault) && is_string($fechaFinDefault) && $fechaFinDefault !== ''
    ? $fechaFinDefault
    : $hoyCvFp->format('Y-m-d');
$datosInicialesJson = $datosInicialesJson ?? '{}';
?>
<style>
    /* ─── Prefijo: cv-  (convenios-estadísticas) ───────────── */
    .cv-tip-btn {
        position: absolute;
        top: 4px;
        right: 4px;
        z-index: 2;
        border: none;
        background: transparent;
        padding: 0 2px;
        margin: 0;
        line-height: 1;
        font-size: 11px;
        color: #8a96a8;
        cursor: help;
        opacity: 0.92;
    }
    .cv-tip-btn:hover,
    .cv-tip-btn:focus { color: #1a3a5c; opacity: 1; }
    .cv-tip-btn:focus { outline: none; box-shadow: 0 0 0 2px rgba(26,58,92,0.2); border-radius: 4px; }
    .cv-tip-btn.cv-tip-inline {
        position: static;
        flex-shrink: 0;
        align-self: flex-start;
        margin-top: 0;
    }
    .tooltip.cv-tip-kpi .tooltip-inner {
        max-width: min(320px, 92vw);
        text-align: left;
        font-size: 12px;
        line-height: 1.45;
    }
    /* Escala única: mismo tamaño para KPI estándar, mini-stats y donut/radial */
    .cv-est-outer {
        --cv-kpi-num: 1.55rem;
        --cv-kpi-chart: 220px;
    }
    .cv-kpi-val-num {
        font-size: var(--cv-kpi-num) !important;
        font-weight: 800;
        line-height: 1.12;
        letter-spacing: -0.02em;
    }
    .cv-kpi-chart-slot {
        width: var(--cv-kpi-chart);
        height: var(--cv-kpi-chart);
        min-height: var(--cv-kpi-chart);
        max-width: 100%;
        margin-left: auto;
        margin-right: auto;
    }
    .cv-desp-kpi-inner {
        padding: 10px 12px !important;
        text-align: center;
        height: 100%;
    }
    .cv-desp-kpi-stack {
        min-height: 118px;
    }
    /* KPI strip global */
    .cv-kpi-strip {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 16px;
    }
    .cv-kpi-strip > .cv-kpi-mini {
        position: relative;
        background: #ffffff;
        border: 1px solid #dde3ec;
        border-radius: 10px;
        padding: 12px 14px;
        text-align: center;
        flex: 1 1 0;
        min-width: 120px;
    }
    @media (max-width: 991.98px) {
        .cv-kpi-strip > .cv-kpi-mini { flex: 1 1 calc(50% - 6px); min-width: 108px; }
    }
    /* Cards clickeables de nuevos (misma escala que KPI estándar) */
    .cv-nuevo-card,
    .cv-nuevo-total {
        flex: 1 1 0;
        min-width: 92px;
        max-width: 148px;
        padding: 8px 10px !important;
        text-align: center;
    }
    .cv-nuevo-card {
        cursor: pointer;
        border: 2px solid transparent !important;
        border-radius: 8px;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .cv-nuevo-card:hover { border-color: rgba(26,58,92,0.25) !important; box-shadow: 0 2px 8px rgba(26,58,92,0.08); }
    .cv-nuevo-card.cv-nuevo-card-active { border-color: #2ecc8b !important; box-shadow: 0 0 0 1px rgba(46,204,139,0.35); }
    .cv-nuevo-card:focus-visible { outline: 2px solid #2ecc8b; outline-offset: 2px; }
    .cv-nuevo-cards-row {
        justify-content: flex-start;
    }
    /* Semanas badges */
    .cv-sem-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }
    .cv-sem-badge .cv-sem-num { font-size: var(--cv-kpi-num); font-weight: 800; }

    /* ─── MODO OSCURO (html.dark-mode) ──────────────────── */
    html.dark-mode .cv-est-outer { background: #0f172a !important; }
    html.dark-mode .cv-kpi-mini { background: #1e293b !important; border-color: #334155 !important; }
    html.dark-mode .cv-panel { background: #1e293b !important; border-color: #334155 !important; }
    html.dark-mode .cv-panel-sem { background: #1e293b !important; border-color: #334155 !important; }
    html.dark-mode .cv-card-rec,
    html.dark-mode .cv-card-pen { background: #1e293b !important; border-color: #334155 !important; }
    html.dark-mode .cv-nuevo-card { background: #253344 !important; border-color: #334155 !important; }
    html.dark-mode .cv-nuevo-card:hover { border-color: rgba(46,204,139,0.4) !important; box-shadow: 0 2px 8px rgba(0,0,0,0.3) !important; }
    html.dark-mode .cv-nuevo-total { background: #253344 !important; border-color: #334155 !important; }
    html.dark-mode .cv-sep,
    html.dark-mode #cvNuevosDetalleWrap { border-top-color: #334155 !important; }
    /* Textos con colores light-mode → adaptar en dark */
    html.dark-mode .cv-est-outer *[style*="color:#6b7a90"] { color: #94a3b8 !important; }
    html.dark-mode .cv-est-outer *[style*="color:#5a6a7d"] { color: #94a3b8 !important; }
    html.dark-mode .cv-est-outer *[style*="color:#1a3a5c"] { color: #e2e8f0 !important; }
    /* Badges de semanas */
    html.dark-mode .cv-sem-badge[style*="0d5c3a"] { background: rgba(46,204,139,0.15) !important; color: #4ade80 !important; }
    html.dark-mode .cv-sem-badge[style*="7a1111"]  { background: rgba(231,76,60,0.15) !important; color: #f87171 !important; }
    html.dark-mode .cv-sem-badge[style*="1a3a5c"]  { background: rgba(148,163,184,0.12) !important; color: #94a3b8 !important; }
    html.dark-mode .cv-sem-badge[style*="7a5000"]  { background: rgba(240,165,0,0.15) !important; color: #fbbf24 !important; }
    html.dark-mode .cv-sem-badge[style*="5b2d8e"]  { background: rgba(192,132,252,0.15) !important; color: #c084fc !important; }
    html.dark-mode .cv-sem-badge[style*="6b7a90"]  { background: rgba(148,163,184,0.1) !important; color: #64748b !important; }
    /* ApexCharts dentro de cv-est-outer */
    html.dark-mode .cv-est-outer .apexcharts-canvas text { fill: #94a3b8 !important; }
    html.dark-mode .cv-est-outer .apexcharts-radialbar-track path { stroke: #334155 !important; }
    html.dark-mode .cv-est-outer .apexcharts-gridline { stroke: #334155 !important; }
    html.dark-mode .cv-est-outer .apexcharts-tooltip { background: #1e293b !important; border-color: #334155 !important; color: #f1f5f9 !important; }
    html.dark-mode .cv-est-outer .apexcharts-tooltip-title { background: #334155 !important; border-color: #475569 !important; color: #f1f5f9 !important; }
    html.dark-mode .cv-est-outer .apexcharts-legend-text { color: #94a3b8 !important; }
    /* Badges dinámicos de recuperación y penetración (data-cv-state) */
    html.dark-mode [data-cv-state="success"] { background: #14532d !important; color: #4ade80 !important; border: 1px solid rgba(74,222,128,0.25) !important; }
    html.dark-mode [data-cv-state="warning"] { background: #451a03 !important; color: #fbbf24 !important; border: 1px solid rgba(251,191,36,0.25) !important; }
    html.dark-mode [data-cv-state="danger"]  { background: #450a0a !important; color: #f87171 !important; border: 1px solid rgba(248,113,113,0.25) !important; }
    html.dark-mode [data-cv-state=""],
    html.dark-mode [data-cv-state="neutral"] { background: #1e3a5f !important; color: #93c5fd !important; }
    /* Panel de KPIs de despacho */
    html.dark-mode .cv-panel-desp { background: #1e293b !important; border-color: #334155 !important; }
    html.dark-mode .cv-desp-kpi { background: #253344 !important; border-color: #334155 !important; }
    html.dark-mode .cv-desp-kpi *[style*="color:#6b7a90"] { color: #94a3b8 !important; }
    html.dark-mode .cv-desp-kpi *[style*="color:#1a3a5c"] { color: #e2e8f0 !important; }
    html.dark-mode .cv-top-desp { background: rgba(46,204,139,0.08) !important; border-color: rgba(46,204,139,0.25) !important; }
    html.dark-mode .cv-top-desp *[style*="color:#1a3a5c"] { color: #e2e8f0 !important; }
    html.dark-mode .cv-top-desp *[style*="color:#6b7a90"] { color: #94a3b8 !important; }
    html.dark-mode .cv-top-desp *[style*="color:#0d5c3a"] { color: #4ade80 !important; }
    /* card gestor período (azul) */
    html.dark-mode .cv-top-desp[style*="eaf3fb"] { background: rgba(52,152,219,0.08) !important; border-color: rgba(52,152,219,0.25) !important; }
    html.dark-mode .cv-top-desp *[style*="color:#2471a3"] { color: #60a5fa !important; }
    /* Tip buttons */
    html.dark-mode .cv-tip-btn { color: #475569 !important; }
    html.dark-mode .cv-tip-btn:hover,
    html.dark-mode .cv-tip-btn:focus { color: #94a3b8 !important; }
    /* Brecha hacia siguiente meta */
    .cv-brecha-box {
        background: #f5f7fa;
        border: 1px solid #dde3ec;
        border-radius: 8px;
        padding: 10px 14px;
    }
    html.dark-mode .cv-brecha-box {
        background: #253344 !important;
        border-color: #334155 !important;
    }
    .cv-brecha-titulo { font-size:10px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#6b7a90;margin-bottom:8px;text-align:center; }
    html.dark-mode .cv-brecha-titulo { color: #94a3b8 !important; }
    .cv-brecha-texto { font-size:12px;color:#1a3a5c;text-align:center;line-height:1.55; }
    html.dark-mode .cv-brecha-texto { color: #e2e8f0 !important; }
    .cv-brecha-track { margin-top:8px;background:#e9ecef;border-radius:6px;height:8px;overflow:hidden; }
    html.dark-mode .cv-brecha-track { background: #334155 !important; }
    .cv-brecha-sub { font-size:10px;color:#6b7a90;text-align:center;margin-top:5px; }
    html.dark-mode .cv-brecha-sub { color: #94a3b8 !important; }
    .cv-card-title {
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .06em;
        color: var(--bs-secondary-color);
    }
    .cv-card-row-label {
        font-size: 12px;
        color: #6b7a90;
    }
    html.dark-mode .cv-card-row-label {
        color: #94a3b8;
    }
    .cv-top-kpi-card {
        min-height: 156px;
    }
    .cv-top-kpi-sub {
        flex-grow: 1;
    }
    .cv-top-kpi-spacer {
        height: 1.45rem;
        margin-top: .5rem;
    }
</style>


<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="cv-est-outer">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                <div>
                    <h4 class="fw-bold mb-1">
                        <i class="fa-solid fa-file-signature me-2 text-primary"></i>Estadísticas Convenios
                    </h4>
                    <p id="cvEstSubtitulo" class="text-muted mb-0 small">—</p>
                </div>
                <div class="gc-est-fp-rango" style="max-width: 28rem; width: 100%;">
                    <label for="flatpickr-range-cv-est" class="form-label small text-muted mb-0">
                        <i class="fa fa-calendar-alt me-1" aria-hidden="true"></i>Periodo (rango de fechas)
                    </label>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <input type="text" id="flatpickr-range-cv-est" readonly
                            class="form-control form-control-sm flex-grow-1 gc-est-fp-input"
                            style="min-width: 12rem; max-width: 19.5rem; cursor: pointer; user-select: none;"
                            placeholder="Selecciona inicio y fin" autocomplete="off"
                            title="No se pueden elegir fechas posteriores a hoy." />
                        <button type="button" class="btn btn-outline-secondary btn-sm flex-shrink-0" id="btnCvEstRestablecerPeriodo"
                            title="Volver al periodo por defecto: lunes de esta semana hasta hoy">
                            Restablecer
                        </button>
                    </div>
                </div>
            </div>

            <!-- ── MAIN ROW ─────────────────────────────────── -->
            <div class="row g-3 mb-4 align-items-stretch">

                <!-- ── LEFT COL (col-lg-8) ─────────────────── -->
                <div class="col-lg-8">

                    <div class="row g-3 mb-3">
                        <div class="col-6 col-md-4 col-xl">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body py-2 d-flex flex-column cv-top-kpi-card">
                                    <span class="badge rounded-pill bg-label-warning text-warning fw-bold mb-2 py-2 px-2 w-100 text-center lh-sm" style="font-size:.88rem;letter-spacing:.06em;line-height:1.25;white-space:normal">Convenios activos</span>
                                    <div class="cv-kpi-period-badge mb-2 text-start align-self-start w-100" style="font-size:.62rem;font-weight:700;letter-spacing:.04em;color:var(--bs-secondary-color);line-height:1.25">—</div>
                                    <div id="cvKpiActivos" class="fs-4 fw-bold text-success">0</div>
                                    <div class="small text-muted mt-1 cv-top-kpi-sub">Totales en el sistema</div>
                                    <div class="cv-top-kpi-spacer"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-xl">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body py-2 d-flex flex-column cv-top-kpi-card">
                                    <span class="badge rounded-pill bg-label-warning text-warning fw-bold mb-2 py-2 px-2 w-100 text-center lh-sm" style="font-size:.88rem;letter-spacing:.06em;line-height:1.25;white-space:normal">Convenios completos</span>
                                    <div class="cv-kpi-period-badge mb-2 text-start align-self-start w-100" style="font-size:.62rem;font-weight:700;letter-spacing:.04em;color:var(--bs-secondary-color);line-height:1.25">—</div>
                                    <div id="cvKpiCompletados" class="fs-4 fw-bold text-primary">0</div>
                                    <div class="small text-muted mt-1 cv-top-kpi-sub">Totales en el sistema</div>
                                    <div class="cv-top-kpi-spacer"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-xl">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body py-2 d-flex flex-column cv-top-kpi-card">
                                    <span class="badge rounded-pill bg-label-warning text-warning fw-bold mb-2 py-2 px-2 w-100 text-center lh-sm" style="font-size:.88rem;letter-spacing:.06em;line-height:1.25;white-space:normal">Convenios cancelados</span>
                                    <div class="cv-kpi-period-badge mb-2 text-start align-self-start w-100" style="font-size:.62rem;font-weight:700;letter-spacing:.04em;color:var(--bs-secondary-color);line-height:1.25">—</div>
                                    <div id="cvKpiCancelados" class="fs-4 fw-bold text-danger">0</div>
                                    <div class="small text-muted mt-1 cv-top-kpi-sub">Totales en el sistema</div>
                                    <div class="cv-top-kpi-spacer"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Panel: Nuevos del período -->
                    <div class="card shadow-sm mb-3 cv-panel">
                        <div class="card-header py-3 d-flex justify-content-between align-items-start flex-wrap gap-2">
                            <div class="d-flex flex-wrap align-items-baseline gap-2" style="flex:1;min-width:0;">
                                <span style="font-size:.7rem;font-weight:600;letter-spacing:.02em;color:var(--bs-secondary-color)">Nuevos convenios del período</span>
                                <span id="cvNuevosRangoInline" class="small text-muted">—</span>
                            </div>
                        </div>
                        <div class="card-body">

                        <!-- Cards clickeables -->
                        <div class="d-flex flex-wrap gap-2 mb-2 cv-nuevo-cards-row">
                            <div class="cv-nuevo-card" data-cv-nuevo-tipo="activos" role="button" tabindex="0" aria-expanded="false"
                                title="Clic para ver desglose por producto"
                                style="position:relative;background:#eef1f5;border:1px solid #dde3ec;border-radius:8px;padding:10px 14px;text-align:center;flex:1;min-width:100px;">
                                <button type="button" class="cv-tip-btn cv-nuevo-no-abrir" data-bs-toggle="tooltip" data-bs-placement="top" data-cv-tip="1"
                                    onclick="event.stopPropagation();"
                                    title="Convenios con estatus Activo generados en el período seleccionado (fecha_alta en el mes/año)."
                                    aria-label="Ayuda: nuevos activos">
                                    <i class="fa fa-info-circle" aria-hidden="true"></i>
                                </button>
                                <div style="font-size:11px;color:#6b7a90;margin-bottom:4px;padding-right:10px;">Activos</div>
                                <div id="cvNuevosActivos" class="cv-kpi-val-num" style="color:#2ecc8b;">0</div>
                            </div>
                            <div class="cv-nuevo-card" data-cv-nuevo-tipo="completados" role="button" tabindex="0" aria-expanded="false"
                                title="Clic para ver desglose por producto"
                                style="position:relative;background:#eef1f5;border:1px solid #dde3ec;border-radius:8px;padding:10px 14px;text-align:center;flex:1;min-width:100px;">
                                <button type="button" class="cv-tip-btn cv-nuevo-no-abrir" data-bs-toggle="tooltip" data-bs-placement="top" data-cv-tip="1"
                                    onclick="event.stopPropagation();"
                                    title="Convenios con estatus Completado generados en el período seleccionado."
                                    aria-label="Ayuda: nuevos completados">
                                    <i class="fa fa-info-circle" aria-hidden="true"></i>
                                </button>
                                <div style="font-size:11px;color:#6b7a90;margin-bottom:4px;padding-right:10px;">Completados</div>
                                <div id="cvNuevosCompletados" class="cv-kpi-val-num" style="color:#3498db;">0</div>
                            </div>
                            <div class="cv-nuevo-card" data-cv-nuevo-tipo="cancelados" role="button" tabindex="0" aria-expanded="false"
                                title="Clic para ver desglose por producto"
                                style="position:relative;background:#eef1f5;border:1px solid #dde3ec;border-radius:8px;padding:10px 14px;text-align:center;flex:1;min-width:100px;">
                                <button type="button" class="cv-tip-btn cv-nuevo-no-abrir" data-bs-toggle="tooltip" data-bs-placement="top" data-cv-tip="1"
                                    onclick="event.stopPropagation();"
                                    title="Convenios con estatus Cancelado generados en el período seleccionado."
                                    aria-label="Ayuda: nuevos cancelados">
                                    <i class="fa fa-info-circle" aria-hidden="true"></i>
                                </button>
                                <div style="font-size:11px;color:#6b7a90;margin-bottom:4px;padding-right:10px;">Cancelados</div>
                                <div id="cvNuevosCancelados" class="cv-kpi-val-num" style="color:#e74c3c;">0</div>
                            </div>
                            <div class="cv-nuevo-total" style="background:#eef1f5;border:1px solid #dde3ec;border-radius:8px;">
                                <div style="font-size:11px;color:#6b7a90;margin-bottom:4px;">Total nuevos</div>
                                <div id="cvNuevosTotal" class="cv-kpi-val-num" style="color:#1a3a5c;">0</div>
                            </div>
                        </div>

                        <p id="cvNuevosClicAviso" style="font-size:11px;color:#5a6a7d;margin:6px 0 4px;line-height:1.45;">Tip: Haz clic en Activos, Completados o Cancelados para ver el desglose por producto.</p>

                        <!-- Gráfica barra de nuevos -->
                        <div id="cvNuevosBarWrap" style="min-height:200px;margin-top:6px;">
                            <div id="cvChartNuevos"></div>
                        </div>

                        <!-- Panel de detalle por producto (oculto por default) -->
                        <div id="cvNuevosDetalleWrap" class="d-none" style="margin-top:14px;padding-top:14px;border-top:1px solid #dde3ec;">
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
                                <div>
                                    <div id="cvNuevosDetalleTitulo" style="font-size:13px;font-weight:700;color:#1a3a5c;">—</div>
                                    <div id="cvNuevosDetalleSub" style="font-size:11px;color:#6b7a90;margin-top:2px;min-height:0;"></div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="cvNuevosDetalleCerrar">Cerrar</button>
                            </div>
                            <div id="cvChartNuevosDetalle" style="min-height:260px;"></div>
                        </div>
                        </div>
                    </div>

                    <!-- Panel: Semanas de pago del período -->
                    <div class="card shadow-sm cv-panel-sem">
                        <div class="card-header py-3 d-flex justify-content-between align-items-start mb-0">
                            <div class="d-flex flex-wrap align-items-baseline gap-2" style="flex:1;min-width:0;">
                                <span style="font-size:.7rem;font-weight:600;letter-spacing:.02em;color:var(--bs-secondary-color)">Semanas de pago del período</span>
                                <span id="cvSemRangoInline" class="small text-muted">—</span>
                            </div>
                        </div>
                        <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            <span class="cv-sem-badge" style="background:#d4f5e7;color:#0d5c3a;">
                                <i class="fa fa-check-circle fa-sm" aria-hidden="true"></i>
                                Pagadas <span id="cvSemPagadas" class="cv-sem-num">0</span>
                            </span>
                            <span class="cv-sem-badge" style="background:#fde8e8;color:#7a1111;">
                                <i class="fa fa-exclamation-circle fa-sm" aria-hidden="true"></i>
                                Vencidas <span id="cvSemVencidas" class="cv-sem-num">0</span>
                            </span>
                            <span class="cv-sem-badge" style="background:#eef1f5;color:#1a3a5c;">
                                <i class="fa fa-clock-o fa-sm" aria-hidden="true"></i>
                                Pendientes <span id="cvSemPendientes" class="cv-sem-num">0</span>
                            </span>
                            <span class="cv-sem-badge" style="background:#fef3cd;color:#7a5000;">
                                <i class="fa fa-adjust fa-sm" aria-hidden="true"></i>
                                Parciales <span id="cvSemParciales" class="cv-sem-num">0</span>
                            </span>
                            <span class="cv-sem-badge" style="background:#f0ecff;color:#5b2d8e;">
                                <i class="fa fa-hourglass-half fa-sm" aria-hidden="true"></i>
                                A conciliar <span id="cvSemConciliar" class="cv-sem-num">0</span>
                            </span>
                            <span class="cv-sem-badge" style="background:#f5f7fa;color:#6b7a90;">
                                Canceladas <span id="cvSemCanceladas" class="cv-sem-num">0</span>
                            </span>
                        </div>
                        <!-- Gráfica horizontal de semanas -->
                        <div id="cvChartSemanas" style="margin-top:14px;"></div>
                        </div>
                    </div>

                    <!-- Gestores / despacho (alineado bajo Semanas de pago) -->
                    <div class="card shadow-sm cv-panel-desp mt-3">
                        <div class="card-header py-3 d-flex justify-content-between align-items-start mb-0">
                            <div class="d-flex flex-wrap align-items-baseline gap-2" style="flex:1;min-width:0;">
                                <span style="font-size:.7rem;font-weight:600;letter-spacing:.02em;color:var(--bs-secondary-color)">Gestores de cobranza — despacho</span>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                        <div class="row g-2">
                            <div class="col-6 col-md-3">
                                <div class="cv-desp-kpi cv-desp-kpi-inner cv-desp-kpi-stack d-flex flex-column justify-content-between align-items-center" style="background:#f5f7fa;border:1px solid #dde3ec;border-radius:10px;">
                                    <div style="font-size:11px;color:#6b7a90;text-transform:uppercase;letter-spacing:0.5px;">
                                        <i class="fa fa-building fa-sm" aria-hidden="true" style="margin-right:3px;"></i>Despacho
                                    </div>
                                    <div id="cvDespCelulaDesp" class="cv-kpi-val-num d-flex align-items-center justify-content-center flex-grow-1" style="color:#3498db;">0</div>
                                    <div style="font-size:10px;color:#6b7a90;">Externo</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="cv-desp-kpi cv-desp-kpi-inner d-flex flex-column justify-content-center cv-desp-kpi-stack" style="background:#f5f7fa;border:1px solid #dde3ec;border-radius:10px;">
                                    <div style="font-size:11px;color:#6b7a90;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.5px;">
                                        <i class="fa fa-file-text fa-sm" aria-hidden="true" style="margin-right:3px;"></i>Créditos en gestión
                                    </div>
                                    <div id="cvDespCreditosGestion" class="cv-kpi-val-num" style="color:#1a3a5c;line-height:1;">0</div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="cv-top-desp" style="background:#eafaf3;border:1px solid rgba(46,204,139,0.3);border-radius:10px;padding:14px 16px;height:100%;min-height:118px;display:flex;flex-direction:column;justify-content:center;">
                                    <div style="font-size:10px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#6b7a90;margin-bottom:8px;">
                                        <i class="fa fa-trophy fa-sm" aria-hidden="true" style="color:#f39c12;margin-right:4px;"></i>Despacho con más convenios
                                    </div>
                                    <div id="cvTopDespNombre" style="font-size:15px;font-weight:700;color:#1a3a5c;line-height:1.3;word-break:break-word;">—</div>
                                    <div style="margin-top:6px;font-size:12px;color:#6b7a90;">
                                        Convenios activos: <strong id="cvTopDespConvenios" style="color:#0d5c3a;">0</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="cv-desp-kpi cv-desp-kpi-inner d-flex flex-column justify-content-center cv-desp-kpi-stack" style="background:#f5f7fa;border:1px solid #dde3ec;border-radius:10px;">
                                    <div style="font-size:11px;color:#6b7a90;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.5px;">
                                        <i class="fa fa-bar-chart fa-sm" aria-hidden="true" style="margin-right:3px;"></i>Prom. convenios/gestor
                                    </div>
                                    <div id="cvDespPromedioConv" class="cv-kpi-val-num" style="color:#3498db;">0</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="cv-desp-kpi cv-desp-kpi-inner d-flex flex-column justify-content-center cv-desp-kpi-stack" style="background:#f5f7fa;border:1px solid #dde3ec;border-radius:10px;">
                                    <div style="font-size:11px;color:#6b7a90;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.5px;">
                                        <i class="fa fa-check-circle fa-sm" aria-hidden="true" style="margin-right:3px;color:#2ecc8b;"></i>Con meta (≥5 conv.)
                                    </div>
                                    <div id="cvDespEnMeta" class="cv-kpi-val-num" style="color:#2ecc8b;">0</div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="cv-top-desp" style="background:#fdf4e7;border:1px solid rgba(243,156,18,0.3);border-radius:10px;padding:14px 16px;height:100%;min-height:118px;display:flex;flex-direction:column;justify-content:center;">
                                    <div style="font-size:10px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#6b7a90;margin-bottom:8px;">
                                        <i class="fa fa-arrow-down fa-sm" aria-hidden="true" style="color:#e67e22;margin-right:4px;"></i>Despacho con menos convenios
                                    </div>
                                    <div id="cvBottomDespNombre" style="font-size:15px;font-weight:700;color:#1a3a5c;line-height:1.3;word-break:break-word;">—</div>
                                    <div style="margin-top:6px;font-size:12px;color:#6b7a90;">
                                        Convenios activos: <strong id="cvBottomDespConvenios" style="color:#e67e22;">0</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>

                </div><!-- /col-lg-8 -->

                <!-- ── RIGHT COL (col-lg-4) ──────────────────── -->
                <div class="col-lg-4 d-flex flex-column gap-3">

                    <!-- Tarjeta de recuperación (montos + radial) -->
                    <div class="cv-card-rec card shadow-sm">
                        <div class="card-header py-3">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                <div class="d-flex align-items-center gap-2 min-w-0">
                                    <span class="cv-card-title">Recuperación</span>
                                </div>
                                <span id="cvBadgeRecuperacion" data-cv-state="" style="font-size:11px;font-weight:700;padding:3px 10px;border-radius:10px;display:inline-block;">—</span>
                            </div>
                        </div>
                        <div class="card-body" style="padding:16px 18px;">
                            <div class="d-flex align-items-center gap-1">
                                <span class="d-none"></span>
                            </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="cv-card-row-label">Recuperación Estimada</span>
                            <span id="cvMontoComp" style="font-size:13px;font-weight:700;color:#1a3a5c;">$0.00</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="cv-card-row-label">Recuperado en período</span>
                            <span id="cvMontoRecup" style="font-size:13px;font-weight:700;color:#2ecc8b;">$0.00</span>
                        </div>
                        <div class="mt-3 pt-3 cv-sep" style="border-top:1px solid #dde3ec;">
                            <div style="font-size:10px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#6b7a90;margin-bottom:8px;text-align:center;">% Recuperación del período</div>
                            <div id="cvChartRecuperacion" class="cv-kpi-chart-slot"></div>
                            <div class="d-flex justify-content-center flex-wrap gap-3 mt-1" style="font-size:11px;color:#6b7a90;">
                                <span>Recuperado: <strong id="cvRecupLegendRecup">$0</strong></span>
                                <span>Comprometido: <strong id="cvRecupLegendComp">$0</strong></span>
                            </div>
                        </div>
                        </div>
                    </div>

                    <!-- Tarjeta de cobertura de convenios -->
                    <div class="cv-card-pen card shadow-sm" style="flex:1 1 auto;">
                        <div class="card-header py-3">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                <div class="d-flex flex-wrap align-items-baseline gap-1 min-w-0" style="flex:1;">
                                    <span class="cv-card-title">Cobertura de convenios</span>
                                </div>
                                <span id="cvBadgePenetracion" data-cv-state="" style="font-size:11px;font-weight:700;padding:3px 10px;border-radius:10px;display:inline-block;">—</span>
                            </div>
                        </div>
                        <div class="card-body" style="padding:16px 18px;">
                        <div class="d-flex flex-column justify-content-center text-center pt-2">
                            <div id="cvPctPenetracion" class="cv-kpi-val-num" style="color:#2ecc8b;">0%</div>
                            <div style="font-size:10px;color:#6b7a90;margin-top:6px;line-height:1.45;">
                                (Con convenio activo / total en despacho) × 100
                            </div>
                        </div>
                        <div class="mt-3 pt-3 cv-sep" style="border-top:1px solid #dde3ec;">
                            <div id="cvChartPenetracion" class="cv-kpi-chart-slot"></div>
                            <div class="d-flex justify-content-center flex-wrap gap-3 mt-1" style="font-size:11px;color:#6b7a90;">
                                <span>
                                    <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:var(--bs-success);margin-right:4px;vertical-align:middle;"></span>
                                    Con convenio: <strong id="cvPenConConvenio">0</strong>
                                </span>
                                <span>
                                    <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:var(--bs-secondary);margin-right:4px;vertical-align:middle;"></span>
                                    Sin convenio: <strong id="cvPenSinConvenio">0</strong>
                                </span>
                            </div>
                        </div>
                        <!-- Brecha hacia la siguiente meta -->
                        <div id="cvBrechaWrap" class="mt-3 pt-3 cv-sep" style="border-top:1px solid #dde3ec;">
                            <div class="cv-brecha-titulo">Brecha hacia siguiente meta</div>
                            <div class="cv-brecha-box">
                                <div id="cvBrechaTexto" class="cv-brecha-texto">—</div>
                                <div class="cv-brecha-track">
                                    <div id="cvBrechaBar" style="height:100%;width:0%;border-radius:6px;background:#2ecc8b;transition:width 0.5s ease;"></div>
                                </div>
                                <div id="cvBrechaSubtexto" class="cv-brecha-sub">—</div>
                            </div>
                        </div>
                        </div>
                    </div>

                    <!-- Gestores / call center (alineado bajo Cobertura) -->
                    <div class="card shadow-sm cv-panel-desp">
                        <div class="card-header py-3 d-flex justify-content-between align-items-start mb-0">
                            <div class="d-flex flex-wrap align-items-baseline gap-2" style="flex:1;min-width:0;">
                                <span style="font-size:.7rem;font-weight:600;letter-spacing:.02em;color:var(--bs-secondary-color)">Gestores de cobranza — call center</span>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                        <div class="row g-2">
                            <div class="col-12">
                                <div class="cv-desp-kpi cv-desp-kpi-inner cv-desp-kpi-stack d-flex flex-column justify-content-between align-items-center" style="background:#f5f7fa;border:1px solid #dde3ec;border-radius:10px;">
                                    <div style="font-size:11px;color:#6b7a90;text-transform:uppercase;letter-spacing:0.5px;">
                                        <i class="fa fa-phone fa-sm" aria-hidden="true" style="margin-right:3px;"></i>Call Center
                                    </div>
                                    <div id="cvDespCelulaCC" class="cv-kpi-val-num d-flex align-items-center justify-content-center flex-grow-1" style="color:#9b59b6;">0</div>
                                    <div style="font-size:10px;color:#6b7a90;">Internos</div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="cv-top-desp" style="background:#eaf3fb;border:1px solid rgba(52,152,219,0.3);border-radius:10px;padding:14px 16px;height:100%;display:flex;flex-direction:column;justify-content:center;">
                                    <div style="font-size:10px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#6b7a90;margin-bottom:8px;">
                                        <i class="fa fa-star fa-sm" aria-hidden="true" style="color:#3498db;margin-right:4px;"></i>Gestor más activo del período
                                    </div>
                                    <div id="cvTopGestorPeriodoNombre" style="font-size:15px;font-weight:700;color:#1a3a5c;line-height:1.3;word-break:break-word;">—</div>
                                    <div style="margin-top:6px;font-size:12px;color:#6b7a90;">
                                        Convenios en el período: <strong id="cvTopGestorPeriodoConvenios" style="color:#2471a3;">0</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="cv-desp-kpi cv-desp-kpi-inner d-flex flex-column justify-content-center cv-desp-kpi-stack" style="background:#f5f7fa;border:1px solid #dde3ec;border-radius:10px;">
                                    <div style="font-size:11px;color:#6b7a90;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.5px;">
                                        <i class="fa fa-user-times fa-sm" aria-hidden="true" style="margin-right:3px;color:#e74c3c;"></i>Sin convenio
                                    </div>
                                    <div id="cvDespSinConvenio" class="cv-kpi-val-num" style="color:#e74c3c;line-height:1;">0</div>
                                    <div style="font-size:10px;color:#6b7a90;margin-top:4px;">Gestores</div>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>

                </div><!-- /col-lg-4 -->

            </div><!-- /row -->
        </div>
    </div>
</div>

<script>
(function () {
    var datosIni = <?php echo $datosInicialesJson; ?>;
    var cvState = {
        fecha_inicio: <?php echo json_encode($fechaIniDefault ?? ''); ?>,
        fecha_fin: <?php echo json_encode($fechaFinDefault ?? ''); ?>
    };

    var ST_BADGE_VERDE    = 'background:#d4f5e7;color:#0d5c3a;font-size:11px;font-weight:700;padding:3px 10px;border-radius:10px;display:inline-block;';
    var ST_BADGE_AMARILLO = 'background:#fef3cd;color:#7a5000;font-size:11px;font-weight:700;padding:3px 10px;border-radius:10px;display:inline-block;';
    var ST_BADGE_ROJO     = 'background:#fde8e8;color:#7a1111;font-size:11px;font-weight:700;padding:3px 10px;border-radius:10px;display:inline-block;';
    /** Mismo tamaño que `.cv-est-outer { --cv-kpi-chart }` (donut + radial). */
    var CV_KPI_CHART_PX = 220;

    var cvCharts            = { nuevos: null, nuevosDetalle: null, recuperacion: null, penetracion: null, semanas: null };
    var cvNuevoTipoAbierto  = null;
    var cvDetalleReqSeq     = 0;
    var cvReqConv           = 0;
    var cvReqCierr          = 0;
    var cvReqAsig           = 0;
    var cvApexLoading       = false;
    var cvApexCallbacks     = [];
    var cvLoadingOpen       = false;

    // ─── Helpers ────────────────────────────────────────────
    function nv(v) {
        var x = parseFloat(String(v == null ? 0 : v).replace(',', '.'));
        return isNaN(x) ? 0 : x;
    }

    function formatDinero(v) {
        var x = nv(v);
        return '$' + x.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function setText(id, txt) {
        var el = document.getElementById(id);
        if (el) el.textContent = txt;
    }

    function isDark() {
        return document.documentElement.classList.contains('dark-mode');
    }

    function cssColor(name, fallback) {
        var v = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
        return v || fallback;
    }

    function cvChartPalette() {
        return {
            success: cssColor('--bs-success', '#2ecc8b'),
            primary: cssColor('--bs-primary', '#3498db'),
            danger: cssColor('--bs-danger', '#e74c3c'),
            warning: cssColor('--bs-warning', '#f0a500'),
            secondary: cssColor('--bs-secondary', '#6b7280'),
            muted: cssColor('--bs-secondary-color', '#6b7a90'),
            grid: isDark() ? '#334155' : '#eef1f5',
            surface: isDark() ? '#1e293b' : '#ffffff'
        };
    }

    var CV_COLORS = [
        cvChartPalette().primary, cvChartPalette().success, cvChartPalette().warning,
        cvChartPalette().danger, cvChartPalette().secondary, '#1abc9c', '#8e44ad',
        '#e67e22', '#34495e', '#16a085', '#27ae60', '#2980b9'
    ];

    function badgeStyle(cls) {
        var c = (cls || '').toLowerCase();
        if (isDark()) {
            if (c.indexOf('danger')  !== -1) return 'background:rgba(231,76,60,0.2);color:#f87171;font-size:11px;font-weight:700;padding:3px 10px;border-radius:10px;display:inline-block;';
            if (c.indexOf('warning') !== -1) return 'background:rgba(240,165,0,0.2);color:#fbbf24;font-size:11px;font-weight:700;padding:3px 10px;border-radius:10px;display:inline-block;';
            return 'background:rgba(46,204,139,0.2);color:#4ade80;font-size:11px;font-weight:700;padding:3px 10px;border-radius:10px;display:inline-block;';
        }
        if (c.indexOf('danger')  !== -1) return ST_BADGE_ROJO;
        if (c.indexOf('warning') !== -1) return ST_BADGE_AMARILLO;
        return ST_BADGE_VERDE;
    }

    function setBadge(id, text, cls) {
        var el = document.getElementById(id);
        if (!el) return;
        el.textContent = text;
        el.setAttribute('style', badgeStyle(cls));
        // data-cv-state lets CSS dark-mode overrides apply reliably via !important
        var c = (cls || '').toLowerCase();
        var state = c.indexOf('danger') !== -1 ? 'danger' : (c.indexOf('warning') !== -1 ? 'warning' : (c.indexOf('success') !== -1 ? 'success' : 'neutral'));
        el.setAttribute('data-cv-state', state);
    }

    function cvFmtYmd(fecha) {
        var y = fecha.getFullYear();
        var m = String(fecha.getMonth() + 1).padStart(2, '0');
        var d = String(fecha.getDate()).padStart(2, '0');
        return y + '-' + m + '-' + d;
    }

    function cvRangoLunesHoy() {
        var hoy = new Date();
        hoy.setHours(0, 0, 0, 0);
        var dow = hoy.getDay();
        var diffToMon = dow === 0 ? -6 : 1 - dow;
        var lun = new Date(hoy.getFullYear(), hoy.getMonth(), hoy.getDate() + diffToMon);
        lun.setHours(0, 0, 0, 0);
        return { ini: cvFmtYmd(lun), fin: cvFmtYmd(hoy) };
    }

    function cvParamsPeriodo() {
        return {
            fecha_inicio: cvState.fecha_inicio,
            fecha_fin: cvState.fecha_fin,
            anio: parseInt(String(cvState.fecha_inicio).slice(0, 4), 10) || new Date().getFullYear(),
            mes: parseInt(String(cvState.fecha_inicio).slice(5, 7), 10) || new Date().getMonth() + 1
        };
    }

    function cvCerrarFlatpickrCalendario(fpInstance) {
        var fp = fpInstance;
        if (!fp) {
            var elFp = document.getElementById('flatpickr-range-cv-est');
            fp = elFp && elFp._flatpickr ? elFp._flatpickr : null;
        }
        if (!fp) return;
        try { if (typeof fp.close === 'function') fp.close(); } catch (e1) {}
        var inp = document.getElementById('flatpickr-range-cv-est');
        if (inp) {
            try { inp.blur(); } catch (e2) {}
        }
    }

    function cvAplicarRangoYRefrescar(iniYmd, finYmd, fpInstance) {
        cvState.fecha_inicio = iniYmd;
        cvState.fecha_fin = finYmd;
        if (fpInstance) {
            try {
                var a = new Date(iniYmd + 'T12:00:00');
                var b = new Date(finYmd + 'T12:00:00');
                fpInstance.setDate([a, b], false);
            } catch (eSd) {}
        }
        cvCerrarFlatpickrCalendario(fpInstance || null);
        refrescar();
    }

    function cvRestaurarPeriodoPorDefecto() {
        var rh = cvRangoLunesHoy();
        var el = document.getElementById('flatpickr-range-cv-est');
        var fp = el && el._flatpickr ? el._flatpickr : null;
        cvAplicarRangoYRefrescar(rh.ini, rh.fin, fp);
    }

    function initFlatpickrCvEst() {
        var el = document.getElementById('flatpickr-range-cv-est');
        if (!el || el._flatpickr || typeof flatpickr === 'undefined') {
            return;
        }
        var hoyMax = new Date();
        hoyMax.setHours(23, 59, 59, 999);
        flatpickr(el, {
            mode: 'range',
            dateFormat: 'Y-m-d',
            clickOpens: true,
            allowInput: false,
            maxDate: hoyMax,
            disableMobile: true,
            locale: {
                firstDayOfWeek: 1,
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
            defaultDate: [cvState.fecha_inicio, cvState.fecha_fin],
            onChange: function (selectedDates, dateStr, instance) {
                if (selectedDates.length === 2) {
                    var ini = cvFmtYmd(selectedDates[0]);
                    var fin = cvFmtYmd(selectedDates[1]);
                    cvCerrarFlatpickrCalendario(instance);
                    setTimeout(function () {
                        cvAplicarRangoYRefrescar(ini, fin, null);
                    }, 0);
                } else if (selectedDates.length === 0) {
                    cvRestaurarPeriodoPorDefecto();
                }
            },
            onClose: function () {
                cvCerrarFlatpickrCalendario(null);
            }
        });
    }

    function scheduleInitFlatpickrCvEst() {
        var n = 0;
        function intentar() {
            if (typeof flatpickr !== 'undefined') {
                initFlatpickrCvEst();
                return;
            }
            n += 1;
            if (n > 100) return;
            setTimeout(intentar, 40);
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', intentar);
        } else {
            intentar();
        }
    }

    // ─── ApexCharts lazy load ───────────────────────────────
    function ensureApex(cb) {
        if (typeof ApexCharts !== 'undefined') { if (cb) cb(); return; }
        if (cb) cvApexCallbacks.push(cb);
        if (cvApexLoading) { return; }
        cvApexLoading = true;
        var s = document.createElement('script');
        s.src = '/assets/vendor/libs/apex-charts/apexcharts.js';
        s.onload  = function () {
            cvApexLoading = false;
            var cbs = cvApexCallbacks.splice(0);
            for (var i = 0; i < cbs.length; i++) { try { cbs[i](); } catch (e) {} }
        };
        s.onerror = function () { cvApexLoading = false; cvApexCallbacks = []; };
        document.head.appendChild(s);
    }

    // ─── POST helper (form-encoded) ──────────────────────────
    function postForm(url, params) {
        var body = Object.keys(params).map(function (k) {
            return encodeURIComponent(k) + '=' + encodeURIComponent(params[k]);
        }).join('&');
        return fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Front-Request': 'true' },
            body: body
        }).then(function (r) {
            return r.text().then(function (txt) {
                var j = null;
                try { j = JSON.parse(txt); } catch (e) { j = null; }
                return { ok: r.ok, status: r.status, json: j };
            });
        });
    }

    // ─── Render: barra horizontal de semanas ───────────────
    function renderBarSemanas(d) {
        var cats   = ['Pagadas', 'Pendientes', 'Vencidas', 'Parciales', 'A conciliar', 'Canceladas'];
        var series = [
            nv(d.semanas_pagadas),
            nv(d.semanas_pendientes),
            nv(d.semanas_vencidas),
            nv(d.semanas_parciales),
            nv(d.semanas_conciliar),
            nv(d.semanas_canceladas)
        ];
        var pal = cvChartPalette();
        var colors = [pal.success, pal.primary, pal.danger, pal.warning, '#9b59b6', pal.secondary];
        ensureApex(function () {
            var dark = isDark();
            var opts = {
                chart: { type: 'bar', height: 300, toolbar: { show: false }, animations: { speed: 380 } },
                series: [{ name: 'Semanas', data: series }],
                xaxis: { categories: cats, labels: { style: { colors: dark ? '#94a3b8' : pal.muted, fontSize: '11px' } } },
                yaxis: { labels: { style: { colors: dark ? '#94a3b8' : pal.muted, fontSize: '11px' } } },
                colors: colors,
                dataLabels: {
                    enabled: true,
                    formatter: function (v) { return v > 0 ? v : ''; },
                    style: { fontSize: '11px', fontWeight: 700, colors: ['#ffffff'] }
                },
                plotOptions: { bar: { horizontal: true, borderRadius: 5, barHeight: '52%', distributed: true } },
                legend: { show: false },
                grid: { borderColor: pal.grid, xaxis: { lines: { show: true } }, yaxis: { lines: { show: false } } },
                tooltip: { theme: dark ? 'dark' : 'light', y: { formatter: function (v) { return v + ' semanas'; } } }
            };
            if (!cvCharts.semanas) {
                cvCharts.semanas = new ApexCharts(document.querySelector('#cvChartSemanas'), opts);
                cvCharts.semanas.render();
            } else {
                cvCharts.semanas.updateOptions(opts, true, true);
            }
        });
    }

    // ─── Render: barra de nuevos del período ────────────────
    function renderBarNuevos(d) {
        ensureApex(function () {
            var pal = cvChartPalette();
            var series = [nv(d.nuevos_activos), nv(d.nuevos_completados), nv(d.nuevos_cancelados)];
            if (!cvCharts.nuevos) {
                cvCharts.nuevos = new ApexCharts(document.querySelector('#cvChartNuevos'), {
                    chart: { type: 'bar', height: 300, toolbar: { show: false } },
                    series: [{ name: 'Nuevos', data: series }],
                    xaxis: {
                        categories: ['Activos', 'Completados', 'Cancelados'],
                        labels: { style: { colors: isDark() ? '#94a3b8' : pal.muted, fontSize: '11px' } }
                    },
                    yaxis: { labels: { style: { colors: isDark() ? '#94a3b8' : pal.muted, fontSize: '11px' } } },
                    colors: [pal.success, pal.primary, pal.danger],
                    dataLabels: { enabled: false },
                    plotOptions: { bar: { borderRadius: 6, columnWidth: '48%', distributed: true } },
                    legend: { show: false },
                    grid: { borderColor: pal.grid },
                    tooltip: { theme: isDark() ? 'dark' : 'light', y: { formatter: function (v) { return v + ' convenios'; } } }
                });
                cvCharts.nuevos.render();
            } else {
                cvCharts.nuevos.updateOptions({
                    xaxis: {
                        categories: ['Activos', 'Completados', 'Cancelados'],
                        labels: { style: { colors: isDark() ? '#94a3b8' : pal.muted, fontSize: '11px' } }
                    },
                    yaxis: { labels: { style: { colors: isDark() ? '#94a3b8' : pal.muted, fontSize: '11px' } } },
                    colors: [pal.success, pal.primary, pal.danger],
                    plotOptions: { bar: { borderRadius: 6, columnWidth: '48%', distributed: true } }
                }, false, true);
                cvCharts.nuevos.updateSeries([{ name: 'Nuevos', data: series }], true);
            }
        });
    }

    // ─── Render: radial de recuperación ─────────────────────
    function renderRadialRecuperacion(pct) {
        var pal = cvChartPalette();
        var color = pct >= 60 ? pal.success : (pct >= 30 ? pal.warning : pal.danger);
        ensureApex(function () {
            var opts = {
                chart: { type: 'radialBar', height: CV_KPI_CHART_PX, toolbar: { show: false }, animations: { speed: 400 } },
                series: [pct],
                labels: ['Recuperación'],
                colors: [color],
                plotOptions: {
                    radialBar: {
                        hollow: { size: '68%', background: 'transparent' },
                        track: { background: isDark() ? '#334155' : '#e9ecef', strokeWidth: '92%' },
                        dataLabels: {
                            name: { show: true, fontSize: '11px', fontWeight: 600, color: isDark() ? '#94a3b8' : pal.muted, offsetY: 30 },
                            value: {
                                show: true, fontSize: '1.35rem', fontWeight: 800, color: isDark() ? '#e2e8f0' : '#1a3a5c', offsetY: -6,
                                formatter: function (v) { return Math.round(parseFloat(String(v).replace(',', '.'))) + '%'; }
                            },
                            total: { show: false }
                        }
                    }
                },
                stroke: { lineCap: 'round' }
            };
            if (!cvCharts.recuperacion) {
                cvCharts.recuperacion = new ApexCharts(document.querySelector('#cvChartRecuperacion'), opts);
                cvCharts.recuperacion.render();
            } else {
                cvCharts.recuperacion.updateOptions({ colors: [color] }, false, false);
                cvCharts.recuperacion.updateSeries([pct], true);
            }
        });
    }

    // ─── Render: donut de penetración ───────────────────────
    function renderDonutPenetracion(conC, sinC) {
        var total   = conC + sinC;
        var series  = total > 0 ? [conC, sinC] : [0, 1];
        var pal = cvChartPalette();
        var colors  = total > 0 ? [pal.success, pal.secondary] : ['#dde3ec', '#dde3ec'];
        ensureApex(function () {
            if (!cvCharts.penetracion) {
                cvCharts.penetracion = new ApexCharts(document.querySelector('#cvChartPenetracion'), {
                    chart: { type: 'donut', height: CV_KPI_CHART_PX, toolbar: { show: false }, animations: { speed: 380 } },
                    series: series,
                    labels: ['Con convenio', 'Sin convenio'],
                    colors: colors,
                    legend: { show: false },
                    dataLabels: { enabled: false },
                    plotOptions: { pie: { donut: { size: '60%' } } },
                    stroke: { width: 2, colors: [isDark() ? '#1e293b' : '#ffffff'] }
                });
                cvCharts.penetracion.render();
            } else {
                cvCharts.penetracion.updateOptions({ colors: colors, stroke: { width: 2, colors: [isDark() ? '#1e293b' : '#ffffff'] } }, false, true);
                cvCharts.penetracion.updateSeries(series, true);
            }
        });
    }

    // ─── Render: pie detalle por producto (drill-down) ──────
    function renderPieDetalle(rows) {
        ensureApex(function () {
            if (cvCharts.nuevosDetalle) {
                try { cvCharts.nuevosDetalle.destroy(); } catch (e) {}
                cvCharts.nuevosDetalle = null;
            }
            var el = document.querySelector('#cvChartNuevosDetalle');
            if (!el) return;
            var labels = []; var series = [];
            for (var i = 0; i < rows.length; i++) { labels.push(rows[i].nombre || '—'); series.push(nv(rows[i].cnt)); }
            if (!labels.length) { labels = ['Sin datos']; series = [1]; }
            var pieColors = [];
            for (var c = 0; c < labels.length; c++) { pieColors.push(CV_COLORS[c % CV_COLORS.length]); }
            if (labels.length === 1 && labels[0] === 'Sin datos') pieColors = ['#dde3ec'];
            cvCharts.nuevosDetalle = new ApexCharts(el, {
                chart: { type: 'pie', height: 280, toolbar: { show: false }, animations: { speed: 360 } },
                series: series, labels: labels, colors: pieColors,
                legend: {
                    position: 'bottom', fontSize: '11px', horizontalAlign: 'center',
                    itemMargin: { horizontal: 6, vertical: 2 },
                    onItemClick: { toggleDataSeries: false },
                    formatter: function (name, opts) { return name + ': ' + opts.w.globals.series[opts.seriesIndex]; }
                },
                plotOptions: { pie: { expandOnClick: true } },
                dataLabels: {
                    enabled: labels.length <= 12 && !(labels.length === 1 && labels[0] === 'Sin datos'),
                    formatter: function (val, opts) { return String(opts.w.config.series[opts.seriesIndex]); },
                    style: { fontSize: '11px', fontWeight: 600 }
                },
                tooltip: { y: { formatter: function (v) { return v + ' convenios'; } } },
                stroke: { width: 1, colors: [isDark() ? '#1e293b' : '#ffffff'] }
            });
            cvCharts.nuevosDetalle.render();
        });
    }

    /** Rango compacto para badges de KPIs (misma idea que CapHum / Gastos Cobranza). */
    function cvPeriodoBadgeText(d) {
        if (!d || !d.fecha_ini || !d.fecha_fin) return '—';
        var a = String(d.fecha_ini).replace(/T.*/, '').slice(0, 10);
        var b = String(d.fecha_fin).replace(/T.*/, '').slice(0, 10);
        function dmy(s) {
            var p = s.split('-');
            if (p.length !== 3) return s;
            return parseInt(p[2], 10) + '/' + parseInt(p[1], 10) + '/' + p[0];
        }
        return dmy(a) + ' – ' + dmy(b);
    }

    function setCvTopKpiPeriodBadges(d) {
        var t = cvPeriodoBadgeText(d);
        document.querySelectorAll('.cv-kpi-period-badge').forEach(function (el) {
            el.textContent = t;
        });
    }

    // ─── Pintar: datos de convenios (KPIs + nuevos) ─────────
    function pintarConvenios(d) {
        if (!d) return;
        var subTit = d.periodo_label && String(d.periodo_label).trim()
            ? String(d.periodo_label).trim()
            : ((d.fecha_ini && d.fecha_fin) ? ('Rango consultado: ' + d.fecha_ini + ' → ' + d.fecha_fin) : '—');
        setText('cvEstSubtitulo', subTit);
        setCvTopKpiPeriodBadges(d);
        setText('cvKpiActivos',     String(d.total_activos      ?? 0));
        setText('cvKpiCompletados', String(d.total_completados  ?? 0));
        setText('cvKpiCancelados',  String(d.total_cancelados   ?? 0));
        var rango = (d.fecha_ini && d.fecha_fin) ? (d.fecha_ini + ' → ' + d.fecha_fin) : '—';
        setText('cvNuevosRangoInline', rango);
        setText('cvNuevosActivos',     String(d.nuevos_activos     ?? 0));
        setText('cvNuevosCompletados', String(d.nuevos_completados ?? 0));
        setText('cvNuevosCancelados',  String(d.nuevos_cancelados  ?? 0));
        setText('cvNuevosTotal',       String(d.nuevos_total       ?? 0));
        renderBarNuevos(d);
    }

    // ─── Pintar: cierres / recuperación ─────────────────────
    function pintarCierres(d) {
        if (!d) return;
        var rango = (d.fecha_ini && d.fecha_fin) ? (d.fecha_ini + ' → ' + d.fecha_fin) : '—';
        setText('cvSemRangoInline', rango);
        setText('cvSemPagadas',    String(d.semanas_pagadas    ?? 0));
        setText('cvSemVencidas',   String(d.semanas_vencidas   ?? 0));
        setText('cvSemPendientes', String(d.semanas_pendientes ?? 0));
        setText('cvSemParciales',  String(d.semanas_parciales  ?? 0));
        setText('cvSemConciliar',  String(d.semanas_conciliar  ?? 0));
        setText('cvSemCanceladas', String(d.semanas_canceladas ?? 0));
        setText('cvMontoComp',  formatDinero(d.monto_comprometido ?? 0));
        setText('cvMontoRecup', formatDinero(d.monto_recuperado   ?? 0));
        setText('cvRecupLegendRecup', formatDinero(d.monto_recuperado   ?? 0));
        setText('cvRecupLegendComp',  formatDinero(d.monto_comprometido ?? 0));
        setBadge('cvBadgeRecuperacion', d.recuperacion_badge_text || '—', d.recuperacion_badge_class || '');
        renderRadialRecuperacion(Math.min(100, Math.max(0, nv(d.pct_recuperacion ?? 0))));
        renderBarSemanas(d);
    }

    // ─── Pintar: cobertura de convenios ─────────────────────
    function pintarAsignacion(d) {
        console.log('[cvAsignacion] datos recibidos:', JSON.parse(JSON.stringify(d || {})));
        if (!d) { console.warn('[cvAsignacion] datos vacíos/nulos'); return; }
        var pct   = Math.min(100, Math.max(0, nv(d.pct_penetracion ?? 0)));
        var pal = cvChartPalette();
        var color = pct >= 40 ? pal.success : (pct >= 20 ? pal.warning : pal.danger);
        var elPct = document.getElementById('cvPctPenetracion');
        if (elPct) {
            elPct.textContent = pct + '%';
            elPct.setAttribute('style', 'color:' + color + ';');
        }
        setBadge('cvBadgePenetracion', d.penetracion_badge_text || '—', d.penetracion_badge_class || '');
        setText('cvPenConConvenio', String(d.con_convenio_activo ?? 0));
        setText('cvPenSinConvenio', String(d.sin_convenio        ?? 0));
        renderDonutPenetracion(nv(d.con_convenio_activo ?? 0), nv(d.sin_convenio ?? 0));

        // ── Brecha hacia la siguiente meta ─────────────────────
        (function () {
            var conC  = nv(d.con_convenio_activo ?? 0);
            var sinC  = nv(d.sin_convenio ?? 0);
            var total = conC + sinC;
            var elTxt  = document.getElementById('cvBrechaTexto');
            var elSub  = document.getElementById('cvBrechaSubtexto');
            var elBar  = document.getElementById('cvBrechaBar');
            if (!elTxt || !elSub || !elBar) return;
            var pct = total > 0 ? (conC / total) * 100 : 0;
            // Determinar siguiente umbral
            var umbrales = [20, 40, 60, 80, 100];
            var meta = null;
            for (var i = 0; i < umbrales.length; i++) {
                if (pct < umbrales[i]) { meta = umbrales[i]; break; }
            }
            if (meta === null) {
                elTxt.textContent = '¡Cobertura máxima alcanzada!';
                elSub.textContent = '100% de la cartera tiene convenio activo.';
                elBar.style.width = '100%';
                elBar.style.background = pal.success;
                return;
            }
            var metaNombres = { 20: 'cobertura media', 40: 'cobertura alta', 60: '60%', 80: '80%', 100: '100%' };
            var necesarios = Math.ceil(total * (meta / 100)) - conC;
            necesarios = Math.max(0, necesarios);
            var progEnMeta = meta > 0 ? Math.min(100, (pct / meta) * 100) : 0;
            var barColor = meta <= 20 ? pal.danger : (meta <= 40 ? pal.warning : pal.success);
            elTxt.innerHTML = 'Faltan <strong style="color:' + barColor + ';font-size:14px;">' + necesarios.toLocaleString('es-MX') + ' convenios</strong><br>para alcanzar ' + (metaNombres[meta] || meta + '%');
            elSub.textContent = Math.round(progEnMeta) + '% del camino hacia el ' + meta + '% · Actual: ' + Math.round(pct * 10) / 10 + '%';
            elBar.style.width = progEnMeta + '%';
            elBar.style.background = barColor;
        })();

        // ── KPIs de entidades de despacho ──────────────────────
        setText('cvDespTotal',       String(d.total_despachos_activos ?? 0));
        setText('cvDespConConvenio', String(d.despachos_con_convenio   ?? 0));
        setText('cvDespSinConvenio', String(d.despachos_sin_convenio   ?? 0));
        setText('cvDespCelulaDesp',  String(d.celula_despacho_cnt      ?? 0));
        setText('cvDespCelulaCC',    String(d.celula_callcenter_cnt    ?? 0));
        setText('cvTopDespNombre',    d.top_despacho_nombre   || '—');
        setText('cvTopDespConvenios', String(d.top_despacho_convenios ?? 0));
        setText('cvBottomDespNombre',    d.bottom_despacho_nombre   || '—');
        setText('cvBottomDespConvenios', String(d.bottom_despacho_convenios ?? 0));
        setText('cvDespCreditosGestion', String(d.creditos_en_gestion      ?? 0));
        setText('cvDespPromedioConv',    String(d.promedio_convenios_gestor ?? 0));
        setText('cvDespEnMeta',          String(d.gestores_en_meta          ?? 0));
        setText('cvTopGestorPeriodoNombre',    d.top_gestor_periodo_nombre    || '—');
        setText('cvTopGestorPeriodoConvenios', String(d.top_gestor_periodo_convenios ?? 0));
    }

    // ─── Loading helpers ─────────────────────────────────────
    function showLoading() {
        if (typeof Swal !== 'undefined' && Swal && typeof Swal.fire === 'function') {
            cvLoadingOpen = true;
            Swal.fire({
                title: 'Cargando datos',
                text: 'Actualizando estadísticas de convenios...',
                allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false,
                didOpen: function () { if (Swal.showLoading) Swal.showLoading(); }
            });
            return;
        }
        setText('cvEstSubtitulo', 'Cargando datos...');
    }

    function hideLoading() {
        if (cvLoadingOpen && typeof Swal !== 'undefined' && Swal && typeof Swal.close === 'function') {
            Swal.close();
            cvLoadingOpen = false;
        }
    }

    // ─── Drill-down: desglose por producto ───────────────────
    function cerrarDetallePanel() {
        cvNuevoTipoAbierto = null;
        var w = document.getElementById('cvNuevosDetalleWrap');
        if (w) w.classList.add('d-none');
        document.querySelectorAll('.cv-nuevo-card').forEach(function (el) {
            el.classList.remove('cv-nuevo-card-active');
            el.setAttribute('aria-expanded', 'false');
        });
        var bar = document.getElementById('cvNuevosBarWrap');
        if (bar) bar.classList.remove('d-none');
        if (cvCharts.nuevosDetalle) {
            try { cvCharts.nuevosDetalle.destroy(); } catch (e) {}
            cvCharts.nuevosDetalle = null;
        }
    }

    function solicitarDetalle(tipo) {
        if (tipo === cvNuevoTipoAbierto) { cerrarDetallePanel(); return; }
        cvNuevoTipoAbierto = tipo;
        document.querySelectorAll('.cv-nuevo-card').forEach(function (el) {
            var t = el.getAttribute('data-cv-nuevo-tipo');
            el.classList.toggle('cv-nuevo-card-active', t === tipo);
            el.setAttribute('aria-expanded', t === tipo ? 'true' : 'false');
        });
        var bar = document.getElementById('cvNuevosBarWrap');
        if (bar) bar.classList.add('d-none');
        var w = document.getElementById('cvNuevosDetalleWrap');
        if (w) w.classList.remove('d-none');
        var titulos = {
            activos:    'Activos del período · por producto',
            completados:'Completados del período · por producto',
            cancelados: 'Cancelados del período · por producto'
        };
        setText('cvNuevosDetalleTitulo', (titulos[tipo] || tipo) + ' (cargando…)');
        setText('cvNuevosDetalleSub', '');
        var reqId = ++cvDetalleReqSeq;
        var pp = cvParamsPeriodo();
        postForm('/convenios/getEstadisticasConveniosDetalle', {
            anio: pp.anio,
            mes: pp.mes,
            fecha_inicio: pp.fecha_inicio,
            fecha_fin: pp.fecha_fin,
            tipo: tipo
        })
            .then(function (wrap) {
                if (reqId !== cvDetalleReqSeq || tipo !== cvNuevoTipoAbierto) return;
                var resp = wrap.json;
                if (!wrap.ok || !resp || !resp.success || !resp.datos) {
                    setText('cvNuevosDetalleTitulo', titulos[tipo] || tipo);
                    setText('cvNuevosDetalleSub', resp && resp.mensaje ? String(resp.mensaje) : 'Sin datos.');
                    renderPieDetalle([]);
                    return;
                }
                var dat = resp.datos;
                var suf = dat.total != null ? ' · Total: ' + dat.total : '';
                setText('cvNuevosDetalleTitulo', (titulos[tipo] || tipo) + suf);
                setText('cvNuevosDetalleSub', (dat.fecha_ini && dat.fecha_fin) ? dat.fecha_ini + ' → ' + dat.fecha_fin : '');
                renderPieDetalle(dat.por_producto || []);
            })
            .catch(function () {
                if (reqId !== cvDetalleReqSeq || tipo !== cvNuevoTipoAbierto) return;
                setText('cvNuevosDetalleTitulo', titulos[tipo] || tipo);
                setText('cvNuevosDetalleSub', 'Error de red al cargar el desglose.');
                renderPieDetalle([]);
            });
    }

    // ─── Refrescar todo el panel ─────────────────────────────
    function refrescar() {
        cerrarDetallePanel();
        showLoading();
        setText('cvEstSubtitulo', 'Actualizando…');
        var params = cvParamsPeriodo();
        var rConv    = ++cvReqConv;
        var rCierr   = ++cvReqCierr;
        var rAsig    = ++cvReqAsig;
        var pending  = 3;

        function checkDone() { pending--; if (pending === 0) hideLoading(); }

        postForm('/convenios/getEstadisticasConvenios', params)
            .then(function (wrap) {
                if (rConv !== cvReqConv) return;
                var resp = wrap.json;
                if (wrap.ok && resp && resp.success && resp.datos) {
                    pintarConvenios(resp.datos);
                } else {
                    setText('cvEstSubtitulo', resp && resp.mensaje ? resp.mensaje : 'Error al obtener convenios.');
                }
                checkDone();
            })
            .catch(function () {
                if (rConv !== cvReqConv) return;
                setText('cvEstSubtitulo', 'Error de red.');
                checkDone();
            });

        postForm('/convenios/getEstadisticasCierresCredito', params)
            .then(function (wrap) {
                if (rCierr !== cvReqCierr) return;
                var resp = wrap.json;
                if (wrap.ok && resp && resp.success && resp.datos) pintarCierres(resp.datos);
                checkDone();
            })
            .catch(function () { if (rCierr !== cvReqCierr) return; checkDone(); });

        postForm('/convenios/getEstadisticasAsignacionCreditos', params)
            .then(function (wrap) {
                if (rAsig !== cvReqAsig) return;
                var resp = wrap.json;
                console.log('[cvAsignacion] respuesta HTTP ok=' + wrap.ok, resp);
                if (!wrap.ok)       { console.error('[cvAsignacion] HTTP error', wrap); }
                else if (!resp)     { console.error('[cvAsignacion] respuesta no es JSON'); }
                else if (!resp.success) { console.error('[cvAsignacion] success=false msg=', resp.message, 'debug=', resp.debug); }
                else if (!resp.datos)   { console.error('[cvAsignacion] success=true pero datos vacíos'); }
                if (wrap.ok && resp && resp.success && resp.datos) pintarAsignacion(resp.datos);
                checkDone();
            })
            .catch(function (err) { if (rAsig !== cvReqAsig) return; console.error('[cvAsignacion] catch error', err); checkDone(); });
    }

    // ─── Inicialización ──────────────────────────────────────
    pintarConvenios(datosIni.convenios   || {});
    pintarCierres  (datosIni.cierres     || {});
    pintarAsignacion(datosIni.asignacion || {});

    scheduleInitFlatpickrCvEst();
    var btnCvFpReset = document.getElementById('btnCvEstRestablecerPeriodo');
    if (btnCvFpReset) {
        btnCvFpReset.addEventListener('click', function () {
            cvRestaurarPeriodoPorDefecto();
        });
    }

    document.querySelectorAll('.cv-nuevo-card').forEach(function (card) {
        card.addEventListener('click', function (e) {
            if (e.target.closest('.cv-nuevo-no-abrir')) return;
            var tipo = card.getAttribute('data-cv-nuevo-tipo');
            if (tipo) solicitarDetalle(tipo);
        });
        card.addEventListener('keydown', function (e) {
            if (e.key !== 'Enter' && e.key !== ' ') return;
            e.preventDefault();
            if (e.target.closest('.cv-nuevo-no-abrir')) return;
            var tipo = card.getAttribute('data-cv-nuevo-tipo');
            if (tipo) solicitarDetalle(tipo);
        });
    });

    var btnCerr = document.getElementById('cvNuevosDetalleCerrar');
    if (btnCerr) btnCerr.addEventListener('click', cerrarDetallePanel);

    // ─── Tooltips Bootstrap 5 ────────────────────────────────
    function initTooltips() {
        if (typeof bootstrap === 'undefined' || !bootstrap.Tooltip) return;
        document.querySelectorAll('[data-cv-tip="1"]').forEach(function (el) {
            if (el.getAttribute('data-cv-tip-inited') === '1') return;
            el.setAttribute('data-cv-tip-inited', '1');
            try { new bootstrap.Tooltip(el, { customClass: 'cv-tip-kpi', container: 'body', trigger: 'hover focus' }); } catch (e) {}
        });
    }
    initTooltips();
})();
</script>
