<div class="container py-4">
    <!-- Título -->
    <div class="row mb-3">
        <div class="col-12">
            <h4 class="mb-0">Avance de Buckets</h4>
            <p class="text-muted small">Indicadores de Avance de Buckets</p>
        </div>
    </div>

    <div class="row">
        <!-- Sidebar con resumen -->
        <div class="col-lg-3 col-md-4 mb-4">
            <div class="mb-4">
                <div class="card">
                    <div class="card-header bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold">Bucket Inicio</span>
                            <span class="fw-bold">Créditos</span>
                        </div>
                    </div>
                    <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                        <div class="d-flex justify-content-between py-2 px-3 border-bottom">
                            <span>a) Current</span>
                            <span class="fw-semibold">38199</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 px-3 border-bottom">
                            <span>b) 1 a 7 días</span>
                            <span class="fw-semibold">16489</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 px-3 border-bottom">
                            <span>c) 8 a 14 días</span>
                            <span class="fw-semibold">876</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 px-3 border-bottom">
                            <span>d) 15 a 21 días</span>
                            <span class="fw-semibold">272</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 px-3 border-bottom">
                            <span>e) 22 a 29 días</span>
                            <span class="fw-semibold">427</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 px-3 border-bottom">
                            <span>f) 30 a 59 días</span>
                            <span class="fw-semibold">868</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 px-3 border-bottom">
                            <span>g) 60 a 89 días</span>
                            <span class="fw-semibold">906</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 px-3 border-bottom">
                            <span>h) 90 a 119 días</span>
                            <span class="fw-semibold">686</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 px-3 bg-light fw-bold">
                            <span>Total</span>
                            <span>63564</span>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <div class="card">
                    <div class="card-header bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold">Bucket Inicio</span>
                            <span class="fw-bold">%</span>
                        </div>
                    </div>
                    <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                        <div class="d-flex justify-content-between py-2 px-3 border-bottom">
                            <span>a) Current</span>
                            <span class="fw-semibold">60,10%</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 px-3 border-bottom">
                            <span>b) 1 a 7 días</span>
                            <span class="fw-semibold">25,94%</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 px-3 border-bottom">
                            <span>c) 8 a 14 días</span>
                            <span class="fw-semibold">1,38%</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 px-3 border-bottom">
                            <span>d) 15 a 21 días</span>
                            <span class="fw-semibold">0,43%</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 px-3 border-bottom">
                            <span>e) 22 a 29 días</span>
                            <span class="fw-semibold">0,67%</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 px-3 border-bottom">
                            <span>f) 30 a 59 días</span>
                            <span class="fw-semibold">1,37%</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 px-3 border-bottom">
                            <span>g) 60 a 89 días</span>
                            <span class="fw-semibold">1,43%</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 px-3 border-bottom">
                            <span>h) 90 a 119 días</span>
                            <span class="fw-semibold">1,08%</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 px-3 bg-light fw-bold">
                            <span>Total</span>
                            <span>100,00%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenido principal -->
        <div class="col-lg-9 col-md-8">
            <!-- Matriz de avance bucket (números) -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Matriz de avance bucket</h5>
                    <div class="table-responsive">
                        <table class="table table-striped dt-responsive" id="avance-buckets-numeros">
                            <thead>
                                <tr>
                                    <th>Bucket_Morosidad_Real</th>
                                    <th class="text-end">a) Current</th>
                                    <th class="text-end">b) 1 a 7 días</th>
                                    <th class="text-end">c) 8 a 14 días</th>
                                    <th class="text-end">d) 15 a 21 días</th>
                                    <th class="text-end">e) 22 a 29 días</th>
                                    <th class="text-end">f) 30 a 59 días</th>
                                    <th class="text-end">g) 60 a 89 días</th>
                                    <th class="text-end">h) 90 a 119 días</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>a) Current</td>
                                    <td class="text-end">38196</td>
                                    <td class="text-end">3</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                </tr>
                                <tr>
                                    <td>b) 1 a 7 días</td>
                                    <td class="text-end">10964</td>
                                    <td class="text-end">5525</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                </tr>
                                <tr>
                                    <td>c) 8 a 14 días</td>
                                    <td class="text-end">137</td>
                                    <td class="text-end">97</td>
                                    <td class="text-end">642</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                </tr>
                                <tr>
                                    <td>d) 15 a 21 días</td>
                                    <td class="text-end">10</td>
                                    <td class="text-end">32</td>
                                    <td class="text-end">2</td>
                                    <td class="text-end">228</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                </tr>
                                <tr>
                                    <td>e) 22 a 29 días</td>
                                    <td class="text-end">8</td>
                                    <td class="text-end">4</td>
                                    <td class="text-end">17</td>
                                    <td class="text-end">2</td>
                                    <td class="text-end">181</td>
                                    <td class="text-end">215</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                </tr>
                                <tr>
                                    <td>f) 30 a 59 días</td>
                                    <td class="text-end">1</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end">1</td>
                                    <td class="text-end">1</td>
                                    <td class="text-end">690</td>
                                    <td class="text-end">175</td>
                                    <td class="text-end text-muted">-</td>
                                </tr>
                                <tr>
                                    <td>g) 60 a 89 días</td>
                                    <td class="text-end">1</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end">905</td>
                                    <td class="text-end text-muted">-</td>
                                </tr>
                                <tr class="table-info fw-bold">
                                    <td>Total</td>
                                    <td class="text-end">49321</td>
                                    <td class="text-end">5661</td>
                                    <td class="text-end">661</td>
                                    <td class="text-end">231</td>
                                    <td class="text-end">182</td>
                                    <td class="text-end">905</td>
                                    <td class="text-end">1080</td>
                                    <td class="text-end text-muted">-</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Matriz de avance bucket (porcentajes) -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Matriz de avance bucket %</h5>
                    <div class="table-responsive">
                        <table class="table table-striped dt-responsive" id="avance-buckets-porcentajes">
                            <thead>
                                <tr>
                                    <th>Bucket_Morosidad_Real</th>
                                    <th class="text-end">a) Current</th>
                                    <th class="text-end">b) 1 a 7 días</th>
                                    <th class="text-end">c) 8 a 14 días</th>
                                    <th class="text-end">d) 15 a 21 días</th>
                                    <th class="text-end">e) 22 a 29 días</th>
                                    <th class="text-end">f) 30 a 59 días</th>
                                    <th class="text-end">g) 60 a 89 días</th>
                                    <th class="text-end">h) 90 a 119 días</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>a) Current</td>
                                    <td class="text-end">60,09%</td>
                                    <td class="text-end">0,00%</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                </tr>
                                <tr>
                                    <td>b) 1 a 7 días</td>
                                    <td class="text-end">17,25%</td>
                                    <td class="text-end">8,69%</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                </tr>
                                <tr>
                                    <td>c) 8 a 14 días</td>
                                    <td class="text-end">0,22%</td>
                                    <td class="text-end">0,15%</td>
                                    <td class="text-end">1,01%</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                </tr>
                                <tr>
                                    <td>d) 15 a 21 días</td>
                                    <td class="text-end">0,02%</td>
                                    <td class="text-end">0,05%</td>
                                    <td class="text-end">0,00%</td>
                                    <td class="text-end">0,36%</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                </tr>
                                <tr>
                                    <td>e) 22 a 29 días</td>
                                    <td class="text-end">0,01%</td>
                                    <td class="text-end">0,01%</td>
                                    <td class="text-end">0,03%</td>
                                    <td class="text-end">0,00%</td>
                                    <td class="text-end">0,28%</td>
                                    <td class="text-end">0,34%</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                </tr>
                                <tr>
                                    <td>f) 30 a 59 días</td>
                                    <td class="text-end">0,00%</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end">0,00%</td>
                                    <td class="text-end">0,00%</td>
                                    <td class="text-end">1,09%</td>
                                    <td class="text-end">0,28%</td>
                                    <td class="text-end text-muted">-</td>
                                </tr>
                                <tr>
                                    <td>g) 60 a 89 días</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                </tr>
                                <tr class="table-info fw-bold">
                                    <td>Total</td>
                                    <td class="text-end">77,59%</td>
                                    <td class="text-end">8,91%</td>
                                    <td class="text-end">1,04%</td>
                                    <td class="text-end">0,36%</td>
                                    <td class="text-end">0,29%</td>
                                    <td class="text-end">1,42%</td>
                                    <td class="text-end">1,70%</td>
                                    <td class="text-end text-muted">-</td>
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
@media (max-width: 576px) {
    .table-responsive {
        font-size: 0.75rem;
    }
    .table th, .table td {
        padding: 0.25rem;
    }
}
.table th {
    position: sticky;
    top: 0;
    background-color: #f8f9fa;
    z-index: 10;
}
.table th:first-child {
    position: sticky;
    left: 0;
    background-color: #f8f9fa;
    z-index: 11;
}
.table td:first-child {
    position: sticky;
    left: 0;
    background-color: white;
    z-index: 5;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Vista Avance de Buckets lista');

    // Función para cargar datos
    // async function loadBucketAdvanceData() {
    //     try {
    //         const response = await fetch('/api/avance-buckets');
    //         const data = await response.json();
    //         // Renderizar datos
    //     } catch (error) {
    //         console.error('Error:', error);
    //     }
    // }
    // loadBucketAdvanceData();
});
</script>