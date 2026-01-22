<div class="card">
    <div class="col-xxl-11 mb-6 order-0">
        <div class="card">
            <div class="row g-0 align-items-center"> <!-- quitar gutters y centrar vertical -->

                <!-- Texto -->
                <div class="col-12 col-md-8">
                    <div class="card-body">
                        <h5 class="card-title text-primary mb-3">HOLA, <?= $_SESSION['usuario_nombre']; ?> </h5>
                        <p class="mb-6">
                            Descarga el reporte de usuarios de Legacy y descubre todos los detalles en Excel.
                        </p>
                        <a href="javascript:;" class="btn btn-sm btn-label-primary">Layout Legacy</a>
                    </div>
                    <div class="row gy-6 mb-6">
                    <div class="col-lg-4">
                        <div class="card shadow-none bg-label-primary h-100">
                            <div class="card-body d-flex justify-content-between flex-wrap-reverse">
                                <div class="mb-0 w-100 app-academy-sm-60 d-flex flex-column justify-content-between text-center text-sm-start">
                                    <div class="card-title">
                                        <h5 class="text-primary mb-2">Disponible para descarga diaria</h5>
                                        <p class="text-body w-sm-80 app-academy-xl-100">El ultimo corte es: Reporte_Corte_Jueves_13_30</p>
                                    </div>
                                    <form id="form-descarga" method="GET" action="/Reporteria/ProcesarDescargarLegacy">
                                        <input type="hidden" name="columna" id="input-columna" value="">
                                        <button type="submit" id="btn-ultimo-corte" class="btn btn-primary">
                                            Descargar Último Corte
                                        </button>
                                    </form>
                                </div>
                                <div class="w-100 app-academy-sm-40 d-flex justify-content-center justify-content-sm-end h-px-150 mb-4 mb-sm-0">
                                    <img class="img-fluid scaleX-n1-rtl" src="https://cdn-icons-png.freepik.com/512/11053/11053297.png?ga=GA1.1.191732613.1764875703" alt="boy illustration">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Imagen -->
                <div class="col-12 col-md-4">
                    <div class="card-body ps-md-3 pe-2 text-end"> <!-- padding solo izquierda, alineada a la derecha -->
                        <img src="https://demos.themeselection.com/sneat-bootstrap-html-admin-template/assets/img/illustrations/sitting-girl-with-laptop.png"
                             class="img-fluid scaleX-n1-rtl"
                             alt="View Badge User">
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
