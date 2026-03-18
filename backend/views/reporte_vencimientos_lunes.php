<div class="container-xxl flex-grow-1 container-p-y">

    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0">
                <i class="fa fa-calendar-week text-primary me-2"></i>
                Vencimientos — Lunes de Cierre
            </h4>
            <p class="text-muted mb-0" style="font-size:.78rem;">
                Primer vencimiento:
                <strong class="text-primary" id="lunesFecha">calculando…</strong>
                &nbsp;·&nbsp; Corte:
                <code class="text-info" id="corteLabel">—</code>
            </p>
        </div>
        <button id="btnExportarCSV" class="btn btn-outline-success btn-sm">
            <i class="fa fa-file-csv me-1"></i> Exportar CSV
        </button>
    </div>

    <!-- Stats rápidas -->
    <div class="row g-2 mb-3">
        <div class="col-6 col-md-2">
            <div class="card text-center h-100 border-0 shadow-sm">
                <div class="card-body py-2 px-1">
                    <div class="text-muted" style="font-size:.65rem;text-transform:uppercase;">Total</div>
                    <div class="fw-bold text-primary" style="font-size:1.4rem;" id="statTotal">—</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card text-center h-100 border-0 shadow-sm">
                <div class="card-body py-2 px-1">
                    <div class="badge bg-label-success mb-1" style="font-size:.62rem;">
                        <i class="fa fa-circle-check me-1"></i>Current
                    </div>
                    <div class="fw-bold" style="font-size:1.4rem;" id="sNacCurrent">—</div>
                    <div class="text-muted" style="font-size:.62rem;">nacieron</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card text-center h-100 border-0 shadow-sm">
                <div class="card-body py-2 px-1">
                    <div class="badge bg-label-warning mb-1" style="font-size:.62rem;">
                        <i class="fa fa-clock me-1"></i>1-7d
                    </div>
                    <div class="fw-bold" style="font-size:1.4rem;" id="sNac17">—</div>
                    <div class="text-muted" style="font-size:.62rem;">nacieron</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body py-2 px-2">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div class="text-muted" style="font-size:.65rem;text-transform:uppercase;">
                            1-7d → pagaron
                        </div>
                        <strong class="text-success" style="font-size:.9rem;" id="sConversion">—</strong>
                    </div>
                    <div class="progress" style="height:8px;">
                        <div class="progress-bar bg-success" id="sConvBar" style="width:0%"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-1" style="font-size:.65rem;">
                        <span class="text-success">
                            <i class="fa fa-circle-check me-1"></i>
                            Pagaron: <strong id="sPagaron">—</strong>
                        </span>
                        <span class="text-muted">
                            Siguen 1-7: <strong id="sSiguen">—</strong>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-3">
        <div class="card-body py-2">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <input id="fBusq" type="text" class="form-control form-control-sm"
                           placeholder="&#xf002;  Buscar nombre o ID crédito…"
                           style="font-family:'Font Awesome 6 Free', sans-serif;">
                </div>
                <div class="col-6 col-md-2">
                    <select id="fBucketNacio" class="form-select form-select-sm">
                        <option value="">Bucket nació</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select id="fBucketCorte" class="form-select form-select-sm">
                        <option value="">Bucket corte</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select id="fTerritorial" class="form-select form-select-sm">
                        <option value="">Territorial</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select id="fZonal" class="form-select form-select-sm">
                        <option value="">Zonal</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select id="fJefe" class="form-select form-select-sm">
                        <option value="">Jefe de Plaza</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select id="fGestor" class="form-select form-select-sm">
                        <option value="">Gestor</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <button id="btnReset" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="fa fa-rotate-left me-1"></i> Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card mb-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="tablaVencimientos"
                       class="table table-hover table-sm align-middle mb-0"
                       style="width:100%">
                    <thead class="table-light">
                    <tr>
                        <th>
                            <i class="fa fa-id-card text-primary me-1"></i>General
                        </th>
                        <th>
                            <i class="fa fa-sitemap text-muted me-1"></i>Jerarquía
                        </th>
                        <th class="text-center">
                            <i class="fa fa-egg text-muted me-1"></i>Nació
                        </th>
                        <th class="text-center">
                            <i class="fa fa-chart-line text-warning me-1"></i>Corte actual
                        </th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Ranking territorial -->
    <div class="card mb-4">
        <div class="card-header py-2">
            <span class="fw-semibold" style="font-size:.8rem;">
                <i class="fa fa-ranking-star text-danger me-1"></i>
                Seguimiento por territorial
                <span class="text-muted fw-normal" style="font-size:.7rem;">
                    — % de créditos 1-7d que ya pagaron · peor primero
                </span>
            </span>
        </div>
        <div class="card-body py-2" id="statsJerarquia">
            <p class="text-muted mb-0" style="font-size:.8rem;">Cargando…</p>
        </div>
    </div>

</div>

<?= $script ?>
