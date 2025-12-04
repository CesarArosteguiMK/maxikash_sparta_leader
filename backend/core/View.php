<?php

$titulo = $titulo ?? "Inicio | "  . CONFIGURACION['EMPRESA'];
$usuario = $_SESSION['nombre'] ?? 'Usuario';

function getMenu()
{
    $menuItems = [
        'Créditos' => [
            'icono' => 'fa-solid fa-usd',
            'subItems' => [
                [
                    'label' => 'Estados de Cuenta',
                    'url' => '/EstadoCuenta/Consulta',
                    'permisos' => [3, 26, 27, 28, 90, 30, 50]
                ],
                [
                    'label' => 'Documentación',
                    'url' => '/EstadoCuenta/Documentacion',
                    'permisos' => [3, 26, 27, 28, 90, 30, 50]
                ]
            ]
        ],
        'Gestiones SKY' => [
            'icono' => 'fa-solid fa-screwdriver-wrench',
            'subItems' => [
                [
                    'label' => 'Historico Gestiones',
                    'url' => '/gestiones/seguimiento',
                    'permisos' => [3, 26, 90]
                ]
            ]
        ],
        'Capital Humano' => [
            'icono' => 'fa-solid fa-users',
            'subItems' => [
                [
                    'label' => 'Gestión',
                    'url' => '/CapHum/GestionCapHum',
                    'permisos' => [3, 26, 27, 28]
                ],
                [
                    'label' => 'Organigrama',
                    'url' => '/CapHum/Organigrama',
                    'permisos' => [3, 26, 27, 28]
                ]
            ]
        ],
        'Reportería' => [
            'icono' => 'fa-solid fa-file',
            'subItems' => [
                [
                    'label' => 'Resumen Call Center',
                    'url' => 'app-viaticos-dashboard.html',
                    'permisos' => [3, 26, 90]
                ],
                [
                    'label' => 'Layout Legacy',
                    'url' => 'app-viaticos-dashboard.html',
                    'permisos' => [3, 26, 90]
                ],
                [
                    'label' => 'Bonos Cobranza',
                    'url' => 'app-viaticos-dashboard.html',
                    'permisos' => [3, 26, 90]
                ]
            ]
        ],
        'Configuración' => [
            'icono' => 'fa-solid fa-cog',
            'subItems' => [
                [
                    'label' => 'Departamentos',
                    'url' => '/departamentos/consulta/',
                    'permisos' => [3, 26]
                ],
                [
                    'label' => 'Puestos',
                    'url' => '/puestos/consulta/',
                    'permisos' => [3, 26]
                ],
                [
                    'label' => 'Perfiles',
                    'url' => '/sucursales/existentes/',
                    'permisos' => [3, 26]
                ]
            ]
        ]
    ];
    $menu = '';

    $menu = '';
    foreach ($menuItems as $key => $item) {
        $submenu = '';
        foreach ($item['subItems'] as $subItem) {
            if (in_array($_SESSION['perfil_id'], $subItem['permisos'])) {
                if ($_SESSION['perfil_id'] != 25 && isset($subItem['soloJefes']) && $subItem['soloJefes'] === true) {
                    if (!isset($_SESSION['es_jefe']) || $_SESSION['es_jefe'] != 1) continue;
                }
                $activo = strtolower($subItem['url']) == strtolower($_SERVER['REQUEST_URI']) ? 'active' : '';
                $submenu .= <<<HTML
                    <li class="menu-item $activo">
                        <a href="{$subItem['url']}" class="menu-link">
                            <div>{$subItem['label']}</div>
                        </a>
                    </li>
                HTML;
            }
        }

        if (empty($submenu)) continue;
        $abierto = strpos($submenu, 'active') !== false ? 'active open' : '';

        $menu .= <<<HTML
            <li class="menu-item $abierto">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="{$item['icono']}">&nbsp;</i>
                    <div>$key</div>
                </a>
                <ul class="menu-sub">
                    $submenu
                </ul>
            </li>
        HTML;
    };

    return $menu;
}
?>

<!doctype html>

<html
    lang="es"
    class="light-style layout-navbar-fixed layout-menu-fixed layout-compact"
    dir="ltr"
    data-theme="theme-default"
    data-assets-path="/assets/"
    data-template="vertical-menu-template"
    data-style="light">

<head>
    <meta charset="utf-8" />
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="description" content="" />

    <title><?= $titulo; ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/assets/img/logo_ico.svg" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="/assets/vendor/fonts/fontawesome.css" />
    <!-- <link rel="stylesheet" href="/assets/vendor/fonts/flag-icons.css" /> -->

    <!-- Preload resources -->
    <link rel="preload" href="/assets/img/wait.svg" as="image">

    <!-- Core CSS -->
    <link rel="stylesheet" href="/assets/vendor/css/core.css" />
    <link rel="stylesheet" href="/assets/css/demo.css" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="/assets/vendor/libs/@form-validation/form-validation.css">
    <link rel="stylesheet" href="/assets/vendor/libs/animate-css/animate.css">
    <link rel="stylesheet" href="/assets/vendor/libs/animate-on-scroll/animate-on-scroll.css">
    <link rel="stylesheet" href="/assets/vendor/libs/apex-charts/apex-charts.css">
    <link rel="stylesheet" href="/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.css">
    <link rel="stylesheet" href="/assets/vendor/libs/bootstrap-select/bootstrap-select.css">
    <link rel="stylesheet" href="/assets/vendor/libs/bs-stepper/bs-stepper.css">
    <link rel="stylesheet" href="/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css">
    <link rel="stylesheet" href="/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css">
    <link rel="stylesheet" href="/assets/vendor/libs/datatables-fixedcolumns-bs5/fixedcolumns.bootstrap5.css">
    <link rel="stylesheet" href="/assets/vendor/libs/datatables-fixedheader-bs5/fixedheader.bootstrap5.css">
    <link rel="stylesheet" href="/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css">
    <link rel="stylesheet" href="/assets/vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.css">
    <link rel="stylesheet" href="/assets/vendor/libs/datatables-select-bs5/select.bootstrap5.css">
    <link rel="stylesheet" href="/assets/vendor/libs/dropzone/dropzone.css">
    <link rel="stylesheet" href="/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.css">
    <link rel="stylesheet" href="/assets/vendor/libs/flatpickr/flatpickr.css">
    <link rel="stylesheet" href="/assets/vendor/libs/fullcalendar/fullcalendar.css">
    <link rel="stylesheet" href="/assets/vendor/libs/jkanban/jkanban.css">
    <link rel="stylesheet" href="/assets/vendor/libs/jquery-timepicker/jquery-timepicker.css">
    <link rel="stylesheet" href="/assets/vendor/libs/jstree/jstree.css">
    <!-- <link rel="stylesheet" href="/assets/vendor/libs/leaflet/leaflet.css"> -->
    <!-- <link rel="stylesheet" href="/assets/vendor/libs/mapbox-gl/mapbox-gl.css"> -->
    <link rel="stylesheet" href="/assets/vendor/libs/nouislider/nouislider.css">
    <link rel="stylesheet" href="/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css">
    <!-- <link rel="stylesheet" href="/assets/vendor/libs/pickr/pickr-themes.css"> -->
    <!-- <link rel="stylesheet" href="/assets/vendor/libs/plyr/plyr.css"> -->
    <!-- <link rel="stylesheet" href="/assets/vendor/libs/quill/editor.css"> -->
    <!-- <link rel="stylesheet" href="/assets/vendor/libs/quill/katex.css"> -->
    <!-- <link rel="stylesheet" href="/assets/vendor/libs/quill/typography.css"> -->
    <link rel="stylesheet" href="/assets/vendor/libs/select2/select2.css">
    <link rel="stylesheet" href="/assets/vendor/libs/shepherd/shepherd.css">
    <link rel="stylesheet" href="/assets/vendor/libs/spinkit/spinkit.css">
    <link rel="stylesheet" href="/assets/vendor/libs/sweetalert2/sweetalert2.css">
    <link rel="stylesheet" href="/assets/vendor/libs/swiper/swiper.css">
    <link rel="stylesheet" href="/assets/vendor/libs/tagify/tagify.css">
    <link rel="stylesheet" href="/assets/vendor/libs/typeahead-js/typeahead.css">

    <!-- Page CSS -->
    <?= $css ?? ''; ?>

    <!-- Helpers -->
    <script src="/assets/vendor/js/helpers.js"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
    <!-- <script src="/assets/vendor/js/template-customizer.js"></script> -->

    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="/assets/js/config.js"></script>
</head>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">

            <!-- Menu -->
            <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
                <div class="app-brand demo">
                    <a href="/Inicio" class="app-brand-link w-100">
                        <span class="app-brand-logo demo w-100">

                        </span>
                        <span class="app-brand-text demo menu-text fw-bold ms-2">
                            <img src="https://__SPARTA_SECRET_REDACTED__.mx/cdn/shop/files/Logotipo-Maxikash-Outline.png?v=1749328460" alt="Logo de la empresa" class="w-100" />
                        </span>
                    </a>

                    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
                        <i class="fa-solid fa-chevron-left d-flex align-items-center justify-content-center"></i>
                    </a>
                </div>
                <hr class="app-brand-text demo menu-text fw-bold ms-2">
                <div class="menu-inner-shadow"></div>

                <ul class="menu-inner py-1">
                    <?= getMenu(); ?>
                </ul>
            </aside>
            <!-- / Menu -->

            <!-- Layout page -->
            <div class="layout-page">

                <!-- Navbar -->
                <nav
                    class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
                    id="layout-navbar">
                    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 d-xl-none">
                        <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
                            <i class="fa-solid fa-bars fa-xl"></i>
                        </a>
                    </div>

                    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
                        <ul class="navbar-nav flex-row align-items-center ms-auto">
                            <!-- User Panel -->
                            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                                <a
                                    class="nav-link dropdown-toggle hide-arrow p-0"
                                    href="javascript:void(0);"
                                    data-bs-toggle="dropdown">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="avatar avatar-online">
                                                <img src="<?= $_SESSION['foto_perfil']; ?>" alt class="w-px-40 h-px-40 rounded-circle object-fit-cover" />
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0"><?= $_SESSION['usuario_nombre']; ?></h6>
                                            <small class="text-muted"><?= $_SESSION['nombre_puesto']; ?></small>
                                        </div>
                                    </div>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="/login/cerrarSesion">
                                            <i class="fa-solid fa-power-off">&nbsp;</i><span>Cerrar sesión</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <!--/ User Panel -->
                        </ul>
                    </div>
                </nav>
                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">

                    <!-- Content -->
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <?= $contenido ?? ''; ?>
                    </div>
                    <!-- / Content -->

                    <!-- <div class="content-backdrop fade"></div> -->
                </div>
                <!-- / Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>

        <!-- Drag Target Area To SlideIn Menu On Small Screens -->
        <div class="drag-target"></div>
    </div>
    <!-- / Layout wrapper -->

    <!-- Core JS -->
    <script src="/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="/assets/vendor/libs/popper/popper.js"></script>
    <script src="/assets/vendor/js/bootstrap.js"></script>
    <script src="/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="/assets/vendor/libs/hammer/hammer.js"></script>
    <script src="/assets/vendor/libs/typeahead-js/typeahead.js"></script>
    <script src="/assets/vendor/js/menu.js"></script>

    <!-- Vendors JS -->
    <script src="/assets/vendor/js/dropdown-hover.js"></script>
    <script src="/assets/vendor/js/helpers.js"></script>
    <script src="/assets/vendor/js/mega-dropdown.js"></script>
    <script src="/assets/vendor/libs/@form-validation/popular.js"></script>
    <script src="/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
    <script src="/assets/vendor/libs/@form-validation/auto-focus.js"></script>
    <script src="/assets/vendor/libs/animate-on-scroll/animate-on-scroll.js"></script>
    <script src="/assets/vendor/libs/apex-charts/apexcharts.js"></script>
    <script src="/assets/vendor/libs/bloodhound/bloodhound.js"></script>
    <script src="/assets/vendor/libs/bootstrap-select/bootstrap-select.js"></script>
    <script src="/assets/vendor/libs/bs-stepper/bs-stepper.js"></script>
    <script src="/assets/vendor/libs/chartjs/chartjs.js"></script>
    <script src="/assets/vendor/libs/cleave-zen/cleave-zen.js"></script>
    <script src="/assets/vendor/libs/clipboard/clipboard.js"></script>
    <script src="/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>
    <script src="/assets/vendor/libs/dropzone/dropzone.js"></script>
    <script src="/assets/vendor/libs/flatpickr/flatpickr.js"></script>
    <script src="/assets/vendor/libs/fullcalendar/fullcalendar.js"></script>
    <!-- <script src="/assets/vendor/libs/i18n/i18n.js"></script> -->
    <script src="/assets/vendor/libs/jkanban/jkanban.js"></script>
    <script src="/assets/vendor/libs/jquery-repeater/jquery-repeater.js"></script>
    <script src="/assets/vendor/libs/jquery-timepicker/jquery-timepicker.js"></script>
    <script src="/assets/vendor/libs/jstree/jstree.js"></script>
    <!-- <script src="/assets/vendor/libs/leaflet/leaflet.js"></script> -->
    <!-- <script src="/assets/vendor/libs/mapbox-gl/mapbox-gl.js"></script> -->
    <script src="/assets/vendor/libs/masonry/masonry.js"></script>
    <script src="/assets/vendor/libs/moment/moment.js"></script>
    <script src="/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js"></script>
    <script src="/assets/vendor/libs/nouislider/nouislider.js"></script>
    <script src="/assets/vendor/libs/numeral/numeral.js"></script>
    <!-- <script src="/assets/vendor/libs/pickr/pickr.js"></script> -->
    <!-- <script src="/assets/vendor/libs/plyr/plyr.js"></script> -->
    <!-- <script src="/assets/vendor/libs/quill/katex.js"></script> -->
    <!-- <script src="/assets/vendor/libs/quill/quill.js"></script> -->
    <script src="/assets/vendor/libs/select2/select2.js"></script>
    <script src="/assets/vendor/libs/shepherd/shepherd.js"></script>
    <script src="/assets/vendor/libs/sortablejs/sortable.js"></script>
    <script src="/assets/vendor/libs/sweetalert2/sweetalert2.js"></script>
    <script src="/assets/vendor/libs/swiper/swiper.js"></script>
    <script src="/assets/vendor/libs/tagify/tagify.js"></script>
    <script src="/assets/vendor/libs/pdf-viewer/pdf.mjs" type="module"></script>

    <!-- Main JS -->
    <script src="/assets/js/main.js"></script>

    <!-- Page JS -->
    <script src="/assets/js/comunes.js"></script>
    <script src="/assets/js/componentes.js"></script>
    <?= $script ?? ''; ?>
</body>

</html>