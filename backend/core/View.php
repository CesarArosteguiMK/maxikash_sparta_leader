<?php

$titulo = $titulo ?? "Inicio | "  . CONFIGURACION['EMPRESA'];
$usuario = $_SESSION['nombre'] ?? 'Usuario';

// Cache-busting para CSS: mismo estilo en todos los navegadores (evita caché vieja en otros equipos)
$__demoCss = realpath(__DIR__ . '/../../public/assets/css/demo.css');
$__darkCss = realpath(__DIR__ . '/../../public/assets/css/dark-mode.css');
$__swalGlassCss = realpath(__DIR__ . '/../../public/assets/css/swal-liquid-glass.css');
$__assetsVer = ($__demoCss ? filemtime($__demoCss) : '') . ($__darkCss ? '.' . filemtime($__darkCss) : '') . ($__swalGlassCss ? '.' . filemtime($__swalGlassCss) : '');
if ($__assetsVer === '.' || $__assetsVer === '') $__assetsVer = (string) time();

function getMenu()
{
    if (!isset($_SESSION['modulos'])) {
        return '';
    }

    $menuItems = [
            'Créditos' => [
                    'icono' => 'fa-solid fa-sack-dollar',
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

            /* MÓDULO INDICADORES — deshabilitado temporalmente (comentar para re-activar)
            'Indicadores' => [
                    'icono' => 'fa-solid fa-chart-line',
                    'subItems' => [
                        [
                            'label' => 'KPI Total',
                            'url' => '/indicadores/kpiTotal',
                            'modulos' => [40]
                        ],
                        [
                            'label' => 'Gestión 1-7',
                            'url' => '/indicadores/gestiones1A7',
                            'modulos' => [24]
                        ],
                        [
                            'label' => 'Eficiencia 1-7',
                            'url' => '/indicadores/eficiencia1A7',
                            'modulos' => [25]
                        ],
                        [
                            'label' => 'Gestión 8-21',
                            'url' => '/indicadores/gestiones8A21',
                            'modulos' => [26]
                        ],
                        [
                            'label' => 'Eficiencia 8-21',
                            'url' => '/indicadores/eficiencia8A21',
                            'modulos' => [27]
                        ],
                        [
                            'label' => 'Intensidad',
                            'url' => '/indicadores/seguimientoIntensidad',
                            'modulos' => [29]
                        ],
                        [
                            'label' => 'Detalle Clientes',
                            'url' => '/indicadores/detalleClientes',
                            'modulos' => [30]
                        ],
                        [
                            'label' => 'Detalle Eficiencia',
                            'url' => '/indicadores/detalleEficiencia',
                            'modulos' => [31]
                        ],
                        [
                            'label' => 'Cartera Inicial',
                            'url' => '/indicadores/carteraInicioSem',
                            'modulos' => [32]
                        ],
                        [
                            'label' => 'Promesas Pago',
                            'url' => '/indicadores/seguimientoPromesasPago',
                            'modulos' => [33]
                        ],
                        [
                            'label' => 'Espartanos',
                            'url' => '/indicadores/espartanos',
                            'modulos' => [34]
                        ],
                        [
                            'label' => 'Matriz Buckets',
                            'url' => '/indicadores/matrizBuckets',
                            'modulos' => [35]
                        ],
                        [
                            'label' => 'Buckets +1',
                            'url' => '/indicadores/matrizBucketsMas1',
                            'modulos' => [36]
                        ],
                        [
                            'label' => 'Auditoría',
                            'url' => '/indicadores/auditoria',
                            'modulos' => [37]
                        ],
                        [
                            'label' => 'Auditoría 2',
                            'url' => '/indicadores/auditoria2',
                            'modulos' => [38]
                        ],
                        [
                            'label' => 'Seguimiento',
                            'url' => '/indicadores/seguimiento',
                            'modulos' => [39]
                        ]
                    ]
            ],
            */

            'Capital Humano' => [
                    'icono' => 'fa-solid fa-users',
                    'subItems' => [
                            [
                                    'label' => 'Gestión',
                                    'url' => '/caphum/gestion',
                                    'modulos' => [4]
                            ],
                            [
                                'label' => 'Candidatos',
                                'url' => '/caphum/candidatos',
                                'modulos' => [42]
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
                                    'label' => 'Sabuesos',
                                    'url' => '/reporteria/sabuesos',
                                    'modulos' => [18, 19]
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
                            ],

                                   [
                                        'label' => 'Reporte CH',
                                        'url' => '/reporteria/reporteCapitalHumano',
                                        'modulos' => [21]
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
                            ],
                            [
                                    'label' => 'Cerrado/Eliminado',
                                    'url' => '/sabueso/cerradoEliminado',
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
            'Onboarding' => [
                    'icono' => 'fa-solid fa-graduation-cap',
                    'subItems' => [
                            [
                                    'label' => 'Curso Onboarding',
                                    'url' => '/onboarding/index',
                                    'modulos' => [44]
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
                                    'label' => 'Países',
                                    'url' => '/paises/consulta',
                                    'modulos' => [41]
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
            ],

    ];

    $menu = '';

    foreach ($menuItems as $key => $item) {

        $submenu = '';

        foreach ($item['subItems'] as $subItem) {

            // ✅ VALIDACIÓN POR MÓDULOS
            if (!empty($subItem['modulos']) && !array_intersect($subItem['modulos'], $_SESSION['modulos'])) {
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
        $keyNorm = str_replace(['á','é','í','ó','ú','ñ','Á','É','Í','Ó','Ú','Ñ','ü','Ü'], ['a','e','i','o','u','n','a','e','i','o','u','n','u','u'], $key);
        $slug = 'menu-' . strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $keyNorm), '-'));

        $menu .= <<<HTML
            <li class="menu-item $slug $abierto">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon {$item['icono']}"></i>
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
    <style id="dark-mode-critical">html.dark-mode body{background-color:#0f172a !important;}
html.dark-mode .layout-wrapper,html.dark-mode .layout-container,html.dark-mode .content-wrapper,html.dark-mode .layout-page,html.dark-mode .container-xxl,html.dark-mode .container-fluid,html.dark-mode .container{background-color:transparent !important;}
html.dark-mode .layout-navbar{background-color:rgba(30,41,59,.95) !important;border-bottom:1px solid #334155 !important;}
html.dark-mode .navbar{background-color:transparent !important;}
html.dark-mode .navbar .nav-link{color:#f1f5f9 !important;}
html.dark-mode .layout-menu,html.dark-mode .menu,html.dark-mode .menu-inner,html.dark-mode .app-brand{background-color:#1e293b !important;}
html.dark-mode .menu-item .menu-link{color:#94a3b8 !important;}
html.dark-mode .menu-item .menu-link:hover{background-color:#334155 !important;color:#93c5fd !important;}
html.dark-mode .card,html.dark-mode .documentacion-card,html.dark-mode .estado-cuenta-card{background-color:#1e293b !important;color:#f1f5f9 !important;border-color:#334155 !important;}
html.dark-mode .card-body,html.dark-mode .card-header,html.dark-mode .card-footer{background-color:#1e293b !important;color:#f1f5f9 !important;border-color:#334155 !important;}
html.dark-mode .card-header{background-color:#334155 !important;}
html.dark-mode .card-footer{background-color:#334155 !important;}
html.dark-mode .form-control,html.dark-mode .form-select{background-color:#1e293b !important;color:#f1f5f9 !important;border-color:#334155 !important;}
html.dark-mode .form-label,html.dark-mode .form-check-label{color:#e0e0e0 !important;}
html.dark-mode .form-check-input{background-color:#252525 !important;border-color:#3a3a3a !important;}
html.dark-mode .input-group-text{background-color:#303030 !important;border-color:#3a3a3a !important;color:#e0e0e0 !important;}
html.dark-mode .btn-outline-secondary{color:#94a3b8 !important;border-color:#475569 !important;}
html.dark-mode .btn-outline-secondary:hover{background-color:#334155 !important;color:#f1f5f9 !important;border-color:#475569 !important;}
html.dark-mode .btn-outline-primary{color:#93c5fd !important;border-color:#3b82f6 !important;}
html.dark-mode .btn-outline-primary:hover{background-color:#1e40af !important;color:#fff !important;border-color:#3b82f6 !important;}
html.dark-mode .inicio-mkx .hero{background:linear-gradient(128deg,#0f172a 0%,#1e3a5f 50%,#1e40af 100%) !important;}
html.dark-mode .inicio-mkx .hero-desc{color:rgba(255,255,255,.85) !important;}
html.dark-mode .inicio-mkx .hero-title{color:#fff !important;}
html.dark-mode .inicio-mkx .hero-badge{color:#c8d62b !important;}
html.dark-mode .inicio-mkx .hero-datetime{background:rgba(255,255,255,0.1) !important;border-color:rgba(255,255,255,0.2) !important;}
html.dark-mode .inicio-mkx .hero-datetime-time,html.dark-mode .inicio-mkx .hero-datetime-date{color:#fff !important;}
html.dark-mode .inicio-mkx .sec-txt{color:#e0e0e0 !important;}
html.dark-mode .inicio-mkx .sec-line{background:#334155 !important;}
html.dark-mode .inicio-mkx .qcard{background:#1e293b !important;border-color:#334155 !important;color:#f1f5f9 !important;}
html.dark-mode .inicio-mkx .qcard:hover{border-color:#475569 !important;}
html.dark-mode .inicio-mkx .qcard .qico{background:rgba(255,255,255,0.08) !important;}
html.dark-mode .inicio-mkx .qcard .qico i{color:rgba(255,255,255,.87) !important;}
html.dark-mode .inicio-mkx .qcard .qt{color:#f1f5f9 !important;}
html.dark-mode .inicio-mkx .qcard .qd{color:rgba(241,245,249,.85) !important;}
html.dark-mode .navbar .dropdown .dropdown-toggle,html.dark-mode .navbar h6{color:#f1f5f9 !important;}
html.dark-mode .navbar .text-muted{color:#94a3b8 !important;}
</style>
    <script>(function(){if(localStorage.getItem('darkMode')==='enabled')document.documentElement.classList.add('dark-mode');})();</script>
    <meta charset="utf-8" />
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
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
    <link rel="stylesheet" href="/assets/vendor/fonts/flag-icons.css" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="/assets/vendor/css/core.css" />
    <link rel="stylesheet" href="/assets/css/demo.css?v=<?= $__assetsVer ?>" />
    <link rel="stylesheet" href="/assets/css/dark-mode.css?v=<?= $__assetsVer ?>" />

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
    <link rel="stylesheet" href="/assets/css/swal-liquid-glass.css?v=<?= $__assetsVer ?>">
    <link rel="stylesheet" href="/assets/vendor/libs/swiper/swiper.css">
    <link rel="stylesheet" href="/assets/vendor/libs/tagify/tagify.css">
    <link rel="stylesheet" href="/assets/vendor/libs/typeahead-js/typeahead.css">

    <!-- Page CSS -->
    <?= $css ?? ''; ?>

    <!-- Iconos del menú lateral en blanco y negro (mismo color para todos) -->
    <style>
    .layout-menu .menu-inner > .menu-item .menu-link .menu-icon { color: var(--bs-body-color, #697a8d) !important; font-size: 1.05rem !important; block-size: 1.05rem !important; inline-size: 1.05rem !important; }
    .layout-menu .menu-inner > .menu-item .menu-link > div { font-size: 0.875rem !important; font-weight: 700 !important; }
    body.dark-mode .layout-menu .menu-inner > .menu-item .menu-link .menu-icon { color: rgba(255,255,255,.87) !important; }
    </style>

    <!-- Dropdown usuario: iconos con color -->
    <style>
    .navbar-dropdown .dropdown-menu .dropdown-item i.fa-fw { width: 1.25em; text-align: center; margin-inline-end: 0.5rem; }
    .navbar-dropdown .dropdown-item.user-dropdown-perfil i { color: #1A52A8 !important; }
    .navbar-dropdown .dropdown-item.user-dropdown-logout i { color: #dc2626 !important; }
    .navbar-dropdown .dropdown-item.user-dropdown-dark i.dark-mode-icon { color: #7c3aed !important; }
    .navbar-dropdown .dropdown-item.user-dropdown-dark:hover i { color: #8b5cf6 !important; }
    body.dark-mode .navbar-dropdown .dropdown-item.user-dropdown-perfil i { color: #60a5fa !important; }
    body.dark-mode .navbar-dropdown .dropdown-item.user-dropdown-logout i { color: #f87171 !important; }
    body.dark-mode .navbar-dropdown .dropdown-item.user-dropdown-dark i.dark-mode-icon { color: #c4b5fd !important; }
    body.dark-mode .navbar-dropdown .dropdown-item.user-dropdown-dark:hover i { color: #a78bfa !important; }
    </style>

    <!-- Campana de notificaciones: estilos + liquid glass dropdown -->
    <style>
    .nav-notif-wrap { position: relative; }
    .nav-notif-wrap .nav-link { padding: 0.5rem 0.75rem !important; }
    .nav-notif-bell { font-size: 1.35rem; color: var(--bs-body-color, #566a7f); transition: color 0.2s ease, transform 0.2s ease; }
    .nav-notif-wrap:hover .nav-notif-bell { color: #696cff; }
    /* Campana con pendientes: color llamativo y pulso */
    .nav-notif-bell.has-unread { color: #dc3545 !important; }
    .nav-notif-wrap:hover .nav-notif-bell.has-unread { color: #e4606d !important; }
    .nav-notif-bell-pulse { animation: notifBellPulse 1.2s ease-in-out infinite; }
    @keyframes notifBellPulse { 0%, 100% { transform: scale(1); opacity: 1; } 50% { transform: scale(1.12); opacity: 0.92; } }
    /* Badge: número de no leídas — rojo fuerte para que se note */
    .nav-notif-badge { position: absolute; top: -4px; right: -4px; min-width: 1.25rem; height: 1.25rem; padding: 0 5px; font-size: 0.7rem; font-weight: 700; line-height: 1.25rem; border-radius: 50%; background: #dc3545 !important; color: #fff !important; text-align: center; border: 2px solid var(--bs-body-bg, #fff); box-shadow: 0 0 0 1px rgba(220,53,69,0.5); }
    body.dark-mode .nav-notif-badge { border-color: rgba(30,41,59,0.95); }
    .dropdown-menu-notif-glass { background: rgba(255, 255, 255, 0.88) !important; backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.5) !important; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12), 0 0 0 1px rgba(255, 255, 255, 0.3) inset !important; min-width: 320px; max-width: 380px; max-height: 70vh; overflow: hidden; padding: 0 !important; display: none; flex-direction: column; }
    .nav-notif-wrap.dropdown.show .dropdown-menu-notif-glass { display: flex !important; }
    .dropdown-menu-notif-glass .notif-header { flex-shrink: 0; padding: 0.75rem 1rem; border-bottom: 1px solid rgba(0,0,0,0.08); font-weight: 600; display: flex; align-items: center; justify-content: space-between; }
    .dropdown-menu-notif-glass .notif-body { overflow-y: auto; flex: 1; }
    .dropdown-menu-notif-glass .notif-body.notif-sin-lista { overflow: hidden; }
    .dropdown-menu-notif-glass .notif-item { padding: 0.65rem 1rem; border-bottom: 1px solid rgba(0,0,0,0.06); cursor: pointer; transition: background 0.15s ease; white-space: normal; }
    .dropdown-menu-notif-glass .notif-item:hover { background: rgba(105, 108, 255, 0.08); }
    /* No leída: fondo destacado y borde izquierdo */
    .dropdown-menu-notif-glass .notif-item.notif-no-leida { background: rgba(220, 53, 69, 0.08); border-left: 3px solid #dc3545; font-weight: 500; }
    /* Leída: estilo más suave */
    .dropdown-menu-notif-glass .notif-item:not(.notif-no-leida) { background: transparent; opacity: 0.85; }
    .dropdown-menu-notif-glass .notif-item:not(.notif-no-leida) .notif-text { color: #697a8d; }
    .dropdown-menu-notif-glass .notif-item .notif-text { font-size: 0.875rem; }
    .dropdown-menu-notif-glass .notif-item .notif-time { font-size: 0.75rem; color: #697a8d; margin-top: 2px; }
    .dropdown-menu-notif-glass .notif-empty { padding: 1.25rem 1rem; text-align: center; color: #697a8d; font-size: 0.875rem; }
    body.dark-mode .dropdown-menu-notif-glass { background: rgba(30, 41, 59, 0.92) !important; border-color: rgba(71, 85, 105, 0.5) !important; box-shadow: 0 8px 32px rgba(0,0,0,0.4), 0 0 0 1px rgba(51, 65, 85, 0.3) inset !important; }
    body.dark-mode .dropdown-menu-notif-glass .notif-header { border-color: rgba(255,255,255,0.1); color: #f1f5f9; }
    body.dark-mode .dropdown-menu-notif-glass .notif-item { border-color: rgba(255,255,255,0.06); }
    body.dark-mode .dropdown-menu-notif-glass .notif-item:hover { background: rgba(129, 140, 248, 0.15); }
    body.dark-mode .dropdown-menu-notif-glass .notif-item.notif-no-leida { background: rgba(220, 53, 69, 0.15); border-left-color: #f87171; }
    body.dark-mode .dropdown-menu-notif-glass .notif-item.notif-no-leida::before { background: #f87171; }
    body.dark-mode .dropdown-menu-notif-glass .notif-item:not(.notif-no-leida) .notif-text { color: #94a3b8; }
    body.dark-mode .dropdown-menu-notif-glass .notif-item .notif-time { color: #94a3b8; }
    body.dark-mode .dropdown-menu-notif-glass .notif-empty { color: #94a3b8; }
    body.dark-mode .nav-notif-bell { color: #e2e8f0; }
    body.dark-mode .nav-notif-wrap:hover .nav-notif-bell { color: #818cf8; }
    </style>

    <!-- Helpers -->
    <script src="/assets/vendor/js/helpers.js"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
    <!-- <script src="/assets/vendor/js/template-customizer.js"></script> -->

    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="/assets/js/config.js"></script>
</head>

<body>
    <script>(function(){var d=document,e=d.documentElement,b=d.body,v=localStorage.getItem('darkMode')==='enabled';if(v){e.classList.add('dark-mode');if(b)b.classList.add('dark-mode');}})();</script>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">

            <!-- Menu -->
            <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
                <div class="app-brand demo">
                    <a href="/Inicio" class="app-brand-link w-100" id="sidebarLogoEaster">
                        <span class="app-brand-logo demo w-100 app-brand-img">
                            <img src="/assets/img/Logotipo-Maxikash-Outline.webp" alt="Maxikash" class="sidebar-logo" />
                        </span>
                        <img src="/assets/img/cabeza_spartan2.png" alt="Spartan" class="app-brand-img-collapsed sidebar-logo-collapsed" />
                        <span class="app-brand-text demo menu-text fw-bold ms-2 d-none d-md-inline-block" style="font-size:0;">Maxikash</span>
                    </a>

                    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
                        <i class="fa-solid fa-chevron-left d-flex align-items-center justify-content-center"></i>
                    </a>
                </div>
                <hr>
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
                            <!-- Notificaciones (campana) -->
                            <li class="nav-item nav-notif-wrap dropdown me-2">
                                <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown" id="navbarNotifToggle" aria-expanded="false">
                                    <span class="position-relative d-inline-block">
                                        <i class="fa-solid fa-bell nav-notif-bell" id="navbarNotifIcon"></i>
                                        <span class="nav-notif-badge position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="navbarNotifBadge" style="display: none;">0</span>
                                    </span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-notif-glass shadow-lg" id="navbarNotifDropdown">
                                    <li class="notif-header list-unstyled mb-0">
                                        <span><i class="fa-solid fa-bell me-2"></i>Notificaciones</span>
                                        <button type="button" class="btn btn-sm btn-link p-0 text-primary" id="navbarNotifMarcarTodas" title="Marcar todas como leídas" style="display: none;">Marcar leídas</button>
                                    </li>
                                    <li class="notif-body list-unstyled mb-0" id="navbarNotifBody">
                                        <div class="notif-empty py-4"><i class="fa-solid fa-inbox d-block mb-2 opacity-50"></i>Cargando…</div>
                                    </li>
                                </ul>
                            </li>
                            <!-- User Panel -->
                            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                                <a
                                    class="nav-link dropdown-toggle hide-arrow p-0"
                                    href="javascript:void(0);"
                                    data-bs-toggle="dropdown">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="avatar avatar-online" id="navbarAvatarEaster">
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
                                        <a class="dropdown-item user-dropdown-perfil" href="/perfil">
                                            <i class="fa-solid fa-user-pen fa-fw"></i><span>Ajustes (tu perfil)</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item dark-mode-toggle user-dropdown-dark" href="javascript:void(0);" onclick="toggleDarkMode()" id="darkModeToggle">
                                            <i class="fa-solid fa-moon dark-mode-icon fa-fw" id="darkModeIcon"></i><span id="darkModeText">Apariencia (modo oscuro)</span>
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item user-dropdown-logout" href="/login/cerrarSesion">
                                            <i class="fa-solid fa-right-from-bracket fa-fw"></i><span>Cerrar sesión</span>
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

    <!-- Modal Easter egg: video Spartan (triple clic en logo) -->
    <style>
    /* Animación de entrada del modal Spartan */
    #modalSpartanVideo .modal-content { border: 2px solid rgba(180, 83, 9, 0.6); box-shadow: 0 0 40px rgba(180, 83, 9, 0.25); transform: scale(0.85); opacity: 0; transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.3s ease; }
    #modalSpartanVideo.show .modal-content { transform: scale(1); opacity: 1; }
    #modalSpartanVideo .spartan-badge { background: linear-gradient(135deg, #b45309 0%, #92400e 100%); color: #fff; font-weight: 700; letter-spacing: 0.08em; padding: 6px 14px; border-radius: 20px; font-size: 0.85rem; display: inline-block; margin-bottom: 12px; opacity: 0; animation: spartanBadgeIn 0.5s ease 0.2s forwards; }
    @keyframes spartanBadgeIn { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }
    /* Efecto burst en tercer clic */
    .spartan-burst { position: fixed; left: 0; top: 0; width: 100%; height: 100%; pointer-events: none; z-index: 10003; }
    .spartan-burst .spartan-burst-dot { position: absolute; width: 8px; height: 8px; background: #b45309; border-radius: 50%; box-shadow: 0 0 12px #b45309; animation: spartanBurst 0.6s ease-out forwards; }
    @keyframes spartanBurst { 0% { opacity: 1; transform: translate(-50%, -50%) scale(1); } 100% { opacity: 0; transform: translate(-50%, -50%) translate(var(--tx), var(--ty)) scale(0); } }
    /* Konami code: épico – overlay flash, mensaje grande, palpitaciones, más fuegos */
    .konami-overlay { position: fixed; inset: 0; z-index: 10004; pointer-events: none; background: radial-gradient(ellipse at center, rgba(251,191,36,0.4) 0%, rgba(180,83,9,0.2) 40%, rgba(0,0,0,0.85) 100%); opacity: 0; animation: konamiOverlayIn 0.5s ease forwards; }
    @keyframes konamiOverlayIn { 0% { opacity: 0; } 20% { opacity: 1; } 100% { opacity: 0.95; } }
    @keyframes konamiOverlayOut { 0% { opacity: 0.95; } 100% { opacity: 0; } }
    .konami-message { position: fixed; left: 50%; top: 50%; transform: translate(-50%, -50%); z-index: 10005; background: linear-gradient(135deg, #0f172a 0%, #1e293b 30%, #334155 100%); color: #fbbf24; padding: 40px 80px; border-radius: 24px; font-size: 3rem; font-weight: 900; box-shadow: 0 0 80px rgba(251,191,36,0.35), 0 25px 80px rgba(0,0,0,0.6), inset 0 1px 0 rgba(255,255,255,0.1); border: 3px solid #b45309; opacity: 0; animation: konamiMessageIn 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; pointer-events: none; text-align: center; letter-spacing: 0.02em; text-shadow: 0 0 30px rgba(251,191,36,0.6), 0 0 60px rgba(180,83,9,0.3); }
    .konami-message .konami-calaveras { font-size: 2.4rem; letter-spacing: 0.3em; margin-bottom: 8px; display: block; animation: konamiSkullPulse 0.5s ease-in-out infinite alternate; }
    .konami-message.konami-visible { animation: konamiMessageIn 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) forwards, konamiHeartbeat 0.7s ease-in-out 0.3s infinite; text-shadow: 0 0 40px rgba(251,191,36,0.8), 0 0 80px rgba(180,83,9,0.4); }
    @keyframes konamiSkullPulse { 0% { opacity: 1; transform: scale(1); } 100% { opacity: 0.9; transform: scale(1.1); } }
    @keyframes konamiMessageIn { 0% { opacity: 0; transform: translate(-50%, -50%) scale(0.3); filter: blur(8px); } 100% { opacity: 1; transform: translate(-50%, -50%) scale(1); filter: blur(0); } }
    @keyframes konamiHeartbeat { 0%, 100% { transform: translate(-50%, -50%) scale(1); } 50% { transform: translate(-50%, -50%) scale(1.06); } }
    @keyframes konamiMessageOut { 0% { opacity: 1; transform: translate(-50%, -50%) scale(1); } 100% { opacity: 0; transform: translate(-50%, -50%) scale(1.1); } }
    .konami-firework { position: absolute; width: 16px; height: 16px; border-radius: 50%; pointer-events: none; box-shadow: 0 0 16px 3px currentColor, 0 0 32px currentColor; }
    @keyframes konamiFireworkBurst { 0% { opacity: 1; transform: translate(-50%, -50%) scale(1); } 100% { opacity: 0; transform: translate(calc(-50% + var(--fw-tx)), calc(-50% + var(--fw-ty))) scale(0.3); } }
    @keyframes konamiFall{0%{transform:translateY(0) rotate(0deg);opacity:1;}100%{transform:translateY(100vh) rotate(720deg);opacity:0.35;}}
    body.konami-shake { animation: konamiShake 0.4s ease-out; }
    @keyframes konamiShake { 0%, 100% { transform: translateX(0); } 15% { transform: translateX(-8px); } 30% { transform: translateX(8px); } 45% { transform: translateX(-5px); } 60% { transform: translateX(5px); } 75% { transform: translateX(-2px); } }
    /* Konami: dos espadas que chocan y terminan cruzadas */
    .konami-swords-wrap { position: fixed; left: 50%; top: 42%; transform: translate(-50%, -50%); z-index: 10007; pointer-events: none; width: 180px; height: 120px; }
    .konami-sword { position: absolute; font-size: 4rem; line-height: 1; transform-origin: 50% 85%; opacity: 0; }
    .konami-sword-left { left: 0; top: 50%; transform: translate(0, -50%) rotate(-70deg); animation: konamiSwordClashLeft 1.4s ease-out 0.2s forwards; }
    .konami-sword-right { right: 0; top: 50%; transform: translate(0, -50%) rotate(70deg); animation: konamiSwordClashRight 1.4s ease-out 0.2s forwards; }
    @keyframes konamiSwordClashLeft { 0% { opacity: 0; transform: translate(0, -50%) rotate(-70deg) translateX(-20px); } 25% { opacity: 1; transform: translate(0, -50%) rotate(-45deg) translateX(8px) scale(1.05); } 45% { opacity: 1; transform: translate(0, -50%) rotate(-40deg) translateX(2px) scale(0.98); } 100% { opacity: 1; transform: translate(0, -50%) rotate(-135deg); } }
    @keyframes konamiSwordClashRight { 0% { opacity: 0; transform: translate(0, -50%) rotate(70deg) translateX(20px); } 25% { opacity: 1; transform: translate(0, -50%) rotate(45deg) translateX(-8px) scale(1.05); } 45% { opacity: 1; transform: translate(0, -50%) rotate(40deg) translateX(-2px) scale(0.98); } 100% { opacity: 1; transform: translate(0, -50%) rotate(135deg); } }
    /* Easter egg: triple clic en avatar → mensaje oculto */
    .avatar-easter-toast { position: fixed; left: 50%; top: 50%; transform: translate(-50%, -50%); z-index: 10006; background: linear-gradient(135deg, #1e293b 0%, #334155 100%); color: #fbbf24; padding: 24px 40px; border-radius: 16px; font-size: 1.25rem; font-weight: 700; box-shadow: 0 16px 48px rgba(0,0,0,0.4); border: 2px solid #b45309; opacity: 0; animation: avatarEasterIn 0.35s ease forwards; pointer-events: none; text-align: center; }
    .avatar-easter-toast .avatar-easter-emoji { font-size: 3rem; display: block; margin-bottom: 8px; }
    .avatar-easter-toast .avatar-easter-gif { width: 200px; height: auto; max-height: 140px; object-fit: contain; display: block; margin: 0 auto 12px; border-radius: 8px; }
    @keyframes avatarEasterIn { 0% { opacity: 0; transform: translate(-50%, -50%) scale(0.7); } 100% { opacity: 1; transform: translate(-50%, -50%) scale(1); } }
    @keyframes avatarEasterOut { 0% { opacity: 1; transform: translate(-50%, -50%) scale(1); } 100% { opacity: 0; transform: translate(-50%, -50%) scale(0.9); } }
    </style>
    <div class="modal fade" id="modalSpartanVideo" tabindex="-1" aria-hidden="true" data-bs-backdrop="true" data-bs-keyboard="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-dark">
                <div class="modal-header border-secondary flex-column align-items-start">
                    <span class="spartan-badge">⚔ ¡Spartan!</span>
                    <h5 class="modal-title text-white w-100"><i class="fa fa-play-circle me-2"></i>Video</h5>
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-0">
                    <video id="spartanVideoPlayer" class="w-100" controls playsinline src="/assets/img/spartan_video.mp4"></video>
                </div>
            </div>
        </div>
    </div>

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
    <script type="module">
        import * as pdfjsLib from '/assets/vendor/libs/pdf-viewer/pdf.mjs';
        window.pdfjsLib = pdfjsLib;
    </script>

    <!-- Main JS -->
    <script src="/assets/js/main.js"></script>

    <!-- Page JS -->
    <script src="/assets/js/comunes.js"></script>
    <script src="/assets/js/componentes.js"></script>

    <!-- Linkify: convierte URLs en texto a enlaces clicables (descripción dictamen Sabueso) -->
    <script>
    window.linkifyDescripcionDictamen = function(text) {
        if (!text || (String(text).trim() === '')) return '—';
        var esc = String(text).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        var urlRegex = /(https?:\/\/[^\s<>"'\]]+|maps\.google[^\s<>"'\]]*|goo\.gl\/[^\s<>"'\]]+)/gi;
        return esc.replace(urlRegex, function(url) {
            var href = url.replace(/&amp;/g, '&');
            return '<a href="' + href.replace(/"/g, '&quot;') + '" target="_blank" rel="noopener noreferrer">' + url + '</a>';
        });
    };
    </script>

    <!-- Campana de notificaciones: cargar lista, badge, marcar leídas, sonido -->
    <script>
    (function(){
        var badgeEl = document.getElementById('navbarNotifBadge');
        var iconEl = document.getElementById('navbarNotifIcon');
        var bodyEl = document.getElementById('navbarNotifBody');
        var btnMarcarTodas = document.getElementById('navbarNotifMarcarTodas');
        var dropdownEl = document.getElementById('navbarNotifDropdown');
        var notifToggle = document.getElementById('navbarNotifToggle');
        var soundInterval = null;
        var audioNotif = null;
        var NOTIF_SOUND_URL = '/assets/audio/ring2.mp3';
        var NOTIF_BEEP_INTERVAL_MS = 1500;
        var NOTIF_SOUND_PLAYED_KEY = 'sparta_notif_sound_played_ids';
        var NOTIF_SOUND_PLAYED_MAX = 100;

        function getNotifSoundPlayedIds() {
            try {
                var raw = localStorage.getItem(NOTIF_SOUND_PLAYED_KEY);
                var arr = raw ? JSON.parse(raw) : [];
                return Array.isArray(arr) ? arr : [];
            } catch (e) { return []; }
        }

        function setNotifSoundPlayedIds(ids) {
            try {
                var prev = getNotifSoundPlayedIds();
                var set = {};
                prev.forEach(function(id){ set[id] = true; });
                ids.forEach(function(id){ if (id > 0) set[id] = true; });
                var merged = Object.keys(set).map(Number).filter(function(id){ return id > 0; });
                if (merged.length > NOTIF_SOUND_PLAYED_MAX) merged = merged.slice(-NOTIF_SOUND_PLAYED_MAX);
                localStorage.setItem(NOTIF_SOUND_PLAYED_KEY, JSON.stringify(merged));
            } catch (e) {}
        }
        function notifUrl(path) {
            var p = (path || "").replace(/^\//, "").replace(/\/$/, "");
            var base = location.pathname.replace(/\/[^/]*$/, "") || "/";
            var pathPart = (base === "/" || base === "") ? "/" : (base.endsWith("/") ? base : base + "/");
            return location.origin + pathPart + "index.php?url=" + encodeURIComponent(p || "notificaciones/listar");
        }
        if (!badgeEl || !bodyEl) return;

        function setNotifBody(html) {
            try { if (bodyEl) bodyEl.innerHTML = html; } catch (e) {}
        }

        function playNotifSound() {
            try {
                if (!audioNotif) {
                    audioNotif = new Audio(NOTIF_SOUND_URL);
                }
                if (audioNotif.paused) {
                    audioNotif.currentTime = 0;
                    audioNotif.play().catch(function(){});
                } else {
                    audioNotif.currentTime = 0;
                    audioNotif.play().catch(function(){});
                }
            } catch (e) {}
        }

        function stopNotifSound() {
            if (soundInterval) {
                clearInterval(soundInterval);
                soundInterval = null;
            }
        }

        function startNotifSoundIfUnread(totalNoLeidas, list) {
            if ((totalNoLeidas | 0) <= 0) {
                stopNotifSound();
                return;
            }
            var idsNoLeidos = (Array.isArray(list) ? list : [])
                .filter(function(n){ return (n.leida | 0) === 0; })
                .map(function(n){ return n.id | 0; })
                .filter(function(id){ return id > 0; });
            var played = getNotifSoundPlayedIds();
            var playedSet = {};
            played.forEach(function(id){ playedSet[id] = true; });
            var hayNuevos = idsNoLeidos.some(function(id){ return !playedSet[id]; });
            if (!hayNuevos) return;
            playNotifSound();
            setNotifSoundPlayedIds(idsNoLeidos);
        }

        function formatNotifTime(dateStr) {
            if (!dateStr) return '';
            var d = new Date(dateStr);
            var now = new Date();
            var diff = (now - d) / 60000;
            if (diff < 1) return 'Ahora';
            if (diff < 60) return Math.floor(diff) + ' min';
            if (diff < 1440) return Math.floor(diff / 60) + ' h';
            return d.toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
        }

        function renderNotificaciones(datos, totalNoLeidas) {
            var list = Array.isArray(datos) ? datos : [];
            if (bodyEl) bodyEl.classList.toggle('notif-sin-lista', list.length === 0);
            if (list.length === 0) {
                bodyEl.innerHTML = '<div class="notif-empty py-4"><i class="fa-solid fa-inbox d-block mb-2 opacity-50"></i>Sin notificaciones</div>';
            } else {
                var html = '';
                list.forEach(function(n){
                    var cls = n.leida == 0 ? ' notif-no-leida' : '';
                    var time = formatNotifTime(n.fecha_creacion);
                    var idTicket = n.id_ticket ? (n.id_ticket | 0) : 0;
                    html += '<div class="notif-item' + cls + '" data-id="' + (n.id|0) + '" data-id-ticket="' + idTicket + '" data-leida="' + (n.leida|0) + '">';
                    html += '<div class="notif-text">' + (n.mensaje || '').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</div>';
                    html += '<div class="notif-time">' + time + '</div></div>';
                });
                bodyEl.innerHTML = html;
                bodyEl.querySelectorAll('.notif-item').forEach(function(el){
                    el.addEventListener('click', function(){
                        var id = parseInt(el.getAttribute('data-id'), 10);
                        if (id > 0 && el.getAttribute('data-leida') === '0') marcarUnaLeida(id);
                        el.classList.remove('notif-no-leida');
                        el.setAttribute('data-leida', '1');
                        el.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
                        el.style.opacity = '0';
                        el.style.transform = 'translateX(8px)';
                        setTimeout(function(){ if (el.parentNode) el.remove(); actualizarSoloBadge(); }, 280);
                    });
                });
            }
            if (totalNoLeidas > 0) {
                badgeEl.textContent = totalNoLeidas > 99 ? '99+' : totalNoLeidas;
                badgeEl.style.display = 'inline-block';
                if (iconEl) { iconEl.classList.add('nav-notif-bell-pulse'); iconEl.classList.add('has-unread'); }
                if (btnMarcarTodas) btnMarcarTodas.style.display = 'inline-block';
                startNotifSoundIfUnread(totalNoLeidas, list);
            } else {
                badgeEl.style.display = 'none';
                if (iconEl) { iconEl.classList.remove('nav-notif-bell-pulse'); iconEl.classList.remove('has-unread'); }
                if (btnMarcarTodas) btnMarcarTodas.style.display = 'none';
                stopNotifSound();
            }
        }

        function marcarUnaLeida(id) {
            fetch(notifUrl('/notificaciones/marcarLeida'), { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id_notificacion: id }), credentials: 'same-origin' })
                .then(function(r){ return r.json(); })
                .then(function(){ actualizarSoloBadge(); });
        }

        function actualizarSoloBadge() {
            fetch(notifUrl('/notificaciones/listar'), { method: 'GET', credentials: 'same-origin' })
                .then(function(r){ return r.ok ? r.json() : null; })
                .then(function(res){
                    if (res && res.total_no_leidas !== undefined) {
                        var total = res.total_no_leidas | 0;
                        if (total > 0) {
                            badgeEl.textContent = total > 99 ? '99+' : total;
                            badgeEl.style.display = 'inline-block';
                            if (iconEl) { iconEl.classList.add('nav-notif-bell-pulse'); iconEl.classList.add('has-unread'); }
                            if (btnMarcarTodas) btnMarcarTodas.style.display = 'inline-block';
                            startNotifSoundIfUnread(total, res.datos || []);
                        } else {
                            badgeEl.style.display = 'none';
                            if (iconEl) { iconEl.classList.remove('nav-notif-bell-pulse'); iconEl.classList.remove('has-unread'); }
                            if (btnMarcarTodas) btnMarcarTodas.style.display = 'none';
                            stopNotifSound();
                        }
                    }
                })
                .catch(function(){});
        }

        function cargarNotificaciones() {
            setNotifBody('<div class="notif-empty py-4"><i class="fa-solid fa-spinner fa-spin d-block mb-2 opacity-50"></i>Cargando…</div>');
            if (bodyEl) bodyEl.classList.add('notif-sin-lista');
            var timeout = setTimeout(function(){
                if (bodyEl) bodyEl.classList.add('notif-sin-lista');
                setNotifBody('<div class="notif-empty py-4"><i class="fa-solid fa-exclamation-triangle d-block mb-2 opacity-50"></i>Timeout al cargar</div>');
            }, 8000);
            var url = notifUrl("/notificaciones/listar");
            fetch(url, { method: "GET", credentials: "same-origin" })
                .then(function(r){
                    if (!r.ok) throw new Error("HTTP " + r.status);
                    return r.text();
                })
                .then(function(text){
                    clearTimeout(timeout);
                    var res;
                    try { res = JSON.parse(text); } catch (e) {
                        if (bodyEl) bodyEl.classList.add('notif-sin-lista');
                        setNotifBody('<div class="notif-empty py-4"><i class="fa-solid fa-code d-block mb-2 opacity-50"></i>Respuesta no válida</div>');
                        return;
                    }
                    var list = (res && res.success && res.datos) ? res.datos : (Array.isArray(res) ? res : []);
                    var totalNoLeidas = (res && res.total_no_leidas !== undefined) ? (res.total_no_leidas | 0) : 0;
                    renderNotificaciones(list, totalNoLeidas);
                    if (totalNoLeidas > 0) {
                        fetch(notifUrl("/notificaciones/marcarTodasLeidas"), { method: "POST", headers: { "Content-Type": "application/json" }, body: "{}", credentials: "same-origin" })
                            .then(function(r){ return r.json(); })
                            .then(function(m){
                                if (m && m.success) {
                                    badgeEl.style.display = "none";
                                    if (iconEl) { iconEl.classList.remove("nav-notif-bell-pulse"); iconEl.classList.remove("has-unread"); }
                                    if (btnMarcarTodas) btnMarcarTodas.style.display = "none";
                                    stopNotifSound();
                                    list.forEach(function(n){ n.leida = 1; });
                                    renderNotificaciones(list, 0);
                                }
                            })
                            .catch(function(){});
                    }
                })
                .catch(function(){
                    clearTimeout(timeout);
                    if (bodyEl) bodyEl.classList.add('notif-sin-lista');
                    setNotifBody('<div class="notif-empty py-4"><i class="fa-solid fa-wifi d-block mb-2 opacity-50"></i>Error de conexión</div>');
                });
        }

        if (dropdownEl && typeof bootstrap !== 'undefined') {
            var bsDropdown = bootstrap.Dropdown.getInstance(notifToggle) || new bootstrap.Dropdown(notifToggle || document.getElementById('navbarNotifToggle'));
            var dropdownContainer = notifToggle ? notifToggle.closest('.dropdown') : null;
            (dropdownContainer || dropdownEl).addEventListener('show.bs.dropdown', function(){ cargarNotificaciones(); });
            var parentLi = notifToggle ? notifToggle.closest('.dropdown') : null;
            if (parentLi) {
                parentLi.addEventListener('show.bs.dropdown', function(){ stopNotifSound(); });
            }
        }

        if (btnMarcarTodas) {
            btnMarcarTodas.addEventListener('click', function(e){
                e.preventDefault();
                e.stopPropagation();
                fetch(notifUrl('/notificaciones/marcarTodasLeidas'), { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: '{}', credentials: 'same-origin' })
                    .then(function(r){ return r.json(); })
                    .then(function(res){ if (res.success) cargarNotificaciones(); });
            });
        }

        document.addEventListener('DOMContentLoaded', function(){
            actualizarSoloBadge();
            setInterval(actualizarSoloBadge, 60000);
        });
    })();
    </script>

    <!-- Easter egg: triple clic en logo del sidebar → video Spartan -->
    <script>
    (function(){
        var logo = document.getElementById('sidebarLogoEaster');
        var modalEl = document.getElementById('modalSpartanVideo');
        var videoEl = document.getElementById('spartanVideoPlayer');
        if (!logo || !modalEl || !videoEl) return;
        var clickCount = 0;
        var resetTimer = null;
        var navTimer = null;

        function playSpartanSound() {
            try {
                var ctx = new (window.AudioContext || window.webkitAudioContext)();
                var osc = ctx.createOscillator();
                var gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.frequency.setValueAtTime(523.25, ctx.currentTime);
                osc.frequency.exponentialRampToValueAtTime(784, ctx.currentTime + 0.08);
                gain.gain.setValueAtTime(0.15, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.15);
                osc.start(ctx.currentTime);
                osc.stop(ctx.currentTime + 0.15);
            } catch (e) {}
        }

        function burstAt(x, y) {
            var wrap = document.createElement('div');
            wrap.className = 'spartan-burst';
            var angles = [0, 45, 90, 135, 180, 225, 270, 315, 22, 67, 112, 202, 248];
            for (var i = 0; i < 12; i++) {
                var a = (angles[i] || i * 30) * Math.PI / 180;
                var dist = 50 + Math.random() * 70;
                var tx = Math.cos(a) * dist + 'px';
                var ty = Math.sin(a) * dist + 'px';
                var dot = document.createElement('span');
                dot.className = 'spartan-burst-dot';
                dot.style.left = x + 'px';
                dot.style.top = y + 'px';
                dot.style.setProperty('--tx', tx);
                dot.style.setProperty('--ty', ty);
                wrap.appendChild(dot);
            }
            document.body.appendChild(wrap);
            setTimeout(function(){ if (wrap.parentNode) wrap.parentNode.removeChild(wrap); }, 700);
        }

        logo.addEventListener('click', function(e){
            e.preventDefault();
            clickCount++;
            if (clickCount === 3) {
                clickCount = 0;
                if (resetTimer) clearTimeout(resetTimer);
                if (navTimer) clearTimeout(navTimer);
                burstAt(e.clientX, e.clientY);
                playSpartanSound();
                setTimeout(function(){
                    var modal = typeof bootstrap !== 'undefined' && bootstrap.Modal ? new bootstrap.Modal(modalEl) : null;
                    if (modal) modal.show();
                    videoEl.currentTime = 0;
                    videoEl.play();
                }, 220);
                return;
            }
            if (resetTimer) clearTimeout(resetTimer);
            resetTimer = setTimeout(function(){ clickCount = 0; }, 600);
            if (navTimer) clearTimeout(navTimer);
            if (clickCount === 1) navTimer = setTimeout(function(){
                if (clickCount === 1) window.location.href = '/Inicio';
            }, 400);
        });
        modalEl.addEventListener('hidden.bs.modal', function(){ videoEl.pause(); });
    })();
    </script>

    <!-- Easter egg: Konami code (↑↑↓↓←→←→BA) → confetti + mensaje + sonido -->
    <script>
    (function(){
        var konamiCodes = [38, 38, 40, 40, 37, 39, 37, 39, 66, 65];
        var konamiKeys = ['ArrowUp', 'ArrowUp', 'ArrowDown', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'ArrowLeft', 'ArrowRight', 'b', 'a'];
        var idx = 0;
        document.addEventListener('keydown', function(e){
            var keyOk = (e.keyCode === konamiCodes[idx]) || (e.key === konamiKeys[idx]);
            if (keyOk) {
                idx++;
                if (idx === konamiCodes.length) {
                    idx = 0;
                    konamiTrigger();
                }
            } else {
                idx = 0;
            }
        });
        function konamiTrigger(){
            if (document.getElementById('konamiConfettiWrap')) return;
            document.body.classList.add('konami-shake');
            setTimeout(function(){ document.body.classList.remove('konami-shake'); }, 450);
            var overlay = document.createElement('div');
            overlay.className = 'konami-overlay';
            overlay.id = 'konamiOverlay';
            document.body.appendChild(overlay);
            setTimeout(function(){ overlay.style.animation = 'konamiOverlayOut 0.8s ease forwards'; setTimeout(function(){ if (overlay.parentNode) overlay.parentNode.removeChild(overlay); }, 850); }, 3200);
            var mexicanColors = ['#006847', '#ffffff', '#ce1126', '#b45309', '#fbbf24', '#d97706', '#f59e0b', '#fef3c7', '#92400e', '#fcd34d', '#f97316'];
            var wrap = document.createElement('div');
            wrap.id = 'konamiConfettiWrap';
            wrap.style.cssText = 'position:fixed;inset:0;pointer-events:none;z-index:10003;overflow:hidden;';
            for (var i = 0; i < 320; i++) {
                var p = document.createElement('div');
                p.style.cssText = 'position:absolute;width:' + (10 + Math.random() * 12) + 'px;height:' + (10 + Math.random() * 12) + 'px;left:' + (Math.random() * 100) + '%;top:-40px;background:' + mexicanColors[Math.floor(Math.random() * mexicanColors.length)] + ';border-radius:3px;animation:konamiFall ' + (2.4 + Math.random() * 2.2) + 's linear forwards;animation-delay:' + (Math.random() * 0.8) + 's;';
                wrap.appendChild(p);
            }
            var calaveraEmojis = ['💀', '🇲🇽', '🎉', '⚔', '💀', '🇲🇽', '🔥', '💀'];
            for (var c = 0; c < 50; c++) {
                var cp = document.createElement('div');
                cp.style.cssText = 'position:absolute;left:' + (Math.random() * 100) + '%;top:-50px;font-size:' + (18 + Math.random() * 22) + 'px;animation:konamiFall ' + (2.8 + Math.random() * 2) + 's linear forwards;animation-delay:' + (Math.random() * 1) + 's;';
                cp.textContent = calaveraEmojis[Math.floor(Math.random() * calaveraEmojis.length)];
                wrap.appendChild(cp);
            }
            var fireworkColors = ['#006847', '#ce1126', '#fbbf24', '#ffffff', '#f59e0b', '#ff6b35', '#fbbf24', '#22c55e'];
            var fwPositions = [0.05, 0.18, 0.32, 0.5, 0.68, 0.82, 0.95, 0.12, 0.25, 0.42, 0.58, 0.75, 0.88, 0.22, 0.48, 0.72];
            for (var f = 0; f < 16; f++) {
                var fx = fwPositions[f] * 100;
                var fy = 8 + Math.random() * 18;
                var fwWrap = document.createElement('div');
                fwWrap.style.cssText = 'position:absolute;left:' + fx + '%;top:' + fy + '%;width:0;height:0;pointer-events:none;';
                var numRays = 48 + Math.floor(Math.random() * 24);
                var distBase = 140 + Math.random() * 100;
                for (var r = 0; r < numRays; r++) {
                    var angle = (r / numRays) * Math.PI * 2 + Math.random() * 0.6;
                    var dist = distBase + Math.random() * 80;
                    var tx = Math.cos(angle) * dist + 'px';
                    var ty = Math.sin(angle) * dist - 40 + 'px';
                    var dot = document.createElement('div');
                    dot.className = 'konami-firework';
                    dot.style.cssText = 'left:0;top:0;background:' + fireworkColors[Math.floor(Math.random() * fireworkColors.length)] + ';color:' + fireworkColors[Math.floor(Math.random() * fireworkColors.length)] + ';animation:konamiFireworkBurst ' + (1.6 + Math.random() * 0.6) + 's ease-out ' + (f * 0.12) + 's forwards;--fw-tx:' + tx + ';--fw-ty:' + ty + ';';
                    fwWrap.appendChild(dot);
                }
                wrap.appendChild(fwWrap);
            }
            document.body.appendChild(wrap);
            setTimeout(function(){ if (wrap.parentNode) wrap.parentNode.removeChild(wrap); }, 6500);
            var swordsWrap = document.createElement('div');
            swordsWrap.className = 'konami-swords-wrap';
            swordsWrap.innerHTML = '<span class="konami-sword konami-sword-left" aria-hidden="true">⚔</span><span class="konami-sword konami-sword-right" aria-hidden="true">⚔</span>';
            document.body.appendChild(swordsWrap);
            setTimeout(function(){ if (swordsWrap.parentNode) swordsWrap.parentNode.removeChild(swordsWrap); }, 1800);
            var msg = document.createElement('div');
            msg.className = 'konami-message';
            msg.innerHTML = '<span class="konami-calaveras">💀 💀 💀</span>¡BIENVENIDO, ESPARTANO!<br><span style="font-size:0.5em;opacity:0.95;letter-spacing:0.15em;">🇲🇽 ¡ARRIBA MÉXICO! 🇲🇽</span>';
            document.body.appendChild(msg);
            setTimeout(function(){ msg.classList.add('konami-visible'); }, 350);
            setTimeout(function(){ msg.style.animation = 'konamiMessageOut 0.6s ease forwards'; msg.classList.remove('konami-visible'); }, 3600);
            setTimeout(function(){ if (msg.parentNode) msg.parentNode.removeChild(msg); }, 4300);
            var spartaAudio = new Audio('/assets/audio/thisissparta.swf.mp3');
            spartaAudio.volume = 0.9;
            spartaAudio.play().catch(function(){});
        }
    })();
    </script>

    <!-- Easter egg: triple clic en avatar del navbar → mensaje oculto -->
    <script>
    (function(){
        var avatar = document.getElementById('navbarAvatarEaster');
        if (!avatar) return;
        var clickCount = 0;
        var resetTimer = null;
        avatar.addEventListener('click', function(e){
            clickCount++;
            if (clickCount === 3) {
                clickCount = 0;
                if (resetTimer) clearTimeout(resetTimer);
                var toast = document.createElement('div');
                toast.className = 'avatar-easter-toast';
                var gifUrl = 'https://media.tenor.com/hGkcP-O1iFwAAAAM/awoo-awoo-300.gif';
                toast.innerHTML = '<img class="avatar-easter-gif" src="' + gifUrl + '" alt="Spartans" /><span>¡Eres parte del equipo!</span>';
                document.body.appendChild(toast);
                var gritoAudio = new Audio('/assets/audio/grito-guerra-.mp3');
                gritoAudio.play().catch(function(){});
                setTimeout(function(){
                    toast.style.animation = 'avatarEasterOut 0.4s ease forwards';
                    setTimeout(function(){ if (toast.parentNode) toast.parentNode.removeChild(toast); }, 400);
                }, 3000);
                return;
            }
            if (resetTimer) clearTimeout(resetTimer);
            resetTimer = setTimeout(function(){ clickCount = 0; }, 600);
        });
    })();
    </script>

    <!-- Dark Mode Script -->
    <script>
        // Función para aplicar el dark mode (iconos modernos: sol = modo claro, luna = modo oscuro)
        function applyDarkMode(isDark) {
            const body = document.body;
            const icon = document.getElementById('darkModeIcon');
            const text = document.getElementById('darkModeText');
            const toggle = document.getElementById('darkModeToggle');

            if (isDark) {
                document.documentElement.classList.add('dark-mode');
                body.classList.add('dark-mode');
                if (icon) { icon.className = 'fa-solid fa-sun dark-mode-icon'; }
                if (text) text.textContent = 'Apariencia (modo claro)';
                if (toggle) toggle.classList.add('active-dark');

                fixInlineStyles();
            } else {
                document.documentElement.classList.remove('dark-mode');
                body.classList.remove('dark-mode');
                if (icon) { icon.className = 'fa-solid fa-moon dark-mode-icon'; }
                if (text) text.textContent = 'Apariencia (modo oscuro)';
                if (toggle) toggle.classList.remove('active-dark');

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
                    el.style.backgroundColor = '#1e293b';
                    el.style.background = '#1e293b';
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
                    el.style.backgroundColor = '#1e293b';
                    el.style.color = '#e0e0e0';
                } else if (el.classList.contains('offcanvas-header')) {
                    el.style.backgroundColor = '#334155';
                    el.style.color = '#ffffff';
                    el.style.borderBottomColor = '#3a3a3a';
                } else if (el.classList.contains('offcanvas-body')) {
                    el.style.backgroundColor = '#1e293b';
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
                        newStyle += '; background: #1e293b !important;';
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
                    el.style.backgroundColor = '#334155';
                } else if (el.classList.contains('accordion-item') || el.classList.contains('accordion-collapse')) {
                    el.style.backgroundColor = '#1e293b';
                    el.style.borderColor = '#3a3a3a';
                } else {
                    el.style.color = '#e0e0e0';
                    el.style.backgroundColor = '#1e293b';
                }
            });

            // Forzar color en dropdowns
            const dropdowns = document.querySelectorAll('.dropdown-menu, .dropdown-item');
            dropdowns.forEach(el => {
                el.style.backgroundImage = 'none';
                if (el.classList.contains('dropdown-menu')) {
                    el.style.backgroundColor = '#1e293b';
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
                document.documentElement.classList.add('dark-mode');
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
