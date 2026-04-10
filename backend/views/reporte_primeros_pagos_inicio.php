<?php
$ppSemanaTz = new DateTimeZone('America/Mexico_City');
$ppHoy = new DateTimeImmutable('now', $ppSemanaTz);
$ppDow = (int) $ppHoy->format('N');
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
<svg xmlns="http://www.w3.org/2000/svg" width="0" height="0" aria-hidden="true">
    <defs>
        <linearGradient id="pp-icon-gradient" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#00B4DB"/>
            <stop offset="100%" style="stop-color:#6E48AA"/>
        </linearGradient>
    </defs>
</svg>

<style>
.pp-icon-svg { height: 150px; width: auto; max-width: 100%; stroke: url(#pp-icon-gradient); fill: none; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }

/* Evita scroll horizontal por la ilustración; el hero no recorta la mascota */
.pp-primeros-pagos-page {
    overflow-x: hidden;
}
.pp-primeros-pagos-page .pp-primeros-inner-card,
.pp-primeros-pagos-page .pp-hero-row {
    overflow: visible;
}
.pp-hero-block {
    position: relative;
    z-index: 0;
    overflow: visible;
}
.pp-hero-text .card-body {
    padding-bottom: 1rem !important;
}
@media (min-width: 768px) {
    .pp-hero-text .card-body {
        padding-top: 1.25rem !important;
        padding-bottom: 1rem !important;
        padding-right: 0.5rem;
    }
}
/* Mascota en columna Bootstrap (flujo normal): tamaño tipo referencia, sin absolute ni overflow clip */
.pp-hero-mascot-col {
    padding-top: 0.5rem;
    padding-bottom: 0.75rem;
}
@media (min-width: 768px) {
    .pp-hero-mascot-col {
        padding-top: 1rem;
        padding-right: 1.75rem;
        padding-bottom: 1rem;
        padding-left: 0.5rem;
    }
}
.pp-hero-mascot-floating {
    display: block;
    object-fit: contain;
    object-position: bottom center;
    filter: drop-shadow(0 10px 28px rgba(26, 82, 168, 0.12));
}
@media (max-width: 767.98px) {
    .pp-hero-mascot-floating {
        margin: 0.5rem auto 0;
        max-width: min(68vw, 240px);
        max-height: min(38vh, 220px);
        width: auto;
        height: auto;
        object-position: bottom center;
    }
}
@media (min-width: 768px) {
    .pp-hero-row.pp-hero-row--con-mascota {
        align-items: stretch;
    }
    .pp-hero-mascot-floating {
        width: auto;
        height: auto;
        max-width: 100%;
        max-height: min(300px, 52vh);
        margin-left: auto;
        margin-right: 0.75rem;
        transform: translateX(-1.85rem);
        object-position: bottom right;
    }
}
body.dark-mode .pp-hero-mascot-floating {
    filter: drop-shadow(0 12px 32px rgba(0, 0, 0, 0.35));
}
</style>

<div class="card pp-primeros-pagos-page">
    <div class="overflow-visible">
        <div class="card pp-primeros-inner-card overflow-visible border-0 shadow-none">
            <div class="pp-hero-block">
            <div class="row g-0 overflow-visible pp-hero-row pp-hero-row--con-mascota">

                <div class="col-12 col-md-8 pp-hero-text">
                    <div class="card-body">
                        <h5 class="card-title text-primary mb-2">HOLA, <?= isset($_SESSION['usuario_nombre']) ? htmlspecialchars(strtoupper($_SESSION['usuario_nombre']), ENT_QUOTES, 'UTF-8') : 'USUARIO'; ?> <i class="fa-solid fa-calendar-week ms-2 text-primary" aria-hidden="true"></i></h5>
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

            </div>
            </div>

                <div class="row gy-4 mb-4 gx-0">
                    <div class="col-lg-4">
                        <div class="card shadow-none bg-label-primary h-100">
                            <div class="card-body d-flex justify-content-between flex-wrap-reverse">
                                <div class="mb-0 w-100 app-academy-sm-60 d-flex flex-column justify-content-between text-center text-sm-start">
                                    <div class="card-title">
                                        <h5 class="text-primary mb-1">Cobranza esperada - semana actual</h5>
                                        <p class="text-primary mb-0 fw-bold small">Semana <?= (int) $ppNumSemana ?></p>
                                        <p class="text-body-secondary small mb-1"><strong>Periodo del:</strong> <?= htmlspecialchars($ppRangoSemana, ENT_QUOTES, 'UTF-8') ?></p>
                                        <p class="text-body small w-sm-80 app-academy-xl-100 mb-0">Disponible de martes a domingo. En este espacio podrás consultar el resumen ejecutivo de los primeros pagos con fecha de vencimiento correspondiente a la semana en curso.</p>
                                    </div>
                                    <div class="mb-0 mt-3">
                                        <a href="/reporteria/VencimientosLunes" class="btn btn-primary w-100">
                                            <i class="fa fa-table-columns me-1"></i>Ver cobranza esperada
                                        </a>
                                    </div>
                                </div>
                                <div class="w-100 app-academy-sm-40 d-flex justify-content-center justify-content-sm-end h-px-150 mb-4 mb-sm-0">
                                    <svg class="pp-icon-svg scaleX-n1-rtl" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" aria-hidden="true">
                                        <rect x="8" y="6" width="48" height="44" rx="4" ry="4"/>
                                        <path d="M8 18h48M20 6v8M44 6v8"/>
                                        <path d="M22 32l6 6 14-14"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card shadow-none bg-label-primary h-100">
                            <div class="card-body d-flex justify-content-between flex-wrap-reverse">
                                <div class="mb-0 w-100 app-academy-sm-60 d-flex flex-column justify-content-between text-center text-sm-start">
                                    <div class="card-title">
                                        <h5 class="text-primary mb-1">Primeros pagos próxima semana</h5>
                                        <p class="text-primary mb-0 fw-bold small">Semana <?= (int) $ppNumSemanaSiguiente ?></p>
                                        <p class="text-body-secondary small mb-1"><strong>Periodo del:</strong> <?= htmlspecialchars($ppRangoVentanaPrimerosPagos, ENT_QUOTES, 'UTF-8') ?></p>
                                        <p class="text-body small w-sm-80 app-academy-xl-100 mb-0">Disponible de jueves a lunes. En este espacio podrás consultar el resumen ejecutivo de los primeros pagos previstos para la siguiente semana, correspondiente a ventas realizadas en días anteriores cuya primera fecha de vencimiento ocurre en la próxima semana.</p>
                                    </div>
                                    <div class="mb-0 mt-3">
                                        <a href="/reporteria/VencimientosLunesSiguienteSemana" class="btn btn-primary w-100">
                                            <i class="fa fa-calendar-check me-1"></i>Ver corte semana actual
                                        </a>
                                    </div>
                                </div>
                                <div class="w-100 app-academy-sm-40 d-flex justify-content-center justify-content-sm-end h-px-150 mb-4 mb-sm-0">
                                    <svg class="pp-icon-svg scaleX-n1-rtl" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" aria-hidden="true">
                                        <rect x="6" y="8" width="44" height="40" rx="4" ry="4"/>
                                        <path d="M6 22h44M22 8v10M38 8v10"/>
                                        <path d="M52 36l6 6-6 6M58 42H42"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

        </div>
    </div>
</div>
