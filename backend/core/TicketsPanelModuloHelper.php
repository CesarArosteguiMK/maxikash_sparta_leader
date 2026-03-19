<?php

namespace Core;

use Models\ConfigPanelUsuario as ConfigPanelUsuarioDAO;

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
        'viaticos' => [
            'titulo' => 'Viáticos',
            'badge' => 'Viáticos',
            'icon' => 'fa-receipt',
            'icon_color' => 'text-primary',
            'formularios' => false,
            'url' => '/viaticos/paneladmin',
        ],
        'aplicaciones_de_pago' => [
            'titulo' => 'Aplicaciones de pago',
            'badge' => 'Aplicaciones de pago',
            'icon' => 'fa-credit-card',
            'icon_color' => 'text-info',
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
            'titulo' => 'Crédito problemático',
            'badge' => 'Crédito problemático',
            'icon' => 'fa-triangle-exclamation',
            'icon_color' => 'text-warning',
            'formularios' => false,
            'url' => '/creditoproblematico/paneladmin',
        ],
        'aclaracion_credito' => [
            'titulo' => 'Aclaración de crédito',
            'badge' => 'Aclaración de crédito',
            'icon' => 'fa-circle-question',
            'icon_color' => 'text-info',
            'formularios' => false,
            'url' => '/aclaracioncredito/paneladmin',
        ],
    ];

    /** categoria_gestion en ticket => clave en config_panel_usuario */
    public const CATEGORIA_CLAVE_PANEL = [
        'validaciones' => 'sabueso_panel_validaciones',
        'viaticos' => 'sabueso_panel_viaticos',
        'aplicaciones_de_pago' => 'sabueso_panel_aplicacionespago',
        'plantilla' => 'sabueso_panel_plantilla',
        'atencion_cliente' => 'sabueso_panel_atencioncliente',
        'credito_problematico' => 'sabueso_panel_creditoproblematico',
        'aclaracion_credito' => 'sabueso_panel_aclaracioncredito',
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
        if ($claveRequerida === '' || !in_array($claveRequerida, $panelesUsuario, true)) {
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
        ];
        if (!empty($extraModuloConfig)) {
            // Permite agregar flags como modo ('gestor'/'territorial') y valores como campoCapo.
            $moduloConfig = array_merge($moduloConfig, $extraModuloConfig);
        }
        $moduloJs = json_encode($moduloConfig, JSON_UNESCAPED_UNICODE);

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
}
