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

    .metric-row.total {
        background-color: #f8f9fa;
        padding: 12px 10px;
        margin: 10px -10px -10px -10px;
        font-weight: 700;
    }

    .big-metric-value {
        font-size: 3rem;
        font-weight: 700;
        color: #2d3561;
        margin-bottom: 10px;
    }

    .big-metric-label {
        font-size: 0.95rem;
        color: #666;
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
        .big-metric-value {
            font-size: 2rem;
        }
        .metric-row {
            flex-direction: column;
            align-items: flex-start;
        }
        .metric-row .metric-value {
            margin-top: 0.25rem;
        }
    }
</style>

<div class="container py-4">
    <!-- Título -->
    <div class="row mb-3">
        <div class="col-12">
            <h4 class="mb-0">Seguimiento</h4>
            <p class="text-muted small">Dashboard de Seguimiento</p>
        </div>
    </div>

    <div class="row">
        <!-- Sidebar con filtros -->
        <div class="col-lg-3 sidebar">
            <div class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Lideres</label>
                        <select class="form-select" id="lideres-filter">
                            <option>Todas</option>
                            <option>Líder 1</option>
                            <option>Líder 2</option>
                            <option>Líder 3</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Territoriales</label>
                        <select class="form-select" id="territoriales-filter">
                            <option>CETINA DUARTE RAU...</option>
                            <option>Territorial 1</option>
                            <option>Territorial 2</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Zonal</label>
                        <select class="form-select" id="zonal-filter">
                            <option>Todas</option>
                            <option>Zonal 1</option>
                            <option>Zonal 2</option>
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
            <!-- Tarjeta de métricas -->
            <div class="card mb-4">
                <div class="row g-0">
                    <!-- Sección izquierda: Tabla de datos -->
                    <div class="col-md-6">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center pb-3 mb-3 border-bottom">
                                <span class="text-muted">Cierre_Actual</span>
                                <small class="text-muted">Recuento de Id_credito</small>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span>a) Current</span>
                                <span class="fw-bold">210</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span>b) 1 a 7 días</span>
                                <span class="fw-bold">168</span>
                            </div>
                            <div class="metric-row total d-flex justify-content-between py-2">
                                <span>Total</span>
                                <span class="fw-bold">378</span>
                            </div>
                        </div>
                    </div>

                    <!-- Sección derecha: Métrica grande -->
                    <div class="col-md-6">
                        <div class="card-body text-center">
                            <div class="big-metric-value">378</div>
                            <div class="big-metric-label">Recuento de Id_credito</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla principal -->
            <div class="card card-datatable">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped dt-responsive" id="tabla-seguimiento">
                            <thead>
                                <tr>
                                    <th>Id_credito</th>
                                    <th>Bucket_Morosidad_Real</th>
                                    <th>Cierre_Actual</th>
                                    <th>Nombre_cliente</th>
                                    <th>Gestor_Asignado</th>
                                    <th>Jefe_de_Plaza</th>
                                    <th>Territorial</th>
                                    <th>Lideres</th>
                                    <th class="text-right">Saldo_vencido</th>
                                    <th class="text-right">Conteo_campo</th>
                                    <th class="text-right">Conteo_telefono</th>
                                    <th class="text-right">Conteo_whatsapp</th>
                                    <th>Ultimo_Dictamen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>318699</td>
                                    <td>b) 1 a 7 días</td>
                                    <td>b) 1 a 7 días</td>
                                    <td>ABDIEL SANCHEZ JERONIMO</td>
                                    <td>ARIAS HERNANDEZ GUSTAVO</td>
                                    <td>BARRUETA DE DIOS CARLOS HUM</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td>-</td>
                                </tr>
                                <tr>
                                    <td>968739</td>
                                    <td>b) 1 a 7 días</td>
                                    <td>b) 1 a 7 días</td>
                                    <td>ABELARDO GALVEZ RODRIGUEZ</td>
                                    <td>MORALES JIMENEZ ADRIAN DE JESUS</td>
                                    <td>ESCOBAR GOMEZ NAUN</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td>-</td>
                                </tr>
                                <tr>
                                    <td>521474</td>
                                    <td>b) 1 a 7 días</td>
                                    <td>b) 1 a 7 días</td>
                                    <td>ADELFO SANDOVAL PATRACA</td>
                                    <td>OCHOA SANTIZ DANIEL</td>
                                    <td>REYES GONZALEZ RENE</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td>-</td>
                                </tr>
                                <tr>
                                    <td>1401840</td>
                                    <td>b) 1 a 7 días</td>
                                    <td>b) 1 a 7 días</td>
                                    <td>ADELINA ELIZABETH INCHON ORELLA</td>
                                    <td>ESPINOSA GONZALEZ ROMEO</td>
                                    <td>REYES GONZALEZ RENE</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td>-</td>
                                </tr>
                                <tr>
                                    <td>1027446</td>
                                    <td>b) 1 a 7 días</td>
                                    <td>b) 1 a 7 días</td>
                                    <td>ADRIAN ORTIZ DANIEL</td>
                                    <td>OCHOA SANTIZ DANIEL</td>
                                    <td>REYES GONZALEZ RENE</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td>-</td>
                                </tr>
                                <tr>
                                    <td>1153258</td>
                                    <td>b) 1 a 7 días</td>
                                    <td>a) Current</td>
                                    <td>ADRIANA LISSETTE JIMENEZ ARRIAGA</td>
                                    <td>TORRES TRUJILLO OMAR CONCEPCION</td>
                                    <td>REYES GONZALEZ RENE</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td>-</td>
                                </tr>
                                <tr>
                                    <td>1543673</td>
                                    <td>b) 1 a 7 días</td>
                                    <td>b) 1 a 7 días</td>
                                    <td>ADRIANA NOEMI GOMEZ MAZARIEGOS</td>
                                    <td>ALVARADO MADRID IGNACIO</td>
                                    <td>REYES GONZALEZ RENE</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td>-</td>
                                </tr>
                                <tr>
                                    <td>1436809</td>
                                    <td>b) 1 a 7 días</td>
                                    <td>a) Current</td>
                                    <td>AGUILENE MOLINA PEREZ</td>
                                    <td>TORRES TRUJILLO OMAR CONCEPCION</td>
                                    <td>REYES GONZALEZ RENE</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td>-</td>
                                </tr>
                                <tr>
                                    <td>176645</td>
                                    <td>b) 1 a 7 días</td>
                                    <td>a) Current</td>
                                    <td>AGUSTIN ORTIZ GALVEZ</td>
                                    <td>SANCHEZ BORRAYES HERIBERTO</td>
                                    <td>REYES GONZALEZ RENE</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td>-</td>
                                </tr>
                                <tr>
                                    <td>781830</td>
                                    <td>b) 1 a 7 días</td>
                                    <td>a) Current</td>
                                    <td>AIDE PEREZ VIDAL</td>
                                    <td>ALVARADO GONZALEZ ASUNCION</td>
                                    <td>BARRUETA DE DIOS CARLOS HUM</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td>-</td>
                                </tr>
                                <tr>
                                    <td>372349</td>
                                    <td>b) 1 a 7 días</td>
                                    <td>b) 1 a 7 días</td>
                                    <td>ALAN RICARDO BELEN DE LA CRUZ</td>
                                    <td>ESPINOSA GONZALEZ ROMEO</td>
                                    <td>REYES GONZALEZ RENE</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td>-</td>
                                </tr>
                                <tr>
                                    <td>1370427</td>
                                    <td>b) 1 a 7 días</td>
                                    <td>a) Current</td>
                                    <td>ALBERTO CARLOS JIMENEZ CRUZ</td>
                                    <td>ALVARADO MADRID IGNACIO</td>
                                    <td>REYES GONZALEZ RENE</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td>-</td>
                                </tr>
                                <tr>
                                    <td>145831</td>
                                    <td>b) 1 a 7 días</td>
                                    <td>b) 1 a 7 días</td>
                                    <td>ALDAIR JAVIER ARPAEZ</td>
                                    <td>ARIAS HERNANDEZ GUSTAVO</td>
                                    <td>BARRUETA DE DIOS CARLOS HUM</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td>-</td>
                                </tr>
                                <tr>
                                    <td>607064</td>
                                    <td>b) 1 a 7 días</td>
                                    <td>a) Current</td>
                                    <td>ALDO DAVID PEÑALOZA MATIAS</td>
                                    <td>BACILIO ROSALES OSCAR ALBERTO</td>
                                    <td>ESCOBAR GOMEZ NAUN</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td>-</td>
                                </tr>
                                <tr class="total-row">
                                    <td>Total</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td></td>
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
    </div>
</div>

<script>
    // Event listeners para los filtros
    const filters = {
        lideres: document.getElementById('lideres-filter'),
        territoriales: document.getElementById('territoriales-filter'),
        zonal: document.getElementById('zonal-filter'),
        campana: document.getElementById('campana-filter')
    };

    Object.keys(filters).forEach(key => {
        filters[key].addEventListener('change', function(e) {
            loadSeguimientoData();
        });
    });

    // Función para cargar datos desde el backend
    async function loadSeguimientoData() {
        try {
            // const response = await fetch('/api/seguimiento', {
            //     method: 'POST',
            //     headers: { 'Content-Type': 'application/json' },
            //     body: JSON.stringify({
            //         lideres: filters.lideres.value,
            //         territoriales: filters.territoriales.value,
            //         zonal: filters.zonal.value,
            //         campana: filters.campana.value
            //     })
            // });
            // const data = await response.json();

            // Actualizar métricas
            // updateMetrics(data.metrics);

            // Actualizar tabla principal
            // updateMainTable(data.records);

        } catch (error) {
            console.error('Error cargando datos de seguimiento:', error);
        }
    }

    // Función para actualizar métricas
    function updateMetrics(metrics) {
        // Actualizar valores de la tarjeta izquierda
        const metricRows = document.querySelectorAll('.metric-row');
        if (metrics && metrics.cierre_actual) {
            // Actualizar a) Current
            metricRows[0].querySelector('.fw-bold').textContent = metrics.cierre_actual.current || '0';
            // Actualizar b) 1 a 7 días
            metricRows[1].querySelector('.fw-bold').textContent = metrics.cierre_actual.dias_1_7 || '0';
            // Actualizar Total
            const total = (parseInt(metrics.cierre_actual.current) || 0) + (parseInt(metrics.cierre_actual.dias_1_7) || 0);
            metricRows[2].querySelector('.fw-bold').textContent = total;

            // Actualizar métrica grande
            document.querySelector('.big-metric-value').textContent = total;
        }
    }

    // Función para actualizar tabla principal
    function updateMainTable(records) {
        const tbody = document.querySelector('#tabla-seguimiento tbody');
        if (!records || records.length === 0) {
            tbody.innerHTML = '<tr><td colspan="13" class="text-center text-muted">No hay datos disponibles</td></tr>';
            return;
        }

        // Calcular totales
        let totalSaldoVencido = 0;
        let totalCampo = 0;
        let totalTelefono = 0;
        let totalWhatsapp = 0;

        const rows = records.map(row => {
            totalSaldoVencido += parseFloat(row.saldo_vencido) || 0;
            totalCampo += parseInt(row.conteo_campo) || 0;
            totalTelefono += parseInt(row.conteo_telefono) || 0;
            totalWhatsapp += parseInt(row.conteo_whatsapp) || 0;

            return `
                <tr>
                    <td>${row.id_credito}</td>
                    <td>${row.bucket_morosidad_real}</td>
                    <td>${row.cierre_actual}</td>
                    <td>${row.nombre_cliente}</td>
                    <td>${row.gestor_asignado}</td>
                    <td>${row.jefe_plaza}</td>
                    <td>${row.territorial || '-'}</td>
                    <td>${row.lideres || '-'}</td>
                    <td class="text-right">${row.saldo_vencido ? formatCurrency(row.saldo_vencido) : '-'}</td>
                    <td class="text-right">${row.conteo_campo || '-'}</td>
                    <td class="text-right">${row.conteo_telefono || '-'}</td>
                    <td class="text-right">${row.conteo_whatsapp || '-'}</td>
                    <td>${row.ultimo_dictamen || '-'}</td>
                </tr>
            `;
        }).join('');

        const totalRow = `
            <tr class="total-row">
                <td>Total</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td class="text-right">${formatCurrency(totalSaldoVencido)}</td>
                <td class="text-right">${totalCampo}</td>
                <td class="text-right">${totalTelefono}</td>
                <td class="text-right">${totalWhatsapp}</td>
                <td></td>
            </tr>
        `;

        tbody.innerHTML = rows + totalRow;
    }

    // Función para formatear moneda
    function formatCurrency(value) {
        return new Intl.NumberFormat('es-MX', {
            style: 'currency',
            currency: 'MXN',
            minimumFractionDigits: 2
        }).format(value);
    }

    // Inicializar
    // loadSeguimientoData();
</script>