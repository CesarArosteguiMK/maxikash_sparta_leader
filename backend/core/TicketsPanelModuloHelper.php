<?php

namespace Core;

use Models\ConfigPanelUsuario as ConfigPanelUsuarioDAO;
use Core\Database;

/**
 * Paneles admin de tickets por módulo (cada uno con URL y script propios, sin Sabueso).
 */
class TicketsPanelModuloHelper
{
    /** categoria_gestion => [ titulo, badge tabla, icono FA (sin fa-solid), color icono, formularios ] */
    public const MODULOS = [
        'validaciones' => [
            'titulo' => 'Validaciones',
            'badge' => 'Validaciones',
            'icon' => 'fa-clipboard-check',
            'icon_color' => 'text-success',
            'formularios' => true,
            'url' => '/validaciones/paneladmin',
        ],
        'ausencia' => [
            'titulo' => 'Ausencias',
            'badge' => 'Ausencias',
            'icon' => 'fa-calendar-xmark',
            'icon_color' => 'text-primary',
            'formularios' => false,
            'url' => '/viaticos/paneladmin',
        ],
        'viaticos' => [
            'titulo' => 'Ausencias',
            'badge' => 'Ausencias',
            'icon' => 'fa-calendar-xmark',
            'icon_color' => 'text-primary',
            'formularios' => false,
            'url' => '/viaticos/paneladmin',
        ],
        'aplicaciones_de_pago' => [
            'titulo' => 'Reclamos de bonos',
            'badge' => 'Reclamos de bonos',
            'icon' => 'fa-gift',
            'icon_color' => 'text-warning',
            'formularios' => false,
            'url' => '/aplicacionespago/paneladmin',
        ],
        'reclamo' => [
            'titulo' => 'Reclamos de bonos',
            'badge' => 'Reclamos de bonos',
            'icon' => 'fa-gift',
            'icon_color' => 'text-warning',
            'formularios' => false,
            'url' => '/aplicacionespago/paneladmin',
        ],
        'plantilla' => [
            'titulo' => 'Plantilla',
            'badge' => 'Plantilla',
            'icon' => 'fa-file-lines',
            'icon_color' => 'text-secondary',
            'formularios' => false,
            'url' => '/plantilla/paneladmin',
        ],
        'atencion_cliente' => [
            'titulo' => 'Atención al cliente',
            'badge' => 'Atención al cliente',
            'icon' => 'fa-headset',
            'icon_color' => 'text-primary',
            'formularios' => false,
            'url' => '/atencioncliente/paneladmin',
        ],
        'credito_problematico' => [
            'titulo' => 'Pagos no identificados',
            'badge' => 'Pagos no identificados',
            'icon' => 'fa-magnifying-glass-dollar',
            'icon_color' => 'text-info',
            'formularios' => false,
            'url' => '/creditoproblematico/paneladmin',
        ],
        'pagos_no_identificados' => [
            'titulo' => 'Pagos no identificados',
            'badge' => 'Pagos no identificados',
            'icon' => 'fa-magnifying-glass-dollar',
            'icon_color' => 'text-info',
            'formularios' => false,
            'url' => '/creditoproblematico/paneladmin',
        ],
        'aclaracion_credito' => [
            'titulo' => 'Incidencias en asignacion de cartera',
            'badge' => 'Incidencias en asignacion de cartera',
            'icon' => 'fa-briefcase',
            'icon_color' => 'text-primary',
            'formularios' => false,
            'url' => '/aclaracioncredito/paneladmin',
        ],
        'incidencias_cartera' => [
            'titulo' => 'Incidencias en asignacion de cartera',
            'badge' => 'Incidencias en asignacion de cartera',
            'icon' => 'fa-briefcase',
            'icon_color' => 'text-primary',
            'formularios' => false,
            'url' => '/aclaracioncredito/paneladmin',
        ],
    ];

    /** categoria_gestion en ticket => clave en config_panel_usuario */
    public const CATEGORIA_CLAVE_PANEL = [
        'validaciones' => 'sabueso_panel_validaciones',
        'ausencia' => 'sabueso_panel_viaticos',
        'solicitud_vacaciones' => 'sabueso_panel_viaticos',
        'viaticos' => 'sabueso_panel_viaticos',
        'aplicaciones_de_pago' => 'sabueso_panel_aplicacionespago',
        'reclamo' => 'sabueso_panel_aplicacionespago',
        'plantilla' => 'sabueso_panel_plantilla',
        'atencion_cliente' => 'sabueso_panel_atencioncliente',
        'credito_problematico' => 'sabueso_panel_creditoproblematico',
        'pagos_no_identificados' => 'sabueso_panel_creditoproblematico',
        'aclaracion_credito' => 'sabueso_panel_aclaracioncredito',
        'incidencias_cartera' => 'sabueso_panel_aclaracioncredito',
    ];

    /**
     * null = puede consultar cualquier categoría (tiene Panel Sabueso principal).
     * array = solo esas categorías (paneles por módulo asignados explícitamente).
     */
    public static function getCategoriasPermitidasTicketsApi(array $panelesUsuario): ?array
    {
        if (in_array('sabueso_paneladmin', $panelesUsuario, true)) {
            return null;
        }
        $cats = [];
        foreach (self::CATEGORIA_CLAVE_PANEL as $cat => $clave) {
            if (in_array($clave, $panelesUsuario, true)) {
                $cats[] = $cat;
            }
        }

        return array_values(array_unique($cats));
    }

    public static function getRedirectUrlForCategoria(string $categoria): ?string
    {
        $c = strtolower(preg_replace('/[^a-z0-9_]/', '', $categoria));

        return isset(self::MODULOS[$c]) ? self::MODULOS[$c]['url'] : null;
    }

    public static function renderModuloPanel(Controller $ctrl, string $categoria, array $extraModuloConfig = []): void
    {
        $c = strtolower(preg_replace('/[^a-z0-9_]/', '', $categoria));
        if (!isset(self::MODULOS[$c])) {
            header('Location: /sabueso/panelAdminInicio', true, 302);
            exit;
        }
        $m = self::MODULOS[$c];
        $modo = isset($extraModuloConfig['modo']) ? strtolower(trim((string)$extraModuloConfig['modo'])) : '';
        $personaId = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        $claveRequerida = self::CATEGORIA_CLAVE_PANEL[$c] ?? '';
        $panelesUsuario = ConfigPanelUsuarioDAO::getPanelesPorPersona($personaId);
        // Panel admin por módulo (p. ej. sabueso_panel_validaciones) no aplica a Validaciones/Gestor ni Validaciones/Territorial:
        // esos accesos van por módulos de ruta (18/27) y rol operativo, no por el mismo permiso que el panel administrador.
        $omitirClavePanelAdmin = ($c === 'validaciones' && in_array($modo, ['territorial', 'gestor'], true));
        if (!$omitirClavePanelAdmin && ($claveRequerida === '' || !in_array($claveRequerida, $panelesUsuario, true))) {
            header('Location: /sabueso/panelAdminInicio', true, 302);
            exit;
        }
        $panelesVis = ConfigPanelUsuarioDAO::getPanelesVisiblesParaPersona($personaId, []);
        $columnsJson = PanelAdminTicketTable::getColumnsConfig(true, $c);
        $titulos = PanelAdminTicketTable::getTitulosColumnasPanelAdminPorCategoria($c);
        $titulosJs = json_encode($titulos, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        $mostrarFormularios = !empty($m['formularios']);
        $tituloPanel = $m['titulo'];
        $prefijoTitulo = 'Panel Admin – ';
        if ($c === 'validaciones' && in_array($modo, ['territorial', 'gestor'], true)) {
            $mostrarFormularios = false;
            $tituloPanel = $modo === 'territorial' ? 'Validaciones/Territorial' : 'Validaciones/Gestor';
            $prefijoTitulo = '';
        }

        $moduloConfig = [
            'categoria' => $c,
            'labelBadge' => $m['badge'],
            'formularios' => $mostrarFormularios,
            'columnas_ocultas' => PanelAdminTicketTable::getIndicesColumnasOcultasModulo($c),
        ];
        if (!empty($extraModuloConfig)) {
            // Permite agregar flags como modo ('gestor'/'territorial') y valores como campoCapo.
            $moduloConfig = array_merge($moduloConfig, $extraModuloConfig);
        }
        // Jefe territorial (sesión): el JS distingue gestor de campo vs. el propio capo en asignacion_ticket.
        if ($c === 'validaciones' && ($moduloConfig['modo'] ?? '') === 'territorial') {
            $moduloConfig['personaIdSesion'] = (int) $personaId;
        }
        $incluirModalFormBuilderLectura = ($c === 'validaciones' && in_array($modo, ['territorial', 'gestor'], true));
        if ($incluirModalFormBuilderLectura) {
            $moduloConfig['verFormularioTerritorialResumen'] = true;
        }
        $moduloJs = json_encode($moduloConfig, JSON_UNESCAPED_UNICODE);

        $ctrl->set('tickets_panel_modal_form_builder_lectura', $incluirModalFormBuilderLectura);
        $ctrl->set('panel_admin_mostrar_volver', count($panelesVis) > 1);
        $ctrl->set('panel_admin_url_inicio', '/sabueso/panelAdminInicio');
        $ctrl->set('tickets_panel_categoria', $c);
        $ctrl->set('tickets_panel_titulo', $tituloPanel);
        $ctrl->set('tickets_panel_titulo_prefijo', $prefijoTitulo);
        $ctrl->set('tickets_panel_icono', $m['icon']);
        $ctrl->set('tickets_panel_icono_color', $m['icon_color'] ?? 'text-primary');
        $ctrl->set('tickets_panel_formularios', $mostrarFormularios);
        $ctrl->set('titulo', $tituloPanel);
        $panelJsVer = @filemtime(dirname(RAIZ) . '/public/assets/js/paneladmin_tickets_modulo.js') ?: time();
        $ctrl->set(
            'script',
            '<script>window.TICKETS_MODULO_CONFIG=' . $moduloJs . ';window.TICKETS_MODULO_COLUMNS=' . $columnsJson['columnsJs'] . ';window.TICKETS_MODULO_TITULOS=' . $titulosJs . ';</script>'
            . '<script src="/assets/js/paneladmin_tickets_modulo.js?v=' . $panelJsVer . '"></script>'
        );
        $ctrl->render('tickets_panel_modulo');
    }

    /**
     * Entrada automática al menú Validaciones (gestor o jefe territorial) sin depender de módulos 18/27.
     * null si no aplica o si usa panel admin de validaciones (config_panel_usuario).
     *
     * @return array{tipo: string, url: string}|null tipo = 'gestor'|'territorial'
     */
    public static function resolverEntradaValidacionesOperativa(int $personaId): ?array
    {
        if ($personaId < 1) {
            return null;
        }
        $panelesMenuUsuario = ConfigPanelUsuarioDAO::getPanelesPorPersona($personaId);
        if (in_array('sabueso_panel_validaciones', $panelesMenuUsuario, true)) {
            return null;
        }
        try {
            $dbMenu = new Database();
            $rowCapo = $dbMenu->queryOne(
                "SELECT pp.nombre AS puesto_nombre, pp.departamento_id AS departamento_id
                 FROM asigna_puesto ap
                 INNER JOIN puesto pp ON pp.id = ap.id_puesto
                 WHERE ap.id_persona = :id_persona
                   AND pp.es_jefe = 1
                   AND (ap.activo = 1 OR ap.activo IS NULL)
                 ORDER BY pp.departamento_id ASC, pp.nivel ASC
                 LIMIT 1",
                ['id_persona' => $personaId]
            );
            $puestoNombreMenu = $rowCapo && isset($rowCapo['puesto_nombre'])
                ? strtolower(trim((string)$rowCapo['puesto_nombre']))
                : '';
            $campoTerritorialMenu = '';
            if ($puestoNombreMenu !== '') {
                if (strpos($puestoNombreMenu, '1_7') !== false) {
                    $campoTerritorialMenu = '1_7';
                } elseif (strpos($puestoNombreMenu, '8_21') !== false) {
                    $campoTerritorialMenu = '8_21';
                }
                if ($campoTerritorialMenu === '') {
                    if (preg_match('/1\s*[-_ ]?\s*7/', $puestoNombreMenu)) {
                        $campoTerritorialMenu = '1_7';
                    } elseif (preg_match('/8\s*[-_ ]?\s*21/', $puestoNombreMenu)) {
                        $campoTerritorialMenu = '8_21';
                    }
                }
            }
            $capoDepartamentoId = $rowCapo && isset($rowCapo['departamento_id']) ? (int)$rowCapo['departamento_id'] : 0;
            $esCapoValidacionesTerritorial = $capoDepartamentoId > 0 && $campoTerritorialMenu !== '';

            if ($esCapoValidacionesTerritorial) {
                return ['tipo' => 'territorial', 'url' => '/validaciones/territorial'];
            }

            $asigRow = $dbMenu->queryOne(
                "SELECT COUNT(1) AS total
                 FROM asignacion_ticket at
                 INNER JOIN ticket t ON t.id_ticket = at.id_ticket
                 WHERE at.id_persona_asignada = :id_persona
                   AND (at.activo = 1 OR at.fecha_liberacion IS NULL)
                   AND (t.activo = 1 OR t.activo IS NULL)
                   AND LOWER(COALESCE(NULLIF(TRIM(t.categoria_gestion),''), 'sabueso')) = 'validaciones'",
                ['id_persona' => $personaId]
            );
            if ((int)($asigRow['total'] ?? 0) > 0) {
                return ['tipo' => 'gestor', 'url' => '/validaciones/gestor'];
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }
}
