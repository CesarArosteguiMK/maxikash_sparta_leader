<?php
$panelesVisibles = isset($panelesVisibles) && is_array($panelesVisibles) ? $panelesVisibles : [];
?>
<style>
#panelAdminInicioWrap .panel-admin-icono { color: var(--bs-primary); }
#panelAdminInicioWrap .panel-admin-icono i { color: inherit; }
#panelAdminInicioWrap .card.bg-label-primary .card-body { padding: 1.25rem; }
#panelAdminInicioWrap .h-px-150 { min-height: 120px; }
</style>
<div class="card mb-4" id="panelAdminInicioWrap">
    <div class="card">
        <div class="row g-0 align-items-center">
            <div class="col-12 col-md-8">
                <div class="card-body">
                    <h5 class="card-title text-primary mb-3">HOLA, <?= htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario'); ?></h5>
                    <p class="mb-6">
                        Accede a los paneles de administración por módulo. Elige el panel que necesites para gestionar tickets, solicitudes y más.
                    </p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card-body ps-md-2 pe-5 text-end">
                    <img src="https://demos.themeselection.com/sneat-bootstrap-html-admin-template/assets/img/illustrations/man-with-laptop.png"
                         class="img-fluid scaleX-n1-rtl"
                         alt="Panel Admin">
                </div>
            </div>
            <div class="row gy-6 mb-6">
                <?php
                $descripciones = [
                    'sabueso_paneladmin' => 'Todos los tickets Sabueso, asignación, dictámenes y seguimiento.',
                    'sabueso_panel_validaciones' => 'Tickets de validación de domicilio y validaciones. Ver y gestionar solicitudes.',
                    'sabueso_panel_plantilla' => 'Tickets de plantilla. Ver y gestionar solicitudes.',
                    'sabueso_panel_atencioncliente' => 'Tickets de atención al cliente. Ver y gestionar solicitudes.',
                    'sabueso_panel_viaticos' => 'Tickets de viáticos. Ver y gestionar solicitudes de viáticos.',
                    'sabueso_panel_aplicacionespago' => 'Tickets de aplicaciones de pago. Ver y gestionar solicitudes.',
                    'sabueso_panel_creditoproblematico' => 'Tickets de crédito problemático. Ver y gestionar solicitudes.',
                    'sabueso_panel_aclaracioncredito' => 'Tickets de aclaración de crédito. Ver y gestionar solicitudes.',
                    'sabueso_panelsolicitudbaja' => 'Solicitudes de baja recibidas y su estado.',
                ];
                foreach ($panelesVisibles as $clave => $info):
                    $label = $info['label'] ?? $clave;
                    $icon = $info['icon'] ?? 'fa-solid fa-table-cells';
                    $url = $info['url'] ?? '#';
                    $desc = $descripciones[$clave] ?? 'Panel de administración.';
                ?>
                <div class="col-lg-4">
                    <div class="card shadow-none bg-label-primary h-100">
                        <div class="card-body d-flex justify-content-between flex-wrap-reverse">
                            <div class="mb-0 w-100 app-academy-sm-60 d-flex flex-column justify-content-between text-center text-sm-start">
                                <div class="card-title">
                                    <h5 class="text-primary mb-2"><?= htmlspecialchars($label); ?></h5>
                                    <p class="text-body w-sm-80 app-academy-xl-100"><?= htmlspecialchars($desc); ?></p>
                                </div>
                                <div class="mb-0">
                                    <a href="<?= htmlspecialchars($url); ?>" class="btn btn-primary">VER <?= strtoupper(htmlspecialchars($label)); ?></a>
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
                <div class="col-lg-4">
                    <div class="card shadow-none bg-label-primary h-100">
                        <div class="card-body d-flex justify-content-between flex-wrap-reverse">
                            <div class="mb-0 w-100 app-academy-sm-60 d-flex flex-column justify-content-between text-center text-sm-start">
                                <div class="card-title">
                                    <h5 class="text-primary mb-2">Más paneles</h5>
                                    <p class="text-body app-academy-sm-60 app-academy-xl-100">Nuevos paneles de administración se habilitarán aquí.</p>
                                </div>
                                <div class="mb-0"><span class="btn btn-sm btn-primary" disabled>PRÓXIMAMENTE</span></div>
                            </div>
                            <div class="w-100 app-academy-sm-40 d-flex justify-content-center justify-content-sm-end h-px-150 mb-4 mb-sm-0">
                                <div class="rounded-3 d-flex align-items-center justify-content-center h-100 text-secondary" style="min-height: 120px; min-width: 100px;">
                                    <i class="fa-solid fa-chart-column fa-3x scaleX-n1-rtl" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
