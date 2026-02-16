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
        .sidebar {
            margin-bottom: 1rem;
        }
        .bottom-tables .col-md-6 {
            margin-bottom: 1rem;
        }
    }
</style>

<div class="container py-4">
    <!-- Título -->
    <div class="row mb-3">
        <div class="col-12">
            <h4 class="mb-0">Auditoría</h4>
            <p class="text-muted small">Dashboard de Auditoría</p>
        </div>
    </div>

    <div class="row">
        <!-- Sidebar con filtros -->
        <div class="col-lg-3 sidebar">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">AUDITORIA</h5>

                    <div class="mb-3">
                        <label class="form-label">Estrategia Dovoedo</label>
                        <select class="form-select" id="estrategia-filter">
                            <option>Selección</option>
                            <option>Estrategia 1</option>
                            <option>Estrategia 2</option>
                            <option>Estrategia 3</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">CIERRE ACTUAL</label>
                        <select class="form-select" id="cierre-filter">
                            <option>Selección múltiple</option>
                            <option>Current</option>
                            <option>1 a 7 días</option>
                            <option>8 a 14 días</option>
                            <option>15 a 21 días</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">LIDERES</label>
                        <select class="form-select" id="lideres-filter">
                            <option>Todas</option>
                            <option>Líder 1</option>
                            <option>Líder 2</option>
                            <option>Líder 3</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Campana</label>
                        <select class="form-select" id="campana-filter">
                            <option>ASIGNACION_W07_1A7</option>
                            <option>ASIGNACION_W08_1A7</option>
                            <option>ASIGNACION_W09_1A7</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenido Principal -->
        <div class="col-lg-9">
            <!-- Tarjetas superiores -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-subtitle mb-2 text-muted">Cierre_Actual | Recuento de Id_credito</h6>
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Total</span>
                                <span class="fw-bold">-</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body text-center">
                            <p class="text-muted">(En blanco)</p>
                            <small class="text-muted">Recuento de Id_credito</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla BD AUDITORIA -->
            <div class="card card-datatable mb-4">
                <div class="card-body">
                    <h5 class="card-title">BD AUDITORIA</h5>
                    <div class="table-responsive">
                        <table class="table table-striped dt-responsive" id="tabla-bd-auditoria">
                            <thead>
                                <tr>
                                    <th>ID CREDITO</th>
                                    <th class="text-right">SALDO TOTAL CAPITAL</th>
                                    <th>BUCKET</th>
                                    <th>GESTOR</th>
                                    <th>Jefe_</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No hay datos disponibles</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tablas inferiores -->
            <div class="row bottom-tables">
                <div class="col-md-6 mb-4">
                    <div class="card card-datatable">
                        <div class="card-body">
                            <h5 class="card-title">Eficiencia Lider 1 a 7</h5>
                            <div class="table-responsive">
                                <table class="table table-striped dt-responsive" id="tabla-eficiencia">
                                    <thead>
                                        <tr>
                                            <th>LIDERES</th>
                                            <th class="text-right">SIN GESTION</th>
                                            <th class="text-right">EFICIENCIA</th>
                                            <th class="text-right">Pagos Recibidos</th>
                                            <th class="text-right">Promesa de Pago</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="total-row">
                                            <td>Total</td>
                                            <td class="text-right">-</td>
                                            <td class="text-right">-</td>
                                            <td class="text-right">-</td>
                                            <td class="text-right">-</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <div class="card card-datatable">
                        <div class="card-body">
                            <h5 class="card-title">LEGACY</h5>
                            <div class="table-responsive">
                                <table class="table table-striped dt-responsive" id="tabla-legacy">
                                    <thead>
                                        <tr>
                                            <th>GESTOR ASIGNADO</th>
                                            <th>id_credit</th>
                                            <th>Nombre_cliente</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">No hay datos disponibles</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Logo -->
            <div class="text-end mt-4">
                <img src="/assets/logo.png" width="100" alt="Maxikash">
            </div>
        </div>
    </div>
</div>

<script>
    // Event listeners para los filtros
    const filters = {
        estrategia: document.getElementById('estrategia-filter'),
        cierre: document.getElementById('cierre-filter'),
        lideres: document.getElementById('lideres-filter'),
        campana: document.getElementById('campana-filter')
    };

    Object.keys(filters).forEach(key => {
        filters[key].addEventListener('change', function(e) {
            console.log(`Filtro ${key} cambiado a:`, e.target.value);
            // Aquí llamarás a tu backend para filtrar los datos
            loadAuditoriaData();
        });
    });

    // Función para cargar datos desde el backend
    async function loadAuditoriaData() {
        try {
            // const response = await fetch('/api/auditoria', {
            //     method: 'POST',
            //     headers: { 'Content-Type': 'application/json' },
            //     body: JSON.stringify({
            //         estrategia: filters.estrategia.value,
            //         cierre: filters.cierre.value,
            //         lideres: filters.lideres.value,
            //         campana: filters.campana.value
            //     })
            // });
            // const data = await response.json();

            // Actualizar tarjetas superiores
            // updateTopCards(data.metrics);

            // Actualizar tabla BD AUDITORIA
            // updateBDAuditoriaTable(data.bdAuditoria);

            // Actualizar tabla Eficiencia Lider
            // updateEficienciaTable(data.eficiencia);

            // Actualizar tabla LEGACY
            // updateLegacyTable(data.legacy);

            console.log('Datos de Auditoría cargados');
        } catch (error) {
            console.error('Error cargando datos de auditoría:', error);
        }
    }

    // Función para actualizar tablas dinámicamente
    function updateBDAuditoriaTable(data) {
        const tbody = document.querySelector('#tabla-bd-auditoria tbody');
        if (!data || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No hay datos disponibles</td></tr>';
            return;
        }

        tbody.innerHTML = data.map(row => `
            <tr>
                <td>${row.id_credito}</td>
                <td class="text-right">${formatCurrency(row.saldo_total_capital)}</td>
                <td>${row.bucket}</td>
                <td>${row.gestor}</td>
                <td>${row.jefe || '-'}</td>
            </tr>
        `).join('');
    }

    function updateEficienciaTable(data) {
        const tbody = document.querySelector('#tabla-eficiencia tbody');
        if (!data || data.length === 0) {
            tbody.innerHTML = `
                <tr class="total-row">
                    <td>Total</td>
                    <td class="text-right">-</td>
                    <td class="text-right">-</td>
                    <td class="text-right">-</td>
                    <td class="text-right">-</td>
                </tr>
            `;
            return;
        }

        const rows = data.map(row => `
            <tr>
                <td>${row.lider}</td>
                <td class="text-right">${row.sin_gestion || '-'}</td>
                <td class="text-right">${row.eficiencia || '-'}</td>
                <td class="text-right">${row.pagos_recibidos || '-'}</td>
                <td class="text-right">${row.promesa_pago || '-'}</td>
            </tr>
        `).join('');

        const totalRow = `
            <tr class="total-row">
                <td>Total</td>
                <td class="text-right">${data.reduce((sum, r) => sum + (r.sin_gestion || 0), 0)}</td>
                <td class="text-right">-</td>
                <td class="text-right">${data.reduce((sum, r) => sum + (r.pagos_recibidos || 0), 0)}</td>
                <td class="text-right">${data.reduce((sum, r) => sum + (r.promesa_pago || 0), 0)}</td>
            </tr>
        `;

        tbody.innerHTML = rows + totalRow;
    }

    function updateLegacyTable(data) {
        const tbody = document.querySelector('#tabla-legacy tbody');
        if (!data || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">No hay datos disponibles</td></tr>';
            return;
        }

        tbody.innerHTML = data.map(row => `
            <tr>
                <td>${row.gestor_asignado}</td>
                <td>${row.id_credit}</td>
                <td>${row.nombre_cliente}</td>
            </tr>
        `).join('');
    }

    function updateTopCards(metrics) {
        // Actualizar las tarjetas superiores con las métricas
        if (metrics && metrics.total_creditos) {
            document.querySelector('.card .fw-bold').textContent = metrics.total_creditos;
        }
    }

    // Función para formatear moneda
    function formatCurrency(value) {
        return new Intl.NumberFormat('es-MX', {
            style: 'currency',
            currency: 'MXN'
        }).format(value);
    }

    // Inicializar
    console.log('Dashboard de Auditoría inicializado');
    // loadAuditoriaData();
</script>