<div class="container py-4">
    <!-- Título -->
    <div class="row mb-3">
        <div class="col-12">
            <h4 class="mb-0">Espartanos</h4>
            <p class="text-muted small">Indicadores de Espartanos</p>
        </div>
    </div>

    <div class="row">
        <!-- Sidebar con filtros -->
        <div class="col-lg-3 col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <label for="territorial-filter" class="form-label fw-bold" style="font-size: 0.92rem;">Territorial</label>
                        <select class="form-select" id="territorial-filter" style="font-size: 0.85rem;">
                            <option>Todas</option>
                            <option>Territorial 1</option>
                            <option>Territorial 2</option>
                            <option>Territorial 3</option>
                        </select>
                    </div>
                    <div>
                        <label for="bucket-filter" class="form-label fw-bold" style="font-size: 0.92rem;">Bucket</label>
                        <select class="form-select" id="bucket-filter" style="font-size: 0.85rem;">
                            <option>Todos</option>
                            <option>Current</option>
                            <option>1 a 7 días</option>
                            <option>8 a 14 días</option>
                            <option>15 a 21 días</option>
                            <option>22 a 29 días</option>
                            <option>30 a 59 días</option>
                            <option>60 a 89 días</option>
                            <option>90 a 119 días</option>
                            <option>120+ días</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenido principal -->
        <div class="col-lg-9 col-md-8">
            <!-- Tarjetas de métricas EN UNA SOLA LÍNEA -->
            <div class="row mb-4">
                <div class="col mb-3">
                    <div class="card border-primary">
                        <div class="card-body text-center py-2">
                            <div class="text-muted small text-uppercase" style="font-size: 0.7rem;">Total ID Crédito</div>
                            <div class="h5 mb-0">329</div>
                        </div>
                    </div>
                </div>
                <div class="col mb-3">
                    <div class="card border-primary">
                        <div class="card-body text-center py-2">
                            <div class="text-muted small text-uppercase" style="font-size: 0.7rem;">CTA Current</div>
                            <div class="h5 mb-0">4</div>
                        </div>
                    </div>
                </div>
                <div class="col mb-3">
                    <div class="card border-primary">
                        <div class="card-body text-center py-2">
                            <div class="text-muted small text-uppercase" style="font-size: 0.7rem;">CTA 22 a Mas</div>
                            <div class="h5 mb-0">301</div>
                        </div>
                    </div>
                </div>
                <div class="col mb-3">
                    <div class="card border-primary">
                        <div class="card-body text-center py-2">
                            <div class="text-muted small text-uppercase" style="font-size: 0.7rem;">Objetivo Pase...</div>
                            <div class="h5 mb-0">10</div>
                        </div>
                    </div>
                </div>
                <div class="col mb-3">
                    <div class="card border-danger">
                        <div class="card-body text-center py-2">
                            <div class="text-muted small text-uppercase" style="font-size: 0.7rem;">Monto Cobrado</div>
                            <div class="h5 mb-0 text-danger">-$331,346 mil</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de detalle por gestor -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Detalle por Gestor</h5>
                    <div class="table-responsive">
                        <table class="table table-striped dt-responsive" id="espartanos-table">
                            <thead>
                                <tr>
                                    <th>Id_credito</th>
                                    <th>Nombre_cliente</th>
                                    <th>Cierre_Actual</th>
                                    <th>Fecha_ultimo_pago_efectivo</th>
                                    <th class="text-end">Saldo_total_capital</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1004359</td>
                                    <td>EDUARDO ALBERTO GARCIA NUÑEZ</td>
                                    <td><span class="badge bg-danger">f) 30 a 59 días</span></td>
                                    <td>2026-01-09</td>
                                    <td class="text-end">$29.636,14</td>
                                </tr>
                                <tr>
                                    <td>1007414</td>
                                    <td>GERSON DIDI FLORES PERALTA</td>
                                    <td><span class="badge bg-danger">c) 8 a 14 días</span></td>
                                    <td>2026-02-11</td>
                                    <td class="text-end">$13.327,18</td>
                                </tr>
                                <tr>
                                    <td>1017099</td>
                                    <td>MARIA FERNANDA FLORES GONZALEZ</td>
                                    <td><span class="badge bg-danger">f) 30 a 59 días</span></td>
                                    <td>2026-01-25</td>
                                    <td class="text-end">$37.721,67</td>
                                </tr>
                                <tr>
                                    <td>1024913</td>
                                    <td>DIANA JAQUELINE SANABRIA MARTINEZ</td>
                                    <td><span class="badge bg-danger">e) 22 a 29 días</span></td>
                                    <td>2026-01-14</td>
                                    <td class="text-end">$37.659,51</td>
                                </tr>
                                <tr>
                                    <td>1043069</td>
                                    <td>EDUARDO MELO DOMINGUEZ</td>
                                    <td><span class="badge bg-danger">e) 22 a 29 días</span></td>
                                    <td>2026-01-15</td>
                                    <td class="text-end">$12.479,09</td>
                                </tr>
                                <tr>
                                    <td>1043332</td>
                                    <td>ANGEL URIEL GARCIA PERALTA</td>
                                    <td><span class="badge bg-danger">e) 22 a 29 días</span></td>
                                    <td>2026-01-13</td>
                                    <td class="text-end">$31.010,68</td>
                                </tr>
                                <tr>
                                    <td>1044807</td>
                                    <td>MELANY CAROLAYNN GARDUÑO MORALES</td>
                                    <td><span class="badge bg-danger">f) 30 a 59 días</span></td>
                                    <td>2026-01-18</td>
                                    <td class="text-end">$22.015,44</td>
                                </tr>
                                <tr>
                                    <td>1045768</td>
                                    <td>TANIA ELIZABETH FERRUSCA BARCENAS</td>
                                    <td><span class="badge bg-danger">f) 30 a 59 días</span></td>
                                    <td>2026-01-19</td>
                                    <td class="text-end">$31.127,68</td>
                                </tr>
                                <tr>
                                    <td>1056036</td>
                                    <td>FRANCO EDUARDO ALFONSO OLVERA</td>
                                    <td><span class="badge bg-danger">f) 30 a 59 días</span></td>
                                    <td>2026-01-10</td>
                                    <td class="text-end">$52.704,88</td>
                                </tr>
                                <tr>
                                    <td>1057456</td>
                                    <td>CAYETANO MALDONADO IBARRA</td>
                                    <td><span class="badge bg-warning">b) 1 a 7 días</span></td>
                                    <td>2026-02-12</td>
                                    <td class="text-end">$22.715,97</td>
                                </tr>
                                <tr>
                                    <td>1065271</td>
                                    <td>DANIEL TRONCOSO HERNANDEZ</td>
                                    <td><span class="badge bg-danger">f) 30 a 59 días</span></td>
                                    <td>2026-01-13</td>
                                    <td class="text-end">$29.644,21</td>
                                </tr>
                                <tr>
                                    <td>1066659</td>
                                    <td>REIVAJ RICARDO DELGADO VEGA</td>
                                    <td><span class="badge bg-danger">f) 30 a 59 días</span></td>
                                    <td>2026-01-13</td>
                                    <td class="text-end">$48.706,96</td>
                                </tr>
                                <tr>
                                    <td>1073904</td>
                                    <td>GIOVANA LOPEZ ECHEVERRIA</td>
                                    <td><span class="badge bg-danger">f) 30 a 59 días</span></td>
                                    <td>2026-01-15</td>
                                    <td class="text-end">$69.451,94</td>
                                </tr>
                                <tr>
                                    <td>1079224</td>
                                    <td>NOE JAFET MOLINA FLORES</td>
                                    <td><span class="badge bg-danger">e) 22 a 29 días</span></td>
                                    <td>2026-01-18</td>
                                    <td class="text-end">$23.674,7</td>
                                </tr>
                                <tr>
                                    <td>1080639</td>
                                    <td>ROBERTH JONATHAN LEON CHACON</td>
                                    <td><span class="badge bg-danger">d) 15 a 21 días</span></td>
                                    <td>2026-02-05</td>
                                    <td class="text-end">$38.627,9</td>
                                </tr>
                                <tr>
                                    <td>1081744</td>
                                    <td>JONATHAN ROGELIO VICTORIA REYES</td>
                                    <td><span class="badge bg-danger">f) 30 a 59 días</span></td>
                                    <td>2026-01-19</td>
                                    <td class="text-end">$23.812,82</td>
                                </tr>
                                <tr>
                                    <td>1081757</td>
                                    <td>VICTOR VELAZQUEZ ROMERO</td>
                                    <td><span class="badge bg-danger">f) 30 a 59 días</span></td>
                                    <td>2026-01-08</td>
                                    <td class="text-end">$13.061,09</td>
                                </tr>
                                <tr class="table-info fw-bold">
                                    <td colspan="4">Total</td>
                                    <td class="text-end">$9.346.304,67</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Logo -->
            <div class="text-end mt-4">
                <img src="/assets/images/logo.png" width="100" height="auto" alt="Logo">
            </div>
        </div>
    </div>
</div>

<style>
    #espartanos-table {
        width: 100%;
        table-layout: auto;
        min-width: 1200px;
    }

    #espartanos-table th {
        position: relative;
        min-width: 120px;
    }

    #espartanos-table th.resizable {
        position: relative;
    }

    #espartanos-table .resizer {
        position: absolute;
        top: 0;
        right: 0;
        width: 5px;
        cursor: col-resize;
        user-select: none;
        height: 100%;
        background: transparent;
    }

    #espartanos-table .resizer:hover {
        background: #4CAF50;
    }

    #espartanos-table .resizing {
        border-right: 2px solid #4CAF50;
    }

    #espartanos-table thead th {
        position: sticky;
        top: 0;
        background-color: #f8f9fa;
        z-index: 10;
        font-size: 0.75rem;
        white-space: nowrap;
        padding: 0.5rem 0.25rem;
    }

    #espartanos-table tbody td {
        font-size: 0.8rem;
        padding: 0.4rem 0.25rem;
    }

    @media (max-width: 576px) {
        .table-responsive {
            font-size: 0.75rem;
        }
        .table th, .table td {
            padding: 0.25rem;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ==========================================
    // FUNCIONALIDAD DE REDIMENSIONAR COLUMNAS
    // ==========================================
    const table = document.getElementById('espartanos-table');
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

    // Filtros
    document.getElementById('territorial-filter').addEventListener('change', function(e) {
        // Aquí conectarás con tu backend para filtrar datos
    });

    document.getElementById('bucket-filter').addEventListener('change', function(e) {
        // Aquí conectarás con tu backend para filtrar datos
    });

    // Función para cargar datos
    // async function loadEspartanosData() {
    //     try {
    //         const response = await fetch('/api/espartanos');
    //         const data = await response.json();
    //         // Renderizar datos
    //     } catch (error) {
    //         console.error('Error:', error);
    //     }
    // }
    // loadEspartanosData();
});
</script>