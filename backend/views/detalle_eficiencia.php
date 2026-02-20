<style>
    #tabla-detalle-eficiencia {
        width: 100%;
        table-layout: fixed;
        min-width: 1200px;
    }

    #tabla-detalle-eficiencia thead th {
        position: sticky;
        top: 0;
        background: #e5e7eb;
        z-index: 10;
    }

    #tabla-detalle-eficiencia td:first-child {
        white-space: normal;
        word-break: break-word;
    }

    #tabla-detalle-eficiencia td:last-child {
        width: 110px;
        text-align: right;
        white-space: nowrap;
    }

    #tabla-detalle-eficiencia small {
        white-space: normal;
        word-break: break-word;
    }

    #tabla-detalle-eficiencia.border-top {
        border-top: none !important;
    }

    #tabla-detalle-eficiencia tbody tr:last-child td {
        border-bottom: none !important;
    }

    #tabla-detalle-eficiencia th:nth-child(n+2),
    #tabla-detalle-eficiencia td:nth-child(n+2) {
        text-align: right;
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
        .form-label {
            font-size: 0.875rem;
        }
        .form-select {
            font-size: 0.875rem;
        }
    }

    #maxicash-logo {
        width: 100px;
        height: auto;
    }
</style>

<div class="container py-4">

    <!-- Título -->
    <div class="row mb-3">
        <div class="col-12">
            <h4 class="mb-0">DETALLE EFICIENCIA</h4>
            <p class="text-muted small">Indicadores de Detalle Eficiencia</p>
        </div>
    </div>

    <!-- Card principal -->
    <div class="card">
        <div class="card-body">

            <!-- ===================== -->
            <!-- FILTROS SUPERIORES -->
            <!-- ===================== -->

            <div class="row g-3 mb-3">
                <div class="col-md-2">
                    <label for="filtro-lideres" class="form-label fw-bold">Líderes</label>
                    <select class="form-select" id="filtro-lideres">
                        <option>MARIO BROS</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="filtro-territoriales" class="form-label fw-bold">Territoriales</label>
                    <select class="form-select" id="filtro-territoriales">
                        <option>ALVARADO SANCHEZ</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="filtro-zonal" class="form-label fw-bold">Zonal</label>
                    <select class="form-select" id="filtro-zonal">
                        <option>HERRERA BARRIOS</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="filtro-gestor" class="form-label fw-bold">Gestor</label>
                    <select class="form-select" id="filtro-gestor">
                        <option value="all">Todas</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="filtro-id-credito" class="form-label fw-bold">ID crédito</label>
                    <select class="form-select" id="filtro-id-credito">
                        <option value="all">Todas</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="filtro-contacto" class="form-label fw-bold">Contacto</label>
                    <select class="form-select" id="filtro-contacto">
                        <option value="all">Todas</option>
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-2">
                    <label for="filtro-campana" class="form-label fw-bold">Campaña</label>
                    <select class="form-select" id="filtro-campana">
                        <option>Selección múltiple</option>
                    </select>
                </div>
            </div>

            <!-- ===================== -->
            <!-- KPIs -->
            <!-- ===================== -->

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card text-center border-primary">
                        <div class="card-body">
                            <h2 class="card-title text-primary" id="kpi-fecha-reciente">(En blanco)</h2>
                            <p class="card-text small">Fecha Dictamen Más Reciente</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card text-center border-warning">
                        <div class="card-body">
                            <h2 class="card-title text-warning" id="kpi-creditos-unicos">(En blanco)</h2>
                            <p class="card-text small">Créditos Únicos</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card text-center border-danger">
                        <div class="card-body">
                            <h2 class="card-title text-danger" id="kpi-fecha-antigua">(En blanco)</h2>
                            <p class="card-text small">Fecha Dictamen Más Antigua</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===================== -->
            <!-- CONTENIDO CENTRAL -->
            <!-- ===================== -->

            <div class="row g-3">
                <!-- Panel Dictamen -->
                <div class="col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Dictamen</h5>
                            <!-- Aquí irá lista o filtros dinámicos -->
                        </div>
                    </div>
                </div>

                <!-- Tabla Principal -->
                <div class="col-lg-9">
                    <div class="card-datatable table-responsive">
                        <table id="tabla-detalle-eficiencia" class="dt-responsive table border-top">
                            <thead>
                                <tr>
                                    <th>id_credito</th>
                                    <th>Cierre_Actual</th>
                                    <th>dictamen_for</th>
                                    <th>fecha_de_promesa_de_pago</th>
                                    <th>Nombre_cliente</th>
                                    <th>Referencia_stp</th>
                                    <th>Observaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Render dinámico -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Footer Logo -->
    <div class="row mt-4">
        <div class="col-12 text-end">
            <img src="/assets/img/Logotipo-Maxikash-Outline.webp" alt="Maxicash Logo" id="maxicash-logo">
        </div>
    </div>

</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    // Aquí podrás:
    // 1. Cargar datos vía fetch()
    // 2. Filtrar según selects
    // 3. Recalcular KPIs dinámicamente
    // 4. Renderizar tabla dinámicamente

    // Ejemplo básico: puedes integrar con los datos pasados desde el controlador
    // const data = [/* datos del modelo */];
    // renderTable(data);
});
</script>