<div id="cc-landing">
<div class="card">
    <div class="card">
        <div class="card">
            <div class="row g-0 align-items-center">

                <!-- Texto -->
                <div class="col-12 col-md-8">
                    <div class="card-body">
                        <h5 class="card-title text-primary mb-3">HOLA, <?= $_SESSION['usuario_nombre']; ?> </h5>
                        <p class="mb-6">
                            Consulta dictámenes de llamadas por periodo o rango de fechas, y revisa el historial de condonaciones de cobranza. ¡Todo en un solo lugar!
                        </p>
                    </div>
                </div>

                <!-- Imagen -->
                <div class="col-12 col-md-4">
                    <div class="card-body ps-md-2 pe-5 text-end">
                        <img src="https://demos.themeselection.com/sneat-bootstrap-html-admin-template/assets/img/illustrations/man-with-laptop.png"
                             class="img-fluid scaleX-n1-rtl"
                             alt="View Badge User">
                    </div>
                </div>

                <div class="row gy-6 mb-6">

                    <!-- Card 1: Dictamen de llamadas -->
                    <div class="col-lg-4">
                        <div class="card shadow-none bg-label-primary h-100">
                            <div class="card-body d-flex justify-content-between flex-wrap-reverse">
                                <div class="mb-0 w-100 app-academy-sm-60 d-flex flex-column justify-content-between text-center text-sm-start">
                                    <div class="card-title">
                                        <h5 class="text-primary mb-2">Dictamen de llamadas</h5>
                                        <p class="text-body w-sm-80 app-academy-xl-100">Consulta, filtra y descarga reportes de dictamen por rango de fechas.</p>
                                    </div>
                                    <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modalReporte">
                                        <i class="fa fa-download me-2"></i>Descargar dictamen
                                    </button>
                                </div>
                                <div class="w-100 app-academy-sm-40 d-flex justify-content-center justify-content-sm-end h-px-150 mb-4 mb-sm-0">
                                    <img class="img-fluid scaleX-n1-rtl" src="https://cdn-icons-png.freepik.com/512/11053/11053297.png?ga=GA1.1.191732613.1764875703" alt="dictamen illustration">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Historial de condonaciones -->
                    <div class="col-lg-4">
                        <div class="card shadow-none bg-label-primary h-100">
                            <div class="card-body d-flex justify-content-between flex-wrap-reverse">
                                <div class="mb-0 w-100 app-academy-sm-60 d-flex flex-column justify-content-between text-center text-sm-start">
                                    <div class="card-title">
                                        <h5 class="text-primary mb-2">Historial condonaciones</h5>
                                        <p class="text-body app-academy-sm-60 app-academy-xl-100">Consulta y gestión de condonaciones de cobranza con KPIs y filtros.</p>
                                    </div>
                                    <button type="button" class="btn btn-primary w-100" id="cc-btn-ver-historial">
                                        <i class="fa-solid fa-hand-holding-dollar me-2"></i>Ver historial
                                    </button>
                                </div>
                                <div class="w-100 app-academy-sm-40 d-flex justify-content-center justify-content-sm-end h-px-150 mb-4 mb-sm-0">
                                    <img class="img-fluid scaleX-n1-rtl" src="https://cdn-icons-png.freepik.com/512/11053/11053333.png?ga=GA1.1.191732613.1764875703" alt="condonaciones illustration">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>
</div><!-- /#cc-landing -->

<!-- Modal dictamen (siempre en DOM) -->
<?php
$omitir_cabecera_call_center = true;
require dirname(__FILE__) . '/dictamen_llamadas.php';
unset($omitir_cabecera_call_center);
?>

<!-- Historial condonaciones: se muestra solo al presionar "Ver historial" -->
<div id="cc-historial-wrap" style="display:none;">
    <div class="mb-3">
        <button type="button" class="btn btn-outline-secondary btn-sm" id="cc-btn-volver">
            <i class="fa-solid fa-arrow-left me-1"></i>Volver
        </button>
    </div>
    <?php
    $omitir_cabecera_call_center = true;
    require dirname(__FILE__) . '/historial_condonaciones.php';
    unset($omitir_cabecera_call_center);
    ?>
</div>

<script>
(function () {
    function mostrarHistorial() {
        document.getElementById('cc-landing').style.display = 'none';
        document.getElementById('cc-historial-wrap').style.display = '';
        window.scrollTo({ top: 0, behavior: 'smooth' });
        if (typeof cargarCondonaciones === 'function') {
            cargarCondonaciones();
        }
    }

    function mostrarLanding() {
        document.getElementById('cc-historial-wrap').style.display = 'none';
        document.getElementById('cc-landing').style.display = '';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    document.addEventListener('DOMContentLoaded', function () {
        var btnVer    = document.getElementById('cc-btn-ver-historial');
        var btnVolver = document.getElementById('cc-btn-volver');

        if (btnVer)    btnVer.addEventListener('click',    mostrarHistorial);
        if (btnVolver) btnVolver.addEventListener('click', mostrarLanding);

        // Si viene de redirección ?seccion=condonaciones, abrir historial directamente
        try {
            var p = new URLSearchParams(window.location.search);
            if (p.get('seccion') === 'condonaciones') mostrarHistorial();
        } catch (e) {}
    });
})();
</script>
