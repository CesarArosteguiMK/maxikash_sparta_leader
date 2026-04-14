<style>
    /* ===== CARDS DE CONDONACIONES ===== */
    .hover-shadow {
        transition: all 0.3s ease;
    }

    .hover-shadow:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15) !important;
    }

    .cursor-pointer {
        cursor: pointer;
    }

    /* ===== DATATABLE ===== */
    .card-datatable {
        padding: 1.5rem;
    }

    .table thead th {
        background: #f8f9fa;
        font-size: 0.85rem;
        font-weight: 600;
        color: #495057;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e9ecef;
        padding: 1rem 0.75rem;
        white-space: nowrap;
    }

    .table tbody td {
        font-size: 0.85rem;
        vertical-align: middle;
        padding: 0.75rem;
        border-bottom: 1px solid #e9ecef;
    }

    .table tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.05);
        transition: all 0.2s ease;
    }

    .dataTables_paginate {
        margin-top: 1rem;
        display: flex;
        justify-content: flex-end;
    }

    .pagination {
        margin: 0;
        display: flex;
        gap: 0.25rem;
    }

    .pagination .paginate_button {
        display: inline-block;
        padding: 0.375rem 0.75rem;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        color: #0d6efd;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .pagination .paginate_button:hover {
        background: #e9ecef;
        border-color: #dee2e6;
    }

    .pagination .paginate_button.current {
        background: #0d6efd;
        border-color: #0d6efd;
        color: white;
    }

    .pagination .paginate_button.disabled {
        color: #6c757d;
        pointer-events: none;
        background: #e9ecef;
    }

    .dataTables_length {
        margin-bottom: 1rem;
    }

    .dataTables_length select {
        margin: 0 0.5rem;
        padding: 0.375rem 1.75rem 0.375rem 0.75rem;
    }

    .dataTables_filter {
        margin-bottom: 1rem;
        text-align: right;
    }

    .dataTables_filter input {
        margin-left: 0.5rem;
        padding: 0.375rem 0.75rem;
        border: 1px solid #d9dee3;
        border-radius: 0.375rem;
    }

    .dataTables_filter input:focus {
        border-color: #0d6efd;
        outline: none;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
    }

    .dataTables_info {
        margin-top: 1rem;
        font-size: 0.85rem;
        color: #6c757d;
    }

    /* ===== BADGES ===== */
    .badge {
        font-size: 0.7rem;
        padding: 0.35rem 0.65rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    /* ===== FILTROS ===== */
    .filter-container {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
        border: 1px solid #e7e7e7;
    }

    .filter-container .form-label {
        font-weight: 600;
        font-size: 0.8rem;
        color: #566a7f;
        margin-bottom: 0.5rem;
    }

    .filter-container .form-control,
    .filter-container .form-select {
        font-size: 0.875rem;
        border-radius: 6px;
    }

    /* ===== INDICADORES / KPIs (Estilo del proyecto) ===== */
    .kpi-wrapper {
        display: flex;
        justify-content: center;
        align-items: stretch;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .kpi-item {
        flex: 0 1 auto;
        min-width: 120px;
        max-width: 150px;
    }

    .kpi-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        height: 100%;
        background: #ffffff;
        border: none;
        border-radius: 0.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        position: relative;
        overflow: hidden;
        cursor: pointer;
    }

    .kpi-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--kpi-color-start), var(--kpi-color-end));
        transition: height 0.3s ease;
    }

    .kpi-card:hover {
        transform: translateY(-5px) scale(1.02);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15), 0 10px 10px -5px rgba(0, 0, 0, 0.1);
    }

    .kpi-card:hover::before {
        height: 6px;
    }

    .kpi-card.tipo-total {
        --kpi-color-start: #4F46E5;
        --kpi-color-end: #6366F1;
    }

    .kpi-card.tipo-monto {
        --kpi-color-start: #10B981;
        --kpi-color-end: #34D399;
    }

    .kpi-card.tipo-promedio {
        --kpi-color-start: #06B6D4;
        --kpi-color-end: #22D3EE;
    }

    .kpi-card.tipo-mes {
        --kpi-color-start: #F59E0B;
        --kpi-color-end: #FBBF24;
    }

    .kpi-card .card-body {
        position: relative;
        padding: 0.75rem 0.7rem;
        text-align: center;
    }

    .kpi-number {
        font-size: 1.3rem;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 0.3rem;
        background: linear-gradient(135deg, var(--kpi-color-start), var(--kpi-color-end));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-family: 'Segoe UI', system-ui, sans-serif;
        transition: all 0.3s ease;
    }

    .kpi-card:hover .kpi-number {
        transform: scale(1.1);
        filter: brightness(1.2);
    }

    .kpi-label {
        font-size: 0.55rem;
        color: #64748B;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0;
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .kpi-icon {
        font-size: 1.2rem;
        opacity: 0.15;
        position: absolute;
        right: 0.5rem;
        top: 0.5rem;
        color: var(--kpi-color-start);
        transition: all 0.3s ease;
    }

    .kpi-card:hover .kpi-icon {
        opacity: 0.25;
        transform: scale(1.1) rotate(5deg);
    }

    /* ===== MODAL DETALLE ===== */
    .detalle-row {
        padding: 0.75rem 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .detalle-row:last-child {
        border-bottom: none;
    }

    .detalle-label {
        font-weight: 600;
        color: #566a7f;
        margin-bottom: 0.25rem;
    }

    .detalle-value {
        color: #333;
    }

    /* ===== TABLA DE DETALLES DE GASTOS ===== */
    .detalles-gastos-table {
        font-size: 0.85rem;
    }

    .detalles-gastos-table thead th {
        background-color: #f5f5f5;
        color: #566a7f;
        font-weight: 600;
        padding: 0.75rem 0.5rem;
    }

    .detalles-gastos-table tbody td {
        padding: 0.65rem 0.5rem;
    }

    /* ===== BOTONES ===== */
    .btn-sm {
        padding: 0.4rem 1rem;
        font-size: 0.875rem;
    }

    .btn i {
        margin-right: 0.25rem;
    }

    /* ===== LOADING SPINNER ===== */
    .spinner-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 300px;
    }

    .spinner-border {
        width: 3rem;
        height: 3rem;
        color: #696cff;
    }

    /* ===== EMPTY STATE ===== */
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
    }

    .empty-state i {
        font-size: 4rem;
        color: #d0d0d0;
        margin-bottom: 1rem;
    }

    .empty-state p {
        color: #999;
        font-size: 1rem;
    }

    /* ===== ANIMACIONES ===== */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .fade-in {
        animation: fadeIn 0.3s ease;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 991px) {
        .kpi-item {
            min-width: 110px;
            max-width: 140px;
        }

        .kpi-number {
            font-size: 1.15rem;
        }

        .kpi-label {
            font-size: 0.52rem;
        }
    }

    @media (max-width: 575px) {
        .kpi-wrapper {
            justify-content: space-evenly;
        }

        .kpi-item {
            min-width: 90px;
            max-width: 110px;
        }

        .kpi-number {
            font-size: 1rem;
        }

        .kpi-label {
            font-size: 0.5rem;
        }

        .kpi-icon {
            font-size: 1rem;
        }
    }

    @media (max-width: 768px) {
        .filter-container {
            padding: 1rem;
        }

        .filter-container .row {
            gap: 0.5rem;
        }
    }
</style>

<?php if (empty($omitir_cabecera_call_center)): ?>
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">
                    <i class="bx bx-history me-2"></i>Historial de Condonaciones
                </h4>
                <p class="text-muted mb-0">Consulta y gestión de condonaciones de cobranza</p>
            </div>
        </div>
<?php else: ?>
<div class="container-fluid px-0">
<?php endif; ?>

        <!-- KPIs / Métricas -->
        <div class="row m-4 mb-3">
            <div class="col-12">
                <div class="kpi-wrapper">
                    <!-- Total -->
                    <div class="kpi-item">
                        <div class="card kpi-card tipo-total shadow-sm h-100">
                            <div class="card-body">
                                <i class="bx bx-receipt kpi-icon"></i>
                                <div class="kpi-number" id="statTotal">0</div>
                                <div class="kpi-label">Total</div>
                            </div>
                        </div>
                    </div>

                    <!-- Monto Total -->
                    <div class="kpi-item">
                        <div class="card kpi-card tipo-monto shadow-sm h-100">
                            <div class="card-body">
                                <i class="bx bx-dollar-circle kpi-icon"></i>
                                <div class="kpi-number" id="statMontoTotal">$0.00</div>
                                <div class="kpi-label">Monto Total</div>
                            </div>
                        </div>
                    </div>

                    <!-- Promedio -->
                    <div class="kpi-item">
                        <div class="card kpi-card tipo-promedio shadow-sm h-100">
                            <div class="card-body">
                                <i class="bx bx-calculator kpi-icon"></i>
                                <div class="kpi-number" id="statPromedio">$0.00</div>
                                <div class="kpi-label">Promedio</div>
                            </div>
                        </div>
                    </div>

                    <!-- Este Mes -->
                    <div class="kpi-item">
                        <div class="card kpi-card tipo-mes shadow-sm h-100">
                            <div class="card-body">
                                <i class="bx bx-calendar kpi-icon"></i>
                                <div class="kpi-number" id="statMesActual">0</div>
                                <div class="kpi-label">Este Mes</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filter-container">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Buscar</label>
                    <input type="text" class="form-control" id="buscarTexto"
                           placeholder="ID, Cliente, Usuario..."
                           onkeyup="aplicarFiltros()">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fecha Desde</label>
                    <input type="date" class="form-control" id="fechaDesde" onchange="aplicarFiltros()">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fecha Hasta</label>
                    <input type="date" class="form-control" id="fechaHasta" onchange="aplicarFiltros()">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Ordenar por</label>
                    <select class="form-select" id="ordenar" onchange="aplicarFiltros()">
                        <option value="fecha_desc">Fecha (más reciente)</option>
                        <option value="fecha_asc">Fecha (más antigua)</option>
                        <option value="monto_desc">Monto (mayor)</option>
                        <option value="monto_asc">Monto (menor)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Tabla de Condonaciones -->
        <div class="card">
            <div class="card-datatable table-responsive">
                <table id="tablaCondonaciones" class="dt-responsive table border-top">
                    <thead>
                        <tr>
                            <th></th>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>ID Crédito</th>
                            <th>Monto Condonado</th>
                            <th>Fecha</th>
                            <th>Usuario</th>
                            <th>Detalles</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="bodyCondonaciones">
                        <!-- Datos cargados dinámicamente -->
                    </tbody>
                </table>
            </div>
        </div>

<?php if (empty($omitir_cabecera_call_center)): ?>
    </div>
</div>
<?php else: ?>
</div>
<?php endif; ?>

<!-- Modal Detalle Condonación -->
<div class="modal fade" id="modalDetalleCondonacion" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bx bx-detail me-2"></i>Detalle de Condonación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="contenidoDetalleCondonacion">
                <!-- Se llena dinámicamente -->
            </div>
        </div>
    </div>
</div>



<script>
let condonacionesData = [];
let tablaCondonaciones = null;

/* ========== INICIALIZAR DATATABLE ========== */
function inicializarDataTable() {
    if ($.fn.DataTable.isDataTable('#tablaCondonaciones')) {
        $('#tablaCondonaciones').DataTable().destroy();
    }

    // Filtro por rango de fechas (campo fecha_solicitud en columna 5 - índice visible)
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        if (settings.nTable.id !== 'tablaCondonaciones') return true;
        const desde = document.getElementById('fechaDesde').value;
        const hasta = document.getElementById('fechaHasta').value;
        if (!desde && !hasta) return true;
        const row = tablaCondonaciones.row(dataIndex).data();
        if (!row) return true;
        const fechaFila = new Date(row.fecha_solicitud);
        if (desde && fechaFila < new Date(desde)) return false;
        if (hasta && fechaFila > new Date(hasta + 'T23:59:59')) return false;
        return true;
    });

    tablaCondonaciones = $('#tablaCondonaciones').DataTable({
        data: [],
        columns: [
            { orderable: false, searchable: false, data: null, defaultContent: '', className: 'control' },
            { data: 'id_condonacion', render: d => `#${d}` },
            { data: 'nombre_colaborador', defaultContent: 'N/A' },
            { data: 'id_credito' },
            {
                data: 'total_condonado',
                render: d => `<strong class="text-success">$${parseFloat(d).toFixed(2)}</strong>`
            },
            {
                data: 'fecha_solicitud',
                render: d => formatearFecha(d)
            },
            { data: 'nombre_usuario', defaultContent: 'N/A' },
            {
                data: 'total_detalles',
                render: d => `<span class="badge bg-info">${d || 0} gastos</span>`,
                orderable: false
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: (d, t, row) =>
                    `<button class="btn btn-sm btn-outline-primary" onclick="verDetalleCondonacion(${row.id_condonacion})">
                        <i class="bx bx-show"></i> Ver
                    </button>`
            }
        ],
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        order: [[5, 'desc']],
        responsive: {
            details: {
                display: $.fn.dataTable.Responsive.display.modal({
                    header: function(row) {
                        const d = row.data();
                        return 'Condonación #' + d.id_condonacion;
                    }
                }),
                renderer: $.fn.dataTable.Responsive.renderer.tableAll({
                    tableClass: 'table table-bordered'
                })
            }
        },
        language: {
            decimal: "",
            emptyTable: "No hay condonaciones registradas",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0 a 0 de 0 registros",
            infoFiltered: "(filtrado de _MAX_ registros totales)",
            thousands: ",",
            lengthMenu: "Mostrar _MENU_ registros",
            loadingRecords: "Cargando...",
            processing: "Procesando...",
            search: "Buscar:",
            zeroRecords: "No se encontraron resultados",
            paginate: {
                first: "«",
                last: "»",
                next: "›",
                previous: "‹"
            }
        },
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        autoWidth: false,
        drawCallback: function() {
            $('.dataTables_paginate > .pagination').addClass('pagination-sm');
            $('.dataTables_length select').addClass('form-select form-select-sm');
            $('.dataTables_filter input').addClass('form-control form-control-sm');
        }
    });
}

/* ========== CARGAR CONDONACIONES ========== */
function cargarCondonaciones() {
    http.request({
        endpoint: "/condonaciones/getConsultaCondonaciones",
        method: "GET",
        onSuccess: (resp) => {
            if (resp.success && resp.datos) {
                condonacionesData = resp.datos;
                tablaCondonaciones.clear().rows.add(resp.datos).draw();
                cargarEstadisticas();
            } else {
                tablaCondonaciones.clear().draw();
            }
        },
        onError: (err) => {
            console.error('Error al cargar condonaciones:', err);
            tablaCondonaciones.clear().draw();
        }
    });
}

/* ========== RENDERIZAR TABLA ========== */
/* ========== MOSTRAR DETALLE ========== */
function verDetalleCondonacion(id) {
    http.request({
        endpoint: "/condonaciones/getDetalleCondonacion",
        method: "POST",
        data: { id_condonacion: id },
        onSuccess: (resp) => {
            if (resp.success && resp.datos) {
                renderDetalleModal(resp.datos);
                const modal = new bootstrap.Modal(document.getElementById('modalDetalleCondonacion'));
                modal.show();
            }
        },
        onError: (err) => {
            console.error('Error:', err);
            showToast('Error al cargar detalle', 'error');
        }
    });
}

/* ========== RENDERIZAR DETALLE EN MODAL ========== */
function renderDetalleModal(datos) {
    const container = document.getElementById('contenidoDetalleCondonacion');

    let detallesHtml = '';
    if (datos.detalles && datos.detalles.length > 0) {
        detallesHtml = `
            <div class="mt-3">
                <h6 class="fw-bold mb-3">Gastos Condonados:</h6>
                <table class="table table-sm detalles-gastos-table">
                    <thead>
                        <tr>
                            <th>ID Gasto</th>
                            <th>Concepto</th>
                            <th>Fecha</th>
                            <th class="text-end">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        datos.detalles.forEach(det => {
            detallesHtml += `
                <tr>
                    <td>#${det.id_gastos_cobranza}</td>
                    <td>${det.concepto || 'N/A'}</td>
                    <td>${formatearFecha(det.fecha)}</td>
                    <td class="text-end">$${parseFloat(det.monto).toFixed(2)}</td>
                </tr>
            `;
        });

        detallesHtml += `
                    </tbody>
                </table>
            </div>
        `;
    }

    container.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <div class="detalle-row">
                    <div class="detalle-label">ID Condonación</div>
                    <div class="detalle-value">#${datos.id_condonacion}</div>
                </div>
                <div class="detalle-row">
                    <div class="detalle-label">Cliente</div>
                    <div class="detalle-value">${datos.nombre_colaborador || 'N/A'}</div>
                </div>
                <div class="detalle-row">
                    <div class="detalle-label">ID Cliente</div>
                    <div class="detalle-value">${datos.id_persona || 'N/A'}</div>
                </div>
                <div class="detalle-row">
                    <div class="detalle-label">Domicilio</div>
                    <div class="detalle-value">${datos.domicilio || 'N/A'}</div>
                </div>
                <div class="detalle-row">
                    <div class="detalle-label">Bucket Morosidad</div>
                    <div class="detalle-value">${datos.bucket || 'N/A'}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="detalle-row">
                    <div class="detalle-label">ID Crédito</div>
                    <div class="detalle-value">#${datos.id_credito}</div>
                </div>
                <div class="detalle-row">
                    <div class="detalle-label">Monto Condonado</div>
                    <div class="detalle-value text-success fw-bold">$${parseFloat(datos.total_condonado).toFixed(2)}</div>
                </div>
                <div class="detalle-row">
                    <div class="detalle-label">Saldo Vencido</div>
                    <div class="detalle-value text-danger">${datos.saldo_vencido ? '$' + parseFloat(datos.saldo_vencido).toFixed(2) : 'N/A'}</div>
                </div>
                <div class="detalle-row">
                    <div class="detalle-label">Días de Mora</div>
                    <div class="detalle-value ${datos.dias_mora > 30 ? 'text-danger' : 'text-warning'}">${datos.dias_mora || '0'} días</div>
                </div>
                <div class="detalle-row">
                    <div class="detalle-label">Fecha</div>
                    <div class="detalle-value">${formatearFecha(datos.fecha_solicitud)}</div>
                </div>
                <div class="detalle-row">
                    <div class="detalle-label">Usuario</div>
                    <div class="detalle-value">${datos.nombre_usuario || datos.usuario || 'N/A'}</div>
                </div>
            </div>
        </div>
        ${datos.comentario ? `
        <div class="mt-3">
            <div class="detalle-label">Comentario</div>
            <div class="detalle-value mt-2 p-3" style="background-color: #f8f9fa; border-radius: 6px;">
                ${datos.comentario}
            </div>
        </div>
        ` : ''}
        ${detallesHtml}
    `;
}



/* ========== ESTADÍSTICAS ========== */
function cargarEstadisticas() {
    http.request({
        endpoint: "/condonaciones/getEstadisticas",
        method: "GET",
        onSuccess: (resp) => {
            if (resp.success && resp.datos) {
                document.getElementById('statTotal').textContent = resp.datos.total || 0;
                document.getElementById('statMontoTotal').textContent =
                    '$' + parseFloat(resp.datos.monto_total || 0).toFixed(2);
                document.getElementById('statPromedio').textContent =
                    '$' + parseFloat(resp.datos.monto_promedio || 0).toFixed(2);

                // Calcular mes actual
                const mesActual = condonacionesData.filter(c => {
                    const fecha = new Date(c.fecha_solicitud);
                    const hoy = new Date();
                    return fecha.getMonth() === hoy.getMonth() &&
                           fecha.getFullYear() === hoy.getFullYear();
                }).length;
                document.getElementById('statMesActual').textContent = mesActual;
            }
        }
    });
}

/* ========== FILTROS ========== */
function aplicarFiltros() {
    if (!tablaCondonaciones) return;

    // Búsqueda de texto (usa el search nativo de DataTables)
    const buscar = document.getElementById('buscarTexto').value;
    tablaCondonaciones.search(buscar);

    // Ordenar por select externo
    const ordenar = document.getElementById('ordenar').value;
    const ordenMap = {
        fecha_desc:  [5, 'desc'],
        fecha_asc:   [5, 'asc'],
        monto_desc:  [4, 'desc'],
        monto_asc:   [4, 'asc']
    };
    if (ordenMap[ordenar]) {
        tablaCondonaciones.order(ordenMap[ordenar]);
    }

    // Redibujar con filtros de fecha (gestionados por ext.search)
    tablaCondonaciones.draw();
}

/* ========== UTILIDADES ========== */
function formatearFecha(fecha) {
    if (!fecha) return 'N/A';
    const f = new Date(fecha);
    return f.toLocaleDateString('es-MX', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function showToast(mensaje, tipo = 'info') {
    const colores = {
        success: '#28a745',
        error: '#dc3545',
        warning: '#ffc107',
        info: '#17a2b8'
    };

    const toast = document.createElement('div');
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 250px;
        background-color: ${colores[tipo] || colores.info};
        color: #fff;
        padding: 1rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        animation: fadeIn 0.3s ease;
    `;
    toast.textContent = mensaje;

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

/* ========== INICIALIZACIÓN ========== */
document.addEventListener('DOMContentLoaded', function() {
    inicializarDataTable();
    cargarCondonaciones();
});
</script>
