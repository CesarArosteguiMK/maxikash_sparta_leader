<?php
$nombreUsuario = isset($_SESSION['usuario_nombre'])
    ? htmlspecialchars(strtoupper((string) $_SESSION['usuario_nombre']), ENT_QUOTES, 'UTF-8')
    : 'USUARIO';

$reportes = [
    [
        'titulo' => 'Seguimiento de motos adjudicadas',
        'texto' => 'Consulta operaciones, ubicacion, unidad, estatus y alertas de calidad de datos para seguimiento operativo.',
        'url' => '/MotosAdjudicadas/reporteSeguimiento',
        'btn' => 'Abrir reporte',
        'icon' => 'fa-table-list',
    ],
    [
        'titulo' => 'Timeline por credito',
        'texto' => 'Consulta el recorrido completo del credito: asignacion, evidencias, recuperacion, cartera, tracking y recepcion.',
        'url' => '/MotosAdjudicadas/timelineCredito',
        'btn' => 'Abrir timeline',
        'icon' => 'fa-timeline',
    ],
];
?>

<style>
    .madj-reporteria-hero {
        border-radius: .85rem;
        background: linear-gradient(120deg, #24334f 0%, #2f63d9 100%);
        color: #fff;
        box-shadow: 0 .65rem 1.6rem rgba(36, 51, 79, .18);
    }
    .madj-reporteria-card {
        border: 1px solid var(--bs-border-color);
        border-radius: .75rem;
        box-shadow: 0 .45rem 1.1rem rgba(15, 23, 42, .05);
        min-height: 260px;
    }
    .madj-reporteria-icon {
        width: 92px;
        height: 92px;
        border-radius: 1.25rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #0f9d92;
        background: linear-gradient(145deg, rgba(15,157,146,.14), rgba(47,99,217,.1));
        font-size: 2.35rem;
    }
</style>

<div class="container-fluid py-3">
    <div class="madj-reporteria-hero p-4 mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h3 class="mb-1 text-white"><i class="fa-solid fa-file-chart-column me-2"></i>Reporteria de Motos Adjudicadas</h3>
                <p class="mb-0" style="color:rgba(255,255,255,.76)">Hola, <?= $nombreUsuario; ?>. Aqui se concentraran los reportes operativos y ejecutivos del flujo de adjudicadas.</p>
            </div>
            <a href="/MotosAdjudicadas/dashboard" class="btn btn-light">
                <i class="fa-solid fa-gauge-high me-1"></i>Ver dashboard
            </a>
        </div>
    </div>

    <div class="row g-3">
        <?php foreach ($reportes as $rep): ?>
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card madj-reporteria-card h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="mb-3">
                            <span class="madj-reporteria-icon"><i class="fa-solid <?= htmlspecialchars($rep['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i></span>
                        </div>
                        <h5 class="fw-bold mb-2"><?= htmlspecialchars($rep['titulo'], ENT_QUOTES, 'UTF-8'); ?></h5>
                        <p class="text-muted flex-grow-1"><?= htmlspecialchars($rep['texto'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <a href="<?= htmlspecialchars($rep['url'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary w-100">
                            <i class="fa-solid fa-arrow-right-to-bracket me-1"></i><?= htmlspecialchars($rep['btn'], ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
