<?php

namespace Controllers;

use Core\Controller;
use Models\Ticket as TicketDAO;
use Models\Empresa as EmpresaDAO;
use Models\CreditoInfoSabueso;
use Models\Notificacion;
use Models\Gestiones as GestionesDAO;
use Models\Ubicacion as UbicacionDAO;
use Models\OfertaCoordenada;
use Models\RegistroAsignacion;
use Models\SolicitudBaja as SolicitudBajaDAO;
use Models\TicketPlantilla as TicketPlantillaDAO;
use Models\TicketAtencionCliente as TicketAtencionClienteDAO;
use Models\TicketValidacion as TicketValidacionDAO;
use Models\TicketViaticos as TicketViaticosDAO;
use Models\TicketAplicacionesPago as TicketAplicacionesPagoDAO;
use Models\TicketCreditoProblematico as TicketCreditoProblematicoDAO;
use Models\TicketAclaracionCredito as TicketAclaracionCreditoDAO;
use Models\ConfigTicketPuesto as ConfigTicketPuestoDAO;
use Models\ConfigEstadisticasPuesto as ConfigEstadisticasPuestoDAO;
use Models\ConfigPanelUsuario as ConfigPanelUsuarioDAO;

// Capas del sistema de predicción: motor, interpretación IA, verificación IA, cache, audit
require_once __DIR__ . '/../services/LocationScoringService.php';
require_once __DIR__ . '/../services/IAInterpretationService.php';
require_once __DIR__ . '/../services/IAVerificationService.php';
require_once __DIR__ . '/../services/PipelineCache.php';
require_once __DIR__ . '/../services/LocationAuditLogger.php';
require_once __DIR__ . '/../services/BehaviorPredictionService.php';
require_once __DIR__ . '/../services/SpatialAnalyticsService.php';
require_once __DIR__ . '/../services/TemporalPaymentsService.php';
require_once __DIR__ . '/../services/GestorComplianceService.php';
require_once __DIR__ . '/../services/GeocodingService.php';
use Services\LocationScoringService;
use Services\IAInterpretationService;
use Services\IAVerificationService;
use Services\PipelineCache;
use Services\LocationAuditLogger;
use Services\BehaviorPredictionService;
use Services\SpatialAnalyticsService;
use Services\TemporalPaymentsService;
use Services\GestorComplianceService;
use Services\GeocodingService;

class Sabueso extends Controller
{
    /**
     * Vista principal: tabla de tickets (Sabueso > Ticket).
     */
    public function ticket()
    {
        $personaId = (int)($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        $idPuesto = (int)($_SESSION['id_puesto'] ?? 0);
        // Usar todos los puestos de la persona: si tiene varios, unir las funciones de cada uno.
        $categoriasDisponibles = ConfigTicketPuestoDAO::getFuncionesPorPersona($personaId);
        if (empty($categoriasDisponibles) && $idPuesto > 0) {
            $categoriasDisponibles = ConfigTicketPuestoDAO::getFuncionesPorPuesto($idPuesto);
        }
        if (empty($categoriasDisponibles) && $idPuesto > 0) {
            $categoriasDisponibles = [];
        }
        if (empty($categoriasDisponibles) && $personaId <= 0 && $idPuesto <= 0) {
            $categoriasDisponibles = ['sabueso', 'solicitud_baja'];
        }
        $columnsJson = $this->getColumnsConfig(false);
        $script = '<script>window.sabuesoTicketColumns=' . $columnsJson['columnsJs'] . ';</script>' . "\n"
            . '<script>window.categoriasDisponiblesPorPuesto=' . json_encode($categoriasDisponibles) . ';</script>' . "\n"
            . '<script src="/assets/js/sabueso_ticket.js"></script>';

        self::set('titulo', 'Ticket | Sabueso');
        self::set('script', $script);
        self::set('esAdminTicket', false);
        self::set('categoriasDisponiblesPorPuesto', $categoriasDisponibles);
        self::set('funcionesTicket', ConfigTicketPuestoDAO::FUNCIONES);
        self::render('sabueso_ticket');
    }

    /**
     * Vista Panel Admin: tabla de todos los tickets con columna Quién levantó. Sin botón Levantar ticket ni buscador.
     */
    /**
     * @param bool|null $forzarSoloConsultaCredito Solo true cuando se invoca desde Analítica (URL /reporteria/consultaIdCredito).
     *                             No usar otros valores: rutas tipo /sabueso/paneladmin/extra seguirían siendo parámetros de URL.
     */
    public function paneladmin($forzarSoloConsultaCredito = null)
    {
        $usuarioId = (int)($_SESSION['usuario_id'] ?? 0);
        $personaId = (int)($_SESSION['persona_id'] ?? $usuarioId);
        $panelesUsuario = ConfigPanelUsuarioDAO::getPanelesPorPersona($personaId);
        /** Rastreo: módulo 29 (permiso especial) o legado 18/19; sin requerir panel sabueso_paneladmin. */
        $soloConsultaCredito = ($forzarSoloConsultaCredito === true)
            || (isset($_GET['solo_consulta_credito']) && (string)$_GET['solo_consulta_credito'] === '1');
        // URL canónica bajo Analítica (evita /sabueso/paneladmin?solo_consulta_credito=1 en la barra de direcciones).
        if ($soloConsultaCredito && $forzarSoloConsultaCredito !== true && isset($_GET['solo_consulta_credito'])) {
            header('Location: /reporteria/consultaIdCredito', true, 302);
            exit;
        }
        if ($soloConsultaCredito) {
            $mods = array_map('intval', (array)($_SESSION['modulos'] ?? []));
            if (!array_intersect([18, 19, 29], $mods)) {
                header('Location: /Inicio', true, 302);
                exit;
            }
        } elseif (!in_array('sabueso_paneladmin', $panelesUsuario, true)) {
            header('Location: /sabueso/panelAdminInicio', true, 302);
            exit;
        }
        $usuarioNombre = $usuarioId ? TicketDAO::getNombrePersona($usuarioId) : '';
        $modulos = $_SESSION['modulos'] ?? [];
        $puedeUsarAnalizarIA = is_array($modulos) && (in_array(19, $modulos) || in_array(27, $modulos));
        self::set('miUsuarioId', $usuarioId);
        self::set('miUsuarioNombre', $usuarioNombre);
        self::set('miPersonaId', $personaId);
        self::set('puedeUsarAnalizarIA', $puedeUsarAnalizarIA);
        $catPanelGet = isset($_GET['categoria']) ? strtolower(preg_replace('/[^a-z0-9_]/', '', (string)$_GET['categoria'])) : '';
        if ($soloConsultaCredito) {
            $catPanelGet = '';
        }
        if ($catPanelGet !== '') {
            $urlModPanel = \Core\TicketsPanelModuloHelper::getRedirectUrlForCategoria($catPanelGet);
            if ($urlModPanel !== null) {
                header('Location: ' . $urlModPanel, true, 302);
                exit;
            }
        }
        $columnsJson = $this->getColumnsConfig(true, $catPanelGet);
        $catsTitulos = ['', 'sabueso', 'validaciones', 'viaticos', 'aplicaciones_de_pago', 'plantilla', 'atencion_cliente', 'credito_problematico', 'aclaracion_credito'];
        $mapTitulosPanel = [];
        foreach ($catsTitulos as $ck) {
            $mapTitulosPanel[$ck === '' ? '_mixto' : $ck] = \Core\PanelAdminTicketTable::getTitulosColumnasPanelAdminPorCategoria($ck);
        }
        $panelAdminTitulosPorCatJs = json_encode($mapTitulosPanel, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        $googleMapsKeyJs = json_encode(defined('GOOGLE_MAPS_API_KEY') && (string)GOOGLE_MAPS_API_KEY !== '' ? (string)GOOGLE_MAPS_API_KEY : '', JSON_UNESCAPED_SLASHES);
        $soloConsultaCreditoJs = $soloConsultaCredito ? 'true' : 'false';
        require __DIR__ . '/SabuesoPaneladminScriptChunk.php';
        $panelesVis = ConfigPanelUsuarioDAO::getPanelesVisiblesParaPersona($personaId, []);
        if ($soloConsultaCredito) {
            self::set('panel_admin_mostrar_volver', true);
            self::set('panel_admin_url_inicio', '/reporteria/sabuesos');
        } else {
            self::set('panel_admin_mostrar_volver', count($panelesVis) > 1);
            self::set('panel_admin_url_inicio', '/sabueso/panelAdminInicio');
        }
        // Estado inicial según ?categoria= para evitar flash de interfaz Sabueso en otros módulos
        $catLabelsPanel = ['sabueso' => 'Sabueso', 'plantilla' => 'Plantilla', 'atencion_cliente' => 'Atención al cliente', 'validaciones' => 'Validaciones', 'viaticos' => 'Viáticos', 'aplicaciones_de_pago' => 'Aplicaciones de pago', 'credito_problematico' => 'Crédito problemático', 'aclaracion_credito' => 'Aclaración de crédito'];
        $catIconosPanel = ['sabueso' => 'fa-dog', 'plantilla' => 'fa-file-lines', 'atencion_cliente' => 'fa-headset', 'validaciones' => 'fa-clipboard-check', 'viaticos' => 'fa-receipt', 'aplicaciones_de_pago' => 'fa-credit-card', 'credito_problematico' => 'fa-triangle-exclamation', 'aclaracion_credito' => 'fa-circle-question'];
        $categoriasSimples = ['viaticos', 'aplicaciones_de_pago', 'credito_problematico', 'aclaracion_credito', 'plantilla', 'atencion_cliente', 'validaciones'];
        $panel_admin_es_simple = $catPanelGet !== '' && in_array($catPanelGet, $categoriasSimples);
        // Panel Admin en /sabueso/paneladmin = solo tickets categoría sabueso (otros módulos tienen su propia URL).
        $panel_admin_titulo_label = ($catPanelGet !== '' && isset($catLabelsPanel[$catPanelGet])) ? $catLabelsPanel[$catPanelGet] : 'Sabueso';
        $panel_admin_icono = ($catPanelGet !== '' && isset($catIconosPanel[$catPanelGet])) ? $catIconosPanel[$catPanelGet] : 'fa-dog';
        self::set('panel_admin_categoria_inicial', $catPanelGet !== '' ? $catPanelGet : 'sabueso');
        self::set('panel_admin_es_simple', $panel_admin_es_simple);
        self::set('panel_admin_titulo_label', $panel_admin_titulo_label);
        self::set('panel_admin_icono', $panel_admin_icono);
        $urlsModPanel = [];
        foreach (\Core\TicketsPanelModuloHelper::MODULOS as $k => $m) {
            $urlsModPanel[$k] = $m['url'];
        }
        self::set('panel_admin_modulo_urls_json', json_encode($urlsModPanel, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT));
        self::set('panel_admin_solo_consulta_credito', $soloConsultaCredito);
        $layoutChromelessReporteriaEmbed = $soloConsultaCredito
            && isset($_GET['chromeless'])
            && (string) $_GET['chromeless'] === '1';
        self::set('panel_admin_chromeless_embed', $layoutChromelessReporteriaEmbed);
        self::set('layoutChromelessReporteriaEmbed', $layoutChromelessReporteriaEmbed);
        self::set('titulo', $soloConsultaCredito ? 'Rastreo' : 'Panel Admin | Sabueso');
        self::set('script', $script);
        self::render('sabueso_paneladmin');
    }

    /**
     * Mismo bloque de script que el panel admin en modo solo consulta por ID de crédito (Analítica), para incrustarlo en Estado de cuenta en la misma página.
     */
    public function getPaneladminScriptSoloConsultaParaEstadoCuenta(): string
    {
        $mods = array_map('intval', (array) ($_SESSION['modulos'] ?? []));
        $idUsuarioSesion = (int) ($_SESSION['usuario_id'] ?? 0);
        // Mismo criterio amplio que Estado de cuenta (usuario 1 o módulo 29) + legado panel 18/19.
        if ($idUsuarioSesion !== 1 && !array_intersect([18, 19, 29], $mods)) {
            return '';
        }
        $usuarioId = (int) ($_SESSION['usuario_id'] ?? 0);
        $personaId = (int) ($_SESSION['persona_id'] ?? $usuarioId);
        $usuarioNombre = $usuarioId ? TicketDAO::getNombrePersona($usuarioId) : '';
        $catPanelGet = '';
        $columnsJson = $this->getColumnsConfig(true, $catPanelGet);
        $catsTitulos = ['', 'sabueso', 'validaciones', 'viaticos', 'aplicaciones_de_pago', 'plantilla', 'atencion_cliente', 'credito_problematico', 'aclaracion_credito'];
        $mapTitulosPanel = [];
        foreach ($catsTitulos as $ck) {
            $mapTitulosPanel[$ck === '' ? '_mixto' : $ck] = \Core\PanelAdminTicketTable::getTitulosColumnasPanelAdminPorCategoria($ck);
        }
        $panelAdminTitulosPorCatJs = json_encode($mapTitulosPanel, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        $googleMapsKeyJs = json_encode(defined('GOOGLE_MAPS_API_KEY') && (string) GOOGLE_MAPS_API_KEY !== '' ? (string) GOOGLE_MAPS_API_KEY : '', JSON_UNESCAPED_SLASHES);
        $soloConsultaCreditoJs = 'true';
        require __DIR__ . '/SabuesoPaneladminScriptChunk.php';

        return $script;
    }

    private function getColumnsConfig($esAdmin, string $categoriaPanel = '')
    {
        return \Core\PanelAdminTicketTable::getColumnsConfig((bool)$esAdmin, $categoriaPanel);
    }

    /**
     * Columnas para la vista Panel Solicitud de baja.
     */
    private function getColumnsConfigPanelSolicitudBaja()
    {
        $base = [
            ['data' => null, 'defaultContent' => '', 'className' => 'control', 'orderable' => false],
            ['data' => 'fecha_display', 'title' => 'Fecha'],
            ['data' => 'motivo_baja', 'title' => 'Motivo'],
            ['data' => 'nombre_colaborador', 'title' => 'Colaborador a dar de baja'],
            ['data' => 'creador_nombre', 'title' => 'Quién solicitó'],
            ['data' => 'adjunto_display', 'title' => 'Adjunto', 'orderable' => false],
            ['data' => 'acciones', 'title' => 'Acciones', 'orderable' => false],
        ];
        return [
            'columnsJs' => json_encode($base, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT | JSON_HEX_APOS),
        ];
    }

    /**
     * Vista Panel Admin Solicitudes de baja: listado + modal Ver (solo lectura).
     */
    public function panelSolicitudBaja()
    {
        $idPersonaSb = (int)($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        if (!in_array('sabueso_panelsolicitudbaja', ConfigPanelUsuarioDAO::getPanelesPorPersona($idPersonaSb), true)) {
            header('Location: /sabueso/panelAdminInicio', true, 302);
            exit;
        }
        $columnsJson = $this->getColumnsConfigPanelSolicitudBaja();
        $script = <<<SCRIPT
        <script>
        function attrEsc(s){ if (s==null||s===undefined) return ''; var x=(s+'').split('&').join('&amp;').split('<').join('&lt;'); return x.split('"').join('&quot;'); }
        $(document).ready(function() {
            configuraTabla("#tablaSolicitudesBaja", {
                registrosPorPagina: 10,
                order: [[1, 'desc']],
                columns: {$columnsJson['columnsJs']}
            });
            getSolicitudesBaja();
        });
        function getSolicitudesBaja() {
            http.request({
                endpoint: "/sabueso/getSolicitudesBaja",
                metodo: "POST",
                onSuccess: function(resp) {
                    var datos = (resp.datos || []).map(function(s) {
                        var fecha = s.fecha_creacion ? new Date(s.fecha_creacion).toLocaleString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—';
                        var nExtra = parseInt(s.num_adjuntos_extra, 10) || 0;
                        var totalAdj = (s.ruta_adjunto && s.ruta_adjunto.trim() ? 1 : 0) + nExtra;
                        var adjunto = totalAdj > 0 ? '<a href="/sabueso/verAdjuntoSolicitudBaja?id=' + (s.id || '') + '" target="_blank" class="btn btn-sm btn-outline-secondary" title="Descargar adjunto"><i class="fa-solid fa-download me-1"></i>Ver' + (totalAdj > 1 ? ' (' + totalAdj + ')' : '') + '</a>' : '<span class="text-muted">—</span>';
                        return {
                            fecha_display: '<small>' + attrEsc(fecha) + '</small>',
                            motivo_baja: '<span class="fw-medium">' + attrEsc(s.motivo_baja || '—') + '</span>',
                            nombre_colaborador: '<small>' + attrEsc(s.nombre_colaborador || '—') + '</small>',
                            creador_nombre: '<small>' + attrEsc(s.creador_nombre || '—') + '</small>',
                            adjunto_display: adjunto,
                            acciones: '<button type="button" class="btn btn-sm btn-outline-primary btn-ver-solicitud" onclick="abrirModalVerSolicitudBaja(' + (s.id || 0) + ')" title="Ver detalle"><i class="fa fa-eye"></i></button>'
                        };
                    });
                    var tabla = $('#tablaSolicitudesBaja').DataTable();
                    tabla.clear().rows.add(datos).draw();
                },
                onError: function() {
                    var tabla = $('#tablaSolicitudesBaja').DataTable();
                    tabla.clear().draw();
                }
            });
        }
        function abrirModalVerSolicitudBaja(id) {
            if (!id) return;
            $('#modalVerSolicitudBaja .modal-body').html('<div class="text-center py-4"><span class="text-muted">Cargando...</span></div>');
            var \$modal = $('#modalVerSolicitudBaja');
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) { bootstrap.Modal.getOrCreateInstance(\$modal[0]).show(); } else { \$modal.modal('show'); }
            http.request({
                endpoint: "/sabueso/getSolicitudBajaPorId",
                metodo: "POST",
                data: JSON.stringify({ id: id }),
                contentType: "application/json",
                processData: false,
                onSuccess: function(r) {
                    if (!(r.success && r.datos)) {
                        $('#modalVerSolicitudBaja .modal-body').html('<div class="alert alert-warning mb-0">' + (r.mensaje || 'No se encontró la solicitud.') + '</div>');
                        return;
                    }
                    var d = r.datos;
                    var esc = attrEsc;
                    var fCreacion = d.fecha_creacion ? new Date(d.fecha_creacion).toLocaleString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—';
                    var html = '<div class="solicitud-ver-seccion"><h6 class="text-uppercase small fw-bold text-muted mb-2">Fecha de registro</h6><div>' + esc(fCreacion) + '</div></div>';
                    html += '<div class="solicitud-ver-seccion"><h6 class="text-uppercase small fw-bold text-muted mb-2">Motivo de la solicitud</h6><div class="fw-medium">' + esc(d.motivo_baja || '—') + '</div></div>';
                    html += '<div class="solicitud-ver-seccion"><h6 class="text-uppercase small fw-bold text-muted mb-2">Detalle del motivo</h6><div class="text-break">' + esc(d.detalle_motivo || '—') + '</div></div>';
                    if (d.descripcion && d.descripcion.trim()) html += '<div class="solicitud-ver-seccion"><h6 class="text-uppercase small fw-bold text-muted mb-2">Descripción u observaciones</h6><div class="text-break">' + esc(d.descripcion) + '</div></div>';
                    html += '<div class="solicitud-ver-seccion"><h6 class="text-uppercase small fw-bold text-muted mb-2">Colaborador a dar de baja</h6><div class="fw-semibold">' + esc(d.nombre_colaborador || '—') + '</div></div>';
                    html += '<div class="solicitud-ver-seccion"><h6 class="text-uppercase small fw-bold text-muted mb-2">Quién solicitó</h6><div>' + esc(d.creador_nombre || '—') + '</div></div>';
                    if ((d.ruta_adjunto && d.ruta_adjunto.trim()) || (d.adjuntos_adicionales && d.adjuntos_adicionales.length > 0)) {
                        html += '<div class="solicitud-ver-seccion"><h6 class="text-uppercase small fw-bold text-muted mb-2">Adjuntos</h6><div class="d-flex flex-wrap gap-2 align-items-center">';
                        if (d.ruta_adjunto && d.ruta_adjunto.trim()) {
                            html += '<a href="/sabueso/verAdjuntoSolicitudBaja?id=' + (d.id || '') + '" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-download me-1"></i>Descargar 1</a>';
                            if (d.nombre_archivo_original && d.nombre_archivo_original.trim()) html += ' <span class="text-muted small">' + esc(d.nombre_archivo_original) + '</span>';
                        }
                        var extras = d.adjuntos_adicionales || [];
                        for (var i = 0; i < extras.length; i++) {
                            var num = i + 2;
                            html += ' <a href="/sabueso/verAdjuntoSolicitudBaja?id=' + (d.id || '') + '&num=' + num + '" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-download me-1"></i>Descargar ' + num + '</a>';
                            if (extras[i].nombre_original) html += ' <span class="text-muted small">' + esc(extras[i].nombre_original) + '</span>';
                        }
                        html += '</div></div>';
                    }
                    $('#modalVerSolicitudBaja .modal-body').html(html);
                },
                onError: function(e) {
                    $('#modalVerSolicitudBaja .modal-body').html('<div class="alert alert-danger mb-0">' + (e && e.mensaje ? attrEsc(e.mensaje) : 'Error al cargar.') + '</div>');
                }
            });
        }
        </script>
        SCRIPT;
        self::set('titulo', 'Solicitudes de baja | Sabueso');
        self::set('script', $script);
        self::render('sabueso_panel_solicitud_baja');
    }

    private function getColumnsConfigCerradoEliminado()
    {
        $base = [
            ['data' => null, 'defaultContent' => '', 'className' => 'control', 'orderable' => false],
            ['data' => 'folio_tipo', 'title' => 'Folio / Tipo'],
            ['data' => 'credito', 'title' => 'Crédito'],
            ['data' => 'fechas', 'title' => 'Fechas'],
            ['data' => 'creador', 'title' => 'Quién levantó'],
            ['data' => 'asignado', 'title' => 'Asignado a'],
            ['data' => 'tipo_accion_display', 'title' => 'Acción'],
            ['data' => 'quien_elimino', 'title' => 'Quién eliminó/cerró'],
            ['data' => 'fecha_eliminacion_display', 'title' => 'Fecha cierre/eliminación'],
            ['data' => 'acciones', 'title' => 'Acciones', 'orderable' => false],
        ];
        return [
            'columnsJs' => json_encode($base, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT | JSON_HEX_APOS),
        ];
    }

    /**
     * Vista Cerrado/Eliminado: tickets desde ticket_historico, tabla + modal Ver (solo lectura).
     */
    public function cerradoEliminado()
    {
        $columnsJson = $this->getColumnsConfigCerradoEliminado();
        $script = <<<SCRIPT
        <script>
        function attrEsc(s){ if (s==null||s===undefined) return ''; var x=(s+'').split('&').join('&amp;').split('<').join('&lt;'); return x.split('"').join('&quot;'); }
        $(document).ready(function() {
            configuraTabla("#tablaTicketsCerradosEliminados", {
                registrosPorPagina: 10,
                columns: {$columnsJson['columnsJs']}
            });
            getTicketsCerradosEliminados();
        });
        function getTicketsCerradosEliminados() {
            http.request({
                endpoint: "/sabueso/getTicketsCerradosEliminados",
                metodo: "POST",
                onSuccess: function(resp) {
                    var datos = (resp.datos || []).map(function(t) {
                        var fechaCreacion = t.fecha_creacion ? new Date(t.fecha_creacion).toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '—';
                        var fechaVenc = t.fecha_vencimiento ? new Date(t.fecha_vencimiento).toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '—';
                        var fechaElim = t.fecha_eliminacion ? new Date(t.fecha_eliminacion).toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—';
                        var tipoAccion = (t.tipo_accion || '').toLowerCase();
                        var tipoAccionBadge = tipoAccion === 'cerrado' ? '<span class="badge bg-warning text-dark">Cerrado</span>' : '<span class="badge bg-danger">Eliminado</span>';
                        var row = {
                            folio_tipo: '<div class=\"fw-semibold\">' + (t.folio || '—') + '</div><div class=\"small text-muted mt-1\">' + (t.tipo_ticket_nombre || '—') + '</div>',
                            credito: '<small>#' + (t.id_credito != null ? t.id_credito : '—') + '</small>',
                            fechas: '<div class="small">Creación: ' + fechaCreacion + '</div><div class="small text-muted mt-1">Venc: ' + fechaVenc + '</div>',
                            creador: '<small>' + (t.creador_nombre || '—') + '</small>',
                            asignado: (t.asignado_nombre && t.asignado_nombre.trim()) ? '<small><i class="fa fa-user-check text-success me-1"></i>' + attrEsc(t.asignado_nombre) + '</small>' : '<span class=\"text-muted\">—</span>',
                            tipo_accion_display: tipoAccionBadge,
                            quien_elimino: '<small>' + (t.quien_elimino_nombre ? attrEsc(t.quien_elimino_nombre) : '—') + '</small>',
                            fecha_eliminacion_display: '<small>' + fechaElim + '</small>',
                            acciones: '<button type="button" class="btn btn-sm btn-outline-primary btn-ver-cerrado" onclick="abrirModalVerCerrado(' + (t.id_ticket || 0) + ')" title="Ver información"><i class="fa fa-eye"></i></button>'
                        };
                        return row;
                    });
                    var tabla = $('#tablaTicketsCerradosEliminados').DataTable();
                    tabla.clear().rows.add(datos).draw();
                },
                onError: function() {
                    var tabla = $('#tablaTicketsCerradosEliminados').DataTable();
                    tabla.clear().draw();
                }
            });
        }
        function abrirModalVerCerrado(idTicket) {
            if (!idTicket) return;
            $('#modalVerCerradoEliminado .modal-body').html('<div class="text-center py-4"><span class="text-muted">Cargando...</span></div>');
            $('#modalVerCerradoEliminado').modal('show');
            http.request({
                endpoint: "/sabueso/getDatosTicketCerradoEliminado",
                metodo: "POST",
                data: JSON.stringify({ id_ticket: idTicket }),
                contentType: "application/json",
                processData: false,
                onSuccess: function(r) {
                    if (!(r.success && r.datos)) {
                        $('#modalVerCerradoEliminado .modal-body').html('<div class="alert alert-warning mb-0">' + (r.mensaje || 'No se encontraron datos.') + '</div>');
                        return;
                    }
                    var d = r.datos;
                    var credito = d.credito || {};
                    var ticket = d.ticket || {};
                    var esc = attrEsc;
                    var fmtF = function(s, conHora) {
                        if (!s) return '—';
                        try {
                            var dt = new Date(s);
                            if (isNaN(dt.getTime())) return s;
                            var opts = { day: '2-digit', month: '2-digit', year: 'numeric' };
                            if (conHora) { opts.hour = '2-digit'; opts.minute = '2-digit'; }
                            return dt.toLocaleString('es-MX', opts);
                        } catch(e) { return s; }
                    };

                    /* --- Datos del crédito --- */
                    var htmlCredito = '<div class="row g-2 mb-3">';
                    htmlCredito += '<div class="col-md-4"><span class="text-muted small d-block">ID crédito</span><div class="fw-semibold">' + (credito.id_credito || '—') + '</div></div>';
                    htmlCredito += '<div class="col-md-8"><span class="text-muted small d-block">Nombre cliente</span><div class="fw-semibold">' + esc(credito.Nombre_cliente || credito.nombre_completo || '—') + '</div></div>';
                    htmlCredito += '<div class="col-md-4"><span class="text-muted small d-block">Teléfono 1</span><div class="fw-semibold">' + esc(credito.telefono_referencia1 || '—') + '</div></div>';
                    htmlCredito += '<div class="col-md-4"><span class="text-muted small d-block">Teléfono 2</span><div class="fw-semibold">' + esc(credito.telefono_referencia2 || '—') + '</div></div>';
                    htmlCredito += '<div class="col-md-4"><span class="text-muted small d-block">Celular</span><div class="fw-semibold">' + esc(credito.Celular || credito.celular || '—') + '</div></div>';
                    htmlCredito += '<div class="col-md-12"><span class="text-muted small d-block">Dirección</span><div class="fw-semibold small">' + esc(credito.Domicilio_Completo || '—') + '</div></div>';
                    htmlCredito += '</div>';

                    /* --- Ticket --- */
                    var htmlTicket = '<div class="border rounded p-3 mb-3">';
                    htmlTicket += '<div class="fw-semibold mb-2"><i class="fa-solid fa-ticket me-1"></i>' + esc(ticket.folio || '—') + ' · ' + esc(ticket.tipo_ticket_nombre || '') + '</div>';
                    htmlTicket += '<div class="row g-2">';
                    htmlTicket += '<div class="col-md-6 small"><span class="text-muted">Estado:</span> ' + esc(ticket.estado_ticket_nombre || '—') + '</div>';
                    htmlTicket += '<div class="col-md-6 small"><span class="text-muted">Prioridad:</span> ' + esc(ticket.prioridad_nombre || '—') + '</div>';
                    htmlTicket += '</div>';
                    htmlTicket += '<div class="small mt-2"><span class="text-muted">Descripción inicial:</span><br>' + esc(ticket.descripcion_inicial || '—') + '</div>';
                    htmlTicket += '<div class="row g-2 mt-2">';
                    htmlTicket += '<div class="col-md-4 small"><span class="text-muted">Creación:</span> ' + fmtF(ticket.fecha_creacion, true) + '</div>';
                    htmlTicket += '<div class="col-md-4 small"><span class="text-muted">Vencimiento:</span> ' + fmtF(ticket.fecha_vencimiento, false) + '</div>';
                    htmlTicket += '<div class="col-md-4 small"><span class="text-muted">Acción:</span> <span class="badge bg-label-' + (ticket.tipo_accion === 'eliminado' ? 'danger' : 'warning') + '">' + esc(ticket.tipo_accion || '—') + '</span></div>';
                    htmlTicket += '</div>';
                    htmlTicket += '<div class="small mt-2"><span class="text-muted">Quién levantó:</span> ' + esc(ticket.creador_nombre || '—') + '</div>';
                    htmlTicket += '<div class="small"><span class="text-muted">Asignado (último):</span> ' + esc(ticket.asignado_nombre || '—') + '</div>';
                    htmlTicket += '<div class="small text-danger mt-1"><i class="fa-solid fa-ban me-1"></i>Cerrado/eliminado: ' + fmtF(ticket.fecha_eliminacion, true) + ' por ' + esc(ticket.quien_elimino_nombre || '—') + '</div>';
                    htmlTicket += '</div>';

                    /* --- Dictamen (enviado al gestor) --- */
                    var dictamen = d.dictamen;
                    var domicilios = d.domicilios || [];
                    var htmlDictamen = '';
                    if (dictamen) {
                        htmlDictamen += '<div class="border rounded p-3 mb-3">';
                        htmlDictamen += '<div class="row g-2">';
                        htmlDictamen += '<div class="col-md-6 small"><span class="text-muted">Tipo:</span> ' + esc(dictamen.tipo || '—') + '</div>';
                        htmlDictamen += '<div class="col-md-6 small"><span class="text-muted">Estado:</span> <span class="badge bg-label-info">' + esc(dictamen.estado || '—') + '</span></div>';
                        htmlDictamen += '<div class="col-md-6 small"><span class="text-muted">Fecha envío:</span> ' + fmtF(dictamen.fecha_actualizacion, true) + '</div>';
                        htmlDictamen += '<div class="col-md-6 small"><span class="text-muted">Visto por gestor:</span> ' + (dictamen.fecha_visto_gestor ? fmtF(dictamen.fecha_visto_gestor, true) + (dictamen.visto_gestor_nombre ? ' (' + esc(dictamen.visto_gestor_nombre) + ')' : '') : '<span class="text-warning">No visto</span>') + '</div>';
                        htmlDictamen += '</div>';
                        var descBase = dictamen.descripcion_base || dictamen.descripcion || '';
                        if (descBase) {
                            htmlDictamen += '<div class="small mt-2"><span class="text-muted">Descripción:</span><br><span style="white-space:pre-line">' + esc(descBase) + '</span></div>';
                        }
                        if (domicilios.length) {
                            htmlDictamen += '<div class="mt-2"><span class="text-muted small fw-bold">Lugares del dictamen (' + domicilios.length + '):</span>';
                            htmlDictamen += '<ul class="list-group list-group-flush mt-1">';
                            for (var di = 0; di < domicilios.length; di++) {
                                var dom = domicilios[di];
                                var dirText = dom.descripcion || dom.direccion || ('Dirección ' + (di + 1));
                                var link = dom.url || dom.link || '';
                                htmlDictamen += '<li class="list-group-item py-1 px-2 small">';
                                htmlDictamen += '<i class="fa-solid fa-location-dot text-primary me-1"></i>';
                                if (link) {
                                    htmlDictamen += '<a href="' + esc(link) + '" target="_blank" rel="noopener">' + esc(dirText) + '</a>';
                                } else {
                                    htmlDictamen += esc(dirText);
                                }
                                htmlDictamen += '</li>';
                            }
                            htmlDictamen += '</ul></div>';
                        }
                        htmlDictamen += '</div>';
                    } else {
                        htmlDictamen = '<span class="text-muted small">Sin dictamen enviado al gestor.</span>';
                    }

                    /* --- Dictamen del sistema (resultado IA) --- */
                    var ds = d.dictamen_sistema;
                    var htmlDS = '';
                    if (ds) {
                        var badgeColor = 'secondary';
                        var res = ds.resultado || '';
                        if (res.indexOf('cumplido') !== -1 || res === 'visito_campo') badgeColor = 'success';
                        else if (res === 'no_visito' || res === 'no_cumplio_prorroga') badgeColor = 'danger';
                        else if (res.indexOf('parcial') !== -1 || res === 'visito_telefonico' || res === 'distancia_lejana') badgeColor = 'warning';
                        else if (res === 'prorroga_activa' || res === 'intensidad_activa') badgeColor = 'info';
                        htmlDS += '<div class="border rounded p-3 mb-3">';
                        htmlDS += '<div class="row g-2">';
                        htmlDS += '<div class="col-md-6 small"><span class="text-muted">Resultado:</span> <span class="badge bg-' + badgeColor + '">' + esc(ds.cumplimiento_etiqueta || ds.resultado || '—') + '</span></div>';
                        htmlDS += '<div class="col-md-3 small"><span class="text-muted">Efectividad:</span> ' + (ds.pct_efectividad !== null ? ds.pct_efectividad + '%' : '—') + '</div>';
                        htmlDS += '<div class="col-md-3 small"><span class="text-muted">Fecha:</span> ' + fmtF(ds.fecha_creacion, true) + '</div>';
                        htmlDS += '</div>';
                        htmlDS += '<div class="row g-2 mt-1">';
                        htmlDS += '<div class="col-md-4 small"><span class="text-muted">Direcciones visitadas:</span> ' + (ds.direcciones_visitadas || 0) + ' de ' + (ds.direcciones_dictamen_total || 0) + (ds.visito_todas_direcciones ? ' <i class="fa-solid fa-check text-success"></i>' : '') + '</div>';
                        htmlDS += '<div class="col-md-4 small"><span class="text-muted">Pago en ventana:</span> ' + (ds.pago_en_ventana ? '<span class="text-success fw-bold">Sí</span>' : '<span class="text-danger">No</span>') + '</div>';
                        if (ds.tipo_contacto) {
                            htmlDS += '<div class="col-md-4 small"><span class="text-muted">Tipo contacto:</span> ' + esc(ds.tipo_contacto) + '</div>';
                        }
                        htmlDS += '</div>';
                        if (ds.prorroga) {
                            htmlDS += '<div class="small mt-1"><span class="text-muted">Prórroga:</span> ' + (ds.prorroga.otorgada ? '<span class="badge bg-info">Otorgada</span>' : 'No') + (ds.prorroga.fecha_limite ? ' · Límite: ' + esc(ds.prorroga.fecha_limite) : '') + '</div>';
                        }
                        if (ds.intensidad) {
                            htmlDS += '<div class="small mt-1"><span class="text-muted">Intensidad:</span> ' + (ds.intensidad.otorgada ? '<span class="badge bg-info">Otorgada</span>' : 'No') + (ds.intensidad.fecha_limite ? ' · Límite: ' + esc(ds.intensidad.fecha_limite) : '') + '</div>';
                        }
                        if (ds.cobertura_direcciones && ds.cobertura_direcciones.length) {
                            htmlDS += '<div class="mt-2"><span class="text-muted small fw-bold">Cobertura de direcciones:</span>';
                            htmlDS += '<ul class="list-group list-group-flush mt-1">';
                            for (var ci = 0; ci < ds.cobertura_direcciones.length; ci++) {
                                var cd = ds.cobertura_direcciones[ci];
                                var vis = cd.visitada;
                                htmlDS += '<li class="list-group-item py-1 px-2 small">';
                                htmlDS += vis ? '<i class="fa-solid fa-circle-check text-success me-1"></i>' : '<i class="fa-solid fa-circle-xmark text-danger me-1"></i>';
                                htmlDS += esc(cd.direccion || ('Dirección ' + (ci + 1)));
                                if (cd.distancia_m !== undefined && cd.distancia_m !== null) {
                                    htmlDS += ' <span class="text-muted">(' + cd.distancia_m + ' m)</span>';
                                }
                                htmlDS += '</li>';
                            }
                            htmlDS += '</ul></div>';
                        }
                        if (ds.medidas_preventivas) {
                            htmlDS += '<div class="small mt-2 fst-italic text-muted"><i class="fa-solid fa-info-circle me-1"></i>' + esc(ds.medidas_preventivas) + '</div>';
                        }
                        htmlDS += '</div>';
                    } else {
                        htmlDS = '<span class="text-muted small">Sin dictamen del sistema generado.</span>';
                    }

                    /* --- Evidencias --- */
                    var evidencias = d.evidencias || [];
                    var htmlEvidencias = '';
                    if (evidencias.length) {
                        htmlEvidencias += '<div class="row g-2">';
                        for (var ei = 0; ei < evidencias.length; ei++) {
                            var ev = evidencias[ei];
                            var nombre = ev.nombre_original || 'Archivo';
                            var ruta = ev.ruta_archivo || '';
                            var esImg = /\.(jpg|jpeg|png|gif|webp)$/i.test(nombre);
                            htmlEvidencias += '<div class="col-md-4 col-6">';
                            if (esImg && ruta) {
                                htmlEvidencias += '<a href="/' + esc(ruta) + '" target="_blank"><img src="/' + esc(ruta) + '" class="img-fluid rounded border" style="max-height:120px;object-fit:cover" alt="' + esc(nombre) + '"></a>';
                            } else if (ruta) {
                                htmlEvidencias += '<a href="/' + esc(ruta) + '" target="_blank" class="btn btn-sm btn-outline-secondary w-100"><i class="fa-solid fa-file me-1"></i>' + esc(nombre) + '</a>';
                            } else {
                                htmlEvidencias += '<span class="small text-muted">' + esc(nombre) + '</span>';
                            }
                            htmlEvidencias += '<div class="small text-muted">' + fmtF(ev.fecha_subida, true) + '</div>';
                            htmlEvidencias += '</div>';
                        }
                        htmlEvidencias += '</div>';
                    } else {
                        htmlEvidencias = '<span class="text-muted small">Sin evidencias adjuntas.</span>';
                    }

                    /* --- Historial de asignación --- */
                    var historial = d.historial_asignacion || [];
                    var htmlHistorial = '';
                    if (historial.length) {
                        htmlHistorial += '<div class="table-responsive"><table class="table table-sm table-bordered mb-0 small"><thead><tr><th>Persona</th><th>Desde</th><th>Hasta</th><th>Duración</th></tr></thead><tbody>';
                        for (var hi = 0; hi < historial.length; hi++) {
                            var h = historial[hi];
                            htmlHistorial += '<tr><td>' + esc(h.persona || '—') + '</td><td>' + esc(h.desde || '—') + '</td><td>' + esc(h.hasta || '—') + '</td><td>' + esc(h.duracion_humana || '—') + '</td></tr>';
                        }
                        htmlHistorial += '</tbody></table></div>';
                    } else {
                        htmlHistorial = '<span class="text-muted small">Sin historial de asignación.</span>';
                    }

                    /* --- Ensamblar modal --- */
                    var html = '';
                    html += '<div class="cerrado-ver-seccion"><h6 class="text-uppercase small fw-bold text-muted mb-2"><i class="fa-solid fa-user me-1"></i>Datos del crédito</h6>' + htmlCredito + '</div>';
                    html += '<div class="cerrado-ver-seccion"><h6 class="text-uppercase small fw-bold text-muted mb-2"><i class="fa-solid fa-ticket me-1"></i>Ticket</h6>' + htmlTicket + '</div>';
                    html += '<div class="cerrado-ver-seccion"><h6 class="text-uppercase small fw-bold text-muted mb-2"><i class="fa-solid fa-file-lines me-1"></i>Dictamen enviado al gestor</h6>' + htmlDictamen + '</div>';
                    html += '<div class="cerrado-ver-seccion"><h6 class="text-uppercase small fw-bold text-muted mb-2"><i class="fa-solid fa-robot me-1"></i>Dictamen del sistema</h6>' + htmlDS + '</div>';
                    html += '<div class="cerrado-ver-seccion"><h6 class="text-uppercase small fw-bold text-muted mb-2"><i class="fa-solid fa-image me-1"></i>Evidencias (' + evidencias.length + ')</h6>' + htmlEvidencias + '</div>';
                    html += '<div class="cerrado-ver-seccion"><h6 class="text-uppercase small fw-bold text-muted mb-2"><i class="fa-solid fa-clock-rotate-left me-1"></i>Historial de asignación</h6>' + htmlHistorial + '</div>';
                    $('#modalVerCerradoEliminado .modal-body').html(html);
                },
                onError: function(e) {
                    $('#modalVerCerradoEliminado .modal-body').html('<div class="alert alert-danger mb-0">' + (e && e.mensaje ? attrEsc(e.mensaje) : 'Error al cargar.') + '</div>');
                }
            });
        }
        </script>
        SCRIPT;
        self::set('titulo', 'Cerrado/Eliminado | Sabueso');
        self::set('script', $script);
        self::render('sabueso_cerrado_eliminado');
    }

    /**
     * Vista Panel Admin (inicio): si tiene un solo panel, redirige directo; si tiene varios, muestra tarjetas para elegir.
     */
    public function panelAdminInicio()
    {
        $id_persona = (int) ($_SESSION['usuario_id'] ?? $_SESSION['persona_id'] ?? 0);
        $panelesVisibles = ConfigPanelUsuarioDAO::getPanelesVisiblesParaPersona($id_persona, []);
        self::set('titulo', 'Panel Admin');
        if (empty($panelesVisibles)) {
            self::render('panel_admin_sin_asignacion');
            return;
        }
        if (count($panelesVisibles) === 1) {
            $info = reset($panelesVisibles);
            $url = $info['url'] ?? '';
            if ($url !== '') {
                header('Location: ' . $url);
                exit;
            }
        }
        self::set('panelesVisibles', $panelesVisibles);
        self::render('panel_admin_inicio');
    }

    /**
     * Vista Estadísticas: cada usuario ve solo las tarjetas para las que tiene permiso:
     * por módulo (47 = Sabueso) o por asignación en config_estadisticas_puesto (puesto del usuario).
     */
    public function estadisticas()
    {
        $id_persona = (int) ($_SESSION['usuario_id'] ?? $_SESSION['persona_id'] ?? 0);
        $tiposPorPuesto = $id_persona > 0 ? ConfigEstadisticasPuestoDAO::getTiposPorPersona($id_persona) : [];
        $seccionesEstadisticas = [
            'sabueso' => in_array('sabueso', $tiposPorPuesto),
        ];
        if (empty($seccionesEstadisticas['sabueso'])) {
            self::set('titulo', 'Estadísticas');
            self::render('estadisticas_sin_asignacion');
            return;
        }
        self::set('seccionesEstadisticas', $seccionesEstadisticas);
        $activas = array_keys(array_filter($seccionesEstadisticas));
        $entrarDirectoEstadistica = (count($activas) === 1) ? $activas[0] : null;
        self::set('estadisticasMostrarBotonVolver', count($activas) > 1);

        $script = <<<'SCRIPT'
        <script>
        function attrEsc(s){ if (s==null||s===undefined) return ''; var x=(s+'').split('&').join('&amp;').split('<').join('&lt;'); return x.split('"').join('&quot;'); }
        function fmtFecha(s) {
            if (!s) return '—';
            try { return new Date(s).toLocaleString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }); } catch(e) { return attrEsc(s); }
        }
        function labelPeriodo(key, periodo) {
            if (periodo == null) return '—';
            var s = String(periodo);
            if (key === 'por_dia') {
                try {
                    var d = new Date(s + (s.length <= 10 ? 'T12:00:00' : ''));
                    return d.toLocaleDateString('es-MX', { day: 'numeric', month: 'short', year: 'numeric' });
                } catch(e) { return s; }
            }
            if (key === 'por_semana') {
                if (/^\d{4}-\d{2}-\d{2}$/.test(s)) {
                    try {
                        var dl = new Date(s + 'T12:00:00');
                        var ds = new Date(dl);
                        ds.setDate(dl.getDate() + 6);
                        return dl.toLocaleDateString('es-MX', { day: 'numeric', month: 'short' }) + ' – ' + ds.toLocaleDateString('es-MX', { day: 'numeric', month: 'short', year: 'numeric' });
                    } catch (eSem) { return s; }
                }
                var n = parseInt(s, 10);
                if (!isNaN(n) && n > 10000) {
                    var y = Math.floor(n / 100);
                    var w = n % 100;
                    return 'Sem ' + w + ' (' + y + ')';
                }
                return s;
            }
            if (key === 'por_mes' && /^\d{4}-\d{2}$/.test(s)) {
                try {
                    var d2 = new Date(s + '-01T12:00:00');
                    return d2.toLocaleDateString('es-MX', { month: 'short', year: 'numeric' });
                } catch(e2) { return s; }
            }
            return s;
        }
        var estadisticasDatos = null;
        var estadisticasFiltroPeriodo = 'por_dia';
        var estadisticasFiltroSabueso = 'por_dia';
        var estadisticasDrill = null;
        var estadisticasDrillFilas = null;
        var estadisticasChartTiemposModalSab = null;
        var estadisticasChartTiemposModalGest = null;
        var modalTiemposHistoricoFoco = 'sabueso';
        function ensureEstadChartJs(cb) {
            if (typeof Chart !== 'undefined') { cb(); return; }
            var ex = document.getElementById('scriptChartJsUmd');
            if (ex) { ex.addEventListener('load', cb); return; }
            var s = document.createElement('script');
            s.id = 'scriptChartJsUmd';
            s.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js';
            s.onload = cb;
            document.head.appendChild(s);
        }
        function destroyEstadTiemposModalChart() {
            if (estadisticasChartTiemposModalSab) {
                try { estadisticasChartTiemposModalSab.destroy(); } catch (eSab) {}
                estadisticasChartTiemposModalSab = null;
            }
            if (estadisticasChartTiemposModalGest) {
                try { estadisticasChartTiemposModalGest.destroy(); } catch (eGest) {}
                estadisticasChartTiemposModalGest = null;
            }
        }
        function fmtEstadSemanaLabel(lunesStr) {
            if (!lunesStr || !/^\d{4}-\d{2}-\d{2}$/.test(lunesStr)) return lunesStr || '';
            try {
                var d = new Date(lunesStr + 'T12:00:00');
                var ds = new Date(d);
                ds.setDate(d.getDate() + 6);
                return d.toLocaleDateString('es-MX', { day: 'numeric', month: 'short' }) + ' – ' + ds.toLocaleDateString('es-MX', { day: 'numeric', month: 'short', year: '2-digit' });
            } catch (eL) { return lunesStr; }
        }
        function fmtDeltaTiempoVsSemanaAnteriorHtml(actSeg, antSeg) {
            if (actSeg == null || antSeg == null || antSeg === 0) return '<span class="text-muted">—</span>';
            var p = ((actSeg - antSeg) / antSeg) * 100;
            var s = (p >= 0 ? '+' : '') + Math.round(p) + '%';
            var cls = p <= 0 ? 'text-success' : 'text-danger';
            return '<span class="' + cls + '">' + s + '</span>';
        }
        function rellenarModalTiemposHistoricoTabla(foco) {
            foco = (foco === 'gestor') ? 'gestor' : 'sabueso';
            var thead = document.getElementById('modalEstadTiemposHistoricoThead');
            var tb = document.getElementById('modalEstadTiemposHistoricoTbody');
            var wrapEmpty = document.getElementById('modalEstadTiemposHistoricoSinDatos');
            var wrapContent = document.getElementById('modalEstadTiemposHistoricoContenido');
            if (!thead || !tb) return;
            thead.innerHTML = '';
            tb.innerHTML = '';
            var semRaw = (estadisticasDatos && estadisticasDatos.tiempos_por_semana && estadisticasDatos.tiempos_por_semana.semanas) ? estadisticasDatos.tiempos_por_semana.semanas : [];
            var hasAny = (foco === 'gestor')
                ? semRaw.some(function(row) { var gs = row.gestor; return gs && gs.muestras > 0; })
                : semRaw.some(function(row) { var sb = row.sabueso; return sb && sb.muestras > 0; });
            if (!hasAny) {
                if (wrapEmpty) {
                    wrapEmpty.classList.remove('d-none');
                    wrapEmpty.textContent = 'Aún no hay semanas con muestras suficientes en el histórico para esta métrica.';
                }
                if (wrapContent) wrapContent.classList.add('d-none');
                return;
            }
            if (wrapEmpty) wrapEmpty.classList.add('d-none');
            if (wrapContent) wrapContent.classList.remove('d-none');
            if (foco === 'gestor') {
                thead.innerHTML = '<tr><th>Semana (lun envío)</th><th class="text-end text-success">Gestor (prom.)</th><th class="text-end small text-muted">n</th><th class="text-end small">vs sem. ant.</th></tr>';
            } else {
                thead.innerHTML = '<tr><th>Semana (lun envío)</th><th class="text-end text-warning">Sabueso (prom.)</th><th class="text-end small text-muted">n</th><th class="text-end small">vs sem. ant.</th></tr>';
            }
            var html = '';
            semRaw.forEach(function(row, i) {
                var prev = semRaw[i + 1];
                var label = attrEsc(fmtEstadSemanaLabel(row.lunes));
                if (foco === 'gestor') {
                    var gs = row.gestor;
                    var gesH = (gs && gs.promedio_humano) ? attrEsc(gs.promedio_humano) : '—';
                    var gesN = (gs && gs.muestras != null) ? gs.muestras : '—';
                    var gesAct = gs && gs.promedio_seg != null ? gs.promedio_seg : null;
                    var gesAnt = prev && prev.gestor && prev.gestor.promedio_seg != null ? prev.gestor.promedio_seg : null;
                    html += '<tr><td>' + label + '</td><td class="text-end fw-medium text-success">' + gesH + '</td><td class="text-end small text-muted">' + gesN + '</td><td class="text-end small">' + fmtDeltaTiempoVsSemanaAnteriorHtml(gesAct, gesAnt) + '</td></tr>';
                } else {
                    var sb = row.sabueso;
                    var sabH = (sb && sb.promedio_humano) ? attrEsc(sb.promedio_humano) : '—';
                    var sabN = (sb && sb.muestras != null) ? sb.muestras : '—';
                    var sabAct = sb && sb.promedio_seg != null ? sb.promedio_seg : null;
                    var sabAnt = prev && prev.sabueso && prev.sabueso.promedio_seg != null ? prev.sabueso.promedio_seg : null;
                    html += '<tr><td>' + label + '</td><td class="text-end fw-medium text-warning">' + sabH + '</td><td class="text-end small text-muted">' + sabN + '</td><td class="text-end small">' + fmtDeltaTiempoVsSemanaAnteriorHtml(sabAct, sabAnt) + '</td></tr>';
                }
            });
            tb.innerHTML = html;
        }
        function abrirModalEstadTiemposHistorico(foco) {
            foco = (foco === 'gestor') ? 'gestor' : 'sabueso';
            if (!estadisticasDatos) return;
            modalTiemposHistoricoFoco = foco;
            rellenarModalTiemposHistoricoTabla(foco);
            var blSab = document.getElementById('estadTiemposModalBloqueGrafSab');
            var blGest = document.getElementById('estadTiemposModalBloqueGrafGest');
            if (blSab) blSab.classList.toggle('d-none', foco === 'gestor');
            if (blGest) blGest.classList.toggle('d-none', foco === 'sabueso');
            var tit = document.getElementById('modalEstadTiemposHistoricoLabel');
            if (tit) {
                if (foco === 'gestor') {
                    tit.innerHTML = '<i class="fa-solid fa-chart-line me-2 text-success"></i>Histórico semanal · Gestor (abrir dictamen)';
                } else {
                    tit.innerHTML = '<i class="fa-solid fa-chart-line me-2 text-warning"></i>Histórico semanal · Sabueso (enviar dictamen)';
                }
            }
            var sub = document.getElementById('modalEstadTiemposHistoricoSub');
            if (sub) {
                sub.textContent = 'Semanas por fecha de envío del dictamen. Incluye tickets ya cerrados; excluye eliminados. «vs sem. ant.» = variación frente a la semana calendario anterior (menor tiempo suele ser mejor).';
            }
            var intro = document.getElementById('modalEstadTiemposIntroGraficas');
            if (intro) {
                intro.innerHTML = 'Cada semana es un <strong>lollipop</strong> (tallo fino + círculo) y la <strong>línea</strong> une solo esa métrica en el tiempo. Eje Y en <strong>minutos</strong> (promedio); el tooltip muestra también el texto legible (p. ej. horas).';
            }
            var el = document.getElementById('modalEstadTiemposHistorico');
            if (!el) return;
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(el).show();
            } else if (typeof $ !== 'undefined' && $(el).modal) {
                $(el).modal('show');
            }
        }
        function renderEstadTiemposSemanasChartModal() {
            var foco = (modalTiemposHistoricoFoco === 'gestor') ? 'gestor' : 'sabueso';
            var canvasSab = document.getElementById('estadTiemposHistoricoChartSabueso');
            var canvasGest = document.getElementById('estadTiemposHistoricoChartGestor');
            if (!canvasSab || !canvasGest) return;
            destroyEstadTiemposModalChart();
            var emptySab = document.getElementById('estadTiemposHistoricoChartEmptySab');
            var emptyGest = document.getElementById('estadTiemposHistoricoChartEmptyGest');
            if (emptySab) { emptySab.classList.add('d-none'); emptySab.textContent = ''; }
            if (emptyGest) { emptyGest.classList.add('d-none'); emptyGest.textContent = ''; }
            var semRaw = (estadisticasDatos && estadisticasDatos.tiempos_por_semana && estadisticasDatos.tiempos_por_semana.semanas) ? estadisticasDatos.tiempos_por_semana.semanas : [];
            var sem = semRaw.slice().reverse();
            var labels = sem.map(function(r) { return fmtEstadSemanaLabel(r.lunes); });
            var dsSab = sem.map(function(r) {
                var sb = r.sabueso;
                if (!sb || sb.muestras < 1 || sb.promedio_seg == null) return null;
                return Math.round((sb.promedio_seg / 60) * 100) / 100;
            });
            var dsGest = sem.map(function(r) {
                var gs = r.gestor;
                if (!gs || gs.muestras < 1 || gs.promedio_seg == null) return null;
                return Math.round((gs.promedio_seg / 60) * 100) / 100;
            });
            function serieTieneDato(arr) {
                return arr.some(function(v) { return v != null && !isNaN(v); });
            }
            var wrapSab = document.getElementById('estadTiemposHistoricoWrapSab');
            var wrapGest = document.getElementById('estadTiemposHistoricoWrapGest');
            var okSab = foco === 'sabueso' && serieTieneDato(dsSab);
            var okGest = foco === 'gestor' && serieTieneDato(dsGest);
            if (wrapSab) wrapSab.classList.toggle('d-none', !okSab);
            if (wrapGest) wrapGest.classList.toggle('d-none', !okGest);
            function tooltipLinea(ctx, campKey) {
                var idx = ctx.dataIndex;
                var row = sem[idx];
                var bloque = row ? row[campKey] : null;
                var v = ctx.parsed.y;
                if (v == null || isNaN(v)) return 'Sin dato esta semana';
                var hum = bloque && bloque.promedio_humano ? bloque.promedio_humano : '';
                var n = bloque && bloque.muestras != null ? bloque.muestras : '';
                var minTxt = (Math.round(v * 10) / 10) + ' min';
                if (hum) minTxt += ' · ' + hum;
                if (n !== '') minTxt += ' · n=' + n;
                return minTxt;
            }
            function opcionesLollipop(campKey) {
                return {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            filter: function(item) { return item.datasetIndex === 1; },
                            callbacks: {
                                label: function(ctx) { return tooltipLinea(ctx, campKey); }
                            }
                        }
                    },
                    scales: {
                        x: {
                            offset: true,
                            grid: { display: true, drawOnChartArea: false },
                            ticks: { maxRotation: 45, minRotation: 0, autoSkip: true, maxTicksLimit: 14 }
                        },
                        y: {
                            beginAtZero: true,
                            title: { display: true, text: 'Minutos (promedio)' },
                            ticks: { precision: 1, maxTicksLimit: 8 }
                        }
                    }
                };
            }
            var palSab = { stem: 'rgba(251, 191, 36, 0.6)', line: '#b45309', pointFill: '#fde68a', pointBorder: '#92400e' };
            var palGest = { stem: 'rgba(134, 239, 172, 0.65)', line: '#15803d', pointFill: '#dcfce7', pointBorder: '#166534' };
            function crearLollipop(canvas, valores, campKey, pal) {
                return new Chart(canvas.getContext('2d'), {
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                type: 'bar',
                                label: '',
                                data: valores,
                                barThickness: 2,
                                maxBarThickness: 3,
                                backgroundColor: pal.stem,
                                borderWidth: 0,
                                borderSkipped: false,
                                order: 2
                            },
                            {
                                type: 'line',
                                label: 'Tendencia',
                                data: valores,
                                borderColor: pal.line,
                                backgroundColor: 'transparent',
                                borderWidth: 2.5,
                                tension: 0,
                                spanGaps: false,
                                pointRadius: 6,
                                pointHoverRadius: 8,
                                pointBackgroundColor: pal.pointFill,
                                pointBorderColor: pal.pointBorder,
                                pointBorderWidth: 2,
                                fill: false,
                                order: 1
                            }
                        ]
                    },
                    options: opcionesLollipop(campKey)
                });
            }
            ensureEstadChartJs(function() {
                if (typeof Chart === 'undefined') return;
                if (okSab) {
                    estadisticasChartTiemposModalSab = crearLollipop(canvasSab, dsSab, 'sabueso', palSab);
                } else if (foco === 'sabueso' && emptySab) {
                    emptySab.classList.remove('d-none');
                    emptySab.textContent = 'No hay semanas con promedio Sabueso para graficar.';
                }
                if (okGest) {
                    estadisticasChartTiemposModalGest = crearLollipop(canvasGest, dsGest, 'gestor', palGest);
                } else if (foco === 'gestor' && emptyGest) {
                    emptyGest.classList.remove('d-none');
                    emptyGest.textContent = 'No hay semanas con promedio gestor para graficar.';
                }
            });
        }
        function renderPeriodBreadcrumb() {
            var bc = $('#estadPeriodBreadcrumb');
            if (!estadisticasDrill) {
                bc.addClass('d-none').empty();
                return;
            }
            // Drill desde pestaña Año
            if (estadisticasFiltroPeriodo === 'por_anio') {
            var parts = [];
            parts.push('<span class="estad-drill-back text-primary" style="cursor:pointer" data-drill-back="anio"><i class="fa fa-arrow-left me-1"></i>Años</span>');
            if (estadisticasDrill.nivel === 'meses' && estadisticasDrill.anio) {
                parts.push(' <span class="text-muted">·</span> <strong>' + estadisticasDrill.anio + '</strong>');
            }
            if (estadisticasDrill.nivel === 'semanas' && estadisticasDrill.anio && estadisticasDrill.mes) {
                parts.push(' <span class="text-muted">·</span> <span class="estad-drill-back text-primary" style="cursor:pointer" data-drill-back="meses" data-anio="' + estadisticasDrill.anio + '">Meses</span>');
                var mesStr = estadisticasDrill.mes < 10 ? '0' + estadisticasDrill.mes : '' + estadisticasDrill.mes;
                parts.push(' <span class="text-muted">·</span> <strong>' + estadisticasDrill.anio + '-' + mesStr + '</strong>');
            }
            if (estadisticasDrill.nivel === 'dias' && estadisticasDrill.lunes) {
                parts.push(' <span class="text-muted">·</span> <span class="estad-drill-back text-primary" style="cursor:pointer" data-drill-back="semanas" data-anio="' + (estadisticasDrill.anio||'') + '" data-mes="' + (estadisticasDrill.mes||'') + '">Semanas</span>');
                parts.push(' <span class="text-muted">·</span> <strong>Semana del ' + estadisticasDrill.lunes + '</strong>');
            }
            bc.removeClass('d-none').html(parts.join(''));
            return;
            }
            // Drill desde pestaña Meses: mes → semanas → días
            if (estadisticasFiltroPeriodo === 'por_mes') {
                var partsM = [];
                partsM.push('<span class="estad-drill-back text-primary" style="cursor:pointer" data-drill-back="por_mes_list"><i class="fa fa-arrow-left me-1"></i>Meses</span>');
                if (estadisticasDrill.nivel === 'semanas' && estadisticasDrill.anio && estadisticasDrill.mes) {
                    var mesStr2 = estadisticasDrill.mes < 10 ? '0' + estadisticasDrill.mes : '' + estadisticasDrill.mes;
                    partsM.push(' <span class="text-muted">·</span> <strong>' + estadisticasDrill.anio + '-' + mesStr2 + '</strong> <span class="text-muted small">(elige semana)</span>');
                }
                if (estadisticasDrill.nivel === 'dias' && estadisticasDrill.lunes) {
                    partsM.push(' <span class="text-muted">·</span> <span class="estad-drill-back text-primary" style="cursor:pointer" data-drill-back="semanas_mes" data-anio="' + (estadisticasDrill.anio||'') + '" data-mes="' + (estadisticasDrill.mes||'') + '">Semanas del mes</span>');
                    partsM.push(' <span class="text-muted">·</span> <strong>Semana del ' + estadisticasDrill.lunes + '</strong>');
                }
                bc.removeClass('d-none').html(partsM.join(''));
                return;
            }
            // Drill desde pestaña Semanas: clic en semana → 7 días
            if (estadisticasFiltroPeriodo === 'por_semana' && estadisticasDrill.nivel === 'dias' && estadisticasDrill.lunes) {
                bc.removeClass('d-none').html(
                    '<span class="estad-drill-back text-primary" style="cursor:pointer" data-drill-back="por_semana_list"><i class="fa fa-arrow-left me-1"></i>Semanas del mes</span>' +
                    ' <span class="text-muted">·</span> <strong>Semana del ' + estadisticasDrill.lunes + '</strong> <span class="text-muted small">(por día)</span>'
                );
                return;
            }
            bc.addClass('d-none').empty();
        }
        function renderPeriodList() {
            if (!estadisticasDatos) return;
            var wrap = $('#estadPeriodList');
            wrap.empty();
            renderPeriodBreadcrumb();
            var filas = (estadisticasDrillFilas != null) ? estadisticasDrillFilas : (estadisticasDatos[estadisticasFiltroPeriodo] || []);
            var keyForLabel = estadisticasFiltroPeriodo;
            if (estadisticasDrill && estadisticasDrill.nivel === 'meses') keyForLabel = 'por_mes';
            if (estadisticasDrill && estadisticasDrill.nivel === 'semanas') keyForLabel = 'por_semana';
            if (estadisticasDrill && estadisticasDrill.nivel === 'dias') keyForLabel = 'por_dia';
            if (!filas.length) {
                wrap.append('<p class="text-muted small px-2 mb-0">Sin datos para este período.</p>');
                return;
            }
            var maxN = 1;
            filas.forEach(function(r) { var v = parseInt(r.n, 10); if (!isNaN(v) && v > maxN) maxN = v; });
            filas.forEach(function(row) {
                var label = labelPeriodo(keyForLabel, row.periodo);
                var n = parseInt(row.n, 10);
                if (isNaN(n)) n = 0;
                var pct = Math.round((n / maxN) * 100);
                var clickAttr = ' class="estad-period-row"';
                if (estadisticasFiltroPeriodo === 'por_anio' && !estadisticasDrill) {
                    clickAttr = ' class="estad-period-row estad-drill-row" style="cursor:pointer" data-drill="meses" data-anio="' + row.periodo + '"';
                } else if (estadisticasDrill && estadisticasDrill.nivel === 'meses' && /^\d{4}-\d{2}$/.test(String(row.periodo))) {
                    var pm = String(row.periodo).split('-');
                    clickAttr = ' class="estad-period-row estad-drill-row" style="cursor:pointer" data-drill="semanas" data-anio="' + pm[0] + '" data-mes="' + parseInt(pm[1], 10) + '"';
                } else if (estadisticasDrill && estadisticasDrill.nivel === 'semanas' && row.lunes) {
                    clickAttr = ' class="estad-period-row estad-drill-row" style="cursor:pointer" data-drill="dias" data-lunes="' + row.lunes + '" data-anio="' + (estadisticasDrill.anio || '') + '" data-mes="' + (estadisticasDrill.mes || '') + '"';
                } else if (estadisticasFiltroPeriodo === 'por_mes' && !estadisticasDrill && /^\d{4}-\d{2}$/.test(String(row.periodo))) {
                    var pm2 = String(row.periodo).split('-');
                    clickAttr = ' class="estad-period-row estad-drill-row" style="cursor:pointer" data-drill="semanas_desde_mes" data-anio="' + pm2[0] + '" data-mes="' + parseInt(pm2[1], 10) + '"';
                } else if (estadisticasFiltroPeriodo === 'por_semana' && row.lunes && (!estadisticasDrill || estadisticasDrill.nivel !== 'dias')) {
                    clickAttr = ' class="estad-period-row estad-drill-row" style="cursor:pointer" data-drill="dias" data-lunes="' + row.lunes + '"';
                } else if ((estadisticasFiltroPeriodo === 'por_dia' && !estadisticasDrill) || (estadisticasDrill && estadisticasDrill.nivel === 'dias')) {
                    if (/^\d{4}-\d{2}-\d{2}$/.test(String(row.periodo))) {
                        clickAttr = ' class="estad-period-row estad-dia-row" style="cursor:pointer" data-periodo="' + attrEsc(row.periodo) + '"';
                    }
                }
                wrap.append(
                    '<div' + clickAttr + '>' +
                    '<span class="estad-period-label text-body">' + attrEsc(label) + '</span>' +
                    '<div class="estad-mini-bar"><div style="width:0%" data-w="' + pct + '"></div></div>' +
                    '<span class="estad-period-val">' + n + '</span></div>'
                );
            });
            setTimeout(function() {
                wrap.find('.estad-mini-bar > div').each(function() { var w = $(this).data('w'); if (w != null) $(this).css('width', w + '%'); });
            }, 80);
        }
        function cargarDrill(tipo, params, onDone) {
            http.request({
                endpoint: '/sabueso/getEstadisticasLevantadosDrill',
                metodo: 'POST',
                data: JSON.stringify(Object.assign({ tipo: tipo }, params || {})),
                contentType: 'application/json',
                processData: false,
                showLoader: false,
                onSuccess: function(r) {
                    if (r.success && r.filas) {
                        estadisticasDrillFilas = r.filas;
                        if (onDone) onDone(r);
                        renderPeriodList();
                    } else {
                        estadisticasDrillFilas = [];
                        renderPeriodList();
                    }
                },
                onError: function() {
                    estadisticasDrillFilas = [];
                    renderPeriodList();
                }
            });
        }
        function avatarHue(name) {
            var h = 0;
            for (var i = 0; i < (name || '').length; i++) h = ((h << 5) - h) + name.charCodeAt(i);
            return Math.abs(h) % 360;
        }
        function iniciales(nombre) {
            var p = (nombre || '').trim().split(/\s+/);
            if (p.length >= 2) return (p[0][0] + p[1][0]).toUpperCase();
            if (p[0]) return p[0].substring(0, 2).toUpperCase();
            return '?';
        }
        function renderPorGestor() {
            var list = (estadisticasDatos && estadisticasDatos.por_gestor) || [];
            var tbL = $('#tbodyPorGestorLectura');
            var tbPV = $('#tbodyPorGestorPV');
            tbL.empty();
            tbPV.empty();
            if (!list.length) {
                tbL.append('<tr><td colspan="6" class="text-muted">Sin datos por quien levantó (tickets con dictamen enviado en el detalle).</td></tr>');
                tbPV.append('<tr><td colspan="6" class="text-muted">Sin datos.</td></tr>');
                return;
            }
            list.forEach(function(g) {
                var tasa = g.tasa != null ? g.tasa : 0;
                var badgeClass = tasa >= 90 ? 'bg-success' : (tasa >= 70 ? 'bg-warning text-dark' : 'bg-danger');
                var sinLeer = g.sin_leer != null ? g.sin_leer : 0;
                var sinClass = sinLeer === 0 ? 'text-success' : (sinLeer <= 2 ? 'text-warning' : 'text-danger');
                var hue = avatarHue(g.nombre);
                var idp = parseInt(g.id_persona, 10) || 0;
                var clickAttr = idp ? ' class="estad-gestor-fila" style="cursor:pointer" data-id-gestor="' + idp + '"' : '';
                var nombreCell = '<td class="text-start" style="min-width:0"><div class="d-flex align-items-center gap-1">' +
                    '<span class="estad-avatar flex-shrink-0" style="background:hsl(' + hue + ',72%,42%)">' + attrEsc(iniciales(g.nombre)) + '</span>' +
                    '<div class="d-flex flex-column flex-grow-1" style="min-width:0"><span class="small fw-medium text-truncate" style="max-width:100%" title="' + attrEsc(g.nombre || '') + '">' + attrEsc(g.nombre || '') + '</span>' +
                    (idp ? '<small class="text-muted"><i class="fa-solid fa-fingerprint me-1"></i>ID ' + idp + '</small>' : '') + '</div></div></td>';
                // Vista lectura (sin cumplimiento)
                tbL.append(
                    '<tr' + clickAttr + '>' + nombreCell +
                    '<td class="text-center fw-bold text-primary">' + (g.tickets || 0) + '</td>' +
                    '<td class="text-center small text-muted">' + attrEsc(g.tiempo_lectura || '—') + '</td>' +
                    '<td class="text-center small text-muted">' + attrEsc(g.tiempo_envio || '—') + '</td>' +
                    '<td class="text-center fw-bold ' + sinClass + '">' + sinLeer + '</td>' +
                    '<td class="text-center"><span class="badge rounded-pill ' + badgeClass + '">' + tasa + '%</span></td></tr>'
                );
                // Vista pagos/visitas + cumplimiento
                var pctC = g.cumplimiento_pct_promedio;
                var pctCTxt = (pctC !== null && pctC !== undefined) ? pctC + '%' : '—';
                var resumenC = g.cumplimiento_resumen_texto || '';
                var tipC = 'Evaluados: ' + (g.cumplimiento_evaluados || 0) + ' | Sin evaluar: ' + (g.cumplimiento_sin_evaluar || 0) + (resumenC ? ' | ' + resumenC : '');
                var badgeC = (pctC !== null && pctC >= 70) ? 'bg-success' : ((pctC !== null && pctC >= 40) ? 'bg-warning text-dark' : 'bg-secondary');
                var cumplCell = '<span class="badge rounded-pill ' + badgeC + ' estad-tooltip-custom" data-bs-toggle="tooltip" data-bs-placement="left" title="' + attrEsc(tipC) + '">' + pctCTxt + '</span>';
                if (pctC === null && (g.cumplimiento_sin_evaluar || 0) === (g.tickets || 0)) {
                    cumplCell = '<span class="text-muted small">Sin DS</span>';
                }
                var pagaron = parseInt(g.pagaron, 10) || 0;
                var visitaron = parseInt(g.visitaron, 10) || 0;
                var prorrogaN = parseInt(g.prorroga_dadas, 10) || 0;
                var ticketsN = parseInt(g.tickets, 10) || 0;
                tbPV.append(
                    '<tr' + clickAttr + '>' + nombreCell +
                    '<td class="text-center fw-bold text-primary">' + ticketsN + '</td>' +
                    '<td class="text-center fw-semibold text-success">' + pagaron + '</td>' +
                    '<td class="text-center fw-semibold" style="color:#0d6efd;">' + visitaron + '</td>' +
                    '<td class="text-center fw-semibold" style="color:#d97706;">' + prorrogaN + '</td>' +
                    '<td class="text-center">' + cumplCell + '</td></tr>'
                );
            });
        }
        var vistaGestorTablaActual = 'lectura';
        function aplicarVistaGestorTabla(key) {
            var k = key || 'lectura';
            vistaGestorTablaActual = k;
            $('#grpVistaGestorTabla .btn').removeClass('active');
            $('#grpVistaGestorTabla .btn[data-vista-gestor="' + k + '"]').addClass('active');
            if (k === 'pagos_visitas') {
                $('#wrapTablaGestorLectura').addClass('d-none');
                $('#wrapTablaGestorPV').removeClass('d-none');
            } else {
                $('#wrapTablaGestorPV').addClass('d-none');
                $('#wrapTablaGestorLectura').removeClass('d-none');
            }
            initEstadisticasTooltips();
        }
        function renderPorSabueso() {
            var list = (estadisticasDatos && estadisticasDatos.por_sabueso) || [];
            var tb = $('#tbodyPorSabueso');
            tb.empty();
            if (!list.length) {
                tb.append('<tr><td colspan="4" class="text-muted">Sin dictámenes en este período con autor asignado. Si acaba de enviar, vuelva a cargar: el sistema rellena el autor con quien envía o con la última asignación antes del envío.</td></tr>');
                return;
            }
            list.forEach(function(s) {
                var idp = parseInt(s.id_persona, 10) || 0;
                var rowClass = (idp === 797 ? 'table-warning ' : '') + (idp ? 'estad-sabueso-fila' : '');
                var nombreEsc = attrEsc((s.nombre || '').trim());
                var badge797 = idp === 797 ? ' <span class="badge bg-secondary">797</span>' : '';
                var crea1a = s.creacion_a_primera_asignacion_humano || s.tiempo_hasta_asignarle_humano || '—';
                var clickAttr = idp ? ' data-id-sabueso="' + idp + '" style="cursor:pointer"' : '';
                var hue = avatarHue(s.nombre || '');
                tb.append(
                    '<tr class="' + rowClass.trim() + '"' + clickAttr + '><td><div class="d-flex align-items-center gap-2">' +
                    '<span class="estad-avatar" style="background:hsl(' + hue + ',72%,42%)">' + attrEsc(iniciales(s.nombre || '')) + '</span>' +
                    '<span class="small fw-medium text-truncate" style="max-width:10rem" title="' + nombreEsc + '">' + nombreEsc + '</span>' + badge797 + '</div></td>' +
                    '<td class="text-center fw-bold text-primary">' + (s.dictaminados || 0) + '</td>' +
                    '<td class="text-center small">' + attrEsc(crea1a) + '</td>' +
                    '<td class="text-center small">' + attrEsc(s.tiempo_asignado_a_envio_humano || '—') + '</td></tr>'
                );
            });
        }
        function initEstadisticasTooltips() {
            if (typeof bootstrap === 'undefined' || !bootstrap.Tooltip) return;
            document.querySelectorAll('#panelResumenSabueso [data-bs-toggle="tooltip"], #panelResumenGestor [data-bs-toggle="tooltip"]').forEach(function(el) {
                var ex = bootstrap.Tooltip.getInstance(el);
                if (ex) ex.dispose();
                new bootstrap.Tooltip(el);
            });
        }
        function initTooltipsEnSwal() {
            if (typeof bootstrap === 'undefined' || !bootstrap.Tooltip) return;
            var root = document.querySelector('.swal2-html-container');
            if (!root) return;
            root.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
                var ex = bootstrap.Tooltip.getInstance(el);
                if (ex) ex.dispose();
                new bootstrap.Tooltip(el, { customClass: 'estad-tooltip-custom', container: 'body' });
            });
        }
        function initTooltipsReporteSemanalModal() {
            if (typeof bootstrap === 'undefined' || !bootstrap.Tooltip) return;
            var root = document.getElementById('modalReporteSemanalGlobalBody');
            if (!root) return;
            root.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
                var ex = bootstrap.Tooltip.getInstance(el);
                if (ex) ex.dispose();
                new bootstrap.Tooltip(el, { customClass: 'estad-tooltip-custom', container: 'body' });
            });
        }
        function destroyReporteSemanalModalCharts() {
            var list = window._reporteSemanalChartInstances || [];
            list.forEach(function(ch) { try { ch.destroy(); } catch (e) {} });
            window._reporteSemanalChartInstances = [];
        }
        function abrirZoomChartReporteSemanalFromCanvas(canvasEl, titulo) {
            if (!canvasEl || typeof Chart === 'undefined') return;
            var sourceChart = Chart.getChart(canvasEl);
            if (!sourceChart) return;
            var modalEl = document.getElementById('modalReporteSemanalChartZoom');
            var zoomCanvas = document.getElementById('canvasReporteSemanalChartZoom');
            var titleEl = document.getElementById('modalReporteSemanalChartZoomLabel');
            if (!modalEl || !zoomCanvas) return;
            if (window._reporteSemanalZoomChartInstance) {
                try { window._reporteSemanalZoomChartInstance.destroy(); } catch (e) {}
                window._reporteSemanalZoomChartInstance = null;
            }
            if (titleEl) titleEl.textContent = titulo || 'Gráfica';
            var dataCopy;
            var optsCopy;
            try {
                dataCopy = JSON.parse(JSON.stringify(sourceChart.data));
            } catch (e0) { return; }
            try {
                optsCopy = JSON.parse(JSON.stringify(sourceChart.options, function(k, v) { return typeof v === 'function' ? undefined : v; }));
            } catch (e1) {
                optsCopy = { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: true, position: 'bottom' } } };
            }
            optsCopy.responsive = true;
            optsCopy.maintainAspectRatio = false;
            var cfg = { type: sourceChart.config.type, data: dataCopy, options: optsCopy };
            if (typeof bootstrap === 'undefined' || !bootstrap.Modal) return;
            var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
            modalEl.addEventListener('shown.bs.modal', function onZoomShown() {
                modalEl.removeEventListener('shown.bs.modal', onZoomShown);
                requestAnimationFrame(function() {
                    requestAnimationFrame(function() {
                        try {
                            window._reporteSemanalZoomChartInstance = new Chart(zoomCanvas, cfg);
                        } catch (e2) { console.error(e2); }
                    });
                });
            }, { once: true });
        }
        function bindReporteSemanalChartCardsClick(wrap) {
            if (!wrap) return;
            wrap.querySelectorAll('.estad-rs-chart-card').forEach(function(card) {
                card.addEventListener('keydown', function(ev) {
                    if (ev.key === 'Enter' || ev.key === ' ') {
                        ev.preventDefault();
                        card.click();
                    }
                });
                card.addEventListener('click', function() {
                    var cv = card.querySelector('canvas');
                    var cap = card.querySelector('.estad-rs-chart-caption');
                    var tit = cap ? (cap.textContent || '').trim() : 'Gráfica';
                    if (cv) abrirZoomChartReporteSemanalFromCanvas(cv, tit);
                });
            });
        }
        function swalCargandoDetalle(titulo) {
            if (typeof Swal === 'undefined') return;
            Swal.fire({
                title: titulo || 'Cargando…',
                html: '<p class="mb-0 text-muted">Obteniendo detalle, espere un momento.</p>',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: function() { if (Swal.showLoading) Swal.showLoading(); }
            });
        }
        function swalCargandoDetalleConProgreso(titulo) {
            if (typeof Swal === 'undefined') return null;
            var pct = 0;
            var target = 97;
            Swal.fire({
                title: titulo || 'Cargando…',
                html: '<p class="mb-2 text-muted">Obteniendo detalle, espere un momento.</p>' +
                    '<div class="progress" style="height:10px;"><div id="estadDetalleProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width:0%"></div></div>' +
                    '<div class="small mt-2 text-end"><strong id="estadDetalleProgressPct">0%</strong></div>',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false
            });
            var iv = setInterval(function() {
                if (pct >= target) return;
                var inc = Math.max(0.3, (target - pct) * 0.04);
                pct = Math.min(target, pct + inc);
                var pctInt = Math.floor(pct);
                var bar = document.getElementById('estadDetalleProgressBar');
                var pctEl = document.getElementById('estadDetalleProgressPct');
                if (bar) bar.style.width = pctInt + '%';
                if (pctEl) pctEl.textContent = pctInt + '%';
            }, 250);
            return {
                stopAndClose: function() {
                    clearInterval(iv);
                    var bar = document.getElementById('estadDetalleProgressBar');
                    var pctEl = document.getElementById('estadDetalleProgressPct');
                    if (bar) bar.style.width = '100%';
                    if (pctEl) pctEl.textContent = '100%';
                    if (typeof Swal !== 'undefined' && Swal.close) Swal.close();
                }
            };
        }
        function abrirDetalleSabueso(idPersona) {
            if (!idPersona) return;
            swalCargandoDetalle('Cargando dictámenes…');
            http.request({
                endpoint: '/sabueso/getEstadisticasSabuesoDetalle',
                metodo: 'POST',
                data: JSON.stringify({ id_persona_autor: idPersona }),
                contentType: 'application/json',
                processData: false,
                showLoader: false,
                onSuccess: function(r) {
                    if (typeof Swal !== 'undefined' && Swal.close) Swal.close();
                    if (!r.success) { if (typeof Swal !== 'undefined') Swal.fire('Aviso', r.mensaje || 'Sin datos', 'info'); return; }
                    var filas = r.filas || [];
                    if (!filas.length) { if (typeof Swal !== 'undefined') Swal.fire('Aviso', 'Sin dictámenes enviados con este autor.', 'info'); return; }
                    function tip(title) { return ' <i class="fa fa-question-circle estad-modal-th-tip text-success" data-bs-toggle="tooltip" data-bs-placement="top" title="' + attrEsc(title) + '"></i>'; }
                    var html = '<div class="estad-modal-detalle-wrap">' +
                        '<div class="estad-modal-detalle-leyenda text-start">' +
                        '<strong>Vista Sabueso.</strong> <em>Asignado→envío</em>: tiempo desde que le quedó asignado el ticket hasta enviar (solo si consta asignación a esta persona antes del envío). ' +
                        '<em>Cola 1ª asign.</em>: desde creación del ticket hasta la primera asignación en Sabueso.</div>' +
                        '<div class="estad-modal-detalle-table-wrap table-responsive">' +
                        '<table class="table table-sm table-bordered text-start">' +
                        '<thead><tr>' +
                        '<th>Folio' + tip('Identificador del ticket.') + '</th>' +
                        '<th>Levantó' + tip('Gestor que creó el ticket (no el Sabueso).') + '</th>' +
                        '<th>Enviado' + tip('Fecha/hora en que se envió el dictamen al gestor.') + '</th>' +
                        '<th>¿Asignado?' + tip('Sí si en asignacion_ticket consta que le quedó asignado a este Sabueso antes del envío.') + '</th>' +
                        '<th>Asignado→envío' + tip('Tiempo desde última asignación a él hasta enviar. — si no estaba asignado a él.') + '</th>' +
                        '<th>Cola 1ª' + tip('Tiempo desde creación del ticket hasta la primera asignación (cualquier persona).') + '</th>' +
                        '<th>Gestor vio' + tip('Si el gestor ya abrió el dictamen (fecha_visto_gestor).') + '</th>' +
                        '<th>T. lectura' + tip('Tiempo del gestor entre envío y apertura. — si no aplica o sin dato.') + '</th>' +
                        '</tr></thead><tbody>';
                    filas.forEach(function(f) {
                        html += '<tr><td class="fw-medium">' + attrEsc(f.folio || '—') + '</td><td>' + attrEsc(f.creador_nombre || '—') + '</td>' +
                            '<td>' + fmtFecha(f.dictamen_envio) + '</td><td>' + attrEsc(f.estaba_asignado_a_el || '—') + '</td>' +
                            '<td>' + attrEsc(f.tiempo_asignado_a_envio_humano || '—') + '</td>' +
                            '<td>' + attrEsc(f.tiempo_cola_hasta_primera_asignacion_humano || '—') + '</td>' +
                            '<td>' + attrEsc(f.visto_si_no || '—') + '</td><td>' + attrEsc(f.tiempo_lectura_gestor_humano || '—') + '</td></tr>';
                    });
                    html += '</tbody></table></div></div>';
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Sabueso · ' + (r.nombre || ''),
                            html: html,
                            width: '940px',
                            showConfirmButton: true,
                            confirmButtonText: 'Cerrar',
                            customClass: { container: 'estad-detalle-swal-container', popup: 'estad-detalle-swal', confirmButton: 'btn btn-success px-4' },
                            didOpen: function() { initTooltipsEnSwal(); }
                        });
                    }
                },
                onError: function() {
                    if (typeof Swal !== 'undefined' && Swal.close) Swal.close();
                    if (typeof Swal !== 'undefined') Swal.fire('Error', 'No se pudo cargar el detalle.', 'error');
                }
            });
        }
        var perPageGestorDetalle = 20;
        function abrirDetalleGestor(idPersona, nombre, vistaTabla) {
            if (!idPersona) return;
            vistaTabla = vistaTabla || 'lectura';
            var progressHandle = (vistaTabla === 'pagos_visitas') ? swalCargandoDetalleConProgreso('Cargando tickets del gestor…') : null;
            if (!progressHandle) swalCargandoDetalle('Cargando tickets del gestor…');
            http.request({
                endpoint: '/sabueso/getEstadisticasGestorDetalle',
                metodo: 'POST',
                data: JSON.stringify({ id_persona_creador: idPersona, page: 1, per_page: perPageGestorDetalle, vista: vistaTabla }),
                contentType: 'application/json',
                processData: false,
                showLoader: false,
                onSuccess: function(r) {
                    if (progressHandle && progressHandle.stopAndClose) progressHandle.stopAndClose(); else if (typeof Swal !== 'undefined' && Swal.close) Swal.close();
                    if (!r.success) { if (typeof Swal !== 'undefined') Swal.fire('Aviso', r.mensaje || 'Sin datos', 'info'); return; }
                    var filas = r.filas || [];
                    var total = (r.total != null && r.total !== '') ? parseInt(r.total, 10) : filas.length;
                    var page = (r.page != null && r.page !== '') ? parseInt(r.page, 10) : 1;
                    var perPage = (r.per_page != null && r.per_page !== '') ? parseInt(r.per_page, 10) : perPageGestorDetalle;
                    if (!filas.length && total === 0) { if (typeof Swal !== 'undefined') Swal.fire('Aviso', 'Sin tickets con dictamen enviado para este gestor.', 'info'); return; }
                    function tipG(title) { return ' <i class="fa fa-question-circle estad-modal-th-tip text-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="' + attrEsc(title) + '"></i>'; }
                    function extension12hDetalleCell(prV) {
                        if (prV === 'Intensidad') return '<span class="text-primary fw-semibold">Intensidad</span>';
                        if (prV === 'Prórroga') return '<span class="text-warning fw-semibold">Prórroga</span>';
                        if (prV === 'Sí' || prV === 'Si') return '<span class="text-warning fw-semibold">Prórroga</span>';
                        if (prV === 'No') return '<span class="text-muted">—</span>';
                        return '<span class="text-muted">—</span>';
                    }
                    function filaPagoVisitoCumpl(f) {
                        var pctF = f.pct_efectividad;
                        var pctFTxt = (pctF !== null && pctF !== undefined && pctF !== '') ? pctF + '%' : '<span class="text-muted">Sin DS</span>';
                        var resMostrar = (f.resultado_ds_mostrar != null && f.resultado_ds_mostrar !== '') ? attrEsc(f.resultado_ds_mostrar) : '<span class="text-muted">Sin dictamen sistema</span>';
                        var pagoV = f.pago_en_ventana_resumen;
                        var pagoVTxt = (pagoV === 'Sí' || pagoV === 'Si') ? '<span class="text-success fw-semibold">Sí</span>' : (pagoV === 'No' ? '<span class="text-danger fw-semibold">No</span>' : '<span class="text-muted">—</span>');
                        var visitoV = f.visito_campo_resumen;
                        var visitoVTxt = (visitoV === 'Sí' || visitoV === 'Si') ? '<span class="text-success fw-semibold">Sí</span>' : (visitoV === 'No' ? '<span class="text-danger fw-semibold">No</span>' : '<span class="text-muted">—</span>');
                        var cumplTxt = (f.cumplimiento_etiqueta != null && f.cumplimiento_etiqueta !== '') ? attrEsc(f.cumplimiento_etiqueta) : resMostrar;
                        var pagoSemanaSi = f.pago_durante_semana_si === true || f.pago_durante_semana_si === '1';
                        var pagoSemanaCount = (f.pago_durante_semana_count != null && f.pago_durante_semana_count !== '') ? parseInt(f.pago_durante_semana_count, 10) : 0;
                        var pagoSemanaTxt = pagoSemanaSi ? '<span class="text-success fw-semibold">Sí</span>' : '<span class="text-muted">No</span>';
                        if (pagoSemanaCount > 0) pagoSemanaTxt += ' <span class="text-muted small">(' + pagoSemanaCount + ')</span>';
                        var pagoPr = f.pago_en_prorroga_resumen;
                        var pagoProrrogaTxt = (pagoPr === 'Sí' || pagoPr === 'Si') ? '<span class="text-success fw-semibold">Sí</span>' : (pagoPr === 'No' ? '<span class="text-danger fw-semibold">No</span>' : '<span class="text-muted">—</span>');
                        return { pctFTxt: pctFTxt, pagoVTxt: pagoVTxt, visitoVTxt: visitoVTxt, cumplTxt: cumplTxt, pagoSemanaTxt: pagoSemanaTxt, pagoProrrogaTxt: pagoProrrogaTxt };
                    }
                    function buildPaginationBlock(tot, p, pp) {
                        var totalPages = Math.ceil(tot / Math.max(1, pp));
                        var desde = tot === 0 ? 0 : (p - 1) * pp + 1;
                        var hasta = Math.min(p * pp, tot);
                        var s20 = pp === 20 ? ' selected' : '';
                        var s50 = pp === 50 ? ' selected' : '';
                        var s100 = pp === 100 ? ' selected' : '';
                        var sel = '<select id="estadGestorPerPage" class="form-select form-select-sm estad-gestor-perpage" style="width:auto;display:inline-block;">' +
                            '<option value="20"' + s20 + '>20</option><option value="50"' + s50 + '>50</option><option value="100"' + s100 + '>100</option></select>';
                        var txt = tot === 0 ? '0 resultados' : ('Mostrando ' + desde + '–' + hasta + ' de ' + tot);
                        var prevDisabled = p <= 1 ? ' disabled' : '';
                        var nextDisabled = p >= totalPages ? ' disabled' : '';
                        return '<div id="estadGestorModalPagination" class="estad-modal-detalle-pager d-flex align-items-center justify-content-between flex-wrap gap-2" data-total="' + tot + '" data-page="' + p + '" data-per-page="' + pp + '">' +
                            '<span class="text-muted small">' + txt + '</span>' +
                            '<div class="d-flex align-items-center gap-2 flex-wrap">' +
                            '<label class="text-muted small mb-0 d-flex align-items-center gap-1">Filas por pág. ' + sel + '</label>' +
                            '<div class="btn-group btn-group-sm">' +
                            '<button type="button" class="btn btn-outline-secondary estad-gestor-prev"' + prevDisabled + '>Anterior</button>' +
                            '<button type="button" class="btn btn-outline-secondary estad-gestor-next"' + nextDisabled + '>Siguiente</button>' +
                            '</div></div></div>';
                    }
                    var html;
                    var swalWidth = '1180px';
                    if (vistaTabla === 'pagos_visitas') {
                        html = '<div class="estad-modal-detalle-wrap" id="estadGestorModalWrap" data-id-persona="' + attrEsc(String(idPersona)) + '" data-vista="pagos_visitas">' +
                            '<div class="estad-modal-detalle-leyenda text-start">' +
                            '<strong>Vista Pagos y visitas.</strong> Por ticket: si hubo <strong>pago</strong> en la ventana que evalúa el DS, si hubo <strong>visita de campo</strong>, etiqueta de <strong>cumplimiento</strong> y <strong>% efectividad</strong>. ' +
                            '<strong>Pago esta semana:</strong> desde el lunes 00:00 (CDMX) hasta ahora, por id_credito (estado de cuenta). ' +
                            'Si sale «Sin DS», generar dictamen del sistema en Panel Admin.</div>' +
                            '<div class="estad-modal-detalle-table-wrap table-responsive">' +
                            '<table class="table table-sm table-bordered text-start">' +
                            '<thead><tr>' +
                            '<th>Folio' + tipG('Ticket.') + '</th>' +
                            '<th>Persona / ID cr.' + tipG('Nombre e ID de crédito de la persona por la cual se levantó el ticket (no del gestor).') + '</th>' +
                            '<th>Dictamen enviado' + tipG('Referencia temporal del envío al gestor.') + '</th>' +
                            '<th>Pagaron' + tipG('Sí = pago en estado de cuenta en la ventana 12h.') + '</th>' +
                            '<th>Visitaron' + tipG('Sí = visita de campo registrada.') + '</th>' +
                            '<th>Extensi\u00f3n' + tipG('Pr\u00f3rroga o Intensidad: segunda ventana de +12 h seg\u00fan el dictamen del sistema.') + '</th>' +
                            '<th>Pago en 2.\u00aa ventana (+12 h)' + tipG('Pago en estado de cuenta en la segunda ventana (Pr\u00f3rroga o Intensidad), seg\u00fan fechas del DS.') + '</th>' +
                            '<th>Pago esta semana' + tipG('Desde lunes 00:00 CDMX de la semana actual hasta ahora; cantidad entre paréntesis.') + '</th>' +
                            '<th>Cumplimiento' + tipG('Etiqueta del dictamen sistema.') + '</th>' +
                            '<th>% efect.' + tipG('Porcentaje de efectividad.') + '</th>' +
                            '</tr></thead><tbody id="estadGestorModalTbody">';
                        filas.forEach(function(f) {
                            var x = filaPagoVisitoCumpl(f);
                            var prTxt = extension12hDetalleCell(f.prorroga_otorgada_resumen);
                            var creditoCell = '<div>' + attrEsc(f.nombre_cliente || '—') + '</div><div class="text-muted small">ID ' + (f.id_credito != null ? f.id_credito : '—') + '</div>';
                            html += '<tr><td class="fw-medium">' + attrEsc(f.folio || '—') + '</td><td>' + creditoCell + '</td><td>' + fmtFecha(f.dictamen_envio) + '</td>' +
                                '<td class="text-center">' + x.pagoVTxt + '</td>' +
                                '<td class="text-center">' + x.visitoVTxt + '</td>' +
                                '<td class="text-center">' + prTxt + '</td>' +
                                '<td class="text-center">' + x.pagoProrrogaTxt + '</td>' +
                                '<td class="text-center">' + x.pagoSemanaTxt + '</td>' +
                                '<td><small>' + x.cumplTxt + '</small></td>' +
                                '<td>' + x.pctFTxt + '</td></tr>';
                        });
                        html += '</tbody></table></div>' + buildPaginationBlock(total, page, perPage) + '</div>';
                        swalWidth = '1280px';
                    } else {
                        html = '<div class="estad-modal-detalle-wrap" id="estadGestorModalWrap" data-id-persona="' + attrEsc(String(idPersona)) + '" data-vista="lectura">' +
                            '<div class="estad-modal-detalle-leyenda text-start">' +
                            '<strong>Vista Lectura y tasa.</strong> Tickets levantados por este gestor con dictamen enviado.<br>' +
                            '<strong>¿Abrió?</strong> / <strong>T. apertura</strong> = lectura del dictamen tras el envío. ' +
                            '<strong>Resultado DS</strong> y <strong>% efect.</strong> cuando ya existe dictamen del sistema.</div>' +
                            '<div class="estad-modal-detalle-table-wrap table-responsive">' +
                            '<table class="table table-sm table-bordered text-start">' +
                            '<thead><tr>' +
                            '<th>Folio' + tipG('Identificador del ticket.') + '</th>' +
                            '<th>Persona / ID cr.' + tipG('Nombre e ID de crédito de la persona por la cual se levantó el ticket (no del gestor).') + '</th>' +
                            '<th>Levantado' + tipG('Cuándo se creó el ticket.') + '</th>' +
                            '<th>Dictamen enviado' + tipG('Cuándo se envió el dictamen al gestor.') + '</th>' +
                            '<th>¿Abrió?' + tipG('Si ya abrió/vio el dictamen.') + '</th>' +
                            '<th>Abrió el' + tipG('Fecha/hora visto.') + '</th>' +
                            '<th>T. apertura' + tipG('Tiempo entre envío y apertura.') + '</th>' +
                            '<th>Resultado DS' + tipG('Texto claro del dictamen sistema.') + '</th>' +
                            '<th>% efect.' + tipG('% efectividad según DS.') + '</th>' +
                            '</tr></thead><tbody id="estadGestorModalTbody">';
                        filas.forEach(function(f) {
                            var pctF = f.pct_efectividad;
                            var pctFTxt = (pctF !== null && pctF !== undefined && pctF !== '') ? pctF + '%' : '<span class="text-muted">Sin DS</span>';
                            var resMostrar = (f.resultado_ds_mostrar != null && f.resultado_ds_mostrar !== '') ? attrEsc(f.resultado_ds_mostrar) : '<span class="text-muted">Sin dictamen sistema</span>';
                            var creditoCellL = '<div>' + attrEsc(f.nombre_cliente || '—') + '</div><div class="text-muted small">ID ' + (f.id_credito != null ? f.id_credito : '—') + '</div>';
                            html += '<tr><td class="fw-medium">' + attrEsc(f.folio || '—') + '</td><td>' + creditoCellL + '</td><td>' + fmtFecha(f.fecha_creacion) + '</td>' +
                                '<td>' + fmtFecha(f.dictamen_envio) + '</td><td>' + attrEsc(f.visto_si_no || '—') + '</td>' +
                                '<td>' + (f.visto_cuando ? fmtFecha(f.visto_cuando) : '—') + '</td>' +
                                '<td>' + attrEsc(f.tiempo_lectura_humano || 'Pendiente') + '</td>' +
                                '<td><small>' + resMostrar + '</small></td>' +
                                '<td>' + pctFTxt + '</td></tr>';
                        });
                        html += '</tbody></table></div>' + buildPaginationBlock(total, page, perPage) + '</div>';
                    }
                    if (typeof Swal !== 'undefined') {
                        var tituloModal = (r.nombre || nombre || '');
                        if (vistaTabla === 'pagos_visitas') {
                            tituloModal = 'Pagos y visitas · ' + tituloModal;
                        } else {
                            tituloModal = 'Lectura y tasa · ' + tituloModal;
                        }
                        Swal.fire({
                            title: tituloModal,
                            html: html,
                            width: swalWidth,
                            showConfirmButton: true,
                            confirmButtonText: 'Cerrar',
                            customClass: { container: 'estad-detalle-swal-container', popup: 'estad-detalle-swal', confirmButton: 'btn btn-primary px-4' },
                            didOpen: function() {
                                initTooltipsEnSwal();
                                var wrap = document.getElementById('estadGestorModalWrap');
                                var pagDiv = document.getElementById('estadGestorModalPagination');
                                if (!wrap) return;
                                var idPersona = wrap.getAttribute('data-id-persona');
                                var vista = wrap.getAttribute('data-vista') || 'lectura';
                                function loadPage(p, optPerPage) {
                                    var pp = optPerPage != null ? optPerPage : (parseInt(pagDiv.getAttribute('data-per-page'), 10) || perPageGestorDetalle);
                                    var sel = document.getElementById('estadGestorPerPage');
                                    if (sel && optPerPage == null) pp = parseInt(sel.value, 10) || pp;
                                    http.request({
                                        endpoint: '/sabueso/getEstadisticasGestorDetalle',
                                        metodo: 'POST',
                                        data: JSON.stringify({ id_persona_creador: idPersona, page: p, per_page: pp, vista: vista }),
                                        contentType: 'application/json',
                                        processData: false,
                                        showLoader: false,
                                        onSuccess: function(res) {
                                            if (!res.success || !res.filas) return;
                                            var filas = res.filas;
                                            var total = parseInt(res.total, 10) || 0;
                                            var page = parseInt(res.page, 10) || 1;
                                            var perPage = parseInt(res.per_page, 10) || perPageGestorDetalle;
                                            var tbody = document.getElementById('estadGestorModalTbody');
                                            if (tbody) {
                                                var rowsHtml = '';
                                                if (vista === 'pagos_visitas') {
                                                    filas.forEach(function(f) {
                                                        var x = filaPagoVisitoCumpl(f);
                                                        var prTxt = extension12hDetalleCell(f.prorroga_otorgada_resumen);
                                                        var creditoCell = '<div>' + attrEsc(f.nombre_cliente || '—') + '</div><div class="text-muted small">ID ' + (f.id_credito != null ? f.id_credito : '—') + '</div>';
                                                        rowsHtml += '<tr><td class="fw-medium">' + attrEsc(f.folio || '—') + '</td><td>' + creditoCell + '</td><td>' + fmtFecha(f.dictamen_envio) + '</td>' +
                                                            '<td class="text-center">' + x.pagoVTxt + '</td><td class="text-center">' + x.visitoVTxt + '</td>' +
                                                            '<td class="text-center">' + prTxt + '</td><td class="text-center">' + x.pagoProrrogaTxt + '</td>' +
                                                            '<td class="text-center">' + x.pagoSemanaTxt + '</td>' +
                                                            '<td><small>' + x.cumplTxt + '</small></td><td>' + x.pctFTxt + '</td></tr>';
                                                    });
                                                } else {
                                                    filas.forEach(function(f) {
                                                        var pctFTxt = (f.pct_efectividad != null && f.pct_efectividad !== '') ? f.pct_efectividad + '%' : '<span class="text-muted">Sin DS</span>';
                                                        var resMostrar = (f.resultado_ds_mostrar != null && f.resultado_ds_mostrar !== '') ? attrEsc(f.resultado_ds_mostrar) : '<span class="text-muted">Sin dictamen sistema</span>';
                                                        var creditoCellL = '<div>' + attrEsc(f.nombre_cliente || '—') + '</div><div class="text-muted small">ID ' + (f.id_credito != null ? f.id_credito : '—') + '</div>';
                                                        rowsHtml += '<tr><td class="fw-medium">' + attrEsc(f.folio || '—') + '</td><td>' + creditoCellL + '</td><td>' + fmtFecha(f.fecha_creacion) + '</td>' +
                                                            '<td>' + fmtFecha(f.dictamen_envio) + '</td><td>' + attrEsc(f.visto_si_no || '—') + '</td>' +
                                                            '<td>' + (f.visto_cuando ? fmtFecha(f.visto_cuando) : '—') + '</td><td>' + attrEsc(f.tiempo_lectura_humano || 'Pendiente') + '</td>' +
                                                            '<td><small>' + resMostrar + '</small></td><td>' + pctFTxt + '</td></tr>';
                                                    });
                                                }
                                                tbody.innerHTML = rowsHtml;
                                            }
                                            var totalPages = perPage > 0 ? Math.ceil(total / perPage) : 1;
                                            var desde = (page - 1) * perPage + 1;
                                            var hasta = Math.min(page * perPage, total);
                                            pagDiv.setAttribute('data-page', page);
                                            pagDiv.setAttribute('data-total', total);
                                            var span = pagDiv.querySelector('span');
                                            if (span) span.textContent = 'Mostrando ' + desde + '–' + hasta + ' de ' + total;
                                            pagDiv.setAttribute('data-per-page', perPage);
                                            var selEl = document.getElementById('estadGestorPerPage');
                                            if (selEl) selEl.value = String(perPage);
                                            var btnPrev = pagDiv.querySelector('.estad-gestor-prev');
                                            var btnNext = pagDiv.querySelector('.estad-gestor-next');
                                            if (btnPrev) { btnPrev.disabled = page <= 1; btnPrev.onclick = function() { if (page > 1) loadPage(page - 1); }; }
                                            if (btnNext) { btnNext.disabled = page >= totalPages; btnNext.onclick = function() { if (page < totalPages) loadPage(page + 1); }; }
                                            initTooltipsEnSwal();
                                        }
                                    });
                                }
                                if (pagDiv) {
                                    var btnPrev = pagDiv.querySelector('.estad-gestor-prev');
                                    var btnNext = pagDiv.querySelector('.estad-gestor-next');
                                    if (btnPrev) btnPrev.onclick = function() { var p = parseInt(pagDiv.getAttribute('data-page'), 10) || 1; if (p > 1) loadPage(p - 1); };
                                    if (btnNext) btnNext.onclick = function() { var p = parseInt(pagDiv.getAttribute('data-page'), 10) || 1; var tot = parseInt(pagDiv.getAttribute('data-total'), 10) || 0; var pp = parseInt(pagDiv.getAttribute('data-per-page'), 10) || perPageGestorDetalle; if (p < Math.ceil(tot / pp)) loadPage(p + 1); };
                                    var perPageSel = document.getElementById('estadGestorPerPage');
                                    if (perPageSel) perPageSel.onchange = function() { loadPage(1, parseInt(perPageSel.value, 10)); };
                                }
                            }
                        });
                    }
                },
                onError: function() {
                    if (progressHandle && progressHandle.stopAndClose) progressHandle.stopAndClose(); else if (typeof Swal !== 'undefined' && Swal.close) Swal.close();
                    if (typeof Swal !== 'undefined') Swal.fire('Error', 'No se pudo cargar el detalle.', 'error');
                }
            });
        }
        function abrirReporteSemanalGlobal(semanaInicio) {
            var modalEl = document.getElementById('modalReporteSemanalGlobal');
            var bodyEl = document.getElementById('modalReporteSemanalGlobalBody');
            if (!modalEl || !bodyEl) {
                if (typeof Swal !== 'undefined') Swal.fire('Error', 'No se encontró el modal de reporte en la página.', 'error');
                return;
            }
            if (typeof Swal !== 'undefined' && Swal.close) Swal.close();
            bodyEl.className = 'p-0 estad-rs-body-loading';
            bodyEl.innerHTML = '<div class="estad-rs-loading-inner text-center text-muted"><i class="fa-solid fa-spinner fa-spin fa-3x mb-3 d-block opacity-75"></i><span>Cargando reporte semanal…</span></div>';
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            } else if (typeof $ !== 'undefined' && $(modalEl).modal) {
                $(modalEl).modal('show');
            }
            http.request({
                endpoint: '/sabueso/getReporteSemanalGestorGlobal',
                metodo: 'POST',
                data: JSON.stringify({ semana_inicio: semanaInicio || '' }),
                contentType: 'application/json',
                processData: false,
                showLoader: false,
                timeout: 900000,
                onSuccess: function(r) {
                    if (typeof Swal !== 'undefined' && Swal.close) Swal.close();
                    var bodyEl2 = document.getElementById('modalReporteSemanalGlobalBody');
                    if (!bodyEl2) return;
                    bodyEl2.classList.remove('estad-rs-body-loading');
                    if (!r || !r.success) {
                        bodyEl2.innerHTML = '<div class="alert alert-warning m-3 mb-0"><i class="fa-solid fa-circle-info me-1"></i>' + attrEsc((r && r.mensaje) || 'Sin datos') + '</div>';
                        return;
                    }
                    var filas = r.filas || [];
                    var semanas = r.semanas || [];
                    var res = r.resumen || {};
                    function boolTxt(v) {
                        if (v === true || v === 1 || v === '1' || v === 'Sí' || v === 'Si') return '<span class="text-success fw-semibold">Sí</span>';
                        if (v === false || v === 0 || v === '0' || v === 'No') return '<span class="text-danger fw-semibold">No</span>';
                        return '<span class="text-muted">—</span>';
                    }
                    function extension12hReporteCell(f) {
                        var t = f.extension_12h_tipo;
                        if (t === 'Intensidad') return '<span class="text-primary fw-semibold">Intensidad</span>';
                        if (t === 'Prórroga') return '<span class="text-warning fw-semibold">Prórroga</span>';
                        if (f.prorroga_si === true || f.prorroga_si === 1 || f.prorroga_si === '1') return '<span class="text-warning fw-semibold">Prórroga</span>';
                        return '<span class="text-muted">—</span>';
                    }
                    function pagoSemanaTxt(f) {
                        var si = f.pago_semana_si === true || f.pago_semana_si === 1 || f.pago_semana_si === '1';
                        var consultado = f.pago_semana_consultado === true || f.pago_semana_consultado === 1 || f.pago_semana_consultado === '1';
                        var n = parseInt(f.pago_semana_count, 10) || 0;
                        var t = si ? '<span class="text-success fw-semibold">Sí</span>' : (consultado ? '<span class="text-muted">No</span>' : '<span class="text-warning" title="Límite de consultas en el reporte masivo o servicio no disponible. Use el botón para consultar este crédito.">No se pudo verificar</span>');
                        if (n > 0) t += ' <span class="text-muted small">(' + n + ')</span>';
                        return t;
                    }
                    function pagoSemanaCellHtml(f) {
                        var txt = pagoSemanaTxt(f);
                        var consultado = f.pago_semana_consultado === true || f.pago_semana_consultado === 1 || f.pago_semana_consultado === '1';
                        var idT = parseInt(f.id_ticket, 10) || 0;
                        var idCr = f.id_credito != null ? parseInt(f.id_credito, 10) : 0;
                        var btn = '';
                        if (!consultado && idT > 0 && idCr > 0 && (r.semana_inicio || '')) {
                            btn = '<button type="button" class="btn btn-outline-secondary btn-sm py-0 px-1 ms-1 reporte-rs-reconsultar-ec" title="Consultar estado de cuenta (este crédito)" data-id-ticket="' + idT + '"><i class="fa-solid fa-rotate"></i></button>';
                        }
                        return '<div class="d-inline-flex align-items-center flex-wrap justify-content-center gap-0">' + txt + btn + '</div>';
                    }
                    function ilocalizableSelectHtml(f) {
                        var idT = parseInt(f.id_ticket, 10) || 0;
                        var il = f.ilocalizable === true || f.ilocalizable === 1 || f.ilocalizable === '1';
                        var ilAuto = (f.ilocalizable_auto !== undefined && f.ilocalizable_auto !== null)
                            ? (f.ilocalizable_auto === true || f.ilocalizable_auto === 1 || f.ilocalizable_auto === '1')
                            : il;
                        var ov = f.ilocalizable_override === true || f.ilocalizable_override === 1 || f.ilocalizable_override === '1';
                        var val = ov ? (il ? '1' : '0') : 'auto';
                        var bigLabel = il ? 'Sí' : 'No';
                        var bigCls = il ? 'text-success' : 'text-danger';
                        var tip = 'Valor en el reporte: ' + bigLabel + '. ';
                        if (!ov) {
                            tip += 'Automático: el sistema dictamina ' + (ilAuto ? 'Sí' : 'No') + ' (ilocalizable).';
                        } else {
                            tip += 'Ajuste manual. La regla del sistema daría ' + (ilAuto ? 'Sí' : 'No') + '.';
                        }
                        var badge = '';
                        if (!ov) {
                            badge = '<span class="reporte-rs-ilocal-src badge rounded-pill border bg-light text-secondary align-middle" style="font-size:0.68rem;font-weight:700;line-height:1.35;" title="' + attrEsc('Automático (regla del sistema)') + '"><i class="fa-solid fa-robot opacity-80 reporte-rs-ilocal-src-ico"></i> autc</span>';
                        } else {
                            badge = '<span class="reporte-rs-ilocal-src badge rounded-pill border bg-warning bg-opacity-15 text-dark align-middle" style="font-size:0.68rem;font-weight:700;line-height:1.35;" title="' + attrEsc('Manual · sistema: ' + (ilAuto ? 'Sí' : 'No')) + '"><i class="fa-solid fa-pen opacity-80 reporte-rs-ilocal-src-ico"></i></span>';
                        }
                        return '<div class="reporte-rs-ilocal-cell d-inline-flex align-items-center justify-content-center flex-nowrap">' +
                            '<span class="reporte-rs-ilocal-left d-inline-flex align-items-center flex-nowrap">' +
                            '<span class="reporte-rs-ilocal-display fw-bold ' + bigCls + '" style="font-size:0.9rem;line-height:1.15;">' + bigLabel + '</span>' +
                            badge +
                            '</span>' +
                            '<select class="form-select form-select-sm reporte-rs-ilocalizable-select py-0 align-middle" style="font-size:0.7rem;height:1.55rem;line-height:1.1;" title="' + attrEsc(tip) + '" aria-label="Cambiar ilocalizable" data-id-ticket="' + idT + '">' +
                            '<option value="auto"' + (val === 'auto' ? ' selected' : '') + '>Auto</option>' +
                            '<option value="1"' + (val === '1' ? ' selected' : '') + '>Sí</option>' +
                            '<option value="0"' + (val === '0' ? ' selected' : '') + '>No</option></select>' +
                            '</div>';
                    }
                    function resumenKpisHtml() {
                        function n(k) {
                            var v = parseInt(res[k], 10);
                            return isNaN(v) ? 0 : v;
                        }
                        var items = [
                            { k: 'total_tickets', label: 'Tickets en la semana', cls: 'bg-secondary', idVal: 'rsKpiValTotal' },
                            { k: 'ilocalizable', label: 'Ilocalizable', cls: 'bg-danger', tip: 'Regla automática: (1) dictamen Sabueso ILOCALIZABLE o DS dictamen_ilocalizable; (2) todas las direcciones y no pagó en la semana (EC consultado). En la tabla puede corregirse manualmente; se guarda por semana.', idVal: 'rsKpiValIlocalizable' },
                            { k: 'localizable', label: 'Localizable', cls: 'bg-success', tip: 'No califica como ilocalizable y estado de cuenta consultado.', idVal: 'rsKpiValLocalizable' },
                            { k: 'pago_12h', label: 'Pago en 12h', cls: 'bg-primary', tip: 'Cumplieron pago en ventana inicial (dictamen sistema).', idVal: 'rsKpiValPago12h' },
                            { k: 'todas_direcciones', label: 'Todas las direcciones', cls: 'bg-info', tip: 'Visitaron todas las direcciones del dictamen.', idVal: 'rsKpiValTodasDir' },
                            { k: 'prorroga', label: 'Extensión', cls: 'bg-warning', tip: 'Tickets con segunda ventana: Prórroga o Intensidad (+12 h).', idVal: 'rsKpiValProrroga' },
                            { k: 'pago_semana', label: 'Pago en la semana', cls: 'bg-success', tip: 'Hubo pago en estado de cuenta dentro de la semana analizada.', idVal: 'rsKpiValPagoSemana' }
                        ];
                        var parts = '';
                        items.forEach(function(it) {
                            var val = n(it.k);
                            var tip = it.tip ? ' title="' + attrEsc(it.tip) + '"' : '';
                            parts += '<span class="badge ' + it.cls + ' bg-opacity-25 text-dark border me-1 mb-1"' + tip + ' style="font-size:0.75rem;font-weight:600;">' +
                                attrEsc(it.label) + ': <span class="text-body" id="' + attrEsc(it.idVal) + '">' + val + '</span></span>';
                        });
                        return '<div class="estad-reporte-semanal-resumen d-flex flex-wrap align-items-center gap-1 py-2 px-1 mb-2 rounded-2 border" style="background:rgba(0,0,0,0.03);font-size:0.8rem;">' +
                            '<span class="text-muted small me-2 w-100 w-md-auto"><i class="fa-solid fa-chart-simple me-1"></i>Resumen semana</span>' + parts + '</div>';
                    }
                    function weekOptionsHtml() {
                        var out = '';
                        semanas.forEach(function(s) {
                            var sel = s.selected ? ' selected' : '';
                            out += '<option value="' + attrEsc(s.inicio || '') + '"' + sel + '>' + attrEsc(s.label || (s.inicio || 'Semana')) + '</option>';
                        });
                        return out;
                    }
                    function reporteRsNormalizeBusqueda(s) {
                        try {
                            var t = String(s || '').toLowerCase();
                            if (typeof t.normalize === 'function') {
                                t = t.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                            }
                            return t.replace(/\s+/g, ' ').trim();
                        } catch (eRepNorm) {
                            return String(s || '').toLowerCase().replace(/\s+/g, ' ').trim();
                        }
                    }
                    function reporteSemanalFiltrosHtml() {
                        if (!filas.length) {
                            return '';
                        }
                        var glist = [];
                        var seenG = {};
                        filas.forEach(function(f) {
                            var gid = f.id_gestor != null ? parseInt(f.id_gestor, 10) : 0;
                            if (gid > 0 && !seenG[gid]) {
                                seenG[gid] = true;
                                glist.push({ id: gid, name: (f.nombre_gestor || '').trim() });
                            }
                        });
                        glist.sort(function(a, b) {
                            return (a.name || '').localeCompare(b.name || '', 'es', { sensitivity: 'base' });
                        });
                        var gestOpts = '<option value="">Todos los gestores</option>';
                        glist.forEach(function(x) {
                            gestOpts += '<option value="' + x.id + '">' + attrEsc(x.name || ('ID ' + x.id)) + '</option>';
                        });
                        return '<div class="estad-rs-filtros-wrap border rounded-2 px-2 py-2 mb-2" style="background:rgba(var(--bs-primary-rgb),0.05);" id="reporteSemanalFiltrosWrap">' +
                            '<div class="d-flex flex-wrap align-items-center gap-2 mb-2">' +
                            '<span class="text-muted small fw-semibold"><i class="fa-solid fa-filter me-1"></i>Filtros</span>' +
                            '<span class="text-muted small d-none d-lg-inline">Los KPI siguen siendo de la semana completa; aqu\u00ed se acota la tabla.</span>' +
                            '</div>' +
                            '<div class="row g-2 align-items-end">' +
                            '<div class="col-6 col-md-4 col-lg-2">' +
                            '<label class="form-label small text-muted mb-0" for="filtroRsIlocalizable">Ilocalizable</label>' +
                            '<select id="filtroRsIlocalizable" class="form-select form-select-sm">' +
                            '<option value="">Todos</option><option value="1">S\u00ed</option><option value="0">No</option></select></div>' +
                            '<div class="col-6 col-md-4 col-lg-2">' +
                            '<label class="form-label small text-muted mb-0" for="filtroRsPagoSemana">Pago en semana</label>' +
                            '<select id="filtroRsPagoSemana" class="form-select form-select-sm">' +
                            '<option value="">Todos</option><option value="si">S\u00ed</option>' +
                            '<option value="no">No (EC verificado)</option><option value="no_verif">No verificado (EC)</option></select></div>' +
                            '<div class="col-6 col-md-4 col-lg-2">' +
                            '<label class="form-label small text-muted mb-0" for="filtroRsTipoContacto">Contacto</label>' +
                            '<select id="filtroRsTipoContacto" class="form-select form-select-sm">' +
                            '<option value="">Todos</option><option value="campo">Campo</option><option value="telefonica">Telef\u00f3nica</option><option value="otro">Sin dato</option></select></div>' +
                            '<div class="col-6 col-md-4 col-lg-2">' +
                            '<label class="form-label small text-muted mb-0" for="filtroRsGestor">Gestor</label>' +
                            '<select id="filtroRsGestor" class="form-select form-select-sm">' + gestOpts + '</select></div>' +
                            '<div class="col-12 col-md-8 col-lg-3">' +
                            '<label class="form-label small text-muted mb-0" for="filtroRsBuscar">Buscar</label>' +
                            '<input type="search" id="filtroRsBuscar" class="form-control form-control-sm" placeholder="Folio, ticket, cr\u00e9dito, cliente, gestor\u2026" autocomplete="off"></div>' +
                            '<div class="col-12 col-lg-1 d-flex align-items-end">' +
                            '<button type="button" class="btn btn-outline-secondary btn-sm w-100" id="btnRsLimpiarFiltros" title="Quitar filtros">Limpiar</button></div>' +
                            '</div>' +
                            '<div class="small text-muted mt-2 mb-0" id="filtroRsContador"></div>' +
                            '</div>';
                    }
                    function tableRowsHtml() {
                        if (!filas.length) {
                            return '<tr><td colspan="11" class="text-center text-muted py-3">Sin tickets en la semana seleccionada.</td></tr>';
                        }
                        var out = '';
                        filas.forEach(function(f) {
                            var idT = parseInt(f.id_ticket, 10) || 0;
                            var consultadoPs = f.pago_semana_consultado === true || f.pago_semana_consultado === 1 || f.pago_semana_consultado === '1';
                            var siPs = f.pago_semana_si === true || f.pago_semana_si === 1 || f.pago_semana_si === '1';
                            var il = f.ilocalizable === true || f.ilocalizable === 1 || f.ilocalizable === '1';
                            var tipoAttr = 'otro';
                            if (f.tipo_contacto === 'Campo') {
                                tipoAttr = 'campo';
                            } else if (f.tipo_contacto === 'Telefónica') {
                                tipoAttr = 'telefonica';
                            }
                            var idGstr = (f.id_gestor != null && parseInt(f.id_gestor, 10) > 0) ? String(parseInt(f.id_gestor, 10)) : '';
                            var searchBlob = reporteRsNormalizeBusqueda([
                                f.folio || '',
                                String(idT),
                                f.id_credito != null ? String(f.id_credito) : '',
                                idGstr,
                                f.nombre_cliente || '',
                                f.nombre_gestor || ''
                            ].join(' '));
                            var cliente = '<div>' + attrEsc(f.nombre_cliente || '—') + '</div><div class="text-muted small">ID ' + (f.id_credito != null ? f.id_credito : '—') + '</div>';
                            var gestor = '<div>' + attrEsc(f.nombre_gestor || '—') + '</div><div class="text-muted small">ID ' + (f.id_gestor != null ? f.id_gestor : '—') + '</div>';
                            var tipoContactoTxt = (f.tipo_contacto === 'Campo') ? '<span class="text-primary fw-semibold">Campo</span>' : ((f.tipo_contacto === 'Telefónica') ? '<span class="text-info fw-semibold">Telefónica</span>' : '<span class="text-muted">—</span>');
                            out += '<tr data-id-ticket="' + idT + '" data-id-gestor="' + attrEsc(idGstr) + '" data-tipo-contacto="' + tipoAttr + '" data-search="' + attrEsc(searchBlob) + '" data-pago-consultado="' + (consultadoPs ? '1' : '0') + '" data-pago-si="' + (siPs ? '1' : '0') + '" data-ilocalizable="' + (il ? '1' : '0') + '">' +
                                '<td class="fw-medium">' + attrEsc(f.folio || '—') + '</td>' +
                                '<td>' + cliente + '</td>' +
                                '<td>' + gestor + '</td>' +
                                '<td class="text-center">' + tipoContactoTxt + '</td>' +
                                '<td class="text-center">' + boolTxt(f.fue_todas_direcciones) + '</td>' +
                                '<td><small>' + attrEsc(f.direcciones_fue || '—') + '</small></td>' +
                                '<td class="text-center">' + boolTxt(f.pago_12h) + '</td>' +
                                '<td class="text-center">' + extension12hReporteCell(f) + '</td>' +
                                '<td class="text-center">' + boolTxt(f.pago_prorroga_12h) + '</td>' +
                                '<td class="text-center td-reporte-pago-semana">' + pagoSemanaCellHtml(f) + '</td>' +
                                '<td class="text-center td-reporte-ilocalizable">' + ilocalizableSelectHtml(f) + '</td>' +
                                '</tr>';
                        });
                        return out;
                    }
                    var html = '<div class="estad-modal-detalle-wrap" id="reporteSemanalGlobalWrap">' +
                        '<div class="estad-modal-detalle-leyenda text-start"><strong>Reporte semanal (semana vencida).</strong> ' +
                        'La primera opción es la semana cerrada más reciente. Puede cambiar a semanas anteriores para consultar histórico.</div>' +
                        '<div class="estad-reporte-semanal-toolbar d-flex align-items-center justify-content-between flex-wrap gap-2">' +
                        '<div class="text-muted small">Semana analizada: <strong>' + attrEsc((r.semana_inicio || '') + ' → ' + (r.semana_fin || '')) + '</strong></div>' +
                        '<div class="d-flex align-items-center flex-wrap gap-2">' +
                        '<div class="btn-group btn-group-sm" role="group" aria-label="Vista reporte">' +
                        '<button type="button" class="btn btn-outline-secondary active" id="btnReporteSemanalVistaTabla"><i class="fa-solid fa-table me-1"></i>Tabla</button>' +
                        '<button type="button" class="btn btn-outline-secondary" id="btnReporteSemanalVistaGraficas"><i class="fa-solid fa-chart-pie me-1"></i>Gráficas</button>' +
                        '</div>' +
                        '<div class="d-flex align-items-center gap-2"><label class="text-muted small mb-0">Semana</label>' +
                        '<select id="selSemanaReporteGlobal" class="form-select form-select-sm">' + weekOptionsHtml() + '</select></div>' +
                        '</div></div>' +
                        '<div id="reporteSemanalBlockTabla">' +
                        resumenKpisHtml() +
                        reporteSemanalFiltrosHtml() +
                        '<div class="estad-modal-detalle-table-wrap table-responsive">' +
                        '<table class="table table-sm table-bordered text-start mb-0 table-reporte-semanal-global">' +
                        '<thead><tr>' +
                        '<th>Folio</th>' +
                        '<th>Cliente / ID cr.</th>' +
                        '<th>Gestor / ID</th>' +
                        '<th>Campo / Telefónica</th>' +
                        '<th>Fue a todas direcciones</th>' +
                        '<th>Direcciones visitadas</th>' +
                        '<th>Pago 12h</th>' +
                        '<th>Extensi\u00f3n</th>' +
                        '<th>Pago 2.\u00aa ventana (+12 h)</th>' +
                        '<th>Pago semana</th>' +
                        '<th title="Automático = regla del sistema; Sí/No = ajuste guardado por semana">Ilocalizable</th>' +
                        '</tr></thead><tbody id="tbodyReporteSemanalGlobal">' + tableRowsHtml() + '</tbody></table></div>' +
                        '<div class="estad-reporte-semanal-footer text-muted" style="font-size:0.72rem;">' +
                        'Ilocalizable: por defecto el sistema aplica la regla (dictamen ILOCALIZABLE / <code>dictamen_ilocalizable</code> / todas las direcciones + sin pago en semana con EC consultado). La columna es <strong>editable</strong> (Automático / Sí / No); el valor se guarda en el reporte de esa semana. La <strong>extensi\u00f3n</strong> (Pr\u00f3rroga o Intensidad) no condiciona la regla de direcciones+pago.' +
                        '</div></div>' +
                        '<div id="reporteSemanalBlockGraficas" class="d-none">' +
                        '<p class="text-muted small mb-2"><i class="fa-solid fa-chart-column me-1"></i>Mismos datos del resumen y del detalle (semana completa; los filtros de la tabla no aplican aqu\u00ed), en distintos tipos de gráfico.</p>' +
                        '<div id="reporteSemanalGraficasWrap"></div>' +
                        '</div></div>';
                    bodyEl2.className = 'p-0';
                    bodyEl2.innerHTML = html;
                    initTooltipsReporteSemanalModal();
                    (function bindReporteSemanalFiltrosUi() {
                        function aplicarFiltrosReporteSemanal() {
                            var tbody = document.getElementById('tbodyReporteSemanalGlobal');
                            var cnt = document.getElementById('filtroRsContador');
                            if (!tbody || !filas.length) {
                                if (cnt) {
                                    cnt.textContent = '';
                                }
                                return;
                            }
                            var filIl = document.getElementById('filtroRsIlocalizable');
                            var filPs = document.getElementById('filtroRsPagoSemana');
                            var filTc = document.getElementById('filtroRsTipoContacto');
                            var filG = document.getElementById('filtroRsGestor');
                            var filBus = document.getElementById('filtroRsBuscar');
                            var vIl = filIl ? filIl.value : '';
                            var vPs = filPs ? filPs.value : '';
                            var vTc = filTc ? filTc.value : '';
                            var vG = filG ? filG.value : '';
                            var q = reporteRsNormalizeBusqueda(filBus ? (filBus.value || '').trim() : '');
                            var rows = tbody.querySelectorAll('tr[data-id-ticket]');
                            var totalD = filas.length;
                            var vis = 0;
                            rows.forEach(function(tr) {
                                var ok = true;
                                if (vIl === '1' && tr.getAttribute('data-ilocalizable') !== '1') {
                                    ok = false;
                                }
                                if (vIl === '0' && tr.getAttribute('data-ilocalizable') === '1') {
                                    ok = false;
                                }
                                var cPs = tr.getAttribute('data-pago-consultado') === '1';
                                var siPs = tr.getAttribute('data-pago-si') === '1';
                                if (vPs === 'si' && !siPs) {
                                    ok = false;
                                }
                                if (vPs === 'no' && (!cPs || siPs)) {
                                    ok = false;
                                }
                                if (vPs === 'no_verif' && cPs) {
                                    ok = false;
                                }
                                if (vTc && (tr.getAttribute('data-tipo-contacto') || 'otro') !== vTc) {
                                    ok = false;
                                }
                                if (vG && String(tr.getAttribute('data-id-gestor') || '') !== vG) {
                                    ok = false;
                                }
                                if (q) {
                                    var hay = tr.getAttribute('data-search') || '';
                                    if (hay.indexOf(q) === -1) {
                                        ok = false;
                                    }
                                }
                                tr.style.display = ok ? '' : 'none';
                                if (ok) {
                                    vis++;
                                }
                            });
                            if (cnt) {
                                cnt.textContent = 'Mostrando ' + vis + ' de ' + totalD + ' filas.';
                            }
                        }
                        window._reporteSemanalAplicarFiltros = aplicarFiltrosReporteSemanal;
                        var filIl = document.getElementById('filtroRsIlocalizable');
                        var filPs = document.getElementById('filtroRsPagoSemana');
                        var filTc = document.getElementById('filtroRsTipoContacto');
                        var filG = document.getElementById('filtroRsGestor');
                        var filBus = document.getElementById('filtroRsBuscar');
                        var btnLim = document.getElementById('btnRsLimpiarFiltros');
                        if (!filIl && !filBus) {
                            return;
                        }
                        if (filIl) {
                            filIl.onchange = aplicarFiltrosReporteSemanal;
                        }
                        if (filPs) {
                            filPs.onchange = aplicarFiltrosReporteSemanal;
                        }
                        if (filTc) {
                            filTc.onchange = aplicarFiltrosReporteSemanal;
                        }
                        if (filG) {
                            filG.onchange = aplicarFiltrosReporteSemanal;
                        }
                        var tBus = null;
                        if (filBus) {
                            filBus.oninput = function() {
                                if (tBus) {
                                    clearTimeout(tBus);
                                }
                                tBus = setTimeout(aplicarFiltrosReporteSemanal, 200);
                            };
                        }
                        if (btnLim) {
                            btnLim.onclick = function() {
                                if (filIl) {
                                    filIl.value = '';
                                }
                                if (filPs) {
                                    filPs.value = '';
                                }
                                if (filTc) {
                                    filTc.value = '';
                                }
                                if (filG) {
                                    filG.value = '';
                                }
                                if (filBus) {
                                    filBus.value = '';
                                }
                                aplicarFiltrosReporteSemanal();
                            };
                        }
                        aplicarFiltrosReporteSemanal();
                    })();
                    function reporteRsRowKpiContrib(tr) {
                        var c = tr.getAttribute('data-pago-consultado') === '1';
                        var i = tr.getAttribute('data-ilocalizable') === '1';
                        var p = tr.getAttribute('data-pago-si') === '1';
                        return {
                            localizable: (c && !i) ? 1 : 0,
                            ilocalizable: i ? 1 : 0,
                            pago_semana: p ? 1 : 0
                        };
                    }
                    function reporteRsBumpKpi(mapKey, delta) {
                        if (!delta) return;
                        var idMap = { localizable: 'rsKpiValLocalizable', ilocalizable: 'rsKpiValIlocalizable', pago_semana: 'rsKpiValPagoSemana' };
                        var id = idMap[mapKey];
                        if (!id) return;
                        var el = document.getElementById(id);
                        if (!el) return;
                        var cur = parseInt(el.textContent, 10) || 0;
                        el.textContent = String(Math.max(0, cur + delta));
                    }
                    var tbodyRs = document.getElementById('tbodyReporteSemanalGlobal');
                    if (tbodyRs && typeof http !== 'undefined' && http.request) {
                        tbodyRs.addEventListener('click', function(ev) {
                            var btn = ev.target.closest('.reporte-rs-reconsultar-ec');
                            if (!btn || btn.disabled) return;
                            var idTicket = parseInt(btn.getAttribute('data-id-ticket'), 10) || 0;
                            if (!idTicket) return;
                            var tr = btn.closest('tr');
                            if (!tr) return;
                            var oldContrib = reporteRsRowKpiContrib(tr);
                            btn.disabled = true;
                            var prevHtml = btn.innerHTML;
                            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
                            http.request({
                                endpoint: '/sabueso/reconsultarPagoSemanaReporteSemanal',
                                metodo: 'POST',
                                data: JSON.stringify({ id_ticket: idTicket, semana_inicio: r.semana_inicio || '' }),
                                contentType: 'application/json',
                                processData: false,
                                showLoader: false,
                                timeout: 90000,
                                onSuccess: function(rr) {
                                    btn.innerHTML = prevHtml;
                                    btn.disabled = false;
                                    if (!rr || !rr.success) {
                                        if (typeof Swal !== 'undefined') Swal.fire('Reconsulta', (rr && rr.mensaje) ? rr.mensaje : 'No se pudo consultar.', 'warning');
                                        return;
                                    }
                                    tr.setAttribute('data-pago-consultado', rr.pago_semana_consultado ? '1' : '0');
                                    tr.setAttribute('data-pago-si', rr.pago_semana_si ? '1' : '0');
                                    tr.setAttribute('data-ilocalizable', rr.ilocalizable ? '1' : '0');
                                    var fSynth = {
                                        id_ticket: rr.id_ticket,
                                        id_credito: rr.id_credito,
                                        pago_semana_si: rr.pago_semana_si,
                                        pago_semana_count: rr.pago_semana_count,
                                        pago_semana_consultado: rr.pago_semana_consultado,
                                        ilocalizable: rr.ilocalizable
                                    };
                                    var tdP = tr.querySelector('.td-reporte-pago-semana');
                                    var tdI = tr.querySelector('.td-reporte-ilocalizable');
                                    if (tdP) tdP.innerHTML = pagoSemanaCellHtml(fSynth);
                                    if (tdI) tdI.innerHTML = boolTxt(rr.ilocalizable);
                                    var newContrib = {
                                        localizable: (rr.pago_semana_consultado && !rr.ilocalizable) ? 1 : 0,
                                        ilocalizable: rr.ilocalizable ? 1 : 0,
                                        pago_semana: rr.pago_semana_si ? 1 : 0
                                    };
                                    reporteRsBumpKpi('localizable', newContrib.localizable - oldContrib.localizable);
                                    reporteRsBumpKpi('ilocalizable', newContrib.ilocalizable - oldContrib.ilocalizable);
                                    reporteRsBumpKpi('pago_semana', newContrib.pago_semana - oldContrib.pago_semana);
                                    if (typeof window._reporteSemanalAplicarFiltros === 'function') {
                                        window._reporteSemanalAplicarFiltros();
                                    }
                                },
                                onError: function() {
                                    btn.innerHTML = prevHtml;
                                    btn.disabled = false;
                                    if (typeof Swal !== 'undefined') Swal.fire('Error', 'No se pudo contactar el servicio de estado de cuenta.', 'error');
                                }
                            });
                        });
                        tbodyRs.addEventListener('change', function(ev) {
                            var sel = ev.target.closest('.reporte-rs-ilocalizable-select');
                            if (!sel || sel.disabled) return;
                            var idTicket = parseInt(sel.getAttribute('data-id-ticket'), 10) || 0;
                            if (!idTicket || !(r.semana_inicio || '')) return;
                            var tr = sel.closest('tr');
                            if (!tr) return;
                            var modo = sel.value === 'auto' ? 'auto' : (sel.value === '1' ? 'si' : 'no');
                            var prevValSel = sel.value;
                            var oldC = reporteRsRowKpiContrib(tr);
                            sel.disabled = true;
                            http.request({
                                endpoint: '/sabueso/guardarIlocalizableReporteSemanal',
                                metodo: 'POST',
                                data: JSON.stringify({ semana_inicio: r.semana_inicio, id_ticket: idTicket, modo: modo }),
                                contentType: 'application/json',
                                processData: false,
                                showLoader: false,
                                timeout: 60000,
                                onSuccess: function(rr) {
                                    sel.disabled = false;
                                    if (!rr || !rr.success) {
                                        if (typeof Swal !== 'undefined') Swal.fire('Guardar', (rr && rr.mensaje) ? rr.mensaje : 'No se pudo guardar.', 'warning');
                                        sel.value = prevValSel;
                                        return;
                                    }
                                    tr.setAttribute('data-ilocalizable', rr.ilocalizable ? '1' : '0');
                                    var fUp = {
                                        id_ticket: rr.id_ticket,
                                        ilocalizable: rr.ilocalizable,
                                        ilocalizable_auto: rr.ilocalizable_auto,
                                        ilocalizable_override: rr.ilocalizable_override
                                    };
                                    var tdI2 = tr.querySelector('.td-reporte-ilocalizable');
                                    if (tdI2) tdI2.innerHTML = ilocalizableSelectHtml(fUp);
                                    var newC = reporteRsRowKpiContrib(tr);
                                    reporteRsBumpKpi('ilocalizable', newC.ilocalizable - oldC.ilocalizable);
                                    reporteRsBumpKpi('localizable', newC.localizable - oldC.localizable);
                                    if (typeof window._reporteSemanalAplicarFiltros === 'function') {
                                        window._reporteSemanalAplicarFiltros();
                                    }
                                },
                                onError: function() {
                                    sel.disabled = false;
                                    sel.value = prevValSel;
                                    if (typeof Swal !== 'undefined') Swal.fire('Error', 'No se pudo guardar el ajuste.', 'error');
                                }
                            });
                        });
                    }
                    var sel = document.getElementById('selSemanaReporteGlobal');
                    if (sel) {
                        sel.onchange = function() {
                            abrirReporteSemanalGlobal(sel.value || '');
                        };
                    }
                    function ensureChartJs(cb) {
                                    if (typeof Chart !== 'undefined') { cb(); return; }
                                    var ex = document.getElementById('scriptChartJsUmd');
                                    if (ex) { ex.addEventListener('load', cb); return; }
                                    var s = document.createElement('script');
                                    s.id = 'scriptChartJsUmd';
                                    s.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js';
                                    s.onload = cb;
                                    document.head.appendChild(s);
                                }
                                function pintarGraficasReporteSemanal() {
                                    destroyReporteSemanalModalCharts();
                                    var wrap = document.getElementById('reporteSemanalGraficasWrap');
                                    if (!wrap || typeof Chart === 'undefined') return;
                                    var total = parseInt(res.total_tickets, 10) || 0;
                                    var labelsKpi = ['Ilocalizable', 'Localizable', 'Pago 12h', 'Todas direcc.', 'Extensión', 'Pago semana'];
                                    var dataKpi = [
                                        parseInt(res.ilocalizable, 10) || 0,
                                        parseInt(res.localizable, 10) || 0,
                                        parseInt(res.pago_12h, 10) || 0,
                                        parseInt(res.todas_direcciones, 10) || 0,
                                        parseInt(res.prorroga, 10) || 0,
                                        parseInt(res.pago_semana, 10) || 0
                                    ];
                                    var colorsKpi = ['#dc3545', '#198754', '#0d6efd', '#0dcaf0', '#ffc107', '#20c997'];
                                    var maxKpi = Math.max.apply(null, dataKpi.concat([0]));
                                    var yTopKpi = Math.max(5, Math.ceil(maxKpi * 1.12));
                                    var scaleYCounts = {
                                        beginAtZero: true,
                                        min: 0,
                                        suggestedMax: yTopKpi,
                                        ticks: { stepSize: 1, precision: 0, maxTicksLimit: 14 }
                                    };
                                    var tc = { Campo: 0, Telefónica: 0, Otros: 0 };
                                    filas.forEach(function(f) {
                                        if (f.tipo_contacto === 'Campo') tc.Campo++;
                                        else if (f.tipo_contacto === 'Telefónica') tc.Telefónica++;
                                        else tc.Otros++;
                                    });
                                    var byG = {};
                                    filas.forEach(function(f) {
                                        var kk = (f.nombre_gestor || '—').trim() || '—';
                                        byG[kk] = (byG[kk] || 0) + 1;
                                    });
                                    var arrG = Object.keys(byG).map(function(k) { return { k: k, v: byG[k] }; }).sort(function(a, b) { return b.v - a.v; }).slice(0, 15);
                                    var maxGestor = arrG.length ? Math.max.apply(null, arrG.map(function(x) { return x.v; })) : 0;
                                    var xTopGest = Math.max(5, Math.ceil(maxGestor * 1.12));
                                    function rsCard(titleUi, canvasId, caption, tall) {
                                        return '<div class="col-lg-6 col-12">' +
                                            '<div class="estad-rs-chart-card' + (tall ? ' estad-rs-chart-tall' : '') + '" role="button" tabindex="0" title="Clic para ampliar">' +
                                            '<div class="estad-rs-chart-card-head"><span>' + titleUi + '</span><i class="fa-solid fa-expand text-muted small" aria-hidden="true"></i></div>' +
                                            '<div class="estad-rs-chart-canvas-wrap"><canvas id="' + canvasId + '"></canvas></div>' +
                                            '<div class="estad-rs-chart-caption">' + caption + '</div></div></div>';
                                    }
                                    var gestoresHtml = (arrG.length === 0)
                                        ? '<div class="col-12"><div class="alert alert-light border mb-0 small">Sin datos de gestor para graficar.</div></div>'
                                        : '<div class="col-12">' +
                                            '<div class="estad-rs-chart-card estad-rs-chart-tall" role="button" tabindex="0" title="Clic para ampliar">' +
                                            '<div class="estad-rs-chart-card-head"><span>Por gestor (top 15)</span><i class="fa-solid fa-expand text-muted small" aria-hidden="true"></i></div>' +
                                            '<div class="estad-rs-chart-canvas-wrap"><canvas id="chartRSGestores"></canvas></div>' +
                                            '<div class="estad-rs-chart-caption">Tickets por gestor que levantó el ticket</div></div></div>';
                                    wrap.innerHTML = '<div class="row g-3 px-2 pb-2">' +
                                        '<div class="col-12"><div class="alert alert-light border py-2 mb-0 small">Total tickets en la semana: <strong>' + total + '</strong></div></div>' +
                                        rsCard('Indicadores clave', 'chartRSBar', 'Barras — cantidades por categoría', false) +
                                        rsCard('Proporción', 'chartRSDoughnut', 'Donut — mismos indicadores', false) +
                                        rsCard('Perfil', 'chartRSRadar', 'Radar — forma del resumen', false) +
                                        rsCard('Polar', 'chartRSPolar', 'Área polar', false) +
                                        rsCard('Tipo de contacto', 'chartRSTipoPie', 'Campo / Telefónica / Sin dato', false) +
                                        rsCard('Tendencia', 'chartRSLine', 'Línea — mismas categorías', false) +
                                        gestoresHtml +
                                        '</div>';
                                    window._reporteSemanalChartInstances = [];
                                    var legBottom = { position: 'bottom', labels: { boxWidth: 10, font: { size: 9 }, padding: 4 } };
                                    var c1 = document.getElementById('chartRSBar');
                                    if (c1) {
                                        window._reporteSemanalChartInstances.push(new Chart(c1, {
                                            type: 'bar',
                                            data: { labels: labelsKpi, datasets: [{ label: 'Cantidad', data: dataKpi, backgroundColor: colorsKpi, borderWidth: 1, borderColor: 'rgba(0,0,0,0.06)' }] },
                                            options: {
                                                responsive: true,
                                                maintainAspectRatio: false,
                                                layout: { padding: { top: 4, right: 8, bottom: 4, left: 4 } },
                                                plugins: { legend: { display: false } },
                                                scales: {
                                                    x: { ticks: { maxRotation: 40, minRotation: 0, autoSkip: false, font: { size: 9 } }, grid: { display: false } },
                                                    y: scaleYCounts
                                                }
                                            }
                                        }));
                                    }
                                    var c2 = document.getElementById('chartRSDoughnut');
                                    if (c2) {
                                        window._reporteSemanalChartInstances.push(new Chart(c2, {
                                            type: 'doughnut',
                                            data: { labels: labelsKpi, datasets: [{ data: dataKpi, backgroundColor: colorsKpi, borderWidth: 1, borderColor: '#fff' }] },
                                            options: { responsive: true, maintainAspectRatio: false, cutout: '42%', plugins: { legend: legBottom } }
                                        }));
                                    }
                                    var c3 = document.getElementById('chartRSRadar');
                                    if (c3) {
                                        window._reporteSemanalChartInstances.push(new Chart(c3, {
                                            type: 'radar',
                                            data: {
                                                labels: labelsKpi,
                                                datasets: [{ label: 'Semana', data: dataKpi, fill: true, backgroundColor: 'rgba(13, 110, 253, 0.2)', borderColor: '#0d6efd', pointBackgroundColor: '#0d6efd', borderWidth: 1 }]
                                            },
                                            options: {
                                                responsive: true,
                                                maintainAspectRatio: false,
                                                layout: { padding: 10 },
                                                plugins: { legend: { display: false } },
                                                scales: { r: { beginAtZero: true, min: 0, suggestedMax: yTopKpi, ticks: { stepSize: 1, precision: 0, showLabelBackdrop: false } } }
                                            }
                                        }));
                                    }
                                    var c4 = document.getElementById('chartRSPolar');
                                    if (c4) {
                                        window._reporteSemanalChartInstances.push(new Chart(c4, {
                                            type: 'polarArea',
                                            data: { labels: labelsKpi, datasets: [{ data: dataKpi, backgroundColor: colorsKpi.map(function(c) { return c + 'cc'; }), borderWidth: 1 }] },
                                            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: legBottom }, scales: { r: { beginAtZero: true, suggestedMax: yTopKpi, ticks: { stepSize: 1 } } } }
                                        }));
                                    }
                                    var c5 = document.getElementById('chartRSTipoPie');
                                    if (c5) {
                                        window._reporteSemanalChartInstances.push(new Chart(c5, {
                                            type: 'pie',
                                            data: { labels: ['Campo', 'Telefónica', 'Sin dato'], datasets: [{ data: [tc.Campo, tc.Telefónica, tc.Otros], backgroundColor: ['#0d6efd', '#0dcaf0', '#adb5bd'], borderWidth: 1, borderColor: '#fff' }] },
                                            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: legBottom } }
                                        }));
                                    }
                                    var c6 = document.getElementById('chartRSLine');
                                    if (c6) {
                                        window._reporteSemanalChartInstances.push(new Chart(c6, {
                                            type: 'line',
                                            data: { labels: labelsKpi, datasets: [{ label: 'Cantidad', data: dataKpi, fill: false, tension: 0.3, borderWidth: 2, borderColor: '#6f42c1', backgroundColor: '#6f42c1', pointRadius: 5, pointHoverRadius: 7 }] },
                                            options: {
                                                responsive: true,
                                                maintainAspectRatio: false,
                                                plugins: { legend: { display: false } },
                                                scales: {
                                                    x: { ticks: { maxRotation: 35, font: { size: 9 } }, grid: { display: false } },
                                                    y: scaleYCounts
                                                }
                                            }
                                        }));
                                    }
                                    var c7 = document.getElementById('chartRSGestores');
                                    if (c7 && arrG.length > 0) {
                                        window._reporteSemanalChartInstances.push(new Chart(c7, {
                                            type: 'bar',
                                            data: {
                                                labels: arrG.map(function(x) { var s = x.k; return s.length > 28 ? s.slice(0, 26) + '…' : s; }),
                                                datasets: [{ label: 'Tickets', data: arrG.map(function(x) { return x.v; }), backgroundColor: 'rgba(111, 66, 193, 0.78)', borderWidth: 1, borderColor: 'rgba(0,0,0,0.06)' }]
                                            },
                                            options: {
                                                indexAxis: 'y',
                                                responsive: true,
                                                maintainAspectRatio: false,
                                                layout: { padding: { left: 4, right: 12, top: 4, bottom: 4 } },
                                                plugins: { legend: { display: false }, tooltip: { intersect: false } },
                                                scales: {
                                                    x: { beginAtZero: true, min: 0, suggestedMax: xTopGest, ticks: { stepSize: 1, precision: 0 }, title: { display: true, text: 'Tickets', font: { size: 10 } } },
                                                    y: { ticks: { font: { size: 9 }, autoSkip: false } }
                                                }
                                            }
                                        }));
                                    }
                                    bindReporteSemanalChartCardsClick(wrap);
                                    setTimeout(function() {
                                        (window._reporteSemanalChartInstances || []).forEach(function(ch) { try { ch.resize(); } catch (e) {} });
                                    }, 150);
                                }
                                var btnTabla = document.getElementById('btnReporteSemanalVistaTabla');
                                var btnGraf = document.getElementById('btnReporteSemanalVistaGraficas');
                                var blockTabla = document.getElementById('reporteSemanalBlockTabla');
                                var blockGraf = document.getElementById('reporteSemanalBlockGraficas');
                                function vistaTabla() {
                                    if (blockTabla) blockTabla.classList.remove('d-none');
                                    if (blockGraf) blockGraf.classList.add('d-none');
                                    if (btnTabla) { btnTabla.classList.add('active'); }
                                    if (btnGraf) { btnGraf.classList.remove('active'); }
                                    var list = window._reporteSemanalChartInstances || [];
                                    list.forEach(function(ch) { try { ch.destroy(); } catch (e) {} });
                                    window._reporteSemanalChartInstances = [];
                                    var wrap = document.getElementById('reporteSemanalGraficasWrap');
                                    if (wrap) wrap.innerHTML = '';
                                }
                                function vistaGraficas() {
                                    if (blockTabla) blockTabla.classList.add('d-none');
                                    if (blockGraf) blockGraf.classList.remove('d-none');
                                    if (btnTabla) { btnTabla.classList.remove('active'); }
                                    if (btnGraf) { btnGraf.classList.add('active'); }
                                    ensureChartJs(function() {
                                        pintarGraficasReporteSemanal();
                                    });
                                }
                    if (btnTabla) btnTabla.onclick = vistaTabla;
                    if (btnGraf) btnGraf.onclick = vistaGraficas;
                },
                onError: function() {
                    if (typeof Swal !== 'undefined' && Swal.close) Swal.close();
                    var bodyErr = document.getElementById('modalReporteSemanalGlobalBody');
                    var modalErr = document.getElementById('modalReporteSemanalGlobal');
                    if (bodyErr && modalErr) {
                        bodyErr.classList.remove('estad-rs-body-loading');
                        bodyErr.className = 'p-0';
                        bodyErr.innerHTML = '<div class="alert alert-danger m-3 mb-0">No se pudo cargar el reporte semanal.</div>';
                        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                            bootstrap.Modal.getOrCreateInstance(modalErr).show();
                        } else if (typeof $ !== 'undefined' && $(modalErr).modal) {
                            $(modalErr).modal('show');
                        }
                    } else if (typeof Swal !== 'undefined') {
                        Swal.fire('Error', 'No se pudo cargar el reporte semanal.', 'error');
                    }
                }
            });
        }
        function cargarEstadisticasSabueso(opts) {
            opts = opts || {};
            var soloSabueso = !!opts.soloSabueso;
            if (!soloSabueso) {
                $('#estadisticasSabuesoContenido').hide();
            } else {
                $('#tbodyPorSabueso').html('<tr><td colspan="4" class="text-muted text-center py-3"><i class="fa fa-spinner fa-spin me-2"></i>Actualizando…</td></tr>');
            }
            http.request({
                endpoint: '/sabueso/getEstadisticasTickets',
                metodo: 'POST',
                data: JSON.stringify({ periodo_sabueso: estadisticasFiltroSabueso || 'por_dia' }),
                contentType: 'application/json',
                processData: false,
                showLoader: !soloSabueso,
                onSuccess: function(r) {
                    if (!soloSabueso) {
                        $('#estadisticasSabuesoContenido').show();
                    }
                    if (!r.success) {
                        $('#estadisticasSabuesoAlert').removeClass('d-none').text(r.mensaje || 'Error al cargar.');
                        return;
                    }
                    $('#estadisticasSabuesoAlert').addClass('d-none');
                    estadisticasDatos = r.datos || {};
                    var t = estadisticasDatos.totales || {};
                    var activos = parseInt(t.tickets_activos, 10) || 0;
                    var enviados = parseInt(t.con_dictamen_enviado, 10) || 0;
                    var vistos = parseInt(t.con_dictamen_visto, 10) || 0;
                    var cerrados = parseInt(t.tickets_cerrados, 10) || 0;
                    // Base = tickets activos (100%). El resto de barras del flujo se calculan sobre ese universo.
                    var pctEnv = activos > 0 ? Math.round((enviados / activos) * 100) : 0;
                    var pctVis = activos > 0 ? Math.round((vistos / activos) * 100) : 0;
                    $('#statTotalActivos').text(activos);
                    $('#statDictamenEnviado').text(enviados);
                    $('#statDictamenVisto').text(vistos);
                    $('#statTicketsCerrados').text(cerrados);
                    if (activos > 0) {
                        $('#barActivos').css('width', '100%');
                        $('#pctActivos').text('100%');
                    } else {
                        $('#barActivos').css('width', '0%');
                        $('#pctActivos').text('0%');
                    }
                    $('#barEnviado').css('width', pctEnv + '%');
                    $('#pctEnviado').text(pctEnv + '%');
                    $('#barVisto').css('width', pctVis + '%');
                    $('#pctVisto').text(pctVis + '%');
                    // Cerrados: la barra y el % son siempre 100% (referencia visual); el valor real es el número mostrado arriba.
                    $('#barCerrados').css('width', '100%');
                    $('#pctCerrados').text('100%');
                    var ts = estadisticasDatos.tiempos_sabueso_segundos;
                    var tg = estadisticasDatos.tiempos_gestor_segundos;
                    if (ts && ts.promedio_humano) {
                        $('#statTiempoSabuesoValor').text(ts.promedio_humano);
                        $('#statTiempoSabuesoSub').text('Semana actual (lun→hoy): ' + (ts.muestras || 0) + ' envíos · Promedio desde última asignación antes del envío hasta enviar (máx. 7 días por muestra). Cada lunes se reinicia. Los envíos cuentan por fecha de envío del dictamen (pueden ser de tickets levantados en semanas anteriores).');
                    } else {
                        $('#statTiempoSabuesoValor').html('<span class="text-muted fs-6 fw-normal">Sin datos</span>');
                        $('#statTiempoSabuesoSub').text('Semana actual: aún no hay envíos esta semana, o sin asignación previa registrada.');
                    }
                    if (tg && tg.promedio_humano) {
                        $('#statTiempoGestorValor').text(tg.promedio_humano);
                        $('#statTiempoGestorSub').text('Semana actual (lun→hoy): ' + (tg.muestras || 0) + ' aperturas · Solo envíos de esta semana; desde envío hasta que el gestor abre (máx. 7 días). Cada lunes se reinicia. Las aperturas cuentan por fecha de envío y de vista (pueden ser de tickets levantados en semanas anteriores).');
                    } else {
                        $('#statTiempoGestorValor').html('<span class="text-muted fs-6 fw-normal">Sin datos</span>');
                        $('#statTiempoGestorSub').text('Semana actual: aún no hay aperturas registradas esta semana.');
                    }
                    $('#tileTiempoLectura').text(tg && tg.promedio_humano ? tg.promedio_humano : '—');
                    $('#tileTiempoLecturaSub').text('Muestras: ' + (tg && tg.muestras != null ? tg.muestras : 0) + ' (solo tickets ya vistos)');
                    $('#tileTiempoEnvio').text(ts && ts.promedio_humano ? ts.promedio_humano : '—');
                    $('#tileTiempoEnvioSub').text('Muestras: ' + (ts && ts.muestras != null ? ts.muestras : 0) + ' (con asignación previa)');
                    var sinLeer = Math.max(0, enviados - vistos);
                    $('#tileSinLeer').text(sinLeer);
                    if (enviados > 0) {
                        var tasa = Math.round((vistos / enviados) * 100);
                        $('#tileTasa').text(tasa + '%');
                        $('#tileTasaSub').text(vistos + ' de ' + enviados + ' vistos');
                    } else {
                        $('#tileTasa').text('—');
                        $('#tileTasaSub').text('Sin dictámenes enviados');
                    }
                    // Panorama cumplimiento global (dictamen_sistema sobre detalle reciente)
                    var cg = estadisticasDatos.cumplimiento_global || {};
                    if (cg.pct_promedio != null) {
                        $('#tileCumplimientoPct').text(cg.pct_promedio);
                    } else {
                        $('#tileCumplimientoPct').html('<span class="text-muted">—</span>');
                    }
                    $('#tileCumplimientoMuestra').text('Muestra: ' + (cg.muestra || 0) + ' · Con evaluación: ' + (cg.con_evaluacion || 0) + ' · Pendiente/sin DS: ' + (cg.sin_evaluacion || 0));
                    var pr = cg.por_resultado || {};
                    var prParts = [];
                    Object.keys(pr).forEach(function(k) { prParts.push(k + '=' + pr[k]); });
                    $('#tileCumplimientoPorResultado').text(prParts.length ? prParts.join(' · ') : 'Sin resultados aún');
                    $('#tileCumplimientoLeyenda').text(cg.leyenda || '');
                    var k = estadisticasDatos.kpis_extra || {};
                    if (k.tiempo_creacion_a_primera_asignacion) {
                        $('#kpiPrimeraAsignacion').text(k.tiempo_creacion_a_primera_asignacion.promedio_humano || '—');
                        $('#kpiPrimeraAsignacionSub').text('Muestras: ' + (k.tiempo_creacion_a_primera_asignacion.muestras || 0));
                    }
                    if (k.reasignaciones_promedio_antes_envio != null) {
                        $('#kpiReasignaciones').text(k.reasignaciones_promedio_antes_envio);
                    }
                    $('#kpiBorradores').text(k.dictamenes_borrador_sin_enviar != null ? k.dictamenes_borrador_sin_enviar : '—');
                    if (k.pct_visto_dentro_12h != null) {
                        $('#kpi12h').text(k.pct_visto_dentro_12h + '%');
                        $('#kpi12hSub').text('de ' + (k.visto_dentro_12h_muestras || 0) + ' vistos');
                    }
                    $('#kpiCola24h').text(k.tickets_cola_lenta_24h != null ? k.tickets_cola_lenta_24h : '—');
                    if (!soloSabueso) {
                        renderPeriodList();
                        renderPorGestor();
                    }
                    renderPorSabueso();
                    initEstadisticasTooltips();
                    var det = estadisticasDatos.detalle_timings || [];
                    var tbDet = $('#tablaDetalleTimings tbody');
                    if ($.fn.DataTable && $.fn.DataTable.isDataTable('#tablaDetalleTimings')) {
                        try { $('#tablaDetalleTimings').DataTable().destroy(); } catch (e) {}
                    }
                    tbDet.empty();
                    if (!det.length) {
                        tbDet.append('<tr><td colspan="8" class="text-muted">No hay tickets con dictamen enviado reciente.</td></tr>');
                    } else {
                        function truncMed(s, n) {
                            if (!s) return '—';
                            s = String(s);
                            if (s.length <= n) return attrEsc(s);
                            return attrEsc(s.substring(0, n)) + '…';
                        }
                        det.forEach(function(row) {
                            var pct = row.pct_efectividad;
                            var pctTxt = (pct === null || pct === undefined || pct === '') ? '—' : (pct + '%');
                            var med = row.medidas_preventivas || '';
                            var medEsc = attrEsc(med);
                            var medCell = med ? '<small class="estad-tooltip-custom" data-bs-toggle="tooltip" data-bs-placement="left" title="' + medEsc + '">' + truncMed(med, 48) + '</small>' : '<small class="text-muted">—</small>';
                            // ID = quien levantó el ticket (gestor / creador), no el asignado Sabueso
                            var creadorCell = '<small>' + attrEsc(row.creador_nombre || '—') + '</small>';
                            var cid = parseInt(row.creador_id, 10) || 0;
                            if (cid) {
                                creadorCell += '<br><small class="text-muted d-block mt-1" title="ID persona que creó el ticket (gestor)"><i class="fa-solid fa-fingerprint me-1"></i>ID ' + cid + '</small>';
                            }
                            tbDet.append(
                                '<tr><td>' + attrEsc(row.folio || '—') + '</td>' +
                                '<td>' + creadorCell + '</td>' +
                                '<td><small>' + attrEsc(row.asignado_nombre || '—') + '</small></td>' +
                                '<td><small>' + fmtFecha(row.fecha_creacion) + '</small></td>' +
                                '<td><small>' + fmtFecha(row.dictamen_fecha_envio) + '</small></td>' +
                                '<td><small>' + fmtFecha(row.dictamen_fecha_visto) + '</small></td>' +
                                '<td><small>' + pctTxt + '</small></td>' +
                                '<td>' + medCell + '</td></tr>'
                            );
                        });
                        initEstadisticasTooltips();
                        if (typeof configuraTabla === 'function') {
                            configuraTabla('#tablaDetalleTimings', {
                                registrosPorPagina: 10,
                                responsive: false,
                                order: [[4, 'desc']],
                                columns: null
                            });
                            setTimeout(initEstadisticasTooltips, 200);
                        }
                    }
                },
                onError: function() {
                    $('#estadisticasSabuesoContenido').show();
                    $('#estadisticasSabuesoAlert').removeClass('d-none').text('Error de red o servidor.');
                }
            });
        }
        $(document).ready(function() {
            var estadisticasSabuesoInicializadas = false;
            function iniciarDashboardSabueso() {
                if (estadisticasSabuesoInicializadas) {
                    $('#estadisticasSelectorWrap').hide();
                    $('#estadisticasSabuesoContenido').show();
                    return;
                }
                estadisticasSabuesoInicializadas = true;
                $('#estadisticasSelectorWrap').hide();
                cargarEstadisticasSabueso();
            }
            $(document).on('click', '#btnEntrarEstadSabueso', function() {
                iniciarDashboardSabueso();
            });
            $(document).on('click', '#btnEstadisticasVolver', function() {
                $('#estadisticasSabuesoContenido').hide();
                $('#estadisticasSelectorWrap').show();
            });
            // Delegación: el tbody se reemplaza al renderizar; el clic debe vivir en document
            $(document).on('click', '#panelResumenGestor tr.estad-gestor-fila[data-id-gestor]', function() {
                var id = parseInt($(this).attr('data-id-gestor'), 10);
                if (id) abrirDetalleGestor(id, '', vistaGestorTablaActual || 'lectura');
            });
            $(document).on('click', '#grpVistaGestorTabla button[data-vista-gestor]', function() {
                aplicarVistaGestorTabla($(this).data('vista-gestor'));
            });
            $(document).on('click', '#btnReporteSemanalGlobal', function() {
                abrirReporteSemanalGlobal('');
            });
            var modalRepSemanal = document.getElementById('modalReporteSemanalGlobal');
            if (modalRepSemanal) {
                modalRepSemanal.addEventListener('hidden.bs.modal', function() {
                    destroyReporteSemanalModalCharts();
                    var bRep = document.getElementById('modalReporteSemanalGlobalBody');
                    if (bRep) bRep.innerHTML = '';
                });
            }
            var modalZoomRS = document.getElementById('modalReporteSemanalChartZoom');
            if (modalZoomRS) {
                modalZoomRS.addEventListener('hidden.bs.modal', function() {
                    if (window._reporteSemanalZoomChartInstance) {
                        try { window._reporteSemanalZoomChartInstance.destroy(); } catch (e) {}
                        window._reporteSemanalZoomChartInstance = null;
                    }
                });
            }
            $(document).on('click', '#tbodyPorSabueso tr[data-id-sabueso]', function(e) {
                var id = parseInt($(this).attr('data-id-sabueso'), 10);
                if (id) abrirDetalleSabueso(id);
            });
            // Filtro izquierda: solo lista "Tickets levantados"
            $('#grpFiltroPeriodo').on('click', 'button[data-key]', function() {
                $('#grpFiltroPeriodo button').removeClass('active');
                $(this).addClass('active');
                estadisticasFiltroPeriodo = $(this).data('key') || 'por_dia';
                // Al cambiar de pestaña, salir del drill y volver a la lista nativa
                estadisticasDrill = null;
                estadisticasDrillFilas = null;
                renderPeriodList();
            });
            var modalHistTiempos = document.getElementById('modalEstadTiemposHistorico');
            if (modalHistTiempos) {
                modalHistTiempos.addEventListener('shown.bs.modal', function() {
                    var wrapHC = document.getElementById('modalEstadTiemposHistoricoContenido');
                    if (wrapHC && wrapHC.classList.contains('d-none')) return;
                    renderEstadTiemposSemanasChartModal();
                });
                modalHistTiempos.addEventListener('hidden.bs.modal', function() {
                    destroyEstadTiemposModalChart();
                });
            }
            $(document).on('click', '#btnHistSabuesoIcon, #btnHistSabuesoLink', function() {
                abrirModalEstadTiemposHistorico('sabueso');
            });
            $(document).on('click', '#btnHistGestorIcon, #btnHistGestorLink', function() {
                abrirModalEstadTiemposHistorico('gestor');
            });
            $(document).on('click', '#estadPeriodList .estad-drill-row', function() {
                var drill = $(this).data('drill');
                if (drill === 'meses') {
                    var anio = parseInt($(this).data('anio'), 10);
                    if (!anio) return;
                    estadisticasDrill = { nivel: 'meses', anio: anio };
                    cargarDrill('meses', { anio: anio });
                } else if (drill === 'semanas') {
                    var anio = parseInt($(this).data('anio'), 10);
                    var mes = parseInt($(this).data('mes'), 10);
                    if (!anio || !mes) return;
                    estadisticasDrill = { nivel: 'semanas', anio: anio, mes: mes };
                    cargarDrill('semanas', { anio: anio, mes: mes });
                } else if (drill === 'dias') {
                    var lunes = $(this).data('lunes');
                    if (!lunes) return;
                    estadisticasDrill = { nivel: 'dias', lunes: lunes, anio: $(this).data('anio') || null, mes: $(this).data('mes') || null };
                    cargarDrill('dias', { lunes: lunes }, function(r) {
                        if (r.lunes) estadisticasDrill.lunes = r.lunes;
                    });
                } else if (drill === 'semanas_desde_mes') {
                    var anio = parseInt($(this).data('anio'), 10);
                    var mes = parseInt($(this).data('mes'), 10);
                    if (!anio || !mes) return;
                    estadisticasDrill = { nivel: 'semanas', anio: anio, mes: mes, desde: 'mes' };
                    cargarDrill('semanas', { anio: anio, mes: mes });
                }
            });
            $(document).on('click', '#estadPeriodList .estad-dia-row', function() {
                var periodo = $(this).data('periodo');
                if (!periodo || !/^\d{4}-\d{2}-\d{2}$/.test(periodo)) return;
                if (typeof swalCargandoDetalle === 'function') swalCargandoDetalle('Cargando tickets del d\u00eda…');
                http.request({
                    endpoint: '/sabueso/getTicketsDetallePorDia',
                    metodo: 'POST',
                    data: JSON.stringify({ fecha: periodo }),
                    contentType: 'application/json',
                    processData: false,
                    showLoader: false,
                    onSuccess: function(r) {
                        if (typeof Swal !== 'undefined' && Swal.isVisible && Swal.isVisible()) Swal.close();
                        if (!r.success) {
                            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'Error', text: r.mensaje || 'No se pudo cargar el detalle.' });
                            return;
                        }
                        var filas = r.filas || [];
                        var fechaLabel = periodo;
                        try { var d = new Date(periodo + 'T12:00:00'); fechaLabel = d.toLocaleDateString('es-MX', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }); } catch (e) {}
                        var html = '<div class="text-muted small mb-2">' + (filas.length ? filas.length + ' ticket(s) levantado(s) el ' + fechaLabel + '.' : 'Ning\u00fan ticket levantado ese d\u00eda.') + '</div>';
                        if (filas.length > 0) {
                            html += '<style>.estad-dia-detalle-tabla thead th{position:sticky;top:0;background:#fff;z-index:1;box-shadow:0 1px 0 0 #dee2e6}</style>';
                            html += '<div class="table-responsive estad-dia-detalle-table-wrap" style="max-height:60vh;overflow-y:auto"><table class="table table-sm table-bordered mb-0 small estad-dia-detalle-tabla"><thead><tr><th>Folio</th><th>ID cr.</th><th>Gestor</th><th>Hora</th><th>T. env\u00edo dict.</th><th>Abrieron</th><th>T. apertura</th><th>Resultado DS</th><th>Extensi\u00f3n</th><th>Pagaron</th><th>Cumplimiento</th></tr></thead><tbody>';
                            filas.forEach(function(f) {
                                html += '<tr><td>' + attrEsc(f.folio) + '</td><td>' + (f.id_credito != null ? f.id_credito : '—') + '</td><td>' + attrEsc(f.gestor_nombre) + '</td><td>' + attrEsc(f.hora_levantado) + '</td><td>' + attrEsc(f.tiempo_dictamen_enviado) + '</td><td>' + attrEsc(f.cuando_abrieron) + '</td><td>' + attrEsc(f.tiempo_apertura) + '</td><td>' + attrEsc(f.resultado_ds) + '</td><td>' + attrEsc(f.prorroga) + '</td><td>' + attrEsc(f.pagaron) + '</td><td>' + attrEsc(f.cumplimiento) + '</td></tr>';
                            });
                            html += '</tbody></table></div>';
                        }
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: 'Tickets del d\u00eda',
                                html: html,
                                width: '90%',
                                maxWidth: '960px',
                                showCloseButton: true,
                                showConfirmButton: true,
                                confirmButtonText: 'Cerrar',
                                customClass: { container: 'estad-detalle-swal-container', popup: 'estad-detalle-swal', confirmButton: 'btn btn-primary px-4' },
                                didOpen: function() { if (typeof initTooltipsEnSwal === 'function') initTooltipsEnSwal(); }
                            });
                        }
                    },
                    onError: function() {
                        if (typeof Swal !== 'undefined' && Swal.isVisible && Swal.isVisible()) Swal.close();
                        if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar el detalle del d\u00eda.' });
                    }
                });
            });
            $(document).on('click', '#estadPeriodBreadcrumb .estad-drill-back', function() {
                var back = $(this).data('drill-back');
                if (back === 'anio') {
                    estadisticasDrill = null;
                    estadisticasDrillFilas = null;
                    renderPeriodList();
                } else if (back === 'meses') {
                    var anio = parseInt($(this).data('anio'), 10);
                    if (!anio) return;
                    estadisticasDrill = { nivel: 'meses', anio: anio };
                    cargarDrill('meses', { anio: anio });
                } else if (back === 'semanas') {
                    var anio = parseInt($(this).data('anio'), 10);
                    var mes = parseInt($(this).data('mes'), 10);
                    if (!anio || !mes) return;
                    estadisticasDrill = { nivel: 'semanas', anio: anio, mes: mes };
                    cargarDrill('semanas', { anio: anio, mes: mes });
                } else if (back === 'por_mes_list') {
                    estadisticasDrill = null;
                    estadisticasDrillFilas = null;
                    renderPeriodList();
                } else if (back === 'por_semana_list') {
                    estadisticasDrill = null;
                    estadisticasDrillFilas = null;
                    renderPeriodList();
                } else if (back === 'semanas_mes') {
                    var anio = parseInt($(this).data('anio'), 10);
                    var mes = parseInt($(this).data('mes'), 10);
                    if (!anio || !mes) return;
                    estadisticasDrill = { nivel: 'semanas', anio: anio, mes: mes, desde: 'mes' };
                    cargarDrill('semanas', { anio: anio, mes: mes });
                }
            });
            // Filtro Por Sabueso: solo dictámenes por día/semana/mes/año (fecha de envío) — no toca la lista izquierda
            $(document).on('click', '#grpFiltroPeriodoSabueso button[data-key]', function() {
                $('#grpFiltroPeriodoSabueso button').removeClass('active');
                $(this).addClass('active');
                estadisticasFiltroSabueso = $(this).data('key') || 'por_dia';
                $('#tbodyPorSabueso').html('<tr><td colspan="4" class="text-muted text-center py-2"><i class="fa fa-spinner fa-spin me-2"></i>Actualizando…</td></tr>');
                http.request({
                    endpoint: '/sabueso/getEstadisticasPorSabuesoSolo',
                    metodo: 'POST',
                    data: JSON.stringify({ periodo_sabueso: estadisticasFiltroSabueso }),
                    contentType: 'application/json',
                    processData: false,
                    showLoader: false,
                    onSuccess: function(r) {
                        if (r.success && r.por_sabueso) {
                            if (!estadisticasDatos) estadisticasDatos = {};
                            estadisticasDatos.por_sabueso = r.por_sabueso;
                            renderPorSabueso();
                            initEstadisticasTooltips();
                        } else {
                            renderPorSabueso();
                        }
                    },
                    onError: function() {
                        $('#tbodyPorSabueso').html('<tr><td colspan="4" class="text-danger">Error al cargar. Intente de nuevo.</td></tr>');
                    }
                });
            });
            $('#grpResumenGestor').on('click', 'button[data-tab]', function() {
                $('#grpResumenGestor button').removeClass('active');
                $(this).addClass('active');
                var tab = $(this).data('tab');
                $('#panelResumenGlobal, #panelResumenGestor, #panelResumenSabueso').addClass('d-none');
                if (tab === 'gestor') {
                    $('#panelResumenGestor').removeClass('d-none');
                    aplicarVistaGestorTabla('lectura');
                    setTimeout(initEstadisticasTooltips, 100);
                } else if (tab === 'sabueso') {
                    $('#panelResumenSabueso').removeClass('d-none');
                    $('#grpFiltroPeriodoSabueso button').removeClass('active');
                    $('#grpFiltroPeriodoSabueso button[data-key="' + (estadisticasFiltroSabueso || 'por_dia') + '"]').addClass('active');
                    setTimeout(initEstadisticasTooltips, 100);
                } else {
                    $('#panelResumenGlobal').removeClass('d-none');
                }
            });
            $(document).on('click', '.btn-descargar-estad-detalle', function() {
                if (typeof Swal === 'undefined') return;
                Swal.fire({
                    title: 'Descargar Excel',
                    html: '<p class="text-start small">Se generará un archivo Excel con el detalle reciente (dictamen enviado): folio, quién levantó, asignado, fechas, % efectividad y medidas.</p><p class="text-start small mb-0"><strong>Nota:</strong> la descarga puede incluir hasta 2000 registros.</p>',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fa fa-download me-1"></i>Descargar',
                    cancelButtonText: 'Cancelar'
                }).then(function(r) {
                    if (!r.isConfirmed) return;
                    Swal.fire({ title: 'Generando…', allowOutsideClick: false, didOpen: function() { Swal.showLoading(); } });
                    fetch('/Reporteria/descargarReporteSabuesosEstadisticasDetalle', { method: 'POST', headers: { 'Content-Type': 'application/json' } })
                        .then(function(res) { if (!res.ok) throw new Error('Error en la descarga'); return res.blob(); })
                        .then(function(blob) {
                            Swal.close();
                            var url = window.URL.createObjectURL(blob);
                            var a = document.createElement('a');
                            a.href = url;
                            a.download = 'Estadisticas_Detalle_Dictamen_' + new Date().toISOString().slice(0, 10) + '.xlsx';
                            document.body.appendChild(a);
                            a.click();
                            window.URL.revokeObjectURL(url);
                            document.body.removeChild(a);
                            Swal.fire({ icon: 'success', title: 'Listo', timer: 1800, showConfirmButton: false });
                        })
                        .catch(function() {
                            Swal.fire('Error', 'No se pudo generar el Excel.', 'error');
                        });
                });
            });
            if (window.entrarDirectoEstadistica || !document.getElementById('estadisticasSelectorWrap')) iniciarDashboardSabueso();
        });
        </script>
        SCRIPT;
        $scriptPre = '<script>window.entrarDirectoEstadistica = ' . json_encode($entrarDirectoEstadistica) . ';</script>' . "\n";
        self::set('titulo', 'Estadísticas | Sabueso');
        self::set('script', $scriptPre . $script);
        self::render('sabueso_estadisticas');
    }

    /**
     * API: estadísticas agregadas de tickets (conteos, series, tiempos Sabueso/gestor).
     */
    public function getEstadisticasTickets()
    {
        $raw = file_get_contents('php://input');
        $body = $raw ? json_decode($raw, true) : [];
        if (!is_array($body)) {
            $body = [];
        }
        $periodoSabueso = (string)($body['periodo_sabueso'] ?? '');
        $opts = [];
        if ($periodoSabueso !== '') {
            $opts['periodo_sabueso'] = $periodoSabueso;
        }
        $datos = empty($opts) ? TicketDAO::getEstadisticasTickets() : TicketDAO::getEstadisticasTickets($opts);
        $success = !empty($datos['success']);
        $mensaje = $datos['mensaje'] ?? '';
        unset($datos['success'], $datos['mensaje']);
        self::respuestaJSON([
            'success' => $success,
            'mensaje' => $mensaje,
            'datos' => $datos
        ]);
    }

    /**
     * API drill Tickets levantados: meses por año, semanas por mes, 7 días por lunes.
     * Body: { "tipo": "meses"|"semanas"|"dias", "anio": 2026, "mes": 3, "lunes": "2026-03-09" }
     */
    public function getEstadisticasLevantadosDrill()
    {
        $raw = file_get_contents('php://input');
        $body = $raw ? json_decode($raw, true) : [];
        if (!is_array($body)) {
            $body = [];
        }
        $tipo = (string)($body['tipo'] ?? '');
        $res = TicketDAO::getEstadisticasLevantadosDrill($tipo, $body);
        self::respuestaJSON($res);
    }

    /**
     * API: detalle de tickets levantados en una fecha (para modal en Estadísticas → Tickets levantados → clic en día).
     * POST/GET: fecha (YYYY-MM-DD). Devuelve { success, mensaje, fecha, filas }.
     */
    public function getTicketsDetallePorDia()
    {
        $raw = file_get_contents('php://input');
        $body = $raw ? json_decode($raw, true) : [];
        if (!is_array($body)) {
            $body = [];
        }
        $fecha = isset($body['fecha']) ? trim((string)$body['fecha']) : (isset($_GET['fecha']) ? trim((string)$_GET['fecha']) : '');
        if ($fecha === '') {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Parámetro fecha (YYYY-MM-DD) requerido.', 'filas' => []]);
            return;
        }
        $res = TicketDAO::getTicketsDetallePorDia($fecha);
        self::respuestaJSON($res);
    }

    /**
     * API ligera: solo agregado Por Sabueso (dictaminó) — sin recalcular todo el dashboard.
     * Usar al cambiar Días/Semanas/Meses/Año en esa tabla.
     */
    public function getEstadisticasPorSabuesoSolo()
    {
        $raw = file_get_contents('php://input');
        $body = $raw ? json_decode($raw, true) : [];
        if (!is_array($body)) {
            $body = [];
        }
        $periodo = (string)($body['periodo_sabueso'] ?? 'por_dia');
        $res = TicketDAO::getEstadisticasPorSabuesoSolo($periodo, false);
        self::respuestaJSON($res);
    }

    /**
     * API: detalle por gestor (creador) — tickets levantados con dictamen enviado, visto/no, tiempo lectura.
     */
    public function getEstadisticasGestorDetalle()
    {
        $raw = file_get_contents('php://input');
        $body = $raw ? json_decode($raw, true) : [];
        if (!is_array($body)) {
            $body = [];
        }
        $id = (int)($body['id_persona_creador'] ?? $_POST['id_persona_creador'] ?? 0);
        $page = max(1, (int)($body['page'] ?? $_POST['page'] ?? 1));
        $perPage = max(1, min(100, (int)($body['per_page'] ?? $_POST['per_page'] ?? 50)));
        $vista = (string)($body['vista'] ?? $_POST['vista'] ?? 'lectura');
        $res = TicketDAO::getEstadisticasGestorDetalle($id, $page, $perPage, $vista);
        self::respuestaJSON($res);
    }

    /**
     * API: reporte semanal global (semana vencida y semanas anteriores).
     */
    public function getReporteSemanalGestorGlobal()
    {
        $raw = file_get_contents('php://input');
        $body = $raw ? json_decode($raw, true) : [];
        if (!is_array($body)) {
            $body = [];
        }
        $semanaInicio = trim((string)($body['semana_inicio'] ?? $_POST['semana_inicio'] ?? ''));
        $res = TicketDAO::getReporteSemanalGestorGlobal($semanaInicio);
        self::respuestaJSON($res);
    }

    /**
     * API: reconsulta EC para un ticket del reporte semanal (un crédito; tickets cerrados incluidos).
     */
    public function reconsultarPagoSemanaReporteSemanal()
    {
        $raw = file_get_contents('php://input');
        $body = $raw ? json_decode($raw, true) : [];
        if (!is_array($body)) {
            $body = [];
        }
        $idTicket = (int)($body['id_ticket'] ?? $_POST['id_ticket'] ?? 0);
        $semanaInicio = trim((string)($body['semana_inicio'] ?? $_POST['semana_inicio'] ?? ''));
        $res = TicketDAO::reconsultarPagoSemanaReporteSemanal($idTicket, $semanaInicio);
        self::respuestaJSON($res);
    }

    /**
     * API: guardar ajuste manual de «ilocalizable» en el reporte semanal (JSON por semana).
     * Body: semana_inicio (Y-m-d), id_ticket, modo: auto | si | no
     */
    public function guardarIlocalizableReporteSemanal()
    {
        $raw = file_get_contents('php://input');
        $body = $raw ? json_decode($raw, true) : [];
        if (!is_array($body)) {
            $body = [];
        }
        $idTicket = (int)($body['id_ticket'] ?? $_POST['id_ticket'] ?? 0);
        $semanaInicio = trim((string)($body['semana_inicio'] ?? $_POST['semana_inicio'] ?? ''));
        $modo = (string)($body['modo'] ?? $_POST['modo'] ?? 'auto');
        $res = TicketDAO::guardarIlocalizableReporteSemanal($semanaInicio, $idTicket, $modo);
        self::respuestaJSON($res);
    }

    public function getEstadisticasSabuesoDetalle()
    {
        $raw = file_get_contents('php://input');
        $body = $raw ? json_decode($raw, true) : [];
        if (!is_array($body)) {
            $body = [];
        }
        $id = (int)($body['id_persona_autor'] ?? $_POST['id_persona_autor'] ?? 0);
        $res = TicketDAO::getEstadisticasSabuesoDetalle($id);
        self::respuestaJSON($res);
    }

    /**
     * API: lista de tickets del usuario actual (solo los que él levantó).
     */
    public function getTickets()
    {
        $usuarioId = (int)($_SESSION['usuario_id'] ?? 0);
        $resultado = TicketDAO::getListaTickets($usuarioId, true);
        $datos = isset($resultado['datos']) ? $resultado['datos'] : [];
        self::respuestaJSON([
            'success' => $resultado['success'] ?? false,
            'mensaje' => $resultado['mensaje'] ?? '',
            'datos'   => $datos
        ]);
    }

    /**
     * API: lista de todos los tickets (Panel Admin), con creador_nombre.
     */
    public function getTicketsPanelAdmin()
    {
        $usuarioId = (int)($_SESSION['usuario_id'] ?? 0);
        $filtros = [];
        $raw = file_get_contents('php://input');
        if ($raw !== '' && $raw !== false) {
            $body = json_decode($raw, true);
            if (is_array($body) && isset($body['filtros']) && is_array($body['filtros'])) {
                $filtros = $body['filtros'];
            }
        }
        $personaIdApi = (int)($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        $panelesU = ConfigPanelUsuarioDAO::getPanelesPorPersona($personaIdApi);
        $permitidas = \Core\TicketsPanelModuloHelper::getCategoriasPermitidasTicketsApi($panelesU);
        if ($permitidas !== null) {
            if ($permitidas === []) {
                self::respuestaJSON(['success' => false, 'mensaje' => 'Sin paneles de administración asignados para su usuario.', 'datos' => []]);
                return;
            }
            $catF = isset($filtros['categoria_gestion']) ? strtolower(preg_replace('/[^a-z0-9_]/', '', (string) $filtros['categoria_gestion'])) : '';
            if ($catF === '') {
                $filtros['categoria_gestion'] = $permitidas[0];
            } elseif (!in_array($catF, $permitidas, true)) {
                self::respuestaJSON(['success' => false, 'mensaje' => 'No tiene permiso para consultar tickets de esta categoría.', 'datos' => []]);
                return;
            }
        }
        // Un listado siempre va acotado a una categoría (nunca "todos los módulos" mezclados).
        $catNorm = isset($filtros['categoria_gestion']) ? trim((string) $filtros['categoria_gestion']) : '';
        if ($catNorm === '') {
            $filtros['categoria_gestion'] = 'sabueso';
        }
        $resultado = TicketDAO::getListaTickets($usuarioId, false, $filtros);
        $datos = isset($resultado['datos']) ? $resultado['datos'] : [];
        self::respuestaJSON([
            'success' => $resultado['success'] ?? false,
            'mensaje' => $resultado['mensaje'] ?? '',
            'datos'   => $datos
        ]);
    }

    /**
     * API: lista de tickets cerrados/eliminados (fecha_eliminacion IS NOT NULL).
     */
    public function getTicketsCerradosEliminados()
    {
        $resultado = TicketDAO::getListaTicketsCerradosEliminados();
        $datos = isset($resultado['datos']) ? $resultado['datos'] : [];
        self::respuestaJSON([
            'success' => $resultado['success'] ?? false,
            'mensaje' => $resultado['mensaje'] ?? '',
            'datos'   => $datos
        ]);
    }

    /**
     * API: datos completos de un ticket cerrado/eliminado para el modal Ver
     * (crédito, ticket, historial asignación, dictamen, dictamen sistema, evidencias, quien eliminó).
     */
    public function getDatosTicketCerradoEliminado()
    {
        $raw = file_get_contents('php://input');
        $datos = json_decode($raw, true) ?: [];
        $idTicket = (int)($datos['id_ticket'] ?? 0);
        if ($idTicket < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'ID de ticket requerido.', 'datos' => null]);
            return;
        }
        $ticket = TicketDAO::getTicketCerradoEliminadoPorId($idTicket);
        if (!$ticket) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Ticket no encontrado o no está cerrado/eliminado.', 'datos' => null]);
            return;
        }
        $idCredito = (int)($ticket['id_credito'] ?? 0);
        $credito = [];
        if ($idCredito > 0) {
            $dir = EmpresaDAO::getConsultaDireccionEstadoCuenta($idCredito);
            $ref = EmpresaDAO::getConsultaReferenciasEstadoCuenta($idCredito);
            $datosDir = ($dir['success'] ?? false) && !empty($dir['datos']) ? $dir['datos'][0] : [];
            $datosRef = ($ref['success'] ?? false) && !empty($ref['datos']) ? $ref['datos'][0] : [];
            $credito = array_merge($datosDir ?: [], $datosRef ?: []);
            $credito['id_credito'] = $idCredito;
            if (empty($datosDir) && !empty($datosRef)) {
                $credito['Nombre_cliente'] = $credito['nombre_completo'] ?? $credito['Nombre_cliente'] ?? '—';
                $credito['Domicilio_Completo'] = $credito['Domicilio_Completo'] ?? 'No disponible';
            }
        }
        $historialTicket = TicketDAO::getHistorialAsignacionPorTicket($idTicket);
        $historial = $historialTicket['historial'] ?? [];

        $dictamenInfo = TicketDAO::getDictamenDetallePorTicket($idTicket);
        $dictamen = null;
        $domicilios = [];
        $evidencias = [];
        if (!empty($dictamenInfo['success']) && !empty($dictamenInfo['datos'])) {
            $dictamen = $dictamenInfo['datos']['dictamen'] ?? null;
            $domicilios = $dictamenInfo['datos']['domicilios'] ?? [];
            $evidencias = $dictamenInfo['datos']['evidencias'] ?? [];
        }

        $dictamenSistema = null;
        try {
            $db = new \Core\Database();
            $dsRow = $db->queryOne(
                "SELECT ds1.resultado, ds1.detalle, ds1.fecha_creacion FROM dictamen_sistema ds1 " .
                "INNER JOIN (SELECT id_ticket, MAX(id) AS mid FROM dictamen_sistema WHERE id_ticket = :tid GROUP BY id_ticket) dsmx " .
                "ON ds1.id_ticket = dsmx.id_ticket AND ds1.id = dsmx.mid",
                ['tid' => $idTicket]
            );
            if (is_array($dsRow) && $dsRow !== []) {
                $detJson = !empty($dsRow['detalle']) ? json_decode((string)$dsRow['detalle'], true) : null;
                $cumpl = TicketDAO::cumplimientoMetadatos($dsRow['resultado'] ?? null);
                $dictamenSistema = [
                    'resultado' => $dsRow['resultado'] ?? null,
                    'fecha_creacion' => $dsRow['fecha_creacion'] ?? null,
                    'cumplimiento_etiqueta' => $cumpl['cumplimiento_etiqueta'] ?? null,
                    'pct_efectividad' => $cumpl['pct_efectividad'] ?? null,
                    'medidas_preventivas' => $cumpl['medidas_preventivas'] ?? null,
                ];
                if (is_array($detJson)) {
                    $dictamenSistema['direcciones_dictamen_total'] = (int)($detJson['direcciones_dictamen_total'] ?? 0);
                    $dictamenSistema['direcciones_visitadas'] = (int)($detJson['direcciones_visitadas'] ?? 0);
                    $dictamenSistema['visito_todas_direcciones'] = !empty($detJson['visito_todas_direcciones']);
                    $dictamenSistema['pago_en_ventana'] = !empty($detJson['pago_en_ventana']);
                    $dictamenSistema['tipo_contacto'] = $detJson['tipo_contacto'] ?? null;
                    $cobertura = $detJson['cobertura_direcciones'] ?? null;
                    if (is_array($cobertura)) {
                        $dictamenSistema['cobertura_direcciones'] = $cobertura;
                    }
                    if (isset($detJson['prorroga']) && is_array($detJson['prorroga'])) {
                        $dictamenSistema['prorroga'] = $detJson['prorroga'];
                    }
                    if (isset($detJson['intensidad']) && is_array($detJson['intensidad'])) {
                        $dictamenSistema['intensidad'] = $detJson['intensidad'];
                    }
                }
            }
        } catch (\Throwable $e) {
            // dictamen_sistema opcional
        }

        self::respuestaJSON([
            'success' => true,
            'mensaje' => 'OK',
            'datos' => [
                'credito' => $credito,
                'ticket' => $ticket,
                'historial_asignacion' => $historial,
                'dictamen' => $dictamen,
                'domicilios' => $domicilios,
                'evidencias' => $evidencias,
                'dictamen_sistema' => $dictamenSistema,
            ]
        ]);
    }

    /**
     * Ventana cerrada: desde domingo 12:01 p.m. hasta lunes 7:00 a.m. (CDMX) no se puede levantar ticket.
     * @return string|null null = permitido; string = mensaje para mostrar
     */
    private static function mensajeSiVentanaLevantarTicketCerrada(): ?string
    {
        try {
            $tz = new \DateTimeZone('America/Mexico_City');
            $now = new \DateTime('now', $tz);
            $dow = (int) $now->format('N'); // 1=lunes … 7=domingo
            $h = (int) $now->format('G');
            $i = (int) $now->format('i');
            $minutes = $h * 60 + $i;
            // Domingo desde las 12:01 p.m. en adelante
            if ($dow === 7 && $minutes >= 12 * 60 + 1) {
                return 'En este momento el registro de tickets no está disponible. Por política operativa, el levantamiento se reanuda el lunes a las 7:00 a.m. (hora CDMX). Agradecemos su comprensión.';
            }
            // Lunes antes de las 7:00 a.m.
            if ($dow === 1 && $minutes < 7 * 60) {
                return 'En este momento el registro de tickets no está disponible. Podrá levantar nuevos tickets a partir de las 7:00 a.m. de hoy (hora CDMX). Gracias por su paciencia.';
            }
        } catch (\Exception $e) {
            // Si falla zona horaria, no bloquear
        }
        return null;
    }

    /**
     * API: indica si en este momento se permite levantar ticket.
     * Body opcional: { "categoria": "sabueso" } — la ventana domingo 12:01–lunes 7:00 aplica solo a Sabueso.
     */
    public function ticketLevantarPermitido()
    {
        $raw = file_get_contents('php://input');
        $body = json_decode($raw, true);
        $categoria = is_array($body) && isset($body['categoria']) ? strtolower(trim((string)$body['categoria'])) : '';
        // Solo Sabueso (o sin categoría = compatibilidad) usa la restricción horaria
        $aplicaVentanaSabueso = ($categoria === '' || $categoria === 'sabueso');
        $msg = $aplicaVentanaSabueso ? self::mensajeSiVentanaLevantarTicketCerrada() : null;
        self::respuestaJSON([
            'success' => true,
            'permitido' => $msg === null,
            'mensaje' => $msg ?? ''
        ]);
    }

    /**
     * API: catálogos para el modal Levantar ticket.
     */
    public function getCatalogosTicket()
    {
        $resultado = TicketDAO::getCatalogosTicket();
        self::respuestaJSON([
            'success' => $resultado['success'] ?? false,
            'mensaje'  => $resultado['mensaje'] ?? '',
            'datos'    => $resultado['datos'] ?? (object)[]
        ]);
    }

    /**
     * API: verifica si el usuario actual ya tiene un ticket activo con ese id_credito.
     * Solo aplica al mismo gestor (quien levantó); no se considera si otro gestor tiene ticket con ese crédito.
     * GET o POST: id_credito (int). Respuesta: { success: true, ya_tiene: bool }.
     */
    public function verificarCreditoDuplicadoCreador()
    {
        $idPersona = (int)($_SESSION['usuario_id'] ?? 0);
        if ($idPersona < 1) {
            self::respuestaJSON(['success' => false, 'ya_tiene' => false, 'mensaje' => 'Sesión inválida.']);
            return;
        }
        $idCredito = 0;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $raw = file_get_contents('php://input');
            $datos = json_decode($raw, true) ?: [];
            $idCredito = isset($datos['id_credito']) && $datos['id_credito'] !== '' && $datos['id_credito'] !== null
                ? (int)$datos['id_credito'] : 0;
        } else {
            $idCredito = isset($_GET['id_credito']) ? (int)$_GET['id_credito'] : 0;
        }
        if ($idCredito < 1) {
            self::respuestaJSON(['success' => true, 'ya_tiene' => false, 'ticket_existente' => null]);
            return;
        }
        $yaTiene = TicketDAO::tieneTicketConCreditoPorCreador($idCredito, $idPersona);
        $ticketExistente = $yaTiene ? TicketDAO::getUltimoTicketActivoConCreditoPorCreador($idCredito, $idPersona) : null;
        self::respuestaJSON(['success' => true, 'ya_tiene' => $yaTiene, 'ticket_existente' => $ticketExistente]);
    }

    /**
     * API: crear ticket (id_persona_creador = sesión, fecha_creacion = NOW CDMX).
     * Valida que el ID de crédito exista en Segundometro u Oferta antes de crear.
     */
    public function crearTicket()
    {
        $raw = file_get_contents('php://input');
        $datos = json_decode($raw, true) ?: [];
        $idPersona = (int)($_SESSION['usuario_id'] ?? 0);
        if ($idPersona < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Sesión inválida.']);
            return;
        }
        // Restricción domingo/lunes solo para tickets Sabueso
        $cat = isset($datos['categoria_gestion']) ? strtolower(trim((string)$datos['categoria_gestion'])) : 'sabueso';
        if ($cat === '' || $cat === 'sabueso') {
            $msgVentana = self::mensajeSiVentanaLevantarTicketCerrada();
            if ($msgVentana !== null) {
                self::respuestaJSON(['success' => false, 'mensaje' => $msgVentana]);
                return;
            }
        }
        $idCredito = isset($datos['id_credito']) && $datos['id_credito'] !== '' && $datos['id_credito'] !== null
            ? (int)$datos['id_credito'] : 0;
        if ($idCredito < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'El ID de crédito es obligatorio.']);
            return;
        }
        $dir = EmpresaDAO::getConsultaDireccionEstadoCuenta($idCredito);
        $ref = EmpresaDAO::getConsultaReferenciasEstadoCuenta($idCredito);
        $datosDir = ($dir['success'] ?? false) && !empty($dir['datos']) ? $dir['datos'] : [];
        $datosRef = ($ref['success'] ?? false) && !empty($ref['datos']) ? $ref['datos'] : [];
        if (empty($datosDir) && empty($datosRef)) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'El ID de crédito no existe o es incorrecto. No se puede crear el ticket. Verifique el número.'
            ]);
            return;
        }
        $resultado = TicketDAO::crear($datos, $idPersona);
        if ($resultado['success'] ?? false) {
            $idTicket = (int)($resultado['datos']['id_ticket'] ?? 0);
            $nombreCreador = trim($_SESSION['usuario_nombre'] ?? 'Alguien');
            // Solo el usuario 797 recibe notificación al abrir un ticket (requerimiento negocio).
            $personasSabueso = [797];
            Notificacion::crearParaPersonas($personasSabueso, 'ticket_levantado', 'Ticket nuevo levantado por ' . $nombreCreador, $idTicket > 0 ? $idTicket : null);
        }
        self::respuestaJSON([
            'success' => $resultado['success'] ?? false,
            'mensaje' => $resultado['mensaje'] ?? '',
            'datos'   => $resultado['datos'] ?? null
        ]);
    }

    /**
     * API: guardar solicitud de baja (Levantar ticket > Solicitud de baja).
     * POST: motivo_baja, detalle_motivo, descripcion (opcional), nombre_colaborador; opcional: adjunto[] (múltiples PDF o imágenes).
     */
    public function guardarSolicitudBaja()
    {
        header('Content-Type: application/json; charset=utf-8');
        $idPersona = (int)($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        if ($idPersona < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Debe iniciar sesión para enviar la solicitud.']);
            return;
        }
        $motivo = isset($_POST['motivo_baja']) ? trim((string)$_POST['motivo_baja']) : '';
        $detalle = isset($_POST['detalle_motivo']) ? trim((string)$_POST['detalle_motivo']) : '';
        $descripcion = isset($_POST['descripcion']) ? trim((string)$_POST['descripcion']) : '';
        $nombreColaborador = isset($_POST['nombre_colaborador']) ? trim((string)$_POST['nombre_colaborador']) : '';
        $archivosGuardados = [];
        if (!empty($_FILES['adjunto'])) {
            $tmpNames = $_FILES['adjunto']['tmp_name'];
            $names = $_FILES['adjunto']['name'] ?? [];
            $isMulti = is_array($tmpNames);
            if (!$isMulti) {
                $tmpNames = [$tmpNames];
                $names = [isset($_FILES['adjunto']['name']) ? $_FILES['adjunto']['name'] : ''];
            }
            $dir = sparta_uploads_join('solicitud_baja');
            \Core\SecureUpload::ensureDir($dir);
            foreach (array_keys($tmpNames) as $i) {
                $tmp = $tmpNames[$i] ?? null;
                if (empty($tmp) || !is_uploaded_file($tmp)) {
                    continue;
                }
                if (!\Core\SecureUpload::validateMime($tmp, \Core\SecureUpload::MIME_PDF_OR_IMAGES)) {
                    self::respuestaJSON(['success' => false, 'mensaje' => 'El adjunto debe ser PDF o imagen (JPG, PNG, GIF, WebP).']);
                    return;
                }
                $mime = \Core\SecureUpload::getMimeType($tmp);
                $ext = $mime ? \Core\SecureUpload::extensionFromMime($mime) : 'bin';
                $nombreArchivo = \Core\SecureUpload::generateSafeFilename($ext);
                $rutaCompleta = $dir . '/' . $nombreArchivo;
                if (!move_uploaded_file($tmp, $rutaCompleta)) {
                    self::respuestaJSON(['success' => false, 'mensaje' => 'Error al guardar el archivo adjunto.']);
                    return;
                }
                $nombreOriginal = isset($names[$i]) ? trim((string)$names[$i]) : $nombreArchivo;
                $archivosGuardados[] = ['nombre_original' => $nombreOriginal, 'ruta' => 'solicitud_baja/' . $nombreArchivo];
            }
        }
        $nombreArchivoOriginal = null;
        $rutaAdjunto = null;
        if (!empty($archivosGuardados)) {
            $primero = $archivosGuardados[0];
            $nombreArchivoOriginal = $primero['nombre_original'];
            $rutaAdjunto = $primero['ruta'];
        }
        $resultado = SolicitudBajaDAO::guardar([
            'motivo_baja'             => $motivo,
            'detalle_motivo'          => $detalle,
            'descripcion'             => $descripcion,
            'nombre_colaborador'      => $nombreColaborador,
            'nombre_archivo_original' => $nombreArchivoOriginal,
            'ruta_adjunto'            => $rutaAdjunto,
        ], $idPersona);
        if (($resultado['success'] ?? false) && !empty($resultado['datos']['id']) && count($archivosGuardados) > 1) {
            $idSolicitud = (int)$resultado['datos']['id'];
            $orden = 1;
            foreach (array_slice($archivosGuardados, 1) as $a) {
                SolicitudBajaDAO::guardarAdjunto($idSolicitud, $a['nombre_original'], $a['ruta'], $orden++);
            }
        }
        self::respuestaJSON([
            'success' => $resultado['success'] ?? false,
            'mensaje' => $resultado['mensaje'] ?? '',
            'datos'   => $resultado['datos'] ?? null
        ]);
    }

    /**
     * Guarda adjunto en sabueso_evidencias y registra en ticket_evidencia para un ticket simple.
     */
    private function guardarAdjuntoTicketSimple($idTicket, $idPersona)
    {
        if (empty($_FILES['adjunto']['tmp_name']) || !is_uploaded_file($_FILES['adjunto']['tmp_name'])) {
            return;
        }
        $tmp = $_FILES['adjunto']['tmp_name'];
        if (!\Core\SecureUpload::validateMime($tmp, \Core\SecureUpload::MIME_PDF_OR_IMAGES)) {
            return;
        }
        $mime = \Core\SecureUpload::getMimeType($tmp);
        $ext = $mime ? \Core\SecureUpload::extensionFromMime($mime) : 'bin';
        $dir = sparta_uploads_join('sabueso_evidencias');
        \Core\SecureUpload::ensureDir($dir);
        $nombreArchivo = 't' . $idTicket . '_' . \Core\SecureUpload::generateSafeFilename($ext);
        if (!move_uploaded_file($tmp, $dir . '/' . $nombreArchivo)) {
            return;
        }
        $rutaRelativa = 'sabueso_evidencias/' . $nombreArchivo;
        $nombreOriginal = isset($_FILES['adjunto']['name']) ? trim((string)$_FILES['adjunto']['name']) : $nombreArchivo;
        TicketDAO::guardarEvidencia($idTicket, $idPersona, $rutaRelativa, $nombreOriginal);
    }

    /**
     * Varios adjuntos (adjunto[] en FormData) o un solo adjunto (mismo campo que solicitud de baja).
     */
    private function guardarAdjuntosMultiplesTicketSimple(int $idTicket, int $idPersona): void
    {
        if (empty($_FILES['adjunto']['tmp_name'])) {
            return;
        }
        $tmps = $_FILES['adjunto']['tmp_name'];
        $names = $_FILES['adjunto']['name'] ?? [];
        if (!is_array($tmps)) {
            $tmps = [$tmps];
            $names = [is_string($names) ? $names : ''];
        }
        $dir = sparta_uploads_join('sabueso_evidencias');
        \Core\SecureUpload::ensureDir($dir);
        foreach ($tmps as $i => $tmp) {
            if (empty($tmp) || !is_uploaded_file($tmp)) {
                continue;
            }
            if (!\Core\SecureUpload::validateMime($tmp, \Core\SecureUpload::MIME_PDF_OR_IMAGES)) {
                continue;
            }
            $mime = \Core\SecureUpload::getMimeType($tmp);
            $ext = $mime ? \Core\SecureUpload::extensionFromMime($mime) : 'bin';
            $nombreArchivo = 't' . $idTicket . '_' . \Core\SecureUpload::generateSafeFilename($ext);
            if (!move_uploaded_file($tmp, $dir . '/' . $nombreArchivo)) {
                continue;
            }
            $rutaRelativa = 'sabueso_evidencias/' . $nombreArchivo;
            $nombreOriginal = isset($names[$i]) ? trim((string) $names[$i]) : $nombreArchivo;
            TicketDAO::guardarEvidencia($idTicket, $idPersona, $rutaRelativa, $nombreOriginal);
        }
    }

    /**
     * API: guardar ticket Plantilla (Levantar ticket > Plantilla). Se guarda en tabla ticket con categoria_gestion.
     * POST: tipo_plantilla, descripcion; opcional: adjunto (PDF o imagen).
     */
    public function guardarTicketPlantilla()
    {
        header('Content-Type: application/json; charset=utf-8');
        $idPersona = (int)($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        if ($idPersona < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Debe iniciar sesión.']);
            return;
        }
        $tipo = isset($_POST['tipo_plantilla']) ? trim((string)$_POST['tipo_plantilla']) : '';
        $descripcion = isset($_POST['descripcion']) ? trim((string)$_POST['descripcion']) : '';
        if ($tipo === '' || $descripcion === '') {
            self::respuestaJSON(['success' => false, 'mensaje' => 'El tipo de plantilla y la descripción son obligatorios.']);
            return;
        }
        $resultado = TicketDAO::crearTicketSimple([
            'categoria_gestion'   => 'plantilla',
            'tipo_categoria'      => $tipo,
            'descripcion_inicial' => $descripcion,
        ], $idPersona);
        if (($resultado['success'] ?? false) && !empty($resultado['datos']['id_ticket'])) {
            $this->guardarAdjuntoTicketSimple((int)$resultado['datos']['id_ticket'], $idPersona);
        }
        self::respuestaJSON(['success' => $resultado['success'] ?? false, 'mensaje' => $resultado['mensaje'] ?? '', 'datos' => $resultado['datos'] ?? null]);
    }

    /**
     * API: guardar ticket Atención al cliente. Se guarda en tabla ticket con categoria_gestion.
     * POST: asunto, descripcion, prioridad (alta|media|baja), contacto_telefono (opcional), contacto_email (opcional).
     */
    public function guardarTicketAtencionCliente()
    {
        header('Content-Type: application/json; charset=utf-8');
        $idPersona = (int)($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        if ($idPersona < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Debe iniciar sesión.']);
            return;
        }
        $asunto = isset($_POST['asunto']) ? trim((string)$_POST['asunto']) : '';
        $descripcion = isset($_POST['descripcion']) ? trim((string)$_POST['descripcion']) : '';
        if ($asunto === '' || $descripcion === '') {
            self::respuestaJSON(['success' => false, 'mensaje' => 'El asunto y la descripción son obligatorios.']);
            return;
        }
        $prioridad = isset($_POST['prioridad']) ? trim((string)$_POST['prioridad']) : 'media';
        $tel = isset($_POST['contacto_telefono']) ? trim((string)$_POST['contacto_telefono']) : '';
        $email = isset($_POST['contacto_email']) ? trim((string)$_POST['contacto_email']) : '';
        $resultado = TicketDAO::crearTicketSimple([
            'categoria_gestion'     => 'atencion_cliente',
            'asunto'                => $asunto,
            'prioridad_categoria'   => $prioridad,
            'contacto_telefono'     => $tel !== '' ? $tel : null,
            'contacto_email'        => $email !== '' ? $email : null,
            'descripcion_inicial'   => $descripcion,
        ], $idPersona);
        self::respuestaJSON(['success' => $resultado['success'] ?? false, 'mensaje' => $resultado['mensaje'] ?? '', 'datos' => $resultado['datos'] ?? null]);
    }

    /**
     * API: guardar ticket Validación de domicilio. Se guarda en tabla ticket con categoria_gestion.
     * POST: descripcion (obligatorio); opcional: adjunto (PDF o imagen), nota, url_direccion (se guarda completa).
     */
    public function guardarTicketValidacion()
    {
        header('Content-Type: application/json; charset=utf-8');
        $idPersona = (int)($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        if ($idPersona < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Debe iniciar sesión.']);
            return;
        }
        $descripcion = isset($_POST['descripcion']) ? trim((string)$_POST['descripcion']) : '';
        if ($descripcion === '') {
            self::respuestaJSON(['success' => false, 'mensaje' => 'La descripción es obligatoria.']);
            return;
        }
        $nota = isset($_POST['nota']) ? trim((string)$_POST['nota']) : null;
        $urlDireccion = isset($_POST['url_direccion']) ? trim((string)$_POST['url_direccion']) : null;
        $resultado = TicketDAO::crearTicketSimple([
            'categoria_gestion'   => 'validaciones',
            'tipo_categoria'      => 'Validación de domicilio',
            'descripcion_inicial' => $descripcion,
            'nota'                => $nota !== '' ? $nota : null,
            'url_direccion'       => $urlDireccion !== '' ? $urlDireccion : null,
        ], $idPersona);
        if (($resultado['success'] ?? false) && !empty($resultado['datos']['id_ticket'])) {
            $this->guardarAdjuntosMultiplesTicketSimple((int) $resultado['datos']['id_ticket'], $idPersona);
        }
        self::respuestaJSON(['success' => $resultado['success'] ?? false, 'mensaje' => $resultado['mensaje'] ?? '', 'datos' => $resultado['datos'] ?? null]);
    }

    /**
     * API: guardar ticket Viáticos. Se guarda en tabla ticket con categoria_gestion.
     * POST: tipo_viatico, descripcion; opcional: adjunto.
     */
    public function guardarTicketViaticos()
    {
        header('Content-Type: application/json; charset=utf-8');
        $idPersona = (int)($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        if ($idPersona < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Debe iniciar sesión.']);
            return;
        }
        $tipo = isset($_POST['tipo_viatico']) ? trim((string)$_POST['tipo_viatico']) : '';
        $descripcion = isset($_POST['descripcion']) ? trim((string)$_POST['descripcion']) : '';
        if ($tipo === '' || $descripcion === '') {
            self::respuestaJSON(['success' => false, 'mensaje' => 'El tipo de viático y la descripción son obligatorios.']);
            return;
        }
        $resultado = TicketDAO::crearTicketSimple([
            'categoria_gestion'   => 'viaticos',
            'tipo_categoria'      => $tipo,
            'descripcion_inicial' => $descripcion,
        ], $idPersona);
        if (($resultado['success'] ?? false) && !empty($resultado['datos']['id_ticket'])) {
            $this->guardarAdjuntoTicketSimple((int)$resultado['datos']['id_ticket'], $idPersona);
        }
        self::respuestaJSON(['success' => $resultado['success'] ?? false, 'mensaje' => $resultado['mensaje'] ?? '', 'datos' => $resultado['datos'] ?? null]);
    }

    /**
     * API: guardar ticket Aplicaciones de pago. Se guarda en tabla ticket con categoria_gestion.
     * POST: tipo_solicitud, descripcion; opcional: adjunto.
     */
    public function guardarTicketAplicacionesPago()
    {
        header('Content-Type: application/json; charset=utf-8');
        $idPersona = (int)($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        if ($idPersona < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Debe iniciar sesión.']);
            return;
        }
        $tipo = isset($_POST['tipo_solicitud']) ? trim((string)$_POST['tipo_solicitud']) : '';
        $descripcion = isset($_POST['descripcion']) ? trim((string)$_POST['descripcion']) : '';
        if ($tipo === '' || $descripcion === '') {
            self::respuestaJSON(['success' => false, 'mensaje' => 'El tipo de solicitud y la descripción son obligatorios.']);
            return;
        }
        $resultado = TicketDAO::crearTicketSimple([
            'categoria_gestion'   => 'aplicaciones_de_pago',
            'tipo_categoria'      => $tipo,
            'descripcion_inicial' => $descripcion,
        ], $idPersona);
        if (($resultado['success'] ?? false) && !empty($resultado['datos']['id_ticket'])) {
            $this->guardarAdjuntoTicketSimple((int)$resultado['datos']['id_ticket'], $idPersona);
        }
        self::respuestaJSON(['success' => $resultado['success'] ?? false, 'mensaje' => $resultado['mensaje'] ?? '', 'datos' => $resultado['datos'] ?? null]);
    }

    /**
     * API: guardar ticket Crédito problemático. Se guarda en tabla ticket con categoria_gestion.
     * POST: tipo_solicitud, descripcion; opcional: adjunto.
     */
    public function guardarTicketCreditoProblematico()
    {
        header('Content-Type: application/json; charset=utf-8');
        $idPersona = (int)($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        if ($idPersona < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Debe iniciar sesión.']);
            return;
        }
        $tipo = isset($_POST['tipo_solicitud']) ? trim((string)$_POST['tipo_solicitud']) : '';
        $descripcion = isset($_POST['descripcion']) ? trim((string)$_POST['descripcion']) : '';
        if ($tipo === '' || $descripcion === '') {
            self::respuestaJSON(['success' => false, 'mensaje' => 'El tipo de solicitud y la descripción son obligatorios.']);
            return;
        }
        $resultado = TicketDAO::crearTicketSimple([
            'categoria_gestion'   => 'credito_problematico',
            'tipo_categoria'      => $tipo,
            'descripcion_inicial' => $descripcion,
        ], $idPersona);
        if (($resultado['success'] ?? false) && !empty($resultado['datos']['id_ticket'])) {
            $this->guardarAdjuntoTicketSimple((int)$resultado['datos']['id_ticket'], $idPersona);
        }
        self::respuestaJSON(['success' => $resultado['success'] ?? false, 'mensaje' => $resultado['mensaje'] ?? '', 'datos' => $resultado['datos'] ?? null]);
    }

    /**
     * API: guardar ticket Aclaración de crédito. Se guarda en tabla ticket con categoria_gestion.
     * POST: tipo_aclaracion, descripcion; opcional: adjunto.
     */
    public function guardarTicketAclaracionCredito()
    {
        header('Content-Type: application/json; charset=utf-8');
        $idPersona = (int)($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        if ($idPersona < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Debe iniciar sesión.']);
            return;
        }
        $tipo = isset($_POST['tipo_aclaracion']) ? trim((string)$_POST['tipo_aclaracion']) : '';
        $descripcion = isset($_POST['descripcion']) ? trim((string)$_POST['descripcion']) : '';
        if ($tipo === '' || $descripcion === '') {
            self::respuestaJSON(['success' => false, 'mensaje' => 'El tipo de aclaración y la descripción son obligatorios.']);
            return;
        }
        $resultado = TicketDAO::crearTicketSimple([
            'categoria_gestion'   => 'aclaracion_credito',
            'tipo_categoria'      => $tipo,
            'descripcion_inicial' => $descripcion,
        ], $idPersona);
        if (($resultado['success'] ?? false) && !empty($resultado['datos']['id_ticket'])) {
            $this->guardarAdjuntoTicketSimple((int)$resultado['datos']['id_ticket'], $idPersona);
        }
        self::respuestaJSON(['success' => $resultado['success'] ?? false, 'mensaje' => $resultado['mensaje'] ?? '', 'datos' => $resultado['datos'] ?? null]);
    }

    /**
     * API: listado de solicitudes de baja (Panel Admin).
     */
    public function getSolicitudesBaja()
    {
        header('Content-Type: application/json; charset=utf-8');
        $pidSb = (int)($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        if (!in_array('sabueso_panelsolicitudbaja', ConfigPanelUsuarioDAO::getPanelesPorPersona($pidSb), true)) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Sin permiso para ver solicitudes de baja.', 'datos' => []]);
            return;
        }
        $resultado = SolicitudBajaDAO::getLista();
        $datos = isset($resultado['datos']) ? $resultado['datos'] : [];
        self::respuestaJSON([
            'success' => $resultado['success'] ?? false,
            'mensaje' => $resultado['mensaje'] ?? '',
            'datos'   => $datos
        ]);
    }

    /**
     * API: una solicitud de baja por ID (modal Ver en Panel Admin).
     */
    public function getSolicitudBajaPorId()
    {
        header('Content-Type: application/json; charset=utf-8');
        $raw = file_get_contents('php://input');
        $body = json_decode($raw, true) ?: [];
        $id = (int)($body['id'] ?? 0);
        if ($id < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'ID requerido.', 'datos' => null]);
            return;
        }
        $row = SolicitudBajaDAO::getPorId($id);
        if (!$row) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Solicitud no encontrada.', 'datos' => null]);
            return;
        }
        $row['adjuntos_adicionales'] = SolicitudBajaDAO::getAdjuntosAdicionales($id);
        self::respuestaJSON(['success' => true, 'mensaje' => '', 'datos' => $row]);
    }

    /**
     * Sirve el archivo adjunto de una solicitud de baja (descarga). Respuesta binaria.
     * GET: id (obligatorio), num (opcional): 1 = primer adjunto (tabla solicitud_baja), 2+ = adjuntos en solicitud_baja_adjunto.
     */
    public function verAdjuntoSolicitudBaja()
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id < 1) {
            http_response_code(400);
            return;
        }
        $num = (int)($_GET['num'] ?? 1);
        $rutaArchivo = null;
        $nombreDescarga = null;
        if ($num <= 1) {
            $row = SolicitudBajaDAO::getPorId($id);
            if ($row && !empty($row['ruta_adjunto'])) {
                $rutaArchivo = $row['ruta_adjunto'];
                $nombreDescarga = !empty($row['nombre_archivo_original']) ? $row['nombre_archivo_original'] : basename($rutaArchivo);
            }
        } else {
            $adjuntos = SolicitudBajaDAO::getAdjuntosAdicionales($id);
            $idx = $num - 2;
            if (isset($adjuntos[$idx])) {
                $rutaArchivo = $adjuntos[$idx]['ruta_archivo'];
                $nombreDescarga = !empty($adjuntos[$idx]['nombre_original']) ? $adjuntos[$idx]['nombre_original'] : basename($rutaArchivo);
            }
        }
        if ($rutaArchivo === null) {
            http_response_code(404);
            return;
        }
        $path = sparta_uploads_resolve_relative($rutaArchivo);
        if ($path === null || !is_file($path)) {
            http_response_code(404);
            return;
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path);
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($path));
        header('Content-Disposition: inline; filename="' . str_replace('"', '\\"', $nombreDescarga) . '"');
        header('Cache-Control: private, max-age=86400');
        readfile($path);
        exit;
    }

    /**
     * API: crear ticket desde bot de WhatsApp (sin sesión; requiere API key).
     * Origen = WhatsApp, id_persona_creador = Bot WhatsApp.
     * Cabecera: X-API-Key: <TICKET_WHATSAPP_API_KEY> o body: { "api_key": "...", ... }
     */
    public function crearTicketWhatsApp()
    {
        $raw = file_get_contents('php://input');
        $datos = json_decode($raw, true) ?: [];

        $apiKey = isset($_SERVER['HTTP_X_API_KEY']) ? trim($_SERVER['HTTP_X_API_KEY']) : (isset($datos['api_key']) ? trim((string)$datos['api_key']) : '');
        $claveEsperada = defined('TICKET_WHATSAPP_API_KEY') ? TICKET_WHATSAPP_API_KEY : '';
        if ($claveEsperada === '' || $apiKey !== $claveEsperada) {
            header('HTTP/1.0 401 Unauthorized');
            self::respuestaJSON(['success' => false, 'mensaje' => 'API key inválida o no enviada.']);
            return;
        }
        // WhatsApp sigue siendo flujo Sabueso: aplica ventana
        $msgVentana = self::mensajeSiVentanaLevantarTicketCerrada();
        if ($msgVentana !== null) {
            self::respuestaJSON(['success' => false, 'mensaje' => $msgVentana]);
            return;
        }

        $idOrigenWhatsApp = TicketDAO::getIdOrigenWhatsApp();
        $idPersonaBot = TicketDAO::getIdPersonaBotWhatsApp();
        if ($idOrigenWhatsApp < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'No está configurado el origen "WhatsApp". Contacte al administrador para configurarlo en la base de datos.']);
            return;
        }
        if ($idPersonaBot < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'No está configurado el usuario "Bot WhatsApp". Contacte al administrador para configurarlo en la base de datos.']);
            return;
        }

        $datos['id_origen_ticket'] = $idOrigenWhatsApp;
        $datos['categoria_gestion'] = 'sabueso';
        $idCredito = isset($datos['id_credito']) && $datos['id_credito'] !== '' && $datos['id_credito'] !== null
            ? (int)$datos['id_credito'] : 0;
        if ($idCredito < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'El ID de crédito es obligatorio.']);
            return;
        }
        $dir = EmpresaDAO::getConsultaDireccionEstadoCuenta($idCredito);
        $ref = EmpresaDAO::getConsultaReferenciasEstadoCuenta($idCredito);
        $datosDir = ($dir['success'] ?? false) && !empty($dir['datos']) ? $dir['datos'] : [];
        $datosRef = ($ref['success'] ?? false) && !empty($ref['datos']) ? $ref['datos'] : [];
        if (empty($datosDir) && empty($datosRef)) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'El ID de crédito no existe o es incorrecto. No se puede crear el ticket.'
            ]);
            return;
        }
        $resultado = TicketDAO::crear($datos, $idPersonaBot);
        self::respuestaJSON([
            'success' => $resultado['success'] ?? false,
            'mensaje' => $resultado['mensaje'] ?? '',
            'datos'   => $resultado['datos'] ?? null
        ]);
    }

    /**
     * API: eliminar ticket (soft delete: marca fecha_eliminacion e id_persona_elimino).
     */
    public function eliminarTicket()
    {
        if (ob_get_length()) {
            ob_clean();
        }
        header('Content-Type: application/json; charset=utf-8');
        $raw = file_get_contents('php://input');
        $datos = json_decode($raw, true) ?: [];
        $idTicket = (int)($datos['id_ticket'] ?? 0);
        if ($idTicket < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'ID de ticket inválido.']);
            return;
        }
        $idPersona = (int)($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        $resultado = TicketDAO::eliminar($idTicket, $idPersona > 0 ? $idPersona : null);
        self::respuestaJSON([
            'success' => $resultado['success'] ?? false,
            'mensaje' => $resultado['mensaje'] ?? ''
        ]);
    }

    /**
     * API: cerrar ticket (registra en ticket_historico con tipo_accion=cerrado y soft-delete).
     */
    public function cerrarTicket()
    {
        if (ob_get_length()) {
            ob_clean();
        }
        header('Content-Type: application/json; charset=utf-8');
        $raw = file_get_contents('php://input');
        $datos = json_decode($raw, true) ?: [];
        $idTicket = (int)($datos['id_ticket'] ?? 0);
        if ($idTicket < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'ID de ticket inválido.']);
            return;
        }
        $idPersona = (int)($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        $resultado = TicketDAO::cerrar($idTicket, $idPersona > 0 ? $idPersona : null);
        self::respuestaJSON([
            'success' => $resultado['success'] ?? false,
            'mensaje' => $resultado['mensaje'] ?? ''
        ]);
    }

    /**
     * API: datos completos de un crédito (persona, domicilio, referencias) para el modal de búsqueda.
     */
    public function getDatosCredito()
    {
        $raw = file_get_contents('php://input');
        $datos = json_decode($raw, true) ?: [];
        $idCredito = isset($datos['id_credito']) && $datos['id_credito'] !== '' ? (int)$datos['id_credito'] : 0;
        /** Estado de cuenta embebido: no FAD (evita timeout a API Python) ni tickets; solo datos para mapas/analítica. */
        $omitirFad = !empty($datos['omitir_fad']);
        if ($idCredito < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'ID de crédito requerido.', 'datos' => null]);
            return;
        }
        $dir = EmpresaDAO::getConsultaDireccionEstadoCuenta($idCredito);
        $ref = EmpresaDAO::getConsultaReferenciasEstadoCuenta($idCredito);
        $datosDir = ($dir['success'] ?? false) && !empty($dir['datos']) ? $dir['datos'][0] : [];
        $datosRef = ($ref['success'] ?? false) && !empty($ref['datos']) ? $ref['datos'][0] : [];
        $todo = array_merge($datosDir ?: [], $datosRef ?: []);
        $todo['id_credito'] = $idCredito;
        // Si no hay datos en ninguna fuente: success true + datos null para que el front muestre alert sin lanzar error.
        if (empty($datosDir) && empty($datosRef)) {
            self::respuestaJSON([
                'success' => true,
                'mensaje' => 'El ID de crédito no existe o es incorrecto. Verifique el número e intente de nuevo.',
                'datos' => null
            ]);
            return;
        }
        if (empty($datosDir) && !empty($datosRef)) {
            $todo['Nombre_cliente'] = $todo['nombre_completo'] ?? $todo['Nombre_cliente'] ?? '—';
            $todo['Domicilio_Completo'] = $todo['Domicilio_Completo'] ?? 'No disponible en esta fuente';
        }
        // Información de ingresos (donde trabaja) desde FAD_DOC: primero BD, si no hay intentar extraer y guardar
        $todo['informacion_ingresos'] = '';
        $todo['empresa'] = '';
        $todo['empleado'] = '';
        $todo['ingreso_mensual_neto'] = '';
        $todo['telefono_laboral'] = '';
        if (!$omitirFad) {
            $infoSabueso = CreditoInfoSabueso::getPorCredito($idCredito);
            if ($infoSabueso && (trim((string)($infoSabueso['informacion_ingresos'] ?? '')) !== '' || trim((string)($infoSabueso['empresa'] ?? '')) !== '')) {
                $todo['informacion_ingresos'] = $infoSabueso['informacion_ingresos'] ?? '';
                $todo['empresa'] = $infoSabueso['empresa'] ?? '';
                $todo['empleado'] = $infoSabueso['empleado'] ?? '';
                $todo['ingreso_mensual_neto'] = $infoSabueso['ingreso_mensual_neto'] ?? '';
                $todo['telefono_laboral'] = $infoSabueso['telefono'] ?? '';
            } else {
                $extraido = $this->extraerInformacionIngresosFAD($idCredito);
                if ($extraido !== null && is_array($extraido) && empty($extraido['error'])) {
                    CreditoInfoSabueso::guardar(
                        $idCredito,
                        $extraido['texto_seccion'] ?? '',
                        $extraido['empresa'] ?? null,
                        $extraido['empleado'] ?? null,
                        $extraido['ingreso_mensual_neto'] ?? null,
                        $extraido['telefono'] ?? null
                    );
                    $todo['informacion_ingresos'] = $extraido['texto_seccion'] ?? '';
                    $todo['empresa'] = $extraido['empresa'] ?? '';
                    $todo['empleado'] = $extraido['empleado'] ?? '';
                    $todo['ingreso_mensual_neto'] = $extraido['ingreso_mensual_neto'] ?? '';
                    $todo['telefono_laboral'] = $extraido['telefono'] ?? '';
                } elseif (is_array($extraido) && !empty($extraido['error'])) {
                    $todo['_fad_debug'] = $extraido['error'];
                    if (!empty($extraido['detail'])) {
                        $todo['_fad_debug_detail'] = $extraido['detail'];
                    }
                }
            }
        }
        $todo['tickets'] = $omitirFad ? [] : TicketDAO::getTicketsPorIdCredito($idCredito);
        self::respuestaJSON(['success' => true, 'mensaje' => 'OK', 'datos' => $todo]);
    }

    /**
     * Intenta extraer la sección "Información de Ingresos" del FAD_DOC (PDF) vía API Python.
     * Devuelve array con texto_seccion, empresa, empleado, ingreso_mensual_neto, telefono en éxito,
     * o array con clave 'error' (no_pdf, api_no_config, api_error, api_empty) si falla.
     */
    private function extraerInformacionIngresosFAD($idCredito)
    {
        $id = (int) $idCredito;
        if ($id < 1) {
            return ['error' => 'id_invalido'];
        }
        $estadoCuenta = new \Controllers\EstadoCuenta();
        $info = $estadoCuenta->getRutaPdfFAD_DOC($id);
        if (!$info || empty($info['path']) || !is_file($info['path'])) {
            return ['error' => 'no_pdf'];
        }
        $path = $info['path'];
        $isTemp = !empty($info['isTemp']);
        $configPath = dirname(__DIR__) . '/config/config.ini';
        $config = is_file($configPath) ? parse_ini_file($configPath, true) : [];
        $apiUrl = isset($config['doc_verificacion']['api_url']) ? trim($config['doc_verificacion']['api_url']) : '';
        $apiKey = isset($config['doc_verificacion']['api_key']) ? trim($config['doc_verificacion']['api_key']) : 'sparta-__SPARTA_SECRET_REDACTED__-doc-verificacion-key';
        if ($apiUrl === '') {
            if ($isTemp) {
                @unlink($path);
            }
            return ['error' => 'api_no_config'];
        }
        $baseUrl = preg_replace('#/verificar\s*$#', '', $apiUrl);
        $endpoint = rtrim($baseUrl, '/') . '/fad/informacion-ingresos';
        $cfile = new \CURLFile($path, 'application/pdf', 'fad_doc.pdf');
        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => ['documento' => $cfile],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'X-API-Key: ' . $apiKey,
            ],
        ]);
        $response = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);
        if ($isTemp) {
            @unlink($path);
        }
        if ($code !== 200 || $response === false) {
            return ['error' => 'api_error', 'detail' => $curlErr ?: 'HTTP ' . $code];
        }
        $json = json_decode($response, true);
        if (!is_array($json) || empty($json['success'])) {
            return ['error' => 'api_error', 'detail' => 'respuesta inválida'];
        }
        $texto = isset($json['texto_seccion']) ? trim((string) $json['texto_seccion']) : '';
        $empresa = isset($json['empresa']) ? trim((string) $json['empresa']) : '';
        $empleado = isset($json['empleado']) ? trim((string) $json['empleado']) : '';
        $ingreso = isset($json['ingreso_mensual_neto']) ? trim((string) $json['ingreso_mensual_neto']) : '';
        $telefono = isset($json['telefono']) ? trim((string) $json['telefono']) : '';
        if ($texto === '' && $empresa === '' && $empleado === '' && $ingreso === '' && $telefono === '') {
            return ['error' => 'api_empty'];
        }
        return [
            'texto_seccion' => $texto,
            'empresa' => $empresa,
            'empleado' => $empleado,
            'ingreso_mensual_neto' => $ingreso,
            'telefono' => $telefono,
        ];
    }

    /**
     * API: histórico de gestiones por id_credito (Contactación y dictamen, Promesas y comentarios).
     * Para el panel de rastreo se devuelven las 16 gestiones más recientes (con coordenadas para el mapa).
     */
    public function getGestionesCredito()
    {
        $raw = file_get_contents('php://input');
        $datos = json_decode($raw, true) ?: [];
        $idCredito = isset($datos['id_credito']) && $datos['id_credito'] !== '' ? (int)$datos['id_credito'] : 0;
        if ($idCredito < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'ID de crédito requerido.', 'datos' => []]);
            return;
        }
        try {
            $gestiones = GestionesDAO::getGestionesParaRastreoCredito((string) $idCredito, 16, 80);
            $gestiones = is_array($gestiones) ? $gestiones : [];
            self::respuestaJSON(['success' => true, 'mensaje' => 'OK', 'datos' => $gestiones]);
        } catch (\Exception $e) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Error al obtener gestiones.', 'datos' => []]);
        }
    }

    /**
     * API: asignar ticket a una persona (Panel Admin).
     */
    public function asignarTicket()
    {
        $raw = file_get_contents('php://input');
        $datos = json_decode($raw, true) ?: [];
        $idTicket = (int)($datos['id_ticket'] ?? 0);
        $idPersona = (int)($datos['id_persona'] ?? 0);
        if ($idTicket < 1 || $idPersona < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'ID de ticket y persona requeridos.']);
            return;
        }
        $quienAsigna = (int)($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        $resultado = TicketDAO::asignar($idTicket, $idPersona, $quienAsigna > 0 ? $quienAsigna : null);
        if ($resultado['success'] ?? false) {
            $idCredito = TicketDAO::getIdCreditoPorTicket($idTicket);
            if ($idCredito !== null) {
                $nombrePersona = TicketDAO::getNombrePersona($idPersona);
                RegistroAsignacion::registrarAsignacion($idCredito, $nombrePersona !== '' ? $nombrePersona : 'Persona #' . $idPersona);
            }
        }
        self::respuestaJSON([
            'success' => $resultado['success'] ?? false,
            'mensaje' => $resultado['mensaje'] ?? ''
        ]);
    }

    /**
     * API: quitar la asignación del ticket (Panel Admin).
     */
    public function quitarAsignacionTicket()
    {
        $raw = file_get_contents('php://input');
        $datos = json_decode($raw, true) ?: [];
        $idTicket = (int)($datos['id_ticket'] ?? 0);
        if ($idTicket < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'ID de ticket requerido.']);
            return;
        }
        $resultado = TicketDAO::quitarAsignacion($idTicket);
        if ($resultado['success'] ?? false) {
            $idCredito = TicketDAO::getIdCreditoPorTicket($idTicket);
            if ($idCredito !== null) {
                RegistroAsignacion::cerrarAsignacionActiva($idCredito);
            }
        }
        self::respuestaJSON(['success' => $resultado['success'] ?? false, 'mensaje' => $resultado['mensaje'] ?? '']);
    }

    /**
     * API: historial de asignación por crédito (para tooltip "Asignado a" por crédito).
     * POST body: { id_credito: number }. Respuesta: asignado_actual, estado, historial.
     */
    public function getHistorialAsignacionCredito()
    {
        $raw = file_get_contents('php://input');
        $datos = json_decode($raw, true) ?: [];
        $idCredito = isset($datos['id_credito']) ? (int) $datos['id_credito'] : 0;
        if ($idCredito < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'ID de crédito requerido.', 'asignado_actual' => null, 'estado' => 'primera_asignacion', 'historial' => []]);
            return;
        }
        $payload = RegistroAsignacion::getHistorialPorCredito($idCredito);
        self::respuestaJSON(array_merge(['success' => true], $payload));
    }

    /**
     * API: historial de asignación por ticket (modal rastreo: "Asignado a" es por ticket, no por crédito).
     * POST body: { id_ticket: number }. Respuesta: asignado_actual, estado, historial.
     */
    public function getHistorialAsignacionTicket()
    {
        $raw = file_get_contents('php://input');
        $datos = json_decode($raw, true) ?: [];
        $idTicket = isset($datos['id_ticket']) ? (int) $datos['id_ticket'] : 0;
        if ($idTicket < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'ID de ticket requerido.', 'asignado_actual' => null, 'estado' => 'primera_asignacion', 'historial' => []]);
            return;
        }
        $payload = TicketDAO::getHistorialAsignacionPorTicket($idTicket);
        self::respuestaJSON(array_merge(['success' => true], $payload));
    }

    /** Rango en metros para considerar un punto de Maxi App "su casa" (igual que geofence cumplimiento gestor). */
    private const RANGO_CASA_M = 100;

    /**
     * API: ubicaciones filtradas por id_credito (idCliente desde segundometro, tabla ubicacion en AWS).
     * Devuelve direcciones_resumen, puntos_mapa, domicilio_megareporte { lat, lng } e indice_casa (índice en puntos_mapa del punto dentro del rango de Megareporte, o null).
     */
    public function getUbicacionesCredito()
    {
        $raw = file_get_contents('php://input');
        $datos = json_decode($raw, true) ?: [];
        $idCredito = isset($datos['id_credito']) && $datos['id_credito'] !== '' ? (int)$datos['id_credito'] : 0;
        if ($idCredito < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'ID de crédito requerido.', 'direcciones_resumen' => [], 'puntos_mapa' => [], 'puntos_geo' => [], 'domicilio_megareporte' => null, 'indice_casa' => null]);
            return;
        }
        try {
            $resultado = UbicacionDAO::getUbicacionesFiltradasPorIdCredito($idCredito);
            $puntosMapa = $resultado['puntos_mapa'] ?? [];
            $puntosGeo = OfertaCoordenada::getPorIdCredito($idCredito);
            $domicilioMegareporte = null;
            $indiceCasa = null;

            $dirMegareporte = EmpresaDAO::getConsultaDireccionEstadoCuenta($idCredito);
            $domicilioCompleto = ($dirMegareporte['success'] ?? false) && !empty($dirMegareporte['datos'][0]['Domicilio_Completo'])
                ? trim((string) $dirMegareporte['datos'][0]['Domicilio_Completo'])
                : '';
            if ($domicilioCompleto !== '' && !empty($puntosMapa)) {
                $geocoding = new GeocodingService();
                $coordsMegareporte = $geocoding->getDomicilioCoordsForCredito($idCredito, $domicilioCompleto);
                if (!empty($coordsMegareporte)) {
                    $domicilioMegareporte = [
                        'lat' => (float) $coordsMegareporte['lat'],
                        'lng' => (float) $coordsMegareporte['lng'],
                    ];
                    $domicilio = [
                        'id' => 'megareporte',
                        'lat' => (float) $coordsMegareporte['lat'],
                        'lng' => (float) $coordsMegareporte['lng'],
                        'label' => $coordsMegareporte['label'] ?? 'Domicilio megareporte',
                    ];
                    $ubicacionesUsuario = [];
                    foreach ($puntosMapa as $i => $p) {
                        $ubicacionesUsuario[] = [
                            'id' => 'u' . $i,
                            'lat' => (float) ($p['latitud'] ?? $p['lat'] ?? 0),
                            'lng' => (float) ($p['longitud'] ?? $p['lng'] ?? 0),
                            'label' => ($p['punto_de_interes'] ?? false) ? 'Punto de interés' : 'Menos frecuente',
                            'visitas_count' => (int) ($p['cantidad_registros'] ?? 0),
                        ];
                    }
                    $spatial = new SpatialAnalyticsService();
                    $distanciasCasa = $spatial->calcularDistanciasCasa($ubicacionesUsuario, $domicilio);
                    $minDist = null;
                    foreach ($distanciasCasa as $idx => $row) {
                        $d = (float) ($row['distancia_m'] ?? 999999);
                        if ($d <= self::RANGO_CASA_M && ($minDist === null || $d < $minDist)) {
                            $minDist = $d;
                            $indiceCasa = $idx;
                        }
                    }
                }
            }

            self::respuestaJSON([
                'success' => $resultado['success'] ?? true,
                'mensaje' => $resultado['mensaje'] ?? '',
                'id_cliente' => $resultado['id_cliente'] ?? null,
                'direcciones_resumen' => $resultado['direcciones_resumen'] ?? [],
                'puntos_mapa' => $puntosMapa,
                'puntos_geo' => $puntosGeo,
                'domicilio_megareporte' => $domicilioMegareporte,
                'indice_casa' => $indiceCasa,
            ]);
        } catch (\Exception $e) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Error al obtener ubicaciones.', 'direcciones_resumen' => [], 'puntos_mapa' => [], 'puntos_geo' => [], 'domicilio_megareporte' => null, 'indice_casa' => null]);
        }
    }

    /**
     * Llama a Google Gemini (gemini-3-flash-preview). Multimodal: acepta texto o array de parts (texto + imágenes base64).
     * $parts: string (solo texto) o array de partes [ ['text' => '...'], ['inlineData' => ['mimeType'=>'...', 'data'=>'base64']], ... ]
     * Devuelve ['success' => bool, 'texto' => string, 'mensaje' => string].
     */
    private function llamarGemini($systemPrompt, $parts, $maxTokens = 1500)
    {
        $apiKey = defined('GEMINI_API_KEY') ? (string) GEMINI_API_KEY : '';
        if ($apiKey === '') {
            return ['success' => false, 'texto' => '', 'mensaje' => 'No está configurada GEMINI_API_KEY. Guarde la clave en la tabla config_api: php backend/config/set_api_key.php GEMINI_API_KEY "su_clave"'];
        }
        $contentParts = is_array($parts) ? $parts : [['text' => (string) $parts]];
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3-flash-preview:generateContent?key=' . urlencode($apiKey);
        $body = [
            'contents' => [['parts' => $contentParts]],
            'systemInstruction' => ['parts' => [['text' => $systemPrompt]]],
            'generationConfig' => ['maxOutputTokens' => (int) $maxTokens],
        ];
        $payload = json_encode($body);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => defined('OPENAI_SSL_VERIFY') ? (bool) OPENAI_SSL_VERIFY : true,
        ]);
        $resp = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);
        if ($resp === false || $curlErr !== '') {
            return ['success' => false, 'texto' => '', 'mensaje' => 'No se pudo conectar con Gemini. ' . $curlErr];
        }
        $json = json_decode($resp, true);
        $texto = '';
        if (!empty($json['candidates'][0]['content']['parts']) && is_array($json['candidates'][0]['content']['parts'])) {
            foreach ($json['candidates'][0]['content']['parts'] as $part) {
                if (!empty($part['text'])) {
                    $texto .= (string) $part['text'];
                }
            }
            $texto = trim($texto);
        }
        if (!empty($json['promptFeedback']['blockReason']) && $texto === '') {
            return ['success' => false, 'texto' => '', 'mensaje' => 'Gemini bloqueó la respuesta: ' . $json['promptFeedback']['blockReason']];
        }
        if (empty($json['candidates']) && $texto === '' && empty($json['error']['message'])) {
            return ['success' => false, 'texto' => '', 'mensaje' => 'Gemini no devolvió contenido.'];
        }
        if (!empty($json['error']['message'])) {
            return ['success' => false, 'texto' => $texto, 'mensaje' => 'Gemini: ' . $json['error']['message']];
        }
        if ($httpCode >= 400) {
            return ['success' => false, 'texto' => $texto, 'mensaje' => 'Error del servicio (código ' . $httpCode . ').'];
        }
        return ['success' => true, 'texto' => $texto, 'mensaje' => 'OK'];
    }

    /**
     * Wrapper público para usar Gemini desde otros controladores (ej. Api interpretar analíticas).
     * $parts: string o array de partes [ ['text' => '...'], ... ]
     */
    public function callGemini(string $systemPrompt, $parts, int $maxTokens = 1500): array
    {
        return $this->llamarGemini($systemPrompt, $parts, $maxTokens);
    }

    /**
     * API: análisis con IA (Google Gemini). Recibe id_credito e id_ticket, reúne contexto y devuelve predicción.
     * Solo usuarios con permiso al Panel Admin (módulo 19) pueden usar esta función.
     */
    public function analizarIA()
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
        @set_time_limit(90);
        try {
            $modulos = $_SESSION['modulos'] ?? [];
            if (!is_array($modulos) || (!in_array(19, $modulos) && !in_array(27, $modulos))) {
                self::respuestaJSON(['success' => false, 'mensaje' => 'No tiene permiso para usar Analizar con IA.', 'texto' => '']);
                return;
            }
            $raw = file_get_contents('php://input');
            $datos = json_decode($raw, true) ?: [];
            $idCredito = isset($datos['id_credito']) && $datos['id_credito'] !== '' ? (int)$datos['id_credito'] : 0;
            $idTicket = isset($datos['id_ticket']) && $datos['id_ticket'] !== '' ? (int)$datos['id_ticket'] : 0;
            if ($idCredito < 1) {
                self::respuestaJSON(['success' => false, 'mensaje' => 'ID de crédito requerido.', 'texto' => '']);
                return;
            }

            // Flujo de 3 capas: Motor determinístico → Interpretación IA → Verificación IA
            try {
                $pipeline = $this->ejecutarPipelinePrediccion($idCredito, $idTicket);
                $jsonLegacy = $pipeline['json_legacy'];
                self::respuestaJSON([
                    'success' => true,
                    'mensaje' => 'OK',
                    'texto' => json_encode($jsonLegacy, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
                    'json' => $jsonLegacy,
                ]);
                return;
            } catch (\Throwable $pipeEx) {
                // Fallback: análisis local sin IA (mantiene funcionalidad si falla el pipeline)
                $fallback = $this->fallbackAnalizarIA($idCredito, $idTicket);
                self::respuestaJSON([
                    'success' => true,
                    'mensaje' => 'Pipeline no disponible: ' . $pipeEx->getMessage(),
                    'texto' => json_encode($fallback, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
                    'json' => $fallback,
                ]);
                return;
            }
        } catch (\Throwable $e) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'Error al analizar con IA: ' . $e->getMessage(),
                'texto' => '',
                'json' => null,
            ]);
        }
    }

    /**
     * Contexto reducido para analizarIA: top 3 ubicaciones, últimas 8 gestiones,
     * últimos 6 mensajes del chat, evidencias como texto (sin imágenes inline). Evita timeouts.
     */
    private function construirContextoAnalizarIAReducido($idCredito, $idTicket)
    {
        $lineas = [];
        $dir = EmpresaDAO::getConsultaDireccionEstadoCuenta($idCredito);
        $datosDir = ($dir['success'] ?? false) && !empty($dir['datos']) ? $dir['datos'][0] : [];
        $lineas[] = 'ID crédito: ' . $idCredito;
        $lineas[] = 'Domicilio megareporte: ' . ($datosDir['Domicilio_Completo'] ?? '—');

        $ubic = UbicacionDAO::getUbicacionesFiltradasPorIdCredito($idCredito);
        $top = array_slice($ubic['direcciones_resumen'] ?? [], 0, 3);
        $lineas[] = "\nTOP_UBICACIONES (máx 3):";
        foreach ($top as $d) {
            $lineas[] = '- ' . ($d['texto'] ?? '—') . '; visitas: ' . ($d['cantidad_registros'] ?? 0) . '; last_seen: ' . ($d['ultima_fecha'] ?? '—');
        }

        // getAllGestiones devuelve orden DESC (más reciente primero); tomar las 8 más recientes
        $gestiones = GestionesDAO::getAllGestiones($idCredito, '');
        $gestiones = is_array($gestiones) ? array_slice($gestiones, 0, 8) : [];
        $lineas[] = "\nULTIMAS_8_GESTIONES:";
        foreach ($gestiones as $g) {
            $lineas[] = '- ' . ($g['fecha_dispositivo'] ?? $g['fecha_hora'] ?? '') . ' | dir: ' . ($g['direccion_actual'] ?? $g['direccion'] ?? '—') . ' | dictamen: ' . ($g['dictamen_campo'] ?? $g['dictamen_ccc'] ?? '—') . ' | comentarios: ' . substr($g['comentarios_generales'] ?? '—', 0, 80);
        }

        if ($idTicket > 0) {
            $chat = TicketDAO::getChatPorTicket($idTicket);
            $listaChat = isset($chat['datos']) && is_array($chat['datos']) ? array_slice($chat['datos'], -6) : [];
            $lineas[] = "\nBITACORA (últimos 6 mensajes):";
            foreach ($listaChat as $m) {
                $lineas[] = '- [' . ($m['fecha_creacion'] ?? '') . '] ' . ($m['persona_nombre'] ?? '') . ': ' . substr($m['mensaje'] ?? '', 0, 100);
            }
            $evid = TicketDAO::getEvidenciasPorTicket($idTicket, TicketDAO::TIPO_ORIGEN_DICTAMEN_SABUESO);
            $listaEvid = isset($evid['datos']) && is_array($evid['datos']) ? array_slice($evid['datos'], -5) : [];
            $lineas[] = "\nEVIDENCIAS DICTAMEN SABUESO (resumen):";
            foreach ($listaEvid as $e) {
                $lineas[] = '- ' . ($e['fecha_subida'] ?? '') . ' | ' . ($e['nombre_original'] ?? '—');
            }
        }

        return implode("\n", $lineas);
    }

    /**
     * Contexto rico para la interpretación IA: reducido + cumplimiento gestor + analítica pagos + estado cuenta + referencias.
     * Mejora la confiabilidad del resumen y riesgos (pago y gestor).
     */
    private function construirContextoParaInterpretacionIA($idCredito, $idTicket, array $analiticas, $resumenEstadoCuenta)
    {
        $lineas = [trim($this->construirContextoAnalizarIAReducido($idCredito, $idTicket))];
        $lineas[] = "\n=== CUMPLIMIENTO GESTOR ===";
        $cg = $analiticas['cumplimiento_gestor'] ?? [];
        $pct = $cg['porcentaje_cumplimiento'] ?? null;
        if ($pct !== null) {
            $lineas[] = 'Porcentaje cumplimiento: ' . $pct . '%. Visitas dentro de rango: ' . ($cg['visitas_cercanas'] ?? 0) . ', fuera de rango: ' . ($cg['visitas_lejanas'] ?? 0);
            if (!empty($cg['alertas'])) {
                $lineas[] = 'Alertas: ' . implode('; ', array_slice($cg['alertas'], 0, 5));
            }
        } else {
            $lineas[] = 'Sin datos de cumplimiento (sin eventos de ubicación del gestor o sin ubicaciones del cliente).';
        }
        $lineas[] = "\n=== ANALTICA PAGOS ===";
        $ap = $analiticas['analitica_pagos'] ?? [];
        $lineas[] = 'Total pagos (usados en análisis): ' . ($ap['total_pagos'] ?? 0) . '. Patrón: ' . ($ap['patron_pago'] ?? '—') . '. Intervalo promedio (días): ' . ($ap['intervalo_promedio_dias'] ?? '—') . '. Día más frecuente: ' . ($ap['dia_mas_frecuente'] ?? '—');
        $lineas[] = "\n=== ESTADO DE CUENTA (API) ===";
        $lineas[] = $resumenEstadoCuenta !== '' ? $resumenEstadoCuenta : 'No disponible o sin pagos.';
        // Último pago (morosidad/tendencia) y tipo punto de interés (estrategia contacto) para la IA
        try {
            $estadoCuentaCtrl = new \Controllers\EstadoCuenta();
            $resEstado = $estadoCuentaCtrl->api___SPARTA_SECRET_REDACTED__($idCredito, date('Y-m-d'));
            if (!empty($resEstado['ok']) && !empty($resEstado['data']['datosPagos'])) {
                $pagosEc = $resEstado['data']['datosPagos'];
                $conFechaMonto = [];
                foreach ($pagosEc as $p) {
                    $f = $p['fechaDeposito'] ?? $p['fechaRegistro'] ?? $p['fechaValor'] ?? null;
                    if ($f !== null) {
                        $fNorm = is_numeric($f) ? date('Y-m-d', (int) $f) : date('Y-m-d', strtotime($f));
                        $conFechaMonto[] = ['fecha' => $fNorm, 'monto' => $p['montoPago'] ?? $p['monto'] ?? null];
                    }
                }
                if (!empty($conFechaMonto)) {
                    usort($conFechaMonto, function ($a, $b) { return strcmp($b['fecha'], $a['fecha']); });
                    $ultFecha = $conFechaMonto[0]['fecha'];
                    $ultMonto = $conFechaMonto[0]['monto'];
                    $diasDesde = (int) floor((time() - strtotime($ultFecha)) / 86400);
                    $lineas[] = 'Último pago (morosidad/tendencia): fecha ' . $ultFecha . ', monto ' . ($ultMonto !== null && $ultMonto !== '' ? $ultMonto : '—') . ', hace ' . $diasDesde . ' día(s).';
                }
            }
        } catch (\Throwable $e) {
            // Sin último pago en contexto
        }
        $ubicCtx = UbicacionDAO::getUbicacionesFiltradasPorIdCredito($idCredito);
        $dirsCtx = $ubicCtx['direcciones_resumen'] ?? [];
        if (!empty($dirsCtx)) {
            $primeraDir = $dirsCtx[0];
            $tipoPunto = trim((string) ($primeraDir['texto'] ?? '')) !== '' ? (string) $primeraDir['texto'] : (!empty($primeraDir['punto_de_interes']) ? 'Punto de interés' : 'Menos frecuente');
            $lineas[] = 'Punto más visitado (estrategia contacto): ' . $tipoPunto . '.';
        }
        $ref = EmpresaDAO::getConsultaReferenciasEstadoCuenta($idCredito);
        $datosRef = ($ref['success'] ?? false) && !empty($ref['datos']) ? $ref['datos'][0] : [];
        if (!empty($datosRef)) {
            $lineas[] = "\n=== REFERENCIAS / CONTACTO ===";
            $lineas[] = 'Tel. referencia 1: ' . ($datosRef['telefono_referencia1'] ?? '—') . ' (' . ($datosRef['nombre_completo_referencia1'] ?? '—') . ')';
            $lineas[] = 'Tel. referencia 2: ' . ($datosRef['telefono_referencia2'] ?? '—') . ' (' . ($datosRef['nombre_completo_referencia2'] ?? '—') . ')';
        }
        return implode("\n", $lineas);
    }

    /**
     * Construye el texto de contexto completo para analizarIA: crédito, pagos, direcciones,
     * todas las gestiones, bitácora completa, evidencias (fotos y comentarios), ubicaciones del mapa.
     */
    private function construirContextoAnalizarIA($idCredito, $idTicket)
    {
        $lineas = [];

        // --- Datos cliente, megareporte, referencias ---
        $dir = EmpresaDAO::getConsultaDireccionEstadoCuenta($idCredito);
        $ref = EmpresaDAO::getConsultaReferenciasEstadoCuenta($idCredito);
        $datosDir = ($dir['success'] ?? false) && !empty($dir['datos']) ? $dir['datos'][0] : [];
        $datosRef = ($ref['success'] ?? false) && !empty($ref['datos']) ? $ref['datos'][0] : [];
        $todo = array_merge($datosDir ?: [], $datosRef ?: []);
        if (!empty($todo)) {
            $lineas[] = '=== DATOS DEL CRÉDITO Y CLIENTE ===';
            $lineas[] = 'ID crédito: ' . $idCredito;
            $lineas[] = 'Cliente: ' . ($todo['Nombre_cliente'] ?? $todo['nombre_completo'] ?? '—');
            $lineas[] = 'Dirección megareporte: ' . ($todo['Domicilio_Completo'] ?? '—');
            $lineas[] = 'Teléfono referencia 1: ' . ($todo['telefono_referencia1'] ?? '—');
            $lineas[] = 'Teléfono referencia 2: ' . ($todo['telefono_referencia2'] ?? '—');
            $lineas[] = 'Nombre referencia 1: ' . ($todo['nombre_completo_referencia1'] ?? '—');
            $lineas[] = 'Nombre referencia 2: ' . ($todo['nombre_completo_referencia2'] ?? '—');
        }

        // --- Pagos (estado de cuenta desde API externa) ---
        try {
            $estadoCuentaCtrl = new \Controllers\EstadoCuenta();
            $resEstado = $estadoCuentaCtrl->api___SPARTA_SECRET_REDACTED__($idCredito, date('Y-m-d'));
            if (!empty($resEstado['ok']) && !empty($resEstado['data'])) {
                $dataEc = $resEstado['data'];
                $pagos = $dataEc['datosPagos'] ?? [];
                $datosCliente = $dataEc['datosCliente'] ?? [];
                $lineas[] = "\n=== PAGOS (estado de cuenta) ===";
                $lineas[] = 'Total de pagos registrados: ' . count($pagos);
                if (!empty($pagos)) {
                    foreach ($pagos as $i => $p) {
                        $fecha = $p['fechaDeposito'] ?? $p['fechaRegistro'] ?? $p['fechaValor'] ?? '—';
                        $monto = $p['montoPago'] ?? '—';
                        $cuotas = $p['numeroCuotaSemanal'] ?? '—';
                        $lineas[] = '  Pago ' . ($i + 1) . ': fecha ' . $fecha . ', monto ' . $monto . ', cuotas ' . $cuotas;
                    }
                }
                if (!empty($datosCliente)) {
                    $lineas[] = 'Saldo / datos cliente API: ' . json_encode(array_filter([
                        'idCredito' => $datosCliente['idCredito'] ?? null,
                        'nombre' => $datosCliente['nombre'] ?? $datosCliente['nombreCliente'] ?? null,
                        'saldo' => $datosCliente['saldo'] ?? $dataEc['datosSaldos']['saldo'] ?? null,
                    ]));
                }
            }
        } catch (\Throwable $e) {
            $lineas[] = "\n=== PAGOS === No disponible (error al consultar estado de cuenta).";
        }

        // --- Ubicaciones del mapa (maxi app) ---
        $ubic = UbicacionDAO::getUbicacionesFiltradasPorIdCredito($idCredito);
        if (!empty($ubic['direcciones_resumen'])) {
            $lineas[] = "\n=== UBICACIONES DEL MAPA (maxi app, por frecuencia) ===";
            foreach ($ubic['direcciones_resumen'] as $i => $d) {
                $visitas = $d['cantidad_registros'] ?? 0;
                $tipo = !empty($d['punto_de_interes']) ? 'Punto de interés' : 'Menos frecuente';
                $lat = $d['lat'] ?? ''; $lng = $d['lng'] ?? '';
                $texto = ($d['texto'] ?? '') . ' (' . $lat . ', ' . $lng . ')';
                $lineas[] = '  ' . ($i + 1) . '. ' . $tipo . ' — ' . $texto . ' — ' . $visitas . ' visitas';
            }
        } else {
            $lineas[] = "\n=== UBICACIONES DEL MAPA === Sin ubicaciones en maxi app para este crédito.";
        }

        // --- Histórico completo de gestiones (todas, no solo dos) ---
        $gestiones = GestionesDAO::getAllGestiones($idCredito, '');
        $gestiones = is_array($gestiones) ? $gestiones : [];
        if (!empty($gestiones)) {
            $lineas[] = "\n=== HISTÓRICO COMPLETO DE GESTIONES (" . count($gestiones) . " registros) ===";
            foreach ($gestiones as $idx => $g) {
                $lineas[] = '--- Gestión ' . ($idx + 1) . ' [' . ($g['app'] ?? '') . '] ' . ($g['fecha_dispositivo'] ?? $g['fecha_hora'] ?? '') . ' ---';
                $lineas[] = '  Dirección actual: ' . ($g['direccion_actual'] ?? $g['direccion'] ?? '—');
                $lineas[] = '  Dirección geo: ' . ($g['direccion_geo'] ?? $g['geolocalizacion'] ?? '—');
                $lineas[] = '  Teléfono celular: ' . ($g['telefono_celular'] ?? '—');
                $lineas[] = '  Medio contactación CCC: ' . ($g['medio_contactacion_ccc'] ?? '—');
                $lineas[] = '  Dictamen CCC: ' . ($g['dictamen_ccc'] ?? '—');
                $lineas[] = '  Medio contactación campo: ' . ($g['medio_contactacion_campo'] ?? '—');
                $lineas[] = '  Dictamen campo: ' . ($g['dictamen_campo'] ?? '—');
                $lineas[] = '  Promesa pago: ' . ($g['promesa_pago'] ?? '—');
                $lineas[] = '  Motivo atraso: ' . ($g['porque_atraso_pago'] ?? '—');
                $lineas[] = '  Referencia 1: ' . ($g['referencia_personal1'] ?? '') . ' ' . ($g['telefono_referencia1'] ?? '');
                $lineas[] = '  Referencia 2: ' . ($g['referencia_personal2'] ?? '') . ' ' . ($g['telefono_referencia2'] ?? '');
                $lineas[] = '  Comentarios: ' . ($g['comentarios_generales'] ?? '—');
            }
        } else {
            $lineas[] = "\n=== GESTIONES === Sin gestiones para este crédito.";
        }

        // --- Bitácora (todos los mensajes del ticket) ---
        if ($idTicket > 0) {
            $chat = TicketDAO::getChatPorTicket($idTicket);
            $listaChat = isset($chat['datos']) ? $chat['datos'] : [];
            if (!empty($listaChat)) {
                $lineas[] = "\n=== BITCORA / COMENTARIOS DEL TICKET (" . count($listaChat) . " mensajes) ===";
                foreach ($listaChat as $m) {
                    $fecha = $m['fecha_creacion'] ?? '';
                    $lineas[] = '  ' . ($m['persona_nombre'] ?? '') . ' [' . $fecha . ']: ' . ($m['mensaje'] ?? '');
                }
            }
            $evid = TicketDAO::getEvidenciasPorTicket($idTicket, TicketDAO::TIPO_ORIGEN_DICTAMEN_SABUESO);
            $listaEvid = isset($evid['datos']) ? $evid['datos'] : [];
            if (!empty($listaEvid)) {
                $lineas[] = "\n=== EVIDENCIAS DICTAMEN SABUESO (fotos) ===";
                $lineas[] = 'Total: ' . count($listaEvid) . ' foto(s).';
                foreach ($listaEvid as $e) {
                    $lineas[] = '  - Archivo: ' . ($e['nombre_original'] ?? '—') . ' (fecha: ' . ($e['fecha_subida'] ?? '') . ')';
                }
            } else {
                $lineas[] = "\n=== EVIDENCIAS === Sin fotos subidas para este ticket.";
            }
        }

        return implode("\n", $lineas);
    }

    /**
     * Construye contexto reducido solo para resumir ubicaciones: cliente, megareporte y lista de ubicaciones del mapa.
     */
    private function construirContextoSoloUbicaciones($idCredito)
    {
        $lineas = [];

        $dir = EmpresaDAO::getConsultaDireccionEstadoCuenta($idCredito);
        $ref = EmpresaDAO::getConsultaReferenciasEstadoCuenta($idCredito);
        $datosDir = ($dir['success'] ?? false) && !empty($dir['datos']) ? $dir['datos'][0] : [];
        $datosRef = ($ref['success'] ?? false) && !empty($ref['datos']) ? $ref['datos'][0] : [];
        $todo = array_merge($datosDir ?: [], $datosRef ?: []);
        if (!empty($todo)) {
            $lineas[] = '=== DATOS DEL CRÉDITO ===';
            $lineas[] = 'ID crédito: ' . $idCredito;
            $lineas[] = 'Cliente: ' . ($todo['Nombre_cliente'] ?? $todo['nombre_completo'] ?? '—');
            $lineas[] = 'Dirección megareporte: ' . ($todo['Domicilio_Completo'] ?? '—');
        }

        $ubic = UbicacionDAO::getUbicacionesFiltradasPorIdCredito($idCredito);
        if (!empty($ubic['direcciones_resumen'])) {
            $lineas[] = "\n=== UBICACIONES DEL MAPA (maxi app, por frecuencia) ===";
            foreach ($ubic['direcciones_resumen'] as $i => $d) {
                $visitas = $d['cantidad_registros'] ?? 0;
                $tipo = !empty($d['punto_de_interes']) ? 'Punto de interés' : 'Menos frecuente';
                $lat = $d['lat'] ?? ''; $lng = $d['lng'] ?? '';
                $texto = ($d['texto'] ?? '') . ' (' . $lat . ', ' . $lng . ')';
                $lineas[] = '  ' . ($i + 1) . '. ' . $tipo . ' — ' . $texto . ' — ' . $visitas . ' visitas';
            }
        } else {
            $lineas[] = "\n=== UBICACIONES DEL MAPA === Sin ubicaciones en maxi app para este crédito.";
        }

        return implode("\n", $lineas);
    }

    /**
     * Contexto reducido para IA: top 3 ubicaciones + últimas 8 gestiones.
     * Evita payloads gigantes y timeouts en Gemini.
     */
    private function construirContextoSoloUbicacionesReducido($idCredito)
    {
        $lineas = [];
        $dir = EmpresaDAO::getConsultaDireccionEstadoCuenta($idCredito);
        $datosDir = ($dir['success'] ?? false) && !empty($dir['datos']) ? $dir['datos'][0] : [];
        $lineas[] = 'ID: ' . $idCredito;
        $lineas[] = 'Domicilio_reportado: ' . ($datosDir['Domicilio_Completo'] ?? '—');

        $ubic = UbicacionDAO::getUbicacionesFiltradasPorIdCredito($idCredito);
        $top = $ubic['direcciones_resumen'] ?? [];
        $top = array_slice($top, 0, 3);
        $lineas[] = 'TOP_UBICACIONES:';
        foreach ($top as $d) {
            $texto = $d['texto'] ?? '—';
            $visitas = $d['cantidad_registros'] ?? 0;
            $last = $d['ultima_fecha'] ?? '—';
            $latlng = ($d['lat'] ?? '') . ',' . ($d['lng'] ?? '');
            $lineas[] = '- texto: ' . $texto . '; visitas: ' . $visitas . '; last_seen: ' . $last . '; latlng: ' . $latlng;
        }

        // getAllGestiones devuelve orden DESC (más reciente primero); tomar las 8 más recientes
        $gestiones = GestionesDAO::getAllGestiones($idCredito, '');
        $gestiones = is_array($gestiones) ? array_slice($gestiones, 0, 8) : [];
        $lineas[] = 'ULTIMAS_GESTIONES (usa la hora para inferir horario probable):';
        foreach ($gestiones as $g) {
            $fecha = $g['fecha_dispositivo'] ?? $g['fecha_hora'] ?? '';
            $dirAct = $g['direccion_actual'] ?? $g['direccion'] ?? '—';
            $tel = $g['telefono_celular'] ?? '—';
            $dictamen = $g['dictamen_campo'] ?? $g['dictamen_ccc'] ?? '—';
            $lineas[] = '- fecha_hora:' . $fecha . '; dir:' . $dirAct . '; tel:' . $tel . '; dictamen:' . $dictamen;
        }

        return implode("\n", $lineas);
    }

    /** Pesos de fuente para scoring (pagos, gps, gestiones, horario). */
    private const WEIGHT_PAYMENTS = 0.40;
    private const WEIGHT_GPS = 0.35;
    private const WEIGHT_GESTIONES = 0.15;
    private const WEIGHT_HORARIO = 0.10;
    private const PAYMENTS_NORM = 8.0;
    private const GPS_VISITS_NORM = 6.0;
    private const GESTIONES_NORM = 8.0;
    private const GPS_DAYS_PENALTY_30 = 0.5;
    private const GPS_DAYS_PENALTY_90 = 0.2;
    private const SUSPECTED_TEST_CONFIDENCE_FACTOR = 0.6;

    /**
     * Obtiene el número de pagos registrados en estado de cuenta para un crédito.
     */
    private function getPagosCountForCredito($idCredito)
    {
        try {
            $estadoCuentaCtrl = new \Controllers\EstadoCuenta();
            $resEstado = $estadoCuentaCtrl->api___SPARTA_SECRET_REDACTED__($idCredito, date('Y-m-d'));
            if (!empty($resEstado['ok']) && !empty($resEstado['data']['datosPagos'])) {
                return count($resEstado['data']['datosPagos']);
            }
        } catch (\Throwable $e) {
            // ignorar
        }
        return 0;
    }

    /**
     * Obtiene datos de estado de cuenta para el pipeline: fechas de pago reales, array para TemporalPaymentsService y resumen para contexto IA.
     * Una sola llamada a la API para usar en analítica, historial_temporal y contexto.
     *
     * @return array { fechas_pago: string[], pagos_para_temporal: array[], resumen_texto: string, total: int }
     */
    private function getDatosEstadoCuentaParaPipeline($idCredito)
    {
        $out = [
            'fechas_pago'         => [],
            'pagos_para_temporal' => [],
            'resumen_texto'       => '',
            'total'               => 0,
        ];
        try {
            $estadoCuentaCtrl = new \Controllers\EstadoCuenta();
            $resEstado = $estadoCuentaCtrl->api___SPARTA_SECRET_REDACTED__($idCredito, date('Y-m-d'));
            if (empty($resEstado['ok']) || empty($resEstado['data'])) {
                return $out;
            }
            $data = $resEstado['data'];
            $pagos = $data['datosPagos'] ?? [];
            $saldos = $data['datosSaldos'] ?? [];
            $out['total'] = count($pagos);
            $fechas = [];
            foreach ($pagos as $p) {
                $f = $p['fechaDeposito'] ?? $p['fechaRegistro'] ?? $p['fechaValor'] ?? null;
                if ($f) {
                    $fechaNorm = is_numeric($f) ? date('Y-m-d', (int) $f) : date('Y-m-d', strtotime($f));
                    if ($fechaNorm) {
                        $fechas[] = $fechaNorm;
                        $out['pagos_para_temporal'][] = ['fecha' => $fechaNorm];
                    }
                }
            }
            $fechas = array_values(array_unique($fechas));
            rsort($fechas);
            $out['fechas_pago'] = $fechas;
            $saldoStr = '';
            if (!empty($saldos) && isset($saldos['saldo'])) {
                $saldoStr = ' Saldo actual: ' . ($saldos['saldo'] ?? '—');
            }
            $ultima = !empty($fechas) ? $fechas[0] : '—';
            $out['resumen_texto'] = 'Estado de cuenta: ' . $out['total'] . ' pago(s) registrado(s). Última fecha de pago: ' . $ultima . '.' . $saldoStr;
            return $out;
        } catch (\Throwable $e) {
            return $out;
        }
    }

    /**
     * Construye candidatos para scoring: domicilio (primera ubicación + pagos/gestiones), trabajo/otro (resto).
     * Cada candidato: place_type, payments_count, gps_visits, last_gps_days, gestiones_count, horario_score, label.
     */
    private function buildCandidatosUbicacion($idCredito)
    {
        $candidatos = [];
        $paymentsCount = $this->getPagosCountForCredito($idCredito);
        $ubic = UbicacionDAO::getUbicacionesFiltradasPorIdCredito($idCredito);
        $ubic = is_array($ubic) ? $ubic : [];
        $top = array_slice($ubic['direcciones_resumen'] ?? [], 0, 5);
        $gestiones = GestionesDAO::getAllGestiones($idCredito, '');
        $gestiones = is_array($gestiones) ? $gestiones : [];
        $totalGestiones = count($gestiones);
        $gestiones = array_slice($gestiones, 0, 16);

        $horarioScoreGlobal = 0.0;
        if (!empty($gestiones)) {
            $ventanas = ['06-09' => 0, '09-12' => 0, '12-15' => 0, '15-18' => 0, '18-21' => 0, '21-24' => 0];
            foreach ($gestiones as $g) {
                $f = $g['fecha_dispositivo'] ?? $g['fecha_hora'] ?? null;
                if ($f && preg_match('/ (\d{1,2}):/', $f, $m)) {
                    $h = (int) $m[1];
                    if ($h >= 6 && $h < 9) $ventanas['06-09']++;
                    elseif ($h >= 9 && $h < 12) $ventanas['09-12']++;
                    elseif ($h >= 12 && $h < 15) $ventanas['12-15']++;
                    elseif ($h >= 15 && $h < 18) $ventanas['15-18']++;
                    elseif ($h >= 18 && $h < 21) $ventanas['18-21']++;
                    else $ventanas['21-24']++;
                }
            }
            $maxVentana = max($ventanas);
            $horarioScoreGlobal = $maxVentana > 0 ? min(1.0, $maxVentana / 4.0) : 0.5;
        } else {
            $horarioScoreGlobal = 0.5;
        }

        $now = time();
        foreach ($top as $i => $d) {
            $visitas = (int) ($d['cantidad_registros'] ?? 0);
            $ultimaFecha = $d['ultima_fecha'] ?? '';
            $lastGpsDays = 9999;
            if ($ultimaFecha !== '') {
                $ts = is_numeric($ultimaFecha) ? (int) $ultimaFecha : strtotime($ultimaFecha);
                if ($ts) {
                    $lastGpsDays = (int) (($now - $ts) / 86400);
                }
            }
            $placeType = $i === 0 ? 'domicilio' : ($i === 1 ? 'trabajo' : 'otro');
            $candidatos[] = [
                'key' => $i,
                'place_type' => $placeType,
                'label' => $d['texto'] ?? $placeType,
                'payments_count' => $i === 0 ? $paymentsCount : 0,
                'gps_visits' => $visitas,
                'last_gps_days' => $lastGpsDays,
                'gestiones_count' => $i === 0 ? $totalGestiones : 0,
                'horario_score' => $i === 0 ? $horarioScoreGlobal : (min(1.0, $visitas / 4.0) * 0.5),
            ];
        }
        return $candidatos;
    }

    /**
     * Calcula scores de localización por candidato (raw y probabilidad normalizada).
     * Pesos: pagos 0.40, gps 0.35, gestiones 0.15, horario 0.10. Penaliza GPS antiguo (>30 días x0.5, >90 x0.2).
     *
     * @param array $candidates Lista de candidatos con payments_count, gps_visits, last_gps_days, gestiones_count, horario_score
     * @return array [ key => ['raw' => float, 'probability' => float], ... ]
     */
    private function compute_location_scores(array $candidates)
    {
        $w = [
            'payments' => self::WEIGHT_PAYMENTS,
            'gps' => self::WEIGHT_GPS,
            'gestiones' => self::WEIGHT_GESTIONES,
            'horario' => self::WEIGHT_HORARIO,
        ];
        $raw = [];
        foreach ($candidates as $c) {
            $key = $c['key'] ?? count($raw);
            $paymentsScore = min(1.0, (($c['payments_count'] ?? 0) / self::PAYMENTS_NORM));
            $gpsFreqNorm = min(1.0, (($c['gps_visits'] ?? 0) / self::GPS_VISITS_NORM));
            $lastGpsDays = $c['last_gps_days'] ?? 9999;
            $gpsMultiplier = 1.0;
            if ($lastGpsDays > 90) {
                $gpsMultiplier = self::GPS_DAYS_PENALTY_90;
            } elseif ($lastGpsDays > 30) {
                $gpsMultiplier = self::GPS_DAYS_PENALTY_30;
            }
            $gpsScore = $gpsFreqNorm * $gpsMultiplier;
            $gestionesScore = min(1.0, (($c['gestiones_count'] ?? 0) / self::GESTIONES_NORM));
            $horarioScore = (float) ($c['horario_score'] ?? 0);

            $rawScore = $w['payments'] * $paymentsScore + $w['gps'] * $gpsScore
                + $w['gestiones'] * $gestionesScore + $w['horario'] * $horarioScore;
            $raw[$key] = $rawScore;
        }
        $total = array_sum($raw) + 1e-12;
        $out = [];
        foreach ($raw as $key => $r) {
            $out[$key] = ['raw' => $r, 'probability' => $r / $total];
        }
        return $out;
    }

    /**
     * Detecta posible prueba/simulación: palabras clave en notas, >3 gestiones mismo gestor en <2 min, pagos duplicados.
     * Si flag_count >= 2 → suspected_test = true y confidence *= 0.6.
     *
     * @return array ['suspected' => bool, 'confidence_multiplier' => float, 'reasons' => string[]]
     */
    private function detectSuspectedTest($idCredito, $idTicket = 0)
    {
        $reasons = [];
        $flagCount = 0;
        $regexNotes = '/\b(test|prueba|dummy|ejemplo|usuario_testeo)\b/ui';

        $gestiones = GestionesDAO::getAllGestiones($idCredito, '');
        $gestiones = is_array($gestiones) ? array_slice($gestiones, 0, 30) : [];
        foreach ($gestiones as $g) {
            $comentarios = $g['comentarios_generales'] ?? '';
            if (preg_match($regexNotes, $comentarios)) {
                $reasons[] = 'Palabra clave en gestión: ' . substr($comentarios, 0, 60);
                $flagCount++;
                break;
            }
        }
        $usuarioPorFecha = [];
        foreach ($gestiones as $g) {
            $f = $g['fecha_dispositivo'] ?? $g['fecha_hora'] ?? '';
            $usuario = $g['usuario_asignado'] ?? $g['usuario'] ?? '';
            if ($f && $usuario !== '') {
                $ts = is_numeric($f) ? (int) $f : strtotime($f);
                $usuarioPorFecha[] = ['ts' => $ts ?: 0, 'usuario' => $usuario];
            }
        }
        usort($usuarioPorFecha, function ($a, $b) { return $a['ts'] <=> $b['ts']; });
        for ($i = 0; $i < count($usuarioPorFecha) - 2; $i++) {
            $a = $usuarioPorFecha[$i];
            $b = $usuarioPorFecha[$i + 2];
            if ($a['usuario'] === $b['usuario'] && ($b['ts'] - $a['ts']) <= 120) {
                $reasons[] = '3+ gestiones mismo gestor en <2 min';
                $flagCount++;
                break;
            }
        }

        if ($idTicket > 0) {
            $chat = TicketDAO::getChatPorTicket($idTicket);
            $listaChat = isset($chat['datos']) && is_array($chat['datos']) ? $chat['datos'] : [];
            foreach ($listaChat as $m) {
                $msg = $m['mensaje'] ?? '';
                if (preg_match($regexNotes, $msg)) {
                    $reasons[] = 'Palabra clave en bitácora';
                    $flagCount++;
                    break;
                }
            }
        }

        $suspected = $flagCount >= 2;
        $multiplier = $suspected ? self::SUSPECTED_TEST_CONFIDENCE_FACTOR : 1.0;
        return ['suspected' => $suspected, 'confidence_multiplier' => $multiplier, 'reasons' => $reasons];
    }

    /**
     * Construye el JSON de predicción de ubicaciones desde candidatos y scores locales (sin IA).
     * Incluye one_line_summary, overall_confidence, predictions (raw_score, evidence, actions con impact_reduction), missing_data_global, suspected_test.
     */
    private function buildResultadoResumenUbicacionesLocal($idCredito, $idTicket = 0)
    {
        $candidatos = $this->buildCandidatosUbicacion($idCredito);
        if (empty($candidatos)) {
            $test = $this->detectSuspectedTest($idCredito, $idTicket);
            $data = $this->prepararDatosParaMotor($idCredito);
            $analiticas = $this->ejecutarAnaliticasDeterministicas($idCredito, $data);
            return [
                'one_line_summary' => 'Sin ubicaciones GPS para este crédito. Revise megareporte y gestiones.',
                'resumen' => 'Sin ubicaciones GPS.',
                'overall_confidence' => round(0.3 * $test['confidence_multiplier'], 2),
                'predictions' => [],
                'next_steps' => ['Revisar megareporte', 'Revisar historial de gestiones'],
                'missing_data_global' => ['ubicaciones_gps', 'gestiones_recientes'],
                'missing' => ['ubicaciones_gps'],
                'suspected_test' => $test['suspected'],
                'analitica_espacial' => $analiticas['analitica_espacial'],
                'analitica_pagos' => $analiticas['analitica_pagos'],
                'cumplimiento_gestor' => $analiticas['cumplimiento_gestor'],
            ];
        }
        $scores = $this->compute_location_scores($candidatos);
        $test = $this->detectSuspectedTest($idCredito, $idTicket);
        $overallConfidence = 0.75 * $test['confidence_multiplier'];

        $predictions = [];
        foreach ($candidatos as $c) {
            $key = $c['key'];
            $sc = $scores[$key] ?? ['raw' => 0, 'probability' => 0];
            $prob = round($sc['probability'], 2);
            $rawScore = $sc['raw'];
            $evidence = [];
            if (($c['payments_count'] ?? 0) > 0) {
                $evidence[] = 'pagos:' . $c['payments_count'];
            }
            $evidence[] = 'gps_visits:' . ($c['gps_visits'] ?? 0) . ',last_gps_days:' . ($c['last_gps_days'] ?? '—');
            if (($c['gestiones_count'] ?? 0) > 0) {
                $evidence[] = 'gestiones:' . $c['gestiones_count'];
            }
            if (($c['horario_score'] ?? 0) > 0) {
                $evidence[] = 'horario_match:' . round($c['horario_score'] * 100) . '%';
            }
            $actions = [];
            if ($c['place_type'] === 'domicilio' && $prob > 0.3) {
                $actions[] = ['action' => 'visita domiciliaria', 'impact_reduction' => 0.70, 'suggested_time' => '13:00-15:00'];
                $actions[] = ['action' => 'llamada previa', 'impact_reduction' => 0.10, 'suggested_time' => '12:30'];
            } else {
                $actions[] = ['action' => 'verificar historial visitas', 'impact_reduction' => 0.20];
            }
            $predictions[] = [
                'place_type' => $c['place_type'],
                'lugar' => $c['place_type'],
                'probability' => $prob,
                'raw_score' => $rawScore,
                'priority' => count($predictions) + 1,
                'evidence' => $evidence,
                'actions' => $actions,
                'missing_data' => $c['place_type'] === 'domicilio' ? ['numero_exterior', 'foto_fachada'] : [],
                'motivo' => $c['label'],
                'horario_probable' => '—',
                'evidencias' => array_map(function ($e) {
                    return (strpos($e, 'pagos:') === 0) ? 'pagos' : (strpos($e, 'gps') === 0 ? 'gps' : (strpos($e, 'gestiones') === 0 ? 'gestiones' : 'horario'));
                }, $evidence),
            ];
        }
        $totalProb = array_sum(array_column($predictions, 'probability'));
        if ($totalProb > 0.001) {
            foreach ($predictions as &$p) {
                $p['probability'] = round($p['probability'] / $totalProb, 2);
            }
            unset($p);
        }

        $data = $this->prepararDatosParaMotor($idCredito);
        $analiticas = $this->ejecutarAnaliticasDeterministicas($idCredito, $data);
        return [
            'one_line_summary' => 'Domicilio con probabilidad ' . round($predictions[0]['probability'] * 100) . '% respaldado por pagos y gestiones; GPS con penalización por antigüedad.',
            'resumen' => 'Domicilio con probabilidad ' . round($predictions[0]['probability'] * 100) . '% respaldado por pagos y gestiones.',
            'overall_confidence' => round($overallConfidence, 2),
            'predictions' => $predictions,
            'next_steps' => ['Revisar mapa de ubicaciones', 'Confirmar horarios con gestiones'],
            'missing_data_global' => ['numero_exterior', 'confirmacion_ingresos_en_caja'],
            'missing' => ['numero_exterior', 'confirmacion_ingresos_en_caja'],
            'suspected_test' => $test['suspected'],
            'analitica_espacial' => $analiticas['analitica_espacial'],
            'analitica_pagos' => $analiticas['analitica_pagos'],
            'cumplimiento_gestor' => $analiticas['cumplimiento_gestor'],
        ];
    }

    /**
     * Devuelve JSON de fallback cuando la IA no responde o falla (solo ubicaciones).
     * Usa scoring local (buildResultadoResumenUbicacionesLocal) para resultado trazable.
     */
    private function fallbackPrediccionUbicaciones($idCredito, $idTicket = 0)
    {
        return $this->buildResultadoResumenUbicacionesLocal($idCredito, $idTicket);
    }

    /**
     * Fallback para analizarIA cuando la IA no responde (esquema cerebro completo).
     * Usa scoring local para predictions y detectSuspectedTest para confidence/suspected_test.
     */
    private function fallbackAnalizarIA($idCredito, $idTicket = 0)
    {
        $local = $this->buildResultadoResumenUbicacionesLocal($idCredito, $idTicket);
        $test = $this->detectSuspectedTest($idCredito, $idTicket);
        $confidence = 0.65 * $test['confidence_multiplier'];
        $pred = [
            'confianza_analisis' => $confidence,
            'confidence' => $confidence,
            'resumen_ejecutivo' => $local['one_line_summary'] ?? 'Análisis no disponible (IA no respondió). Revise mapa y gestiones manualmente.',
            'summary' => $local['one_line_summary'] ?? 'Análisis no disponible.',
            'perfil_conductual' => 'Patrones inferidos desde gestiones y GPS (sin IA).',
            'behavior_profile' => [
                'patterns' => ['Revisar mapa y gestiones manualmente'],
                'hours_distribution' => (object) [],
            ],
            'estrategia_localizacion' => [
                ['accion' => 'Revisar mapa de ubicaciones', 'impacto_reduccion_incertidumbre' => '50%', 'orden' => 1],
                ['accion' => 'Revisar historial de gestiones', 'impacto_reduccion_incertidumbre' => '30%', 'orden' => 2],
            ],
            'riesgos' => $test['suspected'] ? ['Posible registro de prueba en bitácora (impacto en confianza).'] : [],
            'risks' => $test['suspected'] ? [['risk' => 'registros_prueba_en_bitacora', 'impact' => 0.4, 'evidence' => $test['reasons']]] : [],
            'operational_plan' => [
                ['step' => 'Revisar mapa de ubicaciones', 'priority' => 1, 'expected_uncertainty_reduction' => 0.5, 'time_window' => 'hoy'],
                ['step' => 'Revisar historial de gestiones', 'priority' => 2, 'expected_uncertainty_reduction' => 0.3, 'time_window' => 'hoy'],
            ],
            'predictions' => $local['predictions'],
            'missing_data' => $local['missing_data_global'] ?? ['Confirmar horarios recientes', 'Última ubicación GPS'],
            'next_steps' => $local['next_steps'] ?? ['Revisar mapa de ubicaciones', 'Revisar historial de gestiones'],
            'suspected_test' => $test['suspected'],
        ];
        return $this->normalizarJsonPrediccion($pred, true);
    }

    /**
     * Prepara datos crudos para el motor (contrato: id, etiqueta, cantidad_registros, ultima_fecha; id, fecha, tipo).
     *
     * @return array [ 'pagos_count'=>int, 'ubicaciones'=>[{id, etiqueta, cantidad_registros, ultima_fecha}], 'gestiones'=>[{id, fecha, tipo}] ]
     */
    private function prepararDatosParaMotor($idCredito)
    {
        $pagosCount = $this->getPagosCountForCredito($idCredito);
        $ubic = UbicacionDAO::getUbicacionesFiltradasPorIdCredito($idCredito);
        $ubic = is_array($ubic) ? $ubic : [];
        $ubicaciones = [];
        foreach ($ubic['direcciones_resumen'] ?? [] as $i => $d) {
            $ubicaciones[] = [
                'id' => $d['id'] ?? 'u' . $i,
                'etiqueta' => $d['texto'] ?? $d['etiqueta'] ?? '—',
                'cantidad_registros' => (int) ($d['cantidad_registros'] ?? 0),
                'ultima_fecha' => $d['ultima_fecha'] ?? '—',
            ];
        }
        $gestionesRaw = GestionesDAO::getAllGestiones($idCredito, '');
        $gestionesRaw = is_array($gestionesRaw) ? $gestionesRaw : [];
        $gestiones = [];
        foreach ($gestionesRaw as $i => $g) {
            $gestiones[] = [
                'id' => $g['id'] ?? 'g' . $i,
                'fecha' => $g['fecha_dispositivo'] ?? $g['fecha_hora'] ?? '—',
                'tipo' => $g['dictamen_campo'] ?? $g['dictamen_ccc'] ?? '',
            ];
        }
        return [
            'pagos_count' => $pagosCount,
            'ubicaciones' => $ubicaciones,
            'gestiones'   => $gestiones,
        ];
    }

    /**
     * Ejecuta servicios determinísticos de analítica espacial, pagos y cumplimiento gestor.
     * Sin IA. Prioriza pagos reales del estado de cuenta API si se pasan.
     *
     * @param int $idCredito
     * @param array $data Salida de prepararDatosParaMotor
     * @param array[] $pagosDesdeApi Opcional. Array de [ 'fecha' => 'Y-m-d' ] desde estado de cuenta API.
     * @return array [ analitica_espacial, analitica_pagos, cumplimiento_gestor ]
     */
    private function ejecutarAnaliticasDeterministicas($idCredito, array $data, array $pagosDesdeApi = []): array
    {
        $ubic = UbicacionDAO::getUbicacionesFiltradasPorIdCredito($idCredito);
        $ubic = is_array($ubic) ? $ubic : [];
        $direcciones = $ubic['direcciones_resumen'] ?? [];
        $domicilio = [];
        $ubicacionesUsuario = [];
        // Casa = domicilio megareporte (dirección que el usuario dio al registrarse). Geocodificar si es texto.
        $dirMegareporte = EmpresaDAO::getConsultaDireccionEstadoCuenta($idCredito);
        $domicilioCompleto = ($dirMegareporte['success'] ?? false) && !empty($dirMegareporte['datos'][0]['Domicilio_Completo'])
            ? trim((string) $dirMegareporte['datos'][0]['Domicilio_Completo'])
            : '';
        if ($domicilioCompleto !== '') {
            $geocoding = new GeocodingService();
            $coordsMegareporte = $geocoding->getDomicilioCoordsForCredito($idCredito, $domicilioCompleto);
            if (!empty($coordsMegareporte)) {
                $domicilio = [
                    'id' => 'megareporte',
                    'lat' => (float) $coordsMegareporte['lat'],
                    'lng' => (float) $coordsMegareporte['lng'],
                    'label' => $coordsMegareporte['label'] ?? 'Domicilio megareporte',
                ];
            }
        }
        if (empty($domicilio) && !empty($direcciones)) {
            $primera = $direcciones[0];
            $domicilio = [
                'id' => $primera['id'] ?? 'u0',
                'lat' => (float) ($primera['lat'] ?? $primera['latitud'] ?? 0),
                'lng' => (float) ($primera['lng'] ?? $primera['longitud'] ?? 0),
                'label' => $primera['texto'] ?? 'Domicilio',
            ];
        }
        if (!empty($direcciones)) {
            foreach ($direcciones as $i => $d) {
                $orden = $d['orden'] ?? ($i + 1);
                $texto = trim((string) ($d['texto'] ?? ''));
                $label = $texto !== '' ? 'Ubicación ' . $orden . ': ' . $texto : 'Ubicación ' . $orden;
                $ubicacionesUsuario[] = [
                    'id' => $d['id'] ?? 'u' . $i,
                    'lat' => (float) ($d['lat'] ?? $d['latitud'] ?? 0),
                    'lng' => (float) ($d['lng'] ?? $d['longitud'] ?? 0),
                    'label' => $label,
                    'visitas_count' => (int) ($d['cantidad_registros'] ?? 0),
                    'ultima_fecha' => $d['ultima_fecha'] ?? null,
                ];
            }
        }
        $todasUbicaciones = [];
        if (!empty($domicilio) && isset($domicilio['lat']) && isset($domicilio['lng'])) {
            $todasUbicaciones[] = [
                'id' => $domicilio['id'] ?? 'megareporte',
                'lat' => (float) $domicilio['lat'],
                'lng' => (float) $domicilio['lng'],
                'label' => $domicilio['label'] ?? 'Domicilio megareporte',
            ];
        }
        foreach ($ubicacionesUsuario as $u) {
            $todasUbicaciones[] = [
                'id' => $u['id'],
                'lat' => $u['lat'],
                'lng' => $u['lng'],
                'label' => $u['label'] ?? ('Ubicación ' . $u['id']),
            ];
        }
        $puntosGeo = OfertaCoordenada::getPorIdCredito($idCredito);
        if (!empty($puntosGeo)) {
            foreach ($puntosGeo as $i => $g) {
                $latG = (float) ($g['lat'] ?? $g['latitud'] ?? 0);
                $lngG = (float) ($g['lng'] ?? $g['longitud'] ?? 0);
                if ($latG !== 0.0 || $lngG !== 0.0) {
                    $donde = trim((string) ($g['donde_firma'] ?? ''));
                    $todasUbicaciones[] = [
                        'id' => 'geo_' . $i,
                        'lat' => $latG,
                        'lng' => $lngG,
                        'label' => $donde !== '' ? $donde : 'Donde firma ' . ($i + 1),
                    ];
                }
            }
        }
        $eventosGPS = [];
        $idCliente = UbicacionDAO::getIdClientePorIdCredito($idCredito);
        if ($idCliente !== null) {
            $brutos = UbicacionDAO::getUbicacionesBrutasPorIdCliente($idCliente);
            foreach ($brutos ?? [] as $b) {
                $ts = $b['fecha'] ?? $b['fecha_creacion'] ?? null;
                if ($ts !== null) {
                    $ts = is_numeric($ts) ? (int) $ts : strtotime($ts);
                }
                if ($ts) {
                    $eventosGPS[] = [
                        'lat' => (float) ($b['latitud'] ?? 0),
                        'lng' => (float) ($b['longitud'] ?? 0),
                        'timestamp' => $ts,
                    ];
                }
            }
        }
        $spatial = new SpatialAnalyticsService();
        $distanciasCasa = $spatial->calcularDistanciasCasa($ubicacionesUsuario, $domicilio);
        $ultimaApertura = $spatial->ultimaUbicacionApp($eventosGPS, $domicilio, $ubicacionesUsuario);
        $aperturas5 = $spatial->aperturasUltimosDias($eventosGPS, 5, $ubicacionesUsuario, $domicilio);
        $analitica_espacial = [
            'distancias_a_casa' => $distanciasCasa,
            'ultima_apertura' => $ultimaApertura,
            'aperturas_ultimos_5_dias' => $aperturas5,
        ];
        $pagosParaTemporal = [];
        if (!empty($pagosDesdeApi)) {
            $pagosParaTemporal = $pagosDesdeApi;
        } else {
            foreach ($data['gestiones'] ?? [] as $g) {
                $tipo = (string) ($g['tipo'] ?? '');
                if (stripos($tipo, 'Pago') !== false) {
                    $f = $g['fecha'] ?? null;
                    if ($f) {
                        $pagosParaTemporal[] = ['fecha' => is_numeric($f) ? date('Y-m-d', (int) $f) : date('Y-m-d', strtotime($f))];
                    }
                }
            }
        }
        $temporal = new TemporalPaymentsService();
        $analitica_pagos = $temporal->analizarPagos($pagosParaTemporal);
        $eventosGestor = GestionesDAO::getEventosGestorPorCredito($idCredito);
        $compliance = new GestorComplianceService();
        $cumplimiento_gestor = $compliance->verificarCercaniaGestor($eventosGestor, $todasUbicaciones);
        return [
            'analitica_espacial' => $analitica_espacial,
            'analitica_pagos' => $analitica_pagos,
            'cumplimiento_gestor' => $cumplimiento_gestor,
        ];
    }

    /**
     * Construye historial_temporal para BehaviorPredictionService.
     * Prioriza fechas de pago del estado de cuenta API; si no hay, usa fechas extraídas de gestiones (dictamen Pago).
     *
     * @param array $data Salida de prepararDatosParaMotor
     * @param string[] $fechasPagoApi Fechas Y-m-d desde estado de cuenta API (opcional)
     * @return array [ fechas_pago=>[], gestiones=>[], gps=>[] ]
     */
    private function construirHistorialTemporal(array $data, array $fechasPagoApi = []): array
    {
        $fechas_pago = [];
        if (!empty($fechasPagoApi)) {
            $fechas_pago = array_values(array_unique($fechasPagoApi));
            rsort($fechas_pago);
        }
        if (empty($fechas_pago)) {
            foreach ($data['gestiones'] ?? [] as $g) {
                $tipo = (string) ($g['tipo'] ?? '');
                if (stripos($tipo, 'Pago') !== false) {
                    $f = $g['fecha'] ?? null;
                    if ($f) {
                        $ts = is_numeric($f) ? (int) $f : strtotime($f);
                        if ($ts) {
                            $fechas_pago[] = date('Y-m-d', $ts);
                        }
                    }
                }
            }
            rsort($fechas_pago);
            $fechas_pago = array_values(array_unique($fechas_pago));
        }
        return [
            'fechas_pago' => $fechas_pago,
            'gestiones'   => $data['gestiones'] ?? [],
            'gps'         => $data['ubicaciones'] ?? [],
        ];
    }

    /**
     * Ejecuta el flujo: Motor → (cache) → Interpretación IA → Verificación IA → Predictor → audit.
     * Retorna formato final: predicciones_finales {domicilio,trabajo,otro}, confianza_global, plan_operativo[string], prediccion_intencion, riesgos[string], trazabilidad, verificacion, json_legacy.
     */
    private function ejecutarPipelinePrediccion($idCredito, $idTicket = 0)
    {
        $data = $this->prepararDatosParaMotor($idCredito);

        $estadoCuentaData = $this->getDatosEstadoCuentaParaPipeline($idCredito);
        $pagosDesdeApi = $estadoCuentaData['pagos_para_temporal'];
        $resumenEstadoCuenta = $estadoCuentaData['resumen_texto'];

        $motor = new LocationScoringService();
        $resultadoMotor = $motor->calcularProbabilidadLocalizacion($data);

        $dom = (float) ($resultadoMotor['domicilio'] ?? 0);
        $tra = (float) ($resultadoMotor['trabajo'] ?? 0);
        $otr = (float) ($resultadoMotor['otro'] ?? 0);
        $sum = $dom + $tra + $otr;
        if (abs($sum - 100.0) > 0.01) {
            $otr = round(100.0 - $dom - $tra, 2);
        }
        $prediccionesFinales = ['domicilio' => round($dom, 2), 'trabajo' => round($tra, 2), 'otro' => round($otr, 2)];
        $traz = $resultadoMotor['trazabilidad'] ?? [];
        $motorConf = (float) ($resultadoMotor['motor_confidence'] ?? 50.0);

        $analiticas = $this->ejecutarAnaliticasDeterministicas($idCredito, $data, $pagosDesdeApi);

        $cache = new PipelineCache(null, 86400);
        $cacheKey = PipelineCache::hashInput($data) . '_v2';
        $cached = $cache->get($cacheKey);

        $gemini = function ($systemPrompt, $parts, $maxTokens) {
            return $this->llamarGemini($systemPrompt, $parts, $maxTokens);
        };
        $contextoRico = $this->construirContextoParaInterpretacionIA($idCredito, $idTicket, $analiticas, $resumenEstadoCuenta);

        $prediccion_conductual = null;
        if (is_array($cached) && isset($cached['interpretacion']) && isset($cached['verificacion'])) {
            $interpretacion = $cached['interpretacion'];
            $verificacion = $cached['verificacion'];
            $prediccion_conductual = $cached['prediccion_conductual'] ?? null;
        } else {
            $interpretacionSvc = new IAInterpretationService();
            $interpretacion = $interpretacionSvc->interpretar($resultadoMotor, $gemini, $contextoRico);

            $pagosCount = !empty($estadoCuentaData['total']) ? $estadoCuentaData['total'] : $data['pagos_count'];
            $ubic = UbicacionDAO::getUbicacionesFiltradasPorIdCredito($idCredito);
            $gpsList = [];
            foreach (array_slice($ubic['direcciones_resumen'] ?? [], 0, 10) as $i => $d) {
                $gpsList[] = [
                    'id' => $d['id'] ?? 'u' . $i,
                    'texto' => $d['texto'] ?? '—',
                    'visitas' => (int)($d['cantidad_registros'] ?? 0),
                    'ultima_fecha' => $d['ultima_fecha'] ?? '—',
                ];
            }
            $gestionesFull = GestionesDAO::getAllGestiones($idCredito, '');
            $gestionesList = [];
            foreach (array_slice(is_array($gestionesFull) ? $gestionesFull : [], 0, 10) as $i => $g) {
                $gestionesList[] = [
                    'id' => $g['id'] ?? 'g' . $i,
                    'fecha' => $g['fecha_dispositivo'] ?? $g['fecha_hora'] ?? '—',
                    'tipo' => $g['dictamen_campo'] ?? $g['dictamen_ccc'] ?? '—',
                    'gestor' => $g['usuario_asignado'] ?? $g['usuario'] ?? '—',
                    'dictamen' => $g['dictamen_campo'] ?? $g['dictamen_ccc'] ?? '—',
                    'comentarios' => substr($g['comentarios_generales'] ?? '—', 0, 200),
                ];
            }
            $test = $this->detectSuspectedTest($idCredito, $idTicket);
            $datosReales = [
                'pagos_count' => $pagosCount,
                'gps' => $gpsList,
                'gestiones' => $gestionesList,
                'suspected_test' => $test['suspected'],
                'suspected_test_reasons' => $test['reasons'],
                'cumplimiento_gestor' => $analiticas['cumplimiento_gestor'] ?? [],
            ];
            $verificacionSvc = new IAVerificationService();
            $verificacion = $verificacionSvc->verificar($datosReales, $resultadoMotor, $interpretacion, $gemini);

            $historial_temporal = $this->construirHistorialTemporal($data, $estadoCuentaData['fechas_pago']);
            $predictionSvc = new BehaviorPredictionService();
            try {
                $prediccion_conductual = $predictionSvc->predecirIntencionAcreditado($resultadoMotor, $datosReales, $historial_temporal);
            } catch (\Throwable $e) {
                $prediccion_conductual = [
                    'evento_probable' => 'insuficiente_datos',
                    'confianza_evento' => 0.0,
                    'indicadores' => [],
                    'ventana_tiempo_estimada' => ['desde_horas' => 0, 'hasta_horas' => 168],
                    'explicacion_deterministica' => 'Error en predictor: ' . $e->getMessage(),
                    'evidencias' => [],
                ];
            }
            $verificacion = $verificacionSvc->enriquecerConEvidenciasPredictor($datosReales, $resultadoMotor, $prediccion_conductual, $verificacion);

            $cache->set($cacheKey, [
                'interpretacion' => $interpretacion,
                'verificacion' => $verificacion,
                'prediccion_conductual' => $prediccion_conductual,
            ]);

            $responseHash = md5(json_encode($interpretacion, JSON_UNESCAPED_UNICODE) . json_encode($verificacion, JSON_UNESCAPED_UNICODE));
            $audit = new LocationAuditLogger();
            $audit->log(
                $cacheKey,
                ['domicilio' => $dom, 'trabajo' => $tra, 'otro' => $otr, 'motor_confidence' => $motorConf],
                substr(mb_substr($contextoRico, 0, 200) . ' ' . ($interpretacion['resumen'] ?? ''), 0, 500),
                $responseHash,
                [
                    'veracity_score' => $verificacion['veracity_score'] ?? 0,
                    'suspected_test' => $verificacion['suspected_test'] ?? false,
                    'prediccion_conductual' => [
                        'evento_probable' => $prediccion_conductual['evento_probable'] ?? '',
                        'confianza_evento' => $prediccion_conductual['confianza_evento'] ?? 0,
                        'evidencias' => $prediccion_conductual['evidencias'] ?? [],
                    ],
                ]
            );
        }

        if ($prediccion_conductual === null) {
            $historial_temporal = $this->construirHistorialTemporal($data, $estadoCuentaData['fechas_pago']);
            $pagosCount = !empty($estadoCuentaData['total']) ? $estadoCuentaData['total'] : $data['pagos_count'];
            $ubic = UbicacionDAO::getUbicacionesFiltradasPorIdCredito($idCredito);
            $gpsList = [];
            foreach (array_slice($ubic['direcciones_resumen'] ?? [], 0, 10) as $i => $d) {
                $gpsList[] = ['id' => $d['id'] ?? 'u' . $i, 'texto' => $d['texto'] ?? '—', 'visitas' => (int)($d['cantidad_registros'] ?? 0), 'ultima_fecha' => $d['ultima_fecha'] ?? '—'];
            }
            $gestionesFull = GestionesDAO::getAllGestiones($idCredito, '');
            $gestionesList = [];
            foreach (array_slice(is_array($gestionesFull) ? $gestionesFull : [], 0, 10) as $i => $g) {
                $gestionesList[] = ['id' => $g['id'] ?? 'g' . $i, 'fecha' => $g['fecha_dispositivo'] ?? $g['fecha_hora'] ?? '—', 'tipo' => $g['dictamen_campo'] ?? $g['dictamen_ccc'] ?? '—'];
            }
            $datosReales = [
                'pagos_count' => $pagosCount,
                'gps' => $gpsList,
                'gestiones' => $gestionesList,
                'suspected_test' => false,
                'suspected_test_reasons' => [],
                'cumplimiento_gestor' => $analiticas['cumplimiento_gestor'] ?? [],
            ];
            $predictionSvc = new BehaviorPredictionService();
            try {
                $prediccion_conductual = $predictionSvc->predecirIntencionAcreditado($resultadoMotor, $datosReales, $historial_temporal);
            } catch (\Throwable $e) {
                $prediccion_conductual = [
                    'evento_probable' => 'insuficiente_datos',
                    'confianza_evento' => 0.0,
                    'indicadores' => [],
                    'ventana_tiempo_estimada' => ['desde_horas' => 0, 'hasta_horas' => 168],
                    'explicacion_deterministica' => 'Error: ' . $e->getMessage(),
                    'evidencias' => [],
                ];
            }
        }

        $confianzaGlobal = (int) ($verificacion['veracity_score'] ?? 70);
        if ($verificacion['suspected_test'] ?? false) {
            $confianzaGlobal = (int) round($confianzaGlobal * self::SUSPECTED_TEST_CONFIDENCE_FACTOR);
        }
        $confianzaGlobal = max(0, min(100, $confianzaGlobal));

        $planOperativo = array_values(array_map('strval', $interpretacion['acciones_recomendadas'] ?? []));
        if (empty($planOperativo)) {
            $planOperativo = ['Revisar mapa de ubicaciones', 'Revisar historial de gestiones'];
        }

        $riesgosStrings = array_values(array_map('strval', $interpretacion['riesgos_detectados'] ?? []));
        foreach ($verificacion['claims_no_soportados'] ?? [] as $c) {
            $riesgosStrings[] = 'claim_sin_evidencia: ' . (is_string($c) ? $c : json_encode($c, JSON_UNESCAPED_UNICODE));
        }

        $prediccionIntencion = $interpretacion['prediccion_intencion'] ?? ['accion' => 'Revisar mapa y gestiones', 'evidencia' => [], 'nota' => ''];
        if (!isset($prediccionIntencion['evidencia']) || !is_array($prediccionIntencion['evidencia'])) {
            $prediccionIntencion['evidencia'] = array_slice(array_map(function ($c) { return (string)($c['id'] ?? $c['key'] ?? ''); }, $traz['candidatos'] ?? []), 0, 3);
        }
        if (!empty($prediccion_conductual['evidencias'])) {
            $prediccionIntencion['evidencia'] = array_values(array_unique(array_merge(
                $prediccionIntencion['evidencia'],
                array_slice($prediccion_conductual['evidencias'], 0, 5)
            )));
        }
        if (!empty($prediccion_conductual['explicacion_deterministica'])) {
            $prediccionIntencion['nota'] = trim(($prediccionIntencion['nota'] ?? '') . ' ' . $prediccion_conductual['explicacion_deterministica']);
        }

        $verificacionOut = [
            'veracity_score' => (int) ($verificacion['veracity_score'] ?? 70),
            'suspected_test' => (bool) ($verificacion['suspected_test'] ?? false),
            'evidencias_validadas' => array_values($verificacion['evidencias_validadas'] ?? []),
            'claims_no_soportados' => array_values($verificacion['claims_no_soportados'] ?? []),
        ];

        $trazabilidad = [
            'motor' => ['domicilio' => $dom, 'trabajo' => $tra, 'otro' => $otr, 'motor_confidence' => $motorConf, 'trazabilidad' => $traz],
            'interpretacion_ok' => $interpretacion['success'] ?? false,
            'verificacion_ok' => $verificacion['success'] ?? false,
        ];

        $predictionsLegacy = [
            ['place_type' => 'domicilio', 'lugar' => 'domicilio', 'probability' => $dom / 100, 'probabilidad' => $dom / 100, 'priority' => 1, 'motivo' => $traz['candidatos'][0]['label'] ?? 'domicilio', 'horario_probable' => '—', 'evidence' => [], 'actions' => []],
            ['place_type' => 'trabajo', 'lugar' => 'trabajo', 'probability' => $tra / 100, 'probabilidad' => $tra / 100, 'priority' => 2, 'motivo' => $traz['candidatos'][1]['label'] ?? 'trabajo', 'horario_probable' => '—', 'evidence' => [], 'actions' => []],
            ['place_type' => 'otro', 'lugar' => 'otro', 'probability' => $otr / 100, 'probabilidad' => $otr / 100, 'priority' => 3, 'motivo' => 'otro', 'horario_probable' => '—', 'evidence' => [], 'actions' => []],
        ];
        $planLegacy = [];
        foreach ($planOperativo as $i => $step) {
            $planLegacy[] = ['step' => $step, 'priority' => $i + 1, 'expected_uncertainty_reduction' => 0.5, 'time_window' => ''];
        }
        $riesgosLegacy = array_map(function ($r) { return ['risk' => $r, 'impact' => 0.3, 'evidence' => []]; }, $riesgosStrings);
        // Asegurar que prediccion_conductual tenga siempre evento_probable y explicacion_deterministica para el modal
        $pcLegacy = is_array($prediccion_conductual) ? $prediccion_conductual : [];
        $pcLegacy['evento_probable'] = trim((string) ($pcLegacy['evento_probable'] ?? ''));
        $pcLegacy['explicacion_deterministica'] = trim((string) ($pcLegacy['explicacion_deterministica'] ?? ''));
        if ($pcLegacy['evento_probable'] === '') {
            $pcLegacy['evento_probable'] = 'Sin predicción específica; revisar historial y gestiones.';
        }
        if ($pcLegacy['explicacion_deterministica'] === '') {
            $pcLegacy['explicacion_deterministica'] = 'Evaluación basada en datos disponibles (pagos, ubicaciones y cumplimiento del gestor).';
        }
        $pcLegacy['confianza_evento'] = isset($pcLegacy['confianza_evento']) ? (float) $pcLegacy['confianza_evento'] : 0.0;
        $jsonLegacy = [
            'confianza_analisis' => $confianzaGlobal / 100.0,
            'confidence' => $confianzaGlobal / 100.0,
            'resumen_ejecutivo' => $interpretacion['resumen'] ?? '',
            'summary' => $interpretacion['resumen'] ?? '',
            'perfil_conductual' => is_array($interpretacion['patrones_conductuales'] ?? null) ? implode('; ', $interpretacion['patrones_conductuales']) : '',
            'behavior_profile' => ['patterns' => $interpretacion['patrones_conductuales'] ?? [], 'hours_distribution' => (object)[]],
            'predictions' => $predictionsLegacy,
            'operational_plan' => $planLegacy,
            'estrategia_localizacion' => array_map(function ($s, $i) { return ['accion' => $s, 'orden' => $i + 1, 'impacto_reduccion_incertidumbre' => '50%']; }, $planOperativo, array_keys($planOperativo)),
            'risks' => $riesgosLegacy,
            'riesgos' => $riesgosStrings,
            'missing_data' => $verificacion['claims_no_soportados'] ?? [],
            'next_steps' => $planOperativo,
            'suspected_test' => $verificacion['suspected_test'] ?? false,
            'prediccion_conductual' => $pcLegacy,
            'analitica_espacial' => $analiticas['analitica_espacial'],
            'analitica_pagos' => $analiticas['analitica_pagos'],
            'cumplimiento_gestor' => $analiticas['cumplimiento_gestor'],
        ];

        return [
            'predicciones_finales'   => $prediccionesFinales,
            'confianza_global'       => $confianzaGlobal / 100.0,
            'plan_operativo'         => $planOperativo,
            'prediccion_intencion'   => $prediccionIntencion,
            'prediccion_conductual' => $prediccion_conductual,
            'riesgos'                => $riesgosStrings,
            'trazabilidad'           => $trazabilidad,
            'verificacion'           => $verificacionOut,
            'analitica_espacial'     => $analiticas['analitica_espacial'],
            'analitica_pagos'        => $analiticas['analitica_pagos'],
            'cumplimiento_gestor'    => $analiticas['cumplimiento_gestor'],
            'json_legacy'            => $this->normalizarJsonPrediccion($jsonLegacy, true),
        ];
    }

    /**
     * API: resumir solo ubicaciones con IA (Gemini). Recibe id_credito.
     * Devuelve JSON estructurado: one_line_summary, predictions, next_steps. Máx 1024 tokens.
     * Solo usuarios con permiso al Panel Admin (módulo 19) pueden usar esta función.
     */
    public function resumirUbicacionesIA()
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
        @set_time_limit(90);
        try {
            $modulos = $_SESSION['modulos'] ?? [];
            if (!is_array($modulos) || (!in_array(19, $modulos) && !in_array(27, $modulos))) {
                self::respuestaJSON(['success' => false, 'mensaje' => 'No tiene permiso para usar Resumir ubicaciones con IA.', 'texto' => '', 'json' => null]);
                return;
            }
            $raw = file_get_contents('php://input');
            $datos = json_decode($raw, true) ?: [];
            $idCredito = isset($datos['id_credito']) && $datos['id_credito'] !== '' ? (int)$datos['id_credito'] : 0;
            if ($idCredito < 1) {
                self::respuestaJSON(['success' => false, 'mensaje' => 'ID de crédito requerido.', 'texto' => '', 'json' => null]);
                return;
            }

            $localResult = $this->buildResultadoResumenUbicacionesLocal($idCredito, 0);
        $contexto = $this->construirContextoSoloUbicacionesReducido($idCredito);
        $promptSistema = 'Eres un motor analítico que calcula probabilidades de localización. RESPONDE SOLO JSON. No incluyas texto adicional. Usa los pesos de fuente: pagos 0.40, gps 0.35, gestiones 0.15, horario 0.10. Penaliza fuentes antiguas (>30 días -> x0.5, >90 días -> x0.2). Detecta posibles \'prueba\' en notas y marca suspected_test=true si corresponde. Devuelve el campo \'evidence\' con evidencia por candidato.';
        $promptUsuario = "CONTEXT:\n" . $contexto . "\n\nINSTRUCCIONES:\n"
            . "Devuelve un JSON con:\n"
            . "{\n"
            . "  \"one_line_summary\": \"<máx 120 chars>\",\n"
            . "  \"overall_confidence\": 0-1,\n"
            . "  \"predictions\": [\n"
            . "    { \"place_type\": \"domicilio|trabajo|otro\", \"probability\": 0-1, \"raw_score\": float, \"priority\": 1..N, \"evidence\": [\"pagos:8\",\"gps:5,last_days:21\",\"gestiones:8\",\"horario:13-15\"], \"actions\": [{\"action\":\"visita domiciliaria\",\"impact_reduction\":0.70,\"suggested_time\":\"13:00-15:00\"}], \"missing_data\":[\"numero_exterior\"] }\n"
            . "  ],\n"
            . "  \"missing_data_global\": [],\n"
            . "  \"suspected_test\": true|false\n"
            . "}\n";

        $resultado = $this->llamarGemini($promptSistema, [['text' => $promptUsuario]], 2048);
        if (!$resultado['success']) {
            self::respuestaJSON([
                'success' => true,
                'mensaje' => $resultado['mensaje'],
                'texto' => json_encode($localResult, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
                'json' => $localResult,
            ]);
            return;
        }

        $texto = trim($resultado['texto']);
        $texto = preg_replace('/^```(?:json)?\s*/i', '', $texto);
        $texto = preg_replace('/\s*```\s*$/i', '', $texto);
        $json = json_decode($texto, true);
        if (!is_array($json)) {
            self::respuestaJSON([
                'success' => true,
                'mensaje' => 'La IA no devolvió JSON válido.',
                'texto' => $resultado['texto'],
                'json' => $localResult,
            ]);
            return;
        }
        if (!empty($json['error']) && $json['error'] === 'INSUFFICIENT_DATA') {
            $merged = $this->mergeResumenUbicacionesIALocal($localResult, $json);
            self::respuestaJSON(['success' => true, 'mensaje' => 'OK', 'texto' => $texto, 'json' => $merged]);
            return;
        }
        $merged = $this->mergeResumenUbicacionesIALocal($localResult, $json);
        self::respuestaJSON(['success' => true, 'mensaje' => 'OK', 'texto' => $texto, 'json' => $merged]);
        } catch (\Throwable $e) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'Error al resumir ubicaciones: ' . $e->getMessage(),
                'texto' => '',
                'json' => null,
            ]);
        }
    }

    /**
     * Fusiona resultado local (probabilidades calculadas, evidence) con JSON de IA (summary, next_steps, suspected_test).
     * Prioriza: probabilities y raw_score del resultado local; one_line_summary, next_steps y missing_data_global de IA si vienen.
     */
    private function mergeResumenUbicacionesIALocal(array $local, array $ia)
    {
        $out = [
            'one_line_summary' => $ia['one_line_summary'] ?? $local['one_line_summary'] ?? $local['resumen'] ?? '',
            'resumen' => $ia['one_line_summary'] ?? $local['resumen'] ?? '',
            'overall_confidence' => isset($ia['overall_confidence']) ? (float) $ia['overall_confidence'] : ($local['overall_confidence'] ?? 0.75),
            'predictions' => $local['predictions'],
            'next_steps' => !empty($ia['next_steps']) && is_array($ia['next_steps']) ? $ia['next_steps'] : ($local['next_steps'] ?? []),
            'missing_data_global' => !empty($ia['missing_data_global']) && is_array($ia['missing_data_global']) ? $ia['missing_data_global'] : ($local['missing_data_global'] ?? []),
            'missing' => $local['missing'] ?? [],
            'suspected_test' => isset($ia['suspected_test']) ? (bool) $ia['suspected_test'] : ($local['suspected_test'] ?? false),
        ];
        if ($out['suspected_test'] && $out['overall_confidence'] > 0.5) {
            $out['overall_confidence'] = round($out['overall_confidence'] * self::SUSPECTED_TEST_CONFIDENCE_FACTOR, 2);
        }
        if (!empty($ia['predictions']) && is_array($ia['predictions']) && count($ia['predictions']) === count($out['predictions'])) {
            foreach ($out['predictions'] as $i => &$p) {
                $pi = $ia['predictions'][$i] ?? [];
                if (!empty($pi['evidence'])) {
                    $p['evidence'] = is_array($pi['evidence']) ? $pi['evidence'] : [$pi['evidence']];
                }
                if (!empty($pi['actions']) && is_array($pi['actions'])) {
                    $p['actions'] = $pi['actions'];
                }
                if (isset($pi['missing_data']) && is_array($pi['missing_data'])) {
                    $p['missing_data'] = $pi['missing_data'];
                }
            }
            unset($p);
        }
        if (isset($local['analitica_espacial'])) {
            $out['analitica_espacial'] = $local['analitica_espacial'];
        }
        if (isset($local['analitica_pagos'])) {
            $out['analitica_pagos'] = $local['analitica_pagos'];
        }
        if (isset($local['cumplimiento_gestor'])) {
            $out['cumplimiento_gestor'] = $local['cumplimiento_gestor'];
        }
        return $this->normalizarJsonPrediccion($out, false);
    }

    /**
     * Normaliza el JSON de predicción: esquema unificado, arrays, probabilidades suman 1.0.
     * @param array $json Respuesta de la IA
     * @param bool $esAnalizarIA true = esquema analizarIA (confianza_analisis, estrategia, riesgos)
     */
    private function normalizarJsonPrediccion(array $json, $esAnalizarIA = false)
    {
        if (!empty($json['predictions']) && is_array($json['predictions'])) {
            $probs = [];
            foreach ($json['predictions'] as $i => &$p) {
                $p['lugar'] = $p['lugar'] ?? $p['place_type'] ?? 'otro';
                $p['place_type'] = $p['place_type'] ?? $p['lugar'];
                $p['probabilidad'] = isset($p['probabilidad']) ? (float) $p['probabilidad'] : (isset($p['probability']) ? (float) $p['probability'] : (isset($p['confidence']) ? (float) $p['confidence'] : 0.33));
                $p['probability'] = $p['probabilidad'];
                if (isset($p['raw_score'])) {
                    $p['raw_score'] = (float) $p['raw_score'];
                }
                $p['prioridad'] = $p['prioridad'] ?? $p['priority'] ?? $i + 1;
                $p['motivo'] = $p['motivo'] ?? $p['reason'] ?? '';
                $p['horario_probable'] = $p['horario_probable'] ?? '—';
                $ev = $p['evidencias'] ?? $p['evidence'] ?? [];
                $p['evidencias'] = is_array($ev) ? $ev : [ (string) $ev ];
                $p['evidence'] = $p['evidencias'];
                $acciones = $p['acciones'] ?? $p['actions'] ?? [];
                $p['acciones'] = is_array($acciones) ? $acciones : [ (string) $acciones ];
                $p['actions'] = $p['acciones'];
                $probs[] = $p['probabilidad'];
            }
            unset($p);
            $sum = array_sum($probs);
            if ($sum > 0.001) {
                foreach ($json['predictions'] as &$p) {
                    $p['probabilidad'] = round($p['probabilidad'] / $sum, 2);
                }
                unset($p);
            }
        }
        $json['resumen'] = $json['resumen'] ?? $json['one_line_summary'] ?? '';
        $json['one_line_summary'] = $json['one_line_summary'] ?? $json['resumen'];
        if (isset($json['overall_confidence'])) {
            $json['overall_confidence'] = (float) $json['overall_confidence'];
        }
        if (isset($json['suspected_test'])) {
            $json['suspected_test'] = (bool) $json['suspected_test'];
        }
        if (isset($json['missing_data_global']) && !is_array($json['missing_data_global'])) {
            $json['missing_data_global'] = $json['missing_data_global'] === '' || $json['missing_data_global'] === null ? [] : [ (string) $json['missing_data_global'] ];
        }
        if (!empty($json['next_steps']) && !is_array($json['next_steps'])) {
            $json['next_steps'] = [ (string) $json['next_steps'] ];
        }
        $json['missing'] = $json['missing'] ?? $json['missing_data_global'] ?? [];
        if (!is_array($json['missing'])) {
            $json['missing'] = $json['missing'] === '' || $json['missing'] === null ? [] : [ (string) $json['missing'] ];
        }
        if ($esAnalizarIA) {
            $json['confianza_analisis'] = isset($json['confianza_analisis']) ? (float) $json['confianza_analisis'] : (isset($json['confidence']) ? (float) $json['confidence'] : 0.5);
            $json['confidence'] = $json['confianza_analisis'];
            $json['resumen_ejecutivo'] = $json['resumen_ejecutivo'] ?? $json['summary'] ?? $json['resumen'] ?? '';
            $json['summary'] = $json['resumen_ejecutivo'];
            $json['perfil_conductual'] = $json['perfil_conductual'] ?? '';
            $json['behavior_profile'] = $json['behavior_profile'] ?? ['patterns' => [], 'hours_distribution' => (object) []];
            if (!is_array($json['behavior_profile'])) {
                $json['behavior_profile'] = ['patterns' => [], 'hours_distribution' => (object) []];
            }
            $json['estrategia_localizacion'] = isset($json['estrategia_localizacion']) && is_array($json['estrategia_localizacion']) ? $json['estrategia_localizacion'] : [];
            $json['riesgos'] = isset($json['riesgos']) && is_array($json['riesgos']) ? $json['riesgos'] : [];
            if (!empty($json['risks']) && is_array($json['risks'])) {
                foreach ($json['risks'] as &$r) {
                    if (!isset($r['risk'])) {
                        $r = ['risk' => is_string($r) ? $r : '', 'impact' => 0.3, 'evidence' => []];
                    }
                    $r['impact'] = isset($r['impact']) ? (float) $r['impact'] : 0.3;
                    $r['evidence'] = isset($r['evidence']) && is_array($r['evidence']) ? $r['evidence'] : [];
                }
                unset($r);
            } else {
                $json['risks'] = [];
            }
            $json['operational_plan'] = isset($json['operational_plan']) && is_array($json['operational_plan']) ? $json['operational_plan'] : [];
            foreach ($json['operational_plan'] as &$op) {
                $op['expected_uncertainty_reduction'] = isset($op['expected_uncertainty_reduction']) ? (float) $op['expected_uncertainty_reduction'] : 0.5;
                $op['time_window'] = $op['time_window'] ?? $op['estimated_time_window'] ?? '';
            }
            unset($op);
            $json['missing_data'] = $json['missing_data'] ?? $json['missing'] ?? [];
            if (!is_array($json['missing_data'])) {
                $json['missing_data'] = $json['missing_data'] === '' || $json['missing_data'] === null ? [] : [ (string) $json['missing_data'] ];
            }
            $json['suspected_test'] = isset($json['suspected_test']) ? (bool) $json['suspected_test'] : false;
        }
        return $json;
    }

    /**
     * API: resumir histórico de gestiones con IA (Gemini). Recibe id_credito.
     * Usa las últimas 10 gestiones o las del último mes. Solo usuarios con permiso (módulo 19).
     */
    public function resumirGestionesIA()
    {
        $modulos = $_SESSION['modulos'] ?? [];
        if (!is_array($modulos) || (!in_array(19, $modulos) && !in_array(27, $modulos))) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'No tiene permiso para usar Resumen con IA.', 'texto' => '']);
            return;
        }
        $raw = file_get_contents('php://input');
        $datos = json_decode($raw, true) ?: [];
        $idCredito = isset($datos['id_credito']) && $datos['id_credito'] !== '' ? (int)$datos['id_credito'] : 0;
        if ($idCredito < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'ID de crédito requerido.', 'texto' => '']);
            return;
        }

        $gestiones = GestionesDAO::getAllGestiones($idCredito, '');
        $gestiones = is_array($gestiones) ? $gestiones : [];
        $haceUnMes = date('Y-m-d', strtotime('-1 month'));
        $delUltimoMes = array_filter($gestiones, function ($g) use ($haceUnMes) {
            $f = $g['fecha_dispositivo'] ?? $g['fecha_hora'] ?? '';
            return $f && substr($f, 0, 10) >= $haceUnMes;
        });
        $usar = count($delUltimoMes) >= 3 ? array_values($delUltimoMes) : array_slice($gestiones, -10);
        $usar = array_slice($usar, -10);

        $lineas = [];
        $lineas[] = 'ID crédito: ' . $idCredito;
        if (empty($usar)) {
            $lineas[] = 'Sin gestiones para este crédito (últimas 10 o último mes).';
        } else {
            $lineas[] = "\n=== HISTÓRICO DE GESTIONES (últimas " . count($usar) . ") ===";
            foreach ($usar as $i => $g) {
                $lineas[] = '--- Gestión ' . ($i + 1) . ' [' . ($g['app'] ?? '') . '] ' . ($g['fecha_dispositivo'] ?? $g['fecha_hora'] ?? '') . ' ---';
                $lineas[] = '  Contactación CCC: ' . ($g['medio_contactacion_ccc'] ?? '—') . ' · Dictamen: ' . ($g['dictamen_ccc'] ?? '—');
                $lineas[] = '  Contactación campo: ' . ($g['medio_contactacion_campo'] ?? '—') . ' · Dictamen: ' . ($g['dictamen_campo'] ?? '—');
                $lineas[] = '  Promesa pago: ' . ($g['promesa_pago'] ?? '—');
                $lineas[] = '  Motivo atraso: ' . ($g['porque_atraso_pago'] ?? '—');
                $lineas[] = '  Comentarios: ' . ($g['comentarios_generales'] ?? '—');
            }
        }
        $contexto = implode("\n", $lineas);

        $promptSistema = 'Eres un auditor experto en cobranza de campo y control operativo. Evalúas la conducta del gestor, la validez de las ubicaciones GPS y la evidencia de recuperaciones reales (pagos). Tu salida es SOLO análisis y conclusión: no sugieras acciones, no recomiendes qué hacer, no des pasos a seguir.';
        $promptUsuario = "Analiza el desempeño del GESTOR con base en el siguiente historial de gestiones, ubicaciones y comentarios:\n\n"
            . $contexto
            . "\n\nTAREA (IMPORTANTE):\n"
            . "Genera **ÚNICAMENTE un resumen ejecutivo de 2 a 4 párrafos** que contenga solo **análisis y una breve Conclusión**.\n\n"
            . "**Incluye de forma clara:**\n"
            . "- Si el gestor efectivamente localizó al cliente (basado en ubicaciones).\n"
            . "- La conducta general del gestor (gestión real, simulada, administrativa, consistente o irregular).\n"
            . "- Énfasis en recuperaciones: cuando el Dictamen CCC indique pago o en comentarios aparezcan \"pago recibido\", \"abonó\", \"liquidó\", etc.\n"
            . "- Coherencia entre ubicación, comentarios y Dictamen CCC (si el pago es consistente con visita real).\n\n"
            . "**Al final** escribe una sola línea: **Conclusión:** seguida de una oración que resuma la confiabilidad de los reportes y la correlación con ingresos, si aplica.\n\n"
            . "**PROHIBIDO:** No escribas recomendaciones, no digas \"Se recomienda\", \"auditoría\", \"validar\", \"debe\". Solo análisis descriptivo y conclusión.\n\n"
            . "Resalta en **negritas** **PAGO RECIBIDO**, **RECUPERACIÓN CONFIRMADA** e inconsistencias. Tono ejecutivo y directo.";

        $resultado = $this->llamarGemini($promptSistema, $promptUsuario, 4096);
        if (!$resultado['success']) {
            self::respuestaJSON(['success' => false, 'mensaje' => $resultado['mensaje'], 'texto' => $resultado['texto']]);
            return;
        }
        self::respuestaJSON(['success' => true, 'mensaje' => 'OK', 'texto' => $resultado['texto']]);
    }

    /**
     * API: personas del departamento Sabueso (id 5) para asignar ticket.
     */
    public function getPersonasSabueso()
    {
        $resultado = TicketDAO::getPersonasDepartamentoSabueso();
        $datos = isset($resultado['datos']) ? $resultado['datos'] : [];
        self::respuestaJSON([
            'success' => $resultado['success'] ?? false,
            'mensaje' => $resultado['mensaje'] ?? '',
            'datos'   => $datos
        ]);
    }

    /**
     * Personas de máximo rango (jefes organigrama Sabueso) por segmento Campo 1–7 u 8–21.
     * Body JSON: { "campo": "1_7" | "8_21" }
     */
    public function getPersonasSabuesoJefesPorCampo()
    {
        $raw = file_get_contents('php://input');
        $body = json_decode($raw, true) ?: [];
        $campo = isset($body['campo']) ? (string)$body['campo'] : '';
        $resultado = TicketDAO::getPersonasJefesSabuesoPorCampoMorosidad($campo);
        $datos = isset($resultado['datos']) ? $resultado['datos'] : [];
        self::respuestaJSON([
            'success' => $resultado['success'] ?? false,
            'mensaje' => $resultado['mensaje'] ?? '',
            'datos'   => $datos,
        ]);
    }

    /**
     * API: mensajes del chat (bitácora) por ticket.
     */
    public function getChatTicket()
    {
        $raw = file_get_contents('php://input');
        $datos = json_decode($raw, true) ?: [];
        $idTicket = (int)($datos['id_ticket'] ?? 0);
        if ($idTicket < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'ID de ticket requerido.', 'datos' => []]);
            return;
        }
        $resultado = TicketDAO::getChatPorTicket($idTicket);
        $list = isset($resultado['datos']) ? $resultado['datos'] : [];
        self::respuestaJSON(['success' => $resultado['success'] ?? false, 'mensaje' => $resultado['mensaje'] ?? '', 'datos' => $list]);
    }

    /**
     * API: agregar mensaje al chat (bitácora). id_persona = sesión.
     */
    public function addChatTicket()
    {
        $raw = file_get_contents('php://input');
        $datos = json_decode($raw, true) ?: [];
        $idTicket = (int)($datos['id_ticket'] ?? 0);
        $mensaje = isset($datos['mensaje']) ? trim((string)$datos['mensaje']) : '';
        $idPersona = (int)($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        if ($idTicket < 1 || $mensaje === '' || $idPersona < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Datos inválidos.']);
            return;
        }
        $resultado = TicketDAO::agregarChat($idTicket, $idPersona, $mensaje);
        self::respuestaJSON(['success' => $resultado['success'] ?? false, 'mensaje' => $resultado['mensaje'] ?? '']);
    }

    /**
     * API: eliminar un mensaje de la bitácora (chat). Requiere id_mensaje; opcional id_ticket para verificar.
     */
    public function eliminarMensajeChat()
    {
        $raw = file_get_contents('php://input');
        $datos = json_decode($raw, true) ?: [];
        $idMensaje = (int)($datos['id_mensaje'] ?? 0);
        $idTicket = isset($datos['id_ticket']) ? (int)$datos['id_ticket'] : null;
        if ($idMensaje < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'ID de mensaje requerido.']);
            return;
        }
        if ($idTicket !== null && $idTicket < 1) {
            $idTicket = null;
        }
        $resultado = TicketDAO::eliminarMensajeChat($idMensaje, $idTicket);
        self::respuestaJSON(['success' => $resultado['success'] ?? false, 'mensaje' => $resultado['mensaje'] ?? '']);
    }

    /**
     * API: mensajes de dictamen por ticket.
     */
    public function getDictamenTicket()
    {
        $raw = file_get_contents('php://input');
        $datos = json_decode($raw, true) ?: [];
        $idTicket = (int)($datos['id_ticket'] ?? 0);
        if ($idTicket < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'ID de ticket requerido.', 'datos' => []]);
            return;
        }
        $resultado = TicketDAO::getDictamenPorTicket($idTicket);
        $list = isset($resultado['datos']) ? $resultado['datos'] : [];
        self::respuestaJSON(['success' => $resultado['success'] ?? false, 'mensaje' => $resultado['mensaje'] ?? '', 'datos' => $list]);
    }

    /**
     * API: agregar mensaje de dictamen. id_persona = sesión.
     */
    public function addDictamenTicket()
    {
        $raw = file_get_contents('php://input');
        $datos = json_decode($raw, true) ?: [];
        $idTicket = (int)($datos['id_ticket'] ?? 0);
        $mensaje = isset($datos['mensaje']) ? trim((string)$datos['mensaje']) : '';
        $idPersona = (int)($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        if ($idTicket < 1 || $mensaje === '' || $idPersona < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Datos inválidos.']);
            return;
        }
        $resultado = TicketDAO::agregarDictamen($idTicket, $idPersona, $mensaje);
        self::respuestaJSON(['success' => $resultado['success'] ?? false, 'mensaje' => $resultado['mensaje'] ?? '']);
    }

    /**
     * API: eliminar un mensaje de dictamen.
     */
    public function eliminarMensajeDictamen()
    {
        $raw = file_get_contents('php://input');
        $datos = json_decode($raw, true) ?: [];
        $idMensaje = (int)($datos['id_mensaje'] ?? 0);
        $idTicket = isset($datos['id_ticket']) ? (int)$datos['id_ticket'] : null;
        if ($idMensaje < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'ID de mensaje requerido.']);
            return;
        }
        if ($idTicket !== null && $idTicket < 1) {
            $idTicket = null;
        }
        $resultado = TicketDAO::eliminarMensajeDictamen($idMensaje, $idTicket);
        self::respuestaJSON(['success' => $resultado['success'] ?? false, 'mensaje' => $resultado['mensaje'] ?? '']);
    }

    /**
     * API: guardar dictamen como borrador (tipo + descripción). Evidencias se suben con subirEvidenciaTicket.
     */
    public function guardarDictamenBorrador()
    {
        $raw = file_get_contents('php://input');
        $datos = json_decode($raw, true) ?: [];
        $idTicket = (int)($datos['id_ticket'] ?? 0);
        $tipo = isset($datos['tipo']) ? trim((string)$datos['tipo']) : '';
        $descripcion = isset($datos['descripcion']) ? trim((string)$datos['descripcion']) : '';
        $idPersona = (int)($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        if ($idTicket < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Falta el ID del ticket. Cierre y abra de nuevo el rastreo.']);
            return;
        }
        $esIloc = \Models\Ticket::esTipoDictamenIlocalizable($tipo);
        if ($tipo === '' || (!$esIloc && $descripcion === '')) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Faltan tipo o descripción.']);
            return;
        }
        if ($idPersona < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Sesión inválida. Debe iniciar sesión para guardar.']);
            return;
        }
        $resultado = TicketDAO::guardarDictamenBorrador($idTicket, $idPersona, $tipo, $descripcion);
        self::respuestaJSON([
            'success' => $resultado['success'] ?? false,
            'mensaje' => $resultado['mensaje'] ?? '',
            'datos' => $resultado['datos'] ?? null,
            'error' => $resultado['error'] ?? null
        ]);
    }

    /**
     * API: enviar dictamen al gestor (estado = enviado_al_gestor).
     */
    public function enviarDictamenGestor()
    {
        $raw = file_get_contents('php://input');
        $datos = json_decode($raw, true) ?: [];
        $idTicket = (int)($datos['id_ticket'] ?? 0);
        if ($idTicket < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'ID de ticket requerido.']);
            return;
        }
        $idPersona = (int)($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        $resultado = TicketDAO::enviarDictamenGestor($idTicket, $idPersona);
        if ($resultado['success'] ?? false) {
            // Notificación solo al que levantó el ticket (no a todos los de Sabueso/Ticket)
            $idCreador = TicketDAO::getCreadorIdPorTicket($idTicket);
            if ($idCreador > 0) {
                $nombreQuienEnvia = trim($_SESSION['usuario_nombre'] ?? 'Alguien');
                Notificacion::crear($idCreador, 'dictamen_enviado', 'Dictamen enviado por ' . $nombreQuienEnvia, $idTicket);
            }
        }
        self::respuestaJSON(['success' => $resultado['success'] ?? false, 'mensaje' => $resultado['mensaje'] ?? '']);
    }

    /**
     * API: detalle del dictamen para el modal del gestor (tipo, descripción, evidencias con URL).
     */
    public function getDictamenDetalle()
    {
        $raw = file_get_contents('php://input');
        $datos = json_decode($raw, true) ?: [];
        $idTicket = (int)($datos['id_ticket'] ?? 0);
        if ($idTicket < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'ID de ticket requerido.', 'datos' => null]);
            return;
        }
        $resultado = TicketDAO::getDictamenDetallePorTicket($idTicket);
        $datosOut = $resultado['datos'] ?? null;
        if ($datosOut && !empty($datosOut['evidencias'])) {
            $baseUrl = '/sabueso/verEvidencia?id=';
            foreach ($datosOut['evidencias'] as &$e) {
                $e['url'] = $baseUrl . (int)$e['id'];
            }
        }
        self::respuestaJSON([
            'success' => $resultado['success'] ?? false,
            'mensaje' => $resultado['mensaje'] ?? '',
            'datos' => $datosOut
        ]);
    }

    /**
     * API: marcar dictamen como visto por el gestor (fecha y quién lo abrió).
     */
    public function marcarDictamenVisto()
    {
        $raw = file_get_contents('php://input');
        $datos = json_decode($raw, true) ?: [];
        $idTicket = (int)($datos['id_ticket'] ?? 0);
        if ($idTicket < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'ID de ticket requerido.']);
            return;
        }
        $idPersona = (int)($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        $resultado = TicketDAO::marcarDictamenVisto($idTicket, $idPersona);
        if ($resultado['success'] ?? false) {
            $nombreRevisor = trim($_SESSION['usuario_nombre'] ?? 'Alguien');
            $autorDictamen = TicketDAO::getDictamenAutorIdPorTicket($idTicket);
            $ids = [];
            if ($autorDictamen > 0) {
                $ids[] = $autorDictamen;
            }
            if (!empty($ids)) {
            Notificacion::crearParaPersonas($ids, 'dictamen_revisado', 'Dictamen revisado por ' . $nombreRevisor, $idTicket);
            }
        }
        self::respuestaJSON(['success' => $resultado['success'] ?? false, 'mensaje' => $resultado['mensaje'] ?? '']);
    }

    /**
     * API: obtener dictamen actual del ticket (para prellenar formulario y estado del botón).
     */
    public function getDictamenActualTicket()
    {
        $raw = file_get_contents('php://input');
        $datos = json_decode($raw, true) ?: [];
        $idTicket = (int)($datos['id_ticket'] ?? 0);
        if ($idTicket < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'ID de ticket requerido.', 'datos' => null]);
            return;
        }
        $resultado = TicketDAO::getDictamenActualPorTicket($idTicket);
        self::respuestaJSON([
            'success' => $resultado['success'] ?? false,
            'mensaje' => $resultado['mensaje'] ?? '',
            'datos' => $resultado['datos'] ?? null
        ]);
    }

    /**
     * API: generar dictamen del sistema (verificación automática post-12h).
     * Solo usuarios con acceso a Panel Admin (módulo 19).
     */
    public function generarDictamenSistema()
    {
        $modulos = $_SESSION['modulos'] ?? [];
        if (!is_array($modulos) || (!in_array(19, $modulos) && !in_array(27, $modulos))) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'No tiene permiso para esta acción.']);
            return;
        }
        $raw = file_get_contents('php://input');
        $datos = json_decode($raw, true) ?: [];
        $idTicket = (int)($datos['id_ticket'] ?? 0);
        if ($idTicket < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'ID de ticket requerido.']);
            return;
        }
        $forzarRegeneracion = !empty($datos['revalidar_pago']);
        $resultado = TicketDAO::generarDictamenSistema($idTicket, $forzarRegeneracion);
        $payload = [
            'success' => $resultado['success'] ?? false,
            'mensaje' => $resultado['mensaje'] ?? '',
            'datos'   => $resultado['datos'] ?? null,
        ];
        if (isset($resultado['error'])) {
            $payload['error'] = $resultado['error'];
        }
        self::respuestaJSON($payload);
    }

    /**
     * API: obtener dictamen del sistema existente.
     */
    public function getDictamenSistema()
    {
        $raw = file_get_contents('php://input');
        $datos = json_decode($raw, true) ?: [];
        $idTicket = (int)($datos['id_ticket'] ?? 0);
        if ($idTicket < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'ID de ticket requerido.']);
            return;
        }
        $resultado = TicketDAO::getDictamenSistema($idTicket);
        $payload = [
            'success' => $resultado['success'] ?? false,
            'mensaje' => $resultado['mensaje'] ?? '',
            'datos'   => $resultado['datos'] ?? null,
        ];
        if (isset($resultado['error'])) {
            $payload['error'] = $resultado['error'];
        }
        self::respuestaJSON($payload);
    }

    /**
     * API: otorgar prórroga única de 12 horas al dictamen del sistema.
     */
    public function otorgarProrrogaDictamenSistema()
    {
        $modulos = $_SESSION['modulos'] ?? [];
        if (!is_array($modulos) || (!in_array(19, $modulos) && !in_array(27, $modulos))) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'No tiene permiso para esta acción.']);
            return;
        }
        $raw = file_get_contents('php://input');
        $datos = json_decode($raw, true) ?: [];
        $idTicket = (int)($datos['id_ticket'] ?? 0);
        if ($idTicket < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'ID de ticket requerido.']);
            return;
        }
        $idPersona = (int)($_SESSION['persona_id'] ?? ($_SESSION['usuario_id'] ?? 0));
        $nombre = TicketDAO::getNombrePersona($idPersona);
        $resultado = TicketDAO::otorgarProrrogaDictamenSistema($idTicket, $idPersona, (string)$nombre);
        if ($resultado['success'] ?? false) {
            $idGestor = TicketDAO::getCreadorIdPorTicket($idTicket);
            if ($idGestor > 0) {
                $folio = TicketDAO::getFolioPorTicket($idTicket);
                $ticketRef = $folio !== '' ? $folio : '#' . $idTicket;
                $mensaje = 'Se le ha otorgado una prórroga de 12 horas para el ticket (' . $ticketRef . '). Tiene ese plazo para completar las visitas indicadas en el dictamen.';
                Notificacion::crear($idGestor, 'prorroga_otorgada', $mensaje, $idTicket);
            }
        }
        self::respuestaJSON([
            'success' => $resultado['success'] ?? false,
            'mensaje' => $resultado['mensaje'] ?? '',
            'datos'   => $resultado['datos'] ?? null,
        ]);
    }

    /**
     * API: otorgar Intensidad (+12 h) al dictamen del sistema tras visita en campo sin pago en ventana inicial.
     */
    public function otorgarIntensidadDictamenSistema()
    {
        $modulos = $_SESSION['modulos'] ?? [];
        if (!is_array($modulos) || (!in_array(19, $modulos) && !in_array(27, $modulos))) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'No tiene permiso para esta acción.']);
            return;
        }
        $raw = file_get_contents('php://input');
        $datos = json_decode($raw, true) ?: [];
        $idTicket = (int)($datos['id_ticket'] ?? 0);
        if ($idTicket < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'ID de ticket requerido.']);
            return;
        }
        $idPersona = (int)($_SESSION['persona_id'] ?? ($_SESSION['usuario_id'] ?? 0));
        $nombre = TicketDAO::getNombrePersona($idPersona);
        $resultado = TicketDAO::otorgarIntensidadDictamenSistema($idTicket, $idPersona, (string)$nombre);
        if ($resultado['success'] ?? false) {
            $idGestor = TicketDAO::getCreadorIdPorTicket($idTicket);
            if ($idGestor > 0) {
                $folio = TicketDAO::getFolioPorTicket($idTicket);
                $ticketRef = $folio !== '' ? $folio : '#' . $idTicket;
                $mensaje = 'Se ha otorgado Intensidad (12 horas adicionales) para el ticket (' . $ticketRef . '). Aplica tras visita en campo sin pago en la ventana inicial.';
                Notificacion::crear($idGestor, 'intensidad_otorgada', $mensaje, $idTicket);
            }
        }
        self::respuestaJSON([
            'success' => $resultado['success'] ?? false,
            'mensaje' => $resultado['mensaje'] ?? '',
            'datos'   => $resultado['datos'] ?? null,
        ]);
    }

    /**
     * API: listar evidencias del ticket.
     */
    public function getEvidenciasTicket()
    {
        $raw = file_get_contents('php://input');
        $datos = json_decode($raw, true) ?: [];
        $idTicket = (int)($datos['id_ticket'] ?? 0);
        if ($idTicket < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'ID de ticket requerido.', 'datos' => []]);
            return;
        }
        $tipoFiltro = isset($datos['tipo_origen']) ? trim((string)$datos['tipo_origen']) : TicketDAO::TIPO_ORIGEN_DICTAMEN_SABUESO;
        if ($tipoFiltro === '' || strcasecmp($tipoFiltro, 'todos') === 0) {
            $tipoFiltro = null;
        }
        $resultado = TicketDAO::getEvidenciasPorTicket($idTicket, $tipoFiltro);
        $list = isset($resultado['datos']) ? $resultado['datos'] : [];
        $baseUrl = '/sabueso/verEvidencia?id=';
        foreach ($list as &$e) {
            $e['url'] = $baseUrl . (int)$e['id'];
        }
        self::respuestaJSON(['success' => $resultado['success'] ?? false, 'mensaje' => $resultado['mensaje'] ?? '', 'datos' => $list]);
    }

    /**
     * API: evidencia cruda para verificación (pagos, GPS, gestiones, suspected_test).
     * No llama a IA. Sirve para contrastar lo que dice la Lectura IA con datos reales del sistema.
     * Requiere permiso módulo 19 (Panel Admin / Analizar IA).
     */
    public function getEvidenciaVerificacion()
    {
        $modulos = $_SESSION['modulos'] ?? [];
        if (!is_array($modulos) || (!in_array(19, $modulos) && !in_array(27, $modulos))) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'No tiene permiso para ver datos verificados.', 'datos' => null]);
            return;
        }
        $raw = file_get_contents('php://input');
        $datos = json_decode($raw, true) ?: [];
        $idCredito = isset($datos['id_credito']) && $datos['id_credito'] !== '' ? (int) $datos['id_credito'] : 0;
        $idTicket = isset($datos['id_ticket']) ? (int) $datos['id_ticket'] : 0;
        if ($idCredito < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'ID de crédito requerido.', 'datos' => null]);
            return;
        }
        try {
            $pagosCount = $this->getPagosCountForCredito($idCredito);
            $ubic = UbicacionDAO::getUbicacionesFiltradasPorIdCredito($idCredito);
            $gps = [];
            foreach (array_slice($ubic['direcciones_resumen'] ?? [], 0, 10) as $d) {
                $gps[] = [
                    'texto' => $d['texto'] ?? '—',
                    'visitas' => (int) ($d['cantidad_registros'] ?? 0),
                    'ultima_fecha' => $d['ultima_fecha'] ?? '—',
                    'lat' => $d['lat'] ?? null,
                    'lng' => $d['lng'] ?? null,
                ];
            }
            $gestiones = GestionesDAO::getAllGestiones($idCredito, '');
            $gestiones = is_array($gestiones) ? array_slice($gestiones, 0, 10) : [];
            $gestionesList = [];
            foreach ($gestiones as $g) {
                $gestionesList[] = [
                    'fecha' => $g['fecha_dispositivo'] ?? $g['fecha_hora'] ?? '—',
                    'gestor' => $g['usuario_asignado'] ?? $g['usuario'] ?? '—',
                    'dictamen' => $g['dictamen_campo'] ?? $g['dictamen_ccc'] ?? '—',
                    'comentarios' => substr($g['comentarios_generales'] ?? '—', 0, 200),
                ];
            }
            $test = $this->detectSuspectedTest($idCredito, $idTicket);
            self::respuestaJSON([
                'success' => true,
                'mensaje' => 'OK',
                'datos' => [
                    'pagos_count' => $pagosCount,
                    'gps' => $gps,
                    'gestiones' => $gestionesList,
                    'suspected_test' => $test['suspected'],
                    'suspected_test_reasons' => $test['reasons'],
                ],
            ]);
        } catch (\Throwable $e) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'Error al obtener evidencia: ' . $e->getMessage(),
                'datos' => null,
            ]);
        }
    }

    /**
     * API: subir evidencia (imagen). POST id_ticket + file (evidencia).
     */
    public function subirEvidenciaTicket()
    {
        try {
            $idTicket = (int)($_POST['id_ticket'] ?? 0);
            $idPersona = (int)($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
            if ($idTicket < 1 || $idPersona < 1) {
                self::respuestaJSON(['success' => false, 'mensaje' => 'Datos inválidos (ticket o sesión).']);
                return;
            }
            if (empty($_FILES['evidencia']) || $_FILES['evidencia']['error'] !== UPLOAD_ERR_OK) {
                self::respuestaJSON(['success' => false, 'mensaje' => 'No se recibió una imagen válida.']);
                return;
            }
            if (!\Core\SecureUpload::validateMime($_FILES['evidencia']['tmp_name'], \Core\SecureUpload::MIME_IMAGES)) {
                self::respuestaJSON(['success' => false, 'mensaje' => 'Solo se permiten imágenes (JPEG, PNG, GIF, WebP).']);
                return;
            }
            $mime = \Core\SecureUpload::getMimeType($_FILES['evidencia']['tmp_name']);
            $ext = $mime ? \Core\SecureUpload::extensionFromMime($mime) : 'jpg';
            $dir = sparta_uploads_join('sabueso_evidencias');
            \Core\SecureUpload::ensureDir($dir);
            $nombreArchivo = 'ev_' . $idTicket . '_' . $idPersona . '_' . \Core\SecureUpload::generateSafeFilename($ext);
            $rutaCompleta = $dir . '/' . $nombreArchivo;
            if (!move_uploaded_file($_FILES['evidencia']['tmp_name'], $rutaCompleta)) {
                self::respuestaJSON(['success' => false, 'mensaje' => 'Error al guardar el archivo.']);
                return;
            }
            $rutaRelativa = 'sabueso_evidencias/' . $nombreArchivo;
            $resultado = TicketDAO::guardarEvidencia(
                $idTicket,
                $idPersona,
                $rutaRelativa,
                $_FILES['evidencia']['name'],
                TicketDAO::TIPO_ORIGEN_DICTAMEN_SABUESO
            );
            if (!($resultado['success'] ?? false)) {
                @unlink($rutaCompleta);
                self::respuestaJSON(['success' => false, 'mensaje' => $resultado['mensaje'] ?? 'Error al registrar la evidencia en la base de datos.']);
                return;
            }
            $idEvidencia = isset($resultado['datos']['id']) ? (int)$resultado['datos']['id'] : null;
            self::respuestaJSON([
                'success' => true,
                'mensaje' => $resultado['mensaje'] ?? 'Evidencia guardada.',
                'datos' => ['id' => $idEvidencia, 'url' => '/sabueso/verEvidencia?id=' . $idEvidencia]
            ]);
        } catch (\Throwable $e) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Error al subir: ' . $e->getMessage()]);
        }
    }

    /**
     * API: eliminar evidencia. La evidencia es única por ticket; se valida que pertenezca al ticket indicado.
     */
    public function eliminarEvidenciaTicket()
    {
        $raw = file_get_contents('php://input');
        $datos = json_decode($raw, true) ?: [];
        $idEvidencia = (int)($datos['id_evidencia'] ?? 0);
        $idTicket = isset($datos['id_ticket']) ? (int)$datos['id_ticket'] : 0;
        if ($idEvidencia < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'ID de evidencia requerido.']);
            return;
        }
        $row = TicketDAO::getEvidenciaPorId($idEvidencia);
        if (!$row) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Evidencia no encontrada.']);
            return;
        }
        if ($idTicket > 0 && (int)($row['id_ticket'] ?? 0) !== $idTicket) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'La evidencia no pertenece a este ticket.']);
            return;
        }
        if (!empty($row['ruta_archivo'])) {
            $path = sparta_uploads_resolve_relative($row['ruta_archivo']);
            if ($path !== null && is_file($path)) {
                @unlink($path);
            }
        }
        $resultado = TicketDAO::eliminarEvidencia($idEvidencia);
        self::respuestaJSON(['success' => $resultado['success'] ?? false, 'mensaje' => $resultado['mensaje'] ?? '']);
    }

    /**
     * Servir imagen de evidencia (por id). Respuesta binaria, no JSON.
     */
    public function verEvidencia()
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id < 1) {
            http_response_code(404);
            return;
        }
        $row = TicketDAO::getEvidenciaPorId($id);
        if (!$row || empty($row['ruta_archivo'])) {
            http_response_code(404);
            return;
        }
        $path = sparta_uploads_resolve_relative($row['ruta_archivo']);
        if ($path === null || !is_file($path)) {
            http_response_code(404);
            return;
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path);
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: private, max-age=86400');
        header('ETag: "ev-' . $id . '-' . filemtime($path) . '"');
        readfile($path);
        exit;
    }

    /**
     * Vista "Analítica IA - Resumen": métricas desde servicios existentes (sin hardcodear valores).
     * GET /sabueso/resumenAnalitica?id_credito=123
     */
    public function resumenAnalitica()
    {
        $idCredito = (int) ($_GET['id_credito'] ?? $_GET['idCredito'] ?? 0);
        if ($idCredito < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'id_credito requerido.']);
            return;
        }

        $datos = $this->getDatosResumenAnalitica($idCredito);
        if ($datos === null) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'No se pudieron obtener datos para este crédito.']);
            return;
        }

        foreach ($datos as $k => $v) {
            $this->set($k, $v);
        }
        $this->render('sabueso_resumen_analitica');
    }

    /**
     * GET /sabueso/resumenAnaliticaHTML?id_credito=123
     * Devuelve el HTML renderizado del resumen analítica para inyectar en el modal.
     * Solo datos determinísticos (SpatialAnalytics, GestorCompliance, TemporalPayments). Sin IA.
     */
    public function resumenAnaliticaHTML()
    {
        header('Content-Type: application/json; charset=utf-8');
        $idCredito = (int) ($_GET['id_credito'] ?? $_GET['idCredito'] ?? 0);
        if ($idCredito < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'id_credito requerido.']);
            return;
        }
        try {
            $datos = $this->getDatosResumenAnalitica($idCredito);
            if ($datos === null) {
                self::respuestaJSON(['success' => false, 'mensaje' => 'No se pudieron obtener datos para este crédito.']);
                return;
            }
            $datos['en_modal'] = true;
            extract($datos);
            ob_start();
            include __DIR__ . '/../views/sabueso_resumen_analitica.php';
            $html = ob_get_clean();
            // Quitar el <link> CSS duplicado (ya cargado en paneladmin)
            $html = str_replace('<link rel="stylesheet" href="/assets/css/analitica-ia.css">', '', $html);
            self::respuestaJSON(['success' => true, 'html' => trim($html)]);
        } catch (\Throwable $e) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'Error al generar resumen: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Obtiene datos para la vista Resumen Analítica desde servicios existentes (sin inventar valores).
     *
     * @return array|null Variables para la vista o null si falla
     */
    private function getDatosResumenAnalitica(int $idCredito): ?array
    {
        $data = $this->prepararDatosParaMotor($idCredito);
        $estadoCuentaData = $this->getDatosEstadoCuentaParaPipeline($idCredito);
        $pagosDesdeApi = $estadoCuentaData['pagos_para_temporal'] ?? [];
        $analiticas = $this->ejecutarAnaliticasDeterministicas($idCredito, $data, $pagosDesdeApi);
        $analiticaEspacial = $analiticas['analitica_espacial'] ?? [];
        $analiticaPagos = $analiticas['analitica_pagos'] ?? [];
        $cumplimientoGestor = $analiticas['cumplimiento_gestor'] ?? [];

        $eventosGestor = GestionesDAO::getEventosGestorPorCredito($idCredito);
        $detalles = $cumplimientoGestor['detalles'] ?? [];
        // Mismo criterio que API Cumplimiento Gestor: enriquecer por NDICE para que el nombre coincida siempre
        foreach ($detalles as $i => &$d) {
            $nombre = '—';
            if (isset($eventosGestor[$i])) {
                $e = $eventosGestor[$i];
                $nombre = trim((string) ($e['usuario_asignado'] ?? ''));
                if ($nombre === '') {
                    $nombre = trim((string) ($e['codigo_gestor'] ?? ''));
                }
                if ($nombre === '') {
                    $nombre = trim((string) ($e['usuario'] ?? ''));
                }
                $nombre = $nombre !== '' ? $nombre : '—';
            }
            $d['gestor_nombre'] = $nombre;
        }
        unset($d);

        $peorGestor = $this->calcularPeorGestorDesdeDetalles($detalles);

        $distanciasCasa = $analiticaEspacial['distancias_a_casa'] ?? [];
        $distanciaDomicilio = null;
        $domicilioConfirmado = false;
        if (!empty($distanciasCasa) && is_array($distanciasCasa)) {
            $distanciasMetros = array_filter(array_column($distanciasCasa, 'distancia_m'), 'is_numeric');
            $distanciaDomicilio = !empty($distanciasMetros) ? (int) round(min($distanciasMetros)) : null;
            $domicilioConfirmado = $distanciaDomicilio !== null && $distanciaDomicilio <= 100;
        }
        $puntoInteres = [];
        if (!empty($distanciasCasa)) {
            $ordenado = $distanciasCasa;
            usort($ordenado, function ($a, $b) {
                return (int) ($b['visitas_count'] ?? 0) - (int) ($a['visitas_count'] ?? 0);
            });
            $primero = $ordenado[0];
            $distanciaKm = isset($primero['distancia_m']) ? round((float) $primero['distancia_m'] / 1000.0, 2) : null;
            $puntoInteres = [
                'distancia' => $distanciaKm,
                'visitas' => (int) ($primero['visitas_count'] ?? 0),
                'lat' => $primero['lat'] ?? null,
                'lng' => $primero['lng'] ?? null,
            ];
            // Tipo del punto más visitado: usar su label; si no hay, fallback a direcciones_resumen o genérico
            $ubicResumen = UbicacionDAO::getUbicacionesFiltradasPorIdCredito($idCredito);
            $direccionesResumen = $ubicResumen['direcciones_resumen'] ?? [];
            $labelPrimero = trim((string) ($primero['label'] ?? ''));
            if ($labelPrimero !== '') {
                $puntoInteres['tipo'] = $labelPrimero;
            } elseif (!empty($direccionesResumen)) {
                $primeraDir = $direccionesResumen[0];
                $puntoInteres['tipo'] = trim((string) ($primeraDir['texto'] ?? '')) !== ''
                    ? (string) $primeraDir['texto']
                    : (!empty($primeraDir['punto_de_interes']) ? 'Punto de interés' : 'Menos frecuente');
            } else {
                $puntoInteres['tipo'] = 'Punto de interés';
            }
        }
        // Score espacial continuo: distancia (mejor punto) + factor por cantidad de puntos (0.7 + 0.3 × min(1, n/5)).
        $nPuntosEspacial = count($distanciasCasa);
        if ($nPuntosEspacial === 0) {
            $confianzaEspacial = 0.0;
        } else {
            $d = $distanciaDomicilio !== null ? (float) $distanciaDomicilio : 999.0;
            if ($d <= 20) {
                $scoreDist = 1.0;
            } elseif ($d <= 100) {
                $scoreDist = 1.0 - ($d - 20) / 80;
            } else {
                $scoreDist = 0.0;
            }
            $factorPuntos = min(1.0, $nPuntosEspacial / 5);
            $confianzaEspacial = $scoreDist * (0.7 + 0.3 * $factorPuntos);
        }
        $domicilioConfirmado = $distanciaDomicilio !== null && $distanciaDomicilio <= 100;

        $cumplimientoPorc = isset($cumplimientoGestor['porcentaje_cumplimiento']) && is_numeric($cumplimientoGestor['porcentaje_cumplimiento'])
            ? (float) $cumplimientoGestor['porcentaje_cumplimiento'] : null;
        $visitasCercanas = (int) ($cumplimientoGestor['visitas_cercanas'] ?? 0);
        $visitasLejanas = (int) ($cumplimientoGestor['visitas_lejanas'] ?? 0);
        // Score gestión continuo por visita: d<=30→1, 30<d<=150→1-(d-30)/120, d>150→0; confianza = promedio(scoreVisita).
        $confianzaGestion = 0.0;
        if (!empty($detalles)) {
            $sumaScore = 0.0;
            $numVisitas = 0;
            foreach ($detalles as $det) {
                $numVisitas++;
                $distM = isset($det['distancia_m']) && is_numeric($det['distancia_m']) ? (float) $det['distancia_m'] : null;
                if (!empty($det['sin_gps']) || $distM === null) {
                    $sumaScore += 0.0;
                    continue;
                }
                if ($distM <= 30) {
                    $sumaScore += 1.0;
                } elseif ($distM <= 150) {
                    $sumaScore += 1.0 - ($distM - 30) / 120;
                } else {
                    $sumaScore += 0.0;
                }
            }
            $confianzaGestion = $numVisitas > 0 ? $sumaScore / $numVisitas : 0.0;
        } elseif ($cumplimientoPorc !== null) {
            $confianzaGestion = min(1.0, (float) $cumplimientoPorc / 100.0);
        }

        $totalPagos = (int) ($analiticaPagos['total_pagos'] ?? 0);
        $intervaloPromedio = isset($analiticaPagos['intervalo_promedio_dias']) && is_numeric($analiticaPagos['intervalo_promedio_dias'])
            ? round((float) $analiticaPagos['intervalo_promedio_dias'], 1) : null;
        $desviacion = isset($analiticaPagos['desviacion_intervalos']) && is_numeric($analiticaPagos['desviacion_intervalos'])
            ? round((float) $analiticaPagos['desviacion_intervalos'], 2) : null;
        $diaFrecuente = isset($analiticaPagos['dia_mas_frecuente']) && (string) $analiticaPagos['dia_mas_frecuente'] !== ''
            ? (string) $analiticaPagos['dia_mas_frecuente'] : 'N/D';
        $consistenciaDia = isset($analiticaPagos['consistencia_dia']) && is_numeric($analiticaPagos['consistencia_dia'])
            ? round((float) $analiticaPagos['consistencia_dia'] * 100, 1) : null;
        $patronPago = isset($analiticaPagos['patron_pago']) && (string) $analiticaPagos['patron_pago'] !== ''
            ? (string) $analiticaPagos['patron_pago'] : 'desconocido';

        // Score continuo 0–1 desde CV (desviación/intervalo). Etiquetas por rangos: Regular >=0.7, Irregular 0.4–<0.7, Crítico <0.4.
        $scorePagosContinuo = 0.0;
        if ($totalPagos >= 3 && $intervaloPromedio !== null && $intervaloPromedio > 0 && $desviacion !== null) {
            $cv = (float) $desviacion / (float) $intervaloPromedio;
            if ($cv <= 0.35) {
                $scorePagosContinuo = 1.0 - (0.3 / 0.35) * $cv;
            } elseif ($cv <= 0.7) {
                $scorePagosContinuo = 0.7 - (0.3 / 0.35) * ($cv - 0.35);
            } else {
                $scorePagosContinuo = max(0.0, 0.4 - ($cv - 0.7));
            }
        }
        $confianzaPagos = $scorePagosContinuo;
        $etiquetaPatronPago = $scorePagosContinuo >= 0.7 ? 'regular' : ($scorePagosContinuo >= 0.4 ? 'irregular' : 'critico');
        // Promedio simple de los tres factores (1/3 cada uno); solo se promedian los que tienen valor > 0.
        $pesoTotal = 0;
        $confianzaGeneral = 0.0;
        if ($confianzaEspacial > 0) {
            $confianzaGeneral += $confianzaEspacial * (1 / 3);
            $pesoTotal += 1 / 3;
        }
        if ($confianzaGestion > 0) {
            $confianzaGeneral += $confianzaGestion * (1 / 3);
            $pesoTotal += 1 / 3;
        }
        if ($confianzaPagos > 0) {
            $confianzaGeneral += $confianzaPagos * (1 / 3);
            $pesoTotal += 1 / 3;
        }
        $confianzaGeneral = $pesoTotal > 0 ? (int) round(($confianzaGeneral / $pesoTotal) * 100) : 0;

        $scoreCliente = $confianzaEspacial > 0 ? (int) round($confianzaEspacial * 100) : 0;
        $scoreGestion = (int) round($confianzaGestion * 100);
        $scorePagos = $totalPagos > 0 ? (int) round($scorePagosContinuo * 100) : 0;
        if ($totalPagos >= 10 && $scorePagos > 0) {
            $scorePagos = min(100, $scorePagos + 10);
        }

        $ultimoPago = ['fecha' => null, 'monto' => null];
        // Prioridad: estado de cuenta (sección créditos) para fecha y monto del último pago
        try {
            $estadoCuentaCtrl = new \Controllers\EstadoCuenta();
            $resEstado = $estadoCuentaCtrl->api___SPARTA_SECRET_REDACTED__($idCredito, date('Y-m-d'));
            if (!empty($resEstado['ok']) && !empty($resEstado['data']['datosPagos'])) {
                $pagosEc = $resEstado['data']['datosPagos'];
                $conFechaMonto = [];
                foreach ($pagosEc as $p) {
                    $f = $p['fechaDeposito'] ?? $p['fechaRegistro'] ?? $p['fechaValor'] ?? null;
                    if ($f !== null) {
                        $fNorm = is_numeric($f) ? date('Y-m-d', (int) $f) : date('Y-m-d', strtotime($f));
                        $conFechaMonto[] = [
                            'fecha' => $fNorm,
                            'monto' => $p['montoPago'] ?? $p['monto'] ?? null,
                        ];
                    }
                }
                if (!empty($conFechaMonto)) {
                    usort($conFechaMonto, function ($a, $b) {
                        return strcmp($b['fecha'], $a['fecha']);
                    });
                    $ultimoPago['fecha'] = $conFechaMonto[0]['fecha'];
                    $ultimoPago['monto'] = $conFechaMonto[0]['monto'];
                }
            }
        } catch (\Throwable $e) {
            // Si falla estado de cuenta, se usa fallback de gestiones
        }
        // Fallback: gestiones (dictamen Pago) si no hubo estado de cuenta
        if ($ultimoPago['fecha'] === null) {
            $gestionesList = $data['gestiones'] ?? [];
            foreach ($gestionesList as $item) {
                $tipo = (string) ($item['dictamen_campo'] ?? $item['dictamen_ccc'] ?? $item['tipo'] ?? '');
                if (stripos($tipo, 'Pago') !== false) {
                    $f = $item['fecha_dispositivo'] ?? $item['fecha_hora'] ?? $item['fecha'] ?? null;
                    if ($f) {
                        $ultimoPago['fecha'] = is_numeric($f) ? date('Y-m-d', (int) $f) : date('Y-m-d', strtotime($f));
                        $ultimoPago['monto'] = $item['monto'] ?? null;
                        break;
                    }
                }
            }
        }

        $datosFaltantes = [];
        if (empty($ultimoPago['fecha'])) {
            $datosFaltantes[] = ['campo' => 'fecha_ultimo_pago', 'descripcion' => 'Requerido para cálculo de morosidad'];
        }
        if (empty($ultimoPago['monto'])) {
            $datosFaltantes[] = ['campo' => 'monto_ultimo_pago', 'descripcion' => 'Necesario para análisis de tendencia'];
        }
        if (!empty($puntoInteres) && !isset($puntoInteres['tipo'])) {
            $datosFaltantes[] = ['campo' => 'identidad_tipo_punto_interes', 'descripcion' => 'Para optimizar estrategia de contacto'];
        }

        $accionesRecomendadas = [];
        if ($cumplimientoPorc !== null && $cumplimientoPorc < 30 && $peorGestor !== null) {
            $accionesRecomendadas[] = [
                'prioridad' => 'alta',
                'titulo' => 'Auditar o reasignar al gestor responsable por baja eficacia',
                'descripcion' => 'El desempeño en campo es crítico (' . number_format($cumplimientoPorc, 1) . '%). Se requiere acción inmediata: auditoría de campo, capacitación o reasignación del caso.',
            ];
        }
        if (!empty($puntoInteres) && isset($puntoInteres['visitas']) && (int) $puntoInteres['visitas'] >= 10) {
            $accionesRecomendadas[] = [
                'prioridad' => 'media',
                'titulo' => 'Verificar identidad del punto de interés con ' . (int) $puntoInteres['visitas'] . ' visitas',
                'descripcion' => 'Determinar si es lugar de trabajo para ajustar estrategia de contacto y horarios de gestión.',
            ];
        }
        if (!empty($datosFaltantes)) {
            $accionesRecomendadas[] = [
                'prioridad' => 'baja',
                'titulo' => 'Solicitar y registrar información faltante',
                'descripcion' => 'Completar ' . count($datosFaltantes) . ' campo(s) faltante(s) antes de automatizar comunicaciones.',
            ];
        }
        if ($totalPagos >= 5 && ($etiquetaPatronPago === 'regular' || $scorePagos >= 70)) {
            $accionesRecomendadas[] = [
                'prioridad' => 'baja',
                'titulo' => 'Mantener condiciones actuales del crédito',
                'descripcion' => 'Sólo mantener tras verificar que la auditoría no indica problema. Situación de pagos y ubicación según datos disponibles.',
            ];
        }

        $mensajesSugeridos = [];
        if (empty($ultimoPago['fecha']) || empty($ultimoPago['monto'])) {
            $mensajesSugeridos[] = [
                'tipo' => 'WhatsApp',
                'prioridad' => 'media',
                'mensaje' => 'Hola, registramos actividad reciente en su cuenta. ¿Podría confirmar la fecha y el monto de su último pago para actualizar su estado? Gracias.',
            ];
        }
        $mensajesSugeridos[] = [
            'tipo' => 'SMS',
            'prioridad' => 'baja',
            'mensaje' => 'Recordatorio: revise su estado de cuenta. Para más información responda este mensaje o llame al número de atención.',
        ];

        return [
            'id_credito' => $idCredito,
            'analisisEspacial' => $analiticaEspacial,
            'cumplimientoGestor' => $cumplimientoGestor,
            'detallesGestor' => $detalles,
            'analisisPagos' => $analiticaPagos,
            'domicilioConfirmado' => $domicilioConfirmado,
            'distanciaDomicilio' => $distanciaDomicilio,
            'puntoInteres' => $puntoInteres,
            'confianzaEspacial' => $confianzaEspacial,
            'cumplimientoPorc' => $cumplimientoPorc,
            'visitasCercanas' => $visitasCercanas,
            'visitasLejanas' => $visitasLejanas,
            'peorGestor' => $peorGestor,
            'totalPagos' => $totalPagos,
            'intervaloPromedio' => $intervaloPromedio,
            'desviacion' => $desviacion,
            'diaFrecuente' => $diaFrecuente,
            'consistenciaDia' => $consistenciaDia,
            'patronPago' => $patronPago,
            'etiquetaPatronPago' => $etiquetaPatronPago,
            'confianzaGeneral' => $confianzaGeneral,
            'scoreCliente' => $scoreCliente,
            'scoreGestion' => $scoreGestion,
            'scorePagos' => $scorePagos,
            'ultimoPago' => $ultimoPago,
            'datosFaltantes' => $datosFaltantes,
            'accionesRecomendadas' => $accionesRecomendadas,
            'mensajesSugeridos' => $mensajesSugeridos,
        ];
    }

    /**
     * Calcula el gestor con mayor incumplimiento a partir de detalles enriquecidos con gestor_nombre.
     */
    private function calcularPeorGestorDesdeDetalles(array $detalles): ?array
    {
        $porGestor = [];
        foreach ($detalles as $d) {
            $nombre = trim((string) ($d['gestor_nombre'] ?? '—'));
            if ($nombre === '' || $nombre === '—') {
                $nombre = 'N/D';
            }
            if (!isset($porGestor[$nombre])) {
                $porGestor[$nombre] = ['visitas_lejanas' => 0, 'distancia_sum_m' => 0, 'total' => 0];
            }
            $porGestor[$nombre]['total']++;
            if (empty($d['cerca'])) {
                $porGestor[$nombre]['visitas_lejanas']++;
            }
            if (isset($d['distancia_m']) && is_numeric($d['distancia_m'])) {
                $porGestor[$nombre]['distancia_sum_m'] += (float) $d['distancia_m'];
            }
        }
        if (empty($porGestor)) {
            return null;
        }
        $peor = null;
        $peorLejanas = -1;
        foreach ($porGestor as $nombre => $stats) {
            $lejanas = (int) $stats['visitas_lejanas'];
            if ($lejanas > $peorLejanas) {
                $peorLejanas = $lejanas;
                $total = (int) $stats['total'];
                $distanciaPromedioKm = $total > 0 && $stats['distancia_sum_m'] > 0
                    ? round($stats['distancia_sum_m'] / 1000.0 / $total, 2)
                    : 0.0;
                $peor = [
                    'nombre' => $nombre,
                    'distancia_promedio' => $distanciaPromedioKm,
                    'visitas_fuera_rango' => $lejanas,
                ];
            }
        }
        return $peor;
    }
}

