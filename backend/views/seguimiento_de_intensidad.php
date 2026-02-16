<style>
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
    .empty-table {
        text-align: center;
        padding: 40px 20px;
        color: #999;
    }
    .metric-value {
        font-size: 3rem;
        font-weight: 700;
        color: #2d3561;
    }
    .metric-title {
        font-size: 1rem;
        color: #666;
        margin-bottom: 15px;
    }
    .metric-subtitle {
        font-size: 0.85rem;
        color: #999;
        margin-top: 10px;
    }
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
        .metric-value {
            font-size: 2rem;
        }
    }
</style>

<div class="container py-4">
    <!-- Header -->
    <div class="card mb-4">
        <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
            <h4 class="mb-0">JERARQUIA MAXIKASH</h4>
            <div class="d-flex align-items-center gap-2">
                <span class="form-label mb-0">Campaña</span>
                <select class="form-select" id="campana-filter" style="min-width:200px;">
                    <option value="">Seleccionar campaña</option>
                    <option>ASIGNACION_W07_1A7</option>
                    <option>ASIGNACION_W08_1A7</option>
                    <option>ASIGNACION_W09_1A7</option>
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main content -->
        <div class="col-lg-9">
            <!-- Tabla superior -->
            <div class="card card-datatable mb-4">
                <div class="card-body">
                    <h5 class="card-title">DETALLE GESTIÓN CAMPO</h5>
                    <div class="table-responsive">
                        <table class="table table-striped dt-responsive" id="tabla-seguimiento-intensidad">
                            <thead>
                                <tr>
                                    <th>Variable_3</th>
                                    <th>Id_credito</th>
                                    <th class="text-right">Conteo Campo</th>
                                    <th>Domicilio_completo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="4" class="empty-table">Seleccione filtros para ver los datos</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tarjetas de métricas -->
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <div class="metric-title">(En blanco)</div>
                            <div class="metric-value">-</div>
                            <div class="metric-subtitle">Total de ID Credito</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <div class="metric-title">(En blanco)</div>
                            <div class="metric-value">-</div>
                            <div class="metric-subtitle">Gestiones Campo</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mapa -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Coordenadas de Gestiones Campo</h5>
                    <div id="map" style="height: 500px; width: 100%; border-radius: 6px; border: 1px solid #e0e0e0;"></div>
                    <div class="text-end mt-2" style="font-size:0.8rem; color:#666;">Powered by Leaflet & OpenStreetMap</div>
                </div>
            </div>

            <!-- Logo -->
            <div class="text-end mt-4">
                <img src="/assets/logo.png" width="100" alt="Maxikash">
            </div>
        </div>

        <!-- Sidebar derecho con checkboxes -->
        <div class="col-lg-3">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-body">
                    <div class="mb-2 fw-bold">Filtros adicionales</div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="check-seguimiento" value="seguimiento_current">
                        <label class="form-check-label" for="check-seguimiento">Seguimiento a current</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="check-visita-lider" value="visita_lider">
                        <label class="form-check-label" for="check-visita-lider">Visita por parte de líder o gestor más cercano | Llama...</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="check-visita-urgente" value="visita_urgente">
                        <label class="form-check-label" for="check-visita-urgente">Visita urgente</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="check-intensidad" value="intensidad_gestion">
                        <label class="form-check-label" for="check-intensidad">Intensidad de gestión y visita de supervisor</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="check-sabuesos" value="envio_sabuesos">
                        <label class="form-check-label" for="check-sabuesos">Envío a Sabuesos</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    let map;
    let markers = [];
    function initMap() {
        map = L.map('map').setView([19.4326, -99.1332], 5);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);
        addSampleMarkers();
    }
    function addSampleMarkers() {
        const sampleLocations = [
            { lat: 19.4326, lng: -99.1332, name: 'Ciudad de México', info: 'ID: 12345, Gestiones: 5' },
            { lat: 19.0414, lng: -98.2063, name: 'Puebla', info: 'ID: 67890, Gestiones: 3' },
            { lat: 20.6597, lng: -103.3496, name: 'Guadalajara', info: 'ID: 11223, Gestiones: 7' },
            { lat: 25.6866, lng: -100.3161, name: 'Monterrey', info: 'ID: 44556, Gestiones: 4' }
        ];
        sampleLocations.forEach(location => {
            const marker = L.marker([location.lat, location.lng]).addTo(map);
            marker.bindPopup(`
                <strong>${location.name}</strong><br>
                ${location.info}
            `);
            markers.push(marker);
        });
    }
    function clearMarkers() {
        markers.forEach(marker => map.removeLayer(marker));
        markers = [];
    }
    function addMarkersFromData(data) {
        clearMarkers();
        if (!data || data.length === 0) return;
        data.forEach(item => {
            if (item.latitud && item.longitud) {
                const marker = L.marker([item.latitud, item.longitud]).addTo(map);
                marker.bindPopup(`
                    <strong>ID: ${item.id_credito}</strong><br>
                    Gestor: ${item.gestor || 'N/A'}<br>
                    Gestiones: ${item.conteo_campo || 0}<br>
                    Dirección: ${item.domicilio_completo || 'N/A'}
                `);
                markers.push(marker);
            }
        });
        if (markers.length > 0) {
            const group = new L.featureGroup(markers);
            map.fitBounds(group.getBounds().pad(0.1));
        }
    }
    const campanaFilter = document.getElementById('campana-filter');
    const checkboxes = document.querySelectorAll('.form-check-input');
    campanaFilter.addEventListener('change', function() {
        loadData();
    });
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            loadData();
        });
    });
    async function loadData() {
        const selectedFilters = Array.from(checkboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);
        try {
            // const response = await fetch('/api/seguimiento-intensidad', {
            //     method: 'POST',
            //     headers: { 'Content-Type': 'application/json' },
            //     body: JSON.stringify({
            //         campana: campanaFilter.value,
            //         filters: selectedFilters
            //     })
            // });
            // const data = await response.json();
            // updateTable(data.records);
            // updateMetrics(data.metrics);
            // addMarkersFromData(data.locations);
        } catch (error) {
            console.error('Error cargando datos:', error);
        }
    }
    function updateTable(records) {
        const tbody = document.querySelector('#tabla-seguimiento-intensidad tbody');
        if (!records || records.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="empty-table">No hay datos disponibles</td></tr>';
            return;
        }
        tbody.innerHTML = records.map(row => `
            <tr>
                <td>${row.variable_3 || '-'}</td>
                <td>${row.id_credito}</td>
                <td class="text-right">${row.conteo_campo || 0}</td>
                <td>${row.domicilio_completo || '-'}</td>
            </tr>
        `).join('');
    }
    function updateMetrics(metrics) {
        const metricValues = document.querySelectorAll('.metric-value');
        if (metrics) {
            metricValues[0].textContent = metrics.total_creditos || '-';
            metricValues[1].textContent = metrics.total_gestiones || '-';
        }
    }
    document.addEventListener('DOMContentLoaded', function() {
        initMap();
    });
</script>
