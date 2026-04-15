<div id="cc-landing" class="cc-call-center-page">
<div class="card">
    <div class="card">
        <div class="card">
            <div class="row g-0 align-items-center overflow-visible cc-hero-row cc-hero-row--con-mascota cc-hero-block">

                <!-- Texto -->
                <div class="col-12 col-md-8 cc-hero-text">
                    <div class="card-body">
                        <h5 class="card-title text-primary mb-3">HOLA, <?= isset($_SESSION['usuario_nombre']) ? htmlspecialchars(strtoupper((string) $_SESSION['usuario_nombre']), ENT_QUOTES, 'UTF-8') : 'USUARIO'; ?> <i class="fa-solid fa-headset ms-2 text-primary" aria-hidden="true"></i></h5>
                        <p class="mb-6 mb-md-0">
                            Consulta dictámenes de llamadas por periodo o rango de fechas. El historial de condonaciones de cobranza está en el menú <strong>Gastos Cobranza</strong>.
                        </p>
                    </div>
                </div>

                <!-- Mascota (misma convención que Primeros pagos: /public/assets/img/illustrations/) -->
                <div class="col-12 col-md-4 d-flex flex-column justify-content-end align-items-center align-items-md-end cc-hero-mascot-col">
                    <img src="/assets/img/illustrations/call-center-mascota.png"
                         class="cc-hero-mascot-floating img-fluid"
                         width="400"
                         height="400"
                         alt="Call Center — ilustración">
                </div>

                <div class="row gy-6 mb-6 gx-0 justify-content-start">

                    <!-- Card: Dictamen (mismo ancho que antes: 4/12; no expandir al quitar la otra tarjeta) -->
                    <div class="col-12 col-lg-4">
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

                </div>

            </div>
        </div>
    </div>
</div>
<style>
/* Hero Call Center — misma mascota (tamaño/posición/ángulo) que Panel Admin */
.cc-call-center-page,
#cc-landing.cc-call-center-page {
    overflow: visible;
}
.cc-call-center-page .cc-hero-row {
    overflow: visible;
}
.cc-call-center-page > .card {
    overflow: visible;
    position: relative;
}
.cc-hero-block {
    position: relative;
    z-index: 0;
    overflow: visible;
    --cc-mascot-max-w: 280px;
    --cc-mascot-max-h: min(300px, 44vh);
    --cc-mascot-translate-x: -6rem;
    --cc-mascot-translate-y: 3rem;
}
.cc-hero-text .card-body {
    padding-bottom: 1.25rem !important;
    padding-top: 2rem !important;
}
@media (min-width: 768px) {
    .cc-hero-row.cc-hero-row--con-mascota {
        align-items: stretch;
        min-height: 23rem;
        padding-bottom: 5rem;
    }
    .cc-hero-text .card-body {
        padding-top: 2rem !important;
        padding-bottom: 1.5rem !important;
        padding-right: 0.5rem;
    }
}
.cc-hero-mascot-col {
    padding-top: 1rem;
    padding-bottom: 2rem;
}
@media (min-width: 768px) {
    .cc-hero-mascot-col {
        position: relative;
        min-height: 0;
        padding: 0;
        align-self: stretch;
    }
    .cc-hero-text {
        position: relative;
        z-index: 2;
    }
}
.cc-hero-mascot-floating {
    display: block;
    object-fit: contain;
    object-position: bottom center;
    filter: drop-shadow(0 10px 28px rgba(26, 82, 168, 0.12));
}
@media (max-width: 767.98px) {
    .cc-hero-row.cc-hero-row--con-mascota {
        min-height: 15rem;
    }
    .cc-hero-text .card-body {
        text-align: center;
        padding-top: 2rem !important;
        padding-bottom: 1rem !important;
        padding-left: 1rem !important;
        padding-right: 1rem !important;
    }
    .cc-hero-mascot-col {
        align-items: center !important;
        padding-top: 0;
    }
    .cc-hero-mascot-floating {
        margin: 0 auto;
        max-width: min(58vw, 200px);
        max-height: min(32vh, 200px);
        width: auto;
        height: auto;
        object-position: bottom center;
        transform: translateY(var(--cc-mascot-translate-y, 3rem));
    }
}
@media (min-width: 768px) {
    .cc-hero-mascot-floating {
        position: relative;
        right: auto;
        bottom: auto;
        z-index: 1;
        width: auto;
        height: auto;
        max-width: var(--cc-mascot-max-w, 280px);
        max-height: var(--cc-mascot-max-h, 300px);
        margin: 0 0 0 auto;
        object-position: bottom right;
        transform: translate(var(--cc-mascot-translate-x, -6rem), var(--cc-mascot-translate-y, 3rem));
    }
}
body.dark-mode .cc-hero-mascot-floating {
    filter: drop-shadow(0 12px 32px rgba(0, 0, 0, 0.35));
}
</style>
</div><!-- /#cc-landing -->

<!-- Modal dictamen (siempre en DOM) -->
<?php
$omitir_cabecera_call_center = true;
require dirname(__FILE__) . '/dictamen_llamadas.php';
unset($omitir_cabecera_call_center);
?>
