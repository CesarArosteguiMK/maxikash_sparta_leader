<?php
/* Permisos por card (Reporteria::PrimerosPagos); si no vienen del controlador, no ocultar nada (p. ej. vista suelta). */
if (!isset($pp_perm_cobranza) && !isset($pp_perm_cartera) && !isset($pp_perm_proxima) && !isset($pp_perm_historico)) {
    $pp_perm_cobranza = $pp_perm_cartera = $pp_perm_proxima = $pp_perm_historico = true;
}
$pp_perm_cobranza = !empty($pp_perm_cobranza);
$pp_perm_cartera = !empty($pp_perm_cartera);
$pp_perm_proxima = !empty($pp_perm_proxima);
$pp_perm_historico = !empty($pp_perm_historico);
$pp_cards_visibles = (int) $pp_perm_cobranza + (int) $pp_perm_cartera + (int) $pp_perm_proxima + (int) $pp_perm_historico;

$ppSemanaTz = new DateTimeZone('America/Mexico_City');
$ppHoy = new DateTimeImmutable('now', $ppSemanaTz);
$ppDow = (int) $ppHoy->format('N');
/** Martes o miércoles CDMX: bloqueo de «Primeros pagos próxima semana» (solo jueves→lunes). */
$ppBloqueoProximaSemanaMartesMiercoles = ($ppDow === 2 || $ppDow === 3);
$ppLunes = $ppHoy->modify('-' . ($ppDow - 1) . ' days');
$ppDomingo = $ppLunes->modify('+6 days');
$ppNumSemana = (int) $ppLunes->format('W');
$ppMeses = [1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril', 5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto', 9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'];
$ppDiasSem = [1 => 'lunes', 2 => 'martes', 3 => 'miércoles', 4 => 'jueves', 5 => 'viernes', 6 => 'sábado', 7 => 'domingo'];
$ppFmtDia = static function (DateTimeImmutable $d, bool $incluirAnio) use ($ppMeses, $ppDiasSem): string {
    $n = (int) $d->format('N');
    $dia = (int) $d->format('j');
    $mes = $ppMeses[(int) $d->format('n')];
    $s = $ppDiasSem[$n] . ' ' . $dia . ' de ' . $mes;
    if ($incluirAnio) {
        $s .= ' de ' . $d->format('Y');
    }
    return $s;
};
if ($ppLunes->format('Y') === $ppDomingo->format('Y')) {
    $ppRangoSemana = $ppFmtDia($ppLunes, false) . ' al ' . $ppFmtDia($ppDomingo, true);
} else {
    $ppRangoSemana = $ppFmtDia($ppLunes, true) . ' al ' . $ppFmtDia($ppDomingo, true);
}

/* Cartera: periodo martes (apertura) → lunes siguiente (cierre), misma semana operativa que Cobranza esperada. */
$ppMartesCartera = $ppLunes->modify('+1 day');
$ppLunesCierreCartera = $ppLunes->modify('+7 days');
if ($ppMartesCartera->format('Y') === $ppLunesCierreCartera->format('Y')) {
    $ppRangoCartera = $ppFmtDia($ppMartesCartera, false) . ' al ' . $ppFmtDia($ppLunesCierreCartera, true);
} else {
    $ppRangoCartera = $ppFmtDia($ppMartesCartera, true) . ' al ' . $ppFmtDia($ppLunesCierreCartera, true);
}

/* Próximo lunes de corte (semana siguiente) y periodo jueves→lunes (alineado a «Disponible de jueves a lunes») */
$ppLunesSiguiente = $ppLunes->modify('+7 days');
$ppNumSemanaSiguiente = (int) $ppLunesSiguiente->format('W');
$ppJuevesVentana = $ppLunesSiguiente->modify('-4 days');
if ($ppJuevesVentana->format('Y') === $ppLunesSiguiente->format('Y')) {
    $ppRangoVentanaPrimerosPagos = $ppFmtDia($ppJuevesVentana, false) . ' al ' . $ppFmtDia($ppLunesSiguiente, true);
} else {
    $ppRangoVentanaPrimerosPagos = $ppFmtDia($ppJuevesVentana, true) . ' al ' . $ppFmtDia($ppLunesSiguiente, true);
}
?>
<div id="pp-landing" class="pp-primeros-pagos-page reporteria-landing-root">
<div class="card">
    <svg xmlns="http://www.w3.org/2000/svg" class="pp-svg-defs" aria-hidden="true" focusable="false">
        <defs>
            <linearGradient id="pp-icon-gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" style="stop-color:#00B4DB"/>
                <stop offset="100%" style="stop-color:#6E48AA"/>
            </linearGradient>
        </defs>
    </svg>
    <div class="card">
        <div class="card">
            <div class="row g-0 align-items-center overflow-visible pp-hero-row pp-hero-row--con-mascota pp-hero-block">

                <div class="col-12 col-md-8 pp-hero-text">
                    <div class="card-body">
                        <h5 class="card-title text-primary mb-3">HOLA, <?= isset($_SESSION['usuario_nombre']) ? htmlspecialchars(strtoupper($_SESSION['usuario_nombre']), ENT_QUOTES, 'UTF-8') : 'USUARIO'; ?> <i class="fa-solid fa-calendar-week ms-2 text-primary" aria-hidden="true"></i></h5>
                        <p class="mb-0">
                            Monitoreo en cada corte del comportamiento de los primeros pagos de la semana, identificando avances y proyectando la recuperación futura a partir del jueves.
                        </p>
                    </div>
                </div>

                <div class="col-12 col-md-4 d-flex flex-column justify-content-end align-items-center align-items-md-end pp-hero-mascot-col">
                    <img src="/assets/img/illustrations/primeros-pagos-mascota.png"
                         class="pp-hero-mascot-floating img-fluid"
                         alt="Primeros pagos — ilustración">
                </div>

                <div class="row gy-6 mb-6 gx-0 align-items-stretch">
                    <?php if ($pp_cards_visibles === 0): ?>
                    <div class="col-12">
                        <div class="alert alert-warning mb-0" role="alert">
                            No tiene permisos para ver tarjetas aquí. Solicite en Capital Humano el acceso que corresponda.
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($pp_perm_cobranza): ?>
                    <div class="col-lg-3 col-md-6 d-flex">
                        <div class="card shadow-none bg-label-primary h-100 w-100 d-flex flex-column">
                            <div class="card-body pp-landing-card-body d-flex justify-content-between flex-wrap-reverse align-items-stretch flex-grow-1 py-3 px-3">
                                <div class="mb-0 w-100 app-academy-sm-60 d-flex flex-column text-center text-sm-start min-w-0 pp-landing-card-text">
                                    <div class="card-title">
                                        <h5 class="text-primary mb-1">Cobranza esperada - semana actual</h5>
                                        <p class="text-primary mb-0 fw-bold small">Semana <?= (int) $ppNumSemana ?></p>
                                        <p class="text-body-secondary small mb-1"><strong>Periodo del:</strong> <?= htmlspecialchars($ppRangoSemana, ENT_QUOTES, 'UTF-8') ?></p>
                                        <p class="text-body small w-sm-80 app-academy-xl-100 mb-0 pp-landing-card-desc">Disponible de martes a domingo. Resumen ejecutivo del corte de primeros pagos de la semana en curso</p>
                                    </div>
                                    <div class="pp-landing-card-filler flex-grow-1 w-100" aria-hidden="true"></div>
                                    <div class="pp-landing-card-actions mt-2">
                                        <?php if ($ppDow === 1): ?>
                                        <div class="alert alert-warning py-2 px-2 small text-start mb-2" role="alert">
                                            La cartera no abre hasta el martes, revise los datos en la sección de <strong>Primeros pagos próxima semana</strong>.
                                        </div>
                                        <button type="button" class="btn btn-secondary w-100" id="ppBtnCobranzaEsperadaLunes">
                                            <i class="fa fa-table-columns me-1"></i>Ver cobranza esperada
                                        </button>
                                        <?php else: ?>
                                        <a href="/analitica/VencimientosLunes" class="btn btn-primary w-100">
                                            <i class="fa fa-table-columns me-1"></i>Ver cobranza esperada
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="w-100 app-academy-sm-40 d-flex justify-content-center justify-content-sm-end align-items-start align-self-start mb-4 mb-sm-0 flex-shrink-0 pp-landing-icon-slot">
                                    <span class="scaleX-n1-rtl pp-landing-icon-frame pp-landing-icon-frame--cobranza" aria-hidden="true">
                                    <svg class="pp-icon-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" preserveAspectRatio="xMidYMid meet" aria-hidden="true">
                                        <rect x="8" y="8" width="48" height="42" rx="4" ry="4"/>
                                        <path d="M8 20h48M20 8v8M44 8v8"/>
                                        <path d="M22 33l6 6 14-14"/>
                                    </svg>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($pp_perm_cartera): ?>
                    <div class="col-lg-3 col-md-6 d-flex">
                        <div class="card shadow-none bg-label-primary h-100 w-100 d-flex flex-column">
                            <div class="card-body pp-landing-card-body d-flex justify-content-between flex-wrap-reverse align-items-stretch flex-grow-1 py-3 px-3">
                                <div class="mb-0 w-100 app-academy-sm-60 d-flex flex-column text-center text-sm-start min-w-0 pp-landing-card-text">
                                    <div class="card-title">
                                        <h5 class="text-primary mb-1">Cartera - semana actual</h5>
                                        <p class="text-primary mb-0 fw-bold small">Semana <?= (int) $ppNumSemana ?></p>
                                        <p class="text-body-secondary small mb-1"><strong>Periodo del:</strong> <?= htmlspecialchars($ppRangoCartera, ENT_QUOTES, 'UTF-8') ?></p>
                                        <p class="text-body small w-sm-80 app-academy-xl-100 mb-0 pp-landing-card-desc">Cartera completa de la semana actual</p>
                                    </div>
                                    <div class="pp-landing-card-filler flex-grow-1 w-100" aria-hidden="true"></div>
                                    <div class="pp-landing-card-actions mt-2">
                                        <a href="/analitica/Cartera" class="btn btn-primary w-100">
                                            <i class="fa fa-briefcase me-1"></i>Ver cartera
                                        </a>
                                    </div>
                                </div>
                                <div class="w-100 app-academy-sm-40 d-flex justify-content-center justify-content-sm-end align-items-start align-self-start mb-4 mb-sm-0 flex-shrink-0 pp-landing-icon-slot">
                                    <span class="scaleX-n1-rtl pp-landing-icon-frame pp-landing-icon-frame--cartera" aria-hidden="true">
                                    <svg class="pp-icon-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" preserveAspectRatio="xMidYMid meet" aria-hidden="true">
                                        <rect x="8" y="24" width="48" height="26" rx="3" ry="3"/>
                                        <path d="M18 24v-8h28v8"/>
                                        <path d="M24 16V8h16v8"/>
                                        <path d="M27 38h10"/>
                                    </svg>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($pp_perm_proxima): ?>
                    <div class="col-lg-3 col-md-6 d-flex">
                        <div class="card shadow-none bg-label-primary h-100 w-100 d-flex flex-column">
                            <div class="card-body pp-landing-card-body d-flex justify-content-between flex-wrap-reverse align-items-stretch flex-grow-1 py-3 px-3">
                                <div class="mb-0 w-100 app-academy-sm-60 d-flex flex-column text-center text-sm-start min-w-0 pp-landing-card-text">
                                    <div class="card-title">
                                        <h5 class="text-primary mb-1">Primeros pagos próxima semana</h5>
                                        <p class="text-primary mb-0 fw-bold small">Semana <?= (int) $ppNumSemanaSiguiente ?></p>
                                        <p class="text-body-secondary small mb-1"><strong>Periodo del:</strong> <?= htmlspecialchars($ppRangoVentanaPrimerosPagos, ENT_QUOTES, 'UTF-8') ?></p>
                                        <p class="text-body small w-sm-80 app-academy-xl-100 mb-0 pp-landing-card-desc">Disponible de jueves a lunes. Anticipa primeros pagos de la semana siguiente</p>
                                    </div>
                                    <div class="pp-landing-card-filler flex-grow-1 w-100" aria-hidden="true"></div>
                                    <div class="pp-landing-card-actions mt-2">
                                        <?php if ($ppBloqueoProximaSemanaMartesMiercoles): ?>
                                        <div class="alert alert-warning py-2 px-2 small text-start mb-2" role="alert">
                                            La cartera de próxima semana no está disponible; podrá consultarla a partir del jueves. Mientras tanto revise <strong>Cobranza esperada — semana actual</strong>.
                                        </div>
                                        <button type="button" class="btn btn-secondary w-100" id="ppBtnProximaSemanaBloqueado">
                                            <i class="fa fa-calendar-check me-1"></i>Ver corte semana actual
                                        </button>
                                        <?php else: ?>
                                        <a href="/analitica/VencimientosLunesSiguienteSemana" class="btn btn-primary w-100">
                                            <i class="fa fa-calendar-check me-1"></i>Ver corte semana actual
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="w-100 app-academy-sm-40 d-flex justify-content-center justify-content-sm-end align-items-start align-self-start mb-4 mb-sm-0 flex-shrink-0 pp-landing-icon-slot">
                                    <span class="scaleX-n1-rtl pp-landing-icon-frame pp-landing-icon-frame--proxima" aria-hidden="true">
                                    <svg class="pp-icon-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" preserveAspectRatio="xMidYMid meet" aria-hidden="true">
                                        <rect x="6" y="8" width="44" height="42" rx="4" ry="4"/>
                                        <path d="M6 20h44M22 8v8M38 8v8"/>
                                        <path d="M52 38l6 6-6 6M58 44H42"/>
                                    </svg>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($pp_perm_historico): ?>
                    <div class="col-lg-3 col-md-6 d-flex">
                        <div class="card shadow-none bg-label-secondary h-100 w-100 d-flex flex-column" id="pp-card-historico">
                            <div class="card-body pp-landing-card-body d-flex justify-content-between flex-wrap-reverse align-items-stretch flex-grow-1 py-3 px-3">
                                <div class="mb-0 w-100 app-academy-sm-60 d-flex flex-column text-center text-sm-start min-w-0 pp-landing-card-text">
                                    <div class="card-title">
                                        <h5 class="text-primary mb-1">Histórico</h5>
                                        <p class="text-primary mb-0 fw-bold small">Primeros pagos</p>
                                        <p class="text-body-secondary small mb-1"><strong>Consulta:</strong> últimas 5 semanas cerradas</p>
                                        <p class="text-body small w-sm-80 app-academy-xl-100 mb-0 pp-landing-card-desc">Consulta las últimas cinco semanas cerradas de primeros pagos</p>
                                    </div>
                                    <div class="pp-landing-card-filler flex-grow-1 w-100" aria-hidden="true"></div>
                                    <div class="pp-landing-card-actions mt-2">
                                        <a href="/analitica/PrimerosPagosHistorico" class="btn btn-primary w-100">
                                            <i class="fa-solid fa-clock-rotate-left me-1"></i>Ver histórico
                                        </a>
                                    </div>
                                </div>
                                <div class="w-100 app-academy-sm-40 d-flex justify-content-center justify-content-sm-end align-items-start align-self-start mb-4 mb-sm-0 flex-shrink-0 pp-landing-icon-slot">
                                    <span class="scaleX-n1-rtl pp-landing-icon-frame pp-landing-icon-frame--historico" aria-hidden="true">
                                    <svg class="pp-icon-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" preserveAspectRatio="xMidYMid meet" aria-hidden="true">
                                        <circle cx="32" cy="30" r="20"/>
                                        <path d="M32 18v12l9 5"/>
                                        <path d="M12 8l9 9M52 8l-9 9"/>
                                    </svg>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
</div>
<style>
.pp-icon-svg { stroke: url(#pp-icon-gradient); fill: none; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
/*
 * Iconos: mismo borde superior en todas las tarjetas. No se centran en vertical
 * porque cada card puede tener distinta altura. Cuadrado fijo + escala unica.
 */
#pp-landing .pp-landing-icon-slot {
    min-height: 0;
}
@media (min-width: 576px) {
    #pp-landing .pp-landing-card-body > .pp-landing-icon-slot {
        padding-top: 0.15rem;
    }
}
#pp-landing .pp-landing-icon-frame {
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
#pp-landing .pp-landing-icon-frame .pp-icon-svg {
    width: 100%;
    height: 100%;
    max-width: 100%;
    max-height: 100%;
    display: block;
    box-sizing: border-box;
    transform: scale(1.1);
    transform-origin: center center;
}
#pp-landing .pp-landing-icon-frame--cartera .pp-icon-svg,
#pp-landing .pp-landing-icon-frame--historico .pp-icon-svg {
    transform: translateY(-0.45rem) scale(1.1);
}
.pp-primeros-pagos-page,
#pp-landing.pp-primeros-pagos-page {
    overflow: visible;
}
.pp-primeros-pagos-page .pp-hero-row {
    overflow: visible;
}
.pp-primeros-pagos-page > .card {
    overflow: visible;
    position: relative;
}
#pp-card-historico {
    scroll-margin-top: 5.5rem;
}
/* Fila de cards: misma altura; títulos e iconos alineados; botones al mismo borde inferior */
#pp-landing .pp-landing-card-body {
    flex: 1 1 auto;
    min-height: 0;
}
#pp-landing .pp-landing-card-text {
    flex: 1 1 auto;
    min-height: 0;
    min-width: 0;
}
#pp-landing .pp-landing-card-filler {
    flex: 1 1 auto;
    min-height: 0;
}
/* Altura de título igualada entre cards sin hueco extra: exactamente 2 líneas (2lh), no rem fijo */
@media (min-width: 768px) {
    #pp-landing .pp-landing-card-text .card-title > h5.text-primary {
        line-height: 1.25;
        min-height: 2lh;
    }
}
.pp-hero-block {
    position: relative;
    z-index: 0;
    overflow: visible;
    --pp-mascot-max-w: 280px;
    --pp-mascot-max-h: min(300px, 44vh);
    --pp-mascot-translate-x: -6rem;
    --pp-mascot-translate-y: 3rem;
}
.pp-hero-text .card-body {
    padding-bottom: 1.25rem !important;
    padding-top: 2rem !important;
}
@media (min-width: 768px) {
    .pp-hero-row.pp-hero-row--con-mascota {
        align-items: stretch;
        min-height: 23rem;
        padding-bottom: 5rem;
    }
    .pp-hero-text .card-body {
        padding-top: 2rem !important;
        padding-bottom: 1.5rem !important;
        padding-right: 0.5rem;
    }
}
.pp-hero-mascot-col {
    padding-top: 1rem;
    padding-bottom: 2rem;
}
@media (min-width: 768px) {
    .pp-hero-mascot-col {
        position: relative;
        min-height: 0;
        padding: 0;
        align-self: stretch;
    }
    .pp-hero-text {
        position: relative;
        z-index: 2;
    }
}
.pp-hero-mascot-floating {
    display: block;
    object-fit: contain;
    object-position: bottom center;
    filter: drop-shadow(0 10px 28px rgba(26, 82, 168, 0.12));
}
@media (max-width: 767.98px) {
    .pp-hero-row.pp-hero-row--con-mascota {
        min-height: 15rem;
    }
    .pp-hero-text .card-body {
        text-align: center;
        padding-top: 2rem !important;
        padding-bottom: 1rem !important;
        padding-left: 1rem !important;
        padding-right: 1rem !important;
    }
    .pp-hero-mascot-col {
        align-items: center !important;
        padding-top: 0;
    }
    .pp-hero-mascot-floating {
        margin: 0 auto;
        max-width: min(58vw, 200px);
        max-height: min(32vh, 200px);
        width: auto;
        height: auto;
        object-position: bottom center;
        transform: translateY(var(--pp-mascot-translate-y, 3rem));
    }
}
@media (min-width: 768px) {
    .pp-hero-mascot-floating {
        position: relative;
        right: auto;
        bottom: auto;
        z-index: 1;
        width: auto;
        height: auto;
        max-width: var(--pp-mascot-max-w, 280px);
        max-height: var(--pp-mascot-max-h, 300px);
        margin: 0 0 0 auto;
        object-position: bottom right;
        transform: translate(var(--pp-mascot-translate-x, -6rem), var(--pp-mascot-translate-y, 3rem));
    }
}
body.dark-mode .pp-hero-mascot-floating {
    filter: drop-shadow(0 12px 32px rgba(0, 0, 0, 0.35));
}
.pp-svg-defs {
    position: absolute;
    width: 0;
    height: 0;
    overflow: hidden;
    pointer-events: none;
}
</style>
<script>
(function () {
    var PP_MSG_CARTERA_LUNES = 'La cartera no abre hasta el martes, revise los datos en la sección de Primeros pagos próxima semana.';
    var PP_MSG_PROXIMA_SEMANA_CERRADA = 'La cartera de primeros pagos (próxima semana) no está disponible. Podrá consultarla de jueves a lunes; mientras tanto revise la sección de Cobranza esperada — semana actual.';
    var PP_ES_LUNES = <?php echo $ppDow === 1 ? 'true' : 'false'; ?>;
    var PP_ES_MARTES_MIERCOLES = <?php echo $ppBloqueoProximaSemanaMartesMiercoles ? 'true' : 'false'; ?>;
    document.addEventListener('DOMContentLoaded', function () {
        function ppMostrarMsgCarteraLunes() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'info', title: 'Cobranza esperada — semana actual', text: PP_MSG_CARTERA_LUNES });
            } else {
                alert(PP_MSG_CARTERA_LUNES);
            }
        }
        var btn = document.getElementById('ppBtnCobranzaEsperadaLunes');
        if (btn) {
            btn.addEventListener('click', ppMostrarMsgCarteraLunes);
        }
        var btnProx = document.getElementById('ppBtnProximaSemanaBloqueado');
        if (btnProx) {
            btnProx.addEventListener('click', function () {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'info', title: 'Primeros pagos próxima semana', text: PP_MSG_PROXIMA_SEMANA_CERRADA });
                } else {
                    alert(PP_MSG_PROXIMA_SEMANA_CERRADA);
                }
            });
        }
        try {
            var params = new URLSearchParams(window.location.search || '');
            if (params.get('pp_cartera_lunes') === '1' && PP_ES_LUNES) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'info', title: 'Cobranza esperada — semana actual', text: PP_MSG_CARTERA_LUNES });
                } else {
                    alert(PP_MSG_CARTERA_LUNES);
                }
                if (window.history && window.history.replaceState) {
                    window.history.replaceState(null, '', window.location.pathname);
                }
            }
            if (params.get('pp_proxima_semana_cerrada') === '1' && PP_ES_MARTES_MIERCOLES) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'info', title: 'Primeros pagos próxima semana', text: PP_MSG_PROXIMA_SEMANA_CERRADA });
                } else {
                    alert(PP_MSG_PROXIMA_SEMANA_CERRADA);
                }
                if (window.history && window.history.replaceState) {
                    window.history.replaceState(null, '', window.location.pathname);
                }
            }
            if (window.location.hash === '#pp-card-historico') {
                var elHist = document.getElementById('pp-card-historico');
                if (elHist) {
                    setTimeout(function () {
                        elHist.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }, 200);
                }
            }
        } catch (e1) { /* ignorar */ }
    });
})();
</script>
</div><!-- /#pp-landing -->
