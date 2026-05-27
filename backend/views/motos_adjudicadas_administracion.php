<?php
$motosAdminModulos = array_map('intval', (array) ($_SESSION['modulos'] ?? []));
$puedeAdminCobranza = in_array(62, $motosAdminModulos, true);
$puedeDictaminarCreditos = in_array(80, $motosAdminModulos, true);
?>
<div id="motos-admin-landing" class="cc-call-center-page motos-admin-root">
<div class="card">
    <svg xmlns="http://www.w3.org/2000/svg" class="motos-admin-svg-defs" aria-hidden="true" focusable="false">
        <defs>
            <linearGradient id="motos-admin-icon-gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" style="stop-color:#00B4DB"/>
                <stop offset="100%" style="stop-color:#6E48AA"/>
            </linearGradient>
        </defs>
    </svg>
    <div class="card">
        <div class="card">
            <div class="row g-0 align-items-center overflow-visible cc-hero-row cc-hero-row--con-mascota cc-hero-block">

                <div class="col-12 col-md-8 cc-hero-text">
                    <div class="card-body">
                        <h5 class="card-title text-primary mb-3">Administracion de Motos Adjudicadas <i class="fa-solid fa-motorcycle ms-2 text-primary" aria-hidden="true"></i></h5>
                        <p class="mb-6 mb-md-0">
                            Aqui se concentran las herramientas administrativas de <strong>Motos Adjudicadas</strong>. Entra al flujo que necesites y conserva el menu lateral mas limpio para operar sin tantas pestañas sueltas.
                        </p>
                    </div>
                </div>

                <div class="col-12 col-md-4 d-flex flex-column justify-content-end align-items-center align-items-md-end cc-hero-mascot-col">
                    <img src="/assets/img/illustrations/comparativas-mascota.png"
                         class="cc-hero-mascot-floating img-fluid"
                         width="400"
                         height="400"
                         alt="Administracion de motos adjudicadas">
                </div>

                <div class="row gy-6 mb-6 gx-0 justify-content-start">
                    <?php if ($puedeAdminCobranza): ?>
                    <div class="col-12 col-lg-4">
                        <div class="card shadow-none bg-label-primary h-100">
                            <div class="card-body d-flex justify-content-between flex-wrap-reverse">
                                <div class="mb-0 w-100 app-academy-sm-60 d-flex flex-column justify-content-between text-center text-sm-start">
                                    <div class="card-title">
                                        <h5 class="text-primary mb-2">Admin Cobranza</h5>
                                        <p class="text-body w-sm-80 app-academy-xl-100">Administra responsables, asignacion de creditos adjudicados, comentarios e historial operativo de la cartera.</p>
                                    </div>
                                    <div class="mb-0 mt-3">
                                        <a href="/Adjudicacion/AsignacionCreditos" class="btn btn-primary w-100">
                                            <i class="fa-solid fa-arrow-right-to-bracket me-1"></i>Entrar a Admin Cobranza
                                        </a>
                                    </div>
                                </div>
                                <div class="w-100 app-academy-sm-40 d-flex justify-content-center justify-content-sm-end align-items-start align-self-start h-px-150 mb-4 mb-sm-0 flex-shrink-0 motos-admin-icon-slot">
                                    <span class="scaleX-n1-rtl motos-admin-icon-frame" aria-hidden="true">
                                        <svg class="motos-admin-icon-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" preserveAspectRatio="xMidYMid meet" aria-hidden="true">
                                            <rect x="10" y="18" width="44" height="34" rx="4" ry="4"/>
                                            <path d="M22 18v-6h20v6"/>
                                            <path d="M10 30h44"/>
                                            <path d="M24 40h16"/>
                                            <path d="M20 30v5M44 30v5"/>
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($puedeDictaminarCreditos): ?>
                    <div class="col-12 col-lg-4">
                        <div class="card shadow-none bg-label-info h-100">
                            <div class="card-body d-flex justify-content-between flex-wrap-reverse">
                                <div class="mb-0 w-100 app-academy-sm-60 d-flex flex-column justify-content-between text-center text-sm-start">
                                    <div class="card-title">
                                        <h5 class="text-info mb-2">Dictaminar creditos</h5>
                                        <p class="text-body w-sm-80 app-academy-xl-100">Interfaz web para simular el dictamen operativo de creditos adjudicados. Aqui prepararemos la busqueda, decision, evidencias y observaciones.</p>
                                    </div>
                                    <div class="mb-0 mt-3">
                                        <a href="/Adjudicacion/dictaminarCreditos" class="btn btn-info w-100">
                                            <i class="fa-solid fa-clipboard-check me-1"></i>Entrar a dictamen
                                        </a>
                                    </div>
                                </div>
                                <div class="w-100 app-academy-sm-40 d-flex justify-content-center justify-content-sm-end align-items-start align-self-start h-px-150 mb-4 mb-sm-0 flex-shrink-0 motos-admin-icon-slot">
                                    <span class="scaleX-n1-rtl motos-admin-icon-frame" aria-hidden="true">
                                        <svg class="motos-admin-icon-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" preserveAspectRatio="xMidYMid meet" aria-hidden="true">
                                            <rect x="12" y="12" width="40" height="44" rx="4" ry="4"/>
                                            <path d="M24 12v-4h16v4"/>
                                            <path d="M22 34l7 7 14-16"/>
                                            <path d="M22 24h20"/>
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if (!$puedeAdminCobranza && !$puedeDictaminarCreditos): ?>
                    <div class="col-12">
                        <div class="alert alert-warning mb-0">
                            No tienes permisos activos para las herramientas administrativas de Motos Adjudicadas.
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
</div>
<style>
.motos-admin-svg-defs {
    position: absolute;
    width: 0;
    height: 0;
    overflow: hidden;
}
.motos-admin-icon-svg {
    stroke: url(#motos-admin-icon-gradient);
    fill: none;
    stroke-width: 1.8;
    stroke-linecap: round;
    stroke-linejoin: round;
}
#motos-admin-landing .motos-admin-icon-slot {
    width: 100% !important;
    height: 7.5rem;
    flex: 0 0 7.5rem;
    align-self: stretch !important;
    justify-content: center !important;
    margin-bottom: 1rem !important;
}
#motos-admin-landing .motos-admin-icon-frame {
    width: 7.5rem;
    height: 7.5rem;
    min-width: 7.5rem;
    min-height: 7.5rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    overflow: hidden;
}
#motos-admin-landing .motos-admin-icon-frame .motos-admin-icon-svg {
    width: 100%;
    height: 100%;
    display: block;
    box-sizing: border-box;
    transform: scale(1.1);
    transform-origin: center center;
}
.cc-call-center-page,
#motos-admin-landing.cc-call-center-page {
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
</div>
