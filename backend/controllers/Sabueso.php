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
        $columnsJson = $this->getColumnsConfig(false);
        $script = '<script>window.sabuesoTicketColumns=' . $columnsJson['columnsJs'] . ';</script>' . "\n"
            . '<script src="/assets/js/sabueso_ticket.js"></script>';

        self::set('titulo', 'Ticket | Sabueso');
        self::set('script', $script);
        self::set('esAdminTicket', false);
        self::render('sabueso_ticket');
    }

    /**
     * Vista Panel Admin: tabla de todos los tickets con columna Quién levantó. Sin botón Levantar ticket ni buscador.
     */
    public function paneladmin()
    {
        $usuarioId = (int)($_SESSION['usuario_id'] ?? 0);
        $personaId = (int)($_SESSION['persona_id'] ?? $usuarioId);
        $usuarioNombre = $usuarioId ? TicketDAO::getNombrePersona($usuarioId) : '';
        $modulos = $_SESSION['modulos'] ?? [];
        $puedeUsarAnalizarIA = is_array($modulos) && in_array(19, $modulos);
        self::set('miUsuarioId', $usuarioId);
        self::set('miUsuarioNombre', $usuarioNombre);
        self::set('miPersonaId', $personaId);
        self::set('puedeUsarAnalizarIA', $puedeUsarAnalizarIA);
        $columnsJson = $this->getColumnsConfig(true);
        $googleMapsKeyJs = json_encode(defined('GOOGLE_MAPS_API_KEY') && (string)GOOGLE_MAPS_API_KEY !== '' ? (string)GOOGLE_MAPS_API_KEY : '', JSON_UNESCAPED_SLASHES);
        $script = <<<SCRIPT
        <script>
        var esAdminTicket = true;
        var ticketIdRastreoActual = null;
        var idCreditoRastreoActual = null;
        var rastreoDireccionesParaMapa = [];
        var rastreoDomicilioMegareporte = null;
        var rastreoIndiceCasa = null;
        var rastreoCentrarEnPunto = null;
        var rastreoMarkersMaxiApp = [];
        var rastreoMapaLeaflet = null;
        var rastreoMapaGrande = null;
        var googleMapsApiKey = {$googleMapsKeyJs};
        var rastreoDatosClienteActual = { nombre: '', credito: '', telefono: '', direccion: '' };
        var coloresPuntosMapa = ['#dc2626', '#2563eb', '#16a34a', '#ea580c', '#7c3aed', '#0891b2', '#65a30d', '#db2777'];
        function haversineMetrosMapa(lat1, lon1, lat2, lon2) {
            var R = 6371000, dLat = (lat2 - lat1) * Math.PI / 180, dLon = (lon2 - lon1) * Math.PI / 180;
            var a = Math.sin(dLat/2)*Math.sin(dLat/2) + Math.cos(lat1*Math.PI/180)*Math.cos(lat2*Math.PI/180)*Math.sin(dLon/2)*Math.sin(dLon/2);
            return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        }
        function formatDistanciaMapa(m) { if (m >= 1000) return (Math.round(m/100)/10) + ' km'; return Math.round(m) + ' m'; }
        var rastreoUltimoResumenUbicaciones = '';
        var rastreoUltimoResumenGestiones = '';
        var rastreoUltimoAnalizarIA = '';
        var rastreoTicketInfoBase = '';
        var rastreoMapaAlternas = null;
        var rastreoMapaAlternasGrande = null;
        var rastreoGestionesParaMapa = [];
        var rastreoGestionesCargadas = false;
        var rastreoTotalGestiones = 0;
        var rastreoConUbicacion = 0;
        var rastreoPuntosGeo = [];
        var rastreoCentrarEnGeoAlternasIndice = null;
        var rastreoMarkersGeoAlternasGrande = [];
        var rastreoInfoWindowsGeoAlternasGrande = [];
        var rastreoMarkersPorGestorAlternas = {};
        var rastreoMarkersPorGestorAlternasGrande = {};
        var rastreoPolylineAlternas = null;
        var rastreoPolylineAlternasGrande = null;
        var rastreoPolylinePathRawAlternas = [];
        var rastreoPolylinePathRawAlternasGrande = [];
        var rastreoPolylinesPorGestorAlternas = {};
        var rastreoPolylinesPorGestorAlternasGrande = {};
        var rastreoFiltroGestorActual = '';
        var rastreoFiltroGestoresSeleccionados = [];
        var rastreoColoresPorGestor = {};
        var rastreoPaletaColoresGestores = ["#ea580c", "#dc2626", "#9333ea", "#0891b2", "#ca8a04", "#db2777", "#0d9488", "#64748b"];
SCRIPT;
        $script .= <<<'JS'
        function sabuesoCloseDondeDetalle(){
            try {
                var b = document.getElementById('rastreoDondeDetalle');
                var c = document.getElementById('rastreoDondeChevron');
                if (b) {
                    b.style.display = 'none';
                    b.style.position = '';
                    b.style.top = '';
                    b.style.left = '';
                    b.style.width = '';
                }
                if (c) c.className = 'fa fa-chevron-down text-muted small';
                if (window.__sabuesoDondeOutsideHandler) {
                    document.removeEventListener('mousedown', window.__sabuesoDondeOutsideHandler, true);
                    window.__sabuesoDondeOutsideHandler = null;
                }
            } catch(e) {}
        }

        function sabuesoToggleDondeDetalle(idDetalle,idChevron){
            var b=document.getElementById(idDetalle);
            var c=document.getElementById(idChevron);
            if(!b||!c) return;
            var resumen = c.closest ? c.closest('.sabueso-donde-resumen') : (c.parentElement || null);
            var isOpen=b.style.display!=='none'&&b.offsetParent!==null;
            if(isOpen){ sabuesoCloseDondeDetalle(); return; }
            sabuesoCloseDondeDetalle();
            var r = resumen ? resumen.getBoundingClientRect() : c.getBoundingClientRect();
            b.style.display='block';
            b.style.position='fixed';
            var panelW = 360;
            var left = r.left;
            if (left + panelW > (window.innerWidth - 8)) left = window.innerWidth - panelW - 8;
            if (left < 8) left = 8;
            b.style.left=left+'px';
            b.style.top=(r.bottom-10)+'px';
            b.style.width=panelW+'px';
            b.style.zIndex='1060';
            var isDark = (document.body && document.body.classList.contains('dark-mode')) || (document.documentElement && document.documentElement.classList.contains('dark-mode'));
            if (isDark) {
                b.style.backgroundColor = '#1e293b';
                b.style.color = '#f1f5f9';
                b.style.borderColor = '#334155';
                var lb = b.querySelectorAll('.sabueso-donde-label');
                var vl = b.querySelectorAll('.sabueso-donde-valor');
                for (var i=0;i<lb.length;i++) lb[i].style.color = '#94a3b8';
                for (var j=0;j<vl.length;j++) vl[j].style.color = '#e2e8f0';
            } else {
                b.style.backgroundColor = '#f8f9fa';
                b.style.color = '#212529';
                b.style.borderColor = '#dee2e6';
                var lb2 = b.querySelectorAll('.sabueso-donde-label');
                var vl2 = b.querySelectorAll('.sabueso-donde-valor');
                for (var k=0;k<lb2.length;k++) lb2[k].style.color = '#6c757d';
                for (var n=0;n<vl2.length;n++) vl2[n].style.color = '#212529';
            }
            c.className='fa fa-chevron-up text-muted small';
            window.__sabuesoDondeOutsideHandler = function(ev){
                var t = ev.target;
                if ((b && b.contains(t)) || (resumen && resumen.contains(t))) return;
                sabuesoCloseDondeDetalle();
            };
            document.addEventListener('mousedown', window.__sabuesoDondeOutsideHandler, true);
        }

        function sabuesoAppendInformacionIngresos(el,d,esc){
            if(!el) return;
            esc=esc||function(s){ var x=String(s).split("&").join("&amp;").split("<").join("&lt;").split(">").join("&gt;"); return x.split(String.fromCharCode(34)).join("&quot;"); };
            if(d._fad_debug){
                var msg='No se pudo cargar información de ingresos (FAD): '+d._fad_debug;
                if(d._fad_debug_detail) msg+=' — '+esc(d._fad_debug_detail);
                el.innerHTML+="<div style=\"grid-column:1/-1;margin-top:0.5rem;\"><span class=\"text-muted small d-block\">Aviso</span><div class=\"small text-warning\">"+msg+"</div></div>";
                return;
            }

            var txt = (d.informacion_ingresos && String(d.informacion_ingresos).trim()) ? String(d.informacion_ingresos).replace(/\r/g,'').trim() : "";
            if (txt) txt = txt.split(/\n(?=REFERENCIAS?|CONFIRMACIONES)/i)[0].trim();

            var pick = function(labels){
                if(!txt) return "";
                for (var i=0;i<labels.length;i++){
                    var re = new RegExp(labels[i]+"\\s*[:\\-]?\\s*([^\\n]+)","i");
                    var m = txt.match(re);
                    if(m && m[1]){
                        var v = m[1].trim();
                        if(v && v !== '-' && v.toUpperCase() !== 'N/A') return v;
                    }
                }
                return "";
            };

            var empleado = (d.empleado && String(d.empleado).trim()) ? String(d.empleado).trim() : pick(["Empleado"]);
            var cuenta = pick(["Trabaja por su cuenta"]);
            var giro = pick(["Giro del negocio"]);
            var empresa = (d.empresa && String(d.empresa).trim()) ? String(d.empresa).trim() : pick(["Nombre de la Empresa o Negocio","Nombre de la Empresa","Empresa o Negocio"]);
            var tel = (d.telefono_laboral && String(d.telefono_laboral).trim()) ? String(d.telefono_laboral).trim() : pick(["Tel a 10 Digitos","Tel 1"]);
            var ingreso = (d.ingreso_mensual_neto && String(d.ingreso_mensual_neto).trim()) ? String(d.ingreso_mensual_neto).trim() : pick(["Ingreso mensual Neto","Ingreso mensual"]);

            var resumen = empresa || cuenta || empleado || "";
            var hasActividad = !!(empresa || empleado || cuenta || giro || tel || ingreso || txt);
            if(!hasActividad) return;

            var detalle = "";
            if (empleado) detalle += "<div class=\"mb-1\"><span class=\"sabueso-donde-label d-block small\">Empleado</span><span class=\"sabueso-donde-valor fw-medium\">"+esc(empleado)+"</span></div>";
            if (cuenta) detalle += "<div class=\"mb-1\"><span class=\"sabueso-donde-label d-block small\">Trabaja por su cuenta</span><span class=\"sabueso-donde-valor fw-medium\">"+esc(cuenta)+"</span></div>";
            if (giro) detalle += "<div class=\"mb-1\"><span class=\"sabueso-donde-label d-block small\">Giro del negocio</span><span class=\"sabueso-donde-valor fw-medium\">"+esc(giro)+"</span></div>";
            if (empresa) detalle += "<div class=\"mb-1\"><span class=\"sabueso-donde-label d-block small\">Nombre de la empresa o negocio</span><span class=\"sabueso-donde-valor fw-medium\">"+esc(empresa)+"</span></div>";
            if (tel) detalle += "<div class=\"mb-1\"><span class=\"sabueso-donde-label d-block small\">Teléfono</span><span class=\"sabueso-donde-valor fw-medium\">"+esc(tel)+"</span></div>";
            if (ingreso) detalle += "<div class=\"mb-0\"><span class=\"sabueso-donde-label d-block small\">Ingreso mensual neto</span><span class=\"sabueso-donde-valor fw-medium\">"+esc(ingreso)+"</span></div>";
            if (!detalle && txt) detalle = "<div class=\"sabueso-donde-valor fw-medium\" style=\"white-space:pre-wrap;line-height:1.4;\">"+esc(txt)+"</div>";
            if (!detalle) return;

            var idDetalle='rastreoDondeDetalle';
            var idChevron='rastreoDondeChevron';
            var html="<div style=\"grid-column:1/-1;margin-top:0.5rem;position:relative;\">"
                + "<div class=\"sabueso-donde-resumen d-flex align-items-center gap-1 sabueso-donde-clickable\" role=\"button\" tabindex=\"0\" onclick=\"sabuesoToggleDondeDetalle('"+idDetalle+"','"+idChevron+"')\">"
                + "<span class=\"text-muted small\">Donde trabaja:</span> <span class=\"fw-semibold\">"+esc(resumen || 'Actividad laboral')+"</span>"
                + " <i class=\"fa fa-chevron-down text-muted small\" id=\""+idChevron+"\" aria-hidden=\"true\"></i></div>"
                + "<div id=\""+idDetalle+"\" class=\"sabueso-donde-detalle-panel\" style=\"display:none;padding:0.75rem;background-color:#f8f9fa;color:#212529;opacity:1;border-radius:0.375rem;font-size:0.9rem;border:1px solid #dee2e6;border-left:3px solid #696cff;box-shadow:0 0.5rem 1rem rgba(0,0,0,0.15);max-height:320px;overflow-y:auto;\">"
                + detalle
                + "</div></div>";
            el.innerHTML += html;
        }
JS;
        $script .= "\n\n        function actualizarCountdownsDictamen(selector) {\n            var sel = selector || '#tablaTicketsPanel';\n            $(sel + ' .dictamen-countdown').each(function() {\n                var el = this;\n                var fLim = $(el).attr('data-fecha-limite');\n                var f = $(el).attr('data-fecha-envio');\n                if (!f && !fLim) return;\n                var fin;\n                if (fLim) { fin = new Date(fLim); }\n                else { var envio = new Date(f); fin = new Date(envio.getTime() + 12 * 60 * 60 * 1000); }\n                var now = new Date();\n                var ms = fin - now;\n                var txt = '-';\n                var txtCorto = '-';\n                var expired = ms <= 0;\n                if (ms > 0) {\n                    var h = Math.floor(ms / 3600000);\n                    var m = Math.floor((ms % 3600000) / 60000);\n                    var pref = fLim ? 'Prórroga · ' : '';
                    txt = pref + 'Tiempo restante: ' + h + 'h ' + m + 'm';\n                    txtCorto = (fLim ? 'P2 ' : '') + h + 'h ' + m + 'm';\n                } else {\n                    txt = fLim ? 'Prórroga vencida' : 'Plazo vencido';\n                    txtCorto = txt;\n                }\n                $(el).attr('title', txt).attr('data-bs-title', txt).toggleClass('text-danger', expired);\n                var txtEl = $(el).find('.dictamen-countdown-text');\n                if (txtEl.length) txtEl.text(txtCorto).toggleClass('text-danger', expired);\n            });\n        }\n        function attrEsc(s){ if (s==null||s===undefined) return ''; var x=(s+'').split('&').join('&amp;').split('<').join('&lt;'); return x.split('\"').join('&quot;'); }\n        $(document).ready(function() {\n            configuraTabla(\"#tablaTicketsPanel\", {\n                registrosPorPagina: 10,\n                order: [[1, 'desc']],\n                columns: " . $columnsJson['columnsJs'] . "\n            });\n            getTicketsPanelAdmin();\n        });\n\n        function getTicketsPanelAdmin() {\n            http.request({\n                endpoint: \"/sabueso/getTicketsPanelAdmin\",\n                metodo: \"POST\",\n                onSuccess: function(resp) {\n                    var datos = (resp.datos || []).map(function(t) {\n                        var fechaCreacion = t.fecha_creacion ? new Date(t.fecha_creacion).toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '—';\n                        var fechaVenc = t.fecha_vencimiento ? new Date(t.fecha_vencimiento).toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '—';\n                        var prioridadNombre = (t.prioridad_nombre || '').toLowerCase();\n                        var prioridadBadge = '<span class=\"badge bg-label-secondary\">' + (t.prioridad_nombre || '—') + '</span>';\n                        if (prioridadNombre.indexOf('alta') !== -1) prioridadBadge = '<span class=\"badge bg-danger text-white\">' + (t.prioridad_nombre || '—') + '</span>';\n                        else if (prioridadNombre.indexOf('medio') !== -1 || prioridadNombre.indexOf('media') !== -1) prioridadBadge = '<span class=\"badge\" style=\"background-color:#fd7e14;color:#fff;\">' + (t.prioridad_nombre || '—') + '</span>';\n                        else if (prioridadNombre.indexOf('bajo') !== -1 || prioridadNombre.indexOf('baja') !== -1) prioridadBadge = '<span class=\"badge\" style=\"background-color:#ffc107;color:#212529;\">' + (t.prioridad_nombre || '—') + '</span>';\n                        else if (prioridadNombre.indexOf('sin prioridad') !== -1) prioridadBadge = '<span class=\"badge bg-secondary\" style=\"background-color:#6c757d!important;color:#fff;\">' + (t.prioridad_nombre || '—') + '</span>';\n                        var estadoBadge = (t.asignado_nombre && (t.asignado_nombre + '').trim()) ? '<span class=\"badge bg-success text-white\">Asignado</span>' : '<span class=\"badge bg-label-secondary\">Abierto</span>';\n                        var vistoHtml = '';\n                        if ((t.dictamen_estado || '') === 'enviado_al_gestor') {\n                            var vistoTexto = t.dictamen_fecha_visto ? (new Date(t.dictamen_fecha_visto).toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' }) + ' ' + new Date(t.dictamen_fecha_visto).toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' })) : 'No visto';\n                            var iconoOjo = (t.dictamen_fecha_visto && (t.dictamen_fecha_visto + '').trim()) ? 'fa-eye' : 'fa-eye-slash';\n                            var tituloOjo = (t.dictamen_fecha_visto && (t.dictamen_fecha_visto + '').trim()) ? ('Visto: ' + vistoTexto) : 'No visto. Clic para ver dictamen';\n                            vistoHtml = '<span class=\"d-inline-flex align-items-center gap-1 justify-content-end btn-dictamen-ojito\" role=\"button\" tabindex=\"0\" data-bs-toggle=\"tooltip\" data-bs-title=\"' + (tituloOjo + '').replace(/\\x22/g, '&quot;') + '\" data-id-ticket=\"' + (t.id_ticket || '') + '\"><i class=\"fa ' + iconoOjo + ' text-info small\"></i></span>';\n                        }\n                        var tiempoVisitarHtml = '—';\n                        var fEnv = (t.dictamen_fecha_envio || '').trim();\n                        var esNuevo = fEnv && new Date(fEnv) >= new Date('2026-03-09T00:00:00');\n                        if ((t.dictamen_estado || '') === 'enviado_al_gestor' && esNuevo && fEnv) {\n                            var envio = new Date(fEnv);\n                            var fLim = (t.prorroga_fecha_limite || '').trim();\n                            var esProrroga = t.prorroga_activa && fLim;\n                            var fin = esProrroga ? new Date(fLim) : new Date(envio.getTime() + 12 * 60 * 60 * 1000);\n                            var now = new Date();\n                            var ms = fin - now;\n                            var txtInicial = ms > 0 ? (Math.floor(ms / 3600000) + 'h ' + Math.floor((ms % 3600000) / 60000) + 'm') : 'Plazo vencido';\n                            var clsPr = esProrroga ? ' dictamen-countdown-prorroga' : '';\n                            var dataLim = esProrroga ? (' data-fecha-limite=\"' + fLim.replace(/\"/g, '&quot;') + '\"') : '';\n                            var iconBlock = esProrroga ? ('<span class=\"position-relative d-inline-flex align-items-baseline\"><i class=\"fa-solid fa-clock text-warning small\"></i><sup class=\"dictamen-prorroga-marca\" title=\"Prórroga +12h (2ª ventana)\">2</sup></span>') : ('<i class=\"fa-solid fa-clock text-info small\"></i>');\n                            tiempoVisitarHtml = '<span class=\"d-inline-flex align-items-center gap-1 dictamen-countdown cursor-pointer' + clsPr + '\" role=\"button\" tabindex=\"0\" data-fecha-envio=\"' + fEnv.replace(/\"/g, '&quot;') + '\"' + dataLim + ' data-id-ticket=\"' + (t.id_ticket || '') + '\" data-bs-toggle=\"tooltip\" data-bs-title=\"' + (esProrroga ? 'Prórroga +12h · tiempo hasta límite' : 'Ventana 12h desde envío') + '\">' + iconBlock + '<span class=\"dictamen-countdown-text\">' + txtInicial + '</span></span>';\n                        }\n                        var row = {\n                            _fecha_creacion: (t.fecha_creacion || ''),\n                            folio_tipo: '<div class=\"fw-semibold\">' + (t.folio || '—') + '</div><div class=\"small text-muted mt-1\">' + (t.tipo_ticket_nombre || '—') + '</div>',\n                            estado: estadoBadge,\n                            prioridad: prioridadBadge,\n                            credito: '<small>#' + (t.id_credito != null ? t.id_credito : '—') + '</small>',\n                            fechas: '<div class=\"small d-flex align-items-center gap-1\"><i class=\"fa fa-calendar-plus-o text-muted\" style=\"width: 1rem;\"></i><span>Creación: ' + fechaCreacion + '</span></div><div class=\"small text-muted d-flex align-items-center gap-1 mt-1\"><i class=\"fa fa-calendar-times-o\" style=\"width: 1rem;\"></i><span>Vencimiento: ' + fechaVenc + '</span></div>',\n                            creador: '<small class=\"d-flex align-items-center gap-1\"><i class=\"fa fa-user\"></i>' + (t.creador_nombre || '—') + '</small>',\n                            asignado: (t.asignado_nombre && t.asignado_nombre.trim()) ? '<small class=\"d-flex align-items-center gap-1\"><i class=\"fa fa-user-check text-success\"></i>' + t.asignado_nombre + '</small>' : '<span class=\"text-muted\">—</span>',\n                            tiempo_visitar: tiempoVisitarHtml,\n                            ds_resultado: (t.ds_resultado_html != null && t.ds_resultado_html !== '') ? t.ds_resultado_html : '—',\n                            prorroga: (t.prorroga_html != null && t.prorroga_html !== '') ? t.prorroga_html : '—',\n                            dictamen_visto: vistoHtml,\n                            acciones: (function(){ var fEnvDS = (t.dictamen_fecha_envio || '').trim(); var ticketDesdeMarzo10 = t.fecha_creacion && (new Date(t.fecha_creacion) >= new Date(2026, 2, 10)); var plazoVencido = false; if (fEnvDS && ticketDesdeMarzo10 && (t.dictamen_estado || '') === 'enviado_al_gestor') { var plazoDS = new Date(fEnvDS).getTime() + 12*60*60*1000; plazoVencido = (Date.now() >= plazoDS); } var btns = '<div class=\"d-flex flex-column gap-1 align-items-stretch\" style=\"min-width:2.5rem;\"><button class=\"btn btn-sm btn-primary btn-rastreo\" onclick=\"abrirRastreo(this)\" data-id-credito=\"' + (t.id_credito != null ? t.id_credito : 0) + '\" data-id-ticket=\"' + (t.id_ticket) + '\" data-asignado=\"' + attrEsc(t.asignado_nombre) + '\" data-creador-nombre=\"' + attrEsc(t.creador_nombre) + '\" data-fecha-creacion=\"' + attrEsc(t.fecha_creacion) + '\" title=\"Iniciar rastreo\"><i class=\"fa-solid fa-magnifying-glass-plus\"></i></button>'; if (plazoVencido) { btns += '<button type=\"button\" class=\"btn btn-sm btn-warning btn-dictamen-sistema\" onclick=\"abrirDictamenSistema(' + t.id_ticket + ')\" title=\"Dictamen del sistema\"><i class=\"fa-solid fa-robot\"></i></button>'; } btns += '<button class=\"btn btn-sm btn-secondary\" onclick=\"cerrarTicketPanel(' + (t.id_ticket) + ')\" title=\"Cerrar ticket\"><i class=\"fa fa-minus\"></i></button><button class=\"btn btn-sm btn-danger\" onclick=\"eliminarTicketPanel(' + (t.id_ticket) + ')\" title=\"Eliminar ticket\"><i class=\"fa fa-trash\"></i></button></div>'; return btns; })(),\n                            _id_ticket: t.id_ticket,\n                            _dictamen_estado: t.dictamen_estado || '',\n                            _dictamen_fecha_visto: t.dictamen_fecha_visto || ''\n                        };\n                        return row;\n                    });\n                    var tabla = $('#tablaTicketsPanel').DataTable();\n                    tabla.clear().rows.add(datos).draw();\n                    tabla.rows().every(function() {\n                        var d = this.data();\n                        if (d._dictamen_estado === 'enviado_al_gestor') {\n                            $(this.node()).addClass('fila-dictamen-enviado').attr('data-id-ticket', d._id_ticket || '');\n                        }\n                    });\n                    if (typeof actualizarCountdownsDictamen === 'function') actualizarCountdownsDictamen('#tablaTicketsPanel');\n                    $('#tablaTicketsPanel [data-bs-toggle=\"tooltip\"]').tooltip();\n                },\n                onError: function() {\n                    var tabla = $('#tablaTicketsPanel').DataTable();\n                    tabla.clear().draw();\n                }\n            });\n        }\n        setInterval(function() { if (typeof actualizarCountdownsDictamen === 'function') actualizarCountdownsDictamen('#tablaTicketsPanel'); }, 1000);\n        function abrirRastreo(btn) {\n            var idCredito = parseInt(btn.getAttribute('data-id-credito')||0, 10);\n            var idTicket = parseInt(btn.getAttribute('data-id-ticket')||0, 10);\n            var asignadoNombre = (btn.getAttribute('data-asignado')||'').trim();\n            var creadorNombre = (btn.getAttribute('data-creador-nombre')||'').trim();\n            var fechaCreacionRaw = (btn.getAttribute('data-fecha-creacion')||'').trim();\n            var fechaCreacionDisplay = fechaCreacionRaw ? (new Date(fechaCreacionRaw).toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' }) + ' ' + new Date(fechaCreacionRaw).toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' })) : '—';\n            ticketIdRastreoActual = idTicket || null;\n            if (!idCredito || isNaN(idCredito)) { Swal.fire({ icon: 'warning', title: 'Rastreo', text: 'No hay ID de crédito para este ticket.' }); return; }\n            http.request({\n                endpoint: \"/sabueso/getDatosCredito\",\n                metodo: \"POST\",\n                data: JSON.stringify({ id_credito: idCredito }),\n                contentType: \"application/json\",\n                processData: false,\n                showLoader: true,\n                onSuccess: function(resp) {\n                    var d = resp.datos || null;\n                    if (!d) { var msg = (resp.mensaje || 'No se encontraron datos para este crédito.'); $('#rastreoTopLeft').html('<div class=\"alert alert-warning mb-0\"><strong>Crédito #' + idCredito + '</strong><br>' + msg + '<br><small>El crédito debe existir en Segundometro u Oferta para ver el rastreo.</small></div>'); $('#rastreoTopRight').html(''); $('#rastreoTickets').html(''); $('#rastreoDireccionesContenido').html(''); idCreditoRastreoActual = idCredito; window.ticketIdRastreoActual = ticketIdRastreoActual; $('#modalRastreoCredito').attr('data-id-ticket', ticketIdRastreoActual || ''); $('#rastreoIdTicketActual').val(ticketIdRastreoActual || '').attr('data-id-ticket', ticketIdRastreoActual || ''); $('#modalRastreoCredito').modal('show'); return; }\n                    var esc = function(s) { var x = (s + '').split('&').join('&amp;').split('<').join('&lt;').split('>').join('&gt;'); return x.split(String.fromCharCode(34)).join('&quot;'); };\n                    var idCred = (d.id_credito || d.Id_credito || '—');\n                    var nombreCompleto = esc(d.Nombre_cliente || d.nombre_completo || '—');\n                    var tel = (d.telefono_referencia1 || d.telefono_referencia2 || '').trim();\n                    var telEsc = tel ? esc(tel) : '—';\n                    var dirMegareporte = (d.Domicilio_Completo && (d.Domicilio_Completo + '').trim()) ? esc(d.Domicilio_Completo) : '—';
                    rastreoTicketInfoBase = '<div class=\"rastreo-ticket-info-col\"><span class=\"text-muted small d-block\">Quién levantó el ticket</span><div class=\"fw-medium\">' + (creadorNombre ? esc(creadorNombre) : '—') + '</div><span class=\"text-muted small d-block mt-1\">Cuando se levantó</span><div class=\"fw-medium\">' + fechaCreacionDisplay + '</div><span class=\"text-muted small d-block mt-1\">Asignado a</span><div id=\"rastreoAsignadoBlock\" class=\"fw-medium\"><span class=\"text-muted\">Cargando...</span></div></div>';\n                    var htmlTicketInfo = rastreoTicketInfoBase;\n                    var htmlTopLeft = '<div><span class=\"text-muted small d-block\">ID crédito</span><div class=\"fw-semibold\">' + idCred + '</div></div><div><span class=\"text-muted small d-block\">Nombre completo</span><div class=\"fw-semibold\">' + nombreCompleto + '</div></div><div><span class=\"text-muted small d-block\">Teléfono cliente</span><div class=\"fw-semibold\">' + telEsc + '</div></div><div><span class=\"text-muted small d-block\">Dirección megareporte</span><div class=\"fw-semibold small\">' + dirMegareporte + '</div></div>';\n                    var dirContenido = (d.Domicilio_Completo && (d.Domicilio_Completo + '').trim()) ? esc(d.Domicilio_Completo) : '<span class=\"text-muted\">No hay direcciones registradas</span>';\n                    var tickets = d.tickets || [];\n                    var ticketActual = tickets.filter(function(tk) { return tk.id_ticket == ticketIdRastreoActual; })[0];\n                    var htmlTickets = '';\n                    if (ticketActual) {\n                        var fCreacion = ticketActual.fecha_creacion ? new Date(ticketActual.fecha_creacion).toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '—';\n                        var fVenc = ticketActual.fecha_vencimiento ? new Date(ticketActual.fecha_vencimiento).toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '—';\n                        htmlTickets = '<div class=\"small bg-light rounded p-2 mb-2\"><strong>' + esc(ticketActual.folio || '—') + '</strong> · ' + esc(ticketActual.tipo_nombre || '') + ' · ' + esc(ticketActual.estado_nombre || '') + '<br><span class=\"text-muted small\">Descripción:</span> ' + esc(ticketActual.descripcion_inicial || '—') + '<br>Creación: ' + fCreacion + ' · Venc: ' + fVenc + '</div>';\n                    } else { htmlTickets = '<span class=\"text-muted small\">Ticket actual (sin detalle adicional).</span>'; }\n                    $('#rastreoTopLeft').html(htmlTopLeft); if(typeof sabuesoAppendInformacionIngresos==='function')sabuesoAppendInformacionIngresos(document.getElementById('rastreoTopLeft'),d,esc); $('#rastreoTopRight').html(htmlTicketInfo);\n                    loadHistorialAsignacionTicket(ticketIdRastreoActual);\n                    $('#rastreoTickets').html(htmlTickets);\n                    $('#rastreoDireccionesContenido').html('<span class=\"text-muted\">Cargando direcciones...</span>');\n                    rastreoDireccionesParaMapa = [];\n                    $('#btnAsignarRastreo').html('<i class=\"fa-solid fa-user-plus me-1\"></i>Asignar...');\n                    idCreditoRastreoActual = idCredito;\n                    rastreoDatosClienteActual = { nombre: (d.Nombre_cliente || d.nombre_completo || '—'), credito: idCred, telefono: (tel || '—'), direccion: (d.Domicilio_Completo || '—') };\n                    var kA = \"sabueso_ia_\" + idCredito + \"_\" + (idTicket || 0) + \"_analizar\"; var kU = \"sabueso_ia_\" + idCredito + \"_ubicaciones\"; var kG = \"sabueso_ia_\" + idCredito + \"_gestiones\";\n                    try { if (typeof localStorage !== \"undefined\") { rastreoUltimoAnalizarIA = localStorage.getItem(kA) || \"\"; rastreoUltimoResumenUbicaciones = localStorage.getItem(kU) || \"\"; rastreoUltimoResumenGestiones = localStorage.getItem(kG) || \"\"; } else { rastreoUltimoAnalizarIA = \"\"; rastreoUltimoResumenUbicaciones = \"\"; rastreoUltimoResumenGestiones = \"\"; } } catch (e) { rastreoUltimoAnalizarIA = \"\"; rastreoUltimoResumenUbicaciones = \"\"; rastreoUltimoResumenGestiones = \"\"; }\n                    if (rastreoUltimoAnalizarIA) { \$(\"#btnLecturaIAAnalizar\").show(); \$(\"#btnBorrarIAAnalizar\").show(); } else { \$(\"#btnLecturaIAAnalizar\").hide(); \$(\"#btnBorrarIAAnalizar\").hide(); }\n                    if (rastreoUltimoResumenUbicaciones) { \$(\"#btnLecturaIAUbicaciones\").show(); \$(\"#btnBorrarIAUbicaciones\").show(); } else { \$(\"#btnLecturaIAUbicaciones\").hide(); \$(\"#btnBorrarIAUbicaciones\").hide(); }\n                    if (rastreoUltimoResumenGestiones) { \$(\"#btnLecturaIAGestiones\").show(); \$(\"#btnBorrarIAGestiones\").show(); } else { \$(\"#btnLecturaIAGestiones\").hide(); \$(\"#btnBorrarIAGestiones\").hide(); }\n                    window.ticketIdRastreoActual = ticketIdRastreoActual; \$(\"#modalRastreoCredito\").attr(\"data-id-ticket\", ticketIdRastreoActual || \"\"); \$(\"#rastreoIdTicketActual\").val(ticketIdRastreoActual || \"\").attr(\"data-id-ticket\", ticketIdRastreoActual || \"\"); \$(\"#modalRastreoCredito\").modal(\"show\");\n                },\n                onError: function(err) {\n                    var errMsg = (typeof err === 'string' ? err : (err && err.mensaje)) || 'No se pudieron cargar los datos del crédito.';\n                    Swal.fire({ icon: 'error', title: 'Rastreo', text: errMsg });\n                }\n            });\n        }\n        function tooltipHistorialAsignacion(estado, historial) {\n            if (estado === 'primera_asignacion') return 'Es la primera asignación de este ticket.';\n            var lineas = ['Historial de asignación (este ticket)'];\n            (historial || []).forEach(function(h) { lineas.push('• ' + (h.persona || '—') + ': ' + (h.duracion_humana || '—')); });\n            if (estado === 'sin_asignar') lineas.push('Actualmente sin persona asignada a este ticket.');\n            return lineas.join('\\n');\n        }\n        function loadHistorialAsignacionTicket(idTicket) {\n            if (!idTicket) return;\n            http.request({ endpoint: '/sabueso/getHistorialAsignacionTicket', metodo: 'POST', data: JSON.stringify({ id_ticket: idTicket }), contentType: 'application/json', processData: false, onSuccess: function(r) {\n                var asignado = r.asignado_actual || null;\n                var estado = r.estado || 'primera_asignacion';\n                var historial = r.historial || [];\n                var tooltipTxt = tooltipHistorialAsignacion(estado, historial);\n                var tooltipEsc = (tooltipTxt + '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/\"/g, '&quot;');\n                var tooltipAttr = tooltipEsc.replace(/\\n/g, '<br>');\n                var html = asignado ? ('<i class=\"fa-solid fa-user-check text-success me-1\"></i>' + (asignado.replace(/&/g, '&amp;').replace(/</g, '&lt;')) + ' <i class=\"fa-solid fa-circle-info ms-1\" role=\"img\" aria-label=\"Historial\" data-bs-toggle=\"tooltip\" data-bs-html=\"true\" data-bs-title=\"' + tooltipAttr + '\"></i>') : ('<span class=\"text-muted\">Sin asignar</span> <i class=\"fa-solid fa-circle-info ms-1\" role=\"img\" aria-label=\"Historial\" data-bs-toggle=\"tooltip\" data-bs-html=\"true\" data-bs-title=\"' + tooltipAttr + '\"></i>');\n                var bloque = $('#rastreoAsignadoBlock');\n                if (bloque.length) bloque.html(html);\n                if (asignado) { if (!$('#rastreoAsignadoBlock').next('.btn').length) $('#rastreoAsignadoBlock').after('<button type=\"button\" class=\"btn btn-sm btn-outline-danger mt-1\" onclick=\"quitarAsignacionRastreo()\" title=\"Quitar asignación\">Quitar asignación</button>'); } else { $('#rastreoAsignadoBlock').next('.btn').remove(); }\n                $('#btnAsignarRastreo').html(asignado ? '<i class=\"fa-solid fa-user-pen me-1\"></i>Reasignar a...' : '<i class=\"fa-solid fa-user-plus me-1\"></i>Asignar...');\n                if (typeof $().tooltip === 'function') { $('#rastreoAsignadoBlock [data-bs-toggle=\"tooltip\"]').tooltip(); }\n            } });\n        }\n        function mostrarAsignarOpciones() {\n            if (!ticketIdRastreoActual) { Swal.fire({ icon: 'warning', title: 'Asignar', text: 'No hay ticket seleccionado.' }); return; }\n            Swal.fire({ title: 'Asignar ticket', text: '¿A quién desea asignar este ticket?', icon: 'question', showDenyButton: true, showCancelButton: true, confirmButtonText: 'Tomar asignación', denyButtonText: 'Asignar a...', cancelButtonText: 'Cancelar' }).then(function(res) {\n                if (res.isConfirmed) asignarTicketA(miUsuarioId);\n                else if (res.isDenied) abrirModalAsignarA();\n            });\n        }\n        function asignarTicketA(idPersona) {\n            if (!ticketIdRastreoActual || !idPersona) return;\n            http.request({ endpoint: \"/sabueso/asignarTicket\", metodo: \"POST\", data: JSON.stringify({ id_ticket: ticketIdRastreoActual, id_persona: idPersona }), contentType: \"application/json\", processData: false, onSuccess: function(r) {\n                Swal.fire({ icon: 'success', title: 'Asignado', text: r.mensaje || 'Ticket asignado.' });\n                $('#modalRastreoCredito, #modalAsignarA').modal('hide');\n                ticketIdRastreoActual = null;\n                getTicketsPanelAdmin();\n            }, onError: function(e) { Swal.fire({ icon: 'error', title: 'Error', text: (e && e.mensaje) || 'No se pudo asignar.' }); } });\n        }\n        function quitarAsignacionRastreo() {\n            if (!ticketIdRastreoActual) return;\n            if (typeof Swal !== 'undefined') {\n                Swal.fire({ title: '¿Quitar asignación?', text: 'El ticket quedará sin persona asignada.', icon: 'question', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#6c757d', confirmButtonText: 'Sí, quitar' }).then(function(res) {\n                    if (!res.isConfirmed) return;\n                    http.request({ endpoint: '/sabueso/quitarAsignacionTicket', metodo: 'POST', data: JSON.stringify({ id_ticket: ticketIdRastreoActual }), contentType: 'application/json', processData: false, onSuccess: function(r) {\n                        if (r.success) { Swal.fire({ icon: 'success', title: 'Listo', text: r.mensaje || 'Asignación quitada.' }); if (ticketIdRastreoActual) loadHistorialAsignacionTicket(ticketIdRastreoActual); getTicketsPanelAdmin(); } else { if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'Error', text: r.mensaje || 'No se pudo quitar.' }); }\n                    }, onError: function(e) { if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'Error', text: (e && e.mensaje) || 'No se pudo quitar.' }); } });\n                });\n            } else {\n                http.request({ endpoint: '/sabueso/quitarAsignacionTicket', metodo: 'POST', data: JSON.stringify({ id_ticket: ticketIdRastreoActual }), contentType: 'application/json', processData: false, onSuccess: function(r) { if (r.success) { if (idCreditoRastreoActual) loadHistorialAsignacion(idCreditoRastreoActual); getTicketsPanelAdmin(); } } });\n            }\n        }\n        function abrirModalAsignarA() {\n            http.request({ endpoint: \"/sabueso/getPersonasSabueso\", metodo: \"POST\", onSuccess: function(resp) {\n                var list = resp.datos || [];\n                var html = list.length ? list.map(function(p) { return '<div class=\"d-flex justify-content-between align-items-center py-2 border-bottom\"><span>' + (p.nombre_completo || p.id) + '</span><button type=\"button\" class=\"btn btn-sm btn-primary\" onclick=\"asignarTicketA(' + p.id + ')\">Asignárselo</button></div>'; }).join('') : '<p class=\"text-muted mb-0\">No hay personas en el departamento Sabueso.</p>';\n                $('#modalAsignarABody').html(html);\n                $('#modalRastreoCredito').modal('hide');\n                $('#modalAsignarA').modal('show');\n            }, onError: function() { Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar la lista.' }); } });\n        }\n        function cerrarTicketPanel(idTicket) {\n            if (!idTicket) return;\n            Swal.fire({ title: '¿Cerrar ticket?', text: 'El ticket se registrará como cerrado y dejará de mostrarse en la lista activa.', icon: 'question', showCancelButton: true, confirmButtonColor: '#fd7e14', cancelButtonColor: '#6c757d', confirmButtonText: 'Sí, cerrar' }).then(function(res) {\n                if (!res.isConfirmed) return;\n                http.request({\n                    endpoint: \"/sabueso/cerrarTicket\",\n                    metodo: \"POST\",\n                    data: JSON.stringify({ id_ticket: idTicket }),\n                    contentType: \"application/json\",\n                    processData: false,\n                    onSuccess: function(resp) {\n                        Swal.fire({ icon: 'success', title: 'Cerrado', text: resp.mensaje || 'Ticket cerrado.' });\n                        getTicketsPanelAdmin();\n                    },\n                    onError: function(err) {\n                        Swal.fire({ icon: 'error', title: 'Error', text: (err && err.mensaje) || 'No se pudo cerrar.' });\n                    }\n                });\n            });\n        }\n        function eliminarTicketPanel(idTicket) {\n            if (!idTicket) return;\n            Swal.fire({ title: '¿Eliminar ticket?', text: 'Esta acción no se puede deshacer.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#6c757d', confirmButtonText: 'Sí, eliminar' }).then(function(res) {\n                if (!res.isConfirmed) return;\n                http.request({\n                    endpoint: \"/sabueso/eliminarTicket\",\n                    metodo: \"POST\",\n                    data: JSON.stringify({ id_ticket: idTicket }),\n                    contentType: \"application/json\",\n                    processData: false,\n                    onSuccess: function(resp) {\n                        Swal.fire({ icon: 'success', title: 'Eliminado', text: resp.mensaje || 'Ticket eliminado.' });\n                        getTicketsPanelAdmin();\n                    },\n                    onError: function(err) {\n                        Swal.fire({ icon: 'error', title: 'Error', text: (err && err.mensaje) || 'No se pudo eliminar.' });\n                    }\n                });\n            });\n        }\n";
        $evidenciasScript = 'var miUsuarioId = ' . (int)$usuarioId . '; var miPersonaId = ' . (int)$personaId . '; var miUsuarioNombre = ' . json_encode($usuarioNombre ?? '', JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) . ';
        var evidenciasRastreoActual = []; var evidenciaModalSlot = null; var evidenciaModalId = null; var evidenciaPreviewObjectUrl = null;
        function formatGeminiText(text) {
            if (!text) return \'\';
            var formatted = (text + \'\');
            formatted = formatted.replace(/&/g, \'&amp;\').replace(/</g, \'&lt;\').replace(/>/g, \'&gt;\');
            formatted = formatted.replace(/\\*\\*(.*?)\\*\\*/g, \'<b>$1</b>\');
            formatted = formatted.replace(/^\\* /gm, \'• \');
            formatted = formatted.replace(/\\n/g, \'<br>\');
            return formatted;
        }
        function cargarChatRastreo() {
            if (!ticketIdRastreoActual) { $(\'#rastreoBitacoraContenido\').html(\'<span class="text-muted">Seleccione un ticket.</span>\'); return; }
            http.request({ endpoint: "/sabueso/getChatTicket", metodo: "POST", data: JSON.stringify({ id_ticket: ticketIdRastreoActual }), contentType: "application/json", processData: false, onSuccess: function(r) {
                var list = (r.datos || []);
                function initials(n) { var s = (n||\'\').trim(); if (!s) return \'?\'; var parts = s.split(/[\u0020\t\r\n]+/); if (parts.length >= 2) return (parts[0][0]+parts[1][0]).toUpperCase(); return (s[0]+s[1]||\'?\').toString().toUpperCase(); }
                var html = list.length ? list.map(function(m) { var f = m.fecha_creacion ? new Date(m.fecha_creacion).toLocaleString(\'es-MX\', { day: \'2-digit\', month: \'2-digit\', hour: \'2-digit\', minute: \'2-digit\' }) : \'\'; var msg = (m.mensaje || \'\').replace(/</g, \'&lt;\').replace(/>/g, \'&gt;\'); var mine = (m.id_persona && m.id_persona == miPersonaId); var cls = \'bitacora-msg\' + (mine ? \' bitacora-msg-mine\' : \'\'); var mid = (m.id != null ? m.id : 0); return \'<div class="\' + cls + \'" data-msg-id="\' + mid + \'"><div class="bitacora-avatar">\' + initials(m.persona_nombre) + \'</div><div class="bitacora-msg-body"><div class="bitacora-bubble"><div class="bitacora-msg-header d-flex align-items-center flex-wrap gap-1"><strong>\' + (m.persona_nombre || \'—\') + \'</strong> \' + f + \' <button type="button" class="btn btn-link btn-sm p-0 ms-auto text-danger bitacora-btn-delete" onclick="event.preventDefault();eliminarMensajeBitacora(\' + mid + \');" title="Eliminar mensaje" aria-label="Eliminar"><i class="fa-solid fa-trash" style="font-size:0.8rem;"></i></button></div>\' + msg + \'</div></div></div>\'; }).join(\'\') : \'<span class="text-muted">Sin mensajes.</span>\';
                $(\'#rastreoBitacoraContenido\').html(html);
                var el = document.getElementById(\'rastreoBitacoraContenido\'); if (el) el.scrollTop = el.scrollHeight;
            } });
        }
        function eliminarMensajeBitacora(idMensaje) {
            if (!idMensaje || !ticketIdRastreoActual) return;
            if (typeof Swal !== \'undefined\') {
                Swal.fire({ title: \'¿Eliminar mensaje?\', text: \'Esta acción no se puede deshacer.\', icon: \'warning\', showCancelButton: true, confirmButtonColor: \'#d33\', cancelButtonColor: \'#6c757d\', confirmButtonText: \'Sí, eliminar\' }).then(function(res) {
                    if (!res.isConfirmed) return;
                    http.request({ endpoint: \'/sabueso/eliminarMensajeChat\', metodo: \'POST\', data: JSON.stringify({ id_mensaje: idMensaje, id_ticket: ticketIdRastreoActual }), contentType: \'application/json\', processData: false, onSuccess: function(r) {
                        if (r.success) { if (typeof Swal !== \'undefined\') Swal.fire({ icon: \'success\', title: \'Eliminado\', text: r.mensaje || \'Mensaje eliminado.\' }); cargarChatRastreo(); } else { if (typeof Swal !== \'undefined\') Swal.fire({ icon: \'error\', title: \'Error\', text: r.mensaje || \'No se pudo eliminar.\' }); }
                    }, onError: function(e) { if (typeof Swal !== \'undefined\') Swal.fire({ icon: \'error\', title: \'Error\', text: (e && e.mensaje) || \'No se pudo eliminar.\' }); } });
                });
            } else {
                http.request({ endpoint: \'/sabueso/eliminarMensajeChat\', metodo: \'POST\', data: JSON.stringify({ id_mensaje: idMensaje, id_ticket: ticketIdRastreoActual }), contentType: \'application/json\', processData: false, onSuccess: function(r) { if (r.success) cargarChatRastreo(); } });
            }
        }
        function cargarDictamenRastreo() {
            if (!ticketIdRastreoActual) { $(\'#rastreoDictamenContenido\').html(\'<span class="text-muted">Seleccione un ticket.</span>\'); $(\'#rastreoDictamenCombo\').val(\'\'); $(\'#rastreoDictamenDescripcion\').val(\'\'); $(\'#rastreoDictamenDomiciliosWrap, #rastreoDictamenAmpliadaDomiciliosWrap\').empty(); $(\'#btnDictamenEnviarGestor, #btnDictamenAmpliadaEnviarGestor\').prop(\'disabled\', false).html(\'<i class="fa-solid fa-paper-plane me-1"></i>Enviar al gestor\'); $(\'#rastreoDictamenCombo, #rastreoDictamenDescripcion, #rastreoDictamenEvidenciaAdd, #rastreoDictamenAmpliadaEvidenciaAdd\').prop(\'disabled\', false); $(\'.rastreo-seccion-dictamen\').removeClass(\'dictamen-solo-lectura\'); $(\'.rastreo-dictamen-form-ampliada\').removeClass(\'dictamen-solo-lectura\'); return; }
            if (typeof rellenarEvidenciasDictamen === \'function\') rellenarEvidenciasDictamen(ticketIdRastreoActual);
            http.request({ endpoint: "/sabueso/getDictamenActualTicket", metodo: "POST", data: JSON.stringify({ id_ticket: ticketIdRastreoActual }), contentType: "application/json", processData: false, showLoader: false, onSuccess: function(r) {
                var d = (r.success && r.datos) ? r.datos : null;
                $(\'#rastreoDictamenCombo\').val(d && d.tipo ? d.tipo : \'\');
                $(\'#rastreoDictamenAmpliadaCombo\').val(d && d.tipo ? d.tipo : \'\');
                if (typeof rellenarDomiciliosDictamen === \'function\') rellenarDomiciliosDictamen(d && d.descripcion ? d.descripcion : \'\'); else { $(\'#rastreoDictamenDescripcion, #rastreoDictamenAmpliadaDescripcion\').val(d && d.descripcion ? d.descripcion : \'\'); $(\'#rastreoDictamenDomiciliosWrap, #rastreoDictamenAmpliadaDomiciliosWrap\').empty(); }
                var estado = (d && d.estado) ? d.estado : \'\';
                if (estado === \'enviado_al_gestor\') {
                    $(\'#rastreoDictamenCombo, #rastreoDictamenDescripcion, #rastreoDictamenAmpliadaCombo, #rastreoDictamenAmpliadaDescripcion, #rastreoDictamenEvidenciaAdd, #rastreoDictamenAmpliadaEvidenciaAdd\').prop(\'disabled\', true);
                    $(\'#btnDictamenEnviarGestor, #btnDictamenAmpliadaEnviarGestor\').prop(\'disabled\', true).html(\'<i class="fa-solid fa-check me-1"></i>Dictamen enviado\');
                    $(\'.rastreo-seccion-dictamen\').addClass(\'dictamen-solo-lectura\');
                    $(\'.rastreo-dictamen-form-ampliada\').addClass(\'dictamen-solo-lectura\');
                    var fEnv = (d.fecha_actualizacion ? new Date(d.fecha_actualizacion).toLocaleString(\'es-MX\', { day: \'2-digit\', month: \'2-digit\', year: \'numeric\', hour: \'2-digit\', minute: \'2-digit\' }) : \'—\');
                    $(\'#rastreoDictamenContenido\').html(\'<div class="small"><strong>Dictamen enviado al gestor</strong><br><p class="text-info mb-2 d-flex align-items-center gap-1"><i class="fa-solid fa-clock"></i>Vas a tener 12 horas para visitar al cliente.</p><span class="text-muted">Tipo: \' + (d.tipo || \'—\') + \'</span><br><span class="text-muted">Descripción: \' + (d.descripcion || \'\').replace(/</g, \'&lt;\').replace(/>/g, \'&gt;\') + \'</span><br><span class="text-muted">Enviado: \' + fEnv + \'</span></div>\');
                } else if (d && (d.tipo || d.descripcion)) {
                    $(\'#rastreoDictamenCombo, #rastreoDictamenDescripcion, #rastreoDictamenAmpliadaCombo, #rastreoDictamenAmpliadaDescripcion, #rastreoDictamenEvidenciaAdd, #rastreoDictamenAmpliadaEvidenciaAdd\').prop(\'disabled\', false);
                    $(\'#btnDictamenEnviarGestor, #btnDictamenAmpliadaEnviarGestor\').prop(\'disabled\', false).html(\'<i class="fa-solid fa-paper-plane me-1"></i>Enviar al gestor\');
                    $(\'.rastreo-seccion-dictamen\').removeClass(\'dictamen-solo-lectura\');
                    $(\'.rastreo-dictamen-form-ampliada\').removeClass(\'dictamen-solo-lectura\');
                    var fAct = (d.fecha_actualizacion ? new Date(d.fecha_actualizacion).toLocaleString(\'es-MX\', { day: \'2-digit\', month: \'2-digit\', hour: \'2-digit\', minute: \'2-digit\' }) : \'—\');
                    $(\'#rastreoDictamenContenido\').html(\'<div class="small text-success"><strong>Borrador guardado</strong> \' + fAct + \'<br><span class="text-muted">Tipo: \' + (d.tipo || \'—\') + \'</span><br><span class="text-muted">Descripción: \' + (d.descripcion || \'\').replace(/</g, \'&lt;\').replace(/>/g, \'&gt;\') + \'</span></div>\');
                } else {
                    $(\'#rastreoDictamenCombo, #rastreoDictamenDescripcion, #rastreoDictamenAmpliadaCombo, #rastreoDictamenAmpliadaDescripcion, #rastreoDictamenEvidenciaAdd, #rastreoDictamenAmpliadaEvidenciaAdd\').prop(\'disabled\', false);
                    $(\'#btnDictamenEnviarGestor, #btnDictamenAmpliadaEnviarGestor\').prop(\'disabled\', false).html(\'<i class="fa-solid fa-paper-plane me-1"></i>Enviar al gestor\');
                    $(\'.rastreo-seccion-dictamen\').removeClass(\'dictamen-solo-lectura\');
                    $(\'.rastreo-dictamen-form-ampliada\').removeClass(\'dictamen-solo-lectura\');
                    $(\'#rastreoDictamenContenido\').html(\'<span class="text-muted">Amplíe el dictamen para completar tipo, descripción y evidencia. Se guarda automáticamente.</span>\');
                }
            } });
        }
        function eliminarMensajeDictamen(idMensaje) {
            if (!idMensaje || !ticketIdRastreoActual) return;
            if (typeof Swal !== \'undefined\') {
                Swal.fire({ title: \'¿Eliminar dictamen?\', text: \'Esta acción no se puede deshacer.\', icon: \'warning\', showCancelButton: true, confirmButtonColor: \'#d33\', cancelButtonColor: \'#6c757d\', confirmButtonText: \'Sí, eliminar\' }).then(function(res) {
                    if (!res.isConfirmed) return;
                    http.request({ endpoint: \'/sabueso/eliminarMensajeDictamen\', metodo: \'POST\', data: JSON.stringify({ id_mensaje: idMensaje, id_ticket: ticketIdRastreoActual }), contentType: \'application/json\', processData: false, onSuccess: function(r) {
                        if (r.success) { if (typeof Swal !== \'undefined\') Swal.fire({ icon: \'success\', title: \'Eliminado\', text: r.mensaje || \'Dictamen eliminado.\' }); cargarDictamenRastreo(); } else { if (typeof Swal !== \'undefined\') Swal.fire({ icon: \'error\', title: \'Error\', text: r.mensaje || \'No se pudo eliminar.\' }); }
                    }, onError: function(e) { if (typeof Swal !== \'undefined\') Swal.fire({ icon: \'error\', title: \'Error\', text: (e && e.mensaje) || \'No se pudo eliminar.\' }); } });
                });
            } else {
                http.request({ endpoint: \'/sabueso/eliminarMensajeDictamen\', metodo: \'POST\', data: JSON.stringify({ id_mensaje: idMensaje, id_ticket: ticketIdRastreoActual }), contentType: \'application/json\', processData: false, onSuccess: function(r) { if (r.success) cargarDictamenRastreo(); } });
            }
        }
        function cargarGestionesRastreo() {
            if (!idCreditoRastreoActual) { $(\'#rastreoGestionesContenido\').html(\'<span class="text-muted">Seleccione un crédito.</span>\'); return; }
            http.request({ endpoint: "/sabueso/getGestionesCredito", metodo: "POST", data: JSON.stringify({ id_credito: idCreditoRastreoActual }), contentType: "application/json", processData: false, onSuccess: function(r) {
                var list = (r.datos || []);
                function esc(s) { if (s==null||s===undefined) return \'\'; return (s+\'\').replace(/&/g, \'&amp;\').replace(/</g, \'&lt;\').replace(/>/g, \'&gt;\').replace(/\x22/g, \'&quot;\'); }
                function v(s) { var x = (s==null||s===undefined||s===\'\') ? \'—\' : s; return esc(x); }
                var html = \'\';
                list.forEach(function(g) {
                    var fecha = (g.fecha_dispositivo || g.fecha_hora || \'\').toString().substring(0, 16);
                    var appLabel = (g.app && String(g.app).indexOf(\'Sky Logic\') !== -1) ? \'Legacy\' : (g.app || \'—\');
                    html += \'<div class="gestion-card">\';
                    html += \'<div class="gestion-meta"><span class="gestion-app">\' + v(appLabel) + \'</span>\' + (fecha ? \'<span class="gestion-fecha">\' + fecha.replace(\'T\', \' \') + \'</span>\' : \'\') + \'</div>\';
                    if (g.medio_contactacion_ccc || g.dictamen_ccc || g.medio_contactacion_campo || g.dictamen_campo) {
                        html += \'<div class="gestion-row"><span class="gestion-label">Contactación</span><span class="gestion-val">CCC: \' + v(g.medio_contactacion_ccc) + \' · \' + v(g.dictamen_ccc) + \' | Campo: \' + v(g.medio_contactacion_campo) + \' · \' + v(g.dictamen_campo) + \'</span></div>\';
                    }
                    html += \'<div class="gestion-row"><span class="gestion-label">Promesa</span><span class="gestion-val">\' + v(g.promesa_pago) + \'</span></div>\';
                    if (g.porque_atraso_pago) html += \'<div class="gestion-row"><span class="gestion-label">Motivo atraso</span><span class="gestion-val">\' + v(g.porque_atraso_pago) + \'</span></div>\';
                    if (g.comentarios_generales) html += \'<div class="gestion-comentarios">\' + v(g.comentarios_generales) + \'</div>\';
                    html += \'</div>\';
                });
                $(\'#rastreoGestionesContenido\').html(html || \'<span class="text-muted">Sin gestiones para este crédito.</span>\');
                rastreoGestionesParaMapa = (r.datos || []).filter(function(g) { var lat = parseFloat(g.latitud || g.lat), lng = parseFloat(g.longitud || g.lng); return !isNaN(lat) && !isNaN(lng); }).slice(0, 16).map(function(g, idx) { var contacto = ((g.contacto || \'\') + \'\').trim().toUpperCase(); var esTelefono = (contacto === \'TELEFONO\'); return { lat: parseFloat(g.latitud || g.lat), lng: parseFloat(g.longitud || g.lng), nombre: ((g.usuario_asignado || g.usuario || g.codigo_gestor || \'—\') + \'\').trim(), fecha: ((g.fecha_dispositivo || g.fecha_hora || \'\') + \'\').toString().substring(0, 16), numero: idx + 1, esCampo: !esTelefono }; });
                rastreoGestionesCargadas = true;
                rastreoTotalGestiones = (r.datos || []).length;
                rastreoConUbicacion = rastreoGestionesParaMapa.length;
                var htmlGeoPart = buildGeoListHtml(rastreoPuntosGeo);
                $(\'#rastreoDireccionesAlternasContenido\').removeClass(\'rastreo-contenido-cargando\').html(htmlGeoPart || \'<span class="text-muted small">Sin direcciones alternas para este crédito.</span>\');
                $(\'#rastreoDireccionesAlternas .rastreo-mapa-wrap\').show();
                maybeInitMapaAlternas();
            }, onError: function() {
                var htmlGeoErr = buildGeoListHtml(rastreoPuntosGeo);
                $(\'#rastreoDireccionesAlternasContenido\').removeClass(\'rastreo-contenido-cargando\').html(htmlGeoErr + \'<span class="text-muted small">Sin datos de gestiones para el mapa.</span>\');
                $(\'#rastreoDireccionesAlternas .rastreo-mapa-wrap\').show();
            } });
        }
        function maybeInitMapaAlternas() {
            if (!rastreoGestionesCargadas && (!rastreoPuntosGeo || !rastreoPuntosGeo.length)) return;
            initMapaRastreoAlternas(rastreoDireccionesParaMapa, rastreoGestionesParaMapa, rastreoPuntosGeo || []);
        }
        function pinGotaIcon(colorHex) {
            var svg = \'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 40"><path fill="\' + colorHex + \'" stroke="#333" stroke-width="1" d="M12 0C5.4 0 0 5.4 0 12c0 9 12 28 12 28s12-19 12-28C24 5.4 18.6 0 12 0z"/><circle cx="12" cy="10" r="4" fill="white"/></svg>\';
            return { url: \'data:image/svg+xml;charset=UTF-8,\' + encodeURIComponent(svg), scaledSize: new google.maps.Size(28, 40), anchor: new google.maps.Point(14, 40) };
        }
        function pinCirculoIcon(fillHex, strokeHex) {
            strokeHex = strokeHex || \'#333\';
            return { path: google.maps.SymbolPath.CIRCLE, scale: 10, fillColor: fillHex, fillOpacity: 1, strokeColor: strokeHex, strokeWeight: 2 };
        }
        function pinCirculoIconWithNumber(colorHex, num) {
            var n = (num != null && num !== \'\') ? String(num) : \'?\';
            var safe = n.replace(/&/g, \'&amp;\').replace(/</g, \'&lt;\');
            var svg = \'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 36 36"><circle cx="18" cy="18" r="14" fill="\' + colorHex + \'" stroke="#9a3412" stroke-width="2"/><text x="18" y="22" text-anchor="middle" fill="white" font-size="12" font-weight="700" font-family="Arial,sans-serif">\' + safe + \'</text></svg>\';
            return { url: \'data:image/svg+xml;charset=UTF-8,\' + encodeURIComponent(svg), scaledSize: new google.maps.Size(30, 30), anchor: new google.maps.Point(15, 15) };
        }
        function iconoFlotanteGestor(esCampo) { return esCampo ? \'fa-motorcycle\' : \'fa-phone\'; }
        function iconoFlotanteGeo(dondeFirma) {
            var d = (dondeFirma || \'\').toString().trim().toUpperCase();
            if (d.indexOf(\'CASA\') !== -1) return \'fa-house\';           /* casa → 🏠 */
            if (d.indexOf(\'AGENCIA\') !== -1) return \'fa-landmark\';     /* agencia → 🏢 */
            return \'fa-map-marker-alt\';                                   /* otro domicilio → 🏘️ */
        }
        function emojiIconoFlotante(tipo) {
            if (tipo === \'fa-motorcycle\') return \'🛵\';
            if (tipo === \'fa-phone\') return \'📞\';
            if (tipo === \'fa-house\') return \'🏠\';      /* casa (geo) */
            if (tipo === \'fa-landmark\') return \'🏢\';  /* agencia */
            if (tipo === \'fa-megareporte\') return \'🏡\';  /* casa megareporte */
            if (tipo === \'fa-map-marker-alt\') return \'🏘️\';  /* otro domicilio */
            if (tipo === \'fa-location-dot\') return \'📍\';
            return \'📍\';
        }
        function IconoFlotanteOverlay(position, faClass, colorHex, offsetY) { this.position = position; this.faClass = faClass; this.colorHex = colorHex || \'#333\'; this.offsetY = offsetY != null ? offsetY : -24; this.div_ = null; }
        function ensureIconoFlotanteOverlayReady() {
            if (typeof google === \'undefined\' || !google.maps || !google.maps.OverlayView) return;
            if (IconoFlotanteOverlay.prototype.draw) return;
            IconoFlotanteOverlay.prototype = new google.maps.OverlayView();
            IconoFlotanteOverlay.prototype.onAdd = function() {
                this.div_ = document.createElement(\'div\');
                this.div_.className = \'rastreo-icono-flotante\';
                this.div_.innerHTML = \'<span class="rastreo-icono-emoji">\' + emojiIconoFlotante(this.faClass) + \'</span>\';
                this.div_.style.position = \'absolute\'; this.div_.style.pointerEvents = \'none\'; this.div_.style.whiteSpace = \'nowrap\';
                var panes = this.getPanes(); if (panes && panes.floatPane) panes.floatPane.appendChild(this.div_);
            };
            IconoFlotanteOverlay.prototype.draw = function() {
                if (!this.div_ || !this.position) return;
                var proj = this.getProjection(); if (!proj) return;
                var point = proj.fromLatLngToDivPixel(new google.maps.LatLng(this.position.lat, this.position.lng));
                if (!point) return;
                var baseOffset = this.offsetY != null ? this.offsetY : -24;
                var finalOffset = baseOffset;
                var offsetX = 0;
                var slots = [[0,-24],[22,0],[0,18],[-22,0],[18,-18],[-18,-18],[18,18],[-18,18],[28,-12],[-28,-12],[28,12],[-28,12]];
                if (typeof window !== \'undefined\' && window.rastreoIconoPosiciones && Array.isArray(window.rastreoIconoPosiciones)) {
                    var selfLat = this.position.lat, selfLng = this.position.lng;
                    var umbralCluster = 48;
                    var cluster = [];
                    for (var i = 0; i < window.rastreoIconoPosiciones.length; i++) {
                        var o = window.rastreoIconoPosiciones[i];
                        var op = proj.fromLatLngToDivPixel(new google.maps.LatLng(o.lat, o.lng));
                        if (!op) continue;
                        var dx = op.x - point.x, dy = op.y - point.y;
                        if (Math.abs(dx) < umbralCluster && Math.abs(dy) < umbralCluster) {
                            cluster.push({ idx: o.idx != null ? o.idx : i, px: op.x, py: op.y, lat: o.lat, lng: o.lng });
                        }
                    }
                    cluster.sort(function(a,b){ return (a.py - b.py) || (a.px - b.px) || (a.idx - b.idx); });
                    var mySlot = -1;
                    var myIdx = this._idx;
                    for (var k = 0; k < cluster.length; k++) {
                        if (myIdx != null && cluster[k].idx === myIdx) { mySlot = k; break; }
                        if (myIdx == null && Math.abs(cluster[k].lat - selfLat) < 1e-7 && Math.abs(cluster[k].lng - selfLng) < 1e-7) { mySlot = k; break; }
                    }
                    if (mySlot >= 0 && cluster.length > 1) {
                        var s = slots[Math.min(mySlot, slots.length - 1)];
                        offsetX = s[0]; finalOffset = s[1];
                    } else if (cluster.length === 1) {
                        var pathLatLng = [];
                        var myMap = this.getMap();
                        if (myMap === rastreoMapaAlternas && window.rastreoPolylinePathLatLng && Array.isArray(window.rastreoPolylinePathLatLng)) pathLatLng = window.rastreoPolylinePathLatLng;
                        else if (myMap === rastreoMapaAlternasGrande && window.rastreoPolylinePathLatLngGrande && Array.isArray(window.rastreoPolylinePathLatLngGrande)) pathLatLng = window.rastreoPolylinePathLatLngGrande;
                        if (pathLatLng.length > 0) {
                            for (var j = 0; j < pathLatLng.length; j++) {
                                var pj = pathLatLng[j];
                                if (Math.abs((pj.lat || pj.latitude) - selfLat) < 1e-7 && Math.abs((pj.lng || pj.longitude) - selfLng) < 1e-7) {
                                    var nextP = pathLatLng[j + 1], prevP = pathLatLng[j - 1];
                                    var nextPx = nextP ? proj.fromLatLngToDivPixel(new google.maps.LatLng(nextP.lat || nextP.latitude, nextP.lng || nextP.longitude)) : null;
                                    var prevPx = prevP ? proj.fromLatLngToDivPixel(new google.maps.LatLng(prevP.lat || prevP.latitude, prevP.lng || prevP.longitude)) : null;
                                    if ((nextPx && nextPx.y < point.y + 8) || (prevPx && prevPx.y < point.y + 8)) { offsetX = 18; }
                                    break;
                                }
                            }
                        }
                    }
                }
                this.div_.style.left = (point.x - 10 + offsetX) + \'px\'; this.div_.style.top = (point.y + finalOffset) + \'px\';
            };
            IconoFlotanteOverlay.prototype.onRemove = function() { if (this.div_ && this.div_.parentNode) this.div_.parentNode.removeChild(this.div_); this.div_ = null; };
        }
        function crearPuntoConIconoFlotante(map, pos, colorHex, faIconClass, title, infoHtml) {
            ensureIconoFlotanteOverlayReady();
            var idx = (typeof window !== \'undefined\' && window.rastreoIconoPosiciones && Array.isArray(window.rastreoIconoPosiciones)) ? window.rastreoIconoPosiciones.length : 0;
            if (typeof window !== \'undefined\' && window.rastreoIconoPosiciones && Array.isArray(window.rastreoIconoPosiciones)) window.rastreoIconoPosiciones.push({ lat: pos.lat, lng: pos.lng, idx: idx });
            var marker = new google.maps.Marker({ position: pos, map: map, icon: pinCirculoIcon(colorHex), title: title || \'\', zIndex: 1 });
            var overlay = new IconoFlotanteOverlay(pos, faIconClass, colorHex);
            overlay._idx = idx;
            overlay.setMap(map);
            var infow = null;
            if (infoHtml) {
                infow = new google.maps.InfoWindow({ content: infoHtml });
                marker.addListener(\'click\', function() { infow.open(map, marker); });
            }
            return { marker: marker, overlay: overlay, infow: infow };
        }
        function crearPuntoGestorConIconoFlotante(map, pos, colorHex, numero, faIconClass, title, infoHtml) {
            ensureIconoFlotanteOverlayReady();
            var idx = (typeof window !== \'undefined\' && window.rastreoIconoPosiciones && Array.isArray(window.rastreoIconoPosiciones)) ? window.rastreoIconoPosiciones.length : 0;
            if (typeof window !== \'undefined\' && window.rastreoIconoPosiciones && Array.isArray(window.rastreoIconoPosiciones)) window.rastreoIconoPosiciones.push({ lat: pos.lat, lng: pos.lng, idx: idx });
            var marker = new google.maps.Marker({ position: pos, map: map, icon: pinCirculoIconWithNumber(colorHex, numero), title: title || \'\', zIndex: 1 });
            var overlay = new IconoFlotanteOverlay(pos, faIconClass, colorHex, -30);
            overlay.setMap(map);
            var infow = null;
            if (infoHtml) {
                infow = new google.maps.InfoWindow({ content: infoHtml });
                marker.addListener(\'click\', function() { infow.open(map, marker); });
            }
            return { marker: marker, overlay: overlay, infow: infow, numero: numero };
        }
        function pinGotaIconWithNumber(colorHex, num) {
            var n = (num != null && num !== \'\') ? String(num) : \'?\';
            var svg = \'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 40"><path fill="\' + colorHex + \'" stroke="#333" stroke-width="1" d="M12 0C5.4 0 0 5.4 0 12c0 9 12 28 12 28s12-19 12-28C24 5.4 18.6 0 12 0z"/><circle cx="12" cy="10" r="4" fill="white"/><text x="12" y="13" text-anchor="middle" fill="#1f2937" font-size="10" font-weight="700" font-family="Arial,sans-serif">\' + n.replace(/&/g, \'&amp;\').replace(/</g, \'&lt;\') + \'</text></svg>\';
            return { url: \'data:image/svg+xml;charset=UTF-8,\' + encodeURIComponent(svg), scaledSize: new google.maps.Size(28, 40), anchor: new google.maps.Point(14, 40) };
        }
        function pinGotaRosaIcon() {
            var svg = \'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 40"><path fill="#ec4899" stroke="#be185d" stroke-width="1" d="M12 0C5.4 0 0 5.4 0 12c0 9 12 28 12 28s12-19 12-28C24 5.4 18.6 0 12 0z"><animate attributeName="opacity" values="1;0.7;1" dur="1.2s" repeatCount="indefinite"/></path><circle cx="12" cy="10" r="4" fill="white"/></svg>\';
            return { url: \'data:image/svg+xml;charset=UTF-8,\' + encodeURIComponent(svg), scaledSize: new google.maps.Size(28, 40), anchor: new google.maps.Point(14, 40) };
        }
        function pinGotaVerdeIcon() {
            var svg = \'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 40"><path fill="#22c55e" stroke="#15803d" stroke-width="1" d="M12 0C5.4 0 0 5.4 0 12c0 9 12 28 12 28s12-19 12-28C24 5.4 18.6 0 12 0z"/><circle cx="12" cy="10" r="4" fill="white"/></svg>\';
            return { url: \'data:image/svg+xml;charset=UTF-8,\' + encodeURIComponent(svg), scaledSize: new google.maps.Size(28, 40), anchor: new google.maps.Point(14, 40) };
        }
        function pinGotaCarmelitaIcon() {
            var svg = \'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 40"><path fill="#d4a574" stroke="#b8860b" stroke-width="1" d="M12 0C5.4 0 0 5.4 0 12c0 9 12 28 12 28s12-19 12-28C24 5.4 18.6 0 12 0z"/><circle cx="12" cy="10" r="4" fill="white"/></svg>\';
            return { url: \'data:image/svg+xml;charset=UTF-8,\' + encodeURIComponent(svg), scaledSize: new google.maps.Size(28, 40), anchor: new google.maps.Point(14, 40) };
        }
        function getGeoItemClaseYIcon(dondeFirma) {
            var d = (dondeFirma || \'\').toString().trim().toUpperCase();
            if (d.indexOf(\'CASA\') !== -1) return { clase: \'rastreo-geo-casa\', pinClass: \'rastreo-pin-casa\', iconHtml: \'<i class="fa-solid fa-house rastreo-geo-icon"></i>\' };
            if (d.indexOf(\'AGENCIA\') !== -1) return { clase: \'rastreo-geo-agencia\', pinClass: \'rastreo-pin-carmelita\', iconHtml: \'\' };
            return { clase: \'rastreo-geo-otro\', pinClass: \'rastreo-pin-verde\', iconHtml: \'\' };
        }
        function buildGeoListHtml(puntosGeo) {
            if (!puntosGeo || !puntosGeo.length) return \'\';
            function escG(s) { if (s==null||s===undefined) return \'\'; return (s+\'\').replace(/&/g, \'&amp;\').replace(/</g, \'&lt;\').replace(/>/g, \'&gt;\').replace(/\"/g, \'&quot;\'); }
            var html = \'<div class="rastreo-donde-firma-titulo">Donde firma:</div>\';
            puntosGeo.forEach(function(p, i) {
                var donde = escG(p.donde_firma || \'Dirección\');
                var dir = escG(p.direccion_maps || \'\');
                var q = encodeURIComponent(p.direccion_maps || p.lat + \',\' + p.lng);
                var est = getGeoItemClaseYIcon(p.donde_firma);
                var linkPart = dir ? \' — <a href="https://www.google.com/maps/search/?api=1&query=\' + q + \'" target="_blank" rel="noopener" class="rastreo-geo-link">\' + (dir.length > 40 ? dir.substring(0,40)+\'...\' : dir) + \'</a>\' : \'\';
                html += \'<div class="rastreo-geo-item \' + est.clase + \' small mb-2" data-indice-geo="\' + i + \'" title="Clic para ver en mapa"><span class="\' + est.pinClass + \'"></span>\' + est.iconHtml + \' <strong>\' + donde + \'</strong>\' + linkPart + \'</div>\';
            });
            return html;
        }
        function labelDistanciaIcon(txt) {
            var esc = (txt || \'\').replace(/&/g, \'&amp;\').replace(/</g, \'&lt;\').replace(/>/g, \'&gt;\');
            var svg = \'<svg xmlns="http://www.w3.org/2000/svg" width="56" height="24" viewBox="0 0 56 24"><rect width="56" height="24" rx="12" fill="#6366f1" fill-opacity="0.95" stroke="#4f46e5" stroke-width="1"/><text x="28" y="16" text-anchor="middle" fill="white" font-size="12" font-family="Arial,sans-serif">\' + esc + \'</text></svg>\';
            return { url: \'data:image/svg+xml;charset=UTF-8,\' + encodeURIComponent(svg), scaledSize: new google.maps.Size(56, 24), anchor: new google.maps.Point(28, 12) };
        }
        var rastreoClusterMarkers = [];
        var rastreoPuntosClusterActual = [];
        var rastreoLupaCircle = null;
        var rastreoLupaPanel = null;
        var rastreoLupaMiniMap = null;
        var rastreoLupaMapListener = null;
        var rastreoLupaMap = null;
        function cerrarRastreoLupa() {
            if (rastreoLupaCircle) { try { rastreoLupaCircle.setMap(null); } catch (e) {} rastreoLupaCircle = null; }
            if (rastreoLupaPanel && rastreoLupaPanel.parentNode) rastreoLupaPanel.parentNode.removeChild(rastreoLupaPanel);
            rastreoLupaPanel = null;
            if (rastreoLupaMiniMap) { try { rastreoLupaMiniMap = null; } catch (e) {} }
            if (rastreoLupaMapListener && rastreoLupaMap) { try { google.maps.event.removeListener(rastreoLupaMapListener); } catch (e) {} rastreoLupaMapListener = null; rastreoLupaMap = null; }
        }
        function mostrarLupaCluster(map, lat, lng, count) {
            cerrarRastreoLupa();
            if (!map || !map.getDiv) return;
            rastreoLupaMap = map;
            var zoom = typeof map.getZoom === \'function\' ? map.getZoom() : 12;
            var zoomLupa = Math.min(18, zoom + 3);
            rastreoLupaCircle = new google.maps.Circle({ center: { lat: lat, lng: lng }, radius: 80, map: map, fillColor: \'#ea580c\', fillOpacity: 0.15, strokeColor: \'#c2410c\', strokeWeight: 2, clickable: false, zIndex: 997 });
            var mapDiv = map.getDiv();
            if (!mapDiv) return;
            var panelParent = mapDiv.parentNode || mapDiv;
            var panel = document.createElement(\'div\');
            panel.className = \'rastreo-lupa-panel\';
            var idCont = \'rastreoLupaMiniMapContenedor_\' + Date.now();
            panel.innerHTML = \'<button type="button" class="rastreo-lupa-cerrar" aria-label="Cerrar">&times;</button><div class="rastreo-lupa-lente" id="\' + idCont + \'"></div><span class="rastreo-lupa-texto">\' + (count > 0 ? count + \' ubicaciones\' : \'Vista ampliada\') + \' — Clic en mapa para cerrar</span>\';
            panelParent.appendChild(panel);
            rastreoLupaPanel = panel;
            var btnCerrar = panel.querySelector(\'.rastreo-lupa-cerrar\');
            if (btnCerrar) btnCerrar.addEventListener(\'click\', cerrarRastreoLupa);
            setTimeout(function() {
                var cont = document.getElementById(idCont);
                if (cont) {
                    rastreoLupaMiniMap = new google.maps.Map(cont, { center: { lat: lat, lng: lng }, zoom: zoomLupa, disableDefaultUI: true, zoomControl: false, gestureHandling: \'none\', mapTypeControl: false });
                    google.maps.event.addListenerOnce(rastreoLupaMiniMap, \'idle\', function() {
                        var mini = rastreoLupaMiniMap;
                        if (!mini) return;
                        var bounds = mini.getBounds();
                        function enBounds(la, ln) {
                            if (!bounds) return true;
                            var latN = parseFloat(la), lngN = parseFloat(ln);
                            return !isNaN(latN) && !isNaN(lngN) && bounds.contains({ lat: latN, lng: lngN });
                        }
                        (rastreoGestionesParaMapa || []).forEach(function(g) {
                            var latG = parseFloat(g.lat), lngG = parseFloat(g.lng);
                            if (isNaN(latG) || isNaN(lngG) || !enBounds(latG, lngG)) return;
                            var colorG = (rastreoColoresPorGestor && rastreoColoresPorGestor[g.nombre]) || \'#f97316\';
                            new google.maps.Marker({ position: { lat: latG, lng: lngG }, map: mini, icon: pinCirculoIcon(colorG), zIndex: 2 });
                        });
                        (rastreoPuntosGeo || []).forEach(function(p) {
                            var latP = parseFloat(p.lat), lngP = parseFloat(p.lng);
                            if (isNaN(latP) || isNaN(lngP) || !enBounds(latP, lngP)) return;
                            var d = (p.donde_firma || \'\').toString().trim().toUpperCase();
                            var colorP = (d.indexOf(\'AGENCIA\') !== -1) ? \'#d4a574\' : (d.indexOf(\'CASA\') !== -1) ? \'#ec4899\' : \'#22c55e\';
                            new google.maps.Marker({ position: { lat: latP, lng: lngP }, map: mini, icon: pinCirculoIcon(colorP), zIndex: 1 });
                        });
                        (rastreoDireccionesParaMapa || []).forEach(function(p) {
                            var latP = parseFloat(p.latitud || p.lat), lngP = parseFloat(p.longitud || p.lng);
                            if (isNaN(latP) || isNaN(lngP) || !enBounds(latP, lngP)) return;
                            new google.maps.Marker({ position: { lat: latP, lng: lngP }, map: mini, icon: pinCirculoIcon(\'#2563eb\'), zIndex: 1 });
                        });
                        if (rastreoDomicilioMegareporte && rastreoDomicilioMegareporte.lat != null && rastreoDomicilioMegareporte.lng != null) {
                            var latM = parseFloat(rastreoDomicilioMegareporte.lat), lngM = parseFloat(rastreoDomicilioMegareporte.lng);
                            if (!isNaN(latM) && !isNaN(lngM) && enBounds(latM, lngM)) {
                                new google.maps.Marker({ position: { lat: latM, lng: lngM }, map: mini, icon: pinCirculoIcon(\'#000000\'), zIndex: 3 });
                            }
                        }
                    });
                }
            }, 80);
            setTimeout(function() {
                if (rastreoLupaMap === map) rastreoLupaMapListener = map.addListener(\'click\', function() { cerrarRastreoLupa(); });
            }, 150);
        }
        function activarLupaConClicEnMapa(map) {
            if (!map || !map.getDiv) return;
            var mapDiv = map.getDiv();
            var parent = mapDiv.parentNode || mapDiv;
            var overlay = document.createElement(\'div\');
            overlay.className = \'rastreo-lupa-overlay-activo\';
            overlay.title = \'Clic en el mapa para ampliar aquí\';
            overlay.style.cssText = \'position:absolute;top:0;left:0;right:0;bottom:0;z-index:500;cursor:zoom-in;pointer-events:auto;\';
            parent.appendChild(overlay);
            function quitarYAbrir(ev) {
                if (!overlay.parentNode) return;
                overlay.parentNode.removeChild(overlay);
                mapDiv.style.cursor = \'\';
                var rect = mapDiv.getBoundingClientRect();
                var x = (ev.clientX - rect.left);
                var y = (ev.clientY - rect.top);
                var bounds = map.getBounds();
                if (!bounds) { mostrarLupaCluster(map, 19.43, -99.13, 0); return; }
                var ne = bounds.getNorthEast(), sw = bounds.getSouthWest();
                var lat = sw.lat() + (ne.lat() - sw.lat()) * (1 - y / rect.height);
                var lng = sw.lng() + (ne.lng() - sw.lng()) * (x / rect.width);
                mostrarLupaCluster(map, lat, lng, 0);
            }
            overlay.addEventListener(\'click\', quitarYAbrir);
        }
        function pathConArcosParaSegmentosCortos(path, umbralGrados, zoom) {
            umbralGrados = umbralGrados != null ? umbralGrados : 0.09;
            if (!path || path.length < 2) return path || [];
            var curveFactor = 1;
            var angleZoom = 0;
            if (zoom != null && !isNaN(zoom)) {
                curveFactor = Math.max(0, Math.min(1, (18 - zoom) / 10));
                // Inclinacion suave por zoom para evitar cambiar al lado opuesto.
                angleZoom = (zoom - 13) * (Math.PI / 36);
            }
            var out = [path[0]];
            for (var i = 0; i < path.length - 1; i++) {
                var a = path[i], b = path[i + 1];
                var lat1 = parseFloat(a.lat), lng1 = parseFloat(a.lng), lat2 = parseFloat(b.lat), lng2 = parseFloat(b.lng);
                if (isNaN(lat1) || isNaN(lng1) || isNaN(lat2) || isNaN(lng2)) { out.push(b); continue; }
                var dx = lng2 - lng1, dy = lat2 - lat1;
                var len = Math.sqrt(dx * dx + dy * dy);
                if (len < 1e-10) {
                    // Si dos puntos son exactamente iguales, dibujamos un micro-desvio
                    // para que la conexion sea visible en vez de desaparecer.
                    var dppSame = (zoom != null && !isNaN(zoom)) ? (360 / (256 * Math.pow(2, zoom))) : 0.0007;
                    var j = Math.max(dppSame * 20, 0.00002);
                    var dir = (i % 2 === 0) ? 1 : -1;
                    out.push({ lat: lat1 + (j * dir), lng: lng1 - (j * dir) });
                    out.push(b);
                    continue;
                }
                if (len >= umbralGrados) { out.push(b); continue; }
                var dpp = (zoom != null && !isNaN(zoom)) ? (360 / (256 * Math.pow(2, zoom))) : 0.0007;
                var pxDist = len / Math.max(dpp, 1e-9);
                // Cuando ya hay separacion visual, la union debe ser recta.
                if (pxDist >= 18) { out.push(b); continue; }
                var visFactor = Math.max(0, Math.min(1, (18 - pxDist) / 18));
                var segCurve = curveFactor * visFactor;
                if (len < 0.0015) segCurve = Math.max(segCurve, 0.95);
                else if (len < 0.003) segCurve = Math.max(segCurve, 0.8);
                if (segCurve < 1e-4) { out.push(b); continue; }
                var midLat = (lat1 + lat2) / 2, midLng = (lng1 + lng2) / 2;
                var ux = -dy / len, uy = dx / len;
                var minVisualOffset = dpp * (len < 0.0015 ? 46 : len < 0.003 ? 36 : 28);
                var offset = Math.max(len * 1.9, minVisualOffset) * segCurve;
                var perpLat = ux * offset, perpLng = uy * offset;
                var cosA = Math.cos(angleZoom), sinA = Math.sin(angleZoom);
                var cLat = midLat + (perpLat * cosA - perpLng * sinA);
                var cLng = midLng + (perpLat * sinA + perpLng * cosA);
                for (var t = 0.06; t < 1; t += 0.06) {
                    var mt = 1 - t; var mt2 = mt * mt; var t2 = t * t; var two = 2 * mt * t;
                    out.push({ lat: mt2 * lat1 + two * cLat + t2 * lat2, lng: mt2 * lng1 + two * cLng + t2 * lng2 });
                }
                out.push(b);
            }
            return out;
        }
        function groupPointsByArea(puntos, zoom) {
            var factor = 40;
            if (zoom >= 15) factor = 800;
            else if (zoom >= 14) factor = 400;
            else if (zoom >= 13) factor = 200;
            else if (zoom >= 12) factor = 100;
            else if (zoom >= 11) factor = 40;
            var groups = {};
            (puntos || []).forEach(function(p) {
                var lat = parseFloat(p.lat !== undefined ? p.lat : p.latitud);
                var lng = parseFloat(p.lng !== undefined ? p.lng : p.longitud);
                if (isNaN(lat) || isNaN(lng)) return;
                var key = Math.round(lat * factor) + \'_\' + Math.round(lng * factor);
                if (!groups[key]) groups[key] = { lat: 0, lng: 0, count: 0 };
                groups[key].lat += lat;
                groups[key].lng += lng;
                groups[key].count++;
            });
            var clusters = [];
            Object.keys(groups).forEach(function(k) {
                var g = groups[k];
                if (g.count > 1) clusters.push({ lat: g.lat / g.count, lng: g.lng / g.count, count: g.count });
            });
            return clusters;
        }
        function clusterLabelIcon(count) {
            var text = count + \' ubicaciones en esta área\';
            var esc = (text + \'\').replace(/&/g, \'&amp;\').replace(/</g, \'&lt;\').replace(/>/g, \'&gt;\');
            var svg = \'<svg xmlns="http://www.w3.org/2000/svg" width="230" height="40" viewBox="0 0 230 40"><defs><filter id="shadow" x="-20%" y="-20%" width="140%" height="140%"><feDropShadow dx="0" dy="2" stdDeviation="3" flood-opacity="0.25"/></filter></defs><rect x="4" y="4" width="222" height="32" rx="12" fill="rgba(51,65,85,0.94)" stroke="rgba(71,85,105,0.9)" stroke-width="1.2" filter="url(#shadow)"/><text x="115" y="26" text-anchor="middle" fill="#f1f5f9" font-size="12" font-weight="600" font-family="Arial,sans-serif">\' + esc + \'</text></svg>\';
            return { url: \'data:image/svg+xml;charset=UTF-8,\' + encodeURIComponent(svg), scaledSize: new google.maps.Size(230, 40), anchor: new google.maps.Point(0, 20) };
        }
        function addClusterLabelsToMap(map, puntos) {
            if (!map || typeof google === \'undefined\' || !google.maps) return;
            rastreoPuntosClusterActual = puntos || [];
            while (rastreoClusterMarkers.length) {
                var m = rastreoClusterMarkers.pop();
                if (m && m.setMap) m.setMap(null);
            }
        }
        function todosPuntosParaCluster(puntosGeo, puntosMaxiApp, puntosGestores) {
            var seen = {};
            var out = [];
            function add(lat, lng) {
                if (isNaN(lat) || isNaN(lng)) return;
                var key = Math.round(lat * 1e5) + \'_\' + Math.round(lng * 1e5);
                if (seen[key]) return;
                seen[key] = true;
                out.push({ lat: lat, lng: lng });
            }
            (puntosGeo || []).forEach(function(p) { add(parseFloat(p.lat), parseFloat(p.lng)); });
            if (puntosMaxiApp && puntosMaxiApp.length && puntosMaxiApp[0] && (puntosMaxiApp[0].latitud !== undefined || puntosMaxiApp[0].lat !== undefined)) {
                (puntosMaxiApp || []).forEach(function(p) { add(parseFloat(p.latitud || p.lat), parseFloat(p.longitud || p.lng)); });
            }
            (puntosGestores || []).forEach(function(p) { add(parseFloat(p.lat), parseFloat(p.lng)); });
            return out;
        }
        function initMapaRastreoAlternas(puntosMaxiApp, puntosGestores, puntosGeo) {
            puntosGeo = puntosGeo || [];
            var cont = document.getElementById(\'rastreoMapaAlternas\');
            if (!cont) return;
            if (!googleMapsApiKey || !googleMapsApiKey.length) return;
            if (typeof google === \'undefined\' || !google.maps) { setTimeout(function() { maybeInitMapaAlternas(); }, 500); return; }
            if (rastreoMapaAlternas) { try { if (typeof rastreoMapaAlternas.remove === \'function\') rastreoMapaAlternas.remove(); } catch (e) {} rastreoMapaAlternas = null; }
            if (rastreoPolylineAlternas) { try { rastreoPolylineAlternas.setMap(null); } catch (e) {} rastreoPolylineAlternas = null; }
            Object.keys(rastreoPolylinesPorGestorAlternas || {}).forEach(function(k) { var o = rastreoPolylinesPorGestorAlternas[k]; if (o && o.poly && o.poly.setMap) o.poly.setMap(null); });
            rastreoPolylinesPorGestorAlternas = {};
            if (typeof window !== \'undefined\') window.rastreoIconoPosiciones = [];
            rastreoPolylinePathRawAlternas = [];
            rastreoMarkersPorGestorAlternas = {};
            rastreoMapaAlternas = new google.maps.Map(cont, { center: { lat: 19.43, lng: -99.13 }, zoom: 10, mapTypeControl: true, streetViewControl: true, fullscreenControl: true, zoomControl: true });
            var bounds = new google.maps.LatLngBounds();
            var hasPoints = false;
            if (puntosMaxiApp && puntosMaxiApp.length) {
                var esPuntos = puntosMaxiApp[0] && (puntosMaxiApp[0].latitud !== undefined || puntosMaxiApp[0].lat !== undefined);
                if (esPuntos) {
                    (puntosMaxiApp.length > 16 ? puntosMaxiApp.slice(0, 16) : puntosMaxiApp).forEach(function(p, i) {
                        var lat = parseFloat(p.latitud || p.lat), lon = parseFloat(p.longitud || p.lng);
                        if (isNaN(lat) || isNaN(lon)) return;
                        hasPoints = true;
                        var pos = { lat: lat, lng: lon };
                        bounds.extend(pos);
                        var visitas = p.cantidad_registros || 1;
                        var tipoLabel = p.punto_de_interes ? \'Punto de interés\' : \'Menos frecuente\';
                        var infoHtml = \'<strong>Cliente</strong>: \' + (rastreoDatosClienteActual.nombre || \'—\') + \'<br><strong>Crédito</strong>: \' + (rastreoDatosClienteActual.credito || \'—\') + \'<br><strong>Teléfono</strong>: \' + (rastreoDatosClienteActual.telefono || \'—\') + \'<br><strong>Ubicación</strong>: Obteniendo dirección...<br><strong>Visitas</strong>: \' + visitas + \'<br><strong>Tipo</strong>: \' + tipoLabel;
                        var res = crearPuntoConIconoFlotante(rastreoMapaAlternas, pos, \'#2563eb\', \'fa-location-dot\', \'Dirección frecuente \' + (i + 1) + \' — \' + visitas + \' visitas\', infoHtml);
                        if (typeof google.maps.Geocoder !== \'undefined\' && res.infow) {
                            var geocoder = new google.maps.Geocoder();
                            geocoder.geocode({ location: pos }, function(results, status) {
                                if (status === \'OK\' && results[0] && res.infow) { var addr = (results[0].formatted_address || \'\').replace(/</g, \'&lt;\').replace(/>/g, \'&gt;\'); res.infow.setContent(\'<strong>Cliente</strong>: \' + (rastreoDatosClienteActual.nombre || \'—\') + \'<br><strong>Crédito</strong>: \' + (rastreoDatosClienteActual.credito || \'—\') + \'<br><strong>Teléfono</strong>: \' + (rastreoDatosClienteActual.telefono || \'—\') + \'<br><strong>Ubicación</strong>: \' + addr + \'<br><strong>Visitas</strong>: \' + visitas + \'<br><strong>Tipo</strong>: \' + tipoLabel); }
                            });
                        }
                    });
                }
            }
            if (puntosGestores && puntosGestores.length) {
                var seenG = {}, idxG = 0;
                puntosGestores.forEach(function(g) { var n = (g.nombre || \'—\').trim() || \'—\'; if (!seenG[n]) { seenG[n] = true; rastreoColoresPorGestor[n] = rastreoPaletaColoresGestores[idxG % rastreoPaletaColoresGestores.length]; idxG++; } });
                puntosGestores.forEach(function(g, i) {
                    var lat = g.lat, lon = g.lng;
                    if (isNaN(lat) || isNaN(lon)) return;
                    hasPoints = true;
                    var pos = { lat: lat, lng: lon };
                    bounds.extend(pos);
                    var nombreGestor = (g.nombre || \'—\').trim() || \'—\';
                    var colorG = \'#f97316\';
                    var numGota = g.numero != null ? g.numero : (i + 1);
                    var infoHtml = \'<strong>Gestor</strong>: \' + (g.nombre || \'—\') + \' <strong>#\' + numGota + \'</strong>\' + (g.fecha ? \'<br><strong>Fecha</strong>: \' + g.fecha : \'\');
                    var res = crearPuntoGestorConIconoFlotante(rastreoMapaAlternas, pos, colorG, numGota, iconoFlotanteGestor(g.esCampo), (g.nombre || \'Gestor \') + (g.fecha ? \' — \' + g.fecha : \'\') + \' (#\' + numGota + \')\', infoHtml);
                    if (!rastreoMarkersPorGestorAlternas[nombreGestor]) rastreoMarkersPorGestorAlternas[nombreGestor] = [];
                    rastreoMarkersPorGestorAlternas[nombreGestor].push({ marker: res.marker, overlay: res.overlay, numero: numGota });
                });
                var pathGestores = puntosGestores.slice().sort(function(a, b) { return (a.numero != null ? a.numero : 0) - (b.numero != null ? b.numero : 0); }).map(function(g) { return { lat: g.lat, lng: g.lng }; }).filter(function(p) { return !isNaN(parseFloat(p.lat)) && !isNaN(parseFloat(p.lng)); });
                rastreoPolylinePathRawAlternas = pathGestores.slice();
                if (typeof window !== \'undefined\') window.rastreoPolylinePathLatLng = pathGestores.slice();
                var puntosPorGestor = {};
                puntosGestores.forEach(function(g) {
                    var n = (g.nombre || \'—\').trim() || \'—\';
                    if (!puntosPorGestor[n]) puntosPorGestor[n] = [];
                    puntosPorGestor[n].push({ lat: g.lat, lng: g.lng, numero: g.numero != null ? g.numero : 999 });
                });
                var zoomA = typeof rastreoMapaAlternas.getZoom === \'function\' ? rastreoMapaAlternas.getZoom() : 10;
                Object.keys(puntosPorGestor).forEach(function(nomG) {
                    var pts = puntosPorGestor[nomG].sort(function(a,b){ return a.numero - b.numero; }).map(function(p){ return { lat: p.lat, lng: p.lng }; });
                    if (pts.length >= 2) {
                        var pathCurv = pathConArcosParaSegmentosCortos(pts, 0.09, zoomA);
                        var colorPl = rastreoColoresPorGestor[nomG] || \'#c2410c\';
                        var pl = new google.maps.Polyline({ path: pathCurv, strokeColor: colorPl, strokeOpacity: 0.95, strokeWeight: 4, map: null, zIndex: 5 });
                        rastreoPolylinesPorGestorAlternas[nomG] = { poly: pl, pathRaw: pts.slice() };
                    }
                });
            }
            if (puntosGeo && puntosGeo.length) {
                puntosGeo.forEach(function(p, i) {
                    var lat = parseFloat(p.lat), lon = parseFloat(p.lng);
                    if (isNaN(lat) || isNaN(lon)) return;
                    hasPoints = true;
                    var pos = { lat: lat, lng: lon };
                    bounds.extend(pos);
                    var donde = (p.donde_firma || \'\').replace(/</g, \'&lt;\').replace(/>/g, \'&gt;\');
                    var dir = (p.direccion_maps || \'\').replace(/</g, \'&lt;\').replace(/>/g, \'&gt;\');
                    var q = encodeURIComponent(p.direccion_maps || lat + \',\' + lon);
                    var infoHtml = \'<strong>Donde firma:</strong> \' + (donde || \'—\') + \'<br><strong>Dirección</strong>: \' + (dir || \'—\') + \'<br><strong>Cliente</strong>: \' + (rastreoDatosClienteActual.nombre || \'—\') + \'<br><strong>Crédito</strong>: \' + (rastreoDatosClienteActual.credito || \'—\') + \'<br><a href="https://www.google.com/maps/search/?api=1&query=\' + q + \'" target="_blank" rel="noopener">Abrir en Google Maps</a>\';
                    var d = (p.donde_firma || \'\').toString().trim().toUpperCase();
                    var colorGeo = (d.indexOf(\'AGENCIA\') !== -1) ? \'#d4a574\' : (d.indexOf(\'CASA\') !== -1) ? \'#ec4899\' : \'#22c55e\';
                    crearPuntoConIconoFlotante(rastreoMapaAlternas, pos, colorGeo, iconoFlotanteGeo(p.donde_firma), donde || \'Dirección geo \' + (i+1), infoHtml);
                });
            }
            if (rastreoDomicilioMegareporte && rastreoDomicilioMegareporte.lat != null && rastreoDomicilioMegareporte.lng != null) {
                hasPoints = true;
                var posMegareporte = { lat: parseFloat(rastreoDomicilioMegareporte.lat), lng: parseFloat(rastreoDomicilioMegareporte.lng) };
                if (!isNaN(posMegareporte.lat) && !isNaN(posMegareporte.lng)) {
                    bounds.extend(posMegareporte);
                    var dirMegareporte = (rastreoDomicilioMegareporte.direccion || rastreoDatosClienteActual.direccion || \'—\').replace(/</g, \'&lt;\').replace(/>/g, \'&gt;\');
                    var qMegareporte = encodeURIComponent(dirMegareporte || posMegareporte.lat + \',\' + posMegareporte.lng);
                    var infoHtmlMegareporte = \'<strong>Dirección megareporte</strong><br><strong>Dirección</strong>: \' + dirMegareporte + \'<br><strong>Cliente</strong>: \' + (rastreoDatosClienteActual.nombre || \'—\') + \'<br><strong>Crédito</strong>: \' + (rastreoDatosClienteActual.credito || \'—\') + \'<br><a href="https://www.google.com/maps/search/?api=1&query=\' + qMegareporte + \'" target="_blank" rel="noopener">Abrir en Google Maps</a>\';
                    var markerMegareporte = new google.maps.Marker({ position: posMegareporte, map: rastreoMapaAlternas, icon: pinCirculoIcon(\'#000000\'), title: \'Dirección megareporte (casa)\', zIndex: 10 });
                    var infowMegareporte = new google.maps.InfoWindow({ content: infoHtmlMegareporte });
                    markerMegareporte.addListener(\'click\', function() { infowMegareporte.open(rastreoMapaAlternas, markerMegareporte); });
                    var idxMeg = (typeof window !== \'undefined\' && window.rastreoIconoPosiciones && Array.isArray(window.rastreoIconoPosiciones)) ? window.rastreoIconoPosiciones.length : 0;
                    if (typeof window !== \'undefined\' && window.rastreoIconoPosiciones && Array.isArray(window.rastreoIconoPosiciones)) window.rastreoIconoPosiciones.push({ lat: posMegareporte.lat, lng: posMegareporte.lng, idx: idxMeg });
                    ensureIconoFlotanteOverlayReady();
                    var overlayMegareporte = new IconoFlotanteOverlay(posMegareporte, \'fa-megareporte\', \'#000000\');
                    overlayMegareporte._idx = idxMeg;
                    overlayMegareporte.setMap(rastreoMapaAlternas);
                }
            }
            addClusterLabelsToMap(rastreoMapaAlternas, todosPuntosParaCluster(puntosGeo, puntosMaxiApp, puntosGestores));
            if (rastreoMapaAlternas.addListener) {
                rastreoMapaAlternas.addListener(\'zoom_changed\', function() { addClusterLabelsToMap(rastreoMapaAlternas, rastreoPuntosClusterActual); });
                rastreoMapaAlternas.addListener(\'zoom_changed\', function() {
                    var z = rastreoMapaAlternas.getZoom();
                    Object.keys(rastreoPolylinesPorGestorAlternas || {}).forEach(function(k) {
                        var obj = rastreoPolylinesPorGestorAlternas[k];
                        if (obj && obj.poly && obj.pathRaw && obj.pathRaw.length >= 2) obj.poly.setPath(pathConArcosParaSegmentosCortos(obj.pathRaw, 0.09, z));
                    });
                });
                google.maps.event.addDomListener(window, \'resize\', function() { if (rastreoMapaAlternas) addClusterLabelsToMap(rastreoMapaAlternas, rastreoPuntosClusterActual); });
            }
            if (hasPoints) rastreoMapaAlternas.fitBounds(bounds, 50);
            filtrarMapaPorGestor(rastreoFiltroGestoresSeleccionados.length ? rastreoFiltroGestoresSeleccionados : null);
            var wrapAlt = cont.parentNode;
            if (wrapAlt && !document.getElementById(\'rastreoBtnLupaAlternas\')) {
                var btnLupaAlt = document.createElement(\'button\');
                btnLupaAlt.type = \'button\'; btnLupaAlt.id = \'rastreoBtnLupaAlternas\';
                btnLupaAlt.className = \'rastreo-btn-lupa-mapa\'; btnLupaAlt.innerHTML = \'🔍 Lupa\';
                btnLupaAlt.title = \'Clic aquí y luego en el mapa para ampliar una zona\';
                btnLupaAlt.addEventListener(\'click\', function() { activarLupaConClicEnMapa(rastreoMapaAlternas); });
                wrapAlt.appendChild(btnLupaAlt);
            }
        }
        function filtrarMapaPorGestor(sel) {
            var selected = [];
            if (sel !== null && sel !== undefined) { if (Array.isArray(sel)) selected = sel; else selected = (sel + \'\').trim() ? [ (sel + \'\').trim() ] : []; }
            selected = (selected || []).map(function(s){ return ((s == null ? \'\' : (s + \'\')).trim()); }).filter(function(s){ return s.length > 0; });
            rastreoFiltroGestoresSeleccionados = selected;
            rastreoFiltroGestorActual = selected.length === 0 ? \'\' : selected.length === 1 ? selected[0] : selected.join(\',\');
            var showAll = selected.length === 0;
            function iconForGestor(n, isGrande, displayNumero) {
                var color = rastreoColoresPorGestor[n] || rastreoPaletaColoresGestores[0];
                if (!showAll && selected.indexOf(n) === -1) color = null;
                if (!color) return null;
                if (displayNumero != null && displayNumero !== \'\') return pinCirculoIconWithNumber(color, displayNumero);
                return pinCirculoIcon(color);
            }
            function computarNuevoNumero() {
                if (showAll || selected.length === 0) return {};
                var items = [];
                selected.forEach(function(n) {
                    var arr = (rastreoMarkersPorGestorAlternas || {})[n];
                    if (!arr) return;
                    arr.forEach(function(it, idx) { items.push({ gestor: n, item: it, numeroOrig: it.numero != null ? it.numero : 999, idx: idx }); });
                });
                if (items.length === 0) return {};
                items.sort(function(a, b) { return a.numeroOrig - b.numeroOrig; });
                var mapa = {};
                items.forEach(function(it, i) { mapa[it.gestor + \'_\' + it.idx] = (i + 1); });
                return mapa;
            }
            var nuevoNumeroMap = computarNuevoNumero();
            if (rastreoMapaAlternas && rastreoMarkersPorGestorAlternas) {
                Object.keys(rastreoMarkersPorGestorAlternas).forEach(function(n) {
                    var arr = rastreoMarkersPorGestorAlternas[n];
                    var visible = showAll || selected.indexOf(n) !== -1;
                    (arr || []).forEach(function(item, idx) {
                        var marker = item.marker != null ? item.marker : item;
                        var overlay = item.overlay;
                        var displayNum = showAll ? item.numero : (nuevoNumeroMap[n + \'_\' + idx] || item.numero);
                        var icon = iconForGestor(n, false, displayNum);
                        marker.setMap(visible ? rastreoMapaAlternas : null);
                        if (overlay && overlay.setMap) overlay.setMap(visible ? rastreoMapaAlternas : null);
                        if (visible && icon) marker.setIcon(icon);
                    });
                });
            }
            if (rastreoMapaAlternas && rastreoMarkersPorGestorAlternas) {
                Object.keys(rastreoMarkersPorGestorAlternas).forEach(function(n) {
                    var arr = rastreoMarkersPorGestorAlternas[n] || [];
                    var ptsRaw = arr.slice().sort(function(a, b) {
                        var na = (a && a.numero != null) ? a.numero : 999;
                        var nb = (b && b.numero != null) ? b.numero : 999;
                        return na - nb;
                    }).map(function(item) {
                        var marker = item && item.marker ? item.marker : null;
                        if (!marker || !marker.getPosition) return null;
                        var p = marker.getPosition();
                        return p ? { lat: p.lat(), lng: p.lng() } : null;
                    }).filter(function(p) { return !!p; });
                    if (ptsRaw.length < 2) {
                        if (rastreoPolylinesPorGestorAlternas[n] && rastreoPolylinesPorGestorAlternas[n].poly) rastreoPolylinesPorGestorAlternas[n].poly.setMap(null);
                        return;
                    }
                    if (!rastreoPolylinesPorGestorAlternas[n] || !rastreoPolylinesPorGestorAlternas[n].poly) {
                        var colorN = rastreoColoresPorGestor[n] || \'#c2410c\';
                        rastreoPolylinesPorGestorAlternas[n] = { poly: new google.maps.Polyline({ path: [], strokeColor: colorN, strokeOpacity: 0.95, strokeWeight: 4, map: null, zIndex: 5 }), pathRaw: [] };
                    }
                    rastreoPolylinesPorGestorAlternas[n].pathRaw = ptsRaw.slice();
                    var zNow = typeof rastreoMapaAlternas.getZoom === \'function\' ? rastreoMapaAlternas.getZoom() : 10;
                    rastreoPolylinesPorGestorAlternas[n].poly.setPath(pathConArcosParaSegmentosCortos(ptsRaw, 0.09, zNow));
                    var mostrar = !showAll && selected.indexOf(n) !== -1;
                    rastreoPolylinesPorGestorAlternas[n].poly.setMap(mostrar ? rastreoMapaAlternas : null);
                });
            }
            if (rastreoMapaAlternasGrande && rastreoMarkersPorGestorAlternasGrande) {
                var nuevoNumeroMapG = {};
                if (!showAll && selected.length > 0) {
                    var itemsG = [];
                    selected.forEach(function(n) {
                        var arr = (rastreoMarkersPorGestorAlternasGrande || {})[n];
                        if (!arr) return;
                        arr.forEach(function(it, idx) { itemsG.push({ gestor: n, item: it, numeroOrig: it.numero != null ? it.numero : 999, idx: idx }); });
                    });
                    itemsG.sort(function(a, b) { return a.numeroOrig - b.numeroOrig; });
                    itemsG.forEach(function(it, i) { nuevoNumeroMapG[it.gestor + \'_\' + it.idx] = (i + 1); });
                }
                Object.keys(rastreoMarkersPorGestorAlternasGrande).forEach(function(n) {
                    var arr = rastreoMarkersPorGestorAlternasGrande[n];
                    var visible = showAll || selected.indexOf(n) !== -1;
                    (arr || []).forEach(function(item, idx) {
                        var marker = item.marker != null ? item.marker : item;
                        var overlay = item.overlay;
                        var displayNum = showAll ? item.numero : (nuevoNumeroMapG[n + \'_\' + idx] || item.numero);
                        var icon = iconForGestor(n, true, displayNum);
                        marker.setMap(visible ? rastreoMapaAlternasGrande : null);
                        if (overlay && overlay.setMap) overlay.setMap(visible ? rastreoMapaAlternasGrande : null);
                        if (visible && icon) marker.setIcon(icon);
                    });
                });
            }
            if (rastreoMapaAlternasGrande && rastreoMarkersPorGestorAlternasGrande) {
                Object.keys(rastreoMarkersPorGestorAlternasGrande).forEach(function(n) {
                    var arr = rastreoMarkersPorGestorAlternasGrande[n] || [];
                    var ptsRaw = arr.slice().sort(function(a, b) {
                        var na = (a && a.numero != null) ? a.numero : 999;
                        var nb = (b && b.numero != null) ? b.numero : 999;
                        return na - nb;
                    }).map(function(item) {
                        var marker = item && item.marker ? item.marker : null;
                        if (!marker || !marker.getPosition) return null;
                        var p = marker.getPosition();
                        return p ? { lat: p.lat(), lng: p.lng() } : null;
                    }).filter(function(p) { return !!p; });
                    if (ptsRaw.length < 2) {
                        if (rastreoPolylinesPorGestorAlternasGrande[n] && rastreoPolylinesPorGestorAlternasGrande[n].poly) rastreoPolylinesPorGestorAlternasGrande[n].poly.setMap(null);
                        return;
                    }
                    if (!rastreoPolylinesPorGestorAlternasGrande[n] || !rastreoPolylinesPorGestorAlternasGrande[n].poly) {
                        var colorNG = rastreoColoresPorGestor[n] || \'#c2410c\';
                        rastreoPolylinesPorGestorAlternasGrande[n] = { poly: new google.maps.Polyline({ path: [], strokeColor: colorNG, strokeOpacity: 0.95, strokeWeight: 4, map: null, zIndex: 5 }), pathRaw: [] };
                    }
                    rastreoPolylinesPorGestorAlternasGrande[n].pathRaw = ptsRaw.slice();
                    var zNowG = typeof rastreoMapaAlternasGrande.getZoom === \'function\' ? rastreoMapaAlternasGrande.getZoom() : 10;
                    rastreoPolylinesPorGestorAlternasGrande[n].poly.setPath(pathConArcosParaSegmentosCortos(ptsRaw, 0.09, zNowG));
                    var mostrar = !showAll && selected.indexOf(n) !== -1;
                    rastreoPolylinesPorGestorAlternasGrande[n].poly.setMap(mostrar ? rastreoMapaAlternasGrande : null);
                });
            }
        }
        function initMapaRastreoAlternasGrande(puntosMaxiApp, puntosGestores, puntosGeo) {
            puntosGeo = puntosGeo || [];
            rastreoMarkersGeoAlternasGrande = [];
            rastreoInfoWindowsGeoAlternasGrande = [];
            rastreoMarkersPorGestorAlternasGrande = {};
            var cont = document.getElementById(\'rastreoMapaAlternasGrandeContenedor\');
            if (!cont) return;
            var oldOverlayG = cont.querySelector(\'.rastreo-filtro-gestor-overlay\');
            if (oldOverlayG) oldOverlayG.remove();
            var oldLeyendaG = cont.querySelector(\'.rastreo-leyenda-mapa-grande\');
            if (oldLeyendaG) oldLeyendaG.remove();
            if (!googleMapsApiKey || !googleMapsApiKey.length) return;
            if (typeof google === \'undefined\' || !google.maps) return;
            if (rastreoMapaAlternasGrande) { try { if (typeof rastreoMapaAlternasGrande.remove === \'function\') rastreoMapaAlternasGrande.remove(); } catch (e) {} rastreoMapaAlternasGrande = null; }
            if (rastreoPolylineAlternasGrande) { try { rastreoPolylineAlternasGrande.setMap(null); } catch (e) {} rastreoPolylineAlternasGrande = null; }
            Object.keys(rastreoPolylinesPorGestorAlternasGrande || {}).forEach(function(k) { var p = rastreoPolylinesPorGestorAlternasGrande[k]; if (p && p.poly && p.poly.setMap) p.poly.setMap(null); });
            rastreoPolylinesPorGestorAlternasGrande = {};
            if (typeof window !== \'undefined\') window.rastreoIconoPosiciones = [];
            rastreoPolylinePathRawAlternasGrande = [];
            rastreoMapaAlternasGrande = new google.maps.Map(cont, { center: { lat: 19.43, lng: -99.13 }, zoom: 10, mapTypeControl: true, streetViewControl: true, fullscreenControl: true, zoomControl: true });
            var bounds = new google.maps.LatLngBounds();
            var hasPoints = false;
            var iconNegroGrande = pinCirculoIcon(\'#000000\');
            if (puntosMaxiApp && puntosMaxiApp.length) {
                var esPuntos = puntosMaxiApp[0] && (puntosMaxiApp[0].latitud !== undefined || puntosMaxiApp[0].lat !== undefined);
                if (esPuntos) {
                    (puntosMaxiApp.length > 16 ? puntosMaxiApp.slice(0, 16) : puntosMaxiApp).forEach(function(p, i) {
                        var lat = parseFloat(p.latitud || p.lat), lon = parseFloat(p.longitud || p.lng);
                        if (isNaN(lat) || isNaN(lon)) return;
                        hasPoints = true;
                        var pos = { lat: lat, lng: lon };
                        bounds.extend(pos);
                        var visitas = p.cantidad_registros || 1;
                        var tipoLabel = p.punto_de_interes ? \'Punto de interés\' : \'Menos frecuente\';
                        var infoHtml = \'<strong>Cliente</strong>: \' + (rastreoDatosClienteActual.nombre || \'—\') + \'<br><strong>Crédito</strong>: \' + (rastreoDatosClienteActual.credito || \'—\') + \'<br><strong>Teléfono</strong>: \' + (rastreoDatosClienteActual.telefono || \'—\') + \'<br><strong>Ubicación</strong>: Obteniendo dirección...<br><strong>Visitas</strong>: \' + visitas + \'<br><strong>Tipo</strong>: \' + tipoLabel;
                        var res = crearPuntoConIconoFlotante(rastreoMapaAlternasGrande, pos, \'#2563eb\', \'fa-location-dot\', \'Dirección frecuente \' + (i + 1) + \' — \' + visitas + \' visitas\', infoHtml);
                        if (typeof google.maps.Geocoder !== \'undefined\' && res.infow) {
                            var geocoder = new google.maps.Geocoder();
                            geocoder.geocode({ location: pos }, function(results, status) {
                                if (status === \'OK\' && results[0] && res.infow) { var addr = (results[0].formatted_address || \'\').replace(/</g, \'&lt;\').replace(/>/g, \'&gt;\'); res.infow.setContent(\'<strong>Cliente</strong>: \' + (rastreoDatosClienteActual.nombre || \'—\') + \'<br><strong>Crédito</strong>: \' + (rastreoDatosClienteActual.credito || \'—\') + \'<br><strong>Teléfono</strong>: \' + (rastreoDatosClienteActual.telefono || \'—\') + \'<br><strong>Ubicación</strong>: \' + addr + \'<br><strong>Visitas</strong>: \' + visitas + \'<br><strong>Tipo</strong>: \' + tipoLabel); }
                            });
                        }
                    });
                }
            }
            if (puntosGestores && puntosGestores.length) {
                var seenGG = {}, idxGG = 0;
                puntosGestores.forEach(function(g) { var n = (g.nombre || \'—\').trim() || \'—\'; if (!seenGG[n]) { seenGG[n] = true; if (!rastreoColoresPorGestor[n]) rastreoColoresPorGestor[n] = rastreoPaletaColoresGestores[idxGG % rastreoPaletaColoresGestores.length]; idxGG++; } });
                puntosGestores.forEach(function(g, i) {
                    var lat = g.lat, lon = g.lng;
                    if (isNaN(lat) || isNaN(lon)) return;
                    hasPoints = true;
                    var pos = { lat: lat, lng: lon };
                    bounds.extend(pos);
                    var nombreGestorG = (g.nombre || \'—\').trim() || \'—\';
                    var colorG = \'#f97316\';
                    var numGotaG = g.numero != null ? g.numero : (i + 1);
                    var infoHtml = \'<strong>Gestor</strong>: \' + (g.nombre || \'—\') + \' <strong>#\' + numGotaG + \'</strong>\' + (g.fecha ? \'<br><strong>Fecha</strong>: \' + g.fecha : \'\');
                    var res = crearPuntoGestorConIconoFlotante(rastreoMapaAlternasGrande, pos, colorG, numGotaG, iconoFlotanteGestor(g.esCampo), (g.nombre || \'Gestor \') + (g.fecha ? \' — \' + g.fecha : \'\') + \' (#\' + numGotaG + \')\', infoHtml);
                    if (!rastreoMarkersPorGestorAlternasGrande[nombreGestorG]) rastreoMarkersPorGestorAlternasGrande[nombreGestorG] = [];
                    rastreoMarkersPorGestorAlternasGrande[nombreGestorG].push({ marker: res.marker, overlay: res.overlay, numero: numGotaG });
                });
                var pathGestoresG = puntosGestores.slice().sort(function(a, b) { return (a.numero != null ? a.numero : 0) - (b.numero != null ? b.numero : 0); }).map(function(g) { return { lat: g.lat, lng: g.lng }; }).filter(function(p) { return !isNaN(parseFloat(p.lat)) && !isNaN(parseFloat(p.lng)); });
                rastreoPolylinePathRawAlternasGrande = pathGestoresG.slice();
                if (typeof window !== \'undefined\') window.rastreoPolylinePathLatLngGrande = pathGestoresG.slice();
                var puntosPorGestorG = {};
                puntosGestores.forEach(function(g) {
                    var n = (g.nombre || \'—\').trim() || \'—\';
                    if (!puntosPorGestorG[n]) puntosPorGestorG[n] = [];
                    puntosPorGestorG[n].push({ lat: g.lat, lng: g.lng, numero: g.numero != null ? g.numero : 999 });
                });
                var zoomG = typeof rastreoMapaAlternasGrande.getZoom === \'function\' ? rastreoMapaAlternasGrande.getZoom() : 10;
                Object.keys(puntosPorGestorG).forEach(function(nomG) {
                    var pts = puntosPorGestorG[nomG].sort(function(a,b){ return a.numero - b.numero; }).map(function(p){ return { lat: p.lat, lng: p.lng }; });
                    if (pts.length >= 2) {
                        var pathCurvG = pathConArcosParaSegmentosCortos(pts, 0.09, zoomG);
                        var colorPlG = rastreoColoresPorGestor[nomG] || \'#c2410c\';
                        var plG = new google.maps.Polyline({ path: pathCurvG, strokeColor: colorPlG, strokeOpacity: 0.95, strokeWeight: 4, map: null, zIndex: 5 });
                        rastreoPolylinesPorGestorAlternasGrande[nomG] = { poly: plG, pathRaw: pts.slice() };
                    }
                });
            }
            if (puntosGeo && puntosGeo.length) {
                puntosGeo.forEach(function(p, i) {
                    var lat = parseFloat(p.lat), lon = parseFloat(p.lng);
                    if (isNaN(lat) || isNaN(lon)) return;
                    hasPoints = true;
                    var pos = { lat: lat, lng: lon };
                    bounds.extend(pos);
                    var donde = (p.donde_firma || \'\').replace(/</g, \'&lt;\').replace(/>/g, \'&gt;\');
                    var dir = (p.direccion_maps || \'\').replace(/</g, \'&lt;\').replace(/>/g, \'&gt;\');
                    var q = encodeURIComponent(p.direccion_maps || lat + \',\' + lon);
                    var infoHtml = \'<strong>Donde firma:</strong> \' + (donde || \'—\') + \'<br><strong>Dirección</strong>: \' + (dir || \'—\') + \'<br><strong>Cliente</strong>: \' + (rastreoDatosClienteActual.nombre || \'—\') + \'<br><strong>Crédito</strong>: \' + (rastreoDatosClienteActual.credito || \'—\') + \'<br><a href="https://www.google.com/maps/search/?api=1&query=\' + q + \'" target="_blank" rel="noopener">Abrir en Google Maps</a>\';
                    var d = (p.donde_firma || \'\').toString().trim().toUpperCase();
                    var colorGeo = (d.indexOf(\'AGENCIA\') !== -1) ? \'#d4a574\' : (d.indexOf(\'CASA\') !== -1) ? \'#ec4899\' : \'#22c55e\';
                    var resGeo = crearPuntoConIconoFlotante(rastreoMapaAlternasGrande, pos, colorGeo, iconoFlotanteGeo(p.donde_firma), donde || \'Dirección geo \' + (i+1), infoHtml);
                    rastreoMarkersGeoAlternasGrande.push(resGeo.marker);
                    rastreoInfoWindowsGeoAlternasGrande.push(resGeo.infow);
                });
            }
            if (rastreoDomicilioMegareporte && rastreoDomicilioMegareporte.lat != null && rastreoDomicilioMegareporte.lng != null) {
                hasPoints = true;
                var posMegareporteGrande = { lat: parseFloat(rastreoDomicilioMegareporte.lat), lng: parseFloat(rastreoDomicilioMegareporte.lng) };
                if (!isNaN(posMegareporteGrande.lat) && !isNaN(posMegareporteGrande.lng)) {
                    bounds.extend(posMegareporteGrande);
                    var dirMegareporteGrande = (rastreoDomicilioMegareporte.direccion || rastreoDatosClienteActual.direccion || \'—\').replace(/</g, \'&lt;\').replace(/>/g, \'&gt;\');
                    var qMegareporteGrande = encodeURIComponent(dirMegareporteGrande || posMegareporteGrande.lat + \',\' + posMegareporteGrande.lng);
                    var infoHtmlMegareporteGrande = \'<strong>Dirección megareporte</strong><br><strong>Dirección</strong>: \' + dirMegareporteGrande + \'<br><strong>Cliente</strong>: \' + (rastreoDatosClienteActual.nombre || \'—\') + \'<br><strong>Crédito</strong>: \' + (rastreoDatosClienteActual.credito || \'—\') + \'<br><a href="https://www.google.com/maps/search/?api=1&query=\' + qMegareporteGrande + \'" target="_blank" rel="noopener">Abrir en Google Maps</a>\';
                    var markerMegareporteGrande = new google.maps.Marker({ position: posMegareporteGrande, map: rastreoMapaAlternasGrande, icon: iconNegroGrande, title: \'Dirección megareporte (casa)\', zIndex: 10 });
                    var infowMegareporteGrande = new google.maps.InfoWindow({ content: infoHtmlMegareporteGrande });
                    markerMegareporteGrande.addListener(\'click\', function() { infowMegareporteGrande.open(rastreoMapaAlternasGrande, markerMegareporteGrande); });
                    var idxMegG = (typeof window !== \'undefined\' && window.rastreoIconoPosiciones && Array.isArray(window.rastreoIconoPosiciones)) ? window.rastreoIconoPosiciones.length : 0;
                    if (typeof window !== \'undefined\' && window.rastreoIconoPosiciones && Array.isArray(window.rastreoIconoPosiciones)) window.rastreoIconoPosiciones.push({ lat: posMegareporteGrande.lat, lng: posMegareporteGrande.lng, idx: idxMegG });
                    ensureIconoFlotanteOverlayReady();
                    var overlayMegareporteGrande = new IconoFlotanteOverlay(posMegareporteGrande, \'fa-megareporte\', \'#000000\');
                    overlayMegareporteGrande._idx = idxMegG;
                    overlayMegareporteGrande.setMap(rastreoMapaAlternasGrande);
                }
            }
            addClusterLabelsToMap(rastreoMapaAlternasGrande, todosPuntosParaCluster(puntosGeo, puntosMaxiApp, puntosGestores));
            if (rastreoMapaAlternasGrande.addListener) {
                rastreoMapaAlternasGrande.addListener(\'zoom_changed\', function() { addClusterLabelsToMap(rastreoMapaAlternasGrande, rastreoPuntosClusterActual); });
                rastreoMapaAlternasGrande.addListener(\'zoom_changed\', function() {
                    var zG = rastreoMapaAlternasGrande.getZoom();
                    Object.keys(rastreoPolylinesPorGestorAlternasGrande || {}).forEach(function(k) {
                        var obj = rastreoPolylinesPorGestorAlternasGrande[k];
                        if (obj && obj.poly && obj.pathRaw && obj.pathRaw.length >= 2) obj.poly.setPath(pathConArcosParaSegmentosCortos(obj.pathRaw, 0.09, zG));
                    });
                });
                google.maps.event.addDomListener(window, \'resize\', function() { if (rastreoMapaAlternasGrande) addClusterLabelsToMap(rastreoMapaAlternasGrande, rastreoPuntosClusterActual); });
            }
            var conU = (typeof rastreoConUbicacion !== \'undefined\' ? rastreoConUbicacion : (puntosGestores && puntosGestores.length) ? puntosGestores.length : 0);
            var totG = (typeof rastreoTotalGestiones !== \'undefined\' ? rastreoTotalGestiones : 0) || conU;
            var tiposPresentes = { casa: false, otroDomicilio: false, agencia: false, maxiApp: false, gestores: false };
            if (puntosGeo && puntosGeo.length) {
                puntosGeo.forEach(function(p) {
                    var d = (p.donde_firma || \'\').toString().trim().toUpperCase();
                    if (d.indexOf(\'CASA\') !== -1) tiposPresentes.casa = true;
                    else if (d.indexOf(\'AGENCIA\') !== -1) tiposPresentes.agencia = true;
                    else tiposPresentes.otroDomicilio = true;
                });
            }
            if (puntosMaxiApp && puntosMaxiApp.length) {
                var esPuntos = puntosMaxiApp[0] && (puntosMaxiApp[0].latitud !== undefined || puntosMaxiApp[0].lat !== undefined);
                if (esPuntos) tiposPresentes.maxiApp = true;
            }
            if (puntosGestores && puntosGestores.length) tiposPresentes.gestores = true;
            var leyendaGrandeHtml = \'\';
            if (tiposPresentes.casa) leyendaGrandeHtml += \'<span class="d-block rastreo-leyenda-item cursor-pointer d-flex align-items-center gap-1" data-tipo-leyenda="casa" style="cursor:pointer;padding:2px 4px;border-radius:4px;" onmouseover="this.style.background=\\\'rgba(236,72,153,0.1)\\\'" onmouseout="this.style.background=\\\'transparent\\\'"><span class="rounded-circle d-inline-block" style="width:10px;height:10px;background:#ec4899;flex-shrink:0;"></span> 🏠 = <span style="color:#ec4899 !important;font-weight:600 !important;">CASA.</span></span>\';
            if (tiposPresentes.otroDomicilio) leyendaGrandeHtml += \'<span class="d-block rastreo-leyenda-item cursor-pointer d-flex align-items-center gap-1" data-tipo-leyenda="otroDomicilio" style="cursor:pointer;padding:2px 4px;border-radius:4px;" onmouseover="this.style.background=\\\'rgba(34,197,94,0.1)\\\'" onmouseout="this.style.background=\\\'transparent\\\'"><span class="rounded-circle d-inline-block" style="width:10px;height:10px;background:#22c55e;flex-shrink:0;"></span> 🏘️ = <span style="color:#22c55e !important;font-weight:600 !important;">Otro domicilio.</span></span>\';
            if (tiposPresentes.agencia) leyendaGrandeHtml += \'<span class="d-block rastreo-leyenda-item cursor-pointer d-flex align-items-center gap-1" data-tipo-leyenda="agencia" style="cursor:pointer;padding:2px 4px;border-radius:4px;" onmouseover="this.style.background=\\\'rgba(184,134,11,0.1)\\\'" onmouseout="this.style.background=\\\'transparent\\\'"><span class="rounded-circle d-inline-block" style="width:10px;height:10px;background:#b8860b;flex-shrink:0;"></span> 🏢 = <span style="color:#b8860b !important;font-weight:600 !important;">Agencia.</span></span>\';
            if (tiposPresentes.maxiApp) leyendaGrandeHtml += \'<span class="d-block rastreo-leyenda-item cursor-pointer d-flex align-items-center gap-1" data-tipo-leyenda="maxiApp" style="cursor:pointer;padding:2px 4px;border-radius:4px;" onmouseover="this.style.background=\\\'rgba(37,99,235,0.1)\\\'" onmouseout="this.style.background=\\\'transparent\\\'"><span class="rounded-circle d-inline-block" style="width:10px;height:10px;background:#2563eb;flex-shrink:0;"></span> <span style="color:#2563eb !important;font-weight:600 !important;">maxi app.</span></span>\';
            if (rastreoDomicilioMegareporte && rastreoDomicilioMegareporte.lat != null && rastreoDomicilioMegareporte.lng != null) leyendaGrandeHtml += \'<span class="d-block rastreo-leyenda-item cursor-pointer d-flex align-items-center gap-1" data-tipo-leyenda="megareporte" style="cursor:pointer;padding:2px 4px;border-radius:4px;" onmouseover="this.style.background=\\\'rgba(0,0,0,0.1)\\\'" onmouseout="this.style.background=\\\'transparent\\\'"><span class="rounded-circle d-inline-block" style="width:10px;height:10px;background:#000000;border:1px solid #333;flex-shrink:0;"></span> 🏡 = <span style="color:#000000 !important;font-weight:600 !important;">Casa megareporte.</span></span>\';
            if (tiposPresentes.gestores) leyendaGrandeHtml += \'<span class="d-block">Gestores = <span style="font-weight:600">cada asesor con su color.</span></span>\';
            leyendaGrandeHtml += \'<span class="d-block rastreo-leyenda-conteo" style="color:#0f172a !important;font-weight:700 !important;">\' + conU + \' de \' + totG + \' con ubicación (máx. 16 gestores en mapa).</span><span class="d-block small text-muted mt-1">Use el botón 🔍 Lupa y luego clic en el mapa para ampliar una zona.</span>\';
            if (puntosGestores && puntosGestores.length) {
                var seenGG = {}, nombresGG = [];
                puntosGestores.forEach(function(g) { var n = (g.nombre || \'—\').trim() || \'—\'; if (!seenGG[n]) { seenGG[n] = true; nombresGG.push(n); } });
                var escOG = function(s) { if (s == null || s === undefined) return \'\'; return (s+\'\').replace(/&/g, \'&amp;\').replace(/</g, \'&lt;\').replace(/>/g, \'&gt;\').replace(/\"/g, \'&quot;\'); };
                var htmlOG = \'<div class="rastreo-filtro-gestor-overlay" style="position:absolute;top:52px;right:8px;left:auto;z-index:10;background:rgba(255,255,255,0.95);padding:6px 8px;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,0.2);pointer-events:auto;"><button type="button" class="btn btn-sm btn-outline-secondary mb-2 rastreo-btn-lupa-mapa" id="rastreoBtnLupaGrande" title="Clic aquí y luego en el mapa para ampliar una zona">🔍 Lupa</button><span class="small text-muted d-block mb-1">Filtrar por asesor (puede elegir varios)</span><div class="d-flex flex-column gap-1"><label class="d-flex align-items-center gap-2 mb-0 cursor-pointer"><input type="checkbox" class="rastreo-filtro-gestor-cb form-check-input" data-gestor=""> <span>Todos</span></label>\';
                nombresGG.forEach(function(n) { var color = rastreoColoresPorGestor[n] || \'#6b7280\'; htmlOG += \'<label class="d-flex align-items-center gap-2 mb-0 cursor-pointer"><input type="checkbox" class="rastreo-filtro-gestor-cb form-check-input" data-gestor="\' + escOG(n) + \'"> <span class="rounded-circle d-inline-block" style="width:10px;height:10px;background:\' + color + \';flex-shrink:0"></span><span>\' + escOG(n) + \'</span></label>\'; });
                htmlOG += \'</div><div class="rastreo-leyenda-mapa-grande mt-2 small text-muted" style="max-width:280px;">\' + leyendaGrandeHtml + \'</div></div>\';
                cont.insertAdjacentHTML(\'beforeend\', htmlOG);
                (function() {
                    var ov = cont.querySelector(\'.rastreo-filtro-gestor-overlay\');
                    if (!ov) return;
                    var btnLupaG = ov.querySelector(\'#rastreoBtnLupaGrande\');
                    if (btnLupaG) btnLupaG.addEventListener(\'click\', function() { activarLupaConClicEnMapa(rastreoMapaAlternasGrande); });
                    var sel = rastreoFiltroGestoresSeleccionados || [];
                    var cbs = ov.querySelectorAll(\'.rastreo-filtro-gestor-cb\');
                    for (var i = 0; i < cbs.length; i++) {
                        var g = cbs[i].getAttribute(\'data-gestor\');
                        if (g === \'\' || g === null) { cbs[i].checked = (sel.length === 0); continue; }
                        cbs[i].checked = (sel.indexOf(g) !== -1);
                    }
                    var itemsLeyenda = ov.querySelectorAll(\'.rastreo-leyenda-item\');
                    itemsLeyenda.forEach(function(item) {
                        item.addEventListener(\'click\', function() {
                            var tipo = this.getAttribute(\'data-tipo-leyenda\');
                            var boundsLeyenda = new google.maps.LatLngBounds();
                            var tienePuntos = false;
                            if (tipo === \'casa\' && puntosGeo && puntosGeo.length) {
                                puntosGeo.forEach(function(p) {
                                    var d = (p.donde_firma || \'\').toString().trim().toUpperCase();
                                    if (d.indexOf(\'CASA\') !== -1) {
                                        var lat = parseFloat(p.lat), lon = parseFloat(p.lng);
                                        if (!isNaN(lat) && !isNaN(lon)) {
                                            boundsLeyenda.extend({ lat: lat, lng: lon });
                                            tienePuntos = true;
                                        }
                                    }
                                });
                            } else if (tipo === \'otroDomicilio\' && puntosGeo && puntosGeo.length) {
                                puntosGeo.forEach(function(p) {
                                    var d = (p.donde_firma || \'\').toString().trim().toUpperCase();
                                    if (d.indexOf(\'CASA\') === -1 && d.indexOf(\'AGENCIA\') === -1) {
                                        var lat = parseFloat(p.lat), lon = parseFloat(p.lng);
                                        if (!isNaN(lat) && !isNaN(lon)) {
                                            boundsLeyenda.extend({ lat: lat, lng: lon });
                                            tienePuntos = true;
                                        }
                                    }
                                });
                            } else if (tipo === \'agencia\' && puntosGeo && puntosGeo.length) {
                                puntosGeo.forEach(function(p) {
                                    var d = (p.donde_firma || \'\').toString().trim().toUpperCase();
                                    if (d.indexOf(\'AGENCIA\') !== -1) {
                                        var lat = parseFloat(p.lat), lon = parseFloat(p.lng);
                                        if (!isNaN(lat) && !isNaN(lon)) {
                                            boundsLeyenda.extend({ lat: lat, lng: lon });
                                            tienePuntos = true;
                                        }
                                    }
                                });
                            } else if (tipo === \'maxiApp\' && puntosMaxiApp && puntosMaxiApp.length) {
                                var esPuntos = puntosMaxiApp[0] && (puntosMaxiApp[0].latitud !== undefined || puntosMaxiApp[0].lat !== undefined);
                                if (esPuntos) {
                                    puntosMaxiApp.forEach(function(p) {
                                        var lat = parseFloat(p.latitud || p.lat), lon = parseFloat(p.longitud || p.lng);
                                        if (!isNaN(lat) && !isNaN(lon)) {
                                            boundsLeyenda.extend({ lat: lat, lng: lon });
                                            tienePuntos = true;
                                        }
                                    });
                                }
                            } else if (tipo === \'megareporte\' && rastreoDomicilioMegareporte && rastreoDomicilioMegareporte.lat != null && rastreoDomicilioMegareporte.lng != null) {
                                var lat = parseFloat(rastreoDomicilioMegareporte.lat), lon = parseFloat(rastreoDomicilioMegareporte.lng);
                                if (!isNaN(lat) && !isNaN(lon)) {
                                    boundsLeyenda.extend({ lat: lat, lng: lon });
                                    tienePuntos = true;
                                }
                            }
                            if (tienePuntos && rastreoMapaAlternasGrande) {
                                rastreoMapaAlternasGrande.fitBounds(boundsLeyenda, 50);
                            }
                        });
                    });
                })();
            } else if (hasPoints) {
                var htmlLeyenda = \'<div class="rastreo-filtro-gestor-overlay" style="position:absolute;top:52px;right:8px;left:auto;z-index:10;background:rgba(255,255,255,0.95);padding:6px 8px;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,0.2);pointer-events:auto;"><div class="rastreo-leyenda-mapa-grande small text-muted" style="max-width:280px;">\' + leyendaGrandeHtml + \'</div></div>\';
                cont.insertAdjacentHTML(\'beforeend\', htmlLeyenda);
                (function() {
                    var ov = cont.querySelector(\'.rastreo-filtro-gestor-overlay\');
                    if (!ov) return;
                    var itemsLeyenda = ov.querySelectorAll(\'.rastreo-leyenda-item\');
                    itemsLeyenda.forEach(function(item) {
                        item.addEventListener(\'click\', function() {
                            var tipo = this.getAttribute(\'data-tipo-leyenda\');
                            var boundsLeyenda = new google.maps.LatLngBounds();
                            var tienePuntos = false;
                            if (tipo === \'casa\' && puntosGeo && puntosGeo.length) {
                                puntosGeo.forEach(function(p) {
                                    var d = (p.donde_firma || \'\').toString().trim().toUpperCase();
                                    if (d.indexOf(\'CASA\') !== -1) {
                                        var lat = parseFloat(p.lat), lon = parseFloat(p.lng);
                                        if (!isNaN(lat) && !isNaN(lon)) {
                                            boundsLeyenda.extend({ lat: lat, lng: lon });
                                            tienePuntos = true;
                                        }
                                    }
                                });
                            } else if (tipo === \'otroDomicilio\' && puntosGeo && puntosGeo.length) {
                                puntosGeo.forEach(function(p) {
                                    var d = (p.donde_firma || \'\').toString().trim().toUpperCase();
                                    if (d.indexOf(\'CASA\') === -1 && d.indexOf(\'AGENCIA\') === -1) {
                                        var lat = parseFloat(p.lat), lon = parseFloat(p.lng);
                                        if (!isNaN(lat) && !isNaN(lon)) {
                                            boundsLeyenda.extend({ lat: lat, lng: lon });
                                            tienePuntos = true;
                                        }
                                    }
                                });
                            } else if (tipo === \'agencia\' && puntosGeo && puntosGeo.length) {
                                puntosGeo.forEach(function(p) {
                                    var d = (p.donde_firma || \'\').toString().trim().toUpperCase();
                                    if (d.indexOf(\'AGENCIA\') !== -1) {
                                        var lat = parseFloat(p.lat), lon = parseFloat(p.lng);
                                        if (!isNaN(lat) && !isNaN(lon)) {
                                            boundsLeyenda.extend({ lat: lat, lng: lon });
                                            tienePuntos = true;
                                        }
                                    }
                                });
                            } else if (tipo === \'maxiApp\' && puntosMaxiApp && puntosMaxiApp.length) {
                                var esPuntos = puntosMaxiApp[0] && (puntosMaxiApp[0].latitud !== undefined || puntosMaxiApp[0].lat !== undefined);
                                if (esPuntos) {
                                    puntosMaxiApp.forEach(function(p) {
                                        var lat = parseFloat(p.latitud || p.lat), lon = parseFloat(p.longitud || p.lng);
                                        if (!isNaN(lat) && !isNaN(lon)) {
                                            boundsLeyenda.extend({ lat: lat, lng: lon });
                                            tienePuntos = true;
                                        }
                                    });
                                }
                            } else if (tipo === \'megareporte\' && rastreoDomicilioMegareporte && rastreoDomicilioMegareporte.lat != null && rastreoDomicilioMegareporte.lng != null) {
                                var lat = parseFloat(rastreoDomicilioMegareporte.lat), lon = parseFloat(rastreoDomicilioMegareporte.lng);
                                if (!isNaN(lat) && !isNaN(lon)) {
                                    boundsLeyenda.extend({ lat: lat, lng: lon });
                                    tienePuntos = true;
                                }
                            }
                            if (tienePuntos && rastreoMapaAlternasGrande) {
                                rastreoMapaAlternasGrande.fitBounds(boundsLeyenda, 50);
                            }
                        });
                    });
                })();
            }
            if (hasPoints) rastreoMapaAlternasGrande.fitBounds(bounds, 50);
            filtrarMapaPorGestor(rastreoFiltroGestoresSeleccionados.length ? rastreoFiltroGestoresSeleccionados : null);
            setTimeout(function() { if (rastreoMapaAlternasGrande && typeof rastreoMapaAlternasGrande.invalidateSize === \'function\') rastreoMapaAlternasGrande.invalidateSize(); }, 150);
        }
        function cargarEvidenciasRastreo() {
            if (!ticketIdRastreoActual) { renderEvidenciasSlots([]); return; }
            http.request({ endpoint: "/sabueso/getEvidenciasTicket", metodo: "POST", data: JSON.stringify({ id_ticket: ticketIdRastreoActual }), contentType: "application/json", processData: false, onSuccess: function(r) {
                evidenciasRastreoActual = r.datos || []; renderEvidenciasSlots(evidenciasRastreoActual);
            } });
        }
        function renderEvidenciasSlots(lista) {
            var html = \'\';
            html += \'<div class="col-6"><div class="evidencia-slot" data-slot="0" data-id="" title="Clic para cargar"><i class="fa-solid fa-plus text-muted"></i><span class="evidencia-slot-label">Agregar</span></div></div>\';
            var i, e;
            for (i = lista.length - 1; i >= 0; i--) {
                e = lista[i];
                if (e && e.url) {
                    html += \'<div class="col-6"><div class="evidencia-slot" data-slot="\' + (lista.length - i) + \'" data-id="\' + (e.id || \'\') + \'" title="Clic para ver o eliminar"><img src="\' + (e.url || \'\') + \'" alt="Evidencia"></div></div>\';
                }
            }
            $(\'#rastreoEvidenciasSlots\').html(html);
            $(\'#rastreoEvidenciasSlots .evidencia-slot\').off(\'click\').on(\'click\', function() {
                var id = ($(this).attr(\'data-id\') || \'\').trim();
                var slot = parseInt($(this).attr(\'data-slot\') || 0, 10);
                if (id) {
                    evidenciaModalId = parseInt(id, 10); evidenciaModalSlot = null;
                    $(\'#modalEvidenciaRastreoBody\').html(\'<img src="/sabueso/verEvidencia?id=\' + id + \'" class="img-fluid" alt="Evidencia">\');
                    $(\'#modalEvidenciaEliminar\').show(); $(\'#modalEvidenciaRastreo\').modal(\'show\');
                } else {
                    evidenciaModalId = null; evidenciaModalSlot = slot;
                    $(\'#modalEvidenciaEliminar\').hide(); $(\'#modalEvidenciaRastreo\').modal(\'hide\'); $(\'#inputEvidenciaRastreo\').val(\'\'); $(\'#inputEvidenciaRastreo\').click();
                }
            });
        }
        function initMapaRastreo(addressesOrPuntos) {
            var cont = document.getElementById(\'rastreoMapaLeaflet\');
            if (!cont) return;
            rastreoMarkersMaxiApp = [];
            if (rastreoMapaLeaflet) { if (typeof rastreoMapaLeaflet.remove === \'function\') rastreoMapaLeaflet.remove(); rastreoMapaLeaflet = null; }
            if (googleMapsApiKey && googleMapsApiKey.length > 0) {
                function initGoogleMap() {
                    if (typeof google === \'undefined\' || !google.maps) return;
                    rastreoMapaLeaflet = new google.maps.Map(cont, { center: { lat: 19.43, lng: -99.13 }, zoom: 10, mapTypeControl: true, streetViewControl: true, fullscreenControl: true, zoomControl: true });
                    var bounds = new google.maps.LatLngBounds();
                    var hasPoints = false;
                    if (addressesOrPuntos && addressesOrPuntos.length) {
                        var esPuntos = addressesOrPuntos[0] && (addressesOrPuntos[0].latitud !== undefined || addressesOrPuntos[0].lat !== undefined);
                        if (esPuntos) {
                            var lat0 = null, lon0 = null;
                            if (rastreoIndiceCasa !== null && rastreoIndiceCasa >= 0 && rastreoIndiceCasa < addressesOrPuntos.length) {
                                var pc = addressesOrPuntos[rastreoIndiceCasa];
                                lat0 = parseFloat(pc.latitud || pc.lat); lon0 = parseFloat(pc.longitud || pc.lng);
                                if (!isNaN(lat0) && !isNaN(lon0)) { /* origen = punto dentro del rango de Megareporte */ }
                                else { lat0 = null; lon0 = null; }
                            }
                            if (lat0 === null && rastreoDomicilioMegareporte) {
                                lat0 = rastreoDomicilioMegareporte.lat; lon0 = rastreoDomicilioMegareporte.lng;
                            }
                            if (lat0 === null && addressesOrPuntos.length) {
                                var pr = addressesOrPuntos[0];
                                lat0 = parseFloat(pr.latitud || pr.lat); lon0 = parseFloat(pr.longitud || pr.lng);
                                if (isNaN(lat0) || isNaN(lon0)) lat0 = null; else {}
                            }
                            if (lat0 !== null && lon0 !== null && rastreoDomicilioMegareporte && rastreoIndiceCasa === null) {
                                bounds.extend({ lat: lat0, lng: lon0 });
                                new google.maps.Marker({ position: { lat: lat0, lng: lon0 }, map: rastreoMapaLeaflet, icon: pinGotaIcon(\'#059669\'), title: \'Domicilio reportado (Megareporte)\', zIndex: 2 });
                            }
                            addressesOrPuntos.forEach(function(p, i) {
                                var lat = parseFloat(p.latitud || p.lat), lon = parseFloat(p.longitud || p.lng);
                                if (isNaN(lat) || isNaN(lon)) return;
                                hasPoints = true;
                                var pos = { lat: lat, lng: lon };
                                bounds.extend(pos);
                                var visitas = p.cantidad_registros || 1;
                                var color = \'#2563eb\';
                                var iconGota = pinGotaIcon(color);
                                var marker = new google.maps.Marker({ position: pos, map: rastreoMapaLeaflet, icon: iconGota, title: (p.punto_de_interes ? \'Punto de interés \' : \'Menos frecuente \') + (i + 1) + \' — \' + visitas + \' visitas\' });
                                var esCasa = (rastreoIndiceCasa !== null && i === rastreoIndiceCasa);
                                var distStr = esCasa ? \'Su casa (domicilio reportado)\' : (lat0 === null ? \'—\' : \'Distancia desde su casa: \' + formatDistanciaMapa(haversineMetrosMapa(lat0, lon0, lat, lon)));
                                if (!esCasa && lat0 !== null) {
                                    var from0 = { lat: lat0, lng: lon0 };
                                    new google.maps.Polyline({ path: [from0, pos], map: rastreoMapaLeaflet, strokeColor: color, strokeWeight: 2, strokeOpacity: 0.85 });
                                    var latMid = (lat0 + lat) / 2, lngMid = (lon0 + lon) / 2;
                                    var distLabel = formatDistanciaMapa(haversineMetrosMapa(lat0, lon0, lat, lon));
                                    new google.maps.Marker({ position: { lat: latMid, lng: lngMid }, map: rastreoMapaLeaflet, icon: labelDistanciaIcon(distLabel), clickable: false, zIndex: 1 });
                                }
                                var dirTexto = (p.texto || \'\').trim();
                                var datosHtml = \'<strong>Cliente:</strong> \' + (rastreoDatosClienteActual.nombre || \'—\') + \'<br><strong>Crédito:</strong> \' + (rastreoDatosClienteActual.credito || \'—\') + \'<br><strong>Teléfono:</strong> \' + (rastreoDatosClienteActual.telefono || \'—\') + \'<br><strong>Ubicación:</strong> \' + (dirTexto || \'Obteniendo dirección...\') + \'<br><strong>Visitas:</strong> \' + visitas + \'<br><strong>Tipo:</strong> \' + (p.punto_de_interes ? \'Punto de interés\' : \'Menos frecuente\') + \'<br><strong>Distancia:</strong> \' + distStr;
                                var infow = new google.maps.InfoWindow({ content: datosHtml });
                                if (!dirTexto && typeof google.maps.Geocoder !== \'undefined\') {
                                    var geocoder = new google.maps.Geocoder();
                                    geocoder.geocode({ location: pos }, function(results, status) {
                                        if (status === \'OK\' && results[0] && infow) {
                                            var addr = (results[0].formatted_address || \'\').replace(/</g, \'&lt;\');
                                            var html = \'<strong>Cliente:</strong> \' + (rastreoDatosClienteActual.nombre || \'—\') + \'<br><strong>Crédito:</strong> \' + (rastreoDatosClienteActual.credito || \'—\') + \'<br><strong>Teléfono:</strong> \' + (rastreoDatosClienteActual.telefono || \'—\') + \'<br><strong>Ubicación:</strong> \' + addr + \'<br><strong>Visitas:</strong> \' + visitas + \'<br><strong>Tipo:</strong> \' + (p.punto_de_interes ? \'Punto de interés\' : \'Menos frecuente\') + \'<br><strong>Distancia:</strong> \' + distStr;
                                            infow.setContent(html);
                                        }
                                    });
                                }
                                (function(m, w) { m.addListener(\'click\', function() { w.open(rastreoMapaLeaflet, m); }); })(marker, infow);
                                rastreoMarkersMaxiApp[i] = { marker: marker, infow: infow };
                            });
                            addClusterLabelsToMap(rastreoMapaLeaflet, addressesOrPuntos);
                            if (hasPoints) rastreoMapaLeaflet.fitBounds(bounds, 50);
                        } else {
                            addressesOrPuntos.forEach(function(addr, i) {
                                if (!addr || !(addr+\'\').trim()) return;
                                var geocoder = new google.maps.Geocoder();
                                geocoder.geocode({ address: (addr+\'\').trim() }, function(results, status) {
                                    if (status === \'OK\' && results[0] && rastreoMapaLeaflet) {
                                        var loc = results[0].geometry.location;
                                        bounds.extend(loc);
                                        var m = new google.maps.Marker({ position: loc, map: rastreoMapaLeaflet, title: (addr+\'\').substring(0, 80) });
                                        var datosHtml = \'<strong>Cliente:</strong> \' + (rastreoDatosClienteActual.nombre || \'—\') + \'<br><strong>Crédito:</strong> \' + (rastreoDatosClienteActual.credito || \'—\') + \'<br><strong>Teléfono:</strong> \' + (rastreoDatosClienteActual.telefono || \'—\') + \'<br><strong>Dirección:</strong> \' + (addr+\'\').replace(/</g, \'&lt;\').substring(0, 120) + (addr.length > 120 ? \'...\' : \'\');
                                        var w = new google.maps.InfoWindow({ content: datosHtml });
                                        m.addListener(\'click\', function() { w.open(rastreoMapaLeaflet, m); });
                                        if (!hasPoints) { rastreoMapaLeaflet.setCenter(loc); rastreoMapaLeaflet.setZoom(16); }
                                        hasPoints = true;
                                    }
                                });
                            });
                        }
                    }
                }
                if (typeof google !== \'undefined\' && google.maps) { initGoogleMap(); return; }
                if (document.querySelector(\'script[src*="maps.googleapis.com"]\')) { setTimeout(initGoogleMap, 500); return; }
                var s = document.createElement(\'script\');
                s.src = \'https://maps.googleapis.com/maps/api/js?key=\' + googleMapsApiKey;
                s.async = true; s.defer = true;
                s.onload = function() { initGoogleMap(); };
                document.head.appendChild(s);
                return;
            }
            var L = (typeof leaFlet !== \'undefined\' ? leaFlet : (typeof L !== \'undefined\' ? L : null));
            if (!L) return;
            try {
                if (L.Icon && L.Icon.Default) L.Icon.Default.imagePath = \'/assets/vendor/libs/leaflet/images/\';
                rastreoMapaLeaflet = L.map(\'rastreoMapaLeaflet\', { center: [19.43, -99.13], zoom: 10 });
                var isDarkRastreo = document.body && document.body.classList.contains(\'dark-mode\');
                var tileUrlRastreo = isDarkRastreo ? \'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}.png\' : \'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}.png\';
                L.tileLayer(tileUrlRastreo, { attribution: \'&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>\', subdomains: \'abcd\', maxZoom: 20 }).addTo(rastreoMapaLeaflet);
                var zoomDireccion = 16;
                var estiloDireccionPrincipal = { color: \'#dc2626\', fillColor: \'#ef4444\', fillOpacity: 0.7, weight: 3, radius: 14 };
                if (addressesOrPuntos && addressesOrPuntos.length) {
                    var esPuntos = addressesOrPuntos[0] && (addressesOrPuntos[0].latitud !== undefined || addressesOrPuntos[0].lat !== undefined);
                    if (esPuntos) {
                        var bounds = [];
                        var maxVisitas = 1;
                        addressesOrPuntos.forEach(function(p) { var v = p.cantidad_registros || 1; if (v > maxVisitas) maxVisitas = v; });
                        addressesOrPuntos.forEach(function(p, i) {
                            var lat = parseFloat(p.latitud || p.lat), lon = parseFloat(p.longitud || p.lng);
                            if (isNaN(lat) || isNaN(lon)) return;
                            bounds.push([lat, lon]);
                            var visitas = p.cantidad_registros || 1;
                            var radius = Math.min(10 + (visitas / maxVisitas) * 14, 24);
                            var isFirst = (i === 0);
                            var opts = isFirst ? { color: \'#dc2626\', fillColor: \'#ef4444\', fillOpacity: 0.8, weight: 3, radius: radius } : { color: (p.punto_de_interes ? \'#2563eb\' : \'#6b7280\'), fillColor: (p.punto_de_interes ? \'#3b82f6\' : \'#9ca3af\'), fillOpacity: 0.7, weight: 2, radius: radius };
                            var popup = (p.punto_de_interes ? \'Punto de interés \' : \'Menos frecuente \') + (i + 1) + \' — \' + visitas + \' visitas\';
                            L.circleMarker([lat, lon], opts).addTo(rastreoMapaLeaflet).bindPopup(popup);
                            if (isFirst) rastreoMapaLeaflet.setView([lat, lon], zoomDireccion);
                        });
                        if (bounds.length > 1 && L.latLngBounds) rastreoMapaLeaflet.fitBounds(L.latLngBounds(bounds), { padding: [30, 30], maxZoom: zoomDireccion });
                    } else {
                        var added = 0;
                        addressesOrPuntos.forEach(function(addr, i) {
                            if (!addr || !(addr+\'\').trim()) return;
                            var q = encodeURIComponent((addr+\'\').trim());
                            fetch(\'https://nominatim.openstreetmap.org/search?q=\' + q + \'&format=json&limit=1\', { headers: { \'Accept\': \'application/json\', \'User-Agent\': \'SpartaLedgerRastreo/1.0\' } }).then(function(res) { return res.json(); }).then(function(data) {
                                if (data && data[0] && rastreoMapaLeaflet) {
                                    var lat = parseFloat(data[0].lat), lon = parseFloat(data[0].lon);
                                    L.marker([lat, lon]).addTo(rastreoMapaLeaflet).bindPopup((addr+\'\').substring(0, 80) + (addr.length > 80 ? \'...\' : \'\'));
                                    if (added === 0) {
                                        L.circleMarker([lat, lon], estiloDireccionPrincipal).addTo(rastreoMapaLeaflet).bindPopup(\'<strong>Dirección principal</strong><br>\' + ((addr+\'\').substring(0, 80) + (addr.length > 80 ? \'...\' : \'\')));
                                        rastreoMapaLeaflet.setView([lat, lon], zoomDireccion);
                                    }
                                    added++;
                                }
                            }).catch(function() {});
                        });
                    }
                }
                setTimeout(function() { if (rastreoMapaLeaflet && typeof rastreoMapaLeaflet.invalidateSize === \'function\') rastreoMapaLeaflet.invalidateSize(); }, 150);
            } catch (e) { rastreoMapaLeaflet = null; }
        }
        function initMapaRastreoGrande(addressesOrPuntos, indiceCentrar) {
            var cont = document.getElementById(\'rastreoMapaGrandeContenedor\');
            if (!cont) return;
            if (rastreoMapaGrande) { if (typeof rastreoMapaGrande.remove === \'function\') rastreoMapaGrande.remove(); rastreoMapaGrande = null; }
            if (googleMapsApiKey && googleMapsApiKey.length > 0 && typeof google !== \'undefined\' && google.maps) {
                rastreoMapaGrande = new google.maps.Map(cont, { center: { lat: 19.43, lng: -99.13 }, zoom: 10, mapTypeControl: true, streetViewControl: true, fullscreenControl: true, zoomControl: true });
                var bounds = new google.maps.LatLngBounds();
                var hasPoints = false;
                var markersGrande = [];
                if (addressesOrPuntos && addressesOrPuntos.length) {
                    var esPuntos = addressesOrPuntos[0] && (addressesOrPuntos[0].latitud !== undefined || addressesOrPuntos[0].lat !== undefined);
                    if (esPuntos) {
                        var lat0Grande = null, lon0Grande = null;
                        if (rastreoIndiceCasa !== null && rastreoIndiceCasa >= 0 && rastreoIndiceCasa < addressesOrPuntos.length) {
                            var pcG = addressesOrPuntos[rastreoIndiceCasa];
                            lat0Grande = parseFloat(pcG.latitud || pcG.lat); lon0Grande = parseFloat(pcG.longitud || pcG.lng);
                            if (isNaN(lat0Grande) || isNaN(lon0Grande)) { lat0Grande = null; lon0Grande = null; }
                        }
                        if (lat0Grande === null && rastreoDomicilioMegareporte) {
                            lat0Grande = rastreoDomicilioMegareporte.lat; lon0Grande = rastreoDomicilioMegareporte.lng;
                        }
                        if (lat0Grande === null && addressesOrPuntos.length) {
                            var prG = addressesOrPuntos[0];
                            lat0Grande = parseFloat(prG.latitud || prG.lat); lon0Grande = parseFloat(prG.longitud || prG.lng);
                            if (isNaN(lat0Grande) || isNaN(lon0Grande)) lat0Grande = null;
                        }
                        if (lat0Grande !== null && rastreoDomicilioMegareporte && rastreoIndiceCasa === null) {
                            bounds.extend({ lat: lat0Grande, lng: lon0Grande });
                            var iconCasaG = pinGotaIcon(\'#059669\');
                            new google.maps.Marker({ position: { lat: lat0Grande, lng: lon0Grande }, map: rastreoMapaGrande, icon: { url: iconCasaG.url, scaledSize: new google.maps.Size(34, 48), anchor: new google.maps.Point(17, 48) }, title: \'Domicilio reportado (Megareporte)\', zIndex: 2 });
                        }
                        addressesOrPuntos.forEach(function(p, i) {
                            var lat = parseFloat(p.latitud || p.lat), lon = parseFloat(p.longitud || p.lng);
                            if (isNaN(lat) || isNaN(lon)) return;
                            hasPoints = true;
                            var pos = { lat: lat, lng: lon };
                            bounds.extend(pos);
                            var visitas = p.cantidad_registros || 1;
                            var color = \'#2563eb\';
                            var iconGotaGrande = pinGotaIcon(color);
                            var iconGotaGrandeSize = { url: iconGotaGrande.url, scaledSize: new google.maps.Size(34, 48), anchor: new google.maps.Point(17, 48) };
                            var marker = new google.maps.Marker({ position: pos, map: rastreoMapaGrande, icon: iconGotaGrandeSize, title: (p.punto_de_interes ? \'Punto de interés \' : \'Menos frecuente \') + (i + 1) + \' — \' + visitas + \' visitas\' });
                            var esCasaGrande = (rastreoIndiceCasa !== null && i === rastreoIndiceCasa);
                            var distStrGrande = esCasaGrande ? \'Su casa (domicilio reportado)\' : (lat0Grande === null ? \'—\' : \'Distancia desde su casa: \' + formatDistanciaMapa(haversineMetrosMapa(lat0Grande, lon0Grande, lat, lon)));
                            if (!esCasaGrande && lat0Grande !== null) {
                                var from0G = { lat: lat0Grande, lng: lon0Grande };
                                new google.maps.Polyline({ path: [from0G, pos], map: rastreoMapaGrande, strokeColor: color, strokeWeight: 2, strokeOpacity: 0.85 });
                                var latMidG = (lat0Grande + lat) / 2, lngMidG = (lon0Grande + lon) / 2;
                                var distLabelG = formatDistanciaMapa(haversineMetrosMapa(lat0Grande, lon0Grande, lat, lon));
                                new google.maps.Marker({ position: { lat: latMidG, lng: lngMidG }, map: rastreoMapaGrande, icon: labelDistanciaIcon(distLabelG), clickable: false, zIndex: 1 });
                            }
                            var dirTexto = (p.texto || \'\').trim();
                            var datosHtml = \'<strong>Cliente:</strong> \' + (rastreoDatosClienteActual.nombre || \'—\') + \'<br><strong>Crédito:</strong> \' + (rastreoDatosClienteActual.credito || \'—\') + \'<br><strong>Teléfono:</strong> \' + (rastreoDatosClienteActual.telefono || \'—\') + \'<br><strong>Ubicación:</strong> \' + (dirTexto || \'Obteniendo dirección...\') + \'<br><strong>Visitas:</strong> \' + visitas + \'<br><strong>Tipo:</strong> \' + (p.punto_de_interes ? \'Punto de interés\' : \'Menos frecuente\') + \'<br><strong>Distancia:</strong> \' + distStrGrande;
                            var infow = new google.maps.InfoWindow({ content: datosHtml });
                            if (!dirTexto && typeof google.maps.Geocoder !== \'undefined\') {
                                var geocoder = new google.maps.Geocoder();
                                geocoder.geocode({ location: pos }, function(results, status) {
                                    if (status === \'OK\' && results[0] && infow) {
                                        var addr = (results[0].formatted_address || \'\').replace(/</g, \'&lt;\');
                                        var html = \'<strong>Cliente:</strong> \' + (rastreoDatosClienteActual.nombre || \'—\') + \'<br><strong>Crédito:</strong> \' + (rastreoDatosClienteActual.credito || \'—\') + \'<br><strong>Teléfono:</strong> \' + (rastreoDatosClienteActual.telefono || \'—\') + \'<br><strong>Ubicación:</strong> \' + addr + \'<br><strong>Visitas:</strong> \' + visitas + \'<br><strong>Tipo:</strong> \' + (p.punto_de_interes ? \'Punto de interés\' : \'Menos frecuente\') + \'<br><strong>Distancia:</strong> \' + distStrGrande;
                                        infow.setContent(html);
                                    }
                                });
                            }
                            (function(m, w) { m.addListener(\'click\', function() { w.open(rastreoMapaGrande, m); }); })(marker, infow);
                            markersGrande[i] = { marker: marker, infow: infow };
                        });
                        if (hasPoints) rastreoMapaGrande.fitBounds(bounds, 50);
                        addClusterLabelsToMap(rastreoMapaGrande, addressesOrPuntos);
                        if (indiceCentrar !== undefined && indiceCentrar !== null && markersGrande[indiceCentrar] && rastreoMapaGrande) {
                            (function(idx) {
                                setTimeout(function() {
                                    if (!rastreoMapaGrande || !markersGrande[idx]) return;
                                    var mg = markersGrande[idx];
                                    rastreoMapaGrande.panTo(mg.marker.getPosition());
                                    rastreoMapaGrande.setZoom(16);
                                    if (mg.infow && typeof mg.infow.open === \'function\') { try { mg.infow.close(); } catch (e) {} mg.infow.open(rastreoMapaGrande, mg.marker); }
                                    if (typeof mg.marker.setAnimation === \'function\') { mg.marker.setAnimation(google.maps.Animation.BOUNCE); setTimeout(function() { if (mg.marker.setAnimation) mg.marker.setAnimation(null); }, 2500); }
                                }, 300);
                            })(indiceCentrar);
                        }
                    } else {
                        addressesOrPuntos.forEach(function(addr, i) {
                            if (!addr || !(addr+\'\').trim()) return;
                            var geocoder = new google.maps.Geocoder();
                            geocoder.geocode({ address: (addr+\'\').trim() }, function(results, status) {
                                if (status === \'OK\' && results[0] && rastreoMapaGrande) {
                                    var loc = results[0].geometry.location;
                                    bounds.extend(loc);
                                    var m = new google.maps.Marker({ position: loc, map: rastreoMapaGrande, title: (addr+\'\').substring(0, 80) });
                                    var datosHtml = \'<strong>Cliente:</strong> \' + (rastreoDatosClienteActual.nombre || \'—\') + \'<br><strong>Crédito:</strong> \' + (rastreoDatosClienteActual.credito || \'—\') + \'<br><strong>Teléfono:</strong> \' + (rastreoDatosClienteActual.telefono || \'—\') + \'<br><strong>Dirección:</strong> \' + (addr+\'\').replace(/</g, \'&lt;\').substring(0, 120) + (addr.length > 120 ? \'...\' : \'\');
                                    var w = new google.maps.InfoWindow({ content: datosHtml });
                                    m.addListener(\'click\', function() { w.open(rastreoMapaGrande, m); });
                                    if (!hasPoints) { rastreoMapaGrande.setCenter(loc); rastreoMapaGrande.setZoom(16); }
                                    hasPoints = true;
                                }
                            });
                        });
                    }
                }
                setTimeout(function() { if (rastreoMapaGrande && typeof rastreoMapaGrande.invalidateSize === \'function\') rastreoMapaGrande.invalidateSize(); }, 150);
                return;
            }
            var L = (typeof leaFlet !== \'undefined\' ? leaFlet : (typeof L !== \'undefined\' ? L : null));
            if (!L) return;
            try {
                if (L.Icon && L.Icon.Default) L.Icon.Default.imagePath = \'/assets/vendor/libs/leaflet/images/\';
                rastreoMapaGrande = L.map(\'rastreoMapaGrandeContenedor\', { center: [19.43, -99.13], zoom: 10 });
                var isDarkGrande = document.body && document.body.classList.contains(\'dark-mode\');
                var tileUrlGrande = isDarkGrande ? \'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}.png\' : \'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}.png\';
                L.tileLayer(tileUrlGrande, { attribution: \'&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>\', subdomains: \'abcd\', maxZoom: 20 }).addTo(rastreoMapaGrande);
                var zoomDireccion = 16;
                var estiloDireccionPrincipal = { color: \'#dc2626\', fillColor: \'#ef4444\', fillOpacity: 0.7, weight: 3, radius: 14 };
                if (addressesOrPuntos && addressesOrPuntos.length) {
                    var esPuntos = addressesOrPuntos[0] && (addressesOrPuntos[0].latitud !== undefined || addressesOrPuntos[0].lat !== undefined);
                    if (esPuntos) {
                        var bounds = [];
                        var maxVisitas = 1;
                        addressesOrPuntos.forEach(function(p) { var v = p.cantidad_registros || 1; if (v > maxVisitas) maxVisitas = v; });
                        addressesOrPuntos.forEach(function(p, i) {
                            var lat = parseFloat(p.latitud || p.lat), lon = parseFloat(p.longitud || p.lng);
                            if (isNaN(lat) || isNaN(lon)) return;
                            bounds.push([lat, lon]);
                            var visitas = p.cantidad_registros || 1;
                            var radius = Math.min(10 + (visitas / maxVisitas) * 14, 24);
                            var isFirst = (i === 0);
                            var opts = isFirst ? { color: \'#dc2626\', fillColor: \'#ef4444\', fillOpacity: 0.8, weight: 3, radius: radius } : { color: (p.punto_de_interes ? \'#2563eb\' : \'#6b7280\'), fillColor: (p.punto_de_interes ? \'#3b82f6\' : \'#9ca3af\'), fillOpacity: 0.7, weight: 2, radius: radius };
                            var popup = (p.punto_de_interes ? \'Punto de interés \' : \'Menos frecuente \') + (i + 1) + \' — \' + visitas + \' visitas\';
                            L.circleMarker([lat, lon], opts).addTo(rastreoMapaGrande).bindPopup(popup);
                            if (isFirst) rastreoMapaGrande.setView([lat, lon], zoomDireccion);
                        });
                        if (bounds.length > 1 && L.latLngBounds) rastreoMapaGrande.fitBounds(L.latLngBounds(bounds), { padding: [30, 30], maxZoom: zoomDireccion });
                    } else {
                        var added = 0;
                        addressesOrPuntos.forEach(function(addr, i) {
                            if (!addr || !(addr+\'\').trim()) return;
                            var q = encodeURIComponent((addr+\'\').trim());
                            fetch(\'https://nominatim.openstreetmap.org/search?q=\' + q + \'&format=json&limit=1\', { headers: { \'Accept\': \'application/json\', \'User-Agent\': \'SpartaLedgerRastreo/1.0\' } }).then(function(res) { return res.json(); }).then(function(data) {
                                if (data && data[0] && rastreoMapaGrande) {
                                    var lat = parseFloat(data[0].lat), lon = parseFloat(data[0].lon);
                                    L.marker([lat, lon]).addTo(rastreoMapaGrande).bindPopup((addr+\'\').substring(0, 80) + (addr.length > 80 ? \'...\' : \'\'));
                                    if (added === 0) {
                                        L.circleMarker([lat, lon], estiloDireccionPrincipal).addTo(rastreoMapaGrande).bindPopup(\'<strong>Dirección principal</strong><br>\' + ((addr+\'\').substring(0, 80) + (addr.length > 80 ? \'...\' : \'\')));
                                        rastreoMapaGrande.setView([lat, lon], zoomDireccion);
                                    }
                                    added++;
                                }
                            }).catch(function() {});
                        });
                    }
                }
                setTimeout(function() { if (rastreoMapaGrande && typeof rastreoMapaGrande.invalidateSize === \'function\') rastreoMapaGrande.invalidateSize(); }, 150);
            } catch (e) { rastreoMapaGrande = null; }
        }
        $(function() {
            $(\'#rastreoDireccionesContenido\').on(\'click\', \'.rastreo-direccion-item[data-indice][data-lat][data-lng]\', function() {
                var idx = parseInt($(this).data(\'indice\'), 10);
                var lat = parseFloat($(this).data(\'lat\'));
                var lng = parseFloat($(this).data(\'lng\'));
                if (isNaN(lat) || isNaN(lng)) return;
                rastreoCentrarEnPunto = idx;
                var $item = $(this).closest(\'.rastreo-direccion-item\');
                $(\'.rastreo-direccion-item\').removeClass(\'rastreo-direccion-item-parpadeo\');
                $item.addClass(\'rastreo-direccion-item-parpadeo\');
                setTimeout(function() { $item.removeClass(\'rastreo-direccion-item-parpadeo\'); }, 2400);
                $(\'#modalMapaGrande\').modal(\'show\');
            });
            $(\'#rastreoMapaLeaflet\').on(\'click\', function() { $(\'#modalMapaGrande\').modal(\'show\'); });
            $(\'#modalMapaGrande\').on(\'shown.bs.modal\', function() {
                var indice = rastreoCentrarEnPunto;
                rastreoCentrarEnPunto = null;
                initMapaRastreoGrande(rastreoDireccionesParaMapa, indice);
            });
            $(\'#modalMapaGrande\').on(\'hidden.bs.modal\', function() {
                if (rastreoMapaGrande) { if (typeof rastreoMapaGrande.remove === \'function\') rastreoMapaGrande.remove(); rastreoMapaGrande = null; }
            });
            $(\'#rastreoMapaAlternasWrap\').on(\'click\', function() { rastreoCentrarEnGeoAlternasIndice = null; $(\'#rastreoGeoSeleccionadaCard\').hide(); $(\'#modalMapaAlternasGrande\').modal(\'show\'); });
            $(\'#rastreoDireccionesAlternasContenido\').on(\'click\', \'.rastreo-geo-item[data-indice-geo]\', function(e) {
                e.preventDefault();
                var $item = $(this).closest(\'.rastreo-geo-item[data-indice-geo]\');
                var idx = parseInt($item.data(\'indice-geo\'), 10);
                if (isNaN(idx) || idx < 0) return;
                rastreoCentrarEnGeoAlternasIndice = idx;
                $(\'#rastreoGeoSeleccionadaCard\').hide();
                $(\'.rastreo-geo-item[data-indice-geo]\').removeClass(\'rastreo-geo-item-parpadeo\');
                $item.addClass(\'rastreo-geo-item-parpadeo\');
                setTimeout(function() { $item.removeClass(\'rastreo-geo-item-parpadeo\'); }, 2400);
                $(\'#modalMapaAlternasGrande\').modal(\'show\');
            });
            $(document).on(\'change\', \'.rastreo-filtro-gestor-overlay .rastreo-filtro-gestor-cb\', function(e) {
                var overlay = $(this).closest(\'.rastreo-filtro-gestor-overlay\');
                var todosCb = overlay.find(\'.rastreo-filtro-gestor-cb[data-gestor=""]\');
                var isTodos = $(this).data(\'gestor\') === \'\' || $(this).attr(\'data-gestor\') === \'\';
                if (isTodos && $(this).prop(\'checked\')) {
                    overlay.find(\'.rastreo-filtro-gestor-cb\').not(todosCb).prop(\'checked\', false);
                    filtrarMapaPorGestor(null);
                    return;
                }
                if (!isTodos && $(this).prop(\'checked\')) todosCb.prop(\'checked\', false);
                var selected = [];
                overlay.find(\'.rastreo-filtro-gestor-cb:checked\').each(function() {
                    var g = $(this).data(\'gestor\');
                    if (g !== undefined && g !== \'\') selected.push(g);
                });
                if (selected.length === 0) { todosCb.prop(\'checked\', true); filtrarMapaPorGestor(null); return; }
                filtrarMapaPorGestor(selected);
            });
            $(\'#modalMapaAlternasGrande\').on(\'shown.bs.modal\', function() {
                initMapaRastreoAlternasGrande(rastreoDireccionesParaMapa, rastreoGestionesParaMapa, rastreoPuntosGeo || []);
                var idxGeo = rastreoCentrarEnGeoAlternasIndice;
                rastreoCentrarEnGeoAlternasIndice = null;
                if (idxGeo !== null && idxGeo !== undefined) {
                    setTimeout(function() {
                        if (rastreoMapaAlternasGrande && rastreoMarkersGeoAlternasGrande && rastreoMarkersGeoAlternasGrande[idxGeo]) {
                            var m = rastreoMarkersGeoAlternasGrande[idxGeo];
                            var w = rastreoInfoWindowsGeoAlternasGrande[idxGeo];
                            rastreoMapaAlternasGrande.panTo(m.getPosition());
                            rastreoMapaAlternasGrande.setZoom(16);
                            if (w) { try { w.close(); } catch (e) {} w.open(rastreoMapaAlternasGrande, m); }
                            if (typeof m.setAnimation === \'function\') { m.setAnimation(google.maps.Animation.BOUNCE); setTimeout(function() { if (m.setAnimation) m.setAnimation(null); }, 2500); }
                        }
                    }, 300);
                }
            });
            $(\'#modalMapaAlternasGrande\').on(\'hidden.bs.modal\', function() {
                if (rastreoMapaAlternasGrande) { try { if (typeof rastreoMapaAlternasGrande.remove === \'function\') rastreoMapaAlternasGrande.remove(); } catch (e) {} rastreoMapaAlternasGrande = null; }
                rastreoMarkersGeoAlternasGrande = []; rastreoInfoWindowsGeoAlternasGrande = []; rastreoCentrarEnGeoAlternasIndice = null;
            });
            $(\'#modalRastreoCredito\').on(\'shown.bs.modal\', function() {
                rastreoGestionesParaMapa = []; rastreoGestionesCargadas = false; rastreoFiltroGestorActual = \'\';
                $(\'#rastreoDireccionesContenido\').addClass(\'rastreo-contenido-cargando\').html(\'<div class="rastreo-cargando-bloque"><span class="spinner-border text-primary" role="status" aria-hidden="true"></span><span class="rastreo-cargando-texto">Cargando información</span></div>\');
                $(\'#rastreoDirecciones .rastreo-mapa-wrap\').hide();
                $(\'#rastreoDireccionesAlternasContenido\').addClass(\'rastreo-contenido-cargando\').html(\'<div class="rastreo-cargando-bloque"><span class="spinner-border text-primary" role="status" aria-hidden="true"></span><span class="rastreo-cargando-texto">Cargando información</span></div>\');
                $(\'#rastreoDireccionesAlternas .rastreo-mapa-wrap\').hide();
                $(\'#rastreoBitacoraContenido\').html(\'<p class="text-muted mb-0 d-flex align-items-center gap-2"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>Cargando datos</p>\');
                $(\'#rastreoDictamenContenido\').html(\'<p class="text-muted mb-0 d-flex align-items-center gap-2"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>Cargando datos</p>\');
                cargarChatRastreo(); cargarDictamenRastreo(); cargarEvidenciasRastreo(); cargarGestionesRastreo();
                $(\'#rastreoResumenIAGestionesContenido\').empty().hide();
                $(\'#rastreoAnalizarIAContenido\').empty();
                http.request({ endpoint: \'/sabueso/getUbicacionesCredito\', metodo: \'POST\', data: JSON.stringify({ id_credito: idCreditoRastreoActual }), contentType: \'application/json\', processData: false, onSuccess: function(r) {
                    $(\'#rastreoResumenIAContenido\').empty();
                    rastreoDireccionesParaMapa = (r.puntos_mapa && r.puntos_mapa.length) ? r.puntos_mapa : [];
                    rastreoPuntosGeo = (r.puntos_geo && r.puntos_geo.length) ? r.puntos_geo : [];
                    rastreoDomicilioMegareporte = (r.domicilio_megareporte && r.domicilio_megareporte.lat != null && r.domicilio_megareporte.lng != null) ? r.domicilio_megareporte : null;
                    rastreoIndiceCasa = (r.indice_casa !== undefined && r.indice_casa !== null && Number.isInteger(r.indice_casa)) ? r.indice_casa : null;
                    var htmlGeo = buildGeoListHtml(rastreoPuntosGeo);
                    var contenidoAlternas = htmlGeo || \'<span class="text-muted small">Sin direcciones alternas para este crédito.</span>\';
                    $(\'#rastreoDireccionesAlternasContenido\').removeClass(\'rastreo-contenido-cargando\').html(contenidoAlternas);
                    $(\'#rastreoDireccionesAlternas .rastreo-mapa-wrap\').show();
                    if (r.success && r.direcciones_resumen && r.direcciones_resumen.length) {
                        var dirsRastreo = r.direcciones_resumen;
                        function escR(s) { if (s == null || s === undefined) return \'\'; return (s+\'\').replace(/&/g, \'&amp;\').replace(/</g, \'&lt;\').replace(/>/g, \'&gt;\').replace(/\"/g, \'&quot;\'); }
                        function formatDistanciaRastreo(m) { if (m == null || m === \'\' || isNaN(parseFloat(m))) return \'—\'; var x = parseFloat(m); if (x >= 1000) return (Math.round(x/100)/10) + \' km\'; return Math.round(x) + \' m\'; }
                        function formatUltimaFechaRastreo(f) { if (!f) return \'—\'; if (typeof f === \'string\' && f.match(/^\\d{4}-/)) return new Date(f).toLocaleDateString(\'es-MX\'); return (f+\'\').substring(0, 10); }
                        function buildIntroSpatial(data) {
                            data = data || {};
                            var dirMegareporte = (data.direccion_megareporte || \'\').trim();
                            var p1 = dirMegareporte
                                ? \'<p class="small text-muted mb-2"><strong>Su casa</strong> es la <strong>Dirección megareporte</strong>: \' + escR(dirMegareporte) + \'. Las distancias mostradas son a esa casa. Si la distancia es menor a ~100 m, es posible que el punto sea su casa.</p>\'
                                : \'<p class="small text-muted mb-2">Las distancias mostradas son <strong>a la casa del acreditado</strong> (domicilio o ubicación más visitada). Si la distancia es menor a ~100 m, es posible que el punto sea su casa.</p>\';
                            var ultima = data.ultima_apertura || {};
                            var ts = ultima.timestamp;
                            var tsStr = ts ? new Date(ts).toLocaleString(\'es-MX\') : \'—\';
                            var distCasa = formatDistanciaRastreo(ultima.distancia_a_casa_m);
                            var a5 = data.aperturas_ultimos_5_dias || {};
                            var totalA5 = a5.total_aperturas != null ? a5.total_aperturas : 0;
                            var p2 = \'<p class="small text-muted mb-3">Última apertura de la app: \' + tsStr + \'. Distancia a su casa: \' + distCasa + \'. Total de aperturas (GPS) en los últimos 5 días: \' + totalA5 + \'.</p>\';
                            return p1 + p2;
                        }
                        function buildDireccionesMaxiHtml(distanciasACasa, dataSpatial) {
                            distanciasACasa = distanciasACasa || [];
                            var intro = buildIntroSpatial(dataSpatial);
                            var list = dirsRastreo.map(function(d, i) {
                                var row = distanciasACasa[i] || {};
                                var visitas = d.cantidad_registros != null ? d.cantidad_registros : 0;
                                var tipoLabel = d.punto_de_interes ? \'Punto de interés\' : \'Menos frecuente\';
                                var ultimaFecha = formatUltimaFechaRastreo(row.ultima_fecha != null ? row.ultima_fecha : d.ultima_fecha);
                                var distancia = formatDistanciaRastreo(row.distancia_m);
                                var registroTexto = visitas === 1 ? \'1 registro\' : visitas + \' registros\';
                                var badgeCls = visitas >= 3 ? \'badge bg-primary rastreo-badge-registros\' : \'badge rastreo-badge-registros rastreo-badge-registros-pocos\';
                                var badgeRegistros = \'<span class="\' + badgeCls + \' ms-1">\' + registroTexto + \'</span>\';
                                return \'<div class="rastreo-direccion-item rastreo-direccion-row small" data-indice="\' + (d.orden - 1) + \'" data-lat="\' + d.lat + \'" data-lng="\' + d.lng + \'">\' +
                                    \'<div class="rastreo-col-direccion">\' +
                                    \'<div class="rastreo-direccion-label fw-semibold"> Ubicación \' + d.orden + \':</div>\' +
                                    \'<div class="direccion-linea text-muted">\' + tipoLabel + \' — <span class="direccion-value" data-lat="\' + d.lat + \'" data-lng="\' + d.lng + \'">Obteniendo dirección...</span></div>\' +
                                    \'</div>\' +
                                    \'<div class="rastreo-col-registros text-nowrap">\' + badgeRegistros + \'</div>\' +
                                    \'<div class="rastreo-col-fecha-distancia text-muted small">\' +
                                    \'<div class="rastreo-ultima-fecha">Última fecha: \' + ultimaFecha + \'</div>\' +
                                    \'<div class="rastreo-distancia">Distancia a su casa: \' + distancia + \'</div>\' +
                                    \'</div>\' +
                                    \'</div>\';
                            }).join(\'\');
                            return intro + \'<div class="rastreo-direcciones-lista">\' + list + \'</div>\';
                        }
                        $(\'#rastreoDireccionesContenido\').removeClass(\'rastreo-contenido-cargando\').html(buildDireccionesMaxiHtml([], null));
                        fetch(\'/api/analytics/spatial/\' + idCreditoRastreoActual, { method: \'GET\', headers: { \'Accept\': \'application/json\' } }).then(function(resp) { return resp.json(); }).then(function(apiResp) {
                            var data = (apiResp.data || {});
                            var distancias = data.distancias_a_casa || [];
                            $(\'#rastreoDireccionesContenido\').html(buildDireccionesMaxiHtml(distancias, data));
                            (function doReverseGeocode() {
                                var delay = 800;
                                var nodes = document.querySelectorAll(\'#rastreoDireccionesContenido .direccion-value[data-lat][data-lng]\');
                                function setAddr(elm, text) { if (elm && elm.textContent !== undefined) elm.textContent = text; }
                                function fetchOne(elm, isRetry) {
                                    if (!elm || !elm.getAttribute) return;
                                    var lat = parseFloat(elm.getAttribute(\'data-lat\')), lng = parseFloat(elm.getAttribute(\'data-lng\'));
                                    if (isNaN(lat) || isNaN(lng) || lat < -90 || lat > 90 || lng < -180 || lng > 180) { setAddr(elm, \'Sin coordenadas\'); return; }
                                    var url = \'https://nominatim.openstreetmap.org/reverse?lat=\' + lat + \'&lon=\' + lng + \'&format=json\';
                                    fetch(url, { headers: { \'Accept\': \'application/json\', \'User-Agent\': \'SpartaLedger/1.0 (cobranza)\' } }).then(function(r) { return r.json(); }).then(function(data) { setAddr(elm, (data && data.display_name) ? data.display_name : \'Sin dirección\'); }).catch(function() {
                                        if (!isRetry) { setTimeout(function() { fetchOne(elm, true); }, 2000); } else { setAddr(elm, \'Sin dirección\'); }
                                    });
                                }
                                for (var i = 0; i < nodes.length; i++) {
                                    (function(elm, d) { setTimeout(function() { fetchOne(elm, false); }, d); })(nodes[i], delay);
                                    delay += 1100;
                                }
                            })();
                        }).catch(function() {
                            $(\'#rastreoDireccionesContenido\').html(buildDireccionesMaxiHtml([], null));
                            (function doReverseGeocode() {
                                var delay = 800;
                                var nodes = document.querySelectorAll(\'#rastreoDireccionesContenido .direccion-value[data-lat][data-lng]\');
                                function setAddr(elm, text) { if (elm && elm.textContent !== undefined) elm.textContent = text; }
                                function fetchOne(elm, isRetry) {
                                    if (!elm || !elm.getAttribute) return;
                                    var lat = parseFloat(elm.getAttribute(\'data-lat\')), lng = parseFloat(elm.getAttribute(\'data-lng\'));
                                    if (isNaN(lat) || isNaN(lng) || lat < -90 || lat > 90 || lng < -180 || lng > 180) { setAddr(elm, \'Sin coordenadas\'); return; }
                                    var url = \'https://nominatim.openstreetmap.org/reverse?lat=\' + lat + \'&lon=\' + lng + \'&format=json\';
                                    fetch(url, { headers: { \'Accept\': \'application/json\', \'User-Agent\': \'SpartaLedger/1.0 (cobranza)\' } }).then(function(r) { return r.json(); }).then(function(data) { setAddr(elm, (data && data.display_name) ? data.display_name : \'Sin dirección\'); }).catch(function() {
                                        if (!isRetry) { setTimeout(function() { fetchOne(elm, true); }, 2000); } else { setAddr(elm, \'Sin dirección\'); }
                                    });
                                }
                                for (var i = 0; i < nodes.length; i++) {
                                    (function(elm, d) { setTimeout(function() { fetchOne(elm, false); }, d); })(nodes[i], delay);
                                    delay += 1100;
                                }
                            })();
                        });
                    } else {
                        $(\'#rastreoDireccionesContenido\').removeClass(\'rastreo-contenido-cargando\').html(\'<span class="text-muted">Sin ubicaciones en maxi app para este crédito.</span>\');
                    }
                    $(\'#rastreoDirecciones .rastreo-mapa-wrap\').show();
                    try { initMapaRastreo(rastreoDireccionesParaMapa); maybeInitMapaAlternas(); } catch (e) { console.warn(\'Rastreo mapas:\', e); }
                }, onError: function() {
                    rastreoDomicilioMegareporte = null; rastreoIndiceCasa = null;
                    rastreoPuntosGeo = [];
                    $(\'#rastreoDireccionesContenido\').removeClass(\'rastreo-contenido-cargando\').html(\'<span class="text-muted">Sin ubicaciones en maxi app para este crédito.</span>\');
                    $(\'#rastreoDireccionesAlternasContenido\').removeClass(\'rastreo-contenido-cargando\').html(\'<span class="text-muted small">No se pudieron cargar las direcciones alternas. Revisa la conexión o intenta de nuevo.</span>\');
                    $(\'#rastreoDirecciones .rastreo-mapa-wrap\').show();
                    $(\'#rastreoDireccionesAlternas .rastreo-mapa-wrap\').show();
                    try { initMapaRastreo([]); maybeInitMapaAlternas(); } catch (e) { console.warn(\'Rastreo mapas:\', e); }
                } });
            });
            $(\'#modalRastreoCredito\').on(\'hidden.bs.modal\', function() {
                rastreoMarkersMaxiApp = [];
                if (rastreoMapaLeaflet) { if (typeof rastreoMapaLeaflet.remove === \'function\') rastreoMapaLeaflet.remove(); rastreoMapaLeaflet = null; }
                if (rastreoMapaAlternas) { try { if (typeof rastreoMapaAlternas.remove === \'function\') rastreoMapaAlternas.remove(); } catch (e) {} rastreoMapaAlternas = null; }
                if (rastreoMapaAlternasGrande) { try { if (typeof rastreoMapaAlternasGrande.remove === \'function\') rastreoMapaAlternasGrande.remove(); } catch (e) {} rastreoMapaAlternasGrande = null; }
                rastreoGestionesParaMapa = []; rastreoGestionesCargadas = false;
                rastreoPuntosGeo = []; rastreoMarkersGeoAlternasGrande = []; rastreoInfoWindowsGeoAlternasGrande = []; rastreoCentrarEnGeoAlternasIndice = null;
            });
            $(\'#inputEvidenciaRastreo\').on(\'change\', function() {
                var f = this.files && this.files[0];
                if (!f || !ticketIdRastreoActual) return;
                if (evidenciaPreviewObjectUrl) URL.revokeObjectURL(evidenciaPreviewObjectUrl);
                evidenciaPreviewObjectUrl = URL.createObjectURL(f);
                var maxCom = 300;
                $(\'#modalEvidenciaRastreoBody\').html(\'<div class="mb-2"><img src="\' + evidenciaPreviewObjectUrl + \'" class="img-fluid rounded" alt="Vista previa" style="max-height: 280px;"></div><button type="button" class="btn btn-sm btn-primary mt-2" id="btnEvidenciaGuardarModal"><i class="fa-solid fa-save me-1"></i>Guardar evidencia</button>\');
                $(\'#modalEvidenciaEliminar\').hide(); $(\'#modalEvidenciaRastreo\').modal(\'show\');
                $(\'#btnEvidenciaGuardarModal\').off(\'click\').on(\'click\', function() {
                    var fd = new FormData();
                    fd.append(\'id_ticket\', ticketIdRastreoActual); fd.append(\'evidencia\', f);
                    $.ajax({ url: \'/sabueso/subirEvidenciaTicket\', type: \'POST\', data: fd, processData: false, contentType: false, success: function(r) {
                        if (r.success) {
                            if (evidenciaPreviewObjectUrl) { URL.revokeObjectURL(evidenciaPreviewObjectUrl); evidenciaPreviewObjectUrl = null; }
                            $(\'#modalEvidenciaRastreo\').modal(\'hide\'); cargarEvidenciasRastreo();
                        } else { Swal.fire({ icon: \'error\', title: \'Error\', text: r.mensaje || \'No se pudo subir.\' }); }
                    }, error: function() { Swal.fire({ icon: \'error\', title: \'Error\', text: \'No se pudo subir la imagen.\' }); } });
                    $(\'#inputEvidenciaRastreo\').val(\'\');
                });
            });
            $(\'#modalEvidenciaRastreo\').on(\'shown.bs.modal\', function() {
                document.body.classList.add(\'evidencia-modal-open\');
                $(\'#modalEvidenciaRastreo\').css(\'z-index\', 1105);
                $(\'.modal-backdrop\').last().css({\'z-index\': 1100, \'background-color\': \'rgba(0,0,0,0.5)\'});
            });
            $(\'#modalEvidenciaRastreo\').on(\'hidden.bs.modal\', function() {
                document.body.classList.remove(\'evidencia-modal-open\');
                if (evidenciaPreviewObjectUrl) { URL.revokeObjectURL(evidenciaPreviewObjectUrl); evidenciaPreviewObjectUrl = null; }
                $(\'#inputEvidenciaRastreo\').val(\'\');
            });
            $(\'#modalEvidenciaEliminar\').on(\'click\', function() {
                if (!evidenciaModalId) return;
                http.request({ endpoint: "/sabueso/eliminarEvidenciaTicket", metodo: "POST", data: JSON.stringify({ id_evidencia: evidenciaModalId }), contentType: "application/json", processData: false, onSuccess: function(r) {
                    $(\'#modalEvidenciaRastreo\').modal(\'hide\'); cargarEvidenciasRastreo();
                    if (r.mensaje) Swal.fire({ icon: \'success\', title: \'Eliminada\', text: r.mensaje });
                }, onError: function(e) { Swal.fire({ icon: \'error\', title: \'Error\', text: (e && e.mensaje) || \'No se pudo eliminar.\' }); } });
            });
            $(\'#rastreoBitacoraEnviar\').on(\'click\', function() {
                var txt = ($(\'#rastreoBitacoraInput\').val() || \'\').trim();
                if (!txt || !ticketIdRastreoActual) return;
                http.request({ endpoint: "/sabueso/addChatTicket", metodo: "POST", data: JSON.stringify({ id_ticket: ticketIdRastreoActual, mensaje: txt }), contentType: "application/json", processData: false, onSuccess: function(r) {
                    $(\'#rastreoBitacoraInput\').val(\'\'); cargarChatRastreo();
                }, onError: function(e) { Swal.fire({ icon: \'error\', title: \'Error\', text: (e && e.mensaje) || \'No se pudo enviar.\' }); } });
            });
            $(\'#rastreoDictamenEnviar\').on(\'click\', function() {
                var txt = ($(\'#rastreoDictamenInput\').val() || \'\').trim();
                if (!txt || !ticketIdRastreoActual) return;
                http.request({ endpoint: "/sabueso/addDictamenTicket", metodo: "POST", data: JSON.stringify({ id_ticket: ticketIdRastreoActual, mensaje: txt }), contentType: "application/json", processData: false, onSuccess: function(r) {
                    $(\'#rastreoDictamenInput\').val(\'\'); cargarDictamenRastreo();
                }, onError: function(e) { Swal.fire({ icon: \'error\', title: \'Error\', text: (e && e.mensaje) || \'No se pudo enviar.\' }); } });
            });
        });
';
        $script .= '
            $(\'#btnAnalizarRastreo\').on(\'click\', function() {
                var btn = $(\'#btnAnalizarRastreo\'); var txt = $(\'#btnAnalizarRastreoText\');
                if (btn.prop(\'disabled\')) return;
                if (!idCreditoRastreoActual) { Swal.fire({ icon: \'warning\', title: \'Analizar\', text: \'No hay crédito seleccionado.\' }); return; }
                txt.text(\'Analizando...\'); btn.prop(\'disabled\', true);
                $(\'#rastreoAnalizarIAContenido\').html(\'<p class="text-muted mb-0"><span class="spinner-border spinner-border-sm me-2"></span>Ejecutando análisis con IA...</p>\').show();
                $(\'#modalPrediccionIABody\').html(\'<p class="text-muted mb-0"><span class="spinner-border spinner-border-sm me-2"></span>Calculando riesgo de impago y predicción del gestor...</p>\');
                $(\'#modalPrediccionIA\').addClass(\'modal-analitica-ia\');
                http.request({ endpoint: \'/sabueso/analizarIA\', metodo: \'POST\', data: JSON.stringify({ id_credito: idCreditoRastreoActual, id_ticket: ticketIdRastreoActual || 0 }), contentType: \'application/json\', processData: false, timeout: 90000, onSuccess: function(r) {
                    if (!r.success) {
                        $(\'#modalPrediccionIABody\').html(\'<p class="text-danger mb-0">\' + ((r.mensaje || \'Error\') + \'\').replace(/</g, \'&lt;\') + \'</p>\');
                        $(\'#modalPrediccionIALabel\').html(\'<i class="fa-solid fa-wand-magic-sparkles me-2"></i>Análisis con IA\');
                        $(\'#modalPrediccionIA\').modal(\'show\');
                        txt.text(\'Analizar\'); btn.prop(\'disabled\', false); return;
                    }
                    var j = r.json || {};
                    var esc = function(s){ if (s == null || s === undefined) return \'\'; return (s+\'\').replace(/&/g,\'&amp;\').replace(/</g,\'&lt;\').replace(/>/g,\'&gt;\').replace(/"/g,\'&quot;\'); };
                    var confPct = (j.confianza_analisis != null || j.confidence != null) ? Math.round((j.confianza_analisis != null ? j.confianza_analisis : j.confidence) * 100) : null;
                    var html = \'<div class="analitica-ia-container">\';
                    html += \'<p class="mb-2">\' + (confPct != null ? \'<span class="badge bg-label-info me-1">Confianza del análisis: \' + confPct + \'%</span>\' : \'\') + (j.suspected_test ? \' <span class="badge bg-warning text-dark">Posible prueba</span>\' : \'\') + \'</p>\';
                    if (j.resumen_ejecutivo || j.summary) html += \'<p class="fw-semibold mb-3">\' + esc(j.resumen_ejecutivo || j.summary) + \'</p>\';
                    html += \'<div class="analitica-ia-card mb-3"><h3 class="h6 border-bottom pb-2 mb-2">Riesgo / predicción de impago</h3>\';
                    var pc = j.prediccion_conductual || {};
                    var eventoLabel = (pc.evento_probable || \'\');
                    if (eventoLabel === \'pago_en_caja\') eventoLabel = \'Pago reciente\';
                    var confianzaPct = pc.confianza_evento != null ? (pc.confianza_evento <= 1 ? Math.round(pc.confianza_evento * 100) : Math.round(pc.confianza_evento)) : null;
                    if (pc.evento_probable) html += \'<p><strong>Evento probable:</strong> \' + (eventoLabel ? esc(eventoLabel) : esc(pc.evento_probable)) + (confianzaPct != null ? \' (confianza \' + confianzaPct + \'%)\' : \'\') + \'</p>\';
                    if (pc.explicacion_deterministica) html += \'<p class="small text-muted">\' + esc((pc.explicacion_deterministica || \'\').replace(/pago_en_caja/g, \'Pago reciente\')) + \'</p>\';
                    if (!pc.evento_probable && !pc.explicacion_deterministica) html += \'<p class="small text-muted">Sin predicción específica de evento. Revisar historial de pagos y gestiones.</p>\';
                    var riesgosImpago = (j.riesgos || []).filter(function(x){ var t = (x+\'\').toLowerCase(); return /pago|impago|saldo|mora|recuperación|deuda/.test(t); });
                    if (riesgosImpago.length) { html += \'<p class="small mb-1"><strong>Riesgos detectados:</strong></p><ul class="small">\'; riesgosImpago.forEach(function(x){ html += \'<li>\' + esc(x) + \'</li>\'; }); html += \'</ul>\'; }
                    var ap = j.analitica_pagos || {};
                    if (ap.total_pagos != null || ap.patron_pago) html += \'<p class="small mb-0">Historial de pagos: \' + (ap.total_pagos != null ? ap.total_pagos + \' pagos\' : \'\') + (ap.patron_pago ? \', patrón \' + esc(ap.patron_pago) : \'\') + \'</p>\';
                    else if (Object.keys(ap).length > 0 || (j.analitica_pagos && typeof j.analitica_pagos === \'object\')) html += \'<p class="small mb-0 text-muted">Historial de pagos: no disponible.</p>\';
                    html += \'</div>\';
                    html += \'<div class="analitica-ia-card mb-3"><h3 class="h6 border-bottom pb-2 mb-2">Riesgo y predicción con el gestor</h3>\';
                    var cg = j.cumplimiento_gestor || {};
                    if (cg.porcentaje_cumplimiento != null) { html += \'<p><strong>Cumplimiento de gestiones:</strong> \' + esc(cg.porcentaje_cumplimiento) + \'%\'; if (cg.visitas_cercanas != null || cg.visitas_lejanas != null) html += \' (\' + (cg.visitas_cercanas || 0) + \' visitas dentro de rango, \' + (cg.visitas_lejanas || 0) + \' fuera)\'; html += \'</p>\'; }
                    else if (Object.keys(cg).length > 0 || (j.cumplimiento_gestor && typeof j.cumplimiento_gestor === \'object\')) html += \'<p class="small mb-0 text-muted">Cumplimiento de gestiones: no disponible.</p>\';
                    if (cg.alertas && cg.alertas.length) { html += \'<ul class="small">\'; cg.alertas.forEach(function(a){ html += \'<li class="text-warning">\' + esc(a) + \'</li>\'; }); html += \'</ul>\'; }
                    var riesgosGestor = (j.riesgos || []).filter(function(x){ var t = (x+\'\').toLowerCase(); return /gestor|cumplimiento|cobranza|canal|auditoría|eficacia/.test(t); });
                    if (riesgosGestor.length) { html += \'<p class="small mb-1"><strong>Riesgos relacionados al gestor:</strong></p><ul class="small">\'; riesgosGestor.forEach(function(x){ html += \'<li>\' + esc(x) + \'</li>\'; }); html += \'</ul>\'; }
                    if (j.next_steps && j.next_steps.length) { html += \'<p class="small mb-1"><strong>Próximos pasos:</strong></p><ul class="small">\'; j.next_steps.forEach(function(s){ html += \'<li>\' + esc(s) + \'</li>\'; }); html += \'</ul>\'; }
                    html += \'</div>\';
                    var otrosRiesgos = (j.riesgos || []).filter(function(x){ var t = (x+\'\').toLowerCase(); return !/pago|impago|saldo|mora|recuperación|deuda|gestor|cumplimiento|cobranza|canal|auditoría|eficacia/.test(t); });
                    if (otrosRiesgos.length) { html += \'<div class="analitica-ia-card mb-2"><p class="small mb-1"><strong>Otros riesgos:</strong></p><ul class="small">\'; otrosRiesgos.forEach(function(x){ html += \'<li>\' + esc(x) + \'</li>\'; }); html += \'</ul></div>\'; }
                    html += \'<p class="small text-muted mt-2">Análisis generado con IA (pipeline de predicción).</p></div>\';
                    rastreoUltimoAnalizarIA = html;
                    try { if (typeof localStorage !== \'undefined\') { localStorage.setItem(\'sabueso_ia_\' + idCreditoRastreoActual + \'_\' + (ticketIdRastreoActual || 0) + \'_analizar\', html); } } catch (e) {}
                    $(\'#btnLecturaIAAnalizar, #btnBorrarIAAnalizar\').show();
                    $(\'#rastreoAnalizarIAContenido\').empty();
                    $(\'#modalPrediccionIALabel\').html(\'<i class="fa-solid fa-wand-magic-sparkles me-2"></i>Análisis con IA – Riesgo de impago y gestor\');
                    $(\'#modalPrediccionIABody\').html(html);
                    $(\'#modalPrediccionIA\').modal(\'show\');
                    txt.text(\'Analizar\'); btn.prop(\'disabled\', false);
                }, onError: function(e) {
                    var msg = (typeof e === \'string\' ? e : (e && e.mensaje)) || \'No se pudo obtener el análisis con IA.\';
                    $(\'#rastreoAnalizarIAContenido\').empty();
                    $(\'#modalPrediccionIABody\').html(\'<p class="text-danger mb-0">\' + String(msg).replace(/</g, \'&lt;\') + \'</p>\');
                    $(\'#modalPrediccionIALabel\').html(\'<i class="fa-solid fa-wand-magic-sparkles me-2"></i>Análisis con IA\');
                    $(\'#modalPrediccionIA\').modal(\'show\');
                    txt.text(\'Analizar\'); btn.prop(\'disabled\', false);
                } });
            });
            $(\'#btnLecturaIAAnalizar\').on(\'click\', function() {
                if (rastreoUltimoAnalizarIA) {
                    $(\'#modalPrediccionIALabel\').html(\'<i class="fa-solid fa-wand-magic-sparkles me-2"></i>Análisis con IA – Riesgo de impago y gestor\');
                    $(\'#modalPrediccionIABody\').html(rastreoUltimoAnalizarIA);
                    $(\'#modalPrediccionIA\').modal(\'show\');
                } else { Swal.fire({ icon: \'info\', title: \'Lectura\', text: \'Primero ejecute Analizar.\' }); } });
            $(\'#btnResumirUbicacionesIA\').on(\'click\', function() {
                var btn = $(\'#btnResumirUbicacionesIA\');
                if (btn.prop(\'disabled\') || !idCreditoRastreoActual) return;
                btn.prop(\'disabled\', true);
                var cont = $(\'#rastreoResumenIAContenido\');
                cont.html(\'<span class="spinner-border spinner-border-sm me-2"></span>Resumiendo ubicaciones con IA...\').removeClass(\'text-danger\').addClass(\'text-muted\').show();
                http.request({ endpoint: \'/sabueso/resumirUbicacionesIA\', metodo: \'POST\', data: JSON.stringify({ id_credito: idCreditoRastreoActual }), contentType: \'application/json\', processData: false, showLoader: false, timeout: 90000, onSuccess: function(r) {
                    try {
                        if (r && r.success && (r.texto || r.json)) {
                            var txt = \'\';
                            if (r.json && !r.json.error) {
                                var j = r.json;
                                var confPct = (j.overall_confidence != null) ? Math.round(j.overall_confidence*100) : null;
                                txt = (confPct != null ? \'<p class="small mb-2"><span class="badge bg-label-info">Confianza global: \' + confPct + \'%</span>\' + (j.suspected_test ? \' <span class="badge bg-warning text-dark">Posible prueba</span>\' : \'\') + \'</p>\' : \'\');
                                txt += \'<p class="fw-semibold">\' + (j.resumen || j.one_line_summary || \'\').replace(/</g, \'&lt;\').replace(/>/g, \'&gt;\') + \'</p>\';
                                if (j.predictions && j.predictions.length) { txt += \'<p class="small text-muted mt-2 mb-1">Ranking (prob. suman 100%):</p><ul class="list-unstyled">\'; j.predictions.forEach(function(p) { var lugar = p.lugar || p.place_type || \'-\'; var probP = p.probabilidad != null ? p.probabilidad : p.probability != null ? p.probability : p.confidence; var motivo = p.motivo || p.reason || \'\'; var horario = p.horario_probable || \'—\'; var ev = (p.evidencias && p.evidencias.length) ? \' [\' + (p.evidencias.join ? p.evidencias.join(\', \') : p.evidencias) + \']\' : ((p.evidence && p.evidence.length) ? \' [\' + (p.evidence.join ? p.evidence.join(\', \') : p.evidence) + \']\' : \'\'); var acts = (p.actions && p.actions.length) ? p.actions : []; var actsStr = \'\'; if (acts.length) { acts.forEach(function(a) { var ax = typeof a === \'object\' ? (a.action || \'\') + (a.impact_reduction != null ? \' (\' + Math.round(a.impact_reduction*100) + \'%)\' : \'\') : a; actsStr += (actsStr ? \'; \' : \'\') + ax; }); actsStr = \' <span class="text-success small">\' + actsStr + \'</span>\'; } txt += \'<li class="mb-2"><span class="badge bg-label-primary me-1">\' + lugar + \'</span> #\' + (p.prioridad || \'\') + \' \' + (probP != null ? \'<strong>\' + Math.round(probP*100) + \'%</strong> \' : \'\') + motivo.replace(/</g, \'&lt;\') + \' <span class="text-muted">Horario: \' + horario + \'</span>\' + ev + actsStr + \'</li>\'; }); txt += \'</ul>\'; }
                                if (j.next_steps && j.next_steps.length) { txt += \'<p class="small text-muted mt-2 mb-1">Próximos pasos:</p><ul>\'; j.next_steps.forEach(function(s) { txt += \'<li>\' + String(s).replace(/</g, \'&lt;\') + \'</li>\'; }); txt += \'</ul>\'; }
                                if ((j.missing && j.missing.length) || (j.missing_data_global && j.missing_data_global.length)) { var miss = j.missing_data_global && j.missing_data_global.length ? j.missing_data_global : j.missing; txt += \'<p class="small text-warning mt-2">Datos faltantes: \' + (miss.join ? miss.join(\', \') : miss).replace(/</g, \'&lt;\') + \'</p>\'; }
                            } else { txt = formatGeminiText(r.texto || \'\'); }
                            rastreoUltimoResumenUbicaciones = r.texto || (r.json ? JSON.stringify(r.json, null, 2) : \'\');
                            try { if (typeof localStorage !== \'undefined\') localStorage.setItem(\'sabueso_ia_\' + idCreditoRastreoActual + \'_ubicaciones\', rastreoUltimoResumenUbicaciones); } catch (e) {}
                            cont.html(\'<span class="text-success"><i class="fa-solid fa-check me-1"></i>Listo. Use el botón «Lectura de IA» para ver el análisis.</span>\').removeClass(\'text-danger text-muted\').addClass(\'text-body\');
                            $(\'#btnLecturaIAUbicaciones, #btnBorrarIAUbicaciones\').show();
                            $(\'#modalLecturaIALabel\').html(\'<i class="fa-solid fa-book-open me-2"></i>Lectura de IA – Ubicaciones\');
                            $(\'#modalLecturaIABody\').html(txt);
                            $(\'#modalLecturaIA\').modal(\'show\');
                        } else {
                            cont.html(\'<span class="text-danger">\' + ((r && r.mensaje) ? String(r.mensaje).replace(/</g, \'&lt;\') : \'No se obtuvo resumen. Intente de nuevo.\') + \'</span>\').addClass(\'text-danger\');
                        }
                    } catch (e) { cont.html(\'<span class="text-danger">Error al mostrar el resumen.</span>\').addClass(\'text-danger\'); }
                    btn.prop(\'disabled\', false);
                }, onError: function(e) {
                    var msg = (typeof e === \'string\' ? e : (e && e.mensaje != null ? e.mensaje : \'No se pudo conectar con el servicio de IA.\'));
                    cont.html(\'<span class="text-danger">\' + String(msg).replace(/</g, \'&lt;\') + \'</span>\').addClass(\'text-danger\');
                    btn.prop(\'disabled\', false);
                    Swal.fire({ icon: \'error\', title: \'Resumir ubicaciones\', text: typeof msg === \'string\' ? msg : \'Error al resumir.\' });
                } });
            });
            $(\'#btnLecturaIAUbicaciones\').on(\'click\', function() {
                if (rastreoUltimoResumenUbicaciones) {
                    $(\'#modalLecturaIALabel\').html(\'<i class="fa-solid fa-book-open me-2"></i>Lectura de IA – Ubicaciones\');
                    var bodyHtml = \'\';
                    try {
                        var j = JSON.parse(rastreoUltimoResumenUbicaciones);
                        if (j && typeof j === \'object\' && (j.resumen !== undefined || j.one_line_summary !== undefined || (j.predictions && j.predictions.length))) {
                            var confPct = (j.overall_confidence != null) ? Math.round(j.overall_confidence*100) : null;
                            bodyHtml = (confPct != null ? \'<p class="small mb-2"><span class="badge bg-label-info">Confianza global: \' + confPct + \'%</span>\' + (j.suspected_test ? \' <span class="badge bg-warning text-dark">Posible prueba</span>\' : \'\') + \'</p>\' : \'\');
                            bodyHtml += \'<p class="fw-semibold">\' + (j.resumen || j.one_line_summary || \'\').replace(/</g, \'&lt;\').replace(/>/g, \'&gt;\') + \'</p>\';
                            if (j.predictions && j.predictions.length) { bodyHtml += \'<p class="small text-muted mt-2 mb-1">Ranking:</p><ul class="list-unstyled">\'; j.predictions.forEach(function(p) { var lugar = p.lugar || p.place_type || \'-\'; var probP = p.probabilidad != null ? p.probabilidad : p.probability != null ? p.probability : p.confidence; var motivo = p.motivo || p.reason || \'\'; var horario = p.horario_probable || \'—\'; var ev = (p.evidencias && p.evidencias.length) ? \' [\' + (p.evidencias.join ? p.evidencias.join(\', \') : p.evidencias) + \']\' : ((p.evidence && p.evidence.length) ? \' [\' + (p.evidence.join ? p.evidence.join(\', \') : p.evidence) + \']\' : \'\'); var acts = (p.actions && p.actions.length) ? p.actions : []; var actsStr = \'\'; if (acts.length) { acts.forEach(function(a) { var ax = typeof a === \'object\' ? (a.action || \'\') + (a.impact_reduction != null ? \' (\' + Math.round(a.impact_reduction*100) + \'%)\' : \'\') : a; actsStr += (actsStr ? \'; \' : \'\') + ax; }); actsStr = \' <span class="text-success small">\' + actsStr + \'</span>\'; } bodyHtml += \'<li class="mb-2"><span class="badge bg-label-primary me-1">\' + lugar + \'</span> \' + (probP != null ? \'<strong>\' + Math.round(probP*100) + \'%</strong> \' : \'\') + motivo.replace(/</g, \'&lt;\') + \' <span class="text-muted">(\' + horario + \')</span>\' + ev + actsStr + \'</li>\'; }); bodyHtml += \'</ul>\'; }
                            if (j.next_steps && j.next_steps.length) { bodyHtml += \'<p class="small text-muted mt-2 mb-1">Próximos pasos:</p><ul>\'; j.next_steps.forEach(function(s) { bodyHtml += \'<li>\' + String(s).replace(/</g, \'&lt;\') + \'</li>\'; }); bodyHtml += \'</ul>\'; }
                            if ((j.missing && j.missing.length) || (j.missing_data_global && j.missing_data_global.length)) { var miss = j.missing_data_global && j.missing_data_global.length ? j.missing_data_global : j.missing; bodyHtml += \'<p class="small text-warning mt-2">Datos faltantes: \' + (miss.join ? miss.join(\', \') : miss).replace(/</g, \'&lt;\') + \'</p>\'; }
                        } else { bodyHtml = formatGeminiText(rastreoUltimoResumenUbicaciones); }
                    } catch (e) { bodyHtml = formatGeminiText(rastreoUltimoResumenUbicaciones); }
                    $(\'#modalLecturaIABody\').html(bodyHtml);
                    $(\'#modalLecturaIA\').modal(\'show\');
                } else { Swal.fire({ icon: \'info\', title: \'Lectura de IA\', text: \'Primero ejecute Resumir ubicaciones con IA.\' }); }
            });
            $(\'#btnResumenIAGestiones\').on(\'click\', function() {
                var btn = $(\'#btnResumenIAGestiones\');
                if (btn.prop(\'disabled\') || !idCreditoRastreoActual) return;
                btn.prop(\'disabled\', true);
                var cont = $(\'#rastreoResumenIAGestionesContenido\');
                cont.html(\'<span class="spinner-border spinner-border-sm me-2"></span>Generando resumen con IA...\').show().removeClass(\'text-danger\').addClass(\'text-muted\');
                http.request({ endpoint: \'/sabueso/resumirGestionesIA\', metodo: \'POST\', data: JSON.stringify({ id_credito: idCreditoRastreoActual }), contentType: \'application/json\', processData: false, showLoader: false, onSuccess: function(r) {
                    try {
                        if (r && r.success && r.texto && (r.texto + \'\').trim()) {
                            var txt = formatGeminiText(r.texto);
                            rastreoUltimoResumenGestiones = r.texto;
                            try { if (typeof localStorage !== \'undefined\') localStorage.setItem(\'sabueso_ia_\' + idCreditoRastreoActual + \'_gestiones\', r.texto); } catch (e) {}
                            cont.html(\'<span class="text-success"><i class="fa-solid fa-check me-1"></i>Listo. Use el botón «Lectura de IA» para ver el análisis.</span>\').removeClass(\'text-danger text-muted\').addClass(\'text-body\');
                            $(\'#btnLecturaIAGestiones, #btnBorrarIAGestiones\').show();
                            $(\'#modalLecturaIALabel\').html(\'<i class="fa-solid fa-book-open me-2"></i>Lectura de IA – Gestiones\');
                            $(\'#modalLecturaIABody\').html(txt);
                            $(\'#modalLecturaIA\').modal(\'show\');
                        } else {
                            cont.html(\'<span class="text-danger">\' + ((r && r.mensaje) ? String(r.mensaje).replace(/</g, \'&lt;\') : \'No se obtuvo resumen. Intente de nuevo.\') + \'</span>\').addClass(\'text-danger\');
                        }
                    } catch (e) { cont.html(\'<span class="text-danger">Error al mostrar el resumen.</span>\').addClass(\'text-danger\'); }
                    btn.prop(\'disabled\', false);
                }, onError: function(e) {
                    var msg = (typeof e === \'string\' ? e : (e && e.mensaje != null ? e.mensaje : \'No se pudo conectar con el servicio de IA.\'));
                    cont.html(\'<span class="text-danger">\' + String(msg).replace(/</g, \'&lt;\') + \'</span>\').addClass(\'text-danger\');
                    btn.prop(\'disabled\', false);
                    Swal.fire({ icon: \'error\', title: \'Resumen con IA\', text: typeof msg === \'string\' ? msg : \'Error al resumir.\' });
                } });
            });
            $(\'#btnLecturaIAGestiones\').on(\'click\', function() {
                if (rastreoUltimoResumenGestiones) {
                    $(\'#modalLecturaIALabel\').html(\'<i class="fa-solid fa-book-open me-2"></i>Lectura de IA – Gestiones\');
                    $(\'#modalLecturaIABody\').html(formatGeminiText(rastreoUltimoResumenGestiones));
                    $(\'#modalLecturaIA\').modal(\'show\');
                } else { Swal.fire({ icon: \'info\', title: \'Lectura de IA\', text: \'Primero ejecute Resumen con IA (gestiones).\' }); }
            });
            $(\'#btnBorrarIAAnalizar\').on(\'click\', function() {
                if (!idCreditoRastreoActual) return;
                try {
                    if (typeof localStorage !== \'undefined\') {
                        localStorage.removeItem(\'sabueso_ia_\' + idCreditoRastreoActual + \'_\' + (ticketIdRastreoActual || 0) + \'_analizar\');
                    }
                } catch (e) {}
                rastreoUltimoAnalizarIA = \'\';
                $(\'#btnLecturaIAAnalizar, #btnBorrarIAAnalizar\').hide();
                Swal.fire({ icon: \'success\', title: \'Borrado\', text: \'Lectura de IA (Analizar) eliminada. Puede generar una nueva cuando quiera.\' });
            });
            $(\'#btnBorrarIAUbicaciones\').on(\'click\', function() {
                if (!idCreditoRastreoActual) return;
                try { if (typeof localStorage !== \'undefined\') localStorage.removeItem(\'sabueso_ia_\' + idCreditoRastreoActual + \'_ubicaciones\'); } catch (e) {}
                rastreoUltimoResumenUbicaciones = \'\';
                $(\'#btnLecturaIAUbicaciones, #btnBorrarIAUbicaciones\').hide();
                $(\'#rastreoResumenIAContenido\').html(\'\').hide();
                Swal.fire({ icon: \'success\', title: \'Borrado\', text: \'Lectura de IA (Ubicaciones) eliminada. Puede generar una nueva cuando quiera.\' });
            });
            $(\'#btnBorrarIAGestiones\').on(\'click\', function() {
                if (!idCreditoRastreoActual) return;
                try { if (typeof localStorage !== \'undefined\') localStorage.removeItem(\'sabueso_ia_\' + idCreditoRastreoActual + \'_gestiones\'); } catch (e) {}
                rastreoUltimoResumenGestiones = \'\';
                $(\'#btnLecturaIAGestiones, #btnBorrarIAGestiones\').hide();
                $(\'#rastreoResumenIAGestionesContenido\').html(\'\').hide();
                Swal.fire({ icon: \'success\', title: \'Borrado\', text: \'Lectura de IA (Gestiones) eliminada. Puede generar una nueva cuando quiera.\' });
            });
';
        $script .= "\n        </script>";
        $end = "\n        </script>";
        $pos = strrpos($script, $end);
        if ($pos !== false) {
            $script = substr($script, 0, $pos) . "\n\n        " . $evidenciasScript . $end;
        }
        self::set('titulo', 'Panel Admin | Sabueso');
        self::set('script', $script);
        self::render('sabueso_paneladmin');
    }

    private function getColumnsConfig($esAdmin)
    {
        $base = [
            ['data' => null, 'defaultContent' => '', 'className' => 'control', 'orderable' => false],
            ['data' => '_fecha_creacion', 'title' => '', 'visible' => false, 'orderable' => true],
            ['data' => 'folio_tipo', 'title' => 'Folio / Tipo'],
            ['data' => 'estado', 'title' => 'Estado'],
            ['data' => 'prioridad', 'title' => 'Prioridad'],
            ['data' => 'credito', 'title' => 'Crédito'],
            ['data' => 'fechas', 'title' => 'Fechas'],
        ];
        if ($esAdmin) {
            $base[] = ['data' => 'creador', 'title' => 'Quién levantó'];
            $base[] = ['data' => 'asignado', 'title' => 'Asignado a'];
            $base[] = ['data' => 'tiempo_visitar', 'title' => 'Tiempo para visitar', 'orderable' => false, 'className' => 'text-center'];
            $base[] = ['data' => 'ds_resultado', 'title' => 'Resultado DS', 'orderable' => false, 'className' => 'text-center'];
            $base[] = ['data' => 'prorroga', 'title' => 'Prórroga', 'orderable' => false, 'className' => 'text-center'];
            $base[] = ['data' => 'dictamen_visto', 'title' => '', 'orderable' => false, 'className' => 'text-end'];
        } else {
            $base[] = ['data' => 'tiempo_visitar', 'title' => 'Tiempo para visitar', 'orderable' => false, 'className' => 'text-center'];
            $base[] = ['data' => 'ds_resultado', 'title' => 'Resultado DS', 'orderable' => false, 'className' => 'text-center'];
            $base[] = ['data' => 'prorroga', 'title' => 'Prórroga', 'orderable' => false, 'className' => 'text-center'];
            $base[] = ['data' => 'dictamen_visto', 'title' => '', 'orderable' => false, 'className' => 'text-end'];
        }
        $base[] = ['data' => 'acciones', 'title' => 'Acciones', 'orderable' => false];

        return [
            'esAdminJs'  => $esAdmin ? 'true' : 'false',
            'columnsJs'  => json_encode($base, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_TAG | JSON_HEX_AMP),
        ];
    }

    /**
     * Columnas para la vista Cerrado/Eliminado: mismas que Panel Admin + Quién eliminó/cerró, Fecha cierre, Acciones (solo ojo).
     */
    private function getColumnsConfigCerradoEliminado()
    {
        $base = [
            ['data' => null, 'defaultContent' => '', 'className' => 'control', 'orderable' => false],
            ['data' => 'folio_tipo', 'title' => 'Folio / Tipo'],
            ['data' => 'estado', 'title' => 'Estado'],
            ['data' => 'prioridad', 'title' => 'Prioridad'],
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
                        var prioridadNombre = (t.prioridad_nombre || '').toLowerCase();
                        var prioridadBadge = '<span class=\"badge bg-label-secondary\">' + (t.prioridad_nombre || '—') + '</span>';
                        if (prioridadNombre.indexOf('alta') !== -1) prioridadBadge = '<span class=\"badge bg-danger text-white\">' + (t.prioridad_nombre || '—') + '</span>';
                        else if (prioridadNombre.indexOf('medio') !== -1 || prioridadNombre.indexOf('media') !== -1) prioridadBadge = '<span class=\"badge\" style=\"background-color:#fd7e14;color:#fff;\">' + (t.prioridad_nombre || '—') + '</span>';
                        else if (prioridadNombre.indexOf('bajo') !== -1 || prioridadNombre.indexOf('baja') !== -1) prioridadBadge = '<span class=\"badge\" style=\"background-color:#ffc107;color:#212529;\">' + (t.prioridad_nombre || '—') + '</span>';
                        var estadoBadge = '<span class="badge bg-secondary">Cerrado/Eliminado</span>';
                        var tipoAccion = (t.tipo_accion || '').toLowerCase();
                        var tipoAccionBadge = tipoAccion === 'cerrado' ? '<span class="badge bg-warning text-dark">Cerrado</span>' : '<span class="badge bg-danger">Eliminado</span>';
                        var row = {
                            folio_tipo: '<div class=\"fw-semibold\">' + (t.folio || '—') + '</div><div class=\"small text-muted mt-1\">' + (t.tipo_ticket_nombre || '—') + '</div>',
                            estado: estadoBadge,
                            prioridad: prioridadBadge,
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
                    var htmlCredito = '<div class="row g-2 mb-3"><div class="col-md-6"><span class="text-muted small d-block">ID crédito</span><div class="fw-semibold">' + (credito.id_credito || '—') + '</div></div>';
                    htmlCredito += '<div class="col-md-6"><span class="text-muted small d-block">Nombre cliente</span><div class="fw-semibold">' + esc(credito.Nombre_cliente || credito.nombre_completo || '—') + '</div></div>';
                    htmlCredito += '<div class="col-md-6"><span class="text-muted small d-block">Teléfono</span><div class="fw-semibold">' + esc(credito.telefono_referencia1 || credito.telefono_referencia2 || '—') + '</div></div>';
                    htmlCredito += '<div class="col-md-12"><span class="text-muted small d-block">Dirección</span><div class="fw-semibold small">' + esc(credito.Domicilio_Completo || '—') + '</div></div></div>';
                    var fCreacion = ticket.fecha_creacion ? new Date(ticket.fecha_creacion).toLocaleString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—';
                    var fVenc = ticket.fecha_vencimiento ? new Date(ticket.fecha_vencimiento).toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '—';
                    var fElim = ticket.fecha_eliminacion ? new Date(ticket.fecha_eliminacion).toLocaleString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—';
                    var htmlTicket = '<div class="border rounded p-3 mb-3"><div class="fw-semibold mb-2">' + esc(ticket.folio || '—') + ' · ' + esc(ticket.tipo_ticket_nombre || '') + '</div>';
                    htmlTicket += '<div class="small"><span class="text-muted">Descripción:</span> ' + esc(ticket.descripcion_inicial || '—') + '</div>';
                    htmlTicket += '<div class="small mt-2">Creación: ' + fCreacion + ' · Venc: ' + fVenc + '</div>';
                    htmlTicket += '<div class="small mt-1">Quién levantó: ' + esc(ticket.creador_nombre || '—') + '</div>';
                    htmlTicket += '<div class="small">Asignado (último): ' + (ticket.asignado_nombre ? esc(ticket.asignado_nombre) : '—') + '</div>';
                    htmlTicket += '<div class="small text-danger mt-1">Cerrado/eliminado: ' + fElim + ' por ' + esc(ticket.quien_elimino_nombre || '—') + '</div></div>';
                    var historial = d.historial_asignacion || [];
                    var htmlHistorial = historial.length ? '<ul class="list-unstyled small mb-0">' + historial.map(function(h){ return '<li>' + esc(h.persona || '—') + ': ' + (h.duracion_humana || '—') + '</li>'; }).join('') + '</ul>' : '<span class="text-muted small">Sin historial de asignación.</span>';
                    var html = '<div class="cerrado-ver-seccion"><h6 class="text-uppercase small fw-bold text-muted mb-2">Datos del crédito</h6>' + htmlCredito + '</div>';
                    html += '<div class="cerrado-ver-seccion"><h6 class="text-uppercase small fw-bold text-muted mb-2">Ticket</h6>' + htmlTicket + '</div>';
                    html += '<div class="cerrado-ver-seccion"><h6 class="text-uppercase small fw-bold text-muted mb-2">Historial de asignación</h6>' + htmlHistorial + '</div>';
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
     * Vista Estadísticas: solo lectura, datos agregados de tickets (tiempos, conteos, fechas).
     */
    public function estadisticas()
    {
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
        function renderPeriodList() {
            if (!estadisticasDatos) return;
            var filas = estadisticasDatos[estadisticasFiltroPeriodo] || [];
            var wrap = $('#estadPeriodList');
            wrap.empty();
            if (!filas.length) {
                wrap.append('<p class="text-muted small px-2 mb-0">Sin datos para este período.</p>');
                return;
            }
            var maxN = 1;
            filas.forEach(function(r) { var v = parseInt(r.n, 10); if (!isNaN(v) && v > maxN) maxN = v; });
            filas.forEach(function(row) {
                var label = labelPeriodo(estadisticasFiltroPeriodo, row.periodo);
                var n = parseInt(row.n, 10);
                if (isNaN(n)) n = 0;
                var pct = Math.round((n / maxN) * 100);
                wrap.append(
                    '<div class="estad-period-row">' +
                    '<span class="estad-period-label text-body">' + attrEsc(label) + '</span>' +
                    '<div class="estad-mini-bar"><div style="width:0%" data-w="' + pct + '"></div></div>' +
                    '<span class="estad-period-val">' + n + '</span></div>'
                );
            });
            setTimeout(function() {
                wrap.find('.estad-mini-bar > div').each(function() { var w = $(this).data('w'); if (w != null) $(this).css('width', w + '%'); });
            }, 80);
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
            var tb = $('#tbodyPorGestor');
            tb.empty();
            if (!list.length) {
                tb.append('<tr><td colspan="7" class="text-muted">Sin datos por quien levantó (tickets con dictamen enviado en el detalle).</td></tr>');
                return;
            }
            list.forEach(function(g, i) {
                var tasa = g.tasa != null ? g.tasa : 0;
                var badgeClass = tasa >= 90 ? 'bg-success' : (tasa >= 70 ? 'bg-warning text-dark' : 'bg-danger');
                var sinLeer = g.sin_leer != null ? g.sin_leer : 0;
                var sinClass = sinLeer === 0 ? 'text-success' : (sinLeer <= 2 ? 'text-warning' : 'text-danger');
                var hue = avatarHue(g.nombre);
                var idp = parseInt(g.id_persona, 10) || 0;
                var clickAttr = idp ? ' class="estad-gestor-fila" style="cursor:pointer" data-id-gestor="' + idp + '"' : '';
                var pctC = g.cumplimiento_pct_promedio;
                var pctCTxt = (pctC !== null && pctC !== undefined) ? pctC + '%' : '—';
                var resumenC = g.cumplimiento_resumen_texto || '';
                var tipC = 'Evaluados: ' + (g.cumplimiento_evaluados || 0) + ' | Sin evaluar: ' + (g.cumplimiento_sin_evaluar || 0) + (resumenC ? ' | ' + resumenC : '');
                var badgeC = (pctC !== null && pctC >= 70) ? 'bg-success' : ((pctC !== null && pctC >= 40) ? 'bg-warning text-dark' : 'bg-secondary');
                var cumplCell = '<span class="badge rounded-pill ' + badgeC + ' estad-tooltip-custom" data-bs-toggle="tooltip" data-bs-placement="left" title="' + attrEsc(tipC) + '">' + pctCTxt + '</span>';
                if (pctC === null && (g.cumplimiento_sin_evaluar || 0) === (g.tickets || 0)) {
                    cumplCell = '<span class="text-muted small">Sin DS</span>';
                }
                tb.append(
                    '<tr' + clickAttr + '><td><div class="d-flex align-items-center gap-2">' +
                    '<span class="estad-avatar" style="background:hsl(' + hue + ',55%,48%)">' + attrEsc(iniciales(g.nombre)) + '</span>' +
                    '<div class="d-flex flex-column"><span class="small fw-medium text-truncate" style="max-width:9rem" title="' + attrEsc(g.nombre || '') + '">' + attrEsc(g.nombre || '') + '</span>' +
                    (idp ? '<small class="text-muted"><i class="fa-solid fa-fingerprint me-1"></i>ID ' + idp + '</small>' : '') + '</div></div></td>' +
                    '<td class="text-center fw-bold text-primary">' + (g.tickets || 0) + '</td>' +
                    '<td class="text-center small text-muted">' + attrEsc(g.tiempo_lectura || '—') + '</td>' +
                    '<td class="text-center small text-muted">' + attrEsc(g.tiempo_envio || '—') + '</td>' +
                    '<td class="text-center fw-bold ' + sinClass + '">' + sinLeer + '</td>' +
                    '<td class="text-center"><span class="badge rounded-pill ' + badgeClass + '">' + tasa + '%</span></td>' +
                    '<td class="text-center">' + cumplCell + '</td></tr>'
                );
            });
        }
        function renderPorSabueso() {
            var list = (estadisticasDatos && estadisticasDatos.por_sabueso) || [];
            var tb = $('#tbodyPorSabueso');
            tb.empty();
            if (!list.length) {
                tb.append('<tr><td colspan="4" class="text-muted">Sin datos: hace falta dictamen enviado con autor (id_persona en dictamen).</td></tr>');
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
                    '<span class="estad-avatar" style="background:hsl(' + hue + ',50%,45%)">' + attrEsc(iniciales(s.nombre || '')) + '</span>' +
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
                            customClass: { popup: 'estad-detalle-swal', confirmButton: 'btn btn-success px-4' },
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
        function abrirDetalleGestor(idPersona, nombre) {
            if (!idPersona) return;
            swalCargandoDetalle('Cargando tickets del gestor…');
            http.request({
                endpoint: '/sabueso/getEstadisticasGestorDetalle',
                metodo: 'POST',
                data: JSON.stringify({ id_persona_creador: idPersona }),
                contentType: 'application/json',
                processData: false,
                showLoader: false,
                onSuccess: function(r) {
                    if (typeof Swal !== 'undefined' && Swal.close) Swal.close();
                    if (!r.success) { if (typeof Swal !== 'undefined') Swal.fire('Aviso', r.mensaje || 'Sin datos', 'info'); return; }
                    var filas = r.filas || [];
                    if (!filas.length) { if (typeof Swal !== 'undefined') Swal.fire('Aviso', 'Sin tickets con dictamen enviado para este gestor.', 'info'); return; }
                    function tipG(title) { return ' <i class="fa fa-question-circle estad-modal-th-tip text-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="' + attrEsc(title) + '"></i>'; }
                    var html = '<div class="estad-modal-detalle-wrap">' +
                        '<div class="estad-modal-detalle-leyenda text-start">' +
                        '<strong>Vista por gestor.</strong> Solo tickets que <strong>esta persona levantó</strong> y ya tienen dictamen enviado.<br>' +
                        '<strong>¿Abrió?</strong> = ¿Ya abrió el dictamen en pantalla? <strong>T. apertura</strong> = cuánto tardó en abrirlo después del envío (Pendiente = aún no abre).<br>' +
                        '<strong>Resultado DS / % efect.</strong> = salen cuando existe <em>dictamen del sistema</em> generado para ese ticket. Si dice «Sin DS», hay que generarlo desde Panel Admin (botón dictamen sistema).</div>' +
                        '<div class="estad-modal-detalle-table-wrap table-responsive">' +
                        '<table class="table table-sm table-bordered text-start">' +
                        '<thead><tr>' +
                        '<th>Folio' + tipG('Identificador del ticket.') + '</th>' +
                        '<th>Levantado' + tipG('Cuándo se creó el ticket.') + '</th>' +
                        '<th>Dictamen enviado' + tipG('Cuándo Sabueso envió el dictamen a usted.') + '</th>' +
                        '<th>¿Abrió?' + tipG('Si ya abrió/vio el dictamen.') + '</th>' +
                        '<th>Abrió el' + tipG('Fecha/hora en que se registró visto.') + '</th>' +
                        '<th>T. apertura' + tipG('Tiempo entre envío y apertura. Pendiente si aún no abre.') + '</th>' +
                        '<th>Resultado DS' + tipG('Solo texto claro (ej. No visito). Sin códigos técnicos.') + '</th>' +
                        '<th>Pago' + tipG('Sí = hubo pago en estado de cuenta dentro de las 12h que evalúa el dictamen. No = no hubo pago en ese rango (ej. no visitó y sin pago en esas horas).') + '</th>' +
                        '<th>% efect.' + tipG('% efectividad según reglas de cumplimiento (visita/pago/direcciones).') + '</th>' +
                        '</tr></thead><tbody>';
                    filas.forEach(function(f) {
                        var pctF = f.pct_efectividad;
                        var pctFTxt = (pctF !== null && pctF !== undefined && pctF !== '') ? pctF + '%' : '<span class="text-muted">Sin DS</span>';
                        var resMostrar = (f.resultado_ds_mostrar != null && f.resultado_ds_mostrar !== '') ? attrEsc(f.resultado_ds_mostrar) : '<span class="text-muted">Sin dictamen sistema</span>';
                        var pagoV = f.pago_en_ventana_resumen;
                        var pagoVTxt;
                        if (pagoV === 'Sí' || pagoV === 'Si') {
                            pagoVTxt = '<span class="text-success fw-semibold">Sí</span>';
                        } else if (pagoV === 'No') {
                            pagoVTxt = '<span class="text-danger fw-semibold">No</span>';
                        } else {
                            pagoVTxt = '<span class="text-muted">—</span>';
                        }
                        html += '<tr><td class="fw-medium">' + attrEsc(f.folio || '—') + '</td><td>' + fmtFecha(f.fecha_creacion) + '</td>' +
                            '<td>' + fmtFecha(f.dictamen_envio) + '</td><td>' + attrEsc(f.visto_si_no || '—') + '</td>' +
                            '<td>' + (f.visto_cuando ? fmtFecha(f.visto_cuando) : '—') + '</td>' +
                            '<td>' + attrEsc(f.tiempo_lectura_humano || 'Pendiente') + '</td>' +
                            '<td><small>' + resMostrar + '</small></td>' +
                            '<td class="text-center">' + pagoVTxt + '</td>' +
                            '<td>' + pctFTxt + '</td></tr>';
                    });
                    html += '</tbody></table></div></div>';
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Gestor · ' + (r.nombre || nombre || ''),
                            html: html,
                            width: '960px',
                            showConfirmButton: true,
                            confirmButtonText: 'Cerrar',
                            customClass: { popup: 'estad-detalle-swal', confirmButton: 'btn btn-primary px-4' },
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
        function cargarEstadisticasSabueso() {
            $('#estadisticasSabuesoContenido').hide();
            http.request({
                endpoint: '/sabueso/getEstadisticasTickets',
                metodo: 'POST',
                onSuccess: function(r) {
                    $('#estadisticasSabuesoContenido').show();
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
                    var maxTop = Math.max(activos, enviados, vistos, 1);
                    function pct(v) { return Math.round((v / maxTop) * 100); }
                    $('#statTotalActivos').text(activos);
                    $('#statDictamenEnviado').text(enviados);
                    $('#statDictamenVisto').text(vistos);
                    $('#barActivos').css('width', pct(activos) + '%');
                    $('#pctActivos').text(pct(activos) + '%');
                    $('#barEnviado').css('width', pct(enviados) + '%');
                    $('#pctEnviado').text(pct(enviados) + '%');
                    $('#barVisto').css('width', pct(vistos) + '%');
                    $('#pctVisto').text(pct(vistos) + '%');
                    var ts = estadisticasDatos.tiempos_sabueso_segundos;
                    var tg = estadisticasDatos.tiempos_gestor_segundos;
                    if (ts && ts.promedio_humano) {
                        $('#statTiempoSabuesoValor').text(ts.promedio_humano);
                        $('#statTiempoSabuesoSub').text('Promedio con ' + (ts.muestras || 0) + ' tickets · Desde primera asignación Sabueso hasta envío al gestor');
                    } else {
                        $('#statTiempoSabuesoValor').html('<span class="text-muted fs-6 fw-normal">Sin datos</span>');
                        $('#statTiempoSabuesoSub').text('Desde primera asignación Sabueso hasta envío al gestor');
                    }
                    if (tg && tg.promedio_humano) {
                        $('#statTiempoGestorValor').text(tg.promedio_humano);
                        $('#statTiempoGestorSub').text('Promedio con ' + (tg.muestras || 0) + ' tickets · Desde envío hasta que el gestor abre el dictamen');
                    } else {
                        $('#statTiempoGestorValor').html('<span class="text-muted fs-6 fw-normal">Sin datos</span>');
                        $('#statTiempoGestorSub').text('Desde envío hasta que el gestor abre el dictamen');
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
                    renderPeriodList();
                    renderPorGestor();
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
            // Delegación: el tbody se reemplaza al renderizar; el clic debe vivir en document
            $(document).on('click', '#tbodyPorGestor tr[data-id-gestor]', function(e) {
                var id = parseInt($(this).attr('data-id-gestor'), 10);
                if (id) abrirDetalleGestor(id, '');
            });
            $(document).on('click', '#tbodyPorSabueso tr[data-id-sabueso]', function(e) {
                var id = parseInt($(this).attr('data-id-sabueso'), 10);
                if (id) abrirDetalleSabueso(id);
            });
            $('#grpFiltroPeriodo').on('click', 'button[data-key]', function() {
                $('#grpFiltroPeriodo button').removeClass('active');
                $(this).addClass('active');
                estadisticasFiltroPeriodo = $(this).data('key') || 'por_dia';
                renderPeriodList();
            });
            $('#grpResumenGestor').on('click', 'button[data-tab]', function() {
                $('#grpResumenGestor button').removeClass('active');
                $(this).addClass('active');
                var tab = $(this).data('tab');
                $('#panelResumenGlobal, #panelResumenGestor, #panelResumenSabueso').addClass('d-none');
                if (tab === 'gestor') {
                    $('#panelResumenGestor').removeClass('d-none');
                    setTimeout(initEstadisticasTooltips, 100);
                } else if (tab === 'sabueso') {
                    $('#panelResumenSabueso').removeClass('d-none');
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
            cargarEstadisticasSabueso();
        });
        </script>
        SCRIPT;
        self::set('titulo', 'Estadísticas | Sabueso');
        self::set('script', $script);
        self::render('sabueso_estadisticas');
    }

    /**
     * API: estadísticas agregadas de tickets (conteos, series, tiempos Sabueso/gestor).
     */
    public function getEstadisticasTickets()
    {
        $datos = TicketDAO::getEstadisticasTickets();
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
        $res = TicketDAO::getEstadisticasGestorDetalle($id);
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
        $resultado = TicketDAO::getListaTickets($usuarioId, false);
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
     * API: datos completos de un ticket cerrado/eliminado para el modal Ver (crédito, ticket, historial asignación, quien eliminó).
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
        $historial = $idCredito > 0 ? RegistroAsignacion::getHistorialPorCredito($idCredito) : [];
        $historial = isset($historial['historial']) ? $historial['historial'] : [];
        self::respuestaJSON([
            'success' => true,
            'mensaje' => 'OK',
            'datos' => [
                'credito' => $credito,
                'ticket' => $ticket,
                'historial_asignacion' => $historial,
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
     * API: indica si en este momento se permite levantar ticket (menú Ticket / modal).
     */
    public function ticketLevantarPermitido()
    {
        $msg = self::mensajeSiVentanaLevantarTicketCerrada();
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
        $msgVentana = self::mensajeSiVentanaLevantarTicketCerrada();
        if ($msgVentana !== null) {
            self::respuestaJSON(['success' => false, 'mensaje' => $msgVentana]);
            return;
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
        $infoSabueso = CreditoInfoSabueso::getPorCredito($idCredito);
        // Inicializar siempre para que el frontend reciba las claves
        $todo['informacion_ingresos'] = '';
        $todo['empresa'] = '';
        $todo['empleado'] = '';
        $todo['ingreso_mensual_neto'] = '';
        $todo['telefono_laboral'] = '';
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
            } else {
                $todo['informacion_ingresos'] = '';
                $todo['empresa'] = '';
                $todo['empleado'] = '';
                $todo['ingreso_mensual_neto'] = '';
                $todo['telefono_laboral'] = '';
                if (is_array($extraido) && !empty($extraido['error'])) {
                    $todo['_fad_debug'] = $extraido['error'];
                    if (!empty($extraido['detail'])) {
                        $todo['_fad_debug_detail'] = $extraido['detail'];
                    }
                }
            }
        }
        $todo['tickets'] = TicketDAO::getTicketsPorIdCredito($idCredito);
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
            $gestiones = GestionesDAO::getAllGestiones($idCredito, '');
            $gestiones = is_array($gestiones) ? $gestiones : [];
            $gestiones = array_slice($gestiones, 0, 16);
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
        $resultado = TicketDAO::asignar($idTicket, $idPersona);
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
            if (!is_array($modulos) || !in_array(19, $modulos)) {
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
            $evid = TicketDAO::getEvidenciasPorTicket($idTicket);
            $listaEvid = isset($evid['datos']) && is_array($evid['datos']) ? array_slice($evid['datos'], -5) : [];
            $lineas[] = "\nEVIDENCIAS (resumen):";
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
            $evid = TicketDAO::getEvidenciasPorTicket($idTicket);
            $listaEvid = isset($evid['datos']) ? $evid['datos'] : [];
            if (!empty($listaEvid)) {
                $lineas[] = "\n=== EVIDENCIAS (fotos) ===";
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
            if (!is_array($modulos) || !in_array(19, $modulos)) {
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
        if (!is_array($modulos) || !in_array(19, $modulos)) {
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
        if ($tipo === '' || $descripcion === '') {
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
        $resultado = TicketDAO::enviarDictamenGestor($idTicket);
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
        if (!is_array($modulos) || !in_array(19, $modulos)) {
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
        $resultado = TicketDAO::generarDictamenSistema($idTicket);
        self::respuestaJSON([
            'success' => $resultado['success'] ?? false,
            'mensaje' => $resultado['mensaje'] ?? '',
            'datos'   => $resultado['datos'] ?? null,
        ]);
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
        self::respuestaJSON([
            'success' => $resultado['success'] ?? false,
            'mensaje' => $resultado['mensaje'] ?? '',
            'datos'   => $resultado['datos'] ?? null,
        ]);
    }

    /**
     * API: otorgar prórroga única de 12 horas al dictamen del sistema.
     */
    public function otorgarProrrogaDictamenSistema()
    {
        $modulos = $_SESSION['modulos'] ?? [];
        if (!is_array($modulos) || !in_array(19, $modulos)) {
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
        $resultado = TicketDAO::getEvidenciasPorTicket($idTicket);
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
        if (!is_array($modulos) || !in_array(19, $modulos)) {
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
            $dir = __DIR__ . '/../uploads/sabueso_evidencias';
            \Core\SecureUpload::ensureDir($dir);
            $nombreArchivo = 'ev_' . $idTicket . '_' . $idPersona . '_' . \Core\SecureUpload::generateSafeFilename($ext);
            $rutaCompleta = $dir . '/' . $nombreArchivo;
            if (!move_uploaded_file($_FILES['evidencia']['tmp_name'], $rutaCompleta)) {
                self::respuestaJSON(['success' => false, 'mensaje' => 'Error al guardar el archivo.']);
                return;
            }
            $rutaRelativa = 'sabueso_evidencias/' . $nombreArchivo;
            $resultado = TicketDAO::guardarEvidencia($idTicket, $idPersona, $rutaRelativa, $_FILES['evidencia']['name']);
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
            $path = __DIR__ . '/../uploads/' . $row['ruta_archivo'];
            if (is_file($path)) {
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
        $path = __DIR__ . '/../uploads/' . $row['ruta_archivo'];
        if (!is_file($path)) {
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

