<style>
    #tabla-detalle-clientes {
        width: 100%;
        table-layout: auto;
        min-width: 1500px;
    }

    #tabla-detalle-clientes th {
        position: relative;
        min-width: 120px;
    }

    #tabla-detalle-clientes th.resizable {
        position: relative;
    }

    #tabla-detalle-clientes .resizer {
        position: absolute;
        top: 0;
        right: 0;
        width: 5px;
        cursor: col-resize;
        user-select: none;
        height: 100%;
        background: transparent;
    }

    #tabla-detalle-clientes .resizer:hover {
        background: #4CAF50;
    }

    #tabla-detalle-clientes .resizing {
        border-right: 2px solid #4CAF50;
    }

    #tabla-detalle-clientes thead th {
        position: sticky;
        top: 0;
        background: #e5e7eb;
        z-index: 10;
        font-size: 0.75rem;
        white-space: nowrap;
        padding: 0.5rem 0.25rem;
    }

    #tabla-detalle-clientes tbody td,
    #tabla-detalle-clientes tfoot td {
        font-size: 0.8rem;
        padding: 0.4rem 0.25rem;
    }

    #tabla-detalle-clientes td:first-child {
        white-space: normal;
        word-break: break-word;
    }

    #tabla-detalle-clientes td:last-child {
        width: 110px;
        text-align: right;
        white-space: nowrap;
    }

    #tabla-detalle-clientes small {
        white-space: normal;
        word-break: break-word;
    }

    #tabla-detalle-clientes.border-top {
        border-top: none !important;
    }

    #tabla-detalle-clientes tbody tr:last-child td {
        border-bottom: none !important;
    }

    #tabla-detalle-clientes th:nth-child(n+2),
    #tabla-detalle-clientes td:nth-child(n+2) {
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
            <h4 class="mb-0">DETALLE CLIENTES</h4>
            <p class="text-muted small">Indicadores de Detalle Clientes</p>
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
                    <label for="filtro-lideres" class="form-label fw-bold" style="font-size: 0.92rem;">Líderes</label>
                    <select class="form-select" id="filtro-lideres" style="font-size: 0.85rem;">
                        <option>CORTES LUQUIN</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="filtro-territoriales" class="form-label fw-bold" style="font-size: 0.92rem;">Territoriales</label>
                    <select class="form-select" id="filtro-territoriales" style="font-size: 0.85rem;">
                        <option>CORTES LUQUIN RODRIGO</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="filtro-zonal" class="form-label fw-bold" style="font-size: 0.92rem;">Zonal</label>
                    <select class="form-select" id="filtro-zonal" style="font-size: 0.85rem;">
                        <option>QUIROGA GARCIA G.</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="filtro-bucket" class="form-label fw-bold" style="font-size: 0.92rem;">Bucket</label>
                    <select class="form-select" id="filtro-bucket" style="font-size: 0.85rem;">
                        <option>a) Current</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <div class="card text-center border-primary">
                        <div class="card-body">
                            <h2 class="card-title text-primary" id="kpi-creditos">112</h2>
                            <p class="card-text small"># Créditos</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===================== -->
            <!-- TABLA PRINCIPAL -->
            <!-- ===================== -->

            <div class="card-datatable table-responsive">
                <table id="tabla-detalle-clientes" class="dt-responsive table border-top">
                    <thead>
                        <tr>
                            <th>Id_credito</th>
                            <th>Saldo Vencido</th>
                            <th>Cierre_Actual</th>
                            <th>Nombre_cliente</th>
                            <th>Referencia_stp</th>
                            <th>Gestor_Asignado</th>
                            <th>Territorial</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Render dinámico -->
                    </tbody>
                    <tfoot>
                        <tr>
                            <td><strong>Total</strong></td>
                            <td id="total-saldo"><strong>102.80</strong></td>
                            <td colspan="5"></td>
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
    console.log("Vista Detalle Clientes lista para lógica JS");

    // ==========================================
    // FUNCIONALIDAD DE REDIMENSIONAR COLUMNAS
    // ==========================================
    const table = document.getElementById('tabla-detalle-clientes');
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