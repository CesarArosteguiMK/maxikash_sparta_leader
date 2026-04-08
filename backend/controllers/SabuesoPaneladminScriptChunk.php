<?php
/**
 * Fragmento extraído de Sabueso::paneladmin() — mismo JS del panel (rastreo, tabla, etc.).
 * Scope: $panelAdminTitulosPorCatJs, $soloConsultaCreditoJs, $googleMapsKeyJs, $columnsJson, $usuarioId, $personaId, $usuarioNombre.
 * Define: $script (HTML con etiqueta script y merge de evidencias).
 */
        $script = <<<SCRIPT
        <script>
        window.panelAdminTitulosPorCat = {$panelAdminTitulosPorCatJs};
        var esAdminTicket = true;
        window.PANEL_ADMIN_SOLO_CONSULTA_CREDITO = {$soloConsultaCreditoJs};
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
        $script .= "\n\n        function actualizarCountdownsDictamen(selector) {\n            var sel = selector || '#tablaTicketsPanel';\n            $(sel + ' .dictamen-countdown').each(function() {\n                var el = this;\n                var fLim = $(el).attr('data-fecha-limite');\n                var f = $(el).attr('data-fecha-envio');\n                if (!f && !fLim) return;\n                var fin;\n                if (fLim) { fin = new Date(fLim); }\n                else { var envio = new Date(f); fin = new Date(envio.getTime() + 12 * 60 * 60 * 1000); }\n                var now = new Date();\n                var ms = fin - now;\n                var txt = '-';\n                var txtCorto = '-';\n                var expired = ms <= 0;\n                if (ms > 0) {\n                    var h = Math.floor(ms / 3600000);\n                    var m = Math.floor((ms % 3600000) / 60000);\n                    var extTipo = ($(el).attr('data-extension-tipo') || '').toString(); var pref = fLim ? (extTipo === 'intensidad' ? 'Intensidad · ' : 'Prórroga · ') : '';
                    txt = pref + 'Tiempo restante: ' + h + 'h ' + m + 'm';\n                    txtCorto = (fLim ? 'P2 ' : '') + h + 'h ' + m + 'm';\n                } else {\n                    var extTipo2 = ($(el).attr('data-extension-tipo') || '').toString(); txt = fLim ? (extTipo2 === 'intensidad' ? 'Intensidad vencida' : 'Prórroga vencida') : 'Plazo vencido';\n                    txtCorto = txt;\n                }\n                $(el).attr('title', txt).attr('data-bs-title', txt).toggleClass('text-danger', expired);\n                var txtEl = $(el).find('.dictamen-countdown-text');\n                if (txtEl.length) txtEl.text(txtCorto).toggleClass('text-danger', expired);\n            });\n        }\n        function attrEsc(s){ if (s==null||s===undefined) return ''; var x=(s+'').split('&').join('&amp;').split('<').join('&lt;'); return x.split('\"').join('&quot;'); }\n        function panelAdminAplicarTitulosColumnas(dt, cat) {\n            if (!dt || !dt.columns) return;\n            var M = window.panelAdminTitulosPorCat || {};\n            var k = (cat && String(cat).trim()) ? String(cat).toLowerCase().trim() : '_mixto';\n            if (!M[k]) { k = '_mixto'; }\n            var T = M[k];\n            if (!T) return;\n            function sc(i, t) { try { var h = dt.column(i).header(); if (h && h.length) h[0].innerHTML = t; } catch (e) {} }\n            sc(2, T.folio); sc(3, T.estado); sc(4, T.prioridad); sc(5, T.ref); sc(6, T.fechas);\n            sc(7, T.creador); sc(8, T.asignado); sc(9, T.tiempo); sc(10, T.ds);\n        }\n        function panelAdminIconoPorCategoria(c) {\n            c = (c || '').toLowerCase();\n            var m = { sabueso: 'fa-dog', plantilla: 'fa-file-lines', atencion_cliente: 'fa-headset', validaciones: 'fa-clipboard-check', viaticos: 'fa-receipt', aplicaciones_de_pago: 'fa-credit-card', credito_problematico: 'fa-triangle-exclamation', aclaracion_credito: 'fa-circle-question' };\n            return (c && m[c]) ? m[c] : 'fa-list';\n        }\n        $(document).ready(function() {\n            if (window.PANEL_ADMIN_SOLO_CONSULTA_CREDITO) { return; }\n            configuraTabla(\"#tablaTicketsPanel\", {\n                registrosPorPagina: 10,\n                order: [[1, 'desc']],\n                columns: " . $columnsJson['columnsJs'] . "\n            });\n            var urlCat = (function(){ var p = new URLSearchParams(window.location.search); return (p.get('categoria') || '').toLowerCase().trim(); })();\n            try { var dtPa = $('#tablaTicketsPanel').DataTable(); panelAdminAplicarTitulosColumnas(dtPa, urlCat); } catch (ePa) {}\n            window.panelAdminFiltros = window.panelAdminFiltros || {}; window.panelAdminFiltros.categoria_gestion = urlCat || 'sabueso';\n            var catLabelsPanel = { sabueso: 'Sabueso', plantilla: 'Plantilla', atencion_cliente: 'Atención al cliente', validaciones: 'Validaciones', viaticos: 'Viáticos', aplicaciones_de_pago: 'Aplicaciones de pago', credito_problematico: 'Crédito problemático', aclaracion_credito: 'Aclaración de crédito' };\n            if (urlCat && $('#panelAdminTitulo').length) { var lbl = catLabelsPanel[urlCat] || urlCat; $('#panelAdminTitulo').html('<i class=\"fa-solid ' + panelAdminIconoPorCategoria(urlCat) + ' me-2 flex-shrink-0\" aria-hidden=\"true\"></i><span class=\"panel-admin-titulo-texto\">Panel Admin – ' + lbl + '</span>'); }\n            var categoriasSimplesInit = ['viaticos','aplicaciones_de_pago','credito_problematico','aclaracion_credito','plantilla','atencion_cliente','validaciones'];\n            var esSimpleInit = categoriasSimplesInit.indexOf(urlCat) !== -1;\n            if ($('#btnAbrirConsultaCredito').length) $('#btnAbrirConsultaCredito').toggle(!esSimpleInit);\n            if ($('#sabuesoPanelEaster').length) $('#sabuesoPanelEaster').toggle(!esSimpleInit);\n            if ($('#panelAdminFiltrosWrap').length) $('#panelAdminFiltrosWrap').toggle(!esSimpleInit);\n            window.panelAdminPrimeraCarga = true;\n            getTicketsPanelAdmin();\n        });\n\n        function getTicketsPanelAdmin() {\n            if (window.PANEL_ADMIN_SOLO_CONSULTA_CREDITO) { return; }\n            var filtrosPayload = (typeof window.panelAdminFiltros === 'object' && window.panelAdminFiltros) ? window.panelAdminFiltros : {};\n            var esPrimeraCarga = (window.panelAdminPrimeraCarga === true);\n            if (esPrimeraCarga && typeof showWait === 'function') showWait();\n            http.request({\n                endpoint: \"/sabueso/getTicketsPanelAdmin\",\n                metodo: \"POST\",\n                data: JSON.stringify({ filtros: filtrosPayload }),\n                contentType: \"application/json\",\n                processData: false,\n                showLoader: false,\n                onSuccess: function(resp) {\n                    if (esPrimeraCarga) { window.panelAdminPrimeraCarga = false; }\n                    var datos = (resp.datos || []).map(function(t) {\n                        var fechaCreacion = t.fecha_creacion ? new Date(t.fecha_creacion).toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '—';\n                        var fechaVenc = t.fecha_vencimiento ? new Date(t.fecha_vencimiento).toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '—';\n                        var prioridadNombre = (t.prioridad_nombre || '').toLowerCase();\n                        var prioridadBadge = '<span class=\"badge bg-label-secondary\">' + (t.prioridad_nombre || '—') + '</span>';\n                        if (prioridadNombre.indexOf('alta') !== -1) prioridadBadge = '<span class=\"badge bg-danger text-white\">' + (t.prioridad_nombre || '—') + '</span>';\n                        else if (prioridadNombre.indexOf('medio') !== -1 || prioridadNombre.indexOf('media') !== -1) prioridadBadge = '<span class=\"badge\" style=\"background-color:#fd7e14;color:#fff;\">' + (t.prioridad_nombre || '—') + '</span>';\n                        else if (prioridadNombre.indexOf('bajo') !== -1 || prioridadNombre.indexOf('baja') !== -1) prioridadBadge = '<span class=\"badge\" style=\"background-color:#ffc107;color:#212529;\">' + (t.prioridad_nombre || '—') + '</span>';\n                        else if (prioridadNombre.indexOf('sin prioridad') !== -1) prioridadBadge = '<span class=\"badge bg-secondary\" style=\"background-color:#6c757d!important;color:#fff;\">' + (t.prioridad_nombre || '—') + '</span>';\n                        var estadoBadge = (t.asignado_nombre && (t.asignado_nombre + '').trim()) ? '<span class=\"badge bg-success text-white\">Asignado</span>' : '<span class=\"badge bg-label-secondary\">Abierto</span>';\n                        var vistoHtml = '';\n                        if ((t.dictamen_estado || '') === 'enviado_al_gestor') {\n                            var vistoTexto = t.dictamen_fecha_visto ? (new Date(t.dictamen_fecha_visto).toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' }) + ' ' + new Date(t.dictamen_fecha_visto).toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' })) : 'No visto';\n                            var iconoOjo = (t.dictamen_fecha_visto && (t.dictamen_fecha_visto + '').trim()) ? 'fa-eye' : 'fa-eye-slash';\n                            var tituloOjo = (t.dictamen_fecha_visto && (t.dictamen_fecha_visto + '').trim()) ? ('Visto: ' + vistoTexto) : 'No visto. Clic para ver dictamen';\n                            vistoHtml = '<span class=\"d-inline-flex align-items-center gap-1 justify-content-end btn-dictamen-ojito\" role=\"button\" tabindex=\"0\" data-bs-toggle=\"tooltip\" data-bs-title=\"' + (tituloOjo + '').replace(/\\x22/g, '&quot;') + '\" data-id-ticket=\"' + (t.id_ticket || '') + '\"><i class=\"fa ' + iconoOjo + ' text-info small\"></i></span>';\n                        }\n                        var tiempoVisitarHtml = '—';\n                        var fEnv = (t.dictamen_fecha_envio || '').trim();\n                        var esNuevo = fEnv && new Date(fEnv) >= new Date('2026-03-09T00:00:00');\n                        if ((t.dictamen_estado || '') === 'enviado_al_gestor' && esNuevo && fEnv) {\n                            var envio = new Date(fEnv);\n                            var fLim = (t.prorroga_fecha_limite || '').trim();\n                            var esExt12h = t.prorroga_activa && fLim;\n                            var extTipoRow = ((t.extension_countdown_tipo || '') + '').trim();\n                            var esIntRow = extTipoRow === 'intensidad';\n                            var fin = esExt12h ? new Date(fLim) : new Date(envio.getTime() + 12 * 60 * 60 * 1000);\n                            var now = new Date();\n                            var ms = fin - now;\n                            var txtInicial = ms > 0 ? (Math.floor(ms / 3600000) + 'h ' + Math.floor((ms % 3600000) / 60000) + 'm') : 'Plazo vencido';\n                            var clsExt12 = esExt12h ? (esIntRow ? ' dictamen-countdown-intensidad' : ' dictamen-countdown-prorroga') : '';\n                            var dataLim = esExt12h ? (' data-fecha-limite=\"' + fLim.replace(/\"/g, '&quot;') + '\"') : '';\n                            var dataExtTipo = esExt12h ? (' data-extension-tipo=\"' + (esIntRow ? 'intensidad' : 'prorroga') + '\"') : '';\n                            var iconTitle = esExt12h ? (esIntRow ? 'Intensidad +12h (2ª ventana)' : 'Prórroga +12h (2ª ventana)') : '';\n                            var tipCuenta = esExt12h ? (esIntRow ? 'Intensidad +12h · tiempo hasta límite' : 'Prórroga +12h · tiempo hasta límite') : 'Ventana 12h desde envío';\n                            var clockCls = esExt12h ? (esIntRow ? 'text-info' : 'text-warning') : 'text-info';\n                            var supCls = esIntRow ? 'dictamen-intensidad-marca' : 'dictamen-prorroga-marca';\n                            var iconBlock = esExt12h ? ('<span class=\"position-relative d-inline-flex align-items-baseline\"><i class=\"fa-solid fa-clock ' + clockCls + ' small\"></i><sup class=\"' + supCls + '\" title=\"' + iconTitle + '\">2</sup></span>') : ('<i class=\"fa-solid fa-clock text-info small\"></i>');\n                            var txtCountCls = esExt12h && esIntRow ? ' dictamen-countdown-text text-info' : ' dictamen-countdown-text';\n                            tiempoVisitarHtml = '<span class=\"d-inline-flex align-items-center gap-1 dictamen-countdown cursor-pointer' + clsExt12 + '\" role=\"button\" tabindex=\"0\" data-fecha-envio=\"' + fEnv.replace(/\"/g, '&quot;') + '\"' + dataLim + dataExtTipo + ' data-id-ticket=\"' + (t.id_ticket || '') + '\" data-bs-toggle=\"tooltip\" data-bs-title=\"' + tipCuenta + '\">' + iconBlock + '<span class=\"' + txtCountCls.trim() + '\">' + txtInicial + '</span></span>';\n                        }\n                        var prHtml = (t.prorroga_otorgada && t.prorroga_html) ? t.prorroga_html : '';\n                        if (prHtml && tiempoVisitarHtml !== '—') { tiempoVisitarHtml = '<div class=\"d-flex flex-column align-items-center\">' + tiempoVisitarHtml + prHtml + '</div>'; }\n                        else if (prHtml) { tiempoVisitarHtml = prHtml; }\n                        var catLabels = { sabueso: 'Sabueso', plantilla: 'Plantilla', atencion_cliente: 'Atención al cliente', validaciones: 'Validaciones', viaticos: 'Viáticos', aplicaciones_de_pago: 'Aplicaciones de pago', credito_problematico: 'Crédito problemático', aclaracion_credito: 'Aclaración de crédito' };\n                        var catKeyPa = (t.categoria_gestion || 'sabueso').toString().toLowerCase().trim();\n                        var catIcoPa = panelAdminIconoPorCategoria(catKeyPa);\n                        var catLabel = catLabels[catKeyPa] || (t.categoria_gestion || '—');\n                        var folioTipoHtml = '<div class=\"fw-semibold\">' + (t.folio || '—') + '</div><div class=\"small text-muted mt-1\">' + (t.tipo_ticket_nombre || '—') + '</div><div class=\"mt-1\"><span class=\"badge bg-label-primary small d-inline-flex align-items-center gap-1\" title=\"' + (catLabel + '').replace(/\"/g, '&quot;') + '\"><i class=\"fa-solid ' + catIcoPa + '\" style=\"font-size:0.7rem;line-height:1\"></i>' + (catLabel + '').replace(/&/g, '&amp;').replace(/</g, '&lt;') + '</span></div>';\n                        var creditoVal = (t.id_credito != null && t.id_credito > 0) ? ('#' + t.id_credito) : (t.asunto || t.tipo_categoria || '—');\n                        var creditoHtml = '<small>' + (creditoVal + '').replace(/&/g, '&amp;').replace(/</g, '&lt;') + '</small>';\n                        var row = {\n                            _fecha_creacion: (t.fecha_creacion || ''),\n                            folio_tipo: folioTipoHtml,\n                            estado: estadoBadge,\n                            prioridad: prioridadBadge,\n                            credito: creditoHtml,\n                            fechas: '<div class=\"small d-flex align-items-center gap-1\"><i class=\"fa fa-calendar-plus-o text-muted\" style=\"width: 1rem;\"></i><span>Creación: ' + fechaCreacion + '</span></div><div class=\"small text-muted d-flex align-items-center gap-1 mt-1\"><i class=\"fa fa-calendar-times-o\" style=\"width: 1rem;\"></i><span>Vencimiento: ' + fechaVenc + '</span></div>',\n                            creador: '<small class=\"d-flex align-items-center gap-1\"><i class=\"fa fa-user\"></i>' + (t.creador_nombre || '—') + '</small>',\n                            asignado: (t.asignado_nombre && t.asignado_nombre.trim()) ? '<small class=\"d-flex align-items-center gap-1\"><i class=\"fa fa-user-check text-success\"></i>' + t.asignado_nombre + '</small>' : '<span class=\"text-muted\">—</span>',\n                            tiempo_visitar: tiempoVisitarHtml,\n                            ds_resultado: (t.ds_resultado_html != null && t.ds_resultado_html !== '') ? t.ds_resultado_html : '—',\n                            dictamen_visto: vistoHtml,\n                            acciones: (function(){ var tieneCredito = (t.id_credito != null && t.id_credito > 0); var fEnvDS = (t.dictamen_fecha_envio || '').trim(); var ticketDesdeMarzo10 = t.fecha_creacion && (new Date(t.fecha_creacion) >= new Date(2026, 2, 10)); var plazoVencido = false; if (fEnvDS && ticketDesdeMarzo10 && (t.dictamen_estado || '') === 'enviado_al_gestor') { var plazoDS = new Date(fEnvDS).getTime() + 12*60*60*1000; plazoVencido = (Date.now() >= plazoDS); } var btns = '<div class=\"d-inline-flex flex-row flex-wrap gap-1 align-items-center justify-content-center panel-admin-acciones-cell\">'; if (tieneCredito) { btns += '<button class=\"btn btn-sm btn-primary btn-rastreo\" onclick=\"abrirRastreo(this)\" data-id-credito=\"' + t.id_credito + '\" data-id-ticket=\"' + (t.id_ticket) + '\" data-asignado=\"' + attrEsc(t.asignado_nombre) + '\" data-creador-nombre=\"' + attrEsc(t.creador_nombre) + '\" data-fecha-creacion=\"' + attrEsc(t.fecha_creacion) + '\" data-categoria-gestion=\"' + attrEsc((t.categoria_gestion || 'sabueso')) + '\" title=\"Iniciar rastreo\"><i class=\"fa-solid fa-magnifying-glass-plus\"></i></button>'; if (plazoVencido) { btns += '<button type=\"button\" class=\"btn btn-sm btn-warning btn-dictamen-sistema\" onclick=\"abrirDictamenSistema(' + t.id_ticket + ')\" title=\"Dictamen del sistema\"><i class=\"fa-solid fa-robot\"></i></button>'; } } btns += '<button class=\"btn btn-sm btn-secondary\" onclick=\"cerrarTicketPanel(' + (t.id_ticket) + ')\" title=\"Cerrar ticket\"><i class=\"fa fa-minus\"></i></button><button class=\"btn btn-sm btn-danger\" onclick=\"eliminarTicketPanel(' + (t.id_ticket) + ')\" title=\"Eliminar ticket\"><i class=\"fa fa-trash\"></i></button></div>'; return btns; })(),\n                            _id_ticket: t.id_ticket,\n                            _dictamen_estado: t.dictamen_estado || '',\n                            _dictamen_fecha_visto: t.dictamen_fecha_visto || ''\n                        };\n                        return row;\n                    });\n                    var tabla = $('#tablaTicketsPanel').DataTable();\n                    var paginaAntes = tabla.page.info().page;\n                    tabla.clear().rows.add(datos);\n                    var np = tabla.page.info().pages;\n                    if (paginaAntes >= np) paginaAntes = Math.max(0, np - 1);\n                    var cat = (filtrosPayload.categoria_gestion || '').toLowerCase();\n                    var catLabelsPanel2 = { sabueso: 'Sabueso', plantilla: 'Plantilla', atencion_cliente: 'Atención al cliente', validaciones: 'Validaciones', viaticos: 'Viáticos', aplicaciones_de_pago: 'Aplicaciones de pago', credito_problematico: 'Crédito problemático', aclaracion_credito: 'Aclaración de crédito' };\n                    var catLabelTitulo = cat ? (catLabelsPanel2[cat] || cat) : 'Sabueso';\n                    if ($('#panelAdminTitulo').length) $('#panelAdminTitulo').html('<i class=\"fa-solid ' + panelAdminIconoPorCategoria(cat) + ' me-2 flex-shrink-0\" aria-hidden=\"true\"></i><span class=\"panel-admin-titulo-texto\">Panel Admin – ' + catLabelTitulo + '</span>');\n                    var categoriasSimples = ['viaticos','aplicaciones_de_pago','credito_problematico','aclaracion_credito','plantilla','atencion_cliente','validaciones'];\n                    var esPanelSimple = categoriasSimples.indexOf(cat) !== -1;\n                    tabla.columns([9,10,11]).visible(!esPanelSimple, false);\n                    panelAdminAplicarTitulosColumnas(tabla, cat);\n                    if ($('#btnAbrirConsultaCredito').length) $('#btnAbrirConsultaCredito').toggle(!esPanelSimple);\n                    if ($('#sabuesoPanelEaster').length) $('#sabuesoPanelEaster').toggle(!esPanelSimple);\n                    if ($('#panelAdminFiltrosWrap').length) $('#panelAdminFiltrosWrap').toggle(!esPanelSimple);\n                    if (esPrimeraCarga) {\n                        tabla.one('draw.dt.panelAdminPrimera', function() {\n                            window.panelAdminPrimeraCarga = false;\n                            $('#wrapTablaTicketsPanel').show();\n                            document.body.classList.remove('panel-admin-primer-cargando');\n                            Swal.close();\n                        });\n                    }\n                    tabla.page(paginaAntes).draw(false);\n                    tabla.rows().every(function() {\n                        var d = this.data();\n                        if (d._dictamen_estado === 'enviado_al_gestor') {\n                            $(this.node()).addClass('fila-dictamen-enviado').attr('data-id-ticket', d._id_ticket || '');\n                        }\n                    });\n                    if (typeof actualizarCountdownsDictamen === 'function') actualizarCountdownsDictamen('#tablaTicketsPanel');\n                    $('#tablaTicketsPanel [data-bs-toggle=\"tooltip\"]').tooltip();\n                                    },\n                onError: function() {\n                    if (esPrimeraCarga) { window.panelAdminPrimeraCarga = false; $('#wrapTablaTicketsPanel').show(); document.body.classList.remove('panel-admin-primer-cargando'); Swal.close(); }\n                    var tabla = $('#tablaTicketsPanel').DataTable();\n                    tabla.clear().draw();\n                }\n            });\n        }\n        setInterval(function() { if (window.PANEL_ADMIN_SOLO_CONSULTA_CREDITO) return; if (typeof actualizarCountdownsDictamen === 'function') actualizarCountdownsDictamen('#tablaTicketsPanel'); }, 1000);\n        function abrirRastreo(btn) {\n            var idCredito = parseInt(btn.getAttribute('data-id-credito')||0, 10);\n            var idTicket = parseInt(btn.getAttribute('data-id-ticket')||0, 10);\n            var asignadoNombre = (btn.getAttribute('data-asignado')||'').trim();\n            var creadorNombre = (btn.getAttribute('data-creador-nombre')||'').trim();\n            var fechaCreacionRaw = (btn.getAttribute('data-fecha-creacion')||'').trim();\n            var fechaCreacionDisplay = fechaCreacionRaw ? (new Date(fechaCreacionRaw).toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' }) + ' ' + new Date(fechaCreacionRaw).toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' })) : '—';\n            ticketIdRastreoActual = idTicket || null;\n            if (!idCredito || isNaN(idCredito)) { Swal.fire({ icon: 'warning', title: 'Rastreo', text: 'No hay ID de crédito para este ticket.' }); return; }\n            http.request({\n                endpoint: \"/sabueso/getDatosCredito\",\n                metodo: \"POST\",\n                data: JSON.stringify({ id_credito: idCredito, omitir_fad: !!window.RASTREO_EMBED_ESTADO_CUENTA }),\n                contentType: \"application/json\",\n                processData: false,\n                showLoader: true,\n                onSuccess: function(resp) {\n                    var d = resp.datos || null;\n                    if (!d) { var msg = (resp.mensaje || 'No se encontraron datos para este crédito.'); $('#rastreoTopLeft').html('<div class=\"alert alert-warning mb-0\"><strong>Crédito #' + idCredito + '</strong><br>' + msg + '<br><small>El crédito debe existir en Segundometro u Oferta para ver el rastreo.</small></div>'); $('#rastreoTopRight').html(''); $('#rastreoTickets').html(''); $('#rastreoDireccionesContenido').html(''); idCreditoRastreoActual = idCredito; window.ticketIdRastreoActual = ticketIdRastreoActual; $('#modalRastreoCredito').attr('data-id-ticket', ticketIdRastreoActual || ''); $('#rastreoIdTicketActual').val(ticketIdRastreoActual || '').attr('data-id-ticket', ticketIdRastreoActual || ''); $('#modalRastreoCredito').modal('show'); return; }\n                    var esc = function(s) { var x = (s + '').split('&').join('&amp;').split('<').join('&lt;').split('>').join('&gt;'); return x.split(String.fromCharCode(34)).join('&quot;'); };\n                    var idCred = (d.id_credito || d.Id_credito || '—');\n                    var nombreCompleto = esc(d.Nombre_cliente || d.nombre_completo || '—');\n                    var tel = (d.telefono_referencia1 || d.telefono_referencia2 || '').trim();\n                    var telEsc = tel ? esc(tel) : '—';\n                    var dirMegareporte = (d.Domicilio_Completo && (d.Domicilio_Completo + '').trim()) ? esc(d.Domicilio_Completo) : '—';
                    rastreoTicketInfoBase = '<div class=\"rastreo-ticket-info-col\"><span class=\"text-muted small d-block\">Quién levantó el ticket</span><div class=\"fw-medium\">' + (creadorNombre ? esc(creadorNombre) : '—') + '</div><span class=\"text-muted small d-block mt-1\">Cuando se levantó</span><div class=\"fw-medium\">' + fechaCreacionDisplay + '</div><span class=\"text-muted small d-block mt-1\">Asignado a</span><div id=\"rastreoAsignadoBlock\" class=\"fw-medium\"><span class=\"text-muted\">Cargando...</span></div></div>';\n                    var htmlTicketInfo = rastreoTicketInfoBase;\n                    var htmlTopLeft = '<div><span class=\"text-muted small d-block\">ID crédito</span><div class=\"fw-semibold\">' + idCred + '</div></div><div><span class=\"text-muted small d-block\">Nombre completo</span><div class=\"fw-semibold\">' + nombreCompleto + '</div></div><div><span class=\"text-muted small d-block\">Teléfono cliente</span><div class=\"fw-semibold\">' + telEsc + '</div></div><div><span class=\"text-muted small d-block\">Dirección megareporte</span><div class=\"fw-semibold small\">' + dirMegareporte + '</div></div>';\n                    var dirContenido = (d.Domicilio_Completo && (d.Domicilio_Completo + '').trim()) ? esc(d.Domicilio_Completo) : '<span class=\"text-muted\">No hay direcciones registradas</span>';\n                    var tickets = d.tickets || [];\n                    var ticketActual = tickets.filter(function(tk) { return tk.id_ticket == ticketIdRastreoActual; })[0];\n                    var htmlTickets = '';\n                    if (ticketActual) {\n                        var fCreacion = ticketActual.fecha_creacion ? new Date(ticketActual.fecha_creacion).toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '—';\n                        var fVenc = ticketActual.fecha_vencimiento ? new Date(ticketActual.fecha_vencimiento).toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '—';\n                        var _cg = ((ticketActual.categoria_gestion || btn.getAttribute('data-categoria-gestion') || 'sabueso') + '').toLowerCase().trim();\n                        var _cgl = { sabueso: 'Sabueso', plantilla: 'Plantilla', atencion_cliente: 'Atención al cliente', validaciones: 'Validaciones', viaticos: 'Viáticos', aplicaciones_de_pago: 'Aplicaciones de pago', credito_problematico: 'Crédito problemático', aclaracion_credito: 'Aclaración de crédito' };\n                        var _cgn = _cgl[_cg] || _cg.replace(/_/g, ' ');\n                        var _cgi = panelAdminIconoPorCategoria(_cg);\n                        htmlTickets = '<div class=\"small bg-light rounded p-2 mb-2\"><strong>' + esc(ticketActual.folio || '—') + '</strong> · ' + esc(ticketActual.tipo_nombre || '') + ' · ' + esc(ticketActual.estado_nombre || '') + '<br><span class=\"text-muted small\">Descripción:</span> ' + esc(ticketActual.descripcion_inicial || '—') + '<br>Creación: ' + fCreacion + ' · Venc: ' + fVenc + '</div><div class=\"mt-2 px-1\"><span class=\"badge bg-label-primary small d-inline-flex align-items-center gap-1\"><i class=\"fa-solid ' + _cgi + '\" style=\"font-size:0.7rem;line-height:1\"></i>' + esc(_cgn) + '</span></div>';\n                    } else { var _cg2 = ((btn.getAttribute('data-categoria-gestion') || 'sabueso') + '').toLowerCase().trim();\n                        var _cgl2 = { sabueso: 'Sabueso', plantilla: 'Plantilla', atencion_cliente: 'Atención al cliente', validaciones: 'Validaciones', viaticos: 'Viáticos', aplicaciones_de_pago: 'Aplicaciones de pago', credito_problematico: 'Crédito problemático', aclaracion_credito: 'Aclaración de crédito' };\n                        var _cgn2 = _cgl2[_cg2] || _cg2.replace(/_/g, ' ');\n                        var _cgi2 = panelAdminIconoPorCategoria(_cg2);\n                        htmlTickets = '<div class=\"mb-2\"><span class=\"badge bg-label-primary small d-inline-flex align-items-center gap-1\"><i class=\"fa-solid ' + _cgi2 + '\" style=\"font-size:0.7rem;line-height:1\"></i>' + esc(_cgn2) + '</span></div><span class=\"text-muted small\">Ticket actual (sin detalle adicional).</span>'; }\n                    $('#rastreoTopLeft').html(htmlTopLeft); if(!window.RASTREO_EMBED_ESTADO_CUENTA&&typeof sabuesoAppendInformacionIngresos==='function')sabuesoAppendInformacionIngresos(document.getElementById('rastreoTopLeft'),d,esc); $('#rastreoTopRight').html(htmlTicketInfo);\n                    loadHistorialAsignacionTicket(ticketIdRastreoActual);\n                    $('#rastreoTickets').html(htmlTickets);\n                    $('#rastreoDireccionesContenido').html('<span class=\"text-muted\">Cargando direcciones...</span>');\n                    rastreoDireccionesParaMapa = [];\n                    $('#btnAsignarRastreo').html('<i class=\"fa-solid fa-user-plus me-1\"></i>Asignar...');\n                    idCreditoRastreoActual = idCredito;\n                    rastreoDatosClienteActual = { nombre: (d.Nombre_cliente || d.nombre_completo || '—'), credito: idCred, telefono: (tel || '—'), direccion: (d.Domicilio_Completo || '—') };\n                    var kA = \"sabueso_ia_\" + idCredito + \"_\" + (idTicket || 0) + \"_analizar\"; var kU = \"sabueso_ia_\" + idCredito + \"_ubicaciones\"; var kG = \"sabueso_ia_\" + idCredito + \"_gestiones\";\n                    try { if (typeof localStorage !== \"undefined\") { rastreoUltimoAnalizarIA = localStorage.getItem(kA) || \"\"; rastreoUltimoResumenUbicaciones = localStorage.getItem(kU) || \"\"; rastreoUltimoResumenGestiones = localStorage.getItem(kG) || \"\"; } else { rastreoUltimoAnalizarIA = \"\"; rastreoUltimoResumenUbicaciones = \"\"; rastreoUltimoResumenGestiones = \"\"; } } catch (e) { rastreoUltimoAnalizarIA = \"\"; rastreoUltimoResumenUbicaciones = \"\"; rastreoUltimoResumenGestiones = \"\"; }\n                    if (rastreoUltimoAnalizarIA) { \$(\"#btnLecturaIAAnalizar\").show(); \$(\"#btnBorrarIAAnalizar\").show(); } else { \$(\"#btnLecturaIAAnalizar\").hide(); \$(\"#btnBorrarIAAnalizar\").hide(); }\n                    if (rastreoUltimoResumenUbicaciones) { \$(\"#btnLecturaIAUbicaciones\").show(); \$(\"#btnBorrarIAUbicaciones\").show(); } else { \$(\"#btnLecturaIAUbicaciones\").hide(); \$(\"#btnBorrarIAUbicaciones\").hide(); }\n                    if (rastreoUltimoResumenGestiones) { \$(\"#btnLecturaIAGestiones\").show(); \$(\"#btnBorrarIAGestiones\").show(); } else { \$(\"#btnLecturaIAGestiones\").hide(); \$(\"#btnBorrarIAGestiones\").hide(); }\n                    window.ticketIdRastreoActual = ticketIdRastreoActual; \$(\"#modalRastreoCredito\").attr(\"data-id-ticket\", ticketIdRastreoActual || \"\"); \$(\"#rastreoIdTicketActual\").val(ticketIdRastreoActual || \"\").attr(\"data-id-ticket\", ticketIdRastreoActual || \"\"); \$(\"#modalRastreoCredito\").modal(\"show\");\n                },\n                onError: function(err) {\n                    var errMsg = (typeof err === 'string' ? err : (err && err.mensaje)) || 'No se pudieron cargar los datos del crédito.';\n                    Swal.fire({ icon: 'error', title: 'Rastreo', text: errMsg });\n                }\n            });\n        }\n        function tooltipHistorialAsignacion(estado, historial) {\n            if (estado === 'primera_asignacion') return 'Es la primera asignación de este ticket.';\n            var lineas = ['Historial de asignación (este ticket)'];\n            (historial || []).forEach(function(h) { lineas.push('• ' + (h.persona || '—') + ': ' + (h.duracion_humana || '—')); });\n            if (estado === 'sin_asignar') lineas.push('Actualmente sin persona asignada a este ticket.');\n            return lineas.join('\\n');\n        }\n        function loadHistorialAsignacionTicket(idTicket) {\n            if (!idTicket) return;\n            http.request({ endpoint: '/sabueso/getHistorialAsignacionTicket', metodo: 'POST', data: JSON.stringify({ id_ticket: idTicket }), contentType: 'application/json', processData: false, onSuccess: function(r) {\n                var asignado = r.asignado_actual || null;\n                var estado = r.estado || 'primera_asignacion';\n                var historial = r.historial || [];\n                var tooltipTxt = tooltipHistorialAsignacion(estado, historial);\n                var tooltipEsc = (tooltipTxt + '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/\"/g, '&quot;');\n                var tooltipAttr = tooltipEsc.replace(/\\n/g, '<br>');\n                var html = asignado ? ('<i class=\"fa-solid fa-user-check text-success me-1\"></i>' + (asignado.replace(/&/g, '&amp;').replace(/</g, '&lt;')) + ' <i class=\"fa-solid fa-circle-info ms-1\" role=\"img\" aria-label=\"Historial\" data-bs-toggle=\"tooltip\" data-bs-html=\"true\" data-bs-title=\"' + tooltipAttr + '\"></i>') : ('<span class=\"text-muted\">Sin asignar</span> <i class=\"fa-solid fa-circle-info ms-1\" role=\"img\" aria-label=\"Historial\" data-bs-toggle=\"tooltip\" data-bs-html=\"true\" data-bs-title=\"' + tooltipAttr + '\"></i>');\n                var bloque = $('#rastreoAsignadoBlock');\n                if (bloque.length) bloque.html(html);\n                if (asignado) { if (!$('#rastreoAsignadoBlock').next('.btn').length) $('#rastreoAsignadoBlock').after('<button type=\"button\" class=\"btn btn-sm btn-outline-danger mt-1\" onclick=\"quitarAsignacionRastreo()\" title=\"Quitar asignación\">Quitar asignación</button>'); } else { $('#rastreoAsignadoBlock').next('.btn').remove(); }\n                $('#btnAsignarRastreo').html(asignado ? '<i class=\"fa-solid fa-user-pen me-1\"></i>Reasignar a...' : '<i class=\"fa-solid fa-user-plus me-1\"></i>Asignar...');\n                if (typeof $().tooltip === 'function') { $('#rastreoAsignadoBlock [data-bs-toggle=\"tooltip\"]').tooltip(); }\n            } });\n        }\n        function mostrarAsignarOpciones() {\n            if (!ticketIdRastreoActual) { Swal.fire({ icon: 'warning', title: 'Asignar', text: 'No hay ticket seleccionado.' }); return; }\n            Swal.fire({ title: 'Asignar ticket', text: '¿A quién desea asignar este ticket?', icon: 'question', showDenyButton: true, showCancelButton: true, confirmButtonText: 'Tomar asignación', denyButtonText: 'Asignar a...', cancelButtonText: 'Cancelar' }).then(function(res) {\n                if (res.isConfirmed) asignarTicketA(miUsuarioId);\n                else if (res.isDenied) abrirModalAsignarA();\n            });\n        }\n        function asignarTicketA(idPersona) {\n            if (!ticketIdRastreoActual || !idPersona) return;\n            http.request({ endpoint: \"/sabueso/asignarTicket\", metodo: \"POST\", data: JSON.stringify({ id_ticket: ticketIdRastreoActual, id_persona: idPersona }), contentType: \"application/json\", processData: false, onSuccess: function(r) {\n                Swal.fire({ icon: 'success', title: 'Asignado', text: r.mensaje || 'Ticket asignado.' });\n                $('#modalRastreoCredito, #modalAsignarA').modal('hide');\n                ticketIdRastreoActual = null;\n                getTicketsPanelAdmin();\n            }, onError: function(e) { Swal.fire({ icon: 'error', title: 'Error', text: (e && e.mensaje) || 'No se pudo asignar.' }); } });\n        }\n        function quitarAsignacionRastreo() {\n            if (!ticketIdRastreoActual) return;\n            if (typeof Swal !== 'undefined') {\n                Swal.fire({ title: '¿Quitar asignación?', text: 'El ticket quedará sin persona asignada.', icon: 'question', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#6c757d', confirmButtonText: 'Sí, quitar' }).then(function(res) {\n                    if (!res.isConfirmed) return;\n                    http.request({ endpoint: '/sabueso/quitarAsignacionTicket', metodo: 'POST', data: JSON.stringify({ id_ticket: ticketIdRastreoActual }), contentType: 'application/json', processData: false, onSuccess: function(r) {\n                        if (r.success) { Swal.fire({ icon: 'success', title: 'Listo', text: r.mensaje || 'Asignación quitada.' }); if (ticketIdRastreoActual) loadHistorialAsignacionTicket(ticketIdRastreoActual); getTicketsPanelAdmin(); } else { if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'Error', text: r.mensaje || 'No se pudo quitar.' }); }\n                    }, onError: function(e) { if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'Error', text: (e && e.mensaje) || 'No se pudo quitar.' }); } });\n                });\n            } else {\n                http.request({ endpoint: '/sabueso/quitarAsignacionTicket', metodo: 'POST', data: JSON.stringify({ id_ticket: ticketIdRastreoActual }), contentType: 'application/json', processData: false, onSuccess: function(r) { if (r.success) { if (idCreditoRastreoActual) loadHistorialAsignacion(idCreditoRastreoActual); getTicketsPanelAdmin(); } } });\n            }\n        }\n        function abrirModalAsignarA() {\n            http.request({ endpoint: \"/sabueso/getPersonasSabueso\", metodo: \"POST\", onSuccess: function(resp) {\n                var list = resp.datos || [];\n                var html = list.length ? list.map(function(p) { return '<div class=\"d-flex justify-content-between align-items-center py-2 border-bottom\"><span>' + (p.nombre_completo || p.id) + '</span><button type=\"button\" class=\"btn btn-sm btn-primary\" onclick=\"asignarTicketA(' + p.id + ')\">Asignárselo</button></div>'; }).join('') : '<p class=\"text-muted mb-0\">No hay personas en el departamento Sabueso.</p>';\n                $('#modalAsignarABody').html(html);\n                $('#modalRastreoCredito').modal('hide');\n                $('#modalAsignarA').modal('show');\n            }, onError: function() { Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar la lista.' }); } });\n        }\n        function cerrarTicketPanel(idTicket) {\n            if (!idTicket) return;\n            Swal.fire({ title: '¿Cerrar ticket?', text: 'El ticket se registrará como cerrado y dejará de mostrarse en la lista activa.', icon: 'question', showCancelButton: true, confirmButtonColor: '#fd7e14', cancelButtonColor: '#6c757d', confirmButtonText: 'Sí, cerrar' }).then(function(res) {\n                if (!res.isConfirmed) return;\n                http.request({\n                    endpoint: \"/sabueso/cerrarTicket\",\n                    metodo: \"POST\",\n                    data: JSON.stringify({ id_ticket: idTicket }),\n                    contentType: \"application/json\",\n                    processData: false,\n                    onSuccess: function(resp) {\n                        Swal.fire({ icon: 'success', title: 'Cerrado', text: resp.mensaje || 'Ticket cerrado.' });\n                        getTicketsPanelAdmin();\n                    },\n                    onError: function(err) {\n                        Swal.fire({ icon: 'error', title: 'Error', text: (err && err.mensaje) || 'No se pudo cerrar.' });\n                    }\n                });\n            });\n        }\n        function eliminarTicketPanel(idTicket) {\n            if (!idTicket) return;\n            Swal.fire({ title: '¿Eliminar ticket?', text: 'Esta acción no se puede deshacer.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#6c757d', confirmButtonText: 'Sí, eliminar' }).then(function(res) {\n                if (!res.isConfirmed) return;\n                http.request({\n                    endpoint: \"/sabueso/eliminarTicket\",\n                    metodo: \"POST\",\n                    data: JSON.stringify({ id_ticket: idTicket }),\n                    contentType: \"application/json\",\n                    processData: false,\n                    onSuccess: function(resp) {\n                        Swal.fire({ icon: 'success', title: 'Eliminado', text: resp.mensaje || 'Ticket eliminado.' });\n                        getTicketsPanelAdmin();\n                    },\n                    onError: function(err) {\n                        Swal.fire({ icon: 'error', title: 'Error', text: (err && err.mensaje) || 'No se pudo eliminar.' });\n                    }\n                });\n            });\n        }\n";
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
            if (!ticketIdRastreoActual) { $(\'#rastreoDictamenContenido\').html(\'<span class="text-muted">Seleccione un ticket.</span>\'); $(\'#rastreoDictamenCombo\').val(\'\'); $(\'#rastreoDictamenAmpliadaCombo\').val(\'\'); $(\'#rastreoDictamenDescripcion\').val(\'\'); $(\'#rastreoDictamenDomiciliosWrap, #rastreoDictamenAmpliadaDomiciliosWrap\').empty(); $(\'#btnDictamenEnviarGestor, #btnDictamenAmpliadaEnviarGestor\').prop(\'disabled\', false).html(\'<i class="fa-solid fa-paper-plane me-1"></i>Enviar al gestor\'); $(\'#rastreoDictamenCombo, #rastreoDictamenDescripcion, #rastreoDictamenEvidenciaAdd, #rastreoDictamenAmpliadaEvidenciaAdd\').prop(\'disabled\', false); $(\'.rastreo-seccion-dictamen\').removeClass(\'dictamen-solo-lectura\'); $(\'.rastreo-dictamen-form-ampliada\').removeClass(\'dictamen-solo-lectura\'); if (typeof actualizarDictamenCamposPorTipo === \'function\') actualizarDictamenCamposPorTipo(); return; }
            if (typeof rellenarEvidenciasDictamen === \'function\') rellenarEvidenciasDictamen(ticketIdRastreoActual);
            http.request({ endpoint: "/sabueso/getDictamenActualTicket", metodo: "POST", data: JSON.stringify({ id_ticket: ticketIdRastreoActual }), contentType: "application/json", processData: false, showLoader: false, onSuccess: function(r) {
                var d = (r.success && r.datos) ? r.datos : null;
                var dtTipo = (d && d.tipo) ? d.tipo : \'\';
                var tiposNuevos = { ilocalizable: 1, localizable: 1, dual_zonificacion: 1, falta_intensidad_gestion: 1 };
                if (dtTipo && !tiposNuevos[dtTipo]) {
                    [\'#rastreoDictamenCombo\', \'#rastreoDictamenAmpliadaCombo\'].forEach(function(sel) {
                        var $el = $(sel);
                        if (!$el.find(\'option\').filter(function() { return this.value === dtTipo; }).length) {
                            $el.append($(\'<option/>\').attr(\'value\', dtTipo).text(\'(Anterior) \' + dtTipo));
                        }
                    });
                }
                $(\'#rastreoDictamenCombo\').val(dtTipo);
                $(\'#rastreoDictamenAmpliadaCombo\').val(dtTipo);
                if (typeof rellenarDomiciliosDictamen === \'function\') rellenarDomiciliosDictamen(d && d.descripcion ? d.descripcion : \'\'); else { $(\'#rastreoDictamenDescripcion, #rastreoDictamenAmpliadaDescripcion\').val(d && d.descripcion ? d.descripcion : \'\'); $(\'#rastreoDictamenDomiciliosWrap, #rastreoDictamenAmpliadaDomiciliosWrap\').empty(); }
                if (typeof actualizarDictamenCamposPorTipo === \'function\') actualizarDictamenCamposPorTipo();
                var estado = (d && d.estado) ? d.estado : \'\';
                if (estado === \'enviado_al_gestor\') {
                    $(\'#rastreoDictamenCombo, #rastreoDictamenDescripcion, #rastreoDictamenAmpliadaCombo, #rastreoDictamenAmpliadaDescripcion, #rastreoDictamenEvidenciaAdd, #rastreoDictamenAmpliadaEvidenciaAdd\').prop(\'disabled\', true);
                    $(\'#btnDictamenEnviarGestor, #btnDictamenAmpliadaEnviarGestor\').prop(\'disabled\', true).html(\'<i class="fa-solid fa-check me-1"></i>Dictamen enviado\');
                    $(\'.rastreo-seccion-dictamen\').addClass(\'dictamen-solo-lectura\');
                    $(\'.rastreo-dictamen-form-ampliada\').addClass(\'dictamen-solo-lectura\');
                    var fEnv = (d.fecha_actualizacion ? new Date(d.fecha_actualizacion).toLocaleString(\'es-MX\', { day: \'2-digit\', month: \'2-digit\', year: \'numeric\', hour: \'2-digit\', minute: \'2-digit\' }) : \'—\');
                    $(\'#rastreoDictamenContenido\').html(\'<div class="small"><strong>Dictamen enviado al gestor</strong><br><p class="text-info mb-2 d-flex align-items-center gap-1"><i class="fa-solid fa-clock"></i>Vas a tener 12 horas para visitar al cliente.</p><span class="text-muted">Tipo: \' + (typeof etiquetaTipoDictamenSabueso === \'function\' ? etiquetaTipoDictamenSabueso(d.tipo) : (d.tipo || \'—\')) + \'</span><br><span class="text-muted">Descripción: \' + (function(){ var raw = (d.descripcion || \'\').replace(/</g, \'&lt;\').replace(/>/g, \'&gt;\'); if (!raw.trim() && typeof esTipoDictamenIlocalizable === \'function\' && esTipoDictamenIlocalizable(d.tipo)) return \'— (sin comentarios; ILOCALIZABLE)\'; return raw || \'—\'; })() + \'</span><br><span class="text-muted">Enviado: \' + fEnv + \'</span></div>\');
                } else if (d && (d.tipo || d.descripcion)) {
                    $(\'#rastreoDictamenCombo, #rastreoDictamenDescripcion, #rastreoDictamenAmpliadaCombo, #rastreoDictamenAmpliadaDescripcion, #rastreoDictamenEvidenciaAdd, #rastreoDictamenAmpliadaEvidenciaAdd\').prop(\'disabled\', false);
                    $(\'#btnDictamenEnviarGestor, #btnDictamenAmpliadaEnviarGestor\').prop(\'disabled\', false).html(\'<i class="fa-solid fa-paper-plane me-1"></i>Enviar al gestor\');
                    $(\'.rastreo-seccion-dictamen\').removeClass(\'dictamen-solo-lectura\');
                    $(\'.rastreo-dictamen-form-ampliada\').removeClass(\'dictamen-solo-lectura\');
                    var fAct = (d.fecha_actualizacion ? new Date(d.fecha_actualizacion).toLocaleString(\'es-MX\', { day: \'2-digit\', month: \'2-digit\', hour: \'2-digit\', minute: \'2-digit\' }) : \'—\');
                    $(\'#rastreoDictamenContenido\').html(\'<div class="small text-success"><strong>Borrador guardado</strong> \' + fAct + \'<br><span class="text-muted">Tipo: \' + (typeof etiquetaTipoDictamenSabueso === \'function\' ? etiquetaTipoDictamenSabueso(d.tipo) : (d.tipo || \'—\')) + \'</span><br><span class="text-muted">Descripción: \' + (function(){ var raw = (d.descripcion || \'\').replace(/</g, \'&lt;\').replace(/>/g, \'&gt;\'); if (!raw.trim() && typeof esTipoDictamenIlocalizable === \'function\' && esTipoDictamenIlocalizable(d.tipo)) return \'— (sin comentarios; ILOCALIZABLE)\'; return raw || \'—\'; })() + \'</span></div>\');
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
            http.request({ endpoint: "/sabueso/getEvidenciasTicket", metodo: "POST", data: JSON.stringify({ id_ticket: ticketIdRastreoActual, tipo_origen: "dictamen_sabueso" }), contentType: "application/json", processData: false, onSuccess: function(r) {
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
        function abrirModalDetalleRegistrosUbicacion(indice) {
            var p = (rastreoDireccionesParaMapa && rastreoDireccionesParaMapa[indice]) ? rastreoDireccionesParaMapa[indice] : null;
            if (!p) {
                if (typeof Swal !== \'undefined\') Swal.fire({ icon: \'info\', title: \'Detalle\', text: \'No hay datos de esta ubicación.\' });
                return;
            }
            var fechasRaw = p.fechas || [];
            var visitas = p.cantidad_registros != null ? parseInt(p.cantidad_registros, 10) : 0;
            var fechasParsed = [];
            for (var i = 0; i < fechasRaw.length; i++) {
                var d = new Date(fechasRaw[i]);
                if (!isNaN(d.getTime())) fechasParsed.push(d);
            }
            fechasParsed.sort(function(a, b) { return b.getTime() - a.getTime(); });
            var tituloUbic = \'Ubicación \' + (indice + 1);
            function renderLista(filtradas) {
                if (!filtradas.length) return \'<p class="text-muted mb-0 small">No hay registros en el período seleccionado.</p>\';
                var html = \'<div class="text-start" style="max-height:280px;overflow-y:auto;">\';
                for (var j = 0; j < filtradas.length; j++) {
                    var dt = filtradas[j];
                    var ds = dt.toLocaleDateString(\'es-MX\', { day: \'2-digit\', month: \'2-digit\', year: \'numeric\' });
                    var ts = dt.toLocaleTimeString(\'es-MX\', { hour: \'2-digit\', minute: \'2-digit\', hour12: true });
                    html += \'<div class="small py-1 border-bottom">\' + ds + \' – \' + ts + \'</div>\';
                }
                html += \'</div>\';
                return html;
            }
            function aplicarFiltro() {
                var desde = document.getElementById(\'swal-rastreo-detalle-desde\');
                var hasta = document.getElementById(\'swal-rastreo-detalle-hasta\');
                var body = document.getElementById(\'swal-rastreo-detalle-body\');
                var countEl = document.getElementById(\'swal-rastreo-detalle-count\');
                if (!body) return;
                var d0 = desde && desde.value ? desde.value : \'\';
                var d1 = hasta && hasta.value ? hasta.value : \'\';
                var filtradas = fechasParsed.slice();
                if (d0) {
                    var t0 = new Date(d0 + \'T00:00:00\');
                    filtradas = filtradas.filter(function(d) { return d >= t0; });
                }
                if (d1) {
                    var t1 = new Date(d1 + \'T23:59:59.999\');
                    filtradas = filtradas.filter(function(d) { return d <= t1; });
                }
                body.innerHTML = renderLista(filtradas);
                if (countEl) {
                    var base = visitas;
                    if (fechasParsed.length === base) {
                        countEl.textContent = (d0 || d1) ? (\'Mostrando \' + filtradas.length + \' de \' + base + \' registros\') : (\'Total: \' + base + \' registro(s), orden del más reciente al más antiguo.\');
                    } else {
                        countEl.textContent = \'Registros con fecha: \' + fechasParsed.length + (base ? (\' (agrupación: \' + base + \' visitas)\') : \'\') + (d0 || d1 ? \'; filtrados: \' + filtradas.length : \'\');
                    }
                }
            }
            if (typeof Swal === \'undefined\') return;
            if (!fechasParsed.length) {
                Swal.fire({
                    icon: \'info\',
                    title: tituloUbic + \' – Detalle\',
                    html: \'<p class="small mb-0">No hay fechas individuales en el histórico para esta ubicación (datos antiguos o sin timestamp).</p>\',
                    confirmButtonText: \'Cerrar\'
                });
                return;
            }
            var html = \'<p class="small text-muted mb-2" id="swal-rastreo-detalle-count">Total: \' + visitas + \' registro(s), orden del más reciente al más antiguo.</p>\'
                + \'<p class="small mb-2">Por defecto se muestran los <strong>últimos 7 días</strong> respecto a la apertura más reciente. Vacíe ambas fechas y pulse Aplicar para ver todos.</p>\'
                + \'<div class="d-flex flex-wrap gap-2 align-items-end mb-2">\' +
                \'<div><label class="small d-block">Desde</label><input type="date" id="swal-rastreo-detalle-desde" class="form-control form-control-sm"></div>\' +
                \'<div><label class="small d-block">Hasta</label><input type="date" id="swal-rastreo-detalle-hasta" class="form-control form-control-sm"></div>\' +
                \'<button type="button" class="btn btn-sm btn-primary" id="swal-rastreo-detalle-aplicar">Aplicar filtro</button>\' +
                \'</div>\' +
                \'<div id="swal-rastreo-detalle-body"></div>\';
            Swal.fire({
                title: tituloUbic + \' – Detalle de aperturas\',
                html: html,
                width: 560,
                showConfirmButton: true,
                confirmButtonText: \'Cerrar\',
                didOpen: function() {
                    var inputDesde = document.getElementById(\'swal-rastreo-detalle-desde\');
                    var inputHasta = document.getElementById(\'swal-rastreo-detalle-hasta\');
                    var newest = fechasParsed[0];
                    var oldest = fechasParsed[fechasParsed.length - 1];
                    function inicioDiaLocal(d) {
                        return new Date(d.getFullYear(), d.getMonth(), d.getDate());
                    }
                    function aYmd(d) {
                        var y = d.getFullYear();
                        var m = String(d.getMonth() + 1);
                        if (m.length < 2) m = \'0\' + m;
                        var day = String(d.getDate());
                        if (day.length < 2) day = \'0\' + day;
                        return y + \'-\' + m + \'-\' + day;
                    }
                    var hastaDia = inicioDiaLocal(newest);
                    var desdeDia = new Date(hastaDia.getTime());
                    desdeDia.setDate(desdeDia.getDate() - 6);
                    var oldestDia = inicioDiaLocal(oldest);
                    if (desdeDia.getTime() < oldestDia.getTime()) desdeDia = oldestDia;
                    if (inputHasta) inputHasta.value = aYmd(hastaDia);
                    if (inputDesde) inputDesde.value = aYmd(desdeDia);
                    aplicarFiltro();
                    var btn = document.getElementById(\'swal-rastreo-detalle-aplicar\');
                    if (btn) btn.addEventListener(\'click\', aplicarFiltro);
                    [\'swal-rastreo-detalle-desde\', \'swal-rastreo-detalle-hasta\'].forEach(function(id) {
                        var el = document.getElementById(id);
                        if (el) el.addEventListener(\'change\', aplicarFiltro);
                    });
                }
            });
        }
        $(function() {
            $(\'#rastreoDireccionesContenido\').on(\'click\', \'.rastreo-badge-registros-detalle\', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var indice = parseInt($(this).data(\'indice-registro\'), 10);
                if (isNaN(indice)) return;
                abrirModalDetalleRegistrosUbicacion(indice);
            });
            $(\'#rastreoDireccionesContenido\').on(\'keydown\', \'.rastreo-badge-registros-detalle\', function(e) {
                if (e.key !== \'Enter\' && e.key !== \' \') return;
                e.preventDefault();
                e.stopPropagation();
                $(this).trigger(\'click\');
            });
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
                                var badgeRegistros = \'<span class="\' + badgeCls + \' rastreo-badge-registros-detalle ms-1" role="button" tabindex="0" title="Ver fecha y hora de cada apertura" data-indice-registro="\' + (d.orden - 1) + \'">\' + registroTexto + \'</span>\';
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
                            (function rastreoLazyReverseGeocodeDirs() {
                                var MIN_GAP = 1100;
                                var nextSlot = 0;
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
                                function scheduleFetch(elm) {
                                    var now = Date.now();
                                    var startAt = Math.max(now, nextSlot);
                                    nextSlot = startAt + MIN_GAP;
                                    setTimeout(function() { fetchOne(elm, false); }, startAt - now);
                                }
                                if (typeof IntersectionObserver !== \'undefined\' && nodes.length) {
                                    var io = new IntersectionObserver(function(entries) {
                                        entries.forEach(function(en) {
                                            if (!en.isIntersecting) return;
                                            var el = en.target;
                                            if (el.getAttribute(\'data-rastreo-rev-sched\')) return;
                                            el.setAttribute(\'data-rastreo-rev-sched\', \'1\');
                                            io.unobserve(el);
                                            scheduleFetch(el);
                                        });
                                    }, { root: null, rootMargin: \'120px\', threshold: 0.01 });
                                    for (var i = 0; i < nodes.length; i++) io.observe(nodes[i]);
                                } else {
                                    var delay = 400;
                                    for (var j = 0; j < nodes.length; j++) {
                                        (function(elm, d) { setTimeout(function() { fetchOne(elm, false); }, d); })(nodes[j], delay);
                                        delay += MIN_GAP;
                                    }
                                }
                            })();
                        }).catch(function() {
                            $(\'#rastreoDireccionesContenido\').html(buildDireccionesMaxiHtml([], null));
                            (function rastreoLazyReverseGeocodeDirs() {
                                var MIN_GAP = 1100;
                                var nextSlot = 0;
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
                                function scheduleFetch(elm) {
                                    var now = Date.now();
                                    var startAt = Math.max(now, nextSlot);
                                    nextSlot = startAt + MIN_GAP;
                                    setTimeout(function() { fetchOne(elm, false); }, startAt - now);
                                }
                                if (typeof IntersectionObserver !== \'undefined\' && nodes.length) {
                                    var io = new IntersectionObserver(function(entries) {
                                        entries.forEach(function(en) {
                                            if (!en.isIntersecting) return;
                                            var el = en.target;
                                            if (el.getAttribute(\'data-rastreo-rev-sched\')) return;
                                            el.setAttribute(\'data-rastreo-rev-sched\', \'1\');
                                            io.unobserve(el);
                                            scheduleFetch(el);
                                        });
                                    }, { root: null, rootMargin: \'120px\', threshold: 0.01 });
                                    for (var i = 0; i < nodes.length; i++) io.observe(nodes[i]);
                                } else {
                                    var delay = 400;
                                    for (var j = 0; j < nodes.length; j++) {
                                        (function(elm, d) { setTimeout(function() { fetchOne(elm, false); }, d); })(nodes[j], delay);
                                        delay += MIN_GAP;
                                    }
                                }
                            })();
                        });
                    } else {
                        $(\'#rastreoDireccionesContenido\').removeClass(\'rastreo-contenido-cargando\').html(\'<span class="text-muted">Sin ubicaciones en maxi app para este crédito.</span>\');
                    }
                    $(\'#rastreoDirecciones .rastreo-mapa-wrap\').show();
                    var ptsMapa = rastreoDireccionesParaMapa;
                    requestAnimationFrame(function() {
                        requestAnimationFrame(function() {
                            try { initMapaRastreo(ptsMapa); maybeInitMapaAlternas(); } catch (e) { console.warn(\'Rastreo mapas:\', e); }
                        });
                    });
                }, onError: function() {
                    rastreoDomicilioMegareporte = null; rastreoIndiceCasa = null;
                    rastreoPuntosGeo = [];
                    $(\'#rastreoDireccionesContenido\').removeClass(\'rastreo-contenido-cargando\').html(\'<span class="text-muted">Sin ubicaciones en maxi app para este crédito.</span>\');
                    $(\'#rastreoDireccionesAlternasContenido\').removeClass(\'rastreo-contenido-cargando\').html(\'<span class="text-muted small">No se pudieron cargar las direcciones alternas. Revisa la conexión o intenta de nuevo.</span>\');
                    $(\'#rastreoDirecciones .rastreo-mapa-wrap\').show();
                    $(\'#rastreoDireccionesAlternas .rastreo-mapa-wrap\').show();
                    requestAnimationFrame(function() {
                        requestAnimationFrame(function() {
                            try { initMapaRastreo([]); maybeInitMapaAlternas(); } catch (e) { console.warn(\'Rastreo mapas:\', e); }
                        });
                    });
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
