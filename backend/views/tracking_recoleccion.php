<?php
$nombreUsuario = isset($_SESSION['usuario_nombre'])
    ? htmlspecialchars(strtoupper((string) $_SESSION['usuario_nombre']), ENT_QUOTES, 'UTF-8')
    : 'USUARIO';
$opcionesTracking = [
    [
        'titulo' => 'Planeacion de rutas',
        'texto'  => 'Consulta pendientes de recoleccion y retoma borradores para preparar rutas antes de enviarlas.',
        'url'    => '/TrackingRecoleccion/planeacion',
        'btn'    => 'Ver planeacion',
        'icon'   => 'fa-motorcycle',
        'visual' => 'checklist',
        'imgUrl' => 'https://cdn-icons-png.flaticon.com/512/9014/9014820.png',
        'class'  => 'primary',
    ],
    [
        'titulo' => 'Rutas registradas',
        'texto'  => 'Monitorea rutas enviadas o en proceso, consulta el mapa, chat operativo, avance y cancelaciones.',
        'url'    => '/TrackingRecoleccion/rutas',
        'btn'    => 'Ver rutas',
        'icon'   => 'fa-map-marked-alt',
        'visual' => 'map',
        'imgUrl' => 'https://cdn-icons-png.flaticon.com/512/18251/18251605.png',
        'class'  => 'primary',
    ],
    [
        'titulo' => 'CEDIS y Transportistas',
        'texto'  => 'Directorio operativo de CEDIS, transportistas internos y externos para la asignacion de rutas.',
        'url'    => '/TrackingRecoleccion/cedisTransportistas',
        'btn'    => 'Ver catalogos',
        'icon'   => 'fa-building-user',
        'visual' => 'avatar',
        'imgUrl' => 'https://cdn-icons-png.flaticon.com/512/3165/3165829.png',
        'class'  => 'primary',
    ],
    [
        'titulo' => 'Administracion de transportistas',
        'texto'  => 'Consulta disponibilidad, capacidad, rutas activas y alertas antes de asignar recolecciones.',
        'url'    => '/TrackingRecoleccion/administracionTransportistas',
        'btn'    => 'Ver operacion',
        'icon'   => 'fa-truck-fast',
        'visual' => 'rocket',
        'imgUrl' => 'https://cdn-icons-png.flaticon.com/512/11377/11377059.png',
        'class'  => 'primary',
    ],
];

if (!function_exists('trackingMenuSvg')) {
function trackingMenuSvg(string $type): string
{
    $icons = [
        'checklist' => <<<'SVG'
<svg viewBox="0 0 160 140" role="img" focusable="false">
    <path d="M45 20h51l24 24v70c0 9-7 16-16 16H45c-9 0-16-7-16-16V36c0-9 7-16 16-16Z" fill="#f8fbff"/>
    <path d="M45 20h51l24 24v70c0 9-7 16-16 16H45c-9 0-16-7-16-16V36c0-9 7-16 16-16Z" fill="none" stroke="#18b8f2" stroke-width="5" stroke-linejoin="round"/>
    <path d="M96 20v25h24" fill="none" stroke="#4968ff" stroke-width="5" stroke-linejoin="round"/>
    <path d="M48 62h37M48 82h48M48 102h34" fill="none" stroke="#18b8f2" stroke-width="5" stroke-linecap="round"/>
    <circle cx="110" cy="55" r="27" fill="#eef5ff" stroke="#5971ff" stroke-width="6"/>
    <path d="m98 55 9 9 18-18" fill="none" stroke="#4968ff" stroke-width="7" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
SVG,
        'rocket' => <<<'SVG'
<svg viewBox="0 0 160 140" role="img" focusable="false">
    <path d="M35 107c8-31 25-44 49-39l8 29c-18 3-33 8-47 22-4-1-8-5-10-12Z" fill="#ded8d6"/>
    <path d="M125 107c-8-31-25-44-49-39l-8 29c18 3 33 8 47 22 4-1 8-5 10-12Z" fill="#ded8d6"/>
    <path d="M68 118h24c-2-16-3-34 0-54H68c3 20 2 38 0 54Z" fill="#0da8d8"/>
    <path d="M80 20c21 18 29 41 24 72H56c-5-31 3-54 24-72Z" fill="#18c2df"/>
    <path d="M80 20c9 8 15 17 19 28H61c4-11 10-20 19-28Z" fill="#ff4051"/>
    <circle cx="80" cy="60" r="9" fill="#70818f"/>
    <path d="M74 84h12v16H74z" fill="#ffbd5b"/>
    <path d="M61 102v20M99 102v20" fill="none" stroke="#f5f2f0" stroke-width="8" stroke-linecap="round"/>
</svg>
SVG,
        'map' => <<<'SVG'
<svg viewBox="0 0 160 140" role="img" focusable="false">
    <path d="M24 105 47 44l33-13 34 14 22 60-34 13-33-14-45 1Z" fill="#8ce3f1"/>
    <path d="m47 44 22 60 33 14-22-87-33 13Z" fill="#f7d34a"/>
    <path d="m80 31 22 87 34-13-22-60-34-14Z" fill="#11b7ec"/>
    <path d="m34 79 30-13 20 18 29-12" fill="none" stroke="#20d45b" stroke-width="16" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M102 23c-19 0-34 15-34 34 0 27 34 60 34 60s34-33 34-60c0-19-15-34-34-34Z" fill="#ff4051"/>
    <circle cx="102" cy="56" r="11" fill="#fff"/>
</svg>
SVG,
        'avatar' => <<<'SVG'
<svg viewBox="0 0 160 140" role="img" focusable="false">
    <circle cx="80" cy="70" r="60" fill="#cfe5ff"/>
    <circle cx="80" cy="70" r="46" fill="#b9d9ff"/>
    <path d="M42 117c7-24 25-37 38-37s31 13 38 37c-10 8-23 13-38 13s-28-5-38-13Z" fill="#2f79b8"/>
    <path d="m68 87 12 12 12-12v-16H68v16Z" fill="#ffd0c2"/>
    <path d="M56 54c0-18 10-31 24-31s24 13 24 31c0 21-10 34-24 34S56 75 56 54Z" fill="#ffd9cc"/>
    <path d="M57 49c3-18 15-25 31-22 12 2 18 11 17 25-11-7-28-6-48-3Z" fill="#244d73"/>
    <path d="M64 93h32l-10 34H74L64 93Z" fill="#fff"/>
    <path d="m80 99 9 28H71l9-28Z" fill="#f14b4b"/>
    <path d="m64 91 16 8-13 11-9-13 6-6Zm32 0-16 8 13 11 9-13-6-6Z" fill="#e9f3ff"/>
</svg>
SVG,
    ];

    return $icons[$type] ?? $icons['checklist'];
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
                                planeacion de rutas, rutas registradas y el directorio de <strong>CEDIS y transportistas</strong>
                                para operar la recoleccion fisica de motos adjudicadas.
                            </p>
                        </div>
                    </div>

                    <div class="col-12 col-md-4 d-flex flex-column justify-content-end align-items-center align-items-md-end cc-hero-mascot-col">
                        <?php /* TODO: elegir imagen mascota
                        <img src="/assets/img/illustrations/tracking-join-__SPARTA_SECRET_REDACTED__.png"
                             class="cc-hero-mascot-floating img-fluid"
                             width="450"
                             height="450"
                             alt="Tracking recoleccion Maxikash">
                        */ ?>
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
                                                <?php if (!empty($op['imgUrl'])): ?>
                                                    <img src="<?= htmlspecialchars($op['imgUrl'], ENT_QUOTES, 'UTF-8'); ?>" alt="" style="width:132px;height:132px;object-fit:contain;">
                                                <?php else: ?>
                                                    <?= trackingMenuSvg((string) ($op['visual'] ?? 'checklist')); ?>
                                                <?php endif; ?>
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
