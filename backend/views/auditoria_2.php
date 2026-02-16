<style>
    /* Estilos consistentes con all_gestores.php */
    .card-datatable {
        box-shadow: 0 0 0.875rem 0 rgba(33, 37, 41, 0.1);
        border: 1px solid rgba(33, 37, 41, 0.125);
        border-radius: 0.375rem;
    }

    .dt-responsive {
        border-top: 1px solid #dee2e6;
    }

    .table thead th {
        position: sticky;
        top: 0;
        background-color: #f8f9fa;
        z-index: 10;
    }

    .table-responsive {
        max-height: 70vh;
        overflow-y: auto;
    }

    .text-right {
        text-align: right !important;
    }

    .total-row {
        background-color: #e8e8f0 !important;
        font-weight: 700;
    }

    .total-row td {
        border-top: 2px solid #2d3561;
        padding-top: 14px;
        padding-bottom: 14px;
    }

    /* Responsividad para móviles */
    @media (max-width: 576px) {
        .container {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }
        .card-body {
            padding: 1rem;
        }
        .table th, .table td {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .card-title {
            font-size: 1.5rem;
        }
        .filter-row .col-md-4 {
            margin-bottom: 1rem;
        }
    }
</style>

<div class="container py-4">
    <!-- Título -->
    <div class="row mb-3">
        <div class="col-12">
            <h4 class="mb-0">Auditoría 2</h4>
            <p class="text-muted small">Dashboard de Auditoría 2</p>
        </div>
    </div>

    <!-- Header con filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row filter-row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">AUDITORIA</label>
                        <select class="form-select" id="auditoria-filter">
                            <option>Estrategia Dovoedo</option>
                            <option>Estrategia 1</option>
                            <option>Estrategia 2</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Campana</label>
                        <select class="form-select" id="campana-filter">
                            <option>ASIGNACION_W07_1A7</option>
                            <option>ASIGNACION_W08_1A7</option>
                            <option>ASIGNACION_W09_1A7</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="text-end">
                            (En blanco)
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjeta de métrica -->
    <div class="card mb-4">
        <div class="card-body text-center">
            <p class="text-muted fs-4">(En blanco)</p>
            <small class="text-muted">Recuento de Id_credito</small>
        </div>
    </div>

    <!-- Tabla Seguimiento a promesas de pago -->
    <div class="card card-datatable mb-4">
        <div class="card-body">
            <h5 class="card-title">Seguimiento a promesas de pago</h5>
            <div class="table-responsive">
                <table class="table table-striped dt-responsive" id="tabla-seguimiento">
                    <thead>
                        <tr>
                            <th>LIDERES</th>
                            <th class="text-right">Promesa de Pago</th>
                            <th class="text-right">Promesa de Pago cumplida</th>
                            <th class="text-right">% Promesas Cumplidas</th>
                            <th class="text-right">Promesa de Pago vigentes</th>
                            <th class="text-right">% Promesa de Pago vigentes</th>
                            <th class="text-right">Promesas de Pago vencidas</th>
                            <th class="text-right">% Promesas de Pago vencidas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="8" class="text-center text-muted">No hay datos disponibles</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tabla LEGACY -->
    <div class="card card-datatable mb-4">
        <div class="card-body">
            <h5 class="card-title">LEGACY</h5>
            <div class="table-responsive">
                <table class="table table-striped dt-responsive" id="tabla-legacy">
                    <thead>
                        <tr>
                            <th>GESTOR ASIGNADO</th>
                            <th>id_credit</th>
                            <th>Cierre_Actual</th>
                            <th>Nombre_cliente</th>
                            <th>fecha_de_promesa_de_pago</th>
                            <th>hora_de_promesa_de_pago</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="6" class="text-center text-muted">No hay datos disponibles</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Logo -->
    <div class="text-end mt-4">
        <img src="/assets/logo.png" width="100" alt="Maxikash">
    </div>
</div>

<script>
    // Event listeners para los filtros
    const filters = {
        auditoria: document.getElementById('auditoria-filter'),
        campana: document.getElementById('campana-filter')
    };

    Object.keys(filters).forEach(key => {
        filters[key].addEventListener('change', function(e) {
            console.log(`Filtro ${key} cambiado a:`, e.target.value);
            loadAuditoria2Data();
        });
    });

    // Función para cargar datos desde el backend
    async function loadAuditoria2Data() {
        try {
            // const response = await fetch('/api/auditoria2', {
            //     method: 'POST',
            //     headers: { 'Content-Type': 'application/json' },
            //     body: JSON.stringify({
            //         auditoria: filters.auditoria.value,
            //         campana: filters.campana.value
            //     })
            // });
            // const data = await response.json();

            // Actualizar tabla Seguimiento a promesas de pago
            // updateSeguimientoTable(data.seguimiento);

            // Actualizar tabla LEGACY
            // updateLegacyTable(data.legacy);

            console.log('Datos de Auditoría 2 cargados');
        } catch (error) {
            console.error('Error cargando datos de auditoría 2:', error);
        }
    }

    // Función para actualizar tabla de Seguimiento
    function updateSeguimientoTable(data) {
        const tbody = document.querySelector('#tabla-seguimiento tbody');
        if (!data || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No hay datos disponibles</td></tr>';
            return;
        }

        const rows = data.map(row => `
            <tr>
                <td>${row.lider}</td>
                <td class="text-right">${row.promesa_pago || '-'}</td>
                <td class="text-right">${row.promesa_cumplida || '-'}</td>
                <td class="text-right">${row.pct_cumplidas || '-'}</td>
                <td class="text-right">${row.promesa_vigentes || '-'}</td>
                <td class="text-right">${row.pct_vigentes || '-'}</td>
                <td class="text-right">${row.promesa_vencidas || '-'}</td>
                <td class="text-right">${row.pct_vencidas || '-'}</td>
            </tr>
        `).join('');

        // Calcular totales
        const totals = data.reduce((acc, row) => ({
            promesa_pago: acc.promesa_pago + (parseInt(row.promesa_pago) || 0),
            promesa_cumplida: acc.promesa_cumplida + (parseInt(row.promesa_cumplida) || 0),
            promesa_vigentes: acc.promesa_vigentes + (parseInt(row.promesa_vigentes) || 0),
            promesa_vencidas: acc.promesa_vencidas + (parseInt(row.promesa_vencidas) || 0)
        }), { promesa_pago: 0, promesa_cumplida: 0, promesa_vigentes: 0, promesa_vencidas: 0 });

        const pctCumplidas = totals.promesa_pago > 0 ?
            ((totals.promesa_cumplida / totals.promesa_pago) * 100).toFixed(2) + '%' : '-';
        const pctVigentes = totals.promesa_pago > 0 ?
            ((totals.promesa_vigentes / totals.promesa_pago) * 100).toFixed(2) + '%' : '-';
        const pctVencidas = totals.promesa_pago > 0 ?
            ((totals.promesa_vencidas / totals.promesa_pago) * 100).toFixed(2) + '%' : '-';

        const totalRow = `
            <tr class="total-row">
                <td>Total</td>
                <td class="text-right">${totals.promesa_pago}</td>
                <td class="text-right">${totals.promesa_cumplida}</td>
                <td class="text-right">${pctCumplidas}</td>
                <td class="text-right">${totals.promesa_vigentes}</td>
                <td class="text-right">${pctVigentes}</td>
                <td class="text-right">${totals.promesa_vencidas}</td>
                <td class="text-right">${pctVencidas}</td>
            </tr>
        `;

        tbody.innerHTML = rows + totalRow;
    }

    // Función para actualizar tabla LEGACY
    function updateLegacyTable(data) {
        const tbody = document.querySelector('#tabla-legacy tbody');
        if (!data || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No hay datos disponibles</td></tr>';
            return;
        }

        tbody.innerHTML = data.map(row => `
            <tr>
                <td>${row.gestor_asignado || '-'}</td>
                <td>${row.id_credit || '-'}</td>
                <td>${row.cierre_actual || '-'}</td>
                <td>${row.nombre_cliente || '-'}</td>
                <td>${row.fecha_promesa || '-'}</td>
                <td>${row.hora_promesa || '-'}</td>
            </tr>
        `).join('');
    }

    // Función para formatear porcentajes
    function formatPercentage(value) {
        return new Intl.NumberFormat('es-MX', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(value) + '%';
    }

    // Inicializar
    console.log('Dashboard de Auditoría 2 inicializado');
    // loadAuditoria2Data();
</script>