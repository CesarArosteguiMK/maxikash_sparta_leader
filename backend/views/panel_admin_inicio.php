<?php
$panelesVisibles = isset($panelesVisibles) && is_array($panelesVisibles) ? $panelesVisibles : [];
?>
<style>
#panelAdminInicioWrap .panel-admin-icono { color: var(--bs-primary); }
#panelAdminInicioWrap .panel-admin-icono i { color: inherit; }
#panelAdminInicioWrap .card.bg-label-primary .card-body { padding: 1.25rem; }
#panelAdminInicioWrap .h-px-150 { min-height: 120px; }
/* Hero Panel Admin — referencia de mascota compartida con otras landings (mismo tamaño/posición) */
/*
 * No usar overflow-x: hidden en el mismo bloque que contenido que “sale” en Y:
 * en CSS, hidden + visible en los dos ejes hace que visible pase a auto y se recorte
 * la mascota (transform + absolute).
 */
.pai-panel-admin-page,
#panelAdminInicioWrap.pai-panel-admin-page {
    overflow: visible;
}
.pai-panel-admin-page .pai-hero-row {
    overflow: visible;
}
.pai-panel-admin-page > .card {
    overflow: visible;
    position: relative;
}
.pai-hero-block {
    position: relative;
    z-index: 0;
    overflow: visible;
    --pai-mascot-max-w: 280px;
    --pai-mascot-max-h: min(300px, 44vh);
    /* Más negativo = mascota hacia la izquierda (evita tapar iconos de la tercera columna) */
    --pai-mascot-translate-x: -6rem;
    /* Empuje hacia abajo (desktop + móvil vía translateY en media queries) */
    --pai-mascot-translate-y: 3rem;
}
.pai-hero-text .card-body {
    padding-bottom: 1.25rem !important;
    padding-top: 2rem !important;
}
@media (min-width: 768px) {
    .pai-hero-row.pai-hero-row--con-mascota {
        align-items: stretch;
        min-height: 23rem;
        padding-bottom: 5rem;
    }
    .pai-hero-text .card-body {
        padding-top: 2rem !important;
        padding-bottom: 1.5rem !important;
        padding-right: 0.5rem;
    }
}
.pai-hero-mascot-col {
    padding-top: 1rem;
    padding-bottom: 2rem;
}
@media (min-width: 768px) {
    .pai-hero-mascot-col {
        position: relative;
        min-height: 0;
        padding: 0;
        align-self: stretch;
    }
    .pai-hero-text {
        position: relative;
        z-index: 2;
    }
}
.pai-hero-mascot-floating {
    display: block;
    object-fit: contain;
    object-position: bottom center;
    filter: drop-shadow(0 10px 28px rgba(26, 82, 168, 0.12));
}
@media (max-width: 767.98px) {
    .pai-hero-row.pai-hero-row--con-mascota {
        min-height: 15rem;
    }
    .pai-hero-text .card-body {
        text-align: center;
        padding-top: 2rem !important;
        padding-bottom: 1rem !important;
        padding-left: 1rem !important;
        padding-right: 1rem !important;
    }
    .pai-hero-mascot-col {
        align-items: center !important;
        padding-top: 0;
    }
    .pai-hero-mascot-floating {
        margin: 0 auto;
        max-width: min(58vw, 200px);
        max-height: min(32vh, 200px);
        width: auto;
        height: auto;
        object-position: bottom center;
        transform: translateY(var(--pai-mascot-translate-y, 3rem));
    }
}
@media (min-width: 768px) {
    .pai-hero-mascot-floating {
        position: relative;
        right: auto;
        bottom: auto;
        z-index: 1;
        width: auto;
        height: auto;
        max-width: var(--pai-mascot-max-w, 280px);
        max-height: var(--pai-mascot-max-h, 300px);
        margin: 0 0 0 auto;
        object-position: bottom right;
        transform: translate(var(--pai-mascot-translate-x, -6rem), var(--pai-mascot-translate-y, 3rem));
    }
    /* Tarjetas de módulos por encima de la mascota (suelo / mesa del arte) */
    .pai-hero-paneles-row {
        position: relative;
        z-index: 3;
    }
}
body.dark-mode .pai-hero-mascot-floating {
    filter: drop-shadow(0 12px 32px rgba(0, 0, 0, 0.35));
}
/* Próximamente — mismo criterio visual que categorías en Levantar ticket (sabueso_ticket) */
#panelAdminInicioWrap .panel-admin-modulo-card {
    position: relative;
}
#panelAdminInicioWrap .panel-admin-modulo-btn {
    width: min(100%, 17rem);
    min-height: 2.5rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    white-space: nowrap;
    line-height: 1.2;
    padding-left: 1rem;
    padding-right: 1rem;
}
#panelAdminInicioWrap .panel-admin-modulo-card--proximamente {
    overflow: hidden;
    opacity: 0.62;
    pointer-events: none;
}
#panelAdminInicioWrap .panel-admin-modulo-lazo {
    position: absolute;
    top: 1.35rem;
    right: -2.35rem;
    z-index: 2;
    transform: translateY(0.22rem) rotate(40deg);
    transform-origin: center center;
    background: var(--bs-warning);
    color: var(--bs-warning-text-emphasis);
    font-size: 0.62rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    padding: 0.38rem 2.55rem 0.32rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
    line-height: 1.35;
    white-space: nowrap;
    pointer-events: none;
    user-select: none;
}
</style>
<div class="card mb-4 pai-panel-admin-page" id="panelAdminInicioWrap">
    <div class="card">
        <div class="row g-0 align-items-center overflow-visible pai-hero-row pai-hero-row--con-mascota pai-hero-block">
            <div class="col-12 col-md-8 pai-hero-text">
                <div class="card-body">
                    <h5 class="card-title text-primary mb-3">HOLA, <?= isset($_SESSION['usuario_nombre']) ? htmlspecialchars(strtoupper((string) $_SESSION['usuario_nombre']), ENT_QUOTES, 'UTF-8') : 'USUARIO'; ?> <i class="fa-solid fa-shield-halved ms-2 text-primary" aria-hidden="true"></i></h5>
                    <p class="mb-6 mb-md-0">
                        Accede a los paneles de administración por módulo. Elige el panel que necesites para gestionar tickets, solicitudes y más.
                    </p>
                </div>
            </div>
            <div class="col-12 col-md-4 d-flex flex-column justify-content-end align-items-center align-items-md-end pai-hero-mascot-col">
                <img src="/assets/img/illustrations/panel-admin-mascota.png"
                     class="pai-hero-mascot-floating img-fluid"
                     width="400"
                     height="400"
                     alt="Panel Admin — ilustración">
            </div>
            <div class="row gy-6 mb-6 gx-0 pai-hero-paneles-row">
                <?php
                /** Solo estos paneles están operativos; el resto muestra lazo «Próximamente» (como en Levantar ticket). */
                $panelesActivosAhora = ['sabueso_paneladmin', 'sabueso_panel_validaciones', 'sabueso_panel_viaticos', 'sabueso_panel_aplicacionespago', 'sabueso_panel_creditoproblematico', 'sabueso_panel_aclaracioncredito'];
                $descripciones = [
                    'sabueso_paneladmin' => 'Todos los tickets Sabueso, asignación, dictámenes y seguimiento.',
                    'sabueso_panel_validaciones' => 'Tickets de validación de domicilio y validaciones. Ver y gestionar solicitudes.',
                    'sabueso_panel_plantilla' => 'Tickets de plantilla. Ver y gestionar solicitudes.',
                    'sabueso_panel_atencioncliente' => 'Tickets de atención al cliente. Ver y gestionar solicitudes.',
                    'sabueso_panel_viaticos' => 'Tickets de ausencias. Ver solicitudes levantadas desde la app y su detalle.',
                    'sabueso_panel_aplicacionespago' => 'Tickets de reclamos de bonos. Ver solicitudes levantadas desde la app y su detalle.',
                    'sabueso_panel_creditoproblematico' => 'Tickets de pagos no identificados. Ver solicitudes levantadas desde la app y su detalle.',
                    'sabueso_panel_aclaracioncredito' => 'Tickets de incidencias en asignacion de cartera. Ver solicitudes levantadas desde la app y su detalle.',
                    'sabueso_panelsolicitudbaja' => 'Solicitudes de baja recibidas y su estado.',
                ];
                $textosBoton = [
                    'sabueso_paneladmin' => 'VER PANEL SABUESO',
                    'sabueso_panel_validaciones' => 'VER VALIDACIONES',
                    'sabueso_panel_viaticos' => 'VER AUSENCIAS',
                    'sabueso_panel_aplicacionespago' => 'VER RECLAMOS DE BONOS',
                    'sabueso_panel_creditoproblematico' => 'VER PAGOS NO IDENTIFICADOS',
                    'sabueso_panel_aclaracioncredito' => 'VER INCIDENCIAS CARTERA',
                    'sabueso_panelsolicitudbaja' => 'VER PANEL SOLICITUD DE BAJA',
                ];
                $ordenPanelesInicio = [
                    'sabueso_paneladmin' => 10,
                    'sabueso_panel_viaticos' => 20,
                    'sabueso_panel_aplicacionespago' => 30,
                    'sabueso_panel_creditoproblematico' => 40,
                    'sabueso_panel_aclaracioncredito' => 50,
                    'sabueso_panel_validaciones' => 90,
                ];
                uksort($panelesVisibles, static function ($a, $b) use ($ordenPanelesInicio) {
                    return ($ordenPanelesInicio[$a] ?? 80) <=> ($ordenPanelesInicio[$b] ?? 80);
                });
                foreach ($panelesVisibles as $clave => $info):
                    if ($clave === 'sabueso_panelsolicitudbaja') {
                        continue;
                    }
                    $label = $info['label'] ?? $clave;
                    $icon = $info['icon'] ?? 'fa-solid fa-table-cells';
                    $url = $info['url'] ?? '#';
                    $desc = $descripciones[$clave] ?? 'Panel de administración.';
                    $textoBoton = $textosBoton[$clave] ?? ('VER ' . strtoupper((string) $label));
                    $panelOperativo = in_array($clave, $panelesActivosAhora, true);
                    $clsModulo = 'card shadow-none bg-label-primary h-100 panel-admin-modulo-card'
                        . ($panelOperativo ? '' : ' panel-admin-modulo-card--proximamente');
                ?>
                <div class="col-md-6 col-lg-3">
                    <div class="<?= htmlspecialchars($clsModulo, ENT_QUOTES, 'UTF-8'); ?>">
                        <?php if (!$panelOperativo): ?>
                        <span class="panel-admin-modulo-lazo">Próximamente</span>
                        <?php endif; ?>
                        <div class="card-body d-flex justify-content-between flex-wrap-reverse">
                            <div class="mb-0 w-100 app-academy-sm-60 d-flex flex-column justify-content-between text-center text-sm-start">
                                <div class="card-title">
                                    <h5 class="text-primary mb-2"><?= htmlspecialchars($label); ?></h5>
                                    <p class="text-body w-sm-80 app-academy-xl-100"><?= htmlspecialchars($desc); ?></p>
                                </div>
                                <div class="mb-0">
                                    <?php if ($panelOperativo): ?>
                                    <a href="<?= htmlspecialchars($url); ?>" class="btn btn-primary panel-admin-modulo-btn"><?= htmlspecialchars($textoBoton, ENT_QUOTES, 'UTF-8'); ?></a>
                                    <?php else: ?>
                                    <span class="btn btn-primary disabled panel-admin-modulo-btn" aria-disabled="true"><?= htmlspecialchars($textoBoton, ENT_QUOTES, 'UTF-8'); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="w-100 app-academy-sm-40 d-flex justify-content-center justify-content-sm-end h-px-150 mb-4 mb-sm-0">
                                <div class="rounded-3 d-flex align-items-center justify-content-center h-100 panel-admin-icono" style="min-height: 120px; min-width: 100px;">
                                    <i class="<?= htmlspecialchars($icon); ?> fa-3x scaleX-n1-rtl" aria-hidden="true"></i>
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
