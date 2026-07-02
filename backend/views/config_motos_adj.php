<?php
$nombreUsuario = isset($_SESSION['usuario_nombre'])
    ? htmlspecialchars(strtoupper((string) $_SESSION['usuario_nombre']), ENT_QUOTES, 'UTF-8')
    : 'USUARIO';

$opcionesParametros = [
    [
        'titulo' => 'Fechas de rutas',
        'texto'  => 'Configura la anticipacion minima para programar rutas de recoleccion y controlar fechas operativas.',
        'url'    => '/ConfigMotosAdj/rutas',
        'btn'    => 'Ver fechas',
        'icon'   => 'fa-calendar-days',
        'visual' => 'calendar',
    ],
    [
        'titulo' => 'FAD motos adjudicadas',
        'texto'  => 'Controla el encendido general de FAD, excepciones por credito, historial y decisiones recientes.',
        'url'    => '/ConfigMotosAdj/fad',
        'btn'    => 'Ver FAD',
        'icon'   => 'fa-file-signature',
        'visual' => 'document',
    ],
];

if (!function_exists('parametrosMotosVisual')) {
function parametrosMotosVisual(string $type): string
{
    $icons = [
        'calendar' => <<<'SVG'
<svg viewBox="0 0 160 140" role="img" focusable="false">
    <rect x="30" y="28" width="100" height="90" rx="15" fill="#eef5ff" stroke="#2f63d9" stroke-width="5"/>
    <path d="M30 52h100" stroke="#2f63d9" stroke-width="5"/>
    <path d="M55 20v20M105 20v20" stroke="#18b8f2" stroke-width="8" stroke-linecap="round"/>
    <rect x="50" y="68" width="17" height="15" rx="4" fill="#18b8f2"/>
    <rect x="73" y="68" width="17" height="15" rx="4" fill="#2f63d9"/>
    <rect x="96" y="68" width="17" height="15" rx="4" fill="#18b8f2"/>
    <rect x="50" y="91" width="17" height="15" rx="4" fill="#2f63d9"/>
    <rect x="73" y="91" width="17" height="15" rx="4" fill="#18b8f2"/>
    <path d="m96 98 8 8 18-22" fill="none" stroke="#0f9d92" stroke-width="7" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
SVG,
        'document' => <<<'SVG'
<svg viewBox="0 0 160 140" role="img" focusable="false">
    <path d="M45 18h48l25 25v75c0 8-6 14-14 14H45c-8 0-14-6-14-14V32c0-8 6-14 14-14Z" fill="#f8fbff" stroke="#2f63d9" stroke-width="5" stroke-linejoin="round"/>
    <path d="M93 18v27h25" fill="none" stroke="#18b8f2" stroke-width="5" stroke-linejoin="round"/>
    <path d="M50 60h45M50 78h55M50 96h34" stroke="#2f63d9" stroke-width="5" stroke-linecap="round"/>
    <path d="M94 102c10-13 22-20 33-23-5 13-13 25-25 35l-17 5 9-17Z" fill="#0f9d92"/>
    <path d="m88 113 14-14" stroke="#fff" stroke-width="4" stroke-linecap="round"/>
</svg>
SVG,
    ];

    return $icons[$type] ?? $icons['calendar'];
}
}
?>

<div id="pmotos-landing" class="pmotos-page">
    <div class="card pmotos-shell">
        <div class="pmotos-hero">
            <div>
                <h5 class="card-title text-primary mb-3">
                    HOLA, <?= $nombreUsuario; ?>
                    <i class="fa-solid fa-sliders ms-2 text-primary" aria-hidden="true"></i>
                </h5>
                <p class="mb-0">
                    Aqui entras a <strong>Parametros Motos</strong>: administra reglas operativas de motos adjudicadas
                    en modulos separados para que cada configuracion sea clara, auditable y facil de mantener.
                </p>
            </div>
        </div>

        <div class="row gy-4 gx-4 pmotos-grid">
            <?php foreach ($opcionesParametros as $op): ?>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card shadow-none h-100 pmotos-card">
                        <div class="card-body d-flex flex-column">
                            <div class="pmotos-visual mb-4" aria-hidden="true">
                                <?= parametrosMotosVisual((string) ($op['visual'] ?? 'calendar')); ?>
                            </div>
                            <h5 class="text-primary mb-2"><?= htmlspecialchars($op['titulo'], ENT_QUOTES, 'UTF-8'); ?></h5>
                            <p class="text-body flex-grow-1"><?= htmlspecialchars($op['texto'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <a href="<?= htmlspecialchars($op['url'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary w-100 mt-3">
                                <i class="fa-solid <?= htmlspecialchars($op['icon'], ENT_QUOTES, 'UTF-8'); ?> me-1"></i>
                                <?= htmlspecialchars($op['btn'], ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
#pmotos-landing.pmotos-page {
    padding: .25rem;
}
#pmotos-landing .pmotos-shell {
    border: 0;
    border-radius: .75rem;
    padding: 1.35rem;
    overflow: visible;
}
#pmotos-landing .pmotos-hero {
    min-height: 145px;
    display: flex;
    align-items: flex-start;
    padding: .25rem .35rem 1rem;
}
#pmotos-landing .pmotos-hero p {
    max-width: 980px;
    color: #566170;
}
#pmotos-landing .pmotos-grid {
    margin-top: 1.25rem;
}
#pmotos-landing .pmotos-card {
    border: 0;
    border-radius: .65rem;
    background: transparent;
}
#pmotos-landing .pmotos-card .card-body {
    min-height: 340px;
}
#pmotos-landing .pmotos-visual {
    width: 138px;
    height: 138px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    align-self: center;
}
#pmotos-landing .pmotos-visual svg {
    width: 138px;
    height: 138px;
    filter: drop-shadow(0 16px 22px rgba(37, 54, 83, .12));
}
body.dark-mode #pmotos-landing .pmotos-shell {
    background: #17212b;
}
body.dark-mode #pmotos-landing .pmotos-hero p {
    color: #cbd5e1;
}
</style>
