<div id="ll-landing" class="ll-layout-legacy-page reporteria-landing-root">
<div class="card">
    <div>
        <div class="card">
            <div class="row g-0 align-items-center overflow-visible ll-hero-row ll-hero-row--con-mascota ll-hero-block">

                <!-- Texto -->
                <div class="col-12 col-md-8 ll-hero-text">
                    <div class="card-body">
                        <h5 class="card-title text-primary mb-3">HOLA, <?= isset($_SESSION['usuario_nombre']) ? htmlspecialchars(strtoupper((string) $_SESSION['usuario_nombre']), ENT_QUOTES, 'UTF-8') : 'USUARIO'; ?> <i class="fa-solid fa-file-lines ms-2 text-primary" aria-hidden="true"></i></h5>
                        <p class="mb-6 mb-md-0">
                            Descarga el reporte de usuarios de Legacy y descubre todos los detalles en Excel. ¡Todo al alcance de un clic!
                        </p>
                    </div>
                </div>

                <!-- Mascota (misma convención que Primeros pagos / Call Center) -->
                <div class="col-12 col-md-4 d-flex flex-column justify-content-end align-items-center align-items-md-end ll-hero-mascot-col">
                    <img src="/assets/img/illustrations/layout-legacy-mascota.png"
                         class="ll-hero-mascot-floating img-fluid"
                         width="400"
                         height="400"
                         alt="Layout Legacy — ilustración">
                </div>

                <!-- Tarjeta de descarga -->
                <div class="row gy-6 mb-6 gx-0">
                    <div class="col-lg-4">
                        <div class="card shadow-none bg-label-primary h-100">
                            <div class="card-body d-flex justify-content-between flex-wrap-reverse">
                                <div class="mb-0 w-100 app-academy-sm-60 d-flex flex-column justify-content-between text-center text-sm-start">
                                    <div class="card-title">
                                        <h5 class="text-primary mb-2">Disponible para descarga diaria</h5>
                                        <p class="text-body w-sm-80 app-academy-xl-100">Descarga el reporte completo de usuarios de Legacy con toda la información actualizada.</p>
                                    </div>
                                    <form id="form-descarga" method="GET" action="/Reporteria/ProcesarDescargarLegacy">
                                        <input type="hidden" name="columna" id="input-columna" value="">
                                        <button type="submit" id="btn-ultimo-corte" class="btn btn-primary">
                                            <i class="bx bx-download me-2"></i>Descargar Reporte Legacy
                                        </button>
                                    </form>
                                </div>
                                <div class="w-100 app-academy-sm-40 d-flex justify-content-center justify-content-sm-end h-px-150 mb-4 mb-sm-0">
                                    <img class="img-fluid scaleX-n1-rtl" src="https://cdn-icons-png.freepik.com/512/11053/11053297.png?ga=GA1.1.191732613.1764875703" alt="Descarga reporte Legacy">
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
/* Hero Layout Legacy — misma mascota (tamaño/posición/ángulo) que Panel Admin */
.ll-layout-legacy-page,
#ll-landing.ll-layout-legacy-page {
    overflow: visible;
}
.ll-layout-legacy-page .ll-hero-row {
    overflow: visible;
}
.ll-layout-legacy-page > .card {
    overflow: visible;
    position: relative;
}
.ll-hero-block {
    position: relative;
    z-index: 0;
    overflow: visible;
    --ll-mascot-max-w: 280px;
    --ll-mascot-max-h: min(300px, 44vh);
    --ll-mascot-translate-x: -6rem;
    --ll-mascot-translate-y: 3rem;
}
.ll-hero-text .card-body {
    padding-bottom: 1.25rem !important;
    padding-top: 2rem !important;
}
@media (min-width: 768px) {
    .ll-hero-row.ll-hero-row--con-mascota {
        align-items: stretch;
        min-height: 23rem;
        padding-bottom: 5rem;
    }
    .ll-hero-text .card-body {
        padding-top: 2rem !important;
        padding-bottom: 1.5rem !important;
        padding-right: 0.5rem;
    }
}
.ll-hero-mascot-col {
    padding-top: 1rem;
    padding-bottom: 2rem;
}
@media (min-width: 768px) {
    .ll-hero-mascot-col {
        position: relative;
        min-height: 0;
        padding: 0;
        align-self: stretch;
    }
    .ll-hero-text {
        position: relative;
        z-index: 2;
    }
}
.ll-hero-mascot-floating {
    display: block;
    object-fit: contain;
    object-position: bottom center;
    filter: drop-shadow(0 10px 28px rgba(26, 82, 168, 0.12));
}
@media (max-width: 767.98px) {
    .ll-hero-row.ll-hero-row--con-mascota {
        min-height: 15rem;
    }
    .ll-hero-text .card-body {
        text-align: center;
        padding-top: 2rem !important;
        padding-bottom: 1rem !important;
        padding-left: 1rem !important;
        padding-right: 1rem !important;
    }
    .ll-hero-mascot-col {
        align-items: center !important;
        padding-top: 0;
    }
    .ll-hero-mascot-floating {
        margin: 0 auto;
        max-width: min(58vw, 200px);
        max-height: min(32vh, 200px);
        width: auto;
        height: auto;
        object-position: bottom center;
        transform: translateY(var(--ll-mascot-translate-y, 3rem));
    }
}
@media (min-width: 768px) {
    .ll-hero-mascot-floating {
        position: relative;
        right: auto;
        bottom: auto;
        z-index: 1;
        width: auto;
        height: auto;
        max-width: var(--ll-mascot-max-w, 280px);
        max-height: var(--ll-mascot-max-h, 300px);
        margin: 0 0 0 auto;
        object-position: bottom right;
        transform: translate(var(--ll-mascot-translate-x, -6rem), var(--ll-mascot-translate-y, 3rem));
    }
}
body.dark-mode .ll-hero-mascot-floating {
    filter: drop-shadow(0 12px 32px rgba(0, 0, 0, 0.35));
}
</style>
</div><!-- /#ll-landing -->
