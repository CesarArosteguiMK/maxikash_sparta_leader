<style>
    /* Liquid glass suave: compatible con tema global (sin segundo modo oscuro) */
    .estad-sabueso-wrap .estad-glass {
        background: rgba(255, 255, 255, 0.55);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        border: 1px solid rgba(255, 255, 255, 0.7);
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
        border-radius: 1rem;
    }
    [data-bs-theme="dark"] .estad-sabueso-wrap .estad-glass,
    .dark-style .estad-sabueso-wrap .estad-glass {
        background: rgba(30, 34, 44, 0.65);
        border-color: rgba(255, 255, 255, 0.08);
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.25);
    }
    .estad-sabueso-wrap .estad-kpi-card {
        border-radius: 1rem;
        overflow: hidden;
        transition: box-shadow 0.2s ease, transform 0.2s ease;
    }
    .estad-sabueso-wrap .estad-kpi-card:hover { transform: translateY(-2px); }
    .estad-sabueso-wrap .estad-icon-box {
        width: 2.5rem; height: 2.5rem; border-radius: 0.6rem;
        display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
    }
    .estad-sabueso-wrap .estad-num-big {
        font-size: 2.5rem; font-weight: 800; line-height: 1.1; letter-spacing: -0.03em;
    }
    .estad-sabueso-wrap .estad-time-icon {
        width: 3rem; height: 3rem; border-radius: 0.85rem;
        display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;
    }
    .estad-sabueso-wrap .estad-time-value { font-size: 1.5rem; font-weight: 800; letter-spacing: -0.02em; }
    /* Pills período */
    .estad-sabueso-wrap .estad-pill-group {
        background: rgba(var(--bs-primary-rgb), 0.08);
        border-radius: 0.65rem; padding: 0.25rem; display: inline-flex; gap: 0.25rem;
    }
    .estad-sabueso-wrap .estad-pill-group .btn {
        border: none; border-radius: 0.5rem; padding: 0.35rem 0.85rem;
        font-size: 0.8rem; font-weight: 500; background: transparent !important;
    }
    .estad-sabueso-wrap .estad-pill-group .btn.active {
        font-weight: 700;
        background-color: var(--bs-primary) !important;
        color: #fff !important;
        box-shadow: 0 2px 8px rgba(var(--bs-primary-rgb), 0.35);
    }
    /* Lista tickets levantados: filas tipo “card” */
    .estad-sabueso-wrap .estad-period-list { padding: 0.5rem 1rem 1rem; }
    .estad-sabueso-wrap .estad-period-row {
        display: flex; align-items: center; gap: 1rem;
        padding: 0.65rem 1rem; margin-bottom: 0.5rem;
        border-radius: 0.75rem;
        background: rgba(var(--bs-primary-rgb), 0.04);
        border: 1px solid rgba(var(--bs-primary-rgb), 0.08);
        transition: background 0.15s ease, border-color 0.15s ease;
    }
    .estad-sabueso-wrap .estad-period-row:hover {
        background: rgba(var(--bs-primary-rgb), 0.08);
        border-color: rgba(var(--bs-primary-rgb), 0.15);
    }
    .estad-sabueso-wrap .estad-period-label {
        min-width: 5.5rem; font-weight: 600; font-size: 0.9rem;
    }
    .estad-sabueso-wrap .estad-mini-bar {
        flex: 1; height: 8px; border-radius: 999px;
        background: var(--bs-tertiary-bg, rgba(0,0,0,0.06));
        overflow: hidden; max-width: 100%;
    }
    .estad-sabueso-wrap .estad-mini-bar > div {
        height: 100%; border-radius: 999px;
        background: linear-gradient(90deg, var(--bs-primary), rgba(var(--bs-primary-rgb), 0.75));
        transition: width 0.9s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .estad-sabueso-wrap .estad-period-val {
        font-weight: 800; font-size: 1.1rem; min-width: 2.5rem; text-align: right;
        color: var(--bs-primary);
    }
    /* Gestores: avatar + tabla */
    .estad-sabueso-wrap .estad-avatar {
        width: 1.75rem; height: 1.75rem; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 0.55rem; font-weight: 800; color: #fff; flex-shrink: 0;
    }
    .estad-sabueso-wrap .estad-pill-gestor .btn.active {
        background-color: var(--bs-success) !important;
        color: #fff !important;
        box-shadow: 0 2px 8px rgba(25, 135, 84, 0.35);
    }
    .estad-sabueso-wrap .estad-global-tile {
        border-radius: 0.75rem; padding: 0.9rem 1rem;
        border: 1px solid transparent;
    }
    @media (max-width: 767px) {
        .estad-sabueso-wrap .estad-num-big { font-size: 2rem; }
    }
    /* Tooltips pequeños en encabezados de tablas estadísticas */
    .estad-sabueso-wrap .estad-th-tip {
        cursor: help;
        font-size: 0.65rem;
        opacity: 0.75;
        vertical-align: middle;
    }
    .estad-sabueso-wrap .estad-th-tip:hover { opacity: 1; }
    .tooltip.estad-tooltip-custom .tooltip-inner {
        font-size: 0.72rem;
        max-width: 300px;
        text-align: left;
        line-height: 1.35;
        padding: 0.4rem 0.5rem;
    }
    /* Tabla Sabueso: misma sensación que Por gestor (filas con hover suave) */
    .estad-sabueso-wrap #tablaPorSabueso tbody tr.estad-sabueso-fila:hover {
        background: rgba(var(--bs-success-rgb), 0.06);
    }
    /* Modal detalle (Swal) más limpio */
    .swal2-popup.estad-detalle-swal {
        padding: 0;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 12px 40px rgba(0,0,0,0.15);
    }
    .swal2-popup.estad-detalle-swal .swal2-title {
        margin: 0;
        padding: 1rem 1.25rem;
        background: linear-gradient(135deg, rgba(13,110,253,0.08) 0%, rgba(25,135,84,0.06) 100%);
        border-bottom: 1px solid rgba(0,0,0,0.08);
        font-size: 1.05rem;
        font-weight: 700;
        color: #0f5132;
    }
    .swal2-popup.estad-detalle-swal .swal2-html-container {
        margin: 0;
        padding: 0;
        text-align: left;
    }
    .swal2-popup.estad-detalle-swal .swal2-actions {
        margin: 0;
        padding: 0.75rem 1.25rem 1rem;
        background: var(--bs-tertiary-bg, #f8f9fa);
        border-top: 1px solid var(--bs-border-color, #dee2e6);
    }
    .estad-modal-detalle-wrap { padding: 0; }
    .estad-modal-detalle-leyenda {
        font-size: 0.75rem;
        line-height: 1.45;
        padding: 0.75rem 1rem;
        background: rgba(13, 110, 253, 0.06);
        border-bottom: 1px solid rgba(0,0,0,0.06);
        color: #495057;
    }
    .estad-modal-detalle-table-wrap {
        max-height: 380px;
        overflow: auto;
        margin: 0;
    }
    .estad-modal-detalle-table-wrap table {
        font-size: 0.8rem;
        margin-bottom: 0;
    }
    .estad-modal-detalle-table-wrap thead th {
        position: sticky;
        top: 0;
        z-index: 1;
        background: rgba(25,135,84,0.1);
        border-bottom: 2px solid rgba(25,135,84,0.2);
        white-space: nowrap;
        font-weight: 600;
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        padding: 0.5rem 0.4rem;
    }
    .estad-modal-detalle-table-wrap tbody td {
        padding: 0.45rem 0.4rem;
        vertical-align: middle;
    }
    .estad-modal-detalle-table-wrap tbody tr:hover {
        background: rgba(25,135,84,0.05);
    }
    .estad-modal-th-tip {
        cursor: help;
        opacity: 0.7;
        font-size: 0.7rem;
        margin-left: 2px;
        vertical-align: middle;
    }
    .estad-modal-th-tip:hover { opacity: 1; }
    /* Tickets levantados 35% | Por quien levantó 65% (solo lg+) */
    .estad-sabueso-wrap .estad-row-split-40-60 {
        --estad-col-left: 35%;
        --estad-col-right: 65%;
    }
    @media (min-width: 992px) {
        .estad-sabueso-wrap .estad-row-split-40-60 > .estad-col-40 {
            flex: 0 0 var(--estad-col-left) !important;
            max-width: var(--estad-col-left) !important;
            width: var(--estad-col-left);
        }
        .estad-sabueso-wrap .estad-row-split-40-60 > .estad-col-60 {
            flex: 0 0 var(--estad-col-right) !important;
            max-width: var(--estad-col-right) !important;
            width: var(--estad-col-right);
        }
    }
</style>

<div class="alert alert-warning d-none mb-3" id="estadisticasSabuesoAlert" role="alert"></div>

<!-- Sin spinner aquí: http.request usa showLoader:false para no duplicar con Swal "Procesando..." -->
<div id="estadisticasSabuesoCargando" class="estad-glass mb-3" style="display: none;">
    <div class="p-4 text-center text-muted">
        <p class="mb-0">Cargando estadísticas…</p>
    </div>
</div>

<div id="estadisticasSabuesoContenido" class="estad-sabueso-wrap" style="display: none;">

    <div class="row g-3 mb-3">
        <div class="col-lg-4 col-md-6">
            <div class="card h-100 border-0 estad-glass estad-kpi-card">
                <div class="card-body position-relative pt-4 pb-3">
                    <div class="position-absolute top-0 end-0 mt-3 me-3 estad-icon-box bg-primary bg-opacity-10 text-primary">
                        <i class="fa-solid fa-ticket"></i>
                    </div>
                    <div class="text-muted small mb-1">Tickets activos</div>
                    <div class="estad-num-big text-body" id="statTotalActivos">0</div>
                    <div class="d-flex align-items-center gap-2 mt-3">
                        <div class="progress flex-grow-1 rounded-pill" style="height: 6px;">
                            <div class="progress-bar bg-primary rounded-pill" id="barActivos" style="width: 0%"></div>
                        </div>
                        <span class="small fw-bold text-primary text-nowrap" id="pctActivos">0%</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="card h-100 border-0 estad-glass estad-kpi-card">
                <div class="card-body position-relative pt-4 pb-3">
                    <div class="position-absolute top-0 end-0 mt-3 me-3 estad-icon-box bg-success bg-opacity-10 text-success">
                        <i class="fa-solid fa-paper-plane"></i>
                    </div>
                    <div class="text-muted small mb-1">Con dictamen enviado</div>
                    <div class="estad-num-big text-body" id="statDictamenEnviado">0</div>
                    <div class="d-flex align-items-center gap-2 mt-3">
                        <div class="progress flex-grow-1 rounded-pill" style="height: 6px;">
                            <div class="progress-bar bg-success rounded-pill" id="barEnviado" style="width: 0%"></div>
                        </div>
                        <span class="small fw-bold text-success text-nowrap" id="pctEnviado">0%</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12">
            <div class="card h-100 border-0 estad-glass estad-kpi-card">
                <div class="card-body position-relative pt-4 pb-3">
                    <div class="position-absolute top-0 end-0 mt-3 me-3 estad-icon-box text-white" style="background: rgba(111, 66, 193, 0.35);">
                        <i class="fa-solid fa-eye"></i>
                    </div>
                    <div class="text-muted small mb-1">Dictamen ya visto</div>
                    <div class="estad-num-big text-body" id="statDictamenVisto">0</div>
                    <div class="d-flex align-items-center gap-2 mt-3">
                        <div class="progress flex-grow-1 rounded-pill" style="height: 6px;">
                            <div class="progress-bar rounded-pill" id="barVisto" style="width: 0%; background-color: #6f42c1;"></div>
                        </div>
                        <span class="small fw-bold text-nowrap" id="pctVisto" style="color: #6f42c1;">0%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="card border-0 estad-glass h-100">
                <div class="card-body d-flex align-items-center gap-3 py-4">
                    <!-- Reloj de arena: FA5/6 + fallback FA4 -->
                    <div class="estad-time-icon" style="background: rgba(245, 158, 11, 0.18); color: #f59e0b;">
                        <i class="fa fa-hourglass-half" aria-hidden="true"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="text-muted small">Tiempo hasta enviar dictamen (equipo Sabueso)</div>
                        <div class="estad-time-value text-warning" id="statTiempoSabuesoValor">—</div>
                        <div class="text-muted small mt-1" id="statTiempoSabuesoSub">Desde primera asignación hasta envío al gestor</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 estad-glass h-100">
                <div class="card-body d-flex align-items-center gap-3 py-4">
                    <div class="estad-time-icon" style="background: rgba(25, 135, 84, 0.18); color: #198754;">
                        <i class="fa fa-bolt"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="text-muted small">Tiempo del gestor en abrir el dictamen</div>
                        <div class="estad-time-value text-success" id="statTiempoGestorValor">—</div>
                        <div class="text-muted small mt-1" id="statTiempoGestorSub">Desde envío hasta visto por gestor</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3 estad-row-split-40-60">
        <!-- Tickets levantados: 40% -->
        <div class="col-12 estad-col-40">
            <div class="card border-0 estad-glass h-100 overflow-hidden">
                <div class="card-header border-0 d-flex flex-wrap justify-content-between align-items-center gap-2 py-3 bg-transparent">
                    <div>
                        <div class="fw-semibold"><i class="fa-solid fa-calendar-days me-1 text-primary"></i>Tickets levantados</div>
                        <div class="text-muted small">Conteo por período seleccionado</div>
                    </div>
                    <div class="estad-pill-group" id="grpFiltroPeriodo" role="group">
                        <button type="button" class="btn active" data-key="por_dia">Días</button>
                        <button type="button" class="btn" data-key="por_semana">Semanas</button>
                        <button type="button" class="btn" data-key="por_mes">Meses</button>
                        <button type="button" class="btn" data-key="por_anio">Año</button>
                    </div>
                </div>
                <div class="estad-period-list" id="estadPeriodList"></div>
            </div>
        </div>
        <!-- Por quien levantó el ticket: 65% -->
        <div class="col-12 estad-col-60">
            <div class="card border-0 estad-glass h-100 overflow-hidden">
                <div class="card-header border-0 py-3 bg-transparent d-flex flex-wrap justify-content-between align-items-start gap-2">
                    <div>
                        <div class="fw-semibold"><i class="fa-solid fa-users me-1 text-success"></i>Por quien levantó el ticket</div>
                    </div>
                    <div class="estad-pill-group estad-pill-gestor flex-wrap" id="grpResumenGestor" role="group">
                        <button type="button" class="btn active" data-tab="global">Global</button>
                        <button type="button" class="btn" data-tab="gestor">Por gestor (levantó)</button>
                        <button type="button" class="btn" data-tab="sabueso">Por Sabueso (dictaminó)</button>
                    </div>
                    <!-- Guía en 10 segundos: dónde está cada cosa -->
                    <div class="w-100 mt-2 p-2 rounded-2 border" style="background: rgba(13,110,253,0.06); border-color: rgba(13,110,253,0.15) !important; font-size: 0.8rem;">
                        <strong class="text-primary"><i class="fa-solid fa-compass me-1"></i>Cómo leer esto (rápido)</strong>
                        <ul class="mb-0 mt-1 ps-3">
                            <li><strong>Sin leer / Tasa</strong> en <em>tarjetas</em> abajo = vista <strong>Global</strong> (todos los tickets).</li>
                            <li><strong>Sin leer / Tasa por persona</strong> = pulse el botón <strong>Por gestor (levantó)</strong> y verá una <em>tabla</em> con una fila por gestor (columnas con esos nombres).</li>
                            <li><strong>Quién abrió y quién no</strong> = en esa tabla, haga <strong>clic en el nombre</strong>; en el popup, columna <strong>¿Abrió?</strong> (Sí/No).</li>
                            <li><strong>Resultado visita / pago</strong> = en el mismo popup, columnas <strong>Resultado DS</strong> y <strong>% efect.</strong> (si sale «Sin dictamen sistema», aún no se ha generado el dictamen automático para ese ticket).</li>
                        </ul>
                    </div>
                </div>
                <div class="card-body pt-0" id="panelResumenGlobal">
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="estad-global-tile bg-primary bg-opacity-10">
                                <div class="text-muted small mb-1"><i class="fa-solid fa-eye me-1"></i>Lectura prom.</div>
                                <div class="fw-bold text-primary" id="tileTiempoLectura">—</div>
                                <div class="text-muted" style="font-size: 0.7rem;" id="tileTiempoLecturaSub">n=0</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="estad-global-tile bg-success bg-opacity-10">
                                <div class="text-muted small mb-1"><i class="fa-solid fa-paper-plane me-1"></i>Envío prom.</div>
                                <div class="fw-bold text-success" id="tileTiempoEnvio">—</div>
                                <div class="text-muted" style="font-size: 0.7rem;" id="tileTiempoEnvioSub">n=0</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="estad-global-tile bg-warning bg-opacity-10">
                                <div class="text-muted small mb-1"><i class="fa-solid fa-triangle-exclamation me-1"></i>Sin leer</div>
                                <div class="fw-bold text-warning" id="tileSinLeer">0</div>
                                <div class="text-muted" style="font-size: 0.7rem;">Enviado, no visto</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="estad-global-tile" style="background: rgba(111, 66, 193, 0.1);">
                                <div class="text-muted small mb-1"><i class="fa-solid fa-check me-1"></i>Tasa lectura</div>
                                <div class="fw-bold" style="color: #6f42c1;" id="tileTasa">—</div>
                                <div class="text-muted" style="font-size: 0.7rem;" id="tileTasaSub">—</div>
                            </div>
                        </div>
                        <div class="col-12 mt-2">
                            <div class="rounded-2 p-2" style="background: rgba(25, 135, 84, 0.06); border: 1px solid rgba(25, 135, 84, 0.12);">
                                <div class="text-muted small mb-1"><i class="fa-solid fa-clipboard-check me-1"></i>Panorama cumplimiento (dictamen sistema)</div>
                                <div class="d-flex flex-wrap align-items-center gap-3 small">
                                    <span><strong id="tileCumplimientoPct">—</strong> % efect. prom.</span>
                                    <span class="text-muted" id="tileCumplimientoMuestra">Muestra: 0</span>
                                    <span class="text-muted" id="tileCumplimientoPorResultado" style="font-size:0.7rem">—</span>
                                </div>
                                <div class="text-muted mt-1" style="font-size:0.65rem" id="tileCumplimientoLeyenda">—</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0 d-none" id="panelResumenGestor">
                    <div class="table-responsive rounded-2 border" style="max-height: 320px; border-color: rgba(var(--bs-success-rgb), 0.15) !important;">
                        <table class="table table-sm table-hover mb-0 align-middle" id="tablaPorGestor">
                            <thead class="small text-uppercase" style="font-size: 0.6rem; background: rgba(var(--bs-success-rgb), 0.07);">
                                <tr class="text-muted">
                                    <th class="py-2 ps-3">
                                        Levantó
                                        <i class="fa fa-question-circle estad-th-tip ms-1" data-bs-toggle="tooltip" data-bs-custom-class="estad-tooltip-custom" data-bs-placement="top" title="Gestor que creó el ticket (id_persona_creador). Clic en fila: detalle por ticket."></i>
                                    </th>
                                    <th class="text-center py-2">
                                        Tickets
                                        <i class="fa fa-question-circle estad-th-tip ms-1" data-bs-toggle="tooltip" data-bs-custom-class="estad-tooltip-custom" data-bs-placement="top" title="Cantidad de tickets con dictamen enviado en el detalle reciente agregado por este gestor."></i>
                                    </th>
                                    <th class="text-center py-2">
                                        T. lectura
                                        <i class="fa fa-question-circle estad-th-tip ms-1" data-bs-toggle="tooltip" data-bs-custom-class="estad-tooltip-custom" data-bs-placement="top" title="Promedio desde envío hasta que el gestor abrió el dictamen. — = sin ningún visto registrado aún (no se puede promediar)."></i>
                                    </th>
                                    <th class="text-center py-2">T.envío <i class="fa fa-question-circle estad-th-tip ms-1" data-bs-toggle="tooltip" data-bs-custom-class="estad-tooltip-custom" title="Siempre — aquí: no se calcula por gestor en esta grilla; use la tarjeta global «Envío prom.» o el detalle por Sabueso."></i></th>
                                    <th class="text-center py-2">Sin leer <i class="fa fa-question-circle estad-th-tip ms-1" data-bs-toggle="tooltip" data-bs-custom-class="estad-tooltip-custom" title="Cuántos dictámenes ya enviados a este gestor sigue sin abrir. Número entero."></i></th>
                                    <th class="text-center py-2">Tasa <i class="fa fa-question-circle estad-th-tip ms-1" data-bs-toggle="tooltip" data-bs-custom-class="estad-tooltip-custom" title="% de los suyos que ya abrió. 100% = todos vistos."></i></th>
                                    <th class="text-center py-2 pe-3">
                                        Cumplimiento
                                        <i class="fa fa-question-circle estad-th-tip ms-1" data-bs-toggle="tooltip" data-bs-custom-class="estad-tooltip-custom" title="Por ticket levantado por este gestor: resultado del dictamen sistema (visita campo, no visitó, etc.) y % efectividad promedio donde aplica."></i>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="tbodyPorGestor"></tbody>
                        </table>
                    </div>
                    <p class="text-muted small mb-0 mt-2"><i class="fa fa-search me-1"></i>Clic en una fila: listado de tickets de ese gestor con fecha de envío, si ya vio el dictamen y tiempo de lectura.</p>
                </div>
                <!-- Sabueso: hermano de panelResumenGestor (no anidado) para que no quede oculto por d-none del padre -->
                <div class="card-body pt-0 d-none" id="panelResumenSabueso">
                    <div class="table-responsive rounded-2 border" style="max-height: 320px; border-color: rgba(var(--bs-success-rgb), 0.15) !important;">
                        <table class="table table-sm table-hover mb-0 align-middle" id="tablaPorSabueso">
                            <thead class="small text-uppercase" style="font-size: 0.6rem; background: rgba(var(--bs-success-rgb), 0.07);">
                                <tr class="text-muted">
                                    <th class="py-2 ps-3">
                                        Sabueso (autor)
                                        <i class="fa fa-question-circle estad-th-tip ms-1" data-bs-toggle="tooltip" data-bs-custom-class="estad-tooltip-custom" data-bs-placement="top" title="Quien figura como autor al enviar el dictamen al gestor (dictamen.id_persona). No es solo quien tenía el ticket asignado al final."></i>
                                    </th>
                                    <th class="text-center py-2">
                                        Dictaminó
                                        <i class="fa fa-question-circle estad-th-tip ms-1" data-bs-toggle="tooltip" data-bs-custom-class="estad-tooltip-custom" data-bs-placement="top" title="Número de tickets en los que esta persona envió el dictamen (un conteo por ticket)."></i>
                                    </th>
                                    <th class="text-center py-2">
                                        Espera 1ª asign.
                                        <i class="fa fa-question-circle estad-th-tip ms-1" data-bs-toggle="tooltip" data-bs-custom-class="estad-tooltip-custom" data-bs-placement="top" title="En esos tickets: tiempo promedio desde que se creó el ticket hasta la primera asignación en Sabueso (cualquier persona). NO es el tiempo que esta persona tardó en asignar a otro (no guardamos quién hizo clic en asignar)."></i>
                                    </th>
                                    <th class="text-center py-2 pe-3">
                                        Asignado → envío
                                        <i class="fa fa-question-circle estad-th-tip ms-1" data-bs-toggle="tooltip" data-bs-custom-class="estad-tooltip-custom" data-bs-placement="top" title="Solo cuando consta que el ticket le quedó asignado a él antes del envío: tiempo desde esa asignación hasta enviar el dictamen. Si no estaba asignado a él, sale —."></i>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="tbodyPorSabueso"></tbody>
                        </table>
                    </div>
                    <p class="text-muted small mb-1 mt-2"><i class="fa fa-search me-1"></i>Clic en una fila: detalle por ticket (cola, asignado→envío, si el gestor vio).</p>
                    <p class="text-muted small mb-0" style="font-size: 0.7rem;"><strong>ID 797 / Sandra:</strong> «Espera 1ª asign.» = tiempo del <em>ticket</em> hasta su primera asignación. «Asignado → envío» = tiempo de esa persona con el ticket asignado hasta enviar.</p>
                    <div class="alert alert-light border mt-2 mb-0 py-2 px-3" style="font-size: 0.72rem;">
                        <strong>— en tiempos:</strong> no hay dato para calcular (ej. no estaba asignado a esa persona antes de enviar).<br>
                        <strong>«Sin dictamen sistema» en el popup del gestor:</strong> ese ticket aún no tiene generado el dictamen automático; se genera en Panel Admin con el botón del dictamen del sistema.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KPIs operativos: debajo del bloque principal, antes del detalle; estilo glass -->
    <div class="card border-0 estad-glass mb-3 overflow-hidden">
        <div class="card-header border-0 py-3 bg-transparent">
            <div class="fw-semibold"><i class="fa fa-tachometer me-2 text-primary"></i>Indicadores operativos</div>
            <div class="text-muted small">Tiempos de cola, reasignaciones y cumplimiento</div>
        </div>
        <div class="card-body pt-0">
            <div class="row g-3">
                <div class="col-6 col-md-4 col-xl">
                    <div class="rounded-3 p-3 h-100" style="background: rgba(var(--bs-primary-rgb), 0.08); border: 1px solid rgba(var(--bs-primary-rgb), 0.12);">
                        <div class="text-muted small mb-1">Creación → 1ª asignación</div>
                        <div class="fs-5 fw-bold text-primary" id="kpiPrimeraAsignacion">—</div>
                        <div class="text-muted" style="font-size:0.7rem" id="kpiPrimeraAsignacionSub">—</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl">
                    <div class="rounded-3 p-3 h-100" style="background: rgba(13, 110, 253, 0.06); border: 1px solid rgba(13, 110, 253, 0.1);">
                        <div class="text-muted small mb-1">Reasignaciones antes de enviar</div>
                        <div class="fs-5 fw-bold text-body" id="kpiReasignaciones">—</div>
                        <div class="text-muted" style="font-size:0.65rem">Promedio por ticket</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl">
                    <div class="rounded-3 p-3 h-100" style="background: rgba(255, 193, 7, 0.12); border: 1px solid rgba(255, 193, 7, 0.25);">
                        <div class="text-muted small mb-1">Borradores sin enviar</div>
                        <div class="fs-5 fw-bold text-warning" id="kpiBorradores">—</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl">
                    <div class="rounded-3 p-3 h-100" style="background: rgba(25, 135, 84, 0.1); border: 1px solid rgba(25, 135, 84, 0.2);">
                        <div class="text-muted small mb-1">Visto dentro de 12 h</div>
                        <div class="fs-5 fw-bold text-success" id="kpi12h">—</div>
                        <div class="text-muted" style="font-size:0.7rem" id="kpi12hSub">—</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl">
                    <div class="rounded-3 p-3 h-100" style="background: rgba(220, 53, 69, 0.08); border: 1px solid rgba(220, 53, 69, 0.15);">
                        <div class="text-muted small mb-1">Cola lenta (&gt;24 h)</div>
                        <div class="fs-5 fw-bold text-danger" id="kpiCola24h">—</div>
                        <div class="text-muted" style="font-size:0.65rem">Sin asignar o 1ª asignación tardía</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 estad-glass">
        <div class="card-header border-0 py-3 bg-transparent d-flex flex-wrap align-items-start justify-content-between gap-2">
            <div>
                <div class="fw-semibold"><i class="fa-solid fa-list me-2"></i>Detalle reciente (dictamen enviado)</div>
                <div class="text-muted small">Folio, quién levantó (ID del gestor debajo), asignado a Sabueso, fechas; % efectividad y medidas = cumplimiento dictamen sistema</div>
            </div>
            <button type="button" class="btn btn-primary btn-sm btn-descargar-estad-detalle" title="Descargar en Excel (hasta 2000 registros)">
                <i class="fa fa-download me-1"></i>Descargar Excel
            </button>
        </div>
        <div class="card-datatable table-responsive px-2 pb-2">
            <table class="table table-hover mb-0 dt-responsive border-top" id="tablaDetalleTimings" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Folio</th>
                        <th title="Gestor que creó el ticket; el ID (persona) va debajo con icono">Quién levantó</th>
                        <th title="Asignado en Sabueso al momento del dictamen (no es el gestor que levantó)">Asignado a</th>
                        <th>Levantado</th>
                        <th>Dictamen enviado</th>
                        <th>Visto por gestor</th>
                        <th class="text-nowrap" title="% según resultado del dictamen = reglas de cumplimiento (visita/pago/direcciones)">% efectividad</th>
                        <th class="pe-3" title="Acciones sugeridas según cumplimiento">Medidas preventivas</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

</div>
