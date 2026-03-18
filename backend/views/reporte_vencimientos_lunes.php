<div class="container-xxl flex-grow-1 container-p-y">

    <!-- ── Header ─────────────────────────────────────────────── -->
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0">
                <i class="fa fa-calendar-week text-primary me-2"></i>
                Vencimientos — Lunes de Cierre
            </h4>
            <p class="text-muted mb-0" style="font-size:.8rem;">
                Primer vencimiento:
                <strong class="text-primary" id="lunesFecha">calculando…</strong>
                &nbsp;·&nbsp;
                Corte actual:
                <code id="corteLabel" class="text-info">—</code>
            </p>
        </div>
        <button id="btnExportarCSV" class="btn btn-outline-success btn-sm">
            <i class="fa fa-file-csv me-1"></i> Exportar CSV
        </button>
    </div>

    <!-- ── Stat rápido ────────────────────────────────────────── -->
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-2">
            <div class="card text-center h-100">
                <div class="card-body py-2">
                    <div class="text-muted" style="font-size:.68rem;text-transform:uppercase;letter-spacing:.5px;">
                        Registros filtrados
                    </div>
                    <div class="fw-bold text-primary" style="font-size:1.5rem;" id="statTotal">—</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Stats: distribución de nacimiento ─────────────────── -->
    <div class="card mb-3">
        <div class="card-header py-2 d-flex align-items-center gap-2">
            <i class="fa fa-egg text-primary"></i>
            <span class="fw-semibold" style="font-size:.82rem;">Distribución de nacimiento</span>
            <span class="text-muted" style="font-size:.72rem;">(Bucket_Morosidad_Real al nacer)</span>
        </div>
        <div class="card-body py-3">
            <div class="row row-cols-2 row-cols-md-5 g-2" id="statsNacimiento">
                <div class="col">
                    <div class="card text-center h-100 border-0 shadow-sm">
                        <div class="card-body py-3">
                            <div class="spinner-border spinner-border-sm text-muted"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Stats: matriz nacimiento → corte actual ───────────── -->
    <div class="card mb-3">
        <div class="card-header py-2 d-flex align-items-center gap-2">
            <i class="fa fa-arrows-left-right text-warning"></i>
            <span class="fw-semibold" style="font-size:.82rem;">Matriz nacimiento → corte actual</span>
            <span class="ms-2" style="font-size:.7rem;">
                <span class="badge bg-label-success">verde = mejoró</span>
                <span class="badge bg-label-danger ms-1">rojo = empeoró</span>
                <span class="badge bg-label-secondary ms-1">gris = sin cambio</span>
            </span>
        </div>
        <div class="card-body py-2 px-2" id="statsMatriz">
            <div class="text-center py-3">
                <div class="spinner-border spinner-border-sm text-muted"></div>
            </div>
        </div>
    </div>

    <!-- ── Filtros ────────────────────────────────────────────── -->
    <div class="card mb-3">
        <div class="card-header py-2 d-flex align-items-center justify-content-between">
            <span class="fw-semibold" style="font-size:.82rem;">
                <i class="fa fa-sliders text-primary me-1"></i>
                Filtros combinables
            </span>
            <button id="btnReset" class="btn btn-outline-secondary btn-sm" style="font-size:.72rem;padding:.25rem .7rem;">
                <i class="fa fa-rotate-left me-1"></i> Limpiar todo
            </button>
        </div>
        <div class="card-body py-3">
            <div class="row g-2">

                <!-- Búsqueda libre -->
                <div class="col-12 col-md-4">
                    <label class="form-label mb-1" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;">
                        <i class="fa fa-magnifying-glass me-1 text-muted"></i>Buscar cliente / ID
                    </label>
                    <input id="fBusq" type="text"
                           class="form-control form-control-sm"
                           placeholder="Nombre o ID crédito…">
                </div>

                <!-- Bucket nació -->
                <div class="col-6 col-md-2">
                    <label class="form-label mb-1" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;">
                        <i class="fa fa-egg me-1 text-muted"></i>Bucket nació
                    </label>
                    <select id="fBucketNacio" class="form-select form-select-sm">
                        <option value="">Todos</option>
                    </select>
                </div>

                <!-- Bucket corte -->
                <div class="col-6 col-md-2">
                    <label class="form-label mb-1" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;">
                        <i class="fa fa-chart-line me-1 text-muted"></i>Bucket corte
                    </label>
                    <select id="fBucketCorte" class="form-select form-select-sm">
                        <option value="">Todos</option>
                    </select>
                </div>

                <!-- Movimiento -->
                <div class="col-6 col-md-2">
                    <label class="form-label mb-1" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;">
                        <i class="fa fa-arrow-trend-up me-1 text-muted"></i>Movimiento
                    </label>
                    <select id="fMovimiento" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <option value="mejoro">⬆ Mejoró</option>
                        <option value="empeoró">⬇ Empeoró</option>
                        <option value="igual">➡ Sin cambio</option>
                    </select>
                </div>

                <!-- Separador visual -->
                <div class="col-12">
                    <hr class="my-1" style="border-color:#e0e0e0;">
                    <div class="text-muted" style="font-size:.68rem;text-transform:uppercase;letter-spacing:.5px;">
                        <i class="fa fa-sitemap me-1"></i>Filtrar por jerarquía
                    </div>
                </div>

                <!-- Territorial -->
                <div class="col-6 col-md-3">
                    <label class="form-label mb-1" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;">
                        <i class="fa fa-globe me-1 text-secondary"></i>Territorial
                    </label>
                    <select id="fTerritorial" class="form-select form-select-sm">
                        <option value="">Todos</option>
                    </select>
                </div>

                <!-- Zonal -->
                <div class="col-6 col-md-3">
                    <label class="form-label mb-1" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;">
                        <i class="fa fa-map-location-dot me-1 text-info"></i>Zonal
                    </label>
                    <select id="fZonal" class="form-select form-select-sm">
                        <option value="">Todos</option>
                    </select>
                </div>

                <!-- Jefe de Plaza -->
                <div class="col-6 col-md-3">
                    <label class="form-label mb-1" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;">
                        <i class="fa fa-user-tie me-1 text-primary"></i>Jefe de Plaza
                    </label>
                    <select id="fJefe" class="form-select form-select-sm">
                        <option value="">Todos</option>
                    </select>
                </div>

                <!-- Gestor -->
                <div class="col-6 col-md-3">
                    <label class="form-label mb-1" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;">
                        <i class="fa fa-user me-1 text-muted"></i>Gestor
                    </label>
                    <select id="fGestor" class="form-select form-select-sm">
                        <option value="">Todos</option>
                    </select>
                </div>

            </div>
        </div>
    </div>

    <!-- ── Tabla principal ────────────────────────────────────── -->
    <div class="card mb-4">
        <div class="card-header py-2 d-flex align-items-center gap-2">
            <i class="fa fa-table text-primary"></i>
            <span class="fw-semibold" style="font-size:.82rem;">Detalle de créditos</span>
        </div>
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
                            Corte actual · Movimiento · Cuotas · Saldo
                        </th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ── Bloque gestión por jerarquía ───────────────────────── -->
    <div class="card mb-4">
        <div class="card-header py-2 d-flex align-items-center gap-2">
            <i class="fa fa-ranking-star text-danger"></i>
            <span class="fw-semibold" style="font-size:.82rem;">
                Seguimiento por jerarquía
            </span>
            <span class="text-muted" style="font-size:.72rem;">
                — peor seguimiento primero · clic para expandir
            </span>
        </div>
        <div class="card-body" id="statsJerarquia">
            <div class="text-center py-4">
                <div class="spinner-border text-primary"></div>
                <div class="text-muted mt-2" style="font-size:.8rem;">Calculando jerarquías…</div>
            </div>
        </div>
    </div>

</div>

<?= $script ?>
