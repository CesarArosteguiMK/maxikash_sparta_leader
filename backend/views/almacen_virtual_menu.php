<?php
$nombreUsuario = isset($_SESSION['usuario_nombre'])
    ? htmlspecialchars(strtoupper((string) $_SESSION['usuario_nombre']), ENT_QUOTES, 'UTF-8')
    : 'USUARIO';

$modulosAlmacenVirtual = [
    [
        'titulo' => 'Inventario',
        'texto' => 'Ingreso automatico desde Tracking, ficha tecnica por unidad, celula, ubicacion y estatus.',
        'url' => '/MotosAdjudicadas/inventario',
        'btn' => 'Abrir inventario',
        'icon' => 'fa-boxes-stacked',
        'color' => 'primary',
        'estado' => 'Activo',
        'activo' => true,
    ],
    /*
     * Flujo migrado a MaxiApp-Sparta-MotoTrack. Se conserva la vista web anterior
     * como respaldo, pero Sparta ya no debe operar evidencias/codigo.
    [
        'titulo' => 'Evidencias y codigo',
        'texto' => 'Fotografias, serial VIN de 17 digitos y codigo unico antes de recepcion.',
        'url' => '/MotosAdjudicadas/evidenciasCodigo',
        'btn' => 'Abrir evidencias',
        'icon' => 'fa-camera',
        'color' => 'warning',
        'estado' => 'Activo',
        'activo' => true,
    ],
     */
    [
        'titulo' => 'Recepcion almacen',
        'texto' => 'Revision de evidencias moviles, codigo de ingreso, documentos y pase a mecanica.',
        'url' => '/MotosAdjudicadas/recepcionAlmacen',
        'btn' => 'Abrir recepcion',
        'icon' => 'fa-clipboard-check',
        'color' => 'success',
        'estado' => 'Activo',
        'activo' => true,
    ],
    [
        'titulo' => 'Revision mecanica',
        'texto' => 'Diagnostico mecanico, electrico y estetico con dictamen final de la unidad.',
        'url' => '/MotosAdjudicadas/revisionMecanica',
        'btn' => 'Abrir revision',
        'icon' => 'fa-screwdriver-wrench',
        'color' => 'danger',
        'estado' => 'Activo',
        'activo' => true,
    ],
    [
        'titulo' => 'Kanban operativo',
        'texto' => 'Bandeja central por estatus, agencia, tipo de unidad, bitacora y envio a piso de venta.',
        'url' => '/MotosAdjudicadas/kanbanOperativo',
        'btn' => 'Abrir Kanban',
        'icon' => 'fa-table-columns',
        'color' => 'secondary',
        'estado' => 'Activo',
        'activo' => true,
    ],
    [
        'titulo' => 'Piso de venta',
        'texto' => 'Envio de unidades reparadas a Pension a Max o Amigo Efectivo.',
        'url' => '/MotosAdjudicadas/pisoVenta',
        'btn' => 'Abrir piso',
        'icon' => 'fa-store',
        'color' => 'success',
        'estado' => 'Activo',
        'activo' => true,
    ],
    [
        'titulo' => 'Traspasos',
        'texto' => 'Ordenes entre agencias, transportista, evidencia origen y VoBo destino.',
        'url' => '/MotosAdjudicadas/traspasos',
        'btn' => 'Abrir traspasos',
        'icon' => 'fa-right-left',
        'color' => 'dark',
        'estado' => 'Activo',
        'activo' => true,
    ],
];
?>

<div id="av-menu" class="container-fluid py-3 px-3 px-md-4">
    <div class="av-menu-head mb-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-center">
            <div class="d-flex gap-3 align-items-start">
                <span class="av-menu-head-icon">
                    <i class="fa-solid fa-warehouse" aria-hidden="true"></i>
                </span>
                <div>
                    <div class="av-eyebrow">Motos Adjudicadas</div>
                    <h4 class="mb-1">Almacen Virtual</h4>
                    <div class="text-muted small">
                        HOLA, <?= $nombreUsuario; ?>. Entrada operativa para inventario, recepcion, revision administrativa de mecanica, kanban, piso de venta y traspasos.
                    </div>
                </div>
            </div>
            <a href="/MotosAdjudicadas/inventario" class="btn btn-primary av-main-action">
                <i class="fa-solid fa-boxes-stacked me-1"></i>
                Inventario
            </a>
        </div>
    </div>

    <div class="av-roadmap-strip mb-4">
        <div>
            <div class="av-roadmap-title">Roadmap del proyecto</div>
            <div class="text-muted small">Base alineada a requerimientos: transportista, evidencias moviles, recepcion, revision mecanica administrativa, kanban, venta y traspaso.</div>
        </div>
        <span class="badge bg-label-primary text-primary">Modulo 139</span>
    </div>

    <div class="row g-3">
        <?php foreach ($modulosAlmacenVirtual as $modulo): ?>
            <?php
            $color = htmlspecialchars((string) $modulo['color'], ENT_QUOTES, 'UTF-8');
            $activo = !empty($modulo['activo']);
            ?>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card h-100 shadow-none av-module-card<?= $activo ? ' is-active' : ' is-roadmap'; ?>">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                            <span class="av-module-icon text-<?= $color; ?> bg-label-<?= $color; ?>">
                                <i class="fa-solid <?= htmlspecialchars((string) $modulo['icon'], ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true"></i>
                            </span>
                            <span class="badge bg-label-<?= $color; ?> text-<?= $color; ?>">
                                <?= htmlspecialchars((string) $modulo['estado'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </div>
                        <h5 class="mb-2"><?= htmlspecialchars((string) $modulo['titulo'], ENT_QUOTES, 'UTF-8'); ?></h5>
                        <p class="text-muted small flex-grow-1 mb-4"><?= htmlspecialchars((string) $modulo['texto'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php if ($activo): ?>
                            <a href="<?= htmlspecialchars((string) $modulo['url'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-<?= $color; ?> w-100">
                                <i class="fa-solid <?= htmlspecialchars((string) $modulo['icon'], ENT_QUOTES, 'UTF-8'); ?> me-1"></i>
                                <?= htmlspecialchars((string) $modulo['btn'], ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        <?php else: ?>
                            <button type="button" class="btn btn-label-secondary w-100" disabled>
                                <i class="fa-solid fa-clock me-1"></i>
                                <?= htmlspecialchars((string) $modulo['btn'], ENT_QUOTES, 'UTF-8'); ?>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
#av-menu .av-menu-head {
    border: 1px solid #dbe4ef;
    background: #fff;
    border-radius: .5rem;
    padding: 1rem 1.15rem;
}
#av-menu .av-menu-head-icon {
    width: 3rem;
    height: 3rem;
    border-radius: .5rem;
    background: #e0f2fe;
    color: #0369a1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1.2rem;
}
#av-menu .av-eyebrow {
    color: #64748b;
    font-size: .72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0;
}
#av-menu .av-main-action {
    min-width: 11rem;
}
#av-menu .av-roadmap-strip {
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    border-radius: .5rem;
    padding: .85rem 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}
#av-menu .av-roadmap-title {
    color: #1e293b;
    font-size: .9rem;
    font-weight: 800;
}
#av-menu .av-module-card {
    border: 1px solid #e2e8f0;
    border-radius: .5rem;
    transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
}
#av-menu .av-module-card.is-active {
    border-color: #93c5fd;
}
#av-menu .av-module-card.is-roadmap {
    background: #fff;
}
#av-menu .av-module-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 .45rem 1rem rgba(15, 23, 42, .08) !important;
}
#av-menu .av-module-icon {
    width: 2.65rem;
    height: 2.65rem;
    border-radius: .5rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1.05rem;
}
body.dark-mode #av-menu {
    color: #e2e8f0;
}
body.dark-mode #av-menu .av-menu-head,
body.dark-mode #av-menu .av-roadmap-strip {
    background: #172033;
    border-color: #334155;
    box-shadow: 0 .35rem 1rem rgba(2, 6, 23, .18);
}
body.dark-mode #av-menu .av-menu-head-icon {
    background: #0c4a6e;
    color: #7dd3fc;
}
body.dark-mode #av-menu .av-eyebrow {
    color: #7dd3fc;
}
body.dark-mode #av-menu .av-menu-head h4,
body.dark-mode #av-menu .av-roadmap-title,
body.dark-mode #av-menu .av-module-card h5 {
    color: #f8fafc;
}
body.dark-mode #av-menu .text-muted,
body.dark-mode #av-menu .av-module-card .text-muted {
    color: #b6c2d2 !important;
}
body.dark-mode #av-menu .av-roadmap-strip .badge {
    background: #172554 !important;
    color: #93c5fd !important;
    border: 1px solid #1d4ed8;
}
body.dark-mode #av-menu .av-module-card,
body.dark-mode #av-menu .av-module-card.is-roadmap {
    background: #1e293b;
    border-color: #3b4a60;
}
body.dark-mode #av-menu .av-module-card.is-active {
    border-color: #496582;
}
body.dark-mode #av-menu .av-module-card:hover {
    border-color: #60a5fa;
    box-shadow: 0 .55rem 1.25rem rgba(2, 6, 23, .35) !important;
}
body.dark-mode #av-menu .av-module-card .btn-secondary,
body.dark-mode #av-menu .av-module-card .btn-dark,
body.dark-mode #av-menu .av-module-card .btn-label-secondary {
    background: #334155;
    border-color: #475569;
    color: #f8fafc;
}
body.dark-mode #av-menu .av-module-card .btn-secondary:hover,
body.dark-mode #av-menu .av-module-card .btn-dark:hover {
    background: #475569;
    border-color: #64748b;
}
@media (max-width: 575.98px) {
    #av-menu .av-roadmap-strip {
        align-items: flex-start;
        flex-direction: column;
    }
    #av-menu .av-main-action {
        width: 100%;
    }
}
</style>
