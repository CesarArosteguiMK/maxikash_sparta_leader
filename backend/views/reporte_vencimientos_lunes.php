<div class="container-xxl flex-grow-1 container-p-y">


    <!-- ── Header ── -->
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0">
                <i class="fa fa-calendar-week text-primary me-2"></i>
                Primeros pagos — Lunes de Cierre
            </h4>
            <p class="text-muted mb-0" style="font-size:.8rem;">
                Primer vencimiento:
                <strong class="text-primary" id="lunesFecha">calculando…</strong>
                &nbsp;·&nbsp;
                Corte actual:
                <code id="corteLabel" class="text-info">—</code>
            </p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <?php if ((int)($_SESSION['usuario_id'] ?? 0) === 1): ?>
                <div class="d-flex align-items-center gap-1"
                     title="Guardado en el servidor mientras esté en ejecución. Solo envío automático por cron; no afecta “Enviar correo” manual.">
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox" role="switch"
                               id="switchAutoEnvioPrimerosPagos">
                        <label class="form-check-label text-nowrap user-select-none" for="switchAutoEnvioPrimerosPagos"
                               style="font-size:.72rem;">Auto horario</label>
                    </div>
                </div>
                <span id="estadoEnvioAuto" class="badge bg-label-secondary" title="Estado de envío automático">
                    <i class="fa fa-clock me-1"></i> Auto correo: pendiente
                </span>
                <button id="btnEnviarCorreo" class="btn btn-outline-primary btn-sm">
                    <i class="fa fa-envelope me-1"></i> Enviar correo
                </button>
                <button id="btnExportarCSV" class="btn btn-outline-success btn-sm">
                    <i class="fa fa-file-csv me-1"></i> Exportar CSV
                </button>
            <?php endif; ?>
            <a href="/reporteria/PrimerosPagos" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-arrow-left me-1"></i>Volver
            </a>
        </div>
    </div>

    <!-- ── Stat total ── -->
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-2">
            <div class="card text-center h-100">
                <div class="card-body py-2">
                    <div class="text-muted" style="font-size:.68rem;text-transform:uppercase;letter-spacing:.5px;">
                        Registros
                    </div>
                    <div class="fw-bold text-primary" style="font-size:1.5rem;" id="statTotal">—</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Distribución nacimiento (50%) + Distribución de corte (50%) ── -->
    <div class="row g-3 mb-3">
        <div class="col-12 col-md-6">
            <div class="card h-100 mb-0">
                <div class="card-header py-2">
                    <span class="fw-semibold" style="font-size:.82rem;">
                        <i class="fa fa-egg text-primary me-1"></i>
                        Distribución de nacimiento
                    </span>
                </div>
                <div class="card-body py-2">
                    <div class="row row-cols-2 g-2" id="statsNacimiento">
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="h-100" style="background:#f0f3f7;border:1px solid #d8dfe7;border-radius:.5rem;overflow:hidden;">
                <div style="background:#e9edf2;padding:.5rem .75rem;border-bottom:0;">
                    <span class="fw-semibold d-inline-flex flex-wrap align-items-center gap-1" style="font-size:.78rem;line-height:1.35;">
                        <i class="fa fa-chart-pie flex-shrink-0" style="color:#000 !important;"></i>
                        <span style="color:#000 !important;">Distribución de corte:</span>
                        <span id="distribCorteFecha" class="fw-semibold" style="color:#6b7785;">—</span>
                        <span class="text-muted">·</span>
                        <span class="text-body">Corte actual:</span>
                        <code id="distribCorteCorteLbl" class="text-info mb-0" style="font-size:.78rem;">—</code>
                    </span>
                </div>
                <div style="background:#f0f3f7;padding:.5rem;">
                    <div class="row row-cols-2 g-2" id="statsCorte">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Matriz nacimiento → corte ── -->
    <div class="card mb-3">
        <div class="card-body py-2 px-2" id="statsMatriz">
        </div>
    </div>

    <!-- ── Seguimiento por jerarquía ── -->
    <div class="card mb-3">
        <div class="card-header py-2">
            <span class="fw-semibold" style="font-size:.82rem;">
                <i class="fa fa-ranking-star text-danger me-1"></i>
                Seguimiento por jerarquía
                <span class="text-muted fw-normal" style="font-size:.72rem;">
                    — peor seguimiento primero
                </span>
            </span>
        </div>
        <div class="card-body" id="statsJerarquia">
        </div>
    </div>

    <!-- ── Filtros ── -->
    <div class="card mb-3">
        <div class="card-header py-2 d-flex align-items-center justify-content-between">
            <span class="fw-semibold" style="font-size:.82rem;">
                <i class="fa fa-sliders text-primary me-1"></i> Filtros
            </span>
            <button id="btnReset" class="btn btn-outline-secondary btn-sm" style="font-size:.72rem;">
                <i class="fa fa-rotate-left me-1"></i> Limpiar
            </button>
        </div>
        <div class="card-body py-2">
            <div class="row g-2">
                <div class="col-12 col-md-4">
                    <label class="form-label mb-0" style="font-size:.72rem;">Buscar cliente / ID crédito</label>
                    <input id="fBusq" type="text" class="form-control form-control-sm"
                           placeholder="Nombre o ID…">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label mb-0" style="font-size:.72rem;">Bucket nació</label>
                    <select id="fBucketNacio" class="form-select form-select-sm">
                        <option value="">Todos</option>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label mb-0" style="font-size:.72rem;">Bucket corte actual</label>
                    <select id="fBucketCorte" class="form-select form-select-sm">
                        <option value="">Todos</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label mb-0" style="font-size:.72rem;">Movimiento</label>
                    <select id="fMovimiento" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <option value="mejoro">⬆ Mejoró</option>
                        <option value="igual">➡ Sin cambio</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Tabla principal ── -->
    <div class="card mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="tablaVencimientos"
                       class="table table-hover table-sm align-middle"
                       style="width:100%">
                    <thead class="table-light">
                    <tr>
                        <th>
                            <i class="fa fa-id-card text-primary me-1"></i>
                            General
                        </th>
                        <th>
                            <i class="fa fa-sitemap text-muted me-1"></i>
                            Jerarquía
                        </th>
                        <th class="text-center">
                            <i class="fa fa-egg text-primary me-1"></i>
                            Cómo nació
                        </th>
                        <th class="text-center">
                            <i class="fa fa-chart-line text-warning me-1"></i>
                            Corte actual
                        </th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

</div>
