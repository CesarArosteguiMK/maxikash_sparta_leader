<?php
/** @var int    $anioDefault */
/** @var int    $mesDefault */
/** @var string $datosInicialesJson */
$anioDefault        = isset($anioDefault)        ? (int) $anioDefault        : (int) date('Y');
$mesDefault         = isset($mesDefault)         ? (int) $mesDefault         : (int) date('n');
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
        padding: 16px 20px;
        text-align: center;
        flex: 1 1 0;
        min-width: 140px;
    }
    @media (max-width: 991.98px) {
        .cv-kpi-strip > .cv-kpi-mini { flex: 1 1 calc(50% - 6px); min-width: 120px; }
    }
    /* Cards clickeables de nuevos */
    .cv-nuevo-card {
        cursor: pointer;
        border: 2px solid transparent !important;
        border-radius: 8px;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .cv-nuevo-card:hover { border-color: rgba(26,58,92,0.25) !important; box-shadow: 0 2px 8px rgba(26,58,92,0.08); }
    .cv-nuevo-card.cv-nuevo-card-active { border-color: #2ecc8b !important; box-shadow: 0 0 0 1px rgba(46,204,139,0.35); }
    .cv-nuevo-card:focus-visible { outline: 2px solid #2ecc8b; outline-offset: 2px; }
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
    .cv-sem-badge .cv-sem-num { font-size: 16px; font-weight: 800; }

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
    /* Tip buttons */
    html.dark-mode .cv-tip-btn { color: #475569 !important; }
    html.dark-mode .cv-tip-btn:hover,
    html.dark-mode .cv-tip-btn:focus { color: #94a3b8 !important; }
</style>


<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="cv-est-outer" style="background:#f0f4f8;padding:20px;border-radius:12px;">

            <!-- ── HEADER ──────────────────────────────────── -->
            <div style="background:linear-gradient(90deg,#1a3a5c 0%,#1a3a5c 65%,#0d5c3a 100%);border-radius:12px;padding:20px 24px 18px;margin-bottom:16px;">
                <div class="row align-items-start g-3">
                    <div class="col-lg-8">
                        <h4 style="color:#ffffff;font-size:18px;font-weight:700;margin:0 0 4px;">Estadística Convenios</h4>
                        <p id="cvEstSubtitulo" style="color:rgba(255,255,255,0.6);font-size:13px;margin:0;">—</p>
                        <p id="cvEstRangoFechas" style="color:rgba(255,255,255,0.45);font-size:11px;margin:6px 0 0;">—</p>
                    </div>
                    <div class="col-lg-4">
                        <div class="row g-2 justify-content-lg-end">
                            <div class="col-6">
                                <label for="cvEstAnio" style="color:rgba(255,255,255,0.6);font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.8px;display:block;margin-bottom:4px;">Año</label>
                                <select id="cvEstAnio" aria-label="Año" style="background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.3);border-radius:8px;color:#ffffff;padding:6px 10px;font-size:13px;font-weight:600;cursor:pointer;width:100%;"></select>
                            </div>
                            <div class="col-6">
                                <label for="cvEstMes" style="color:rgba(255,255,255,0.6);font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.8px;display:block;margin-bottom:4px;">Mes</label>
                                <select id="cvEstMes" aria-label="Mes" style="background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.3);border-radius:8px;color:#ffffff;padding:6px 10px;font-size:13px;font-weight:600;cursor:pointer;width:100%;"></select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── MAIN ROW ─────────────────────────────────── -->
            <div class="row g-3 mb-4 align-items-start">

                <!-- ── LEFT COL (col-lg-8) ─────────────────── -->
                <div class="col-lg-8">

                    <!-- KPI Global strip: Activos / Completados / Cancelados -->
                    <div class="cv-kpi-strip">
                        <div class="cv-kpi-mini">
                            <button type="button" class="cv-tip-btn" data-bs-toggle="tooltip" data-bs-placement="top" data-cv-tip="1"
                                title="Total de convenios en estatus Activo (todas las fechas, sin filtro de período)."
                                aria-label="Ayuda: convenios activos">
                                <i class="fa fa-info-circle" aria-hidden="true"></i>
                            </button>
                            <div style="font-size:12px;color:#6b7a90;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.5px;padding-right:16px;">Activos</div>
                            <div id="cvKpiActivos" style="font-size:32px;font-weight:800;color:#2ecc8b;line-height:1;">0</div>
                        </div>
                        <div class="cv-kpi-mini">
                            <button type="button" class="cv-tip-btn" data-bs-toggle="tooltip" data-bs-placement="top" data-cv-tip="1"
                                title="Total de convenios en estatus Completado: el cliente liquidó todas las semanas (todas las fechas)."
                                aria-label="Ayuda: convenios completados">
                                <i class="fa fa-info-circle" aria-hidden="true"></i>
                            </button>
                            <div style="font-size:12px;color:#6b7a90;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.5px;padding-right:16px;">Completados</div>
                            <div id="cvKpiCompletados" style="font-size:32px;font-weight:800;color:#3498db;line-height:1;">0</div>
                        </div>
                        <div class="cv-kpi-mini">
                            <button type="button" class="cv-tip-btn" data-bs-toggle="tooltip" data-bs-placement="top" data-cv-tip="1"
                                title="Total de convenios en estatus Cancelado, ya sea por incumplimiento automático o cancelación manual (todas las fechas)."
                                aria-label="Ayuda: convenios cancelados">
                                <i class="fa fa-info-circle" aria-hidden="true"></i>
                            </button>
                            <div style="font-size:12px;color:#6b7a90;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.5px;padding-right:16px;">Cancelados</div>
                            <div id="cvKpiCancelados" style="font-size:32px;font-weight:800;color:#e74c3c;line-height:1;">0</div>
                        </div>
                    </div>

                    <!-- Panel: Nuevos del período -->
                    <div class="cv-panel" style="position:relative;background:#f5f7fa;border:1px solid #dde3ec;border-radius:12px;padding:16px 18px;margin-bottom:16px;">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2" style="margin-bottom:10px;">
                            <div class="d-flex flex-wrap align-items-baseline gap-2" style="flex:1;min-width:0;">
                                <span style="font-size:10px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#2ecc8b;">Nuevos convenios del período</span>
                                <span id="cvNuevosRangoInline" style="font-size:11px;font-weight:600;color:#6b7a90;letter-spacing:0.02em;">—</span>
                            </div>
                            <button type="button" class="cv-tip-btn cv-tip-inline" data-bs-toggle="tooltip" data-bs-placement="left" data-cv-tip="1"
                                title="Convenios cuya fecha_alta cae dentro del mes y año seleccionados, desglosados por estatus. Haz clic en Activos, Completados o Cancelados para ver el desglose por producto de convenio."
                                aria-label="Ayuda: nuevos del período">
                                <i class="fa fa-info-circle" aria-hidden="true"></i>
                            </button>
                        </div>

                        <!-- Cards clickeables -->
                        <div class="d-flex flex-wrap gap-2 mb-2">
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
                                <div id="cvNuevosActivos" style="font-size:22px;font-weight:700;color:#2ecc8b;">0</div>
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
                                <div id="cvNuevosCompletados" style="font-size:22px;font-weight:700;color:#3498db;">0</div>
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
                                <div id="cvNuevosCancelados" style="font-size:22px;font-weight:700;color:#e74c3c;">0</div>
                            </div>
                            <div class="cv-nuevo-total" style="background:#eef1f5;border:1px solid #dde3ec;border-radius:8px;padding:10px 14px;text-align:center;flex:1;min-width:100px;">
                                <div style="font-size:11px;color:#6b7a90;margin-bottom:4px;">Total nuevos</div>
                                <div id="cvNuevosTotal" style="font-size:22px;font-weight:700;color:#1a3a5c;">0</div>
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

                    <!-- Panel: Semanas de pago del período -->
                    <div class="cv-panel-sem" style="background:#ffffff;border:1px solid #dde3ec;border-radius:12px;padding:16px 18px;">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex flex-wrap align-items-baseline gap-2" style="flex:1;min-width:0;">
                                <span style="font-size:10px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#2ecc8b;">Semanas de pago del período</span>
                                <span id="cvSemRangoInline" style="font-size:11px;font-weight:600;color:#6b7a90;">—</span>
                            </div>
                            <button type="button" class="cv-tip-btn cv-tip-inline" data-bs-toggle="tooltip" data-bs-placement="left" data-cv-tip="1"
                                title="Semanas de amortización (convenio_cliente_amortizacion) cuya fecha_pago cae dentro del mes seleccionado, agrupadas por estatus_pago."
                                aria-label="Ayuda: semanas de pago">
                                <i class="fa fa-info-circle" aria-hidden="true"></i>
                            </button>
                        </div>
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
                    </div>

                </div><!-- /col-lg-8 -->

                <!-- ── RIGHT COL (col-lg-4) ──────────────────── -->
                <div class="col-lg-4 d-flex flex-column" style="gap:16px;">

                    <!-- Tarjeta de recuperación (montos + radial) -->
                    <div class="cv-card-rec" style="background:#ffffff;border:1px solid #dde3ec;border-radius:12px;padding:16px 18px;">
                        <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-1">
                                <span style="font-size:10px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#2ecc8b;">Recuperación</span>
                                <button type="button" class="cv-tip-btn cv-tip-inline" data-bs-toggle="tooltip" data-bs-placement="left" data-cv-tip="1"
                                    title="Monto comprometido = suma de total_a_pagar de convenios activos y completados. Monto recuperado = suma de pago_semanal de amortizaciones con estatus_pago=pagado cuya fecha_pago cae en el período seleccionado."
                                    aria-label="Ayuda: recuperación">
                                    <i class="fa fa-info-circle" aria-hidden="true"></i>
                                </button>
                            </div>
                            <span id="cvBadgeRecuperacion" data-cv-state="" style="font-size:11px;font-weight:700;padding:3px 10px;border-radius:10px;display:inline-block;">—</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span style="font-size:12px;color:#6b7a90;">Comprometido total</span>
                            <span id="cvMontoComp" style="font-size:13px;font-weight:700;color:#1a3a5c;">$0.00</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span style="font-size:12px;color:#6b7a90;">Recuperado en período</span>
                            <span id="cvMontoRecup" style="font-size:13px;font-weight:700;color:#2ecc8b;">$0.00</span>
                        </div>
                        <div class="mt-3 pt-3 cv-sep" style="border-top:1px solid #dde3ec;">
                            <div style="font-size:10px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#6b7a90;margin-bottom:2px;text-align:center;">% Recuperación del período</div>
                            <div id="cvChartRecuperacion" style="min-height:180px;max-width:260px;margin:0 auto;"></div>
                            <div class="d-flex justify-content-center flex-wrap gap-3 mt-1" style="font-size:11px;color:#6b7a90;">
                                <span>Recuperado: <strong id="cvRecupLegendRecup">$0</strong></span>
                                <span>Comprometido: <strong id="cvRecupLegendComp">$0</strong></span>
                            </div>
                        </div>
                    </div>

                    <!-- Tarjeta de penetración de cartera -->
                    <div class="cv-card-pen" style="background:#f5f7fa;border:1px solid #dde3ec;border-radius:12px;padding:16px 18px;flex:1 1 auto;">
                        <div class="d-flex justify-content-between align-items-start mb-2 flex-wrap gap-2">
                            <div class="d-flex flex-wrap align-items-baseline gap-1" style="flex:1;min-width:0;">
                                <span style="font-size:10px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#2ecc8b;">Penetración de cartera</span>
                                <button type="button" class="cv-tip-btn cv-tip-inline" data-bs-toggle="tooltip" data-bs-placement="left" data-cv-tip="1"
                                    title="De los créditos activos en despacho (estatus=1 en asigna_creditos_despacho), el porcentaje que tiene convenio activo. Indica cuánta cartera en mora externa ya ha sido captada con convenio."
                                    aria-label="Ayuda: penetración de cartera">
                                    <i class="fa fa-info-circle" aria-hidden="true"></i>
                                </button>
                            </div>
                            <span id="cvBadgePenetracion" data-cv-state="" style="font-size:11px;font-weight:700;padding:3px 10px;border-radius:10px;display:inline-block;">—</span>
                        </div>
                        <div class="d-flex flex-column justify-content-center text-center pt-2">
                            <div id="cvPctPenetracion" style="font-size:28px;font-weight:800;color:#2ecc8b;">0%</div>
                            <div style="font-size:10px;color:#6b7a90;margin-top:6px;line-height:1.45;">
                                (Con convenio activo / total en despacho) × 100
                            </div>
                        </div>
                        <div class="mt-3 pt-3 cv-sep" style="border-top:1px solid #dde3ec;">
                            <div id="cvChartPenetracion" style="min-height:180px;max-width:260px;margin:0 auto;"></div>
                            <div class="d-flex justify-content-center flex-wrap gap-3 mt-1" style="font-size:11px;color:#6b7a90;">
                                <span>
                                    <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#2ecc8b;margin-right:4px;vertical-align:middle;"></span>
                                    Con convenio: <strong id="cvPenConConvenio">0</strong>
                                </span>
                                <span>
                                    <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#bdc3c7;margin-right:4px;vertical-align:middle;"></span>
                                    Sin convenio: <strong id="cvPenSinConvenio">0</strong>
                                </span>
                            </div>
                        </div>
                    </div>

                </div><!-- /col-lg-4 -->

            </div><!-- /row -->

            <!-- ── PANEL DESPACHOS ──────────────────────── -->
            <div class="cv-panel-desp" style="background:#ffffff;border:1px solid #dde3ec;border-radius:12px;padding:16px 18px;margin-top:0;">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <span style="font-size:10px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#2ecc8b;">Despachos</span>
                    <button type="button" class="cv-tip-btn cv-tip-inline" data-bs-toggle="tooltip" data-bs-placement="left" data-cv-tip="1"
                        title="Despachos activos registrados en el sistema, su distribución por célula (Despacho / Agente Call Center) y cuántos tienen al menos un convenio activo."
                        aria-label="Ayuda: despachos">
                        <i class="fa fa-info-circle" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="row g-2">
                    <!-- Totales despacho -->
                    <div class="col-6 col-md-4 col-xl-2">
                        <div class="cv-desp-kpi" style="background:#f5f7fa;border:1px solid #dde3ec;border-radius:10px;padding:14px 16px;text-align:center;height:100%;">
                            <div style="font-size:11px;color:#6b7a90;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.5px;">Total despachos</div>
                            <div id="cvDespTotal" style="font-size:28px;font-weight:800;color:#1a3a5c;line-height:1;">0</div>
                        </div>
                    </div>
                    <!-- Con convenio -->
                    <div class="col-6 col-md-4 col-xl-2">
                        <div class="cv-desp-kpi" style="background:#f5f7fa;border:1px solid #dde3ec;border-radius:10px;padding:14px 16px;text-align:center;height:100%;">
                            <div style="font-size:11px;color:#6b7a90;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.5px;">Con convenios activos</div>
                            <div id="cvDespConConvenio" style="font-size:28px;font-weight:800;color:#2ecc8b;line-height:1;">0</div>
                        </div>
                    </div>
                    <!-- Sin convenio -->
                    <div class="col-6 col-md-4 col-xl-2">
                        <div class="cv-desp-kpi" style="background:#f5f7fa;border:1px solid #dde3ec;border-radius:10px;padding:14px 16px;text-align:center;height:100%;">
                            <div style="font-size:11px;color:#6b7a90;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.5px;">Sin convenios</div>
                            <div id="cvDespSinConvenio" style="font-size:28px;font-weight:800;color:#e74c3c;line-height:1;">0</div>
                        </div>
                    </div>
                    <!-- Célula Despacho -->
                    <div class="col-6 col-md-4 col-xl-2">
                        <div class="cv-desp-kpi" style="background:#f5f7fa;border:1px solid #dde3ec;border-radius:10px;padding:14px 16px;text-align:center;height:100%;">
                            <div style="font-size:11px;color:#6b7a90;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.5px;">
                                <i class="fa fa-building fa-sm" aria-hidden="true" style="margin-right:3px;"></i>Despachos
                            </div>
                            <div id="cvDespCelulaDesp" style="font-size:28px;font-weight:800;color:#3498db;line-height:1;">0</div>
                        </div>
                    </div>
                    <!-- Célula Call Center -->
                    <div class="col-6 col-md-4 col-xl-2">
                        <div class="cv-desp-kpi" style="background:#f5f7fa;border:1px solid #dde3ec;border-radius:10px;padding:14px 16px;text-align:center;height:100%;">
                            <div style="font-size:11px;color:#6b7a90;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.5px;">
                                <i class="fa fa-phone fa-sm" aria-hidden="true" style="margin-right:3px;"></i>Agente Call Center
                            </div>
                            <div id="cvDespCelulaCC" style="font-size:28px;font-weight:800;color:#9b59b6;line-height:1;">0</div>
                        </div>
                    </div>
                    <!-- Top despacho -->
                    <div class="col-12 col-md-8 col-xl-4">
                        <div class="cv-top-desp" style="background:#eafaf3;border:1px solid rgba(46,204,139,0.3);border-radius:10px;padding:14px 16px;height:100%;display:flex;flex-direction:column;justify-content:center;">
                            <div style="font-size:10px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#6b7a90;margin-bottom:8px;">
                                <i class="fa fa-trophy fa-sm" aria-hidden="true" style="color:#f39c12;margin-right:4px;"></i>Despacho con más convenios
                            </div>
                            <div id="cvTopDespNombre" style="font-size:15px;font-weight:700;color:#1a3a5c;line-height:1.3;word-break:break-word;">—</div>
                            <div style="margin-top:6px;font-size:12px;color:#6b7a90;">
                                Convenios activos: <strong id="cvTopDespConvenios" style="color:#0d5c3a;">0</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- /panel despachos -->
        </div>
    </div>
</div>

<script>
(function () {
    var anioSel  = <?php echo json_encode($anioDefault); ?>;
    var mesSel   = <?php echo json_encode($mesDefault); ?>;
    var datosIni = <?php echo $datosInicialesJson; ?>;

    var ST_BADGE_VERDE    = 'background:#d4f5e7;color:#0d5c3a;font-size:11px;font-weight:700;padding:3px 10px;border-radius:10px;display:inline-block;';
    var ST_BADGE_AMARILLO = 'background:#fef3cd;color:#7a5000;font-size:11px;font-weight:700;padding:3px 10px;border-radius:10px;display:inline-block;';
    var ST_BADGE_ROJO     = 'background:#fde8e8;color:#7a1111;font-size:11px;font-weight:700;padding:3px 10px;border-radius:10px;display:inline-block;';

    var CV_COLORS = ['#1a3a5c','#2ecc8b','#3498db','#e74c3c','#f39c12','#9b59b6','#1abc9c','#e67e22','#34495e','#16a085','#27ae60','#2980b9','#8e44ad'];

    var cvCharts            = { nuevos: null, nuevosDetalle: null, recuperacion: null, penetracion: null };
    var cvNuevoTipoAbierto  = null;
    var cvDetalleReqSeq     = 0;
    var cvReqConv           = 0;
    var cvReqCierr          = 0;
    var cvReqAsig           = 0;
    var cvApexLoading       = false;
    var cvLoadingOpen       = false;

    // ─── Helpers ────────────────────────────────────────────
    function mesNombre(m) {
        var n = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
        return n[m] || String(m);
    }

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

    // ─── Año / Mes selects ──────────────────────────────────
    function llenarAnios() {
        var sel = document.getElementById('cvEstAnio');
        if (!sel) return;
        var now = new Date(); var y0 = now.getFullYear();
        var desde = y0 - 3; var hasta = y0;
        if (anioSel > hasta) anioSel = hasta;
        sel.innerHTML = '';
        for (var y = desde; y <= hasta; y++) {
            var o = document.createElement('option');
            o.value = String(y); o.textContent = String(y);
            o.setAttribute('style', 'color:#1a3a5c;background:#ffffff;');
            if (y === anioSel) o.selected = true;
            sel.appendChild(o);
        }
    }

    function llenarMeses() {
        var sel = document.getElementById('cvEstMes');
        if (!sel) return;
        var now = new Date(); var y = anioSel;
        var mesMax = (y === now.getFullYear()) ? (now.getMonth() + 1) : 12;
        if (mesSel > mesMax) mesSel = mesMax;
        if (mesSel < 1) mesSel = 1;
        sel.innerHTML = '';
        for (var m = 1; m <= mesMax; m++) {
            var o = document.createElement('option');
            o.value = String(m); o.textContent = mesNombre(m) + ' ' + y;
            o.setAttribute('style', 'color:#1a3a5c;background:#ffffff;');
            if (m === mesSel) o.selected = true;
            sel.appendChild(o);
        }
    }

    // ─── ApexCharts lazy load ───────────────────────────────
    function ensureApex(cb) {
        if (typeof ApexCharts !== 'undefined') { if (cb) cb(); return; }
        if (cvApexLoading) { return; }
        cvApexLoading = true;
        var s = document.createElement('script');
        s.src = '/assets/vendor/libs/apex-charts/apexcharts.js';
        s.onload  = function () { cvApexLoading = false; if (cb) cb(); };
        s.onerror = function () { cvApexLoading = false; };
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

    // ─── Render: barra de nuevos del período ────────────────
    function renderBarNuevos(d) {
        ensureApex(function () {
            var series = [nv(d.nuevos_activos), nv(d.nuevos_completados), nv(d.nuevos_cancelados)];
            if (!cvCharts.nuevos) {
                cvCharts.nuevos = new ApexCharts(document.querySelector('#cvChartNuevos'), {
                    chart: { type: 'bar', height: 220, toolbar: { show: false } },
                    series: [{ name: 'Nuevos', data: series }],
                    xaxis: { categories: ['Activos', 'Completados', 'Cancelados'] },
                    colors: ['#2ecc8b', '#3498db', '#e74c3c'],
                    dataLabels: { enabled: false },
                    plotOptions: { bar: { borderRadius: 6, columnWidth: '48%', distributed: true } },
                    legend: { show: false },
                    grid: { borderColor: isDark() ? '#334155' : '#eef1f5' }
                });
                cvCharts.nuevos.render();
            } else {
                cvCharts.nuevos.updateOptions({
                    xaxis: { categories: ['Activos', 'Completados', 'Cancelados'] },
                    colors: ['#2ecc8b', '#3498db', '#e74c3c'],
                    plotOptions: { bar: { borderRadius: 6, columnWidth: '48%', distributed: true } }
                }, false, true);
                cvCharts.nuevos.updateSeries([{ name: 'Nuevos', data: series }], true);
            }
        });
    }

    // ─── Render: radial de recuperación ─────────────────────
    function renderRadialRecuperacion(pct) {
        var color = pct >= 60 ? '#2ecc8b' : (pct >= 30 ? '#f0a500' : '#e74c3c');
        ensureApex(function () {
            var opts = {
                chart: { type: 'radialBar', height: 180, toolbar: { show: false }, animations: { speed: 400 } },
                series: [pct],
                labels: ['Recuperación'],
                colors: [color],
                plotOptions: {
                    radialBar: {
                        hollow: { size: '62%', background: 'transparent' },
                        track: { background: isDark() ? '#334155' : '#e9ecef', strokeWidth: '100%' },
                        dataLabels: {
                            name: { show: true, fontSize: '11px', fontWeight: 600, color: isDark() ? '#94a3b8' : '#6b7a90', offsetY: 14 },
                            value: {
                                show: true, fontSize: '22px', fontWeight: 800, color: isDark() ? '#e2e8f0' : '#1a3a5c', offsetY: -10,
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
        var colors  = total > 0 ? ['#2ecc8b', '#bdc3c7'] : ['#dde3ec', '#dde3ec'];
        ensureApex(function () {
            if (!cvCharts.penetracion) {
                cvCharts.penetracion = new ApexCharts(document.querySelector('#cvChartPenetracion'), {
                    chart: { type: 'donut', height: 180, toolbar: { show: false }, animations: { speed: 380 } },
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

    // ─── Pintar: datos de convenios (KPIs + nuevos) ─────────
    function pintarConvenios(d) {
        if (!d) return;
        setText('cvEstSubtitulo',    d.periodo_label || '—');
        if (d.fecha_ini && d.fecha_fin) {
            setText('cvEstRangoFechas', 'Rango consultado: ' + d.fecha_ini + ' → ' + d.fecha_fin);
        } else {
            setText('cvEstRangoFechas', '—');
        }
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
    }

    // ─── Pintar: penetración de cartera ─────────────────────
    function pintarAsignacion(d) {
        if (!d) return;
        var pct   = Math.min(100, Math.max(0, nv(d.pct_penetracion ?? 0)));
        var color = pct >= 40 ? '#2ecc8b' : (pct >= 20 ? '#f0a500' : '#e74c3c');
        var elPct = document.getElementById('cvPctPenetracion');
        if (elPct) {
            elPct.textContent = pct + '%';
            elPct.setAttribute('style', 'font-size:28px;font-weight:800;color:' + color + ';');
        }
        setBadge('cvBadgePenetracion', d.penetracion_badge_text || '—', d.penetracion_badge_class || '');
        setText('cvPenConConvenio', String(d.con_convenio_activo ?? 0));
        setText('cvPenSinConvenio', String(d.sin_convenio        ?? 0));
        renderDonutPenetracion(nv(d.con_convenio_activo ?? 0), nv(d.sin_convenio ?? 0));

        // ── KPIs de entidades de despacho ──────────────────────
        setText('cvDespTotal',       String(d.total_despachos_activos ?? 0));
        setText('cvDespConConvenio', String(d.despachos_con_convenio   ?? 0));
        setText('cvDespSinConvenio', String(d.despachos_sin_convenio   ?? 0));
        setText('cvDespCelulaDesp',  String(d.celula_despacho_cnt      ?? 0));
        setText('cvDespCelulaCC',    String(d.celula_callcenter_cnt    ?? 0));
        setText('cvTopDespNombre',    d.top_despacho_nombre   || '—');
        setText('cvTopDespConvenios', String(d.top_despacho_convenios ?? 0));
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
        var mes = parseInt(document.getElementById('cvEstMes').value, 10);
        if (isNaN(mes)) mes = mesSel;
        var reqId = ++cvDetalleReqSeq;
        postForm('/convenios/getEstadisticasConveniosDetalle', { anio: anioSel, mes: mes, tipo: tipo })
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
        var mes = parseInt(document.getElementById('cvEstMes').value, 10);
        if (isNaN(mes)) mes = mesSel;
        var ay = document.getElementById('cvEstAnio');
        if (ay) { var y = parseInt(ay.value, 10); if (!isNaN(y)) anioSel = y; }
        cerrarDetallePanel();
        showLoading();
        setText('cvEstSubtitulo', 'Actualizando…');
        var params   = { anio: anioSel, mes: mes };
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
                    setText('cvEstRangoFechas', '');
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
                if (wrap.ok && resp && resp.success && resp.datos) pintarAsignacion(resp.datos);
                checkDone();
            })
            .catch(function () { if (rAsig !== cvReqAsig) return; checkDone(); });
    }

    // ─── Inicialización ──────────────────────────────────────
    llenarAnios();
    llenarMeses();
    pintarConvenios(datosIni.convenios   || {});
    pintarCierres  (datosIni.cierres     || {});
    pintarAsignacion(datosIni.asignacion || {});

    document.getElementById('cvEstAnio').addEventListener('change', function () {
        anioSel = parseInt(this.value, 10);
        if (isNaN(anioSel)) anioSel = new Date().getFullYear();
        llenarMeses();
        refrescar();
    });
    document.getElementById('cvEstMes').addEventListener('change', function () {
        mesSel = parseInt(this.value, 10);
        if (isNaN(mesSel)) mesSel = 1;
        refrescar();
    });

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
