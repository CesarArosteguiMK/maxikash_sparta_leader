<?php
/**
 * Estructura del menú lateral (misma jerarquía que View::getMenu).
 * Usada para agrupar módulos en "Administrar puestos y módulos del usuario".
 */
declare(strict_types=1);

if (!function_exists('getMenuSidebarModulosStructure')) {
    /**
     * @return array<string, array{icono: string, subItems: list<array{label: string, url: string, modulos: list<int>}>}>
     */
    function getMenuSidebarModulosStructure(): array
    {


        return [
            'Créditos' => [
                'icono' => 'fa-solid fa-sack-dollar',
                'subItems' => [
                    ['label' => 'Estados de Cuenta', 'url' => '/estadocuenta/consulta', 'modulos' => [1]],
                    ['label' => 'Documentación', 'url' => '/estadocuenta/documentacion', 'modulos' => [2]],
                    ['label' => 'Histórico Gestiones', 'url' => '/gestiones/seguimiento', 'modulos' => [3]],
                ],
            ],
            'Capital Humano' => [
                'icono' => 'fa-solid fa-users',
                'subItems' => [
                    ['label' => 'Gestión', 'url' => '/caphum/gestion', 'modulos' => [4]],
                    ['label' => 'Revisión RR.HH.', 'url' => '/caphum/actualizacionesInfo', 'modulos' => [83]],
                    ['label' => 'Organigrama Cobranza', 'url' => '/caphum/organigrama', 'modulos' => [5]],
                    ['label' => 'EST Organizacional', 'url' => '/caphum/estructuraOrganizacional', 'modulos' => [86]],
                    ['label' => 'Control de Bajas', 'url' => '/caphum/bajas', 'modulos' => [13]],
                    ['label' => 'Selección de Personal', 'url' => '/caphum/candidatos', 'modulos' => [42]],
                    ['label' => 'Curso Onboarding', 'url' => '/onboarding/index', 'modulos' => [44]],
                    ['label' => 'Reportes de Personal', 'url' => '/analitica/reporteCapitalHumano', 'modulos' => [34]],
                    ['label' => 'Estadísticas', 'url' => '/caphum/estadisticas', 'modulos' => [38]],
                ],
            ],
            'Analítica' => [
                'icono' => 'fa-solid fa-chart-line',
                'subItems' => [
                    ['label' => 'Primeros pagos', 'url' => '/analitica/PrimerosPagos', 'modulos' => [49, 65, 66, 67, 68]],
                    ['label' => 'Cartera actual', 'url' => '/analitica/carteraactual', 'modulos' => [19]],
                    ['label' => 'Call Center', 'url' => '/analitica/callcenter', 'modulos' => [6]],
                    // ❌ Se eliminó Sabuesos para evitar conflicto con Tickets
                    ['label' => 'Layout Legacy', 'url' => '/analitica/layoutlegacy', 'modulos' => [7]],
                    ['label' => 'Analítica sabueso', 'url' => '/sabueso/estadisticas', 'modulos' => [47]],
                    ['label' => 'Comparativas', 'url' => '/analitica/comparativas', 'modulos' => [60, 81]],
                    ['label' => 'Avance Bucket', 'url' => '/analitica/avanceBucket', 'modulos' => [77]],
                ],
            ],
            'Tickets' => [
                'icono' => 'fa-solid fa-ticket',
                'subItems' => [
                    [
                        'label' => 'Mis Tickets',
                        'url' => '/sabueso/ticket',
                        'modulos' => [18],
                    ],
                    [
                        'label' => 'Panel Admin',
                        'url' => '/sabueso/panelAdminInicio',
                        'modulos' => [25, 27],
                    ],
                    [
                        'label' => 'Cerrado/Eliminado Sabueso',
                        'url' => '/sabueso/cerradoEliminado',
                        'modulos' => [48],
                    ],
                ],
            ],
            'Convenios' => [
                'icono' => 'fa-solid fa-building-columns',
                'subItems' => [
                    ['label' => 'Asignación de Créditos', 'url' => '/Despachos/AsignacionCreditosDespacho', 'modulos' => [20]],
                    ['label' => 'Mi Cartera', 'url' => '/Despachos/MiGestion', 'modulos' => [45]],
                    ['label' => 'Crear Convenio', 'url' => '/convenios/consulta', 'modulos' => [46]],
                    ['label' => 'Cierre de Crédito', 'url' => '/CierreCredito/consulta', 'modulos' => [51]],
                    ['label' => 'Estadísticas', 'url' => '/convenios/estadisticas', 'modulos' => [56]],
                ],
            ],
            'Motos Adjudicadas' => [
                'icono' => 'fa-solid fa-motorcycle',
                'subItems' => [
                    ['label' => 'Administracion', 'url' => '/Adjudicacion/administracion', 'modulos' => [62, 80]],
                    ['label' => 'Operaciones', 'url' => '/MotosAdjudicadas/pipeline', 'modulos' => [63]],
                    ['label' => 'Monitoreo', 'url' => '/MotosAdjudicadas/monitoreoAdjudicaciones', 'modulos' => [76]],
                    ['label' => 'Consulta REPUVE', 'url' => '/MotosAdjudicadas/repuveConsulta', 'modulos' => [78]],
                    ['label' => '1.- Evidencias', 'url' => '/AtencionClientes/evidencias', 'modulos' => [70]],
                    ['label' => '2.- Recuperación', 'url' => '/AtencionClientes/recuperacion', 'modulos' => [71]],
                    ['label' => '3.-Cartera', 'url' => '/AtencionClientes/cierreDocumentacion', 'modulos' => [72]],
                    ['label' => '4.- Recepción', 'url' => '/AtencionClientes/recepcion', 'modulos' => [73]],
                    ['label' => 'Retenciones', 'url' => '/AtencionClientes/consulta', 'modulos' => [69]],
                    ['label' => 'Tracking Recolección', 'url' => '/TrackingRecoleccion/index', 'modulos' => [74]],
                ],
            ],
            'Direcciones' => [
                'icono' => 'fa-solid fa-route',
                'subItems' => [
                    ['label' => 'Direcciones', 'url' => '/analitica/asignacion', 'modulos' => [84]],
                ],
            ],
            'Legacy Notificaciones' => [
                'icono' => 'fa-solid fa-bullhorn',
                'subItems' => [
                    ['label' => 'Campañas', 'url' => '/MotosAdjudicadas/campaniaNotificacionLegacy', 'modulos' => [64]],
                ],
            ],
            'Gastos Cobranza' => [
                'icono' => 'fa-solid fa-file-invoice-dollar',
                'subItems' => [
                    ['label' => 'Historial condonaciones', 'url' => '/condonaciones/historial', 'modulos' => [15, 39]],
                    ['label' => 'Estadísticas', 'url' => '/gastoscobranza/estadisticagc', 'modulos' => [40]],
                ],
            ],
            'Organización' => [
                'icono' => 'fa-solid fa-cog',
                'subItems' => [
                    ['label' => 'Áreas', 'url' => '/departamentos/consulta/', 'modulos' => [10]],
                    ['label' => 'Países', 'url' => '/paises/consulta', 'modulos' => [41]],
                    ['label' => 'Equivalencia puestos', 'url' => '/equivalencias/consulta', 'modulos' => [17]],
                    ['label' => 'Asignación por puestos', 'url' => '/configticketpuesto/consulta', 'modulos' => [26]],
                ],
            ],
            'Servicios' => [
                'icono' => 'fa-solid fa-laptop',
                'subItems' => [
                    ['label' => 'Segundometro', 'url' => '/segundometro/shell', 'modulos' => [16]],
                    ['label' => 'Gastos Cobranza', 'url' => '/gastoscobranza/shell', 'modulos' => [31]],
                    ['label' => 'Cartera', 'url' => '/gastoscobranza/shellcartera', 'modulos' => [50]],
                ],
            ],
        ];
    }
}

if (!function_exists('mapModuloWebIdToSidebarMeta')) {
    /**
     * Primera coincidencia en el orden del menú gana (p. ej. mismo id en varias entradas).
     *
     * @return array{menu_grupo: string, menu_grupo_icono: string, menu_grupo_orden: int, menu_item_label: string, menu_item_orden: int}|null
     */
    function mapModuloWebIdToSidebarMeta(int $moduloWebId): ?array
    {
        static $cache = null;
        if ($cache === null) {
            $cache = [];
            $grupoOrden = 0;
            foreach (getMenuSidebarModulosStructure() as $grupoNombre => $def) {
                $icono = $def['icono'] ?? 'fa-solid fa-folder';
                $subOrden = 0;
                foreach ($def['subItems'] ?? [] as $sub) {
                    foreach ($sub['modulos'] ?? [] as $mid) {
                        $mid = (int) $mid;
                        if ($mid <= 0 || isset($cache[$mid])) {
                            continue;
                        }
                        $cache[$mid] = [
                            'menu_grupo' => $grupoNombre,
                            'menu_grupo_icono' => $icono,
                            'menu_grupo_orden' => $grupoOrden,
                            'menu_item_label' => (string) ($sub['label'] ?? ''),
                            'menu_item_orden' => $subOrden,
                        ];
                    }
                    $subOrden++;
                }
                $grupoOrden++;
            }
        }
        return $cache[$moduloWebId] ?? null;
    }
}

if (!function_exists('getMenuSidebarGrupoBaseMeta')) {
    /**
     * Metadatos de agrupación (sin ítem) para un grupo del menú lateral.
     *
     * @return array{menu_grupo: string, menu_grupo_icono: string, menu_grupo_orden: int}|null
     */
    function getMenuSidebarGrupoBaseMeta(string $grupoNombre): ?array
    {
        $orden = 0;
        foreach (getMenuSidebarModulosStructure() as $gn => $def) {
            if ($gn === $grupoNombre) {
                return [
                    'menu_grupo' => $gn,
                    'menu_grupo_icono' => (string) ($def['icono'] ?? 'fa-solid fa-folder'),
                    'menu_grupo_orden' => $orden,
                ];
            }
            $orden++;
        }

        return null;
    }
}

if (!function_exists('mapMetaDesdeAnclaModuloMenu')) {
    /**
     * Metadatos a partir del ítem de menú ancla (p. ej. módulo web 1 = Estados de cuenta).
     *
     * @param bool $agruparPorSubmodulo Si es true, la tarjeta del modal se titula con el **submódulo**
     *        (p. ej. «Estados de Cuenta», «Documentación») y no con el módulo padre («Créditos»).
     *        `menu_grupo_orden` pasa a ser un índice global (módulo_padre × 1000 + ítem) para ordenar tarjetas.
     *
     * @return array{menu_grupo: string, menu_grupo_icono: string, menu_grupo_orden: int, menu_item_label: string, menu_item_orden: int}|null
     */
    function mapMetaDesdeAnclaModuloMenu(int $anclaModuloId, string $itemLabel, int $itemOrden, bool $agruparPorSubmodulo = false): ?array
    {
        $base = mapModuloWebIdToSidebarMeta($anclaModuloId);
        if ($base === null) {
            return null;
        }
        $label = trim($itemLabel) !== '' ? trim($itemLabel) : (string) ($base['menu_item_label'] ?? 'Módulo');
        $gOrden = (int) ($base['menu_grupo_orden'] ?? 999);
        $iOrden = (int) ($base['menu_item_orden'] ?? 999);
        $subEtiqueta = trim((string) ($base['menu_item_label'] ?? ''));

        if ($agruparPorSubmodulo && $subEtiqueta !== '') {
            return [
                'menu_grupo' => $subEtiqueta,
                'menu_grupo_icono' => (string) ($base['menu_grupo_icono'] ?? 'fa-solid fa-folder'),
                'menu_grupo_orden' => ($gOrden * 1000) + max(0, min(999, $iOrden)),
                'menu_item_label' => $label,
                'menu_item_orden' => $itemOrden,
            ];
        }

        return [
            'menu_grupo' => $base['menu_grupo'],
            'menu_grupo_icono' => $base['menu_grupo_icono'],
            'menu_grupo_orden' => $gOrden,
            'menu_item_label' => $label,
            'menu_item_orden' => $itemOrden,
        ];
    }
}

if (!function_exists('mapPermisoEspecialToMenuMeta')) {
    /**
     * Ubica permisos especiales bajo el **módulo padre** del menú lateral (Créditos, Capital Humano,
     * Convenios, Analítica, …), no bajo el submenú (p. ej. «Estados de Cuenta»), para el modal de perfiles.
     *
     * Ids alineados con EstadoCuenta, Reporteria, Convenios, CapHum, etc.
     */
    function mapPermisoEspecialToMenuMeta(int $mid, string $pestana, string $nombreRaw): ?array
    {
        if (strcasecmp(trim($pestana), 'Permisos especiales') !== 0) {
            return null;
        }
        $nombreRaw = trim(preg_replace('/\x{00A0}/u', ' ', str_replace("\xc2\xa0", ' ', trim((string) $nombreRaw))));
        /** modulo_web del menú ancla => orden dentro del grupo padre */
        static $anclas = [
            // Anclas 1 y 2 → tarjeta «Créditos»
            23 => [1, 110],
            29 => [1, 120],
            30 => [1, 130],
            35 => [1, 140],
            36 => [1, 150],
            37 => [1, 160],
            21 => [2, 210],
            22 => [2, 220],
            24 => [2, 230],
            // Analítica — Primeros pagos
            33 => [49, 110],
            65 => [49, 120],
            66 => [49, 130],
            67 => [49, 140],
            68 => [49, 150],
            // Convenios — Crear convenio
            32 => [46, 110],
            // Capital Humano — Gestión
            43 => [4, 110],
            82 => [4, 115],
            // Motos Adjudicadas - 1.- Evidencias
            79 => [70, 710],
            // Cierre de crédito (ancla 51 vía nombre): tarjeta «Convenios»
        ];
        if (isset($anclas[$mid])) {
            [$ancla, $orden] = $anclas[$mid];

            return mapMetaDesdeAnclaModuloMenu($ancla, $nombreRaw, $orden, false);
        }
        $nb = mb_strtolower(trim($nombreRaw), 'UTF-8');
        $nbNorm = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ü', 'ñ'],
            ['a', 'e', 'i', 'o', 'u', 'u', 'n'],
            $nb
        );
        if ($nbNorm === '') {
            return null;
        }
        // Cierre de crédito (ancla módulo 51 → grupo «Convenios»): orden 401 / 411 / 421 / 431
        $excluyeCrearConvenio = str_contains($nbNorm, 'crear convenio')
            || str_contains($nbNorm, 'registrar convenio');
        $tNorm = preg_replace('/\s+/u', ' ', trim($nbNorm));
        // 1. Convenio (pestaña Cierre de crédito): título «Convenio(s)» o cualquier texto que empiece por esa palabra
        if (!$excluyeCrearConvenio && preg_match('/\bconvenios?\b/u', $nbNorm) === 1) {
            if (
                $tNorm === 'convenio'
                || $tNorm === 'convenios'
                || preg_match('/^convenios?\b/u', $tNorm) === 1
            ) {
                return mapMetaDesdeAnclaModuloMenu(51, $nombreRaw, 401, false);
            }
        }
        // 2. Validación de cierre
        if (str_contains($nbNorm, 'validaci') && str_contains($nbNorm, 'cierre')) {
            return mapMetaDesdeAnclaModuloMenu(51, $nombreRaw, 411, false);
        }
        // 3. En proceso
        if ($tNorm === 'en proceso' || (preg_match('/\ben\s+proceso\b/u', $nbNorm) !== 0
                && (str_contains($nbNorm, 'cierre') || str_contains($nbNorm, 'credito')))) {
            return mapMetaDesdeAnclaModuloMenu(51, $nombreRaw, 421, false);
        }
        // 4. Historial
        if ($tNorm === 'historial' || (preg_match('/\bhistorial\b/u', $nbNorm) !== 0
                && (str_contains($nbNorm, 'cierre') || str_contains($nbNorm, 'credito')))) {
            return mapMetaDesdeAnclaModuloMenu(51, $nombreRaw, 431, false);
        }
        // Otros textos de cierre de crédito
        if (str_contains($nbNorm, 'cierre') && str_contains($nbNorm, 'credito')) {
            return mapMetaDesdeAnclaModuloMenu(51, $nombreRaw, 450, false);
        }

        return null;
    }
}

if (!function_exists('enriquecerPerfilesModulosConMenuSidebar')) {
    /**
     * Añade campos menu_* y ordena como el menú lateral.
     *
     * @param list<array<string, mixed>> $perfiles
     * @return list<array<string, mixed>>
     */
    function enriquecerPerfilesModulosConMenuSidebar(array $perfiles): array
    {
        foreach ($perfiles as &$p) {
            $mid = (int) ($p['modulo_id'] ?? 0);
            $pestana = trim((string) ($p['pestana'] ?? ''));
            $nombreRaw = trim((string) ($p['modulo_nombre'] ?? ''));

            // Permisos especiales: resolver primero por id/nombre (p. ej. «Convenio» → Cierre de crédito), no por mapa genérico de módulo
            $meta = null;
            if ($mid === 60) {
                $meta = mapMetaDesdeAnclaModuloMenu(60, $nombreRaw !== '' ? $nombreRaw : 'Avance semanal vs semanas pasadas', 600, false);
            } elseif ($mid === 81) {
                $meta = mapMetaDesdeAnclaModuloMenu(60, $nombreRaw !== '' ? $nombreRaw : 'Semana actual vs semana pasada', 610, false);
            } elseif ($mid === 62) {
                $meta = mapMetaDesdeAnclaModuloMenu(62, $nombreRaw !== '' ? $nombreRaw : 'Admin Cobranza', 620, false);
            } elseif ($mid === 80) {
                $meta = mapMetaDesdeAnclaModuloMenu(62, $nombreRaw !== '' ? $nombreRaw : 'Dictaminar creditos', 630, false);
            }
            // Cierre de crédito — células / cartera (ids 56–59 y/o nombre «Cierre: Despachos»…): tarjeta «Convenios»; pestaña Permisos especiales en CapHum
            $nbCierreCel = mb_strtolower(preg_replace('/\s+/u', ' ', trim($nombreRaw)), 'UTF-8');
            $pareceCierreCelulaPorNombre = $nbCierreCel !== ''
                && str_contains($nbCierreCel, 'cierre')
                && (str_contains($nbCierreCel, 'despachos')
                    || str_contains($nbCierreCel, 'call center')
                    || str_contains($nbCierreCel, 'cartera'));
            $cierreCelulaPorId = $mid > 0 && in_array($mid, [56, 57, 59], true);
            if ($cierreCelulaPorId || $pareceCierreCelulaPorNombre) {
                $ordenCierreCelula = [56 => 801, 57 => 802, 59 => 803];
                $orden = $ordenCierreCelula[$mid] ?? 805;
                if (!$cierreCelulaPorId) {
                    if (str_contains($nbCierreCel, 'despachos')) {
                        $orden = 801;
                    } elseif (str_contains($nbCierreCel, 'call center')) {
                        $orden = 802;
                    } elseif (str_contains($nbCierreCel, 'cartera')) {
                        $orden = 803;
                    }
                }
                $meta = mapMetaDesdeAnclaModuloMenu(
                    51,
                    $nombreRaw !== '' ? $nombreRaw : 'Cierre de crédito',
                    $orden,
                    false
                );
                if ($meta === null) {
                    $baseConv = getMenuSidebarGrupoBaseMeta('Convenios');
                    if ($baseConv !== null) {
                        $meta = array_merge($baseConv, [
                            'menu_item_label' => $nombreRaw !== '' ? $nombreRaw : 'Cierre de crédito',
                            'menu_item_orden' => $orden,
                        ]);
                    }
                }
            }
            if ($meta === null && $mid > 0 && strcasecmp(trim($pestana), 'Permisos especiales') === 0) {
                $meta = mapPermisoEspecialToMenuMeta($mid, $pestana, $nombreRaw);
            }
            if ($meta === null && $mid > 0) {
                $meta = mapModuloWebIdToSidebarMeta($mid);
            }
            if ($meta === null && $mid > 0) {
                $nb = mb_strtolower($nombreRaw, 'UTF-8');
                $nbNorm = str_replace(
                    ['á', 'é', 'í', 'ó', 'ú', 'ü', 'ñ'],
                    ['a', 'e', 'i', 'o', 'u', 'u', 'n'],
                    $nb
                );
                $baseAnalitica = getMenuSidebarGrupoBaseMeta('Analítica');
                if ($baseAnalitica !== null && str_contains($nbNorm, 'bono') && str_contains($nbNorm, 'cobranza')) {
                    $meta = array_merge($baseAnalitica, [
                        'menu_item_label' => $nombreRaw !== '' ? $nombreRaw : 'Bonos cobranza',
                        'menu_item_orden' => 998,
                    ]);
                } elseif (str_contains($nbNorm, 'cierre')
                    && (str_contains($nbNorm, 'credito')
                        || str_contains($nbNorm, 'despachos')
                        || str_contains($nbNorm, 'call center')
                        || str_contains($nbNorm, 'cartera'))) {
                    $meta = mapMetaDesdeAnclaModuloMenu(51, $nombreRaw !== '' ? $nombreRaw : 'Cierre de Crédito', 998, false);
                    if ($meta === null) {
                        $baseConv2 = getMenuSidebarGrupoBaseMeta('Convenios');
                        if ($baseConv2 !== null) {
                            $meta = array_merge($baseConv2, [
                                'menu_item_label' => $nombreRaw !== '' ? $nombreRaw : 'Cierre de Crédito',
                                'menu_item_orden' => 998,
                            ]);
                        }
                    }
                }
            }
            if ($meta !== null) {
                $p['menu_grupo'] = $meta['menu_grupo'];
                $p['menu_grupo_icono'] = $meta['menu_grupo_icono'];
                $p['menu_grupo_orden'] = $meta['menu_grupo_orden'];
                $p['menu_item_label'] = $meta['menu_item_label'];
                $p['menu_item_orden'] = $meta['menu_item_orden'];
            } else {
                $p['menu_grupo'] = 'Otros';
                $p['menu_grupo_icono'] = 'fa-solid fa-diagram-project';
                $p['menu_grupo_orden'] = 999;
                $p['menu_item_label'] = (string) ($p['modulo_nombre'] ?? 'Módulo');
                $p['menu_item_orden'] = 999;
            }
        }
        unset($p);

        usort(
            $perfiles,
            static function (array $a, array $b): int {
                $ga = (int) ($a['menu_grupo_orden'] ?? 999);
                $gb = (int) ($b['menu_grupo_orden'] ?? 999);
                if ($ga !== $gb) {
                    return $ga <=> $gb;
                }
                $ia = (int) ($a['menu_item_orden'] ?? 999);
                $ib = (int) ($b['menu_item_orden'] ?? 999);
                if ($ia !== $ib) {
                    return $ia <=> $ib;
                }
                return ((int) ($a['modulo_id'] ?? 0)) <=> ((int) ($b['modulo_id'] ?? 0));
            }
        );

        return $perfiles;
    }
}
