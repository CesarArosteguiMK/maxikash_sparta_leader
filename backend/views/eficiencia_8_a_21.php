<style>
    #tabla-eficiencia-8a21 {
        width: 100%;
        table-layout: auto;
        min-width: 3500px;
    }

    #tabla-eficiencia-8a21 th {
        position: relative;
        min-width: 120px;
    }

    #tabla-eficiencia-8a21 th.resizable {
        position: relative;
    }

    #tabla-eficiencia-8a21 .resizer {
        position: absolute;
        top: 0;
        right: 0;
        width: 5px;
        cursor: col-resize;
        user-select: none;
        height: 100%;
        background: transparent;
    }

    #tabla-eficiencia-8a21 .resizer:hover {
        background: #4CAF50;
    }

    #tabla-eficiencia-8a21 .resizing {
        border-right: 2px solid #4CAF50;
    }

    #tabla-eficiencia-8a21 thead th {
        position: sticky;
        top: 0;
        background: #e5e7eb;
        z-index: 10;
    }

    #tabla-eficiencia-8a21 td:first-child {
        white-space: normal;
        word-break: break-word;
    }

    #tabla-eficiencia-8a21 td:last-child {
        width: 110px;
        text-align: right;
        white-space: nowrap;
    }

    #tabla-eficiencia-8a21 small {
        white-space: normal;
        word-break: break-word;
    }

    #tabla-eficiencia-8a21.border-top {
        border-top: none !important;
    }

    #tabla-eficiencia-8a21 tbody tr:last-child td {
        border-bottom: none !important;
    }

    #tabla-eficiencia-8a21 th:nth-child(n+2),
    #tabla-eficiencia-8a21 td:nth-child(n+2) {
        text-align: right;
    }

    #tabla-eficiencia-8a21 thead th {
        font-size: 0.75rem;
        white-space: nowrap;
        padding: 0.5rem 0.25rem;
    }

    #tabla-eficiencia-8a21 tbody td,
    #tabla-eficiencia-8a21 tfoot td {
        font-size: 0.8rem;
        padding: 0.4rem 0.25rem;
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
            <h4 class="mb-0">Reportes de Eficiencia 8 a 21</h4>
            <p class="text-muted small">Indicadores de Eficiencia 8 a 21</p>
        </div>
    </div>

    <!-- Card principal -->
    <div class="card">
        <div class="card-body">

            <!-- ===================== -->
            <!-- FILTROS SUPERIORES -->
            <!-- ===================== -->

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="filtro-contacto" class="form-label fw-bold" style="font-size: 0.92rem;">Contacto</label>
                    <select class="form-select" id="filtro-contacto" style="max-width: 220px; font-size: 0.85rem;">
                        <option value="all">Todas</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="filtro-campana" class="form-label fw-bold" style="font-size: 0.92rem;">Campaña</label>
                    <select class="form-select" id="filtro-campana" style="max-width: 220px; font-size: 0.85rem;">
                        <option value="all">Todas</option>
                    </select>
                </div>
            </div>

            <!-- ===================== -->
            <!-- TABLA PRINCIPAL -->
            <!-- ===================== -->

            <div class="card-datatable table-responsive">
                <table id="tabla-eficiencia-8a21" class="dt-responsive table border-top">
                    <thead>
                        <tr>
                            <th>LÍDERES</th>
                            <th>CURRENT</th>
                            <th>1 A 7 DÍAS</th>
                            <th>8 A 14 DÍAS</th>
                            <th>15 A 21 DÍAS</th>
                            <th>TOTAL GENERAL</th>
                            <th>SIN GESTIÓN</th>
                            <th>SALDO VENCIDO</th>
                            <th>EFICIENCIA</th>
                            <th>Pagos Recibidos</th>
                            <th>Promesa de Pago</th>
                            <th>Negativa de Pago</th>
                            <th>Prestanombre</th>
                            <th>Contacto con Tercero</th>
                            <th>No Contesta la Llamada</th>
                            <th>Sin Contacto</th>
                            <th>Ilocalizable</th>
                            <th>Pago No Identificado</th>
                            <th>Cambio de Domicilio</th>
                            <th>Convenio Pago Parcial</th>
                            <th>Moto Recuperada</th>
                            <th>Siniestro</th>
                            <th>Defunción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Render dinámico -->
                    </tbody>
                    <tfoot>
                        <tr>
                            <td><strong>Total</strong></td>
                            <td id="t-current"><strong>0</strong></td>
                            <td id="t-1a7"><strong>0</strong></td>
                            <td id="t-8a14"><strong>0</strong></td>
                            <td id="t-15a21"><strong>0</strong></td>
                            <td id="t-general"><strong>0</strong></td>
                            <td id="t-sin"><strong>0</strong></td>
                            <td id="t-saldo"><strong>$0</strong></td>
                            <td id="t-eficiencia"><strong>0%</strong></td>
                            <td id="t-pagos"><strong>0</strong></td>
                            <td id="t-promesa"><strong>0</strong></td>
                            <td id="t-negativa"><strong>0</strong></td>
                            <td id="t-prestanombre"><strong>0</strong></td>
                            <td id="t-tercero"><strong>0</strong></td>
                            <td id="t-no-contesta"><strong>0</strong></td>
                            <td id="t-sin-contacto"><strong>0</strong></td>
                            <td id="t-ilocalizable"><strong>0</strong></td>
                            <td id="t-pago-no-id"><strong>0</strong></td>
                            <td id="t-cambio-dom"><strong>0</strong></td>
                            <td id="t-convenio"><strong>0</strong></td>
                            <td id="t-moto"><strong>0</strong></td>
                            <td id="t-siniestro"><strong>0</strong></td>
                            <td id="t-defuncion"><strong>0</strong></td>
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
    console.log("Vista Eficiencia 8 a 21 lista para lógica JS");

    // ==========================================
    // FUNCIONALIDAD DE REDIMENSIONAR COLUMNAS
    // ==========================================
    const table = document.getElementById('tabla-eficiencia-8a21');
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
    // 2. Filtrar según selects
    // 3. Recalcular totales dinámicamente
    // 4. Renderizar tabla dinámicamente

    // Ejemplo básico: puedes integrar con los datos pasados desde el controlador
    // const data = [/* datos del modelo */];
    // renderTable(data);
});
</script>