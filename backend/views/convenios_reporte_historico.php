<style>
    .cvh-shell { color: #334155; }
    .cvh-toolbar,
    .cvh-panel,
    .cvh-kpi {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: .55rem;
        box-shadow: 0 .35rem 1rem rgba(15, 23, 42, .04);
    }
    .cvh-title {
        color: #5b3ea6;
        font-weight: 800;
        letter-spacing: 0;
    }
    .cvh-kpi { min-height: 92px; }
    .cvh-kpi small { color: #64748b; font-weight: 700; text-transform: uppercase; font-size: .68rem; }
    .cvh-kpi strong { color: #1f2937; font-size: 1.15rem; }
    .cvh-combo-chart {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: .55rem;
        box-shadow: 0 .35rem 1rem rgba(15, 23, 42, .04);
    }
    .cvh-combo-head small {
        color: #64748b;
        font-weight: 800;
        text-transform: uppercase;
        font-size: .68rem;
    }
    .cvh-combo-head strong {
        color: #172033;
        font-size: 1.05rem;
    }
    .cvh-combo-bar {
        width: 100%;
        height: 24px;
        border-radius: 999px;
        background: #e2e8f0;
        overflow: hidden;
        display: flex;
        margin-top: .85rem;
    }
    .cvh-combo-segment {
        display: block;
        height: 100%;
        width: 0%;
        transition: width .22s ease;
    }
    .cvh-combo-segment.original { background: #1E3A8A; }
    .cvh-combo-segment.descuento { background: #3B82F6; }
    .cvh-combo-segment.pagado { background: #93C5FD; }
    .cvh-combo-legend {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .75rem;
        margin-top: .85rem;
    }
    .cvh-combo-item {
        min-width: 0;
        border: 1px solid #edf2f7;
        border-radius: .45rem;
        padding: .65rem .75rem;
        background: #fbfdff;
    }
    .cvh-combo-item span {
        display: flex;
        align-items: center;
        gap: .45rem;
        color: #64748b;
        font-size: .72rem;
        font-weight: 800;
        text-transform: uppercase;
    }
    .cvh-combo-item strong {
        display: block;
        color: #172033;
        margin-top: .15rem;
        overflow-wrap: anywhere;
    }
    .cvh-dot {
        width: .68rem;
        height: .68rem;
        border-radius: 50%;
        flex: 0 0 auto;
    }
    .cvh-dot.original { background: #1E3A8A; }
    .cvh-dot.descuento { background: #3B82F6; }
    .cvh-dot.pagado { background: #93C5FD; }
    @media (max-width: 768px) {
        .cvh-combo-legend { grid-template-columns: 1fr; }
    }
    .cvh-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1120px;
    }
    .cvh-table th {
        color: #3151c7;
        font-size: .72rem;
        text-transform: uppercase;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: .72rem .75rem;
        white-space: nowrap;
    }
    .cvh-table td {
        border-bottom: 1px solid #edf2f7;
        padding: .72rem .75rem;
        vertical-align: middle;
        font-size: .86rem;
    }
    .cvh-table tbody tr:hover { background: #fbf8ff; }
    .cvh-money { font-weight: 800; color: #1f2937; }
    .cvh-muted { color: #64748b; font-size: .76rem; }
    .cvh-badge {
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        font-weight: 800;
        font-size: .72rem;
        padding: .24rem .52rem;
    }
    .cvh-badge.activo { color: #0369a1; background: #e0f2fe; }
    .cvh-badge.completado { color: #15803d; background: #dcfce7; }
    .cvh-badge.cancelado { color: #b91c1c; background: #fee2e2; }
    .cvh-badge.neutral { color: #475569; background: #e2e8f0; }
    .cvh-reactivado { color: #8a4f00; background: #fff4cf; border: 1px solid #fedf89; }
    .cvh-celula-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: .13rem .45rem;
        font-size: .68rem;
        font-weight: 800;
        line-height: 1.2;
        vertical-align: middle;
    }
    .cvh-celula-badge.despachos { color: #1e3a8a; background: #dbeafe; }
    .cvh-celula-badge.callcenter { color: #166534; background: #bbf7d0; }
    .cvh-celula-badge.campo { color: #92400e; background: #fef3c7; }
    .cvh-search-select {
        position: relative;
    }
    .cvh-search-button {
        width: 100%;
        min-height: 38px;
        border: 1px solid #d9dee3;
        border-radius: .375rem;
        background: #fff;
        color: #475569;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .5rem;
        padding: .4375rem .875rem;
        text-align: left;
    }
    .cvh-search-button > span {
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .cvh-search-panel {
        position: absolute;
        z-index: 30;
        left: 0;
        right: 0;
        top: calc(100% + .25rem);
        display: none;
        border: 1px solid #d9dee3;
        border-radius: .45rem;
        background: #fff;
        box-shadow: 0 .75rem 1.5rem rgba(15, 23, 42, .12);
        padding: .5rem;
    }
    .cvh-search-select.open .cvh-search-panel {
        display: block;
    }
    .cvh-search-input {
        width: 100%;
        border: 1px solid #d9dee3;
        border-radius: .375rem;
        padding: .42rem .65rem;
        margin-bottom: .4rem;
        font-size: .82rem;
    }
    .cvh-search-options {
        max-height: 220px;
        overflow-y: auto;
    }
    .cvh-search-option {
        width: 100%;
        border: 0;
        border-radius: .35rem;
        background: transparent;
        color: #334155;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .5rem;
        padding: .45rem .55rem;
        text-align: left;
        font-size: .83rem;
    }
    .cvh-search-option:hover,
    .cvh-search-option.active {
        background: #eef4ff;
    }
    .cvh-search-empty {
        color: #64748b;
        padding: .6rem .55rem;
        font-size: .82rem;
    }
    .cvh-progress {
        width: 128px;
        height: 7px;
        border-radius: 999px;
        overflow: hidden;
        background: #e2e8f0;
    }
    .cvh-progress > span {
        display: block;
        height: 100%;
        background: linear-gradient(90deg, #22c55e, #5b72e8);
    }
    .cvh-empty {
        border: 1px dashed #cbd5e1;
        border-radius: .55rem;
        color: #64748b;
        background: #f8fafc;
    }
    .cvh-detail-modal .modal-content,
    .cvh-detail-modal .modal-body {
        background: #f5f7fb;
    }
    .cvh-detail-modal .modal-header {
        background: #fff;
        border-bottom: 1px solid #e2e8f0;
    }
    .cvi-shell { color: #334155; }
    .cvi-panel,
    .cvi-kpi {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: .55rem;
        box-shadow: 0 .35rem 1rem rgba(15, 23, 42, .04);
    }
    .cvi-title { color: #5b3ea6; font-weight: 800; letter-spacing: 0; }
    .cvi-kpi small { color: #64748b; font-weight: 800; text-transform: uppercase; font-size: .68rem; }
    .cvi-kpi strong { color: #172033; font-size: 1.15rem; }
    .cvi-pill {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        border-radius: 999px;
        padding: .24rem .55rem;
        font-size: .72rem;
        font-weight: 800;
    }
    .cvi-pill.activo { color: #0369a1; background: #e0f2fe; }
    .cvi-pill.completado,
    .cvi-pill.pagado { color: #15803d; background: #dcfce7; }
    .cvi-pill.cancelado,
    .cvi-pill.vencido { color: #b91c1c; background: #fee2e2; }
    .cvi-pill.parcial,
    .cvi-pill.pendiente { color: #9a5b00; background: #fef3c7; }
    .cvi-pill.pendiente_conciliar { color: #5b3ea6; background: #f4efff; }
    .cvi-pill.neutral { color: #475569; background: #e2e8f0; }
    .cvi-pill.reactivado { color: #8a4f00; background: #fff4cf; border: 1px solid #fedf89; }
    .cvi-info-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .75rem;
    }
    .cvi-info {
        border: 1px solid #edf2f7;
        border-radius: .45rem;
        padding: .75rem;
        min-height: 76px;
        background: #fbfdff;
    }
    .cvi-info span { display: block; color: #64748b; font-size: .72rem; font-weight: 800; text-transform: uppercase; }
    .cvi-info strong { display: block; color: #1f2937; margin-top: .15rem; overflow-wrap: anywhere; }
    .cvi-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 880px;
    }
    .cvi-table th {
        color: #3151c7;
        font-size: .72rem;
        text-transform: uppercase;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: .7rem .75rem;
        white-space: nowrap;
    }
    .cvi-table td {
        border-bottom: 1px solid #edf2f7;
        padding: .7rem .75rem;
        vertical-align: middle;
        font-size: .86rem;
    }
    .cvi-timeline {
        position: relative;
        padding-left: 1.25rem;
    }
    .cvi-timeline::before {
        content: "";
        position: absolute;
        left: .35rem;
        top: .25rem;
        bottom: .25rem;
        width: 2px;
        background: #e2e8f0;
    }
    .cvi-event {
        position: relative;
        padding: 0 0 1rem 1rem;
    }
    .cvi-event::before {
        content: "";
        position: absolute;
        left: -.02rem;
        top: .35rem;
        width: .7rem;
        height: .7rem;
        border-radius: 50%;
        background: #5b72e8;
        border: 2px solid #fff;
        box-shadow: 0 0 0 2px #dbe4ff;
    }
    .cvi-event h6 { margin: 0; font-weight: 800; color: #1f2937; }
    .cvi-event p { margin: .15rem 0 0; color: #64748b; }
    .cvi-empty {
        border: 1px dashed #cbd5e1;
        border-radius: .55rem;
        color: #64748b;
        background: #f8fafc;
    }
    @media (max-width: 992px) {
        .cvi-info-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 576px) {
        .cvi-info-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="container-fluid py-3 cvh-shell">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div>
            <h4 class="cvh-title mb-1"><i class="fa-solid fa-table-list me-2"></i>Histórico de convenios</h4>
            <div class="text-muted">Convenios generados con monto original, oferta, descuento aplicado y monto pactado.</div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-outline-secondary" href="/convenios/reporteria">
                <i class="fa-solid fa-arrow-left me-1"></i>Reportería
            </a>
            <button type="button" id="cvhExportExcel" class="btn btn-outline-primary">
                <i class="fa-solid fa-file-excel me-1"></i>Exportar XLSX
            </button>
        </div>
    </div>

    <form id="cvhFilters" class="cvh-toolbar p-3 mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-2">
                <label class="form-label fw-bold">Desde</label>
                <input type="date" class="form-control" name="fecha_inicio" value="">
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label fw-bold">Hasta</label>
                <input type="date" class="form-control" name="fecha_fin" value="">
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label fw-bold">Estatus</label>
                <select class="form-select" name="estatus">
                    <option value="">Todos</option>
                    <option value="activo">Activo</option>
                    <option value="completado">Liquidado</option>
                    <option value="cancelado">Cancelado</option>
                </select>
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label fw-bold">Célula</label>
                <input type="hidden" name="celula" id="cvhCelula" value="">
                <div class="cvh-search-select" id="cvhCelulaDropdown">
                    <button type="button" class="cvh-search-button" id="cvhCelulaButton">
                        <span>Todas</span><i class="fa-solid fa-chevron-down"></i>
                    </button>
                    <div class="cvh-search-panel">
                        <input type="text" class="cvh-search-input" id="cvhCelulaSearch" placeholder="Buscar célula">
                        <div class="cvh-search-options" id="cvhCelulaOptions"></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label fw-bold">Oferta</label>
                <input type="hidden" name="id_producto_convenio" id="cvhProducto" value="">
                <div class="cvh-search-select" id="cvhProductoDropdown">
                    <button type="button" class="cvh-search-button" id="cvhProductoButton">
                        <span>Todas</span><i class="fa-solid fa-chevron-down"></i>
                    </button>
                    <div class="cvh-search-panel">
                        <input type="text" class="cvh-search-input" id="cvhProductoSearch" placeholder="Buscar oferta">
                        <div class="cvh-search-options" id="cvhProductoOptions"></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label fw-bold">Gestor</label>
                <input type="hidden" name="gestor" id="cvhGestor" value="">
                <div class="cvh-search-select" id="cvhGestorDropdown">
                    <button type="button" class="cvh-search-button" id="cvhGestorButton">
                        <span>Todos</span><i class="fa-solid fa-chevron-down"></i>
                    </button>
                    <div class="cvh-search-panel">
                        <input type="text" class="cvh-search-input" id="cvhGestorSearch" placeholder="Buscar gestor">
                        <div class="cvh-search-options" id="cvhGestorOptions"></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-1">
                <label class="form-label fw-bold">Límite</label>
                <select class="form-select" name="limit">
                    <option value="0" selected>Todos</option>
                    <option value="300">300</option>
                    <option value="600">600</option>
                    <option value="1000">1000</option>
                    <option value="3000">3000</option>
                </select>
            </div>
            <div class="col-12 col-md-11">
                <label class="form-label fw-bold">Buscar</label>
                <input type="text" class="form-control" name="q" placeholder="Cliente, crédito, convenio">
            </div>
        </div>
    </form>

    <div class="row g-2 mb-3" id="cvhKpis">
        <div class="col-6 col-xl-2"><div class="cvh-kpi p-3"><small>Total</small><br><strong id="kTotal">0</strong></div></div>
        <div class="col-6 col-xl-2"><div class="cvh-kpi p-3"><small>Monto original</small><br><strong id="kOriginal">$0.00</strong></div></div>
        <div class="col-6 col-xl-2"><div class="cvh-kpi p-3"><small>Monto convenio</small><br><strong id="kConvenio">$0.00</strong></div></div>
        <div class="col-6 col-xl-2"><div class="cvh-kpi p-3"><small>Descuento</small><br><strong id="kDescuento">$0.00</strong></div></div>
        <div class="col-6 col-xl-2"><div class="cvh-kpi p-3"><small>Pagado</small><br><strong id="kPagado">$0.00</strong></div></div>
        <div class="col-6 col-xl-2"><div class="cvh-kpi p-3"><small>Restante</small><br><strong id="kSaldo">$0.00</strong></div></div>
    </div>

    <div class="cvh-combo-chart p-3 mb-3" id="cvhMiniCharts">
        <div class="cvh-combo-head d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <small>Comparativo de importes</small>
                <strong>Original / Descuento / Pagado</strong>
            </div>
        </div>
        <div class="cvh-combo-bar" aria-label="Gráfica combinada de importes">
            <span id="chartOriginalBar" class="cvh-combo-segment original" title="Monto original"></span>
            <span id="chartDescuentoBar" class="cvh-combo-segment descuento" title="Descuento"></span>
            <span id="chartPagadoBar" class="cvh-combo-segment pagado" title="Pagado"></span>
        </div>
        <div class="cvh-combo-legend">
            <div class="cvh-combo-item">
                <span><i class="cvh-dot original"></i>Monto original</span>
                <strong id="chartOriginalValue">$0.00</strong>
            </div>
            <div class="cvh-combo-item">
                <span><i class="cvh-dot descuento"></i>Descuento</span>
                <strong id="chartDescuentoValue">$0.00</strong>
            </div>
            <div class="cvh-combo-item">
                <span><i class="cvh-dot pagado"></i>Pagado</span>
                <strong id="chartPagadoValue">$0.00</strong>
            </div>
        </div>
    </div>

    <div class="cvh-panel p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="cvh-table">
                <thead>
                    <tr>
                        <th>Fecha convenio</th>
                        <th>Cliente</th>
                        <th>ID crédito</th>
                        <th>Monto original</th>
                        <th>Oferta seleccionada</th>
                        <th>Descuento aplicado</th>
                        <th>Monto convenio</th>
                        <th>Pagado / Restante</th>
                        <th>Estatus</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody id="cvhRows">
                    <tr><td colspan="10" class="text-center text-muted py-4">Cargando información...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div id="cvhEmpty" class="cvh-empty p-4 text-center mt-3 d-none">
        <i class="fa-solid fa-circle-info me-1"></i>No se encontraron convenios con los filtros seleccionados.
    </div>
</div>

<div class="modal fade cvh-detail-modal" id="cvhDetalleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title cvi-title mb-0">
                        <i class="fa-solid fa-timeline me-2"></i>Reporte individual de convenio
                    </h5>
                    <div class="text-muted small">Ficha completa, amortización y bitácora del convenio seleccionado.</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="container-fluid py-3 cvi-shell">
                    <div id="cviModalLoading" class="cvi-empty p-4 text-center d-none">
                        <i class="fa-solid fa-circle-notch fa-spin me-1"></i>Cargando convenio...
                    </div>

                    <div id="cviModalMessage" class="cvi-empty p-4 text-center d-none"></div>

                    <div id="cviModalContent" class="d-none">
                        <div class="cvi-panel p-3 mb-3">
                            <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-3">
                                <div>
                                    <h5 id="cviModalCliente" class="fw-bold mb-1">--</h5>
                                    <div class="text-muted">
                                        <span id="cviModalOferta">--</span>
                                        <span id="cviModalReactivado"></span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div id="cviModalStatus"></div>
                                    <a id="cviModalPdf" class="btn btn-sm btn-outline-secondary mt-2 d-none" target="_blank" rel="noopener">
                                        <i class="fa-solid fa-file-pdf me-1"></i>PDF
                                    </a>
                                </div>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6 col-xl-2"><div class="cvi-kpi p-3"><small>Monto original</small><br><strong id="cviModalOriginal">$0.00</strong></div></div>
                                <div class="col-6 col-xl-2"><div class="cvi-kpi p-3"><small>Descuento</small><br><strong id="cviModalDescuento">$0.00</strong></div></div>
                                <div class="col-6 col-xl-2"><div class="cvi-kpi p-3"><small>Convenio</small><br><strong id="cviModalConvenio">$0.00</strong></div></div>
                                <div class="col-6 col-xl-2"><div class="cvi-kpi p-3"><small>Pagado</small><br><strong id="cviModalPagado">$0.00</strong></div></div>
                                <div class="col-6 col-xl-2"><div class="cvi-kpi p-3"><small>Restante</small><br><strong id="cviModalSaldo">$0.00</strong></div></div>
                                <div class="col-6 col-xl-2"><div class="cvi-kpi p-3"><small>Semanas</small><br><strong id="cviModalSemanas">0</strong></div></div>
                            </div>

                            <div id="cviModalInfoGrid" class="cvi-info-grid"></div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-xl-7">
                                <div class="cvi-panel p-0 overflow-hidden">
                                    <div class="p-3 border-bottom">
                                        <h6 class="fw-bold mb-0"><i class="fa-solid fa-table me-1 text-primary"></i>Amortizacion</h6>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="cvi-table">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Fecha pago</th>
                                                    <th>Pago semanal</th>
                                                    <th>Pago realizado</th>
                                                    <th>Estatus</th>
                                                    <th>Comprobante</th>
                                                </tr>
                                            </thead>
                                            <tbody id="cviModalAmortizacion"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-xl-5">
                                <div class="cvi-panel p-3 h-100">
                                    <h6 class="fw-bold mb-3"><i class="fa-solid fa-clock-rotate-left me-1 text-primary"></i>Bitacora</h6>
                                    <div id="cviModalBitacora" class="cvi-timeline"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const form = document.getElementById('cvhFilters');
    const tbody = document.getElementById('cvhRows');
    const empty = document.getElementById('cvhEmpty');
    const productoSelect = document.getElementById('cvhProducto');
    const productoDropdown = document.getElementById('cvhProductoDropdown');
    const productoButton = document.getElementById('cvhProductoButton');
    const productoSearch = document.getElementById('cvhProductoSearch');
    const productoOptions = document.getElementById('cvhProductoOptions');
    const celulaSelect = document.getElementById('cvhCelula');
    const celulaDropdown = document.getElementById('cvhCelulaDropdown');
    const celulaButton = document.getElementById('cvhCelulaButton');
    const celulaSearch = document.getElementById('cvhCelulaSearch');
    const celulaOptions = document.getElementById('cvhCelulaOptions');
    const gestorSelect = document.getElementById('cvhGestor');
    const gestorDropdown = document.getElementById('cvhGestorDropdown');
    const gestorButton = document.getElementById('cvhGestorButton');
    const gestorSearch = document.getElementById('cvhGestorSearch');
    const gestorOptions = document.getElementById('cvhGestorOptions');
    const exportBtn = document.getElementById('cvhExportExcel');
    const detalleModalEl = document.getElementById('cvhDetalleModal');
    const detalleModal = window.bootstrap?.Modal && detalleModalEl
        ? (window.bootstrap.Modal.getOrCreateInstance
            ? window.bootstrap.Modal.getOrCreateInstance(detalleModalEl)
            : new window.bootstrap.Modal(detalleModalEl))
        : null;
    const detalleLoading = document.getElementById('cviModalLoading');
    const detalleMessage = document.getElementById('cviModalMessage');
    const detalleContent = document.getElementById('cviModalContent');
    let currentRows = [];
    let catalogoCargado = false;
    let productosCatalogo = [];
    let gestoresCatalogo = [];
    const celulasCatalogo = [
        { value: '', label: 'Todas', celula: '' },
        { value: 'despachos', label: 'Despachos', celula: 'Despachos' },
        { value: 'callcenter', label: 'Call Center', celula: 'Call Center' },
        { value: 'campo', label: 'Campo', celula: 'Campo' }
    ];
    let loadTimer = null;
    let requestSeq = 0;
    let detalleRequestSeq = 0;

    const money = (value) => new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
        minimumFractionDigits: 2
    }).format(Number(value || 0));

    const fmtDate = (value) => {
        if (!value) return '--';
        const clean = String(value).slice(0, 10);
        const parts = clean.split('-');
        return parts.length === 3 ? `${parts[2]}/${parts[1]}/${parts[0]}` : value;
    };

    const esc = (value) => String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    const numericMoney = (value) => {
        const raw = String(value ?? '').trim();
        if (raw === '' || Number.isNaN(Number(raw))) return 0;
        return Number(raw);
    };

    function statusBadge(value) {
        const v = String(value || '').toLowerCase();
        const labels = {
            activo: 'Activo',
            completado: 'Liquidado',
            cancelado: 'Cancelado'
        };
        const label = labels[v] || (v ? v.charAt(0).toUpperCase() + v.slice(1) : 'Sin estatus');
        const cls = ['activo', 'completado', 'cancelado'].includes(v) ? v : 'neutral';
        return `<span class="cvh-badge ${cls}">${esc(label)}</span>`;
    }

    function celulaInfo(value) {
        const raw = String(value || '').trim();
        const normalized = raw.toLowerCase().replace(/\s+/g, '');
        if (normalized === 'despachos' || normalized === 'despacho' || normalized === '1') {
            return { label: 'Despachos', cls: 'despachos' };
        }
        if (normalized === 'callcenter' || normalized === 'call' || normalized === '2') {
            return { label: 'Call Center', cls: 'callcenter' };
        }
        return { label: 'Campo', cls: 'campo' };
    }

    function celulaBadge(value) {
        const info = celulaInfo(value);
        return `<span class="cvh-celula-badge ${info.cls}">${esc(info.label)}</span>`;
    }

    function renderCatalogos(catalogos) {
        if (!catalogoCargado) {
            productosCatalogo = catalogos?.productos || [];
            renderProductoOptions('');
            renderCelulaOptions('');
            catalogoCargado = true;
        }
        gestoresCatalogo = catalogos?.gestores || [];
        renderGestorOptions('');
    }

    function renderProductoOptions(query) {
        const q = String(query || '').trim().toLowerCase();
        const actual = productoSelect.value;
        const rows = [{ id: '', nombre: 'Todas' }, ...productosCatalogo]
            .filter((p) => String(p.nombre || '').toLowerCase().includes(q));
        productoOptions.innerHTML = rows.length ? rows.map((p) => {
            const value = String(p.id ?? '');
            const active = value === actual ? ' active' : '';
            return `<button type="button" class="cvh-search-option${active}" data-type="producto" data-value="${esc(value)}">
                <span>${esc(p.nombre || 'Todas')}</span>
            </button>`;
        }).join('') : '<div class="cvh-search-empty">Sin coincidencias.</div>';
        updateProductoButton();
    }

    function renderCelulaOptions(query) {
        const q = String(query || '').trim().toLowerCase();
        const actual = celulaSelect.value;
        const rows = celulasCatalogo.filter((c) => c.label.toLowerCase().includes(q));
        celulaOptions.innerHTML = rows.length ? rows.map((c) => {
            const active = c.value === actual ? ' active' : '';
            const badge = c.value ? celulaBadge(c.celula) : '';
            return `<button type="button" class="cvh-search-option${active}" data-type="celula" data-value="${esc(c.value)}">
                <span>${esc(c.label)}</span>${badge}
            </button>`;
        }).join('') : '<div class="cvh-search-empty">Sin coincidencias.</div>';
        updateCelulaButton();
    }

    function renderGestorOptions(query) {
        const q = String(query || '').trim().toLowerCase();
        const actualGestor = gestorSelect.value;
        const vistos = new Set();
        const options = [];
        [{ usuario: '', celula: '', total: 0 }, ...gestoresCatalogo].forEach((g) => {
            const usuario = String(g.usuario || '').trim();
            const label = usuario || 'Todos';
            if (usuario && vistos.has(usuario)) return;
            if (!label.toLowerCase().includes(q)) return;
            if (usuario) vistos.add(usuario);
            const value = usuario;
            const celula = celulaInfo(g.celula);
            const active = value === actualGestor ? ' active' : '';
            const badge = usuario ? celulaBadge(g.celula) : '';
            options.push(`<button type="button" class="cvh-search-option${active}" data-type="gestor" data-value="${esc(value)}">
                <span>${esc(label)}</span>${badge}
            </button>`);
        });
        gestorOptions.innerHTML = options.length ? options.join('') : '<div class="cvh-search-empty">Sin coincidencias.</div>';
        if (actualGestor && !gestoresCatalogo.some((g) => String(g.usuario || '').trim() === actualGestor)) {
            gestorSelect.value = '';
        }
        updateGestorButton();
    }

    function updateProductoButton() {
        const selected = productosCatalogo.find((p) => String(p.id) === String(productoSelect.value));
        productoButton.querySelector('span').textContent = selected ? selected.nombre : 'Todas';
    }

    function updateCelulaButton() {
        const selected = celulasCatalogo.find((c) => c.value === celulaSelect.value);
        celulaButton.querySelector('span').innerHTML = selected && selected.value
            ? `${esc(selected.label)} ${celulaBadge(selected.celula)}`
            : 'Todas';
    }

    function updateGestorButton() {
        const selected = gestoresCatalogo.find((g) => String(g.usuario || '').trim() === gestorSelect.value);
        gestorButton.querySelector('span').innerHTML = selected
            ? `${esc(selected.usuario)} ${celulaBadge(selected.celula)}`
            : 'Todos';
    }

    function openDropdown(dropdown, searchInput, renderFn) {
        document.querySelectorAll('.cvh-search-select.open').forEach((node) => {
            if (node !== dropdown) node.classList.remove('open');
        });
        dropdown.classList.toggle('open');
        if (dropdown.classList.contains('open')) {
            searchInput.value = '';
            renderFn('');
            setTimeout(() => searchInput.focus(), 0);
        }
    }

    function closeDropdowns() {
        document.querySelectorAll('.cvh-search-select.open').forEach((node) => node.classList.remove('open'));
    }

    function selectSearchOption(type, value) {
        if (type === 'producto') {
            productoSelect.value = value;
            updateProductoButton();
            renderProductoOptions(productoSearch.value);
        } else if (type === 'celula') {
            celulaSelect.value = value;
            updateCelulaButton();
            renderCelulaOptions(celulaSearch.value);
        } else if (type === 'gestor') {
            gestorSelect.value = value;
            updateGestorButton();
            renderGestorOptions(gestorSearch.value);
        }
        closeDropdowns();
        scheduleLoad(0);
    }

    function renderResumen(resumen) {
        document.getElementById('kTotal').textContent = Number(resumen?.total_convenios || 0).toLocaleString('es-MX');
        document.getElementById('kOriginal').textContent = money(resumen?.monto_original);
        document.getElementById('kConvenio').textContent = money(resumen?.monto_convenio);
        document.getElementById('kDescuento').textContent = money(resumen?.descuento_total);
        document.getElementById('kPagado').textContent = money(resumen?.total_pagado);
        document.getElementById('kSaldo').textContent = money(resumen?.saldo_reportado);
        renderMiniCharts(resumen || {});
    }

    function renderMiniCharts(resumen) {
        const original = Number(resumen.monto_original || 0);
        const descuento = Number(resumen.descuento_total || 0);
        const pagado = Number(resumen.total_pagado || 0);
        const total = original + descuento + pagado;
        const setSegment = (valueId, barId, value) => {
            const pct = total > 0 && value > 0 ? Math.max(3, (value / total) * 100) : 0;
            document.getElementById(valueId).textContent = money(value);
            document.getElementById(barId).style.width = `${pct.toFixed(1)}%`;
        };

        setSegment('chartOriginalValue', 'chartOriginalBar', original);
        setSegment('chartDescuentoValue', 'chartDescuentoBar', descuento);
        setSegment('chartPagadoValue', 'chartPagadoBar', pagado);
    }

    function detalleStatusBadge(value) {
        const v = String(value || '').toLowerCase();
        const labels = {
            activo: 'Activo',
            completado: 'Liquidado',
            cancelado: 'Cancelado',
            pagado: 'Pagado',
            parcial: 'Parcial',
            vencido: 'Vencido',
            pendiente: 'Pendiente',
            pendiente_conciliar: 'Pendiente conciliar'
        };
        const label = labels[v] || (v ? v.charAt(0).toUpperCase() + v.slice(1) : 'Sin estatus');
        const known = ['activo', 'completado', 'cancelado', 'pagado', 'parcial', 'vencido', 'pendiente', 'pendiente_conciliar'];
        const cls = known.includes(v) ? v : 'neutral';
        return `<span class="cvi-pill ${cls}">${esc(label)}</span>`;
    }

    function detallePagoTotal(row) {
        return numericMoney(row.monto_pagado) + Number(row.monto_secundario || 0);
    }

    function detalleSet(id, value) {
        const node = document.getElementById(id);
        if (node) node.textContent = value;
    }

    function detalleHtml(id, html) {
        const node = document.getElementById(id);
        if (node) node.innerHTML = html;
    }

    function renderDetalleInfo(convenio) {
        const items = [
            ['ID convenio', convenio.id_convenio],
            ['ID crédito', convenio.id_credito],
            ['Fecha convenio', fmtDate(convenio.fecha_acuerdo || convenio.fecha_alta)],
            ['Primer pago', fmtDate(convenio.fecha_primer_pago)],
            ['Ultimo pago', fmtDate(convenio.fecha_ultimo_pago)],
            ['Célula', convenio.celula],
            ['Usuario alta', convenio.usuario_alta],
            ['Calendario', convenio.tipo_calendario || convenio.frecuencia],
            ['Base calculo', convenio.base_calculo || '--'],
            ['Monto adicional', money(convenio.monto_adicional)],
            ['Pago semanal', money(convenio.pago_semanal)],
            ['Cancelamiento', convenio.fecha_cancelacion ? fmtDate(convenio.fecha_cancelacion) : 'Sin cancelacion'],
        ];
        detalleHtml('cviModalInfoGrid', items.map(([label, value]) => {
            return `<div class="cvi-info"><span>${esc(label)}</span><strong>${esc(value ?? '--')}</strong></div>`;
        }).join(''));
    }

    function renderDetalleAmortizacion(rows) {
        if (!rows.length) {
            detalleHtml('cviModalAmortizacion', '<tr><td colspan="6" class="text-center text-muted py-4">Sin tabla de amortización registrada.</td></tr>');
            return;
        }
        detalleHtml('cviModalAmortizacion', rows.map((row) => {
            const comprobante = row.comprobante_path
                ? `<a class="btn btn-sm btn-outline-primary" href="${esc(row.comprobante_path)}" target="_blank" rel="noopener"><i class="fa-solid fa-paperclip"></i></a>`
                : '<span class="text-muted">--</span>';
            return `<tr>
                <td class="fw-bold">${esc(row.numero_semana)}</td>
                <td>
                    <div>${fmtDate(row.fecha_pago)}</div>
                    <div class="text-muted small">${row.fecha_pago_real ? 'Real: ' + fmtDate(row.fecha_pago_real) : ''}</div>
                </td>
                <td>${money(row.pago_semanal)}</td>
                <td class="fw-bold">${money(detallePagoTotal(row))}</td>
                <td>${detalleStatusBadge(row.estatus_pago)}</td>
                <td>${comprobante}</td>
            </tr>`;
        }).join(''));
    }

    function renderDetalleBitacora(rows) {
        if (!rows.length) {
            detalleHtml('cviModalBitacora', '<div class="text-muted">Sin eventos registrados.</div>');
            return;
        }
        detalleHtml('cviModalBitacora', rows.map((event) => {
            const hora = String(event.fecha || '').length > 10 ? ` ${esc(String(event.fecha).slice(11, 16))}` : '';
            const usuario = event.usuario ? `<div class="small text-muted">Usuario: ${esc(event.usuario)}</div>` : '';
            return `<div class="cvi-event">
                <div class="small text-muted mb-1">${fmtDate(event.fecha)}${hora}</div>
                <h6>${esc(event.titulo)}</h6>
                <p>${esc(event.detalle || '')}</p>
                ${usuario}
            </div>`;
        }).join(''));
    }

    function renderDetalleReporte(datos) {
        const convenio = datos.convenio || {};
        const amortizacion = datos.amortizacion || [];
        const pagado = amortizacion.reduce((sum, row) => sum + detallePagoTotal(row), 0);
        const totalConvenio = Number(convenio.total_a_pagar || 0);
        const saldo = Math.max(totalConvenio - pagado, 0);

        detalleSet('cviModalCliente', convenio.nombre_cliente || 'Sin nombre');
        detalleSet('cviModalOferta', convenio.oferta_seleccionada || 'Sin oferta');
        detalleHtml('cviModalStatus', detalleStatusBadge(convenio.estatus));
        detalleHtml('cviModalReactivado', Number(convenio.es_reactivado || 0) === 1
            ? '<span class="cvi-pill reactivado ms-2"><i class="fa-solid fa-rotate"></i>Reactivado</span>'
            : '');

        const pdf = document.getElementById('cviModalPdf');
        if (pdf) {
            if (convenio.pdf_adjunto) {
                pdf.href = convenio.pdf_adjunto;
                pdf.classList.remove('d-none');
            } else {
                pdf.classList.add('d-none');
                pdf.removeAttribute('href');
            }
        }

        detalleSet('cviModalOriginal', money(convenio.adeudo_total_original || convenio.monto_original));
        detalleSet('cviModalDescuento', `${money(convenio.descuento_monto)} (${Number(convenio.porcentaje_descuento || 0).toFixed(2)}%)`);
        detalleSet('cviModalConvenio', money(totalConvenio));
        detalleSet('cviModalPagado', money(pagado));
        detalleSet('cviModalSaldo', money(saldo));
        detalleSet('cviModalSemanas', convenio.numero_semanas || amortizacion.length || 0);

        renderDetalleInfo(convenio);
        renderDetalleAmortizacion(amortizacion);
        renderDetalleBitacora(datos.bitacora || []);

        detalleMessage.classList.add('d-none');
        detalleContent.classList.remove('d-none');
    }

    function setDetalleLoading(isLoading) {
        detalleLoading.classList.toggle('d-none', !isLoading);
        if (isLoading) {
            detalleMessage.classList.add('d-none');
            detalleContent.classList.add('d-none');
        }
    }

    function showDetalleError(message) {
        detalleContent.classList.add('d-none');
        detalleMessage.classList.remove('d-none');
        detalleMessage.innerHTML = `<i class="fa-solid fa-triangle-exclamation me-1"></i>${esc(message)}`;
    }

    function mostrarDetalleModal() {
        if (!detalleModalEl) return;
        if (detalleModal) {
            detalleModal.show();
            return;
        }
        if (window.jQuery && typeof window.jQuery(detalleModalEl).modal === 'function') {
            window.jQuery(detalleModalEl).modal('show');
            return;
        }
        detalleModalEl.classList.add('show');
        detalleModalEl.style.display = 'block';
        detalleModalEl.removeAttribute('aria-hidden');
        detalleModalEl.setAttribute('aria-modal', 'true');
        document.body.classList.add('modal-open');
    }

    function cerrarDetalleModalManual() {
        if (!detalleModalEl || detalleModal) return;
        if (window.jQuery && typeof window.jQuery(detalleModalEl).modal === 'function') return;
        detalleModalEl.classList.remove('show');
        detalleModalEl.style.display = 'none';
        detalleModalEl.setAttribute('aria-hidden', 'true');
        detalleModalEl.removeAttribute('aria-modal');
        document.body.classList.remove('modal-open');
    }

    async function abrirDetalleConvenio(idConvenio) {
        const id = Number(idConvenio || 0);
        if (!id) return;
        mostrarDetalleModal();
        const seq = ++detalleRequestSeq;
        setDetalleLoading(true);
        try {
            const params = new URLSearchParams({ id_convenio: String(id) });
            const response = await fetch(`/convenios/reporteIndividualDatos?${params.toString()}`, {
                headers: { 'Accept': 'application/json' }
            });
            const payload = await response.json();
            if (!payload.success) {
                throw new Error(payload.mensaje || 'No se pudo cargar el reporte individual.');
            }
            if (seq !== detalleRequestSeq) return;
            renderDetalleReporte(payload.datos || {});
        } catch (err) {
            if (seq !== detalleRequestSeq) return;
            showDetalleError(err.message || 'No se pudo cargar el reporte individual.');
        } finally {
            if (seq === detalleRequestSeq) setDetalleLoading(false);
        }
    }

    function renderRows(rows) {
        currentRows = rows || [];
        empty.classList.toggle('d-none', currentRows.length > 0);
        if (!currentRows.length) {
            tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted py-4">Sin resultados.</td></tr>';
            return;
        }

        tbody.innerHTML = currentRows.map((row) => {
            const montoConvenio = Number(row.monto_convenio || 0);
            const pagado = Number(row.total_pagado || 0);
            const saldo = Number(row.saldo_reportado || 0);
            const pct = montoConvenio > 0 ? Math.max(0, Math.min(100, (pagado / montoConvenio) * 100)) : 0;
            const reactivado = Number(row.es_reactivado || 0) === 1
                ? '<span class="cvh-badge cvh-reactivado ms-1"><i class="fa-solid fa-rotate"></i>Reactivado</span>'
                : '';
            const celula = celulaInfo(row.celula);

            return `<tr>
                <td>
                    <div class="fw-bold">${fmtDate(row.fecha_convenio)}</div>
                    <div class="cvh-muted">Convenio #${esc(row.id_convenio)}</div>
                </td>
                <td>
                    <div class="fw-bold">${esc(row.nombre_cliente || 'Sin nombre')}</div>
                    <div class="cvh-muted">${esc(celula.label)}</div>
                </td>
                <td class="fw-bold">${esc(row.id_credito)}</td>
                <td class="cvh-money">${money(row.monto_original)}</td>
                <td>
                    <div class="fw-bold">${esc(row.oferta_seleccionada)}</div>
                    <div class="cvh-muted">Alta: ${esc(row.usuario_alta || '--')} - ${celulaBadge(row.celula)}</div>
                    <div>${reactivado}</div>
                </td>
                <td>
                    <div class="cvh-money">${money(row.descuento_monto)}</div>
                    <div class="cvh-muted">${Number(row.porcentaje_descuento || 0).toFixed(2)}%</div>
                </td>
                <td>
                    <div class="cvh-money">${money(montoConvenio)}</div>
                    <div class="cvh-muted">${esc(row.numero_semanas || 0)} semanas</div>
                </td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <div class="cvh-progress"><span style="width:${pct.toFixed(1)}%"></span></div>
                        <strong>${pct.toFixed(0)}%</strong>
                    </div>
                    <div class="cvh-muted">Pagado ${money(pagado)} / Restante ${money(saldo)}</div>
                </td>
                <td>${statusBadge(row.estatus)}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-primary cvh-ver-detalle" data-id-convenio="${esc(row.id_convenio)}">
                        <i class="fa-solid fa-eye me-1"></i>Ver
                    </button>
                </td>
            </tr>`;
        }).join('');
    }

    async function loadHistorico() {
        const seq = ++requestSeq;
        const params = new URLSearchParams(new FormData(form));
        tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted py-4">Cargando información...</td></tr>';
        try {
            const response = await fetch(`/convenios/reporteHistoricoDatos?${params.toString()}`, {
                headers: { 'Accept': 'application/json' }
            });
            const payload = await response.json();
            if (!payload.success) {
                throw new Error(payload.mensaje || 'No se pudo cargar el reporte.');
            }
            if (seq !== requestSeq) return;
            renderCatalogos(payload.datos?.catalogos || {});
            renderResumen(payload.datos?.resumen || {});
            renderRows(payload.datos?.rows || []);
        } catch (err) {
            if (seq !== requestSeq) return;
            tbody.innerHTML = `<tr><td colspan="10" class="text-center text-danger py-4">${esc(err.message)}</td></tr>`;
            renderResumen({});
            currentRows = [];
        }
    }

    function scheduleLoad(delay = 250) {
        clearTimeout(loadTimer);
        loadTimer = setTimeout(loadHistorico, delay);
    }

    function exportExcel() {
        const params = new URLSearchParams(new FormData(form));
        window.location.href = `/convenios/reporteHistoricoExcel?${params.toString()}`;
    }

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        loadHistorico();
    });
    form.addEventListener('input', (event) => {
        if (event.target && event.target.classList.contains('cvh-search-input')) return;
        scheduleLoad(event.target && event.target.name === 'q' ? 350 : 150);
    });
    form.addEventListener('change', () => scheduleLoad(0));
    tbody.addEventListener('click', (event) => {
        const button = event.target.closest('.cvh-ver-detalle');
        if (!button) return;
        abrirDetalleConvenio(button.dataset.idConvenio || '');
    });
    detalleModalEl?.querySelectorAll('[data-bs-dismiss="modal"], [data-dismiss="modal"]').forEach((button) => {
        button.addEventListener('click', cerrarDetalleModalManual);
    });
    productoButton.addEventListener('click', () => openDropdown(productoDropdown, productoSearch, renderProductoOptions));
    celulaButton.addEventListener('click', () => openDropdown(celulaDropdown, celulaSearch, renderCelulaOptions));
    gestorButton.addEventListener('click', () => openDropdown(gestorDropdown, gestorSearch, renderGestorOptions));
    productoSearch.addEventListener('input', (event) => {
        event.stopPropagation();
        renderProductoOptions(event.target.value);
    });
    celulaSearch.addEventListener('input', (event) => {
        event.stopPropagation();
        renderCelulaOptions(event.target.value);
    });
    gestorSearch.addEventListener('input', (event) => {
        event.stopPropagation();
        renderGestorOptions(event.target.value);
    });
    productoOptions.addEventListener('click', (event) => {
        const option = event.target.closest('.cvh-search-option');
        if (!option) return;
        selectSearchOption(option.dataset.type, option.dataset.value || '');
    });
    celulaOptions.addEventListener('click', (event) => {
        const option = event.target.closest('.cvh-search-option');
        if (!option) return;
        selectSearchOption(option.dataset.type, option.dataset.value || '');
    });
    gestorOptions.addEventListener('click', (event) => {
        const option = event.target.closest('.cvh-search-option');
        if (!option) return;
        selectSearchOption(option.dataset.type, option.dataset.value || '');
    });
    document.addEventListener('click', (event) => {
        if (!event.target.closest('.cvh-search-select')) closeDropdowns();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeDropdowns();
    });
    exportBtn.addEventListener('click', exportExcel);
    loadHistorico();
})();
</script>
