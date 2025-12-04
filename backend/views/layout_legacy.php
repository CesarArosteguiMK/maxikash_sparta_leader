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
