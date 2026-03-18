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

    <!-- ── Distribución de nacimiento ────────────────────────── -->
    <div class="card mb-3">
        <div class="card-header py-2">
            <span class="fw-semibold" style="font-size:.82rem;">
                <i class="fa fa-egg text-primary me-1"></i>
                Distribución de nacimiento
                <span class="text-muted fw-normal" style="font-size:.72rem;">
                    — cómo llegaron al lunes de cierre
                </span>
            </span>
        </div>
        <div class="card-body py-2">
            <div class="row row-cols-2 row-cols-md-5 g-2" id="statsNacimiento">
                <div class="col text-center text-muted" style="font-size:.78rem;">Cargando…</div>
            </div>
        </div>
    </div>

    <!-- ── Matriz nacimiento → corte actual ───────────────────── -->
    <div class="card mb-3">
        <div class="card-header py-2">
            <span class="fw-semibold" style="font-size:.82rem;">
                <i class="fa fa-arrows-left-right text-warning me-1"></i>
                Matriz nacimiento → corte actual
                <span class="text-muted fw-normal" style="font-size:.72rem;">
                    — verde: mejoró &nbsp;·&nbsp; rojo: empeoró &nbsp;·&nbsp; gris: sin cambio
                </span>
            </span>
        </div>
        <div class="card-body py-2 px-2" id="statsMatriz">
            <div class="text-muted text-center py-2" style="font-size:.78rem;">Cargando…</div>
        </div>
    </div>

    <!-- ── Filtros ─────────────────────────────────────────────── -->
    <div class="card mb-3">
        <div class="card-header py-2 d-flex align-items-center justify-content-between">
            <span class="fw-semibold" style="font-size:.82rem;">
                <i class="fa fa-sliders text-primary me-1"></i>
                Filtros combinables
            </span>
            <button id="btnReset"
                    class="btn btn-outline-secondary btn-sm"
                    style="font-size:.72rem;padding:.2rem .7rem;">
                <i class="fa fa-rotate-left me-1"></i> Limpiar todo
            </button>
        </div>
        <div class="card-body py-2">
            <div class="row g-2">

                <!-- Búsqueda libre -->
                <div class="col-12 col-md-3">
                    <label class="form-label mb-0" style="font-size:.72rem;">
                        <i class="fa fa-magnifying-glass me-1 text-muted"></i>
                        Buscar cliente / ID
                    </label>
                    <input id="fBusq" type="text"
                           class="form-control form-control-sm"
                           placeholder="Nombre o ID crédito…">
                </div>

                <!-- Bucket nació -->
                <div class="col-6 col-md-2">
                    <label class="form-label mb-0" style="font-size:.72rem;">
                        <i class="fa fa-egg me-1 text-muted"></i>
                        Bucket nació
                    </label>
                    <select id="fBucketNacio" class="form-select form-select-sm">
                        <option value="">Todos</option>
                    </select>
                </div>

                <!-- Bucket corte -->
                <div class="col-6 col-md-2">
                    <label class="form-label mb-0" style="font-size:.72rem;">
                        <i class="fa fa-chart-line me-1 text-muted"></i>
                        Bucket corte
                    </label>
                    <select id="fBucketCorte" class="form-select form-select-sm">
                        <option value="">Todos</option>
                    </select>
                </div>

                <!-- Movimiento -->
                <div class="col-6 col-md-2">
                    <label class="form-label mb-0" style="font-size:.72rem;">
                        <i class="fa fa-arrows-up-down me-1 text-muted"></i>
                        Movimiento
                    </label>
                    <select id="fMovimiento" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <option value="mejoro">⬆ Mejoró</option>
                        <option value="empeoró">⬇ Empeoró</option>
                        <option value="igual">➡ Sin cambio</option>
                    </select>
                </div>

                <!-- Territorial -->
                <div class="col-6 col-md-3">
                    <label class="form-label mb-0" style="font-size:.72rem;">
                        <i class="fa fa-globe me-1 text-muted"></i>
                        Territorial
                    </label>
                    <select id="fTerritorial" class="form-select form-select-sm">
                        <option value="">Todos</option>
                    </select>
                </div>

                <!-- Zonal -->
                <div class="col-6 col-md-3">
                    <label class="form-label mb-0" style="font-size:.72rem;">
                        <i class="fa fa-map-location-dot me-1 text-muted"></i>
                        Zonal
                    </label>
                    <select id="fZonal" class="form-select form-select-sm">
                        <option value="">Todos</option>
                    </select>
                </div>

                <!-- Jefe de Plaza -->
                <div class="col-6 col-md-3">
                    <label class="form-label mb-0" style="font-size:.72rem;">
                        <i class="fa fa-user-tie me-1 text-muted"></i>
                        Jefe de Plaza
                    </label>
                    <select id="fJefe" class="form-select form-select-sm">
                        <option value="">Todos</option>
                    </select>
                </div>

                <!-- Gestor -->
                <div class="col-6 col-md-3">
                    <label class="form-label mb-0" style="font-size:.72rem;">
                        <i class="fa fa-user me-1 text-muted"></i>
                        Gestor
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
        <div class="card-header py-2">
            <span class="fw-semibold" style="font-size:.82rem;">
                <i class="fa fa-table text-primary me-1"></i>
                Detalle de créditos
            </span>
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
                            Corte actual · Cuotas · Saldo
                        </th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ── Ranking de gestión por jerarquía ───────────────────── -->
    <div class="card mb-4">
        <div class="card-header py-2">
            <span class="fw-semibold" style="font-size:.82rem;">
                <i class="fa fa-ranking-star text-danger me-1"></i>
                Seguimiento por jerarquía
                <span class="text-muted fw-normal" style="font-size:.72rem;">
                    — peor seguimiento primero · haz clic para expandir
                </span>
            </span>
        </div>
        <div class="card-body pb-2" id="statsJerarquia">
            <div class="text-muted text-center py-3" style="font-size:.78rem;">
                <i class="fa fa-spinner fa-spin me-1"></i> Cargando…
            </div>
        </div>
    </div>

</div>

<?= $script ?>
