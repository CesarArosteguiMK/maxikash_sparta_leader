<div class="container-xxl flex-grow-1 container-p-y">

    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-1">
                <i class="fa fa-calendar-week text-primary me-2"></i>
                Vencimientos — Lunes de Cierre
            </h4>
            <p class="text-muted mb-0" style="font-size:.82rem;">
                Créditos con primer vencimiento el
                <strong class="text-primary" id="lunesFecha">calculando…</strong>
            </p>
        </div>
        <button id="btnExportarCSV" class="btn btn-outline-success btn-sm">
            <i class="fa fa-file-csv me-1"></i> Exportar CSV
        </button>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card text-center h-100">
                <div class="card-body py-3">
                    <div class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.5px;">
                        Total registros
                    </div>
                    <div class="fw-bold text-primary" style="font-size:1.6rem;" id="statTotal">—</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center h-100">
                <div class="card-body py-3">
                    <div class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.5px;">
                        Saldo vencido total
                    </div>
                    <div class="fw-bold text-warning" style="font-size:1.4rem;" id="statSaldo">—</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body py-3">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label mb-1" style="font-size:.78rem;">Bucket final</label>
                    <select id="fBucket" class="form-select form-select-sm">
                        <option value="">Todos los buckets</option>
                    </select>
                </div>
                <div class="col-12 col-md-5">
                    <label class="form-label mb-1" style="font-size:.78rem;">Gestor asignado</label>
                    <select id="fGestor" class="form-select form-select-sm">
                        <option value="">Todos los gestores</option>
                    </select>
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button id="btnReset" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="fa fa-rotate-left me-1"></i> Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="tablaVencimientos"
                       class="table table-hover table-sm align-middle"
                       style="width:100%">
                    <thead class="table-light">
                    <tr>
                        <th>ID Crédito</th>
                        <th>Cliente</th>
                        <th class="text-center">Bucket</th>
                        <th class="text-center">Bucket Real</th>
                        <th class="text-center">Bucket Final</th>
                        <th>Gestor</th>
                        <th>Jefe de Plaza</th>
                        <th>Zonal</th>
                        <th>Territorial</th>
                        <th class="text-center">Cuotas venc.</th>
                        <th class="text-end">Saldo vencido</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?= $script ?>
