<style>
    #tabla-reportes {
        width: 100%;
        table-layout: auto;
        min-width: 2000px;
    }

    #tabla-reportes th {
        position: relative;
        min-width: 120px;
    }

    #tabla-reportes th.resizable {
        position: relative;
    }

    #tabla-reportes .resizer {
        position: absolute;
        top: 0;
        right: 0;
        width: 5px;
        cursor: col-resize;
        user-select: none;
        height: 100%;
        background: transparent;
    }

    #tabla-reportes .resizer:hover {
        background: #4CAF50;
    }

    #tabla-reportes .resizing {
        border-right: 2px solid #4CAF50;
    }

    #tabla-reportes thead th {
        position: sticky;
        top: 0;
        background: #ffffff;
        z-index: 10;
        font-size: 0.75rem;
        white-space: nowrap;
        padding: 0.5rem 0.25rem;
    }

    #tabla-reportes tbody td,
    #tabla-reportes tfoot td {
        font-size: 0.8rem;
        padding: 0.4rem 0.25rem;
    }

    #tabla-reportes td:first-child {
        white-space: normal;
        word-break: break-word;
    }

    #tabla-reportes td:last-child {
        width: 110px;
        text-align: right;
        white-space: nowrap;
    }

    #tabla-reportes small {
        white-space: normal;
        word-break: break-word;
    }

    #tabla-reportes.border-top {
        border-top: none !important;
    }

    #tabla-reportes tbody tr:last-child td {
        border-bottom: none !important;
    }

    #tabla-reportes th:nth-child(n+2):nth-child(-n+9),
    #tabla-reportes td:nth-child(n+2):nth-child(-n+9) {
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
        .form-select, .form-check-input {
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
            <h4 class="mb-0">Reportes de Gestiones 1 a 7</h4>
            <p class="text-muted small">Indicadores de Gestiones 1 a 7</p>
        </div>
    </div>

    <!-- Card principal -->
    <div class="card">
        <div class="card-body">

            <!-- ===================== -->
            <!-- FILTROS SUPERIORES -->
            <!-- ===================== -->

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label for="filtro-campana" class="form-label fw-bold">Campaña</label>
                    <select class="form-select" id="filtro-campana">
                        <option value="ASIGNACION_W07_1A7">ASIGNACION_W07_1A7</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Fuera de Zona</label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="false" id="fuera-zona-false">
                            <label class="form-check-label" for="fuera-zona-false">
                                False
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="true" id="fuera-zona-true" checked>
                            <label class="form-check-label" for="fuera-zona-true">
                                True
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===================== -->
            <!-- TABLA PRINCIPAL -->
            <!-- ===================== -->

            <div class="card-datatable table-responsive">
                <table id="tabla-reportes" class="dt-responsive table border-top">
                    <thead>
                        <tr>
                            <th>Líderes</th>
                            <th>CURRENT</th>
                            <th>1 A 7 DÍAS</th>
                            <th>SIN GESTIÓN</th>
                            <th>EFICIENCIA</th>
                            <th>TOTAL GENERAL</th>
                            <th>CAMPO</th>
                            <th>TELEFONO</th>
                            <th>SALDO VENCIDO</th>
                            <th>Fecha Dictamen Más Antigua</th>
                            <th>Fecha Dictamen Más Reciente</th>
                            <th>Estatus Actividad Gestor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- JS inyectará filas -->
                    </tbody>
                    <tfoot>
                        <tr>
                            <td><strong>Total</strong></td>
                            <td id="total-current"><strong>0</strong></td>
                            <td id="total-1a7"><strong>0</strong></td>
                            <td id="total-sin-gestion"><strong>0</strong></td>
                            <td id="total-eficiencia"><strong>0%</strong></td>
                            <td id="total-general"><strong>0</strong></td>
                            <td id="total-campo"><strong>0</strong></td>
                            <td id="total-telefono"><strong>0</strong></td>
                            <td id="total-saldo"><strong>$0</strong></td>
                            <td><strong>-</strong></td>
                            <td><strong>-</strong></td>
                            <td><strong>-</strong></td>
                        </tr>
                    </tfoot>
                </table>
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
    console.log("Vista Gestiones 1 a 7 lista para lógica JS");

    // ==========================================
    // FUNCIONALIDAD DE REDIMENSIONAR COLUMNAS
    // ==========================================
    const table = document.getElementById('tabla-reportes');
    const headers = table.querySelectorAll('thead th');
    
    headers.forEach((th, index) => {
        th.classList.add('resizable');
        
        // Crear el elemento resizer
        const resizer = document.createElement('div');
        resizer.classList.add('resizer');
        th.appendChild(resizer);
        
        let startX, startWidth;
        
        resizer.addEventListener('mousedown', (e) => {
            startX = e.pageX;
            startWidth = th.offsetWidth;
            
            th.classList.add('resizing');
            
            const mouseMoveHandler = (e) => {
                const width = startWidth + (e.pageX - startX);
                th.style.width = width + 'px';
                
                // Aplicar el mismo ancho a todas las celdas de la columna
                const cells = table.querySelectorAll(`tbody td:nth-child(${index + 1}), tfoot td:nth-child(${index + 1})`);
                cells.forEach(cell => {
                    cell.style.width = width + 'px';
                });
            };
            
            const mouseUpHandler = () => {
                th.classList.remove('resizing');
                document.removeEventListener('mousemove', mouseMoveHandler);
                document.removeEventListener('mouseup', mouseUpHandler);
            };
            
            document.addEventListener('mousemove', mouseMoveHandler);
            document.addEventListener('mouseup', mouseUpHandler);
        });
    });

    // Aquí podrás:
    // 1. Cargar datos vía fetch()
    // 2. Filtrar según selects y checkboxes
    // 3. Recalcular totales dinámicamente
    // 4. Renderizar tabla dinámicamente

    // Ejemplo básico: puedes integrar con los datos pasados desde el controlador
    // const data = [/* datos del modelo */];
    // renderTable(data);
});
</script>