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
        $ticketSubItems = [
            [
                'label' => 'Ticket',
                'url' => '/sabueso/ticket',
                'modulos' => [18],
            ],
            [
                'label' => 'Panel Admin',
                'url' => '/sabueso/panelAdminInicio',
                'modulos' => [19, 25, 27],
            ],
            [
                'label' => 'Cerrado/Eliminado Sabueso',
                'url' => '/sabueso/cerradoEliminado',
                'modulos' => [48],
            ],
            [
                'label' => 'Estadísticas',
                'url' => '/sabueso/estadisticas',
                'modulos' => [47],
            ],
        ];

        return [
            'Créditos' => [
                'icono' => 'fa-solid fa-sack-dollar',
                'subItems' => [
                    ['label' => 'Estados de Cuenta', 'url' => '/estadocuenta/consulta', 'modulos' => [1]],
                    ['label' => 'Documentación', 'url' => '/estadocuenta/documentacion', 'modulos' => [2]],
                ],
            ],
            'Gestiones' => [
                'icono' => 'fa-solid fa-screwdriver-wrench',
                'subItems' => [
                    ['label' => 'Histórico Gestiones', 'url' => '/gestiones/seguimiento', 'modulos' => [3]],
                ],
            ],
            'Capital Humano' => [
                'icono' => 'fa-solid fa-users',
                'subItems' => [
                    ['label' => 'Gestión', 'url' => '/caphum/gestion', 'modulos' => [4]],
                    ['label' => 'Candidatos', 'url' => '/caphum/candidatos', 'modulos' => [42]],
                    ['label' => 'Bajas', 'url' => '/caphum/bajas', 'modulos' => [13]],
                    ['label' => 'Organigrama', 'url' => '/caphum/organigrama', 'modulos' => [5]],
                ],
            ],
            'Reportería' => [
                'icono' => 'fa-solid fa-file',
                'subItems' => [
                    ['label' => 'Call Center', 'url' => '/reporteria/callcenter', 'modulos' => [6, 14, 15]],
                    ['label' => 'Primeros pagos', 'url' => '/reporteria/PrimerosPagos', 'modulos' => [49]],
                    ['label' => 'Sabuesos', 'url' => '/reporteria/sabuesos', 'modulos' => [18, 19]],
                    ['label' => 'Layout Legacy', 'url' => '/reporteria/layoutlegacy', 'modulos' => [7]],
                    ['label' => 'Capital Humano', 'url' => '/reporteria/reporteCapitalHumano', 'modulos' => [21]],
                    ['label' => 'Flujo cobranza', 'url' => '/ReporteriaBI/FlujoCobranza', 'modulos' => [50]],
                ],
            ],
            'Ticket' => [
                'icono' => 'fa-solid fa-ticket',
                'subItems' => $ticketSubItems,
            ],
            'Convenios' => [
                'icono' => 'fa-solid fa-building-columns',
                'subItems' => [
                    ['label' => 'Asignación de Créditos', 'url' => '/Despachos/AsignacionCreditosDespacho', 'modulos' => [20]],
                    ['label' => 'Mi Cartera', 'url' => '/Despachos/MiGestion', 'modulos' => [45]],
                    ['label' => 'Crear Convenio', 'url' => '/convenios/consulta', 'modulos' => [46]],
                    ['label' => 'Cierre de Crédito', 'url' => '/CierreCredito/consulta', 'modulos' => [50]],
                ],
            ],
            'Onboarding' => [
                'icono' => 'fa-solid fa-graduation-cap',
                'subItems' => [
                    ['label' => 'Curso Onboarding', 'url' => '/onboarding/index', 'modulos' => [44]],
                ],
            ],
            'Configuración' => [
                'icono' => 'fa-solid fa-cog',
                'subItems' => [
                    ['label' => 'Departamentos', 'url' => '/departamentos/consulta/', 'modulos' => [10]],
                    ['label' => 'Países', 'url' => '/paises/consulta', 'modulos' => [41]],
                    ['label' => 'Equivalencia puestos', 'url' => '/equivalencias/consulta', 'modulos' => [17]],
                    ['label' => 'Asignación por puestos', 'url' => '/configticketpuesto/consulta', 'modulos' => [26]],
                ],
            ],
            'Shell' => [
                'icono' => 'fa-solid fa-laptop',
                'subItems' => [
                    ['label' => 'Shell Segundómetro', 'url' => '/segundometro/shell', 'modulos' => [16]],
                    ['label' => 'Shell Gastos Cobranza', 'url' => '/gastoscobranza/shell', 'modulos' => [31]],
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
            $meta = mapModuloWebIdToSidebarMeta($mid);
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
