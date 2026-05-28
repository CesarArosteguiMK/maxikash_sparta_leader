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
        'img'    => 'https://cdn-icons-png.freepik.com/512/11053/11053297.png',
        'class'  => 'primary',
    ],
    [
        'titulo' => 'Borradores',
        'texto'  => 'Retoma rutas guardadas, ajusta puntos de recoleccion, ETA, transportista y destino antes de enviarlas.',
        'url'    => '/TrackingRecoleccion/borradores',
        'btn'    => 'Ver borradores',
        'icon'   => 'fa-file-pen',
        'img'    => 'https://cdn-icons-png.freepik.com/512/4545/4545742.png',
        'class'  => 'primary',
    ],
    [
        'titulo' => 'Rutas registradas',
        'texto'  => 'Monitorea rutas enviadas o en proceso, consulta el mapa, chat operativo, avance y cancelaciones.',
        'url'    => '/TrackingRecoleccion/rutas',
        'btn'    => 'Ver rutas',
        'icon'   => 'fa-map-marked-alt',
        'img'    => 'https://cdn-icons-png.freepik.com/512/854/854878.png',
        'class'  => 'primary',
    ],
    [
        'titulo' => 'CEDIS y Transportistas',
        'texto'  => 'Directorio operativo de CEDIS, transportistas internos y externos para la asignacion de rutas.',
        'url'    => '/TrackingRecoleccion/cedisTransportistas',
        'btn'    => 'Ver catalogos',
        'icon'   => 'fa-building-user',
        'img'    => 'https://cdn-icons-png.freepik.com/512/3135/3135715.png',
        'class'  => 'primary',
    ],
];
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
                                            <img class="img-fluid scaleX-n1-rtl trk-menu-img"
                                                 src="<?= htmlspecialchars($op['img'], ENT_QUOTES, 'UTF-8'); ?>"
                                                 alt="<?= htmlspecialchars($op['titulo'], ENT_QUOTES, 'UTF-8'); ?>">
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
#trk-landing .trk-menu-img {
    max-height: 135px;
    object-fit: contain;
}
@media (max-width: 767.98px) {
    #trk-landing .cc-hero-mascot-floating {
        margin-right: 0;
        margin-top: 1rem;
    }
}
</style>
