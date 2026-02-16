<style>
    #tabla-inicio-semana {
        width: 100%;
        table-layout: auto;
        min-width: 1200px;
    }

    #tabla-inicio-semana th {
        position: relative;
        min-width: 120px;
    }

    #tabla-inicio-semana th.resizable {
        position: relative;
    }

    #tabla-inicio-semana .resizer {
        position: absolute;
        top: 0;
        right: 0;
        width: 5px;
        cursor: col-resize;
        user-select: none;
        height: 100%;
        background: transparent;
    }

    #tabla-inicio-semana .resizer:hover {
        background: #4CAF50;
    }

    #tabla-inicio-semana .resizing {
        border-right: 2px solid #4CAF50;
    }

    #tabla-inicio-semana thead th {
        position: sticky;
        top: 0;
        background: #dcdcec;
        border-top: 3px solid #4B49AC;
        z-index: 10;
        font-size: 0.75rem;
        white-space: nowrap;
        padding: 0.5rem 0.25rem;
    }

    #tabla-inicio-semana tbody td {
        font-size: 0.8rem;
        padding: 0.4rem 0.25rem;
    }

    #tabla-inicio-semana td:first-child {
        white-space: normal;
        word-break: break-word;
    }

    #tabla-inicio-semana td:last-child {
        width: 110px;
        text-align: right;
        white-space: nowrap;
    }

    #tabla-inicio-semana small {
        white-space: normal;
        word-break: break-word;
    }

    #tabla-inicio-semana.border-top {
        border-top: none !important;
    }

    #tabla-inicio-semana tbody tr:last-child td {
        border-bottom: none !important;
    }

    #tabla-inicio-semana th:nth-child(n+2),
    #tabla-inicio-semana td:nth-child(n+2) {
        text-align: right;
    }

    #tabla-combinado-1,
    #tabla-combinado-2 {
        width: 100%;
        table-layout: auto;
        min-width: 900px;
    }

    #tabla-combinado-1 th,
    #tabla-combinado-2 th {
        position: relative;
        min-width: 120px;
    }

    #tabla-combinado-1 th.resizable,
    #tabla-combinado-2 th.resizable {
        position: relative;
    }

    #tabla-combinado-1 .resizer,
    #tabla-combinado-2 .resizer {
        position: absolute;
        top: 0;
        right: 0;
        width: 5px;
        cursor: col-resize;
        user-select: none;
        height: 100%;
        background: transparent;
    }

    #tabla-combinado-1 .resizer:hover,
    #tabla-combinado-2 .resizer:hover {
        background: #4CAF50;
    }

    #tabla-combinado-1 .resizing,
    #tabla-combinado-2 .resizing {
        border-right: 2px solid #4CAF50;
    }

    #tabla-combinado-1 thead th,
    #tabla-combinado-2 thead th {
        position: sticky;
        top: 0;
        background: #dcdcec;
        border-top: 3px solid #4B49AC;
        z-index: 10;
        font-size: 0.75rem;
        white-space: nowrap;
        padding: 0.5rem 0.25rem;
    }

    #tabla-combinado-1 tbody td,
    #tabla-combinado-2 tbody td {
        font-size: 0.8rem;
        padding: 0.4rem 0.25rem;
    }

    #tabla-combinado-1 th:nth-child(n+2),
    #tabla-combinado-1 td:nth-child(n+2),
    #tabla-combinado-2 th:nth-child(n+2),
    #tabla-combinado-2 td:nth-child(n+2) {
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
            <h4 class="mb-0">Reporte de Inicio de Semana</h4>
            <p class="text-muted small">Cartera Inicio de Semana</p>
        </div>
    </div>

    <!-- ================= BLOQUE 1: Inicio de Semana ================= -->

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Inicio de Semana</h5>

            <div class="card-datatable table-responsive">
                <table id="tabla-inicio-semana" class="dt-responsive table border-top">
                    <thead>
                        <tr>
                            <th>BUCKET</th>
                            <th>CRÉDITOS</th>
                            <th>DISTRIBUCIÓN</th>
                            <th>CAPITAL</th>
                            <th>DISTRIBUCIÓN</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>a) Current</td>
                            <td>38199</td>
                            <td>60,10%</td>
                            <td>$930.468.010,69</td>
                            <td>58,53%</td>
                        </tr>
                        <tr>
                            <td>b) 1 a 7 días</td>
                            <td>16489</td>
                            <td>25,94%</td>
                            <td>$402.767.499,29</td>
                            <td>25,33%</td>
                        </tr>
                        <tr>
                            <td>c) 8 a 14 días</td>
                            <td>876</td>
                            <td>1,38%</td>
                            <td>$22.234.835,3</td>
                            <td>1,40%</td>
                        </tr>
                        <tr>
                            <td>d) 15 a 21 días</td>
                            <td>272</td>
                            <td>0,43%</td>
                            <td>$7.452.707,17</td>
                            <td>0,47%</td>
                        </tr>
                        <tr>
                            <td>e) 22 a 29 días</td>
                            <td>427</td>
                            <td>0,67%</td>
                            <td>$11.970.237,6</td>
                            <td>0,75%</td>
                        </tr>
                        <tr>
                            <td>f) 30 a 59 días</td>
                            <td>868</td>
                            <td>1,37%</td>
                            <td>$22.927.373,46</td>
                            <td>1,44%</td>
                        </tr>
                        <tr>
                            <td>g) 60 a 89 días</td>
                            <td>906</td>
                            <td>1,43%</td>
                            <td>$24.993.910,77</td>
                            <td>1,57%</td>
                        </tr>
                        <tr>
                            <td>h) 90 a 119 días</td>
                            <td>686</td>
                            <td>1,08%</td>
                            <td>$19.005.643,41</td>
                            <td>1,20%</td>
                        </tr>
                        <tr>
                            <td>i) 120+ días</td>
                            <td>4841</td>
                            <td>7,62%</td>
                            <td>$147.966.130,02</td>
                            <td>9,31%</td>
                        </tr>
                        <tr class="fw-bold border-top">
                            <td>Total</td>
                            <td>63564</td>
                            <td>100,00%</td>
                            <td>$1.589.786.347,71</td>
                            <td>100,00%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ================= BLOQUE 2: Current + 1 a 7 días ================= -->

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Current + 1 a 7 días</h5>

            <div class="card-datatable table-responsive">
                <table id="tabla-combinado-1" class="dt-responsive table border-top">
                    <thead>
                        <tr>
                            <th>COMBINADO</th>
                            <th>CRÉDITOS</th>
                            <th>DISTRIBUCIÓN</th>
                            <th>CAPITAL</th>
                            <th>DISTRIBUCIÓN CAPITAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Current + 1 a 7 días</td>
                            <td>54688</td>
                            <td>86,04%</td>
                            <td>$1.333.235.509,98</td>
                            <td>83,86%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ================= BLOQUE 3: 8+ ================= -->

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">8+</h5>

            <div class="card-datatable table-responsive">
                <table id="tabla-combinado-2" class="dt-responsive table border-top">
                    <thead>
                        <tr>
                            <th>COMBINADO</th>
                            <th>CRÉDITOS</th>
                            <th>DISTRIBUCIÓN</th>
                            <th>CAPITAL</th>
                            <th>DISTRIBUCIÓN CAPITAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>8+</td>
                            <td>8876</td>
                            <td>13,96%</td>
                            <td>$256.550.837,73</td>
                            <td>16,14%</td>
                        </tr>
                    </tbody>
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
    console.log("Vista Cartera Inicio Semana lista para lógica JS");

    // ==========================================
    // FUNCIONALIDAD DE REDIMENSIONAR COLUMNAS
    // ==========================================
    
    function agregarRedimensionamiento(tablaId) {
        const table = document.getElementById(tablaId);
        if (!table) return;
        
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
                    const cells = table.querySelectorAll(`tbody td:nth-child(${index + 1})`);
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
    }
    
    // Aplicar a las tres tablas
    agregarRedimensionamiento('tabla-inicio-semana');
    agregarRedimensionamiento('tabla-combinado-1');
    agregarRedimensionamiento('tabla-combinado-2');

    // Aquí podrás:
    // 1. Cargar datos vía fetch()
    // 2. Recalcular totales dinámicamente
    // 3. Renderizar tablas dinámicamente

    // Ejemplo básico: puedes integrar con los datos pasados desde el controlador
    // const data = [/* datos del modelo */];
    // renderTable(data);
});
</script>