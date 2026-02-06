<?php

$titulo = $titulo ?? "Inicio | "  . CONFIGURACION['EMPRESA'];
$usuario = $_SESSION['nombre'] ?? 'Usuario';

function getMenu()
{
    if (!isset($_SESSION['modulos'])) {
        return '';
    }

    $menuItems = [
            'Créditos' => [
                    'icono' => 'fa-solid fa-usd',
                    'subItems' => [
                            [
                                    'label' => 'Estados de Cuenta',
                                    'url' => '/estadocuenta/consulta',
                                    'modulos' => [1]
                            ],
                            [
                                    'label' => 'Documentación',
                                    'url' => '/estadocuenta/documentacion',
                                    'modulos' => [2]
                            ]
                    ]
            ],
            'Gestiones Campo' => [
                    'icono' => 'fa-solid fa-screwdriver-wrench',
                    'subItems' => [
                            [
                                    'label' => 'Histórico Gestiones',
                                    'url' => '/gestiones/seguimiento',
                                    'modulos' => [3]
                            ]
                    ]
            ],
            'Capital Humano' => [
                    'icono' => 'fa-solid fa-users',
                    'subItems' => [
                            [
                                    'label' => 'Gestión',
                                    'url' => '/caphum/gestion',
                                    'modulos' => [4]
                            ],
                            [
                                'label' => 'Bajas',
                                'url' => '/caphum/bajas',
                                'modulos' => [13]
                            ],
                            [
                                    'label' => 'Organigrama',
                                    'url' => '/caphum/organigrama',
                                    'modulos' => [5]
                            ]
                    ]
            ],
            'Reportería' => [
                    'icono' => 'fa-solid fa-file',
                    'subItems' => [
                            [
                                    'label' => 'Resumen Call Center',
                                    'url' => '/reporteria/resumencallcenter',
                                    'modulos' => [6]
                            ],
                            [
                                    'label' => 'Layout Legacy',
                                    'url' => '/reporteria/layoutlegacy',
                                    'modulos' => [7]
                            ],
                            [
                                    'label' => 'Dictamen de Llamadas',
                                    'url' => '/estadocuenta/reporteDictamen',
                                    'modulos' => [14]
                            ]

                            
                    ]
            ],
            'Condonaciones' => [
                    'icono' => 'fa-solid fa-hand-holding-dollar',
                    'subItems' => [
                            [
                                    'label' => 'Historial Condonaciones',
                                    'url' => '/condonaciones/historial',
                                    'modulos' => [15]
                            ]
                    ]
            ],
            'Sabueso' => [
                    'icono' => 'fa-solid fa-dog',
                    'subItems' => [
                            [
                                    'label' => 'Ticket',
                                    'url' => '/sabueso/ticket',
                                    'modulos' => [18]
                            ],
                            [
                                    'label' => 'Panel Admin',
                                    'url' => '/sabueso/paneladmin',
                                    'modulos' => [19]
                            ]
                    ]
            ],
            'Despachos' => [
                    'icono' => 'fa-solid fa-building-columns',
                    'subItems' => [
                            [
                                    'label' => 'Asignación de Créditos',
                                    'url' => '/Despachos/AsignacionCreditosDespacho',
                                    'modulos' => [20]
                            ]
                    ]
            ],
            'Configuración' => [
                    'icono' => 'fa-solid fa-cog',
                    'subItems' => [
                            [
                                    'label' => 'Departamentos',
                                    'url' => '/departamentos/consulta/',
                                    'modulos' => [10]
                            ],
                            [
                                    'label' => 'Equivalencia puestos',
                                    'url' => '/equivalencias/consulta',
                                    'modulos' => [17]
                            ],
                            [
                                    'label' => 'Shell Segundómetro',
                                    'url' => '/segundometro/shell',
                                    'modulos' => [16]
                            ]
                    ]
            ]
    ];

    $menu = '';

    foreach ($menuItems as $key => $item) {

        $submenu = '';

        foreach ($item['subItems'] as $subItem) {

            // ✅ VALIDACIÓN POR MÓDULOS
            if (!array_intersect($subItem['modulos'], $_SESSION['modulos'])) {
                continue;
            }

            $activo = strtolower($subItem['url']) == strtolower($_SERVER['REQUEST_URI'])
                    ? 'active'
                    : '';

            $submenu .= <<<HTML
                <li class="menu-item $activo">
                    <a href="{$subItem['url']}" class="menu-link">
                        <div>{$subItem['label']}</div>
                    </a>
                </li>
            HTML;
        }

        if ($submenu === '') continue;

        $abierto = strpos($submenu, 'active') !== false ? 'active open' : '';

        $menu .= <<<HTML
            <li class="menu-item $abierto">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="{$item['icono']} me-2"></i>
                    <div>$key</div>
                </a>
                <ul class="menu-sub">
                    $submenu
                </ul>
            </li>
        HTML;
    }

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
        content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="" />

    <title><?= $titulo; ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/assets/img/logo_ico2.svg" />

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
    <link rel="stylesheet" href="/assets/css/dark-mode.css" />

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
    <link rel="stylesheet" href="/assets/vendor/libs/leaflet/leaflet.css">
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
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0);" onclick="toggleDarkMode()">
                                            <i class="fa-solid fa-moon" id="darkModeIcon">&nbsp;</i><span id="darkModeText">Modo Oscuro</span>
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
    <script>
    (function(){if(typeof flatpickr==='undefined')return;if(!flatpickr.l10ns)flatpickr.l10ns={};if(!flatpickr.l10ns.es){flatpickr.l10ns.es={weekdays:{shorthand:['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'],longhand:['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado']},months:{shorthand:['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'],longhand:['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre']},firstDayOfWeek:1,ordinal:function(){return'.';}};}try{flatpickr.localize(flatpickr.l10ns.es);}catch(e){}})();
    </script>
    <script src="/assets/vendor/libs/fullcalendar/fullcalendar.js"></script>
    <!-- <script src="/assets/vendor/libs/i18n/i18n.js"></script> -->
    <script src="/assets/vendor/libs/jkanban/jkanban.js"></script>
    <script src="/assets/vendor/libs/jquery-repeater/jquery-repeater.js"></script>
    <script src="/assets/vendor/libs/jquery-timepicker/jquery-timepicker.js"></script>
    <script src="/assets/vendor/libs/jstree/jstree.js"></script>
    <script src="/assets/vendor/libs/leaflet/leaflet.js"></script>
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

    <!-- Dark Mode Script -->
    <script>
        // Función para aplicar el dark mode
        function applyDarkMode(isDark) {
            const body = document.body;
            const icon = document.getElementById('darkModeIcon');
            const text = document.getElementById('darkModeText');
            
            if (isDark) {
                body.classList.add('dark-mode');
                if (icon) icon.className = 'fa-solid fa-sun';
                if (text) text.textContent = 'Modo Claro';
                
                // Forzar estilos en elementos específicos
                fixInlineStyles();
            } else {
                body.classList.remove('dark-mode');
                if (icon) icon.className = 'fa-solid fa-moon';
                if (text) text.textContent = 'Modo Oscuro';
                
                // Restaurar estilos originales
                restoreOriginalStyles();
            }
        }

        // Función para restaurar estilos originales cuando se desactiva dark mode
        function restoreOriginalStyles() {
            // Restaurar dropdowns
            const dropdowns = document.querySelectorAll('.dropdown-menu, .dropdown-item');
            dropdowns.forEach(el => {
                el.style.backgroundColor = '';
                el.style.color = '';
                el.style.backgroundImage = '';
                el.style.borderColor = '';
            });

            // Restaurar acordeones
            const accordions = document.querySelectorAll('.accordion-button, .accordion-body, .accordion-item, .accordion-collapse');
            accordions.forEach(el => {
                el.style.backgroundColor = '';
                el.style.color = '';
                el.style.backgroundImage = '';
                el.style.borderColor = '';
            });

            // Restaurar offcanvas y todos sus hijos
            const offcanvasElements = document.querySelectorAll('.offcanvas, .offcanvas-header, .offcanvas-body, .offcanvas *');
            offcanvasElements.forEach(el => {
                el.style.backgroundColor = '';
                el.style.color = '';
                el.style.borderColor = '';
                el.style.borderBottomColor = '';
                el.style.background = '';
            });

            // Restaurar cards
            const cards = document.querySelectorAll('.card, .card-header, .card-body');
            cards.forEach(el => {
                el.style.backgroundColor = '';
                el.style.color = '';
            });

            // Restaurar formularios
            const formElements = document.querySelectorAll('.form-control, .form-select, .form-label');
            formElements.forEach(el => {
                el.style.backgroundColor = '';
                el.style.color = '';
                el.style.borderColor = '';
            });

            // Restaurar modales
            const modals = document.querySelectorAll('.modal-content, .modal-header, .modal-body, .modal-footer');
            modals.forEach(el => {
                el.style.backgroundColor = '';
                el.style.color = '';
            });

            // Restaurar tabs
            const tabs = document.querySelectorAll('.nav-tabs .nav-link, .tab-content, .tab-pane');
            tabs.forEach(el => {
                el.style.backgroundColor = '';
                el.style.color = '';
            });

            // NO RESTAURAR KPIs - Ellos mantienen sus estilos originales con variables CSS
            // Los KPIs no necesitan restauración porque usan variables CSS que funcionan en ambos modos
        }

        // Función para corregir estilos inline problemáticos
        function fixInlineStyles() {
            // Encontrar elementos con background blanco inline
            const whiteBackgrounds = document.querySelectorAll('[style*="background-color: #fff"], [style*="background-color: white"], [style*="background: #fff"], [style*="background: white"], [style*="background-color: rgb(255, 255, 255)"]');
            whiteBackgrounds.forEach(el => {
                if (!el.classList.contains('btn-primary') && !el.classList.contains('btn-success') && !el.classList.contains('btn-danger') && !el.classList.contains('btn-warning') && !el.classList.contains('btn-info')) {
                    el.style.backgroundColor = '#252525';
                    el.style.background = '#252525';
                }
            });

            // Encontrar elementos con texto negro inline
            const blackText = document.querySelectorAll('[style*="color: #000"], [style*="color: black"], [style*="color: rgb(0, 0, 0)"], [style*="color: #333"]');
            blackText.forEach(el => {
                if (!el.classList.contains('btn')) {
                    el.style.color = '#e0e0e0';
                }
            });

            // FORZAR BACKGROUNDS OSCUROS EN OFFCANVAS
            const offcanvasElements = document.querySelectorAll('.offcanvas, .offcanvas-header, .offcanvas-body, .offcanvas *');
            offcanvasElements.forEach(el => {
                if (el.classList.contains('offcanvas')) {
                    el.style.backgroundColor = '#252525';
                    el.style.color = '#e0e0e0';
                } else if (el.classList.contains('offcanvas-header')) {
                    el.style.backgroundColor = '#303030';
                    el.style.color = '#ffffff';
                    el.style.borderBottomColor = '#3a3a3a';
                } else if (el.classList.contains('offcanvas-body')) {
                    el.style.backgroundColor = '#252525';
                    el.style.color = '#e0e0e0';
                }
                
                // Eliminar fondos blancos de cualquier hijo
                if (el.style.backgroundColor === 'rgb(255, 255, 255)' || el.style.backgroundColor === '#fff' || el.style.backgroundColor === 'white') {
                    if (!el.classList.contains('btn-primary') && !el.classList.contains('btn-success') && !el.classList.contains('btn-danger')) {
                        el.style.backgroundColor = 'transparent';
                    }
                }
            });

            // ELIMINAR TODOS LOS GRADIENTES - Convertir a colores sólidos
            // EXCEPTO en elementos de KPIs que necesitan mantener sus colores
            const elementsWithGradient = document.querySelectorAll('[style*="linear-gradient"], [style*="radial-gradient"]');
            elementsWithGradient.forEach(el => {
                // Saltar elementos de KPIs
                if (el.classList.contains('kpi-card') || 
                    el.classList.contains('kpi-number') || 
                    el.closest('.kpi-card') ||
                    el.classList.contains('kpi-separator')) {
                    return;
                }
                
                const style = el.getAttribute('style');
                if (style) {
                    // Eliminar cualquier gradiente del estilo inline
                    let newStyle = style.replace(/background(-image)?:\s*(?:linear|radial)-gradient\([^)]+\)\s*;?/gi, '');
                    
                    // Si el elemento tiene clases específicas, asignar colores sólidos
                    if (el.classList.contains('table-header-indigo')) {
                        newStyle += '; background: #4F46E5 !important;';
                    } else if (el.classList.contains('table-header-emerald')) {
                        newStyle += '; background: #10B981 !important;';
                    } else if (el.classList.contains('table-header-purple')) {
                        newStyle += '; background: #8B5CF6 !important;';
                    } else if (el.classList.contains('table-header-amber')) {
                        newStyle += '; background: #F59E0B !important;';
                    } else if (el.classList.contains('btn-gradient-success')) {
                        newStyle += '; background: #28a745 !important;';
                    } else if (el.classList.contains('btn-gradient-danger')) {
                        newStyle += '; background: #dc3545 !important;';
                    } else {
                        // Para otros elementos, usar color oscuro por defecto
                        newStyle += '; background: #252525 !important;';
                    }
                    
                    el.setAttribute('style', newStyle);
                    el.style.backgroundImage = 'none';
                }
            });

            // Forzar color en acordeones
            const accordions = document.querySelectorAll('.accordion-button, .accordion-body, .accordion-item, .accordion-collapse');
            accordions.forEach(el => {
                el.style.backgroundImage = 'none';
                if (el.classList.contains('accordion-button')) {
                    el.style.color = '#ffffff';
                    el.style.backgroundColor = '#303030';
                } else if (el.classList.contains('accordion-item') || el.classList.contains('accordion-collapse')) {
                    el.style.backgroundColor = '#252525';
                    el.style.borderColor = '#3a3a3a';
                } else {
                    el.style.color = '#e0e0e0';
                    el.style.backgroundColor = '#252525';
                }
            });

            // Forzar color en dropdowns
            const dropdowns = document.querySelectorAll('.dropdown-menu, .dropdown-item');
            dropdowns.forEach(el => {
                el.style.backgroundImage = 'none';
                if (el.classList.contains('dropdown-menu')) {
                    el.style.backgroundColor = '#252525';
                    el.style.borderColor = '#3a3a3a';
                } else if (el.classList.contains('dropdown-item')) {
                    el.style.color = '#e0e0e0';
                    el.style.backgroundColor = 'transparent';
                }
            });

            // Eliminar gradientes de KPI cards
            // NO HACER - Los KPIs deben mantener sus gradientes
            // const kpiCards = document.querySelectorAll('.kpi-card, .stat-card, [class*="kpi-"]');
            // kpiCards.forEach(el => {
            //     el.style.backgroundImage = 'none';
            // });

            // Eliminar gradientes de botones
            const buttons = document.querySelectorAll('.btn, button');
            buttons.forEach(btn => {
                if (btn.style.background && (btn.style.background.includes('gradient') || btn.style.backgroundImage)) {
                    btn.style.backgroundImage = 'none';
                }
            });
        }

        // Función para toggle del dark mode
        function toggleDarkMode() {
            const isDark = document.body.classList.toggle('dark-mode');
            localStorage.setItem('darkMode', isDark ? 'enabled' : 'disabled');
            applyDarkMode(isDark);
        }

        // Aplicar dark mode al cargar la página (antes de que se renderice)
        (function() {
            const darkMode = localStorage.getItem('darkMode');
            if (darkMode === 'enabled') {
                document.body.classList.add('dark-mode');
            }
        })();

        // Actualizar el icono y texto después de que el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            const isDarkStored = localStorage.getItem('darkMode') === 'enabled';
            const isDarkByClass = document.body.classList.contains('dark-mode');
            const isDark = isDarkStored || isDarkByClass;
            applyDarkMode(isDark);
            
            // Re-aplicar cuando se abren modales o se actualiza contenido dinámico
            const observer = new MutationObserver(function(mutations) {
                if (document.body.classList.contains('dark-mode')) {
                    fixInlineStyles();
                }
            });
            
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        });
    </script>

    <?= $script ?? ''; ?>
</body>

</html>