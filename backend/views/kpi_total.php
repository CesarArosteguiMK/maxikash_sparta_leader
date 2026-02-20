<style>
    /* ===================================
       TABLA GESTORES - RESPONSIVA MEJORADA
       =================================== */
    
    #tabla-gestores {
        width: 100%;
        table-layout: auto; /* Cambiado de fixed a auto para mejor adaptación */
        font-size: 0.9rem;
    }

    /* Headers con texto largo - permitir wrap */
    #tabla-gestores th {
        word-break: normal;
        white-space: normal;
        overflow-wrap: break-word;
        hyphens: auto;
        padding: 0.75rem 0.5rem;
        vertical-align: middle;
        font-size: 0.85rem;
        line-height: 1.3;
        min-width: 90px;
    }

    /* Celdas de datos */
    #tabla-gestores td {
        word-break: normal;
        white-space: normal;
        overflow-wrap: break-word;
        padding: 0.6rem 0.5rem;
        vertical-align: middle;
    }

    /* Primera columna (Gestor_Asignado) - más ancha */
    #tabla-gestores td:first-child,
    #tabla-gestores th:first-child {
        min-width: 140px;
        max-width: 200px;
        word-break: break-word;
        white-space: normal;
    }

    /* Columnas numéricas - alineadas a la derecha */
    #tabla-gestores td:not(:first-child),
    #tabla-gestores th:not(:first-child) {
        text-align: right;
        white-space: nowrap;
    }

    /* Última columna específica */
    #tabla-gestores td:last-child {
        width: auto;
        text-align: right;
        white-space: nowrap;
    }

    /* Footer */
    #tabla-gestores tfoot td {
        font-weight: bold;
        border-top: 2px solid #dee2e6;
        padding: 0.75rem 0.5rem;
    }

    #tabla-gestores small {
        white-space: normal;
        word-break: break-word;
    }

    #tabla-gestores.border-top {
        border-top: none !important;
    }

    #tabla-gestores tbody tr:last-child td {
        border-bottom: none !important;
    }

    /* ===================================
       TABLAS CRÉDITOS Y PROMESAS
       =================================== */
    
    #tabla-creditos td:last-child,
    #tabla-promesas td:last-child {
        text-align: right;
    }

    /* ===================================
       RESPONSIVIDAD GENERAL
       =================================== */
    
    /* Tablets y pantallas medianas (768px - 991px) */
    @media (max-width: 991px) {
        #tabla-gestores {
            font-size: 0.85rem;
        }

        #tabla-gestores th {
            font-size: 0.8rem;
            padding: 0.5rem 0.4rem;
            line-height: 1.2;
        }

        #tabla-gestores td {
            padding: 0.5rem 0.4rem;
            font-size: 0.85rem;
        }

        #tabla-gestores td:first-child,
        #tabla-gestores th:first-child {
            min-width: 120px;
            max-width: 160px;
        }
    }

    /* Pantallas pequeñas (576px - 767px) */
    @media (max-width: 767px) {
        #tabla-gestores {
            font-size: 0.8rem;
        }

        #tabla-gestores th {
            font-size: 0.75rem;
            padding: 0.4rem 0.3rem;
            line-height: 1.1;
        }

        #tabla-gestores td {
            padding: 0.4rem 0.3rem;
            font-size: 0.8rem;
        }

        #tabla-gestores td:first-child,
        #tabla-gestores th:first-child {
            min-width: 100px;
            max-width: 140px;
            font-size: 0.75rem;
        }

        /* Abreviar headers en móvil */
        #tabla-gestores th:nth-child(4) {
            font-size: 0.7rem;
        }

        #tabla-gestores th:nth-child(5) {
            font-size: 0.7rem;
        }
    }

    /* Móviles muy pequeños (< 576px) */
    @media (max-width: 576px) {
        .container {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }

        .card-body {
            padding: 1rem;
        }

        .table th, .table td {
            padding: 0.25rem 0.3rem;
            font-size: 0.75rem;
        }

        #tabla-gestores {
            font-size: 0.75rem;
        }

        #tabla-gestores th {
            font-size: 0.7rem;
            padding: 0.3rem 0.2rem;
            line-height: 1.1;
        }

        #tabla-gestores td {
            padding: 0.3rem 0.2rem;
            font-size: 0.75rem;
        }

        #tabla-gestores td:first-child,
        #tabla-gestores th:first-child {
            min-width: 90px;
            max-width: 120px;
            font-size: 0.7rem;
        }

        /* Reducir padding de columnas numéricas en móvil */
        #tabla-gestores td:not(:first-child),
        #tabla-gestores th:not(:first-child) {
            padding-left: 0.2rem;
            padding-right: 0.2rem;
        }

        .card-title {
            font-size: 1.5rem;
        }

        .form-label {
            font-size: 0.875rem;
        }

        .form-select {
            font-size: 0.875rem;
        }
    }

    /* Extra pequeño (< 400px) */
    @media (max-width: 400px) {
        #tabla-gestores {
            font-size: 0.7rem;
        }

        #tabla-gestores th {
            font-size: 0.65rem;
            padding: 0.25rem 0.15rem;
        }

        #tabla-gestores td {
            padding: 0.25rem 0.15rem;
            font-size: 0.7rem;
        }

        #tabla-gestores td:first-child,
        #tabla-gestores th:first-child {
            min-width: 80px;
            max-width: 100px;
        }
    }

    /* ===================================
       LOGO MAXICASH
       =================================== */
    
    #maxicash-logo {
        width: 100px;
        height: auto;
    }

    @media (max-width: 576px) {
        #maxicash-logo {
            width: 80px;
        }
    }

    /* ===================================
       CONTENEDOR DE TABLA CON SCROLL
       =================================== */
    
    .card-datatable {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch; /* Smooth scrolling en iOS */
    }

    /* Scroll horizontal suave en móviles */
    @media (max-width: 767px) {
        .card-datatable {
            max-width: 100%;
            overflow-x: auto;
        }

        /* Sombra para indicar que hay scroll */
        .card-datatable::-webkit-scrollbar {
            height: 6px;
        }

        .card-datatable::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .card-datatable::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }

        .card-datatable::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    }

    /* ===================================
       MEJORAS ADICIONALES
       =================================== */
    
    /* Evitar que los números se partan */
    #tabla-gestores td:nth-child(2),
    #tabla-gestores td:nth-child(3),
    #tabla-gestores td:nth-child(4),
    #tabla-gestores td:nth-child(5) {
        white-space: nowrap;
        font-variant-numeric: tabular-nums; /* Números alineados */
    }

    /* Estilo hover para filas */
    #tabla-gestores tbody tr:hover {
        background-color: #f8f9fa;
    }

    /* Bordes más suaves */
    #tabla-gestores {
        border-collapse: collapse;
    }

    #tabla-gestores td,
    #tabla-gestores th {
        border: 1px solid #dee2e6;
    }
</style>

<!-- HTML DE LA TABLA (Sin cambios en estructura, solo optimización) -->
<div class="container py-4">

    <!-- Título -->
    <div class="row mb-3">
        <div class="col-12">
            <h4 class="mb-0">KPI Total</h4>
            <p class="text-muted small">Indicadores KPI Total</p>
        </div>
    </div>

    <!-- Card principal -->
    <div class="card">
        <div class="card-body">

            <!-- ===================== -->
            <!-- FILTROS SUPERIORES -->
            <!-- ===================== -->

            <div class="row g-3 mb-4">
                <div class="col-md-2">
                    <label for="filtro-lider" class="form-label fw-bold">Líderes</label>
                    <select class="form-select" id="filtro-lider">
                        <option value="all">Todas</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="filtro-territorial" class="form-label fw-bold">Territoriales</label>
                    <select class="form-select" id="filtro-territorial">
                        <option value="all">ALVARADO SANCHEZ...</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="filtro-zonal" class="form-label fw-bold">Zonal</label>
                    <select class="form-select" id="filtro-zonal">
                        <option value="all">Todas</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="filtro-campania" class="form-label fw-bold">Campaña Legacy</label>
                    <select class="form-select" id="filtro-campania">
                        <option value="all">Todas</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="filtro-cierre" class="form-label fw-bold">Cierre Actual</label>
                    <select class="form-select" id="filtro-cierre">
                        <option value="all">Todas</option>
                    </select>
                </div>
            </div>

            <!-- ===================== -->
            <!-- TARJETAS KPI -->
            <!-- ===================== -->

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card text-center border-primary">
                        <div class="card-body">
                            <h2 class="card-title text-primary" id="kpi-dictamen">14</h2>
                            <p class="card-text small">Gestores con dictamen &lt; 80% Campo</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card text-center border-warning">
                        <div class="card-body">
                            <h2 class="card-title text-warning" id="kpi-promesas">24</h2>
                            <p class="card-text small">Gestores con promesas de pago &lt; 60%</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card text-center border-danger">
                        <div class="card-body">
                            <h2 class="card-title text-danger" id="kpi-eficiencia">28</h2>
                            <p class="card-text small">Gestores con eficiencia &lt; 70%</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===================== -->
            <!-- TABLAS EN UNA SOLA FILA -->
            <!-- ===================== -->
            <div class="row g-3">
                <!-- Tabla Principal MEJORADA -->
                <div class="col-lg-4">
                    <div class="card-datatable table-responsive mb-3">
                        <table id="tabla-gestores" class="dt-responsive table border-top">
                            <thead>
                                <tr>
                                    <th>Gestor Asignado</th>
                                    <th>Eficiencia</th>
                                    <th>Sin Gestión</th>
                                    <th>% Gestión Campo</th>
                                    <th>% Promesas Cumplidas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Ejemplo de datos para testing -->
                                <tr>
                                    <td>JUAN CARLOS MARTINEZ HERNANDEZ</td>
                                    <td>85.5%</td>
                                    <td>0</td>
                                    <td>72.3%</td>
                                    <td>68.9%</td>
                                </tr>
                                <tr>
                                    <td>MARIA GUADALUPE RODRIGUEZ LOPEZ</td>
                                    <td>92.1%</td>
                                    <td>2</td>
                                    <td>88.7%</td>
                                    <td>75.4%</td>
                                </tr>
                                <tr>
                                    <td>JOSE ANTONIO FERNANDEZ GARCIA</td>
                                    <td>78.3%</td>
                                    <td>1</td>
                                    <td>65.2%</td>
                                    <td>58.1%</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td><strong>Total</strong></td>
                                    <td id="total-eficiencia"><strong>45.22%</strong></td>
                                    <td><strong>0</strong></td>
                                    <td id="total-gestion"><strong>62.22%</strong></td>
                                    <td id="total-promesas"><strong>39.40%</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                
                <!-- Tabla Créditos -->
                <div class="col-lg-4">
                    <div class="card mb-3">
                        <div class="card-datatable table-responsive" style="max-height: 300px;">
                            <table id="tabla-creditos" class="dt-responsive table border-top">
                                <thead>
                                    <tr>
                                        <th>Crédito</th>
                                        <th>Cierre_Actual</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Datos dinámicos -->
                                    <tr><td>a) Current</td><td>450</td></tr>
                                    <tr><td>b) 1 a 7 días</td><td>320</td></tr>
                                    <tr><td>c) 8 a 14 días</td><td>150</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Tabla Promesas -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-datatable table-responsive" style="max-height: 300px;">
                            <table id="tabla-promesas" class="dt-responsive table border-top">
                                <thead>
                                    <tr>
                                        <th>Fecha promesa</th>
                                        <th># Créditos</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Datos dinámicos -->
                                    <tr><td>2026-02-17</td><td>125</td></tr>
                                    <tr><td>2026-02-18</td><td>98</td></tr>
                                    <tr><td>2026-02-19</td><td>87</td></tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td><strong>Total</strong></td>
                                        <td id="total-creditos"><strong>920</strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ===================== -->
            <!-- FIN TABLAS EN UNA SOLA FILA -->
            <!-- ===================== -->

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
    // 4. Renderizar tablas dinámicamente
});
</script>