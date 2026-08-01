<?php
$nombreUsuario = isset($_SESSION['usuario_nombre'])
    ? htmlspecialchars(strtoupper((string) $_SESSION['usuario_nombre']), ENT_QUOTES, 'UTF-8')
    : 'USUARIO';

$reportes = [
    [
        'titulo' => 'Histórico de convenios',
        'texto' => 'Consulta todos los convenios generados con monto original, oferta, descuento, monto pactado y acceso al detalle individual.',
        'url' => '/convenios/reporteHistorico',
        'btn' => 'Abrir histórico',
        'icon' => 'fa-table-list',
    ],
    [
        'titulo' => 'Reporte individual',
        'texto' => 'Revisa un convenio por ID de convenio o ID de crédito, incluyendo amortización, seguimiento de cierre y bitácora.',
        'url' => '/convenios/reporteIndividual',
        'btn' => 'Abrir individual',
        'icon' => 'fa-timeline',
    ],
    [
        'titulo' => 'Estadísticas',
        'texto' => 'Indicadores operativos de convenios, cierre de crédito y recuperación por período.',
        'url' => '/convenios/estadisticas',
        'btn' => 'Ver estadísticas',
        'icon' => 'fa-chart-simple',
    ],
];
?>

<style>
    .conv-report-shell { color: #334155; }
    .conv-report-head {
        border: 1px solid #e2e8f0;
        border-left: 5px solid #6d4ac7;
        border-radius: .55rem;
        background: #fff;
        box-shadow: 0 .45rem 1.2rem rgba(15, 23, 42, .05);
    }
    .conv-report-card {
        border: 1px solid #e2e8f0;
        border-radius: .55rem;
        min-height: 245px;
        box-shadow: 0 .35rem 1rem rgba(15, 23, 42, .04);
    }
    .conv-report-icon {
        width: 64px;
        height: 64px;
        border-radius: .75rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #6d4ac7;
        background: #f4efff;
        font-size: 1.75rem;
    }
    .conv-report-link { background: #26344f; border-color: #26344f; }
    .conv-report-link:hover { background: #1d2940; border-color: #1d2940; }
</style>

<div class="container-fluid py-3 conv-report-shell">
    <div class="conv-report-head p-4 mb-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h4 class="mb-1 fw-bold"><i class="fa-solid fa-building-columns me-2 text-primary"></i>Reportería de Convenios</h4>
                <p class="mb-0 text-muted">Hola, <?= $nombreUsuario; ?>. Aquí se concentran los reportes históricos e individuales del sistema de convenios.</p>
            </div>
            <a href="/convenios/consulta" class="btn btn-outline-primary">
                <i class="fa-solid fa-magnifying-glass me-1"></i>Ir a consulta
            </a>
        </div>
    </div>

    <div class="row g-3">
        <?php foreach ($reportes as $rep): ?>
            <div class="col-12 col-md-6 col-xl-4">
                <div class="conv-report-card h-100 bg-white">
                    <div class="p-4 h-100 d-flex flex-column">
                        <div class="mb-3">
                            <span class="conv-report-icon"><i class="fa-solid <?= htmlspecialchars($rep['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i></span>
                        </div>
                        <h5 class="fw-bold mb-2"><?= htmlspecialchars($rep['titulo'], ENT_QUOTES, 'UTF-8'); ?></h5>
                        <p class="text-muted flex-grow-1"><?= htmlspecialchars($rep['texto'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <a href="<?= htmlspecialchars($rep['url'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary conv-report-link w-100">
                            <i class="fa-solid fa-arrow-right-to-bracket me-1"></i><?= htmlspecialchars($rep['btn'], ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
