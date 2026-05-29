<?php
$nombreUsuario = isset($_SESSION['usuario_nombre'])
    ? htmlspecialchars(strtoupper((string) $_SESSION['usuario_nombre']), ENT_QUOTES, 'UTF-8')
    : 'USUARIO';
$opcionesTracking = [
    [
        'titulo' => 'Creditos disponibles',
        'texto'  => 'Consulta las motos adjudicadas listas para planeacion, filtra por estado y municipio, y prepara nuevas rutas.',
        'url'    => '/TrackingRecoleccion/creditos',
        'btn'    => 'Ver creditos',
        'icon'   => 'fa-motorcycle',
        'visual' => 'moto',
        'class'  => 'primary',
    ],
    [
        'titulo' => 'Borradores',
        'texto'  => 'Retoma rutas guardadas, ajusta puntos de recoleccion, ETA, transportista y destino antes de enviarlas.',
        'url'    => '/TrackingRecoleccion/borradores',
        'btn'    => 'Ver borradores',
        'icon'   => 'fa-file-pen',
        'visual' => 'draft',
        'class'  => 'primary',
    ],
    [
        'titulo' => 'Rutas registradas',
        'texto'  => 'Monitorea rutas enviadas o en proceso, consulta el mapa, chat operativo, avance y cancelaciones.',
        'url'    => '/TrackingRecoleccion/rutas',
        'btn'    => 'Ver rutas',
        'icon'   => 'fa-map-marked-alt',
        'visual' => 'route',
        'class'  => 'primary',
    ],
    [
        'titulo' => 'CEDIS y Transportistas',
        'texto'  => 'Directorio operativo de CEDIS, transportistas internos y externos para la asignacion de rutas.',
        'url'    => '/TrackingRecoleccion/cedisTransportistas',
        'btn'    => 'Ver catalogos',
        'icon'   => 'fa-building-user',
        'visual' => 'cedis',
        'class'  => 'primary',
    ],
];

if (!function_exists('trackingMenuSvg')) {
function trackingMenuSvg(string $type): string
{
    $icons = [
        'moto' => <<<'SVG'
<svg viewBox="0 0 160 140" role="img" focusable="false">
    <defs>
        <linearGradient id="trkMotoG" x1="20" y1="18" x2="138" y2="118" gradientUnits="userSpaceOnUse">
            <stop offset="0" stop-color="#38bdf8"/>
            <stop offset=".55" stop-color="#2563eb"/>
            <stop offset="1" stop-color="#172554"/>
        </linearGradient>
    </defs>
    <path d="M28 94c0-15 12-27 27-27s27 12 27 27-12 27-27 27-27-12-27-27Zm82 0c0-15 12-27 27-27s27 12 27 27-12 27-27 27-27-12-27-27Z" class="trk-svg-soft"/>
    <path d="M47 94a8 8 0 1 0 16 0 8 8 0 0 0-16 0Zm82 0a8 8 0 1 0 16 0 8 8 0 0 0-16 0Z" fill="#fff"/>
    <path d="M55 94h24l20-31h21l17 31M80 94 66 57h24l17 37M91 57l16-17h18M79 94h31M58 57h24" class="trk-svg-line"/>
    <path d="M104 39h20l8 12h-27" class="trk-svg-fill"/>
    <path d="M49 74h17m63 0h17" class="trk-svg-line-thin"/>
</svg>
SVG,
        'draft' => <<<'SVG'
<svg viewBox="0 0 160 140" role="img" focusable="false">
    <defs>
        <linearGradient id="trkDraftG" x1="38" y1="14" x2="124" y2="124" gradientUnits="userSpaceOnUse">
            <stop offset="0" stop-color="#38bdf8"/>
            <stop offset=".52" stop-color="#2563eb"/>
            <stop offset="1" stop-color="#172554"/>
        </linearGradient>
    </defs>
    <path d="M42 18h58l28 28v73a9 9 0 0 1-9 9H42a9 9 0 0 1-9-9V27a9 9 0 0 1 9-9Z" class="trk-svg-soft"/>
    <path d="M100 18v29h28" class="trk-svg-line"/>
    <path d="M56 59h49M56 77h58M56 95h34" class="trk-svg-line-thin"/>
    <path d="m103 101 27-27a8 8 0 0 1 11 11l-27 27-17 6 6-17Z" class="trk-svg-fill"/>
    <path d="m124 80 11 11" class="trk-svg-line-thin is-white"/>
</svg>
SVG,
        'route' => <<<'SVG'
<svg viewBox="0 0 160 140" role="img" focusable="false">
    <defs>
        <linearGradient id="trkRouteG" x1="22" y1="18" x2="136" y2="120" gradientUnits="userSpaceOnUse">
            <stop offset="0" stop-color="#38bdf8"/>
            <stop offset=".5" stop-color="#2563eb"/>
            <stop offset="1" stop-color="#172554"/>
        </linearGradient>
    </defs>
    <path d="M26 41 62 26l38 16 34-16v83l-34 16-38-16-36 15V41Z" class="trk-svg-soft"/>
    <path d="M62 26v83M100 42v83" class="trk-svg-line-thin"/>
    <path d="M47 94c27-36 50 18 71-26" class="trk-svg-line"/>
    <path d="M118 25c-14 0-25 11-25 25 0 20 25 44 25 44s25-24 25-44c0-14-11-25-25-25Z" class="trk-svg-fill"/>
    <circle cx="118" cy="50" r="9" fill="#fff"/>
</svg>
SVG,
        'cedis' => <<<'SVG'
<svg viewBox="0 0 160 140" role="img" focusable="false">
    <defs>
        <linearGradient id="trkCedisG" x1="28" y1="15" x2="132" y2="125" gradientUnits="userSpaceOnUse">
            <stop offset="0" stop-color="#38bdf8"/>
            <stop offset=".52" stop-color="#2563eb"/>
            <stop offset="1" stop-color="#172554"/>
        </linearGradient>
    </defs>
    <path d="M28 60 80 30l52 30v61H28V60Z" class="trk-svg-soft"/>
    <path d="M20 62 80 27l60 35M43 121V73h74v48" class="trk-svg-line"/>
    <path d="M56 85h48v36H56V85Z" class="trk-svg-fill"/>
    <path d="M56 97h48M80 85v36" class="trk-svg-line-thin is-white"/>
    <path d="M43 73h74M43 121h74" class="trk-svg-line-thin"/>
</svg>
SVG,
    ];

    return $icons[$type] ?? $icons['moto'];
}
}
?>

<div id="trk-landing" class="cc-call-center-page reporteria-landing-root">
    <div class="card">
        <div class="card">
            <div class="card">
                <div class="row g-0 align-items-center overflow-visible cc-hero-row cc-hero-row--con-mascota cc-hero-block">
                    <div class="col-12 col-md-8 cc-hero-text">
                        <div class="card-body">
                            <h5 class="card-title text-primary mb-3">
                                HOLA, <?= $nombreUsuario; ?>
                                <i class="fa-solid fa-route ms-2 text-primary" aria-hidden="true"></i>
                            </h5>
                            <p class="mb-6 mb-md-0">
                                Aqui entras al <strong>tracking de recoleccion</strong>: administra creditos disponibles,
                                borradores, rutas registradas y el directorio de <strong>CEDIS y transportistas</strong>
                                para operar la recoleccion fisica de motos adjudicadas.
                            </p>
                        </div>
                    </div>

                    <div class="col-12 col-md-4 d-flex flex-column justify-content-end align-items-center align-items-md-end cc-hero-mascot-col">
                        <img src="/assets/img/illustrations/tracking-join-__SPARTA_SECRET_REDACTED__.png"
                             class="cc-hero-mascot-floating img-fluid"
                             width="450"
                             height="450"
                             alt="Tracking recoleccion Maxikash">
                    </div>

                    <div class="row gy-6 mb-6 gx-0 justify-content-start">
                        <?php foreach ($opcionesTracking as $op): ?>
                            <div class="col-12 col-lg-3">
                                <div class="card shadow-none bg-label-<?= htmlspecialchars($op['class'], ENT_QUOTES, 'UTF-8'); ?> h-100 trk-menu-card">
                                    <div class="card-body d-flex justify-content-between flex-wrap-reverse">
                                        <div class="mb-0 w-100 app-academy-sm-60 d-flex flex-column justify-content-between text-center text-sm-start">
                                            <div class="card-title">
                                                <h5 class="text-<?= htmlspecialchars($op['class'], ENT_QUOTES, 'UTF-8'); ?> mb-2">
                                                    <?= htmlspecialchars($op['titulo'], ENT_QUOTES, 'UTF-8'); ?>
                                                </h5>
                                                <p class="text-body w-sm-80 app-academy-xl-100">
                                                    <?= htmlspecialchars($op['texto'], ENT_QUOTES, 'UTF-8'); ?>
                                                </p>
                                            </div>
                                            <div class="mb-0 mt-3">
                                                <a href="<?= htmlspecialchars($op['url'], ENT_QUOTES, 'UTF-8'); ?>"
                                                   class="btn btn-<?= htmlspecialchars($op['class'], ENT_QUOTES, 'UTF-8'); ?> w-100">
                                                    <i class="fa-solid <?= htmlspecialchars($op['icon'], ENT_QUOTES, 'UTF-8'); ?> me-1"></i>
                                                    <?= htmlspecialchars($op['btn'], ENT_QUOTES, 'UTF-8'); ?>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="w-100 app-academy-sm-40 d-flex justify-content-center justify-content-sm-end h-px-150 mb-4 mb-sm-0">
                                            <div class="trk-menu-visual" aria-hidden="true">
                                                <?= trackingMenuSvg((string) ($op['visual'] ?? 'moto')); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
#trk-landing.cc-call-center-page,
#trk-landing .cc-hero-row,
#trk-landing > .card {
    overflow: visible;
}
#trk-landing .cc-hero-block {
    position: relative;
    z-index: 0;
    overflow: visible;
}
#trk-landing .cc-hero-mascot-floating {
    max-width: min(280px, 80vw);
    margin-right: 2.5rem;
    margin-top: 1.75rem;
    filter: drop-shadow(0 20px 34px rgba(15, 23, 42, .16));
}
#trk-landing .trk-menu-card {
    border-radius: .65rem;
}
#trk-landing .trk-menu-visual {
    width: 132px;
    height: 132px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
#trk-landing .trk-menu-visual svg {
    width: 132px;
    height: 132px;
    overflow: visible;
    filter: drop-shadow(0 16px 22px rgba(37, 54, 83, .12));
}
#trk-landing .trk-menu-visual .trk-svg-soft {
    fill: #eff6ff;
    stroke: url(#trkMotoG);
    stroke-width: 3;
}
#trk-landing .trk-menu-visual .trk-svg-line {
    fill: none;
    stroke: url(#trkMotoG);
    stroke-width: 8;
    stroke-linecap: round;
    stroke-linejoin: round;
}
#trk-landing .trk-menu-visual .trk-svg-line-thin {
    fill: none;
    stroke: url(#trkMotoG);
    stroke-width: 4;
    stroke-linecap: round;
    stroke-linejoin: round;
}
#trk-landing .trk-menu-visual .trk-svg-fill {
    fill: url(#trkMotoG);
}
#trk-landing .trk-menu-visual .is-white {
    stroke: rgba(255, 255, 255, .82);
}
@media (max-width: 767.98px) {
    #trk-landing .cc-hero-mascot-floating {
        margin-right: 0;
        margin-top: 1rem;
    }
}
</style>
