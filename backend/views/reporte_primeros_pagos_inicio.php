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
</style>

<div class="card">
    <div>
        <div class="card">
            <div class="row g-0 align-items-center">

                <!-- Texto -->
                <div class="col-12 col-md-8">
                    <div class="card-body">
                        <h5 class="card-title text-primary mb-3">HOLA, <?= isset($_SESSION['usuario_nombre']) ? htmlspecialchars(strtoupper($_SESSION['usuario_nombre']), ENT_QUOTES, 'UTF-8') : 'USUARIO'; ?> <i class="fa-solid fa-calendar-week ms-2 text-primary" aria-hidden="true"></i></h5>
                        <p class="mb-6">
                            Monitoreo en cada corte del comportamiento de los primeros pagos de la semana, identificando avances y proyectando la recuperación futura a partir del jueves.
                        </p>
                    </div>
                </div>

                <!-- Ilustración Sneat (man-with-laptop). Si falla la red, coloca el PNG en public/assets/img/illustrations/man-with-laptop.png -->
                <div class="col-12 col-md-4">
                    <div class="card-body ps-md-2 pe-5 text-end">
                        <img src="https://demos.themeselection.com/sneat-bootstrap-html-admin-template/assets/img/illustrations/man-with-laptop.png"
                             class="img-fluid scaleX-n1-rtl"
                             alt="View Badge User">
                    </div>
                </div>

                <div class="row gy-6 mb-6">
                    <div class="col-lg-4">
                        <div class="card shadow-none bg-label-primary h-100">
                            <div class="card-body d-flex justify-content-between flex-wrap-reverse">
                                <div class="mb-0 w-100 app-academy-sm-60 d-flex flex-column justify-content-between text-center text-sm-start">
                                    <div class="card-title">
                                        <h5 class="text-primary mb-1">Cobranza esperada - semana actual</h5>
                                        <p class="text-primary mb-0 fw-bold small">Semana <?= (int) $ppNumSemana ?></p>
                                        <p class="text-body-secondary small mb-1"><strong>Periodo del:</strong> <?= htmlspecialchars($ppRangoSemana, ENT_QUOTES, 'UTF-8') ?></p>
                                        <p class="text-body small w-sm-80 app-academy-xl-100 mb-0">Primer vencimiento en el lunes de cierre. Corte de mora según calendario de cartera (martes ~8:00). Matriz, distribución y jerarquía.</p>
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
                                        <h5 class="text-primary mb-1">Primeros pagos semana actual</h5>
                                        <p class="text-body small app-academy-sm-60 app-academy-xl-100 mb-0">Mismos créditos que cobranza esperada. El “corte actual” usa solo cortes del lunes (primer slot del día), sin martes en adelante.</p>
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
</div>
