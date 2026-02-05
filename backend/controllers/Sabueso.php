<?php

namespace Controllers;

use Core\Controller;
use Models\Ticket as TicketDAO;
use Models\Empresa as EmpresaDAO;
use Models\Gestiones as GestionesDAO;
use Models\Ubicacion as UbicacionDAO;

class Sabueso extends Controller
{
    /**
     * Vista principal: tabla de tickets (Sabueso > Ticket).
     */
    public function ticket()
    {
        $usuarioId = (int)($_SESSION['usuario_id'] ?? 0);
        $columnsJson = $this->getColumnsConfig(false);

        $script = <<<SCRIPT
        <script>
        var esAdminTicket = false;
SCRIPT;
        $script .= "\n\n        $(document).ready(function() {\n            configuraTabla(\"#tablaTickets\", {\n                registrosPorPagina: 10,\n                columns: " . $columnsJson['columnsJs'] . "\n            });\n            getTickets();\n            $('#modalLevantarTicket').on('shown.bs.modal', function() { cargarCatalogosTicket(); });\n        });\n\n        function getTickets() {\n            http.request({\n                endpoint: \"/sabueso/getTickets\",\n                metodo: \"POST\",\n                onSuccess: function(resp) {\n                    var datos = (resp.datos || []).map(function(t) {\n                        var fechaCreacion = t.fecha_creacion\n                            ? new Date(t.fecha_creacion).toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' })\n                            : '—';\n                    var fechaVenc = t.fecha_vencimiento\n                            ? new Date(t.fecha_vencimiento).toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' })\n                            : '—';\n                        var prioridadNombre = (t.prioridad_nombre || '').toLowerCase();\n                        var prioridadBadge = '<span class=\"badge bg-label-secondary\">' + (t.prioridad_nombre || '—') + '</span>';\n                        if (prioridadNombre.indexOf('alta') !== -1) prioridadBadge = '<span class=\"badge bg-danger text-white\">' + (t.prioridad_nombre || '—') + '</span>';\n                        else if (prioridadNombre.indexOf('medio') !== -1 || prioridadNombre.indexOf('media') !== -1) prioridadBadge = '<span class=\"badge\" style=\"background-color:#fd7e14;color:#fff;\">' + (t.prioridad_nombre || '—') + '</span>';\n                        else if (prioridadNombre.indexOf('bajo') !== -1 || prioridadNombre.indexOf('baja') !== -1) prioridadBadge = '<span class=\"badge\" style=\"background-color:#ffc107;color:#212529;\">' + (t.prioridad_nombre || '—') + '</span>';\n                        else if (prioridadNombre.indexOf('sin prioridad') !== -1) prioridadBadge = '<span class=\"badge bg-secondary\" style=\"background-color:#6c757d!important;color:#fff;\">' + (t.prioridad_nombre || '—') + '</span>';\n                        var row = {\n                            folio_tipo: '<div class=\"fw-semibold\">' + (t.folio || '—') + '</div><div class=\"small text-muted mt-1\">' + (t.tipo_ticket_nombre || '—') + '</div>',\n                            estado: '<span class=\"badge bg-label-secondary\">' + (t.estado_ticket_nombre || '—') + '</span>',\n                            prioridad: prioridadBadge,\n                            credito: '<small>#' + (t.id_credito != null ? t.id_credito : '—') + '</small>',\n                            fechas: '<div class=\"small d-flex align-items-center gap-1\"><i class=\"fa fa-calendar-plus-o text-muted\" style=\"width: 1rem;\"></i><span>Creación: ' + fechaCreacion + '</span></div><div class=\"small text-muted d-flex align-items-center gap-1 mt-1\"><i class=\"fa fa-calendar-times-o\" style=\"width: 1rem;\"></i><span>Vencimiento: ' + fechaVenc + '</span></div>',\n                            acciones: ''\n                        };\n                        return row;\n                    });\n                    var tabla = $('#tablaTickets').DataTable();\n                    tabla.clear().rows.add(datos).draw();\n                },\n                onError: function() {\n                    var tabla = $('#tablaTickets').DataTable();\n                    tabla.clear().draw();\n                }\n            });\n        }\n\n        var fechaVencimientoClickHandler = null;\n        function abrirModalLevantarTicket() {\n            $('#modal_id_tipo_ticket, #modal_id_prioridad, #modal_id_origen_ticket').val('');\n            $('#modal_id_credito, #modal_descripcion_inicial').val('');\n            clearFechaVencimiento();\n            $('#modalLevantarTicket').modal('show');\n        }\n        function configurarFechaVencimiento() {\n            setTimeout(function() {\n                var oldInput = document.getElementById('modal_fecha_vencimiento');\n                if (!oldInput) return;\n                if (fechaVencimientoClickHandler && oldInput) {\n                    oldInput.removeEventListener('click', fechaVencimientoClickHandler);\n                    fechaVencimientoClickHandler = null;\n                }\n                try {\n                    if (oldInput._flatpickr && typeof oldInput._flatpickr.destroy === 'function') oldInput._flatpickr.destroy();\n                    if (typeof flatpickr !== \"undefined\" && typeof flatpickr.getInstance === \"function\") {\n                        var existing = flatpickr.getInstance(oldInput);\n                        if (existing && typeof existing.destroy === 'function') existing.destroy();\n                    }\n                } catch (e) {}\n                var currentValue = oldInput.value || '';\n                var newInput = document.createElement('input');\n                newInput.type = 'text';\n                newInput.id = 'modal_fecha_vencimiento';\n                newInput.className = 'form-control';\n                newInput.placeholder = 'YYYY-MM-DD';\n                newInput.value = currentValue;\n                newInput.setAttribute('autocomplete', 'off');\n                if (oldInput.parentNode) oldInput.parentNode.replaceChild(newInput, oldInput);\n                setTimeout(function() {\n                    var input = document.getElementById('modal_fecha_vencimiento');\n                    if (!input) return;\n                    var manana = new Date(); manana.setDate(manana.getDate() + 1);\n                    var minStr = manana.getFullYear() + '-' + String(manana.getMonth() + 1).padStart(2, '0') + '-' + String(manana.getDate()).padStart(2, '0');\n                    if (typeof flatpickr !== 'undefined') {\n                        try {\n                            var fp = flatpickr(input, { minDate: manana, dateFormat: 'Y-m-d', allowInput: false, clickOpens: true, defaultDate: null, appendTo: document.body, static: false });\n                            if (fp && fp.calendarContainer) { fp.calendarContainer.style.zIndex = '99999'; }\n                            fechaVencimientoClickHandler = function(e) { e.preventDefault(); e.stopPropagation(); setTimeout(function() { if (fp && typeof fp.open === 'function') fp.open(); if (fp && fp.calendarContainer) { fp.calendarContainer.style.zIndex = '99999'; } }, 10); };\n                            input.addEventListener('click', fechaVencimientoClickHandler);\n                        } catch (err) { input.type = 'date'; input.setAttribute('min', minStr); input.value = ''; }\n                    } else { input.type = 'date'; input.setAttribute('min', minStr); input.value = ''; }\n                }, 50);\n            }, 200);\n        }\n        function clearFechaVencimiento() {\n            var input = document.getElementById('modal_fecha_vencimiento');\n            if (!input) return;\n            try { if (input._flatpickr && typeof input._flatpickr.clear === 'function') input._flatpickr.clear(); else input.value = ''; } catch (e) { input.value = ''; }\n        }\n\n        var datosCreditoActual = null;\n        function buscarCreditoModal() {\n            var id = ($('#buscar_id_credito').val() || '').toString().trim();\n            if (!id || isNaN(parseInt(id, 10))) {\n                Swal.fire({ icon: 'warning', title: 'ID de crédito', text: 'Escriba un ID de crédito numérico y pulse Buscar.' });\n                return;\n            }\n            http.request({\n                endpoint: \"/sabueso/getDatosCredito\",\n                metodo: \"POST\",\n                data: JSON.stringify({ id_credito: parseInt(id, 10) }),\n                contentType: \"application/json\",\n                processData: false,\n                showLoader: true,\n                onSuccess: function(resp) {\n                    datosCreditoActual = resp.datos || null;\n                    $('#buscar_id_credito').val('');\n                    if (!resp.success || !datosCreditoActual) {\n                        var msg = resp.mensaje || 'El ID de crédito no existe o es incorrecto. Verifique el número e intente de nuevo.';\n                        setTimeout(function() { Swal.fire({ icon: 'error', title: 'ID de crédito incorrecto', text: msg }); }, 0);\n                        return;\n                    }\n                    var d = datosCreditoActual;\n                    var html = '<div class=\"credito-modal-list\">';\n                    html += '<div class=\"credito-modal-item\"><i class=\"fa-solid fa-user text-primary me-2\"></i><span class=\"text-muted small\">Nombre</span><div class=\"fw-medium\">' + (d.Nombre_cliente || d.nombre_completo || '—') + '</div></div>';\n                    html += '<div class=\"credito-modal-item\"><i class=\"fa-solid fa-hashtag text-primary me-2\"></i><span class=\"text-muted small\">ID de crédito</span><div class=\"fw-medium\">' + (d.id_credito || d.Id_credito || '—') + '</div></div>';\n                    if (d.Id_cliente) html += '<div class=\"credito-modal-item\"><i class=\"fa-solid fa-id-card text-primary me-2\"></i><span class=\"text-muted small\">ID cliente</span><div class=\"fw-medium\">' + d.Id_cliente + '</div></div>';\n                    html += '<div class=\"credito-modal-item\"><i class=\"fa-solid fa-map-marker-alt text-primary me-2\"></i><span class=\"text-muted small\">Dirección</span><div class=\"fw-medium\">' + (d.Domicilio_Completo || '—') + '</div></div>';\n                    var tel = d.telefono_referencia1 || d.telefono_referencia2 || '';\n                    if (tel) html += '<div class=\"credito-modal-item\"><i class=\"fa-solid fa-phone text-primary me-2\"></i><span class=\"text-muted small\">Teléfono</span><div class=\"fw-medium\">' + tel + '</div></div>';\n                    if (d.correo || d.email) html += '<div class=\"credito-modal-item\"><i class=\"fa-solid fa-envelope text-primary me-2\"></i><span class=\"text-muted small\">Correo</span><div class=\"fw-medium\">' + (d.correo || d.email || '—') + '</div></div>';\n                    var tickets = d.tickets || [];\n                    if (tickets.length > 0) {\n                        html += '<div class=\"credito-modal-item mt-3 pt-3 border-top\"><span class=\"text-muted small d-block mb-2\"><i class=\"fa-solid fa-ticket me-1\"></i>Ticket(s) levantado(s)</span>';\n                        tickets.forEach(function(tk) {\n                            var fCreacion = tk.fecha_creacion ? new Date(tk.fecha_creacion).toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '—';\n                            var fVenc = tk.fecha_vencimiento ? new Date(tk.fecha_vencimiento).toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '—';\n                            html += '<div class=\"small bg-light rounded p-2 mb-2\"><strong>' + (tk.folio || '—') + '</strong> · ' + (tk.tipo_nombre || '') + ' · ' + (tk.estado_nombre || '') + '<br><span class=\"text-muted small\">Descripción:</span> ' + (tk.descripcion_inicial || '—') + '<br>Creación: ' + fCreacion + ' · Venc: ' + fVenc + '</div>';\n                        });\n                        html += '</div>';\n                    }\n                    html += '</div>';\n                    $('#modalDatosCreditoBody').html(html);\n                    $('#modalDatosCredito').modal('show');\n                },\n                onError: function(err) {\n                    datosCreditoActual = null;\n                    $('#buscar_id_credito').val('');\n                    var errMsg = (typeof err === 'string' ? err : (err && err.mensaje)) || 'El ID de crédito no existe o es incorrecto. Verifique el número e intente de nuevo.';\n                    setTimeout(function() { Swal.fire({ icon: 'error', title: 'ID de crédito incorrecto', text: errMsg }); }, 0);\n                }\n            });\n        }\n        function usarCreditoEnTicket() {\n            if (datosCreditoActual && (datosCreditoActual.id_credito || datosCreditoActual.Id_credito)) {\n                var idCred = datosCreditoActual.id_credito || datosCreditoActual.Id_credito;\n                $('#modal_id_credito').val(idCred);\n                $('#modalDatosCredito').modal('hide');\n                $('#modalLevantarTicket').modal('show');\n            }\n        }\n\n        function cargarCatalogosTicket() {\n            http.request({\n                endpoint: \"/sabueso/getCatalogosTicket\",\n                metodo: \"POST\",\n                onSuccess: function(resp) {\n                    var c = resp.datos || {};\n                    var tipos = c.tipos || [], estados = c.estados || [], prioridades = c.prioridades || [], origenes = c.origenes || [];\n                    function options(arr, key) {\n                        var h = '<option value=\"\">Seleccione...</option>';\n                        arr.forEach(function(o) { h += '<option value=\"' + (o.id) + '\">' + (o.nombre || o.id) + '</option>'; });\n                        return h;\n                    }\n                    $('#modal_id_tipo_ticket').html(options(tipos));\n                    $('#modal_id_prioridad').html(options(prioridades));\n                    $('#modal_id_origen_ticket').html(options(origenes));\n                    var origenSistema = origenes.filter(function(o) { return (o.nombre || '').toLowerCase().indexOf('sistema') !== -1; })[0];\n                    if (origenSistema && origenSistema.id) $('#modal_id_origen_ticket').val(origenSistema.id);\n                    else if (origenes.length > 0 && origenes[0].id) $('#modal_id_origen_ticket').val(origenes[0].id);\n                    setTimeout(configurarFechaVencimiento, 150);\n                }\n            });\n        }\n\n        function enviarLevantarTicket() {\n            var payload = {\n                id_tipo_ticket: $('#modal_id_tipo_ticket').val(),\n                id_prioridad: $('#modal_id_prioridad').val(),\n                id_origen_ticket: $('#modal_id_origen_ticket').val(),\n                id_credito: ($('#modal_id_credito').val() || '').toString().trim(),\n                descripcion_inicial: ($('#modal_descripcion_inicial').val() || '').toString().trim(),\n                fecha_vencimiento: ($('#modal_fecha_vencimiento').val() || '').toString().trim()\n            };\n            if (!payload.id_tipo_ticket || !payload.id_prioridad || !payload.id_origen_ticket || !payload.descripcion_inicial) {\n                Swal.fire({ icon: 'warning', title: 'Faltan datos', text: 'Complete tipo, prioridad, origen y descripción.' });\n                return;\n            }\n            if (!payload.id_credito || isNaN(parseInt(payload.id_credito, 10)) || parseInt(payload.id_credito, 10) < 1) {\n                Swal.fire({ icon: 'warning', title: 'ID de crédito obligatorio', text: 'Debe indicar un ID de crédito válido.' });\n                return;\n            }\n            if (!payload.fecha_vencimiento) {\n                Swal.fire({ icon: 'warning', title: 'Fecha de vencimiento obligatoria', text: 'Debe seleccionar la fecha de vencimiento.' });\n                return;\n            }\n            http.request({\n                endpoint: \"/sabueso/getDatosCredito\",\n                metodo: \"POST\",\n                data: JSON.stringify({ id_credito: parseInt(payload.id_credito, 10) }),\n                contentType: \"application/json\",\n                processData: false,\n                showLoader: true,\n                onSuccess: function(respVerif) {\n                    if (!respVerif.datos) {\n                        var msgVerif = respVerif.mensaje || 'El ID de crédito no existe o es incorrecto. No se puede crear el ticket. Verifique el número.';\n                        setTimeout(function() { Swal.fire({ icon: 'error', title: 'ID de crédito incorrecto', text: msgVerif }); }, 0);\n                        return;\n                    }\n                    http.request({\n                        endpoint: \"/sabueso/crearTicket\",\n                        metodo: \"POST\",\n                        data: JSON.stringify(payload),\n                        contentType: \"application/json\",\n                        processData: false,\n                        onSuccess: function(resp) {\n                            $('#modalLevantarTicket').modal('hide');\n                            $('#modal_id_tipo_ticket, #modal_id_prioridad, #modal_id_origen_ticket').val('');\n                            $('#modal_id_credito, #modal_descripcion_inicial').val('');\n                            clearFechaVencimiento();\n                            Swal.fire({ icon: 'success', title: 'Ticket creado', text: resp.mensaje || 'Folio: ' + (resp.datos && resp.datos.folio ? resp.datos.folio : '') });\n                            getTickets();\n                        },\n                        onError: function(err) {\n                            Swal.fire({ icon: 'error', title: 'Error', text: (err && err.mensaje) || 'No se pudo crear el ticket.' });\n                        }\n                    });\n                },\n                onError: function(err) {\n                    var msg = (typeof err === 'string' ? err : (err && err.mensaje)) || 'El ID de crédito no existe o es incorrecto. No se puede crear el ticket. Verifique el número.';\n                    setTimeout(function() { Swal.fire({ icon: 'error', title: 'ID de crédito incorrecto', text: msg }); }, 0);\n                }\n            });\n        }\n\n        function eliminarTicket(idTicket) {\n            if (!idTicket) return;\n            Swal.fire({ title: '¿Eliminar ticket?', text: 'Esta acción no se puede deshacer.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#6c757d', confirmButtonText: 'Sí, eliminar' }).then(function(res) {\n                if (!res.isConfirmed) return;\n                http.request({\n                    endpoint: \"/sabueso/eliminarTicket\",\n                    metodo: \"POST\",\n                    data: JSON.stringify({ id_ticket: idTicket }),\n                    contentType: \"application/json\",\n                    processData: false,\n                    onSuccess: function(resp) {\n                        Swal.fire({ icon: 'success', title: 'Eliminado', text: resp.mensaje || 'Ticket eliminado.' });\n                        getTickets();\n                    },\n                    onError: function(err) {\n                        Swal.fire({ icon: 'error', title: 'Error', text: (err && err.mensaje) || 'No se pudo eliminar.' });\n                    }\n                });\n            });\n        }\n        </script>";

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
        var rastreoMapaLeaflet = null;
        var rastreoMapaGrande = null;
        var googleMapsApiKey = {$googleMapsKeyJs};
        var rastreoDatosClienteActual = { nombre: '', credito: '', telefono: '', direccion: '' };
        var rastreoUltimoResumenUbicaciones = '';
        var rastreoUltimoResumenGestiones = '';
        var rastreoUltimoAnalizarIA = '';
SCRIPT;
        $script .= "\n\n        function attrEsc(s){ if (s==null||s===undefined) return ''; var x=(s+'').split('&').join('&amp;').split('<').join('&lt;'); return x.split('\"').join('&quot;'); }\n        $(document).ready(function() {\n            configuraTabla(\"#tablaTicketsPanel\", {\n                registrosPorPagina: 10,\n                columns: " . $columnsJson['columnsJs'] . "\n            });\n            getTicketsPanelAdmin();\n        });\n\n        function getTicketsPanelAdmin() {\n            http.request({\n                endpoint: \"/sabueso/getTicketsPanelAdmin\",\n                metodo: \"POST\",\n                onSuccess: function(resp) {\n                    var datos = (resp.datos || []).map(function(t) {\n                        var fechaCreacion = t.fecha_creacion ? new Date(t.fecha_creacion).toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '—';\n                        var fechaVenc = t.fecha_vencimiento ? new Date(t.fecha_vencimiento).toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '—';\n                        var prioridadNombre = (t.prioridad_nombre || '').toLowerCase();\n                        var prioridadBadge = '<span class=\"badge bg-label-secondary\">' + (t.prioridad_nombre || '—') + '</span>';\n                        if (prioridadNombre.indexOf('alta') !== -1) prioridadBadge = '<span class=\"badge bg-danger text-white\">' + (t.prioridad_nombre || '—') + '</span>';\n                        else if (prioridadNombre.indexOf('medio') !== -1 || prioridadNombre.indexOf('media') !== -1) prioridadBadge = '<span class=\"badge\" style=\"background-color:#fd7e14;color:#fff;\">' + (t.prioridad_nombre || '—') + '</span>';\n                        else if (prioridadNombre.indexOf('bajo') !== -1 || prioridadNombre.indexOf('baja') !== -1) prioridadBadge = '<span class=\"badge\" style=\"background-color:#ffc107;color:#212529;\">' + (t.prioridad_nombre || '—') + '</span>';\n                        else if (prioridadNombre.indexOf('sin prioridad') !== -1) prioridadBadge = '<span class=\"badge bg-secondary\" style=\"background-color:#6c757d!important;color:#fff;\">' + (t.prioridad_nombre || '—') + '</span>';\n                        var estadoBadge = (t.asignado_nombre && (t.asignado_nombre + '').trim()) ? '<span class=\"badge bg-success text-white\">Asignado</span>' : '<span class=\"badge bg-label-secondary\">Abierto</span>';\n                        var row = {\n                            folio_tipo: '<div class=\"fw-semibold\">' + (t.folio || '—') + '</div><div class=\"small text-muted mt-1\">' + (t.tipo_ticket_nombre || '—') + '</div>',\n                            estado: estadoBadge,\n                            prioridad: prioridadBadge,\n                            credito: '<small>#' + (t.id_credito != null ? t.id_credito : '—') + '</small>',\n                            fechas: '<div class=\"small d-flex align-items-center gap-1\"><i class=\"fa fa-calendar-plus-o text-muted\" style=\"width: 1rem;\"></i><span>Creación: ' + fechaCreacion + '</span></div><div class=\"small text-muted d-flex align-items-center gap-1 mt-1\"><i class=\"fa fa-calendar-times-o\" style=\"width: 1rem;\"></i><span>Vencimiento: ' + fechaVenc + '</span></div>',\n                            creador: '<small class=\"d-flex align-items-center gap-1\"><i class=\"fa fa-user\"></i>' + (t.creador_nombre || '—') + '</small>',\n                            asignado: (t.asignado_nombre && t.asignado_nombre.trim()) ? '<small class=\"d-flex align-items-center gap-1\"><i class=\"fa fa-user-check text-success\"></i>' + t.asignado_nombre + '</small>' : '<span class=\"text-muted\">—</span>',\n                            acciones: '<div class=\"d-flex flex-wrap gap-1 align-items-center\"><button class=\"btn btn-sm btn-primary btn-rastreo\" onclick=\"abrirRastreo(this)\" data-id-credito=\"' + (t.id_credito != null ? t.id_credito : 0) + '\" data-id-ticket=\"' + (t.id_ticket) + '\" data-asignado=\"' + attrEsc(t.asignado_nombre) + '\" data-creador-nombre=\"' + attrEsc(t.creador_nombre) + '\" data-fecha-creacion=\"' + attrEsc(t.fecha_creacion) + '\" title=\"Iniciar rastreo\"><i class=\"fa-solid fa-magnifying-glass-plus\"></i></button><button class=\"btn btn-sm btn-danger\" onclick=\"eliminarTicketPanel(' + (t.id_ticket) + ')\" title=\"Eliminar\"><i class=\"fa fa-trash\"></i></button></div>'\n                        };\n                        return row;\n                    });\n                    var tabla = $('#tablaTicketsPanel').DataTable();\n                    tabla.clear().rows.add(datos).draw();\n                },\n                onError: function() {\n                    var tabla = $('#tablaTicketsPanel').DataTable();\n                    tabla.clear().draw();\n                }\n            });\n        }\n        function abrirRastreo(btn) {\n            var idCredito = parseInt(btn.getAttribute('data-id-credito')||0, 10);\n            var idTicket = parseInt(btn.getAttribute('data-id-ticket')||0, 10);\n            var asignadoNombre = (btn.getAttribute('data-asignado')||'').trim();\n            var creadorNombre = (btn.getAttribute('data-creador-nombre')||'').trim();\n            var fechaCreacionRaw = (btn.getAttribute('data-fecha-creacion')||'').trim();\n            var fechaCreacionDisplay = fechaCreacionRaw ? (new Date(fechaCreacionRaw).toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' }) + ' ' + new Date(fechaCreacionRaw).toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' })) : '—';\n            ticketIdRastreoActual = idTicket || null;\n            if (!idCredito || isNaN(idCredito)) { Swal.fire({ icon: 'warning', title: 'Rastreo', text: 'No hay ID de crédito para este ticket.' }); return; }\n            http.request({\n                endpoint: \"/sabueso/getDatosCredito\",\n                metodo: \"POST\",\n                data: JSON.stringify({ id_credito: idCredito }),\n                contentType: \"application/json\",\n                processData: false,\n                showLoader: true,\n                onSuccess: function(resp) {\n                    var d = resp.datos || null;\n                    if (!d) { var msg = (resp.mensaje || 'No se encontraron datos para este crédito.'); $('#rastreoTopLeft').html('<div class=\"alert alert-warning mb-0\"><strong>Crédito #' + idCredito + '</strong><br>' + msg + '<br><small>El crédito debe existir en Segundometro u Oferta para ver el rastreo.</small></div>'); $('#rastreoTopRight').html(''); $('#rastreoTickets').html(''); $('#rastreoDireccionesContenido').html(''); idCreditoRastreoActual = idCredito; $('#modalRastreoCredito').modal('show'); return; }\n                    var esc = function(s) { var x = (s + '').split('&').join('&amp;').split('<').join('&lt;').split('>').join('&gt;'); return x.split(String.fromCharCode(34)).join('&quot;'); };\n                    var idCred = (d.id_credito || d.Id_credito || '—');\n                    var nombreCompleto = esc(d.Nombre_cliente || d.nombre_completo || '—');\n                    var tel = (d.telefono_referencia1 || d.telefono_referencia2 || '').trim();\n                    var telEsc = tel ? esc(tel) : '—';\n                    var dirMegareporte = (d.Domicilio_Completo && (d.Domicilio_Completo + '').trim()) ? esc(d.Domicilio_Completo) : '—';
                    var htmlTicketInfo = '<div class=\"rastreo-ticket-info-col\"><span class=\"text-muted small d-block\">Quién levantó el ticket</span><div class=\"fw-medium\">' + (creadorNombre ? esc(creadorNombre) : '—') + '</div><span class=\"text-muted small d-block mt-1\">Cuando se levantó</span><div class=\"fw-medium\">' + fechaCreacionDisplay + '</div>'; if (asignadoNombre) htmlTicketInfo += '<span class=\"text-muted small d-block mt-1\">Asignado a</span><div class=\"fw-medium\"><i class=\"fa-solid fa-user-check text-success me-1\"></i>' + esc(asignadoNombre) + '</div>'; htmlTicketInfo += '</div>';\n                    var htmlTopLeft = '<div><span class=\"text-muted small d-block\">ID crédito</span><div class=\"fw-semibold\">' + idCred + '</div></div><div><span class=\"text-muted small d-block\">Nombre completo</span><div class=\"fw-semibold\">' + nombreCompleto + '</div></div><div><span class=\"text-muted small d-block\">Teléfono cliente</span><div class=\"fw-semibold\">' + telEsc + '</div></div><div><span class=\"text-muted small d-block\">Dirección megareporte</span><div class=\"fw-semibold small\">' + dirMegareporte + '</div></div>';\n                    var dirContenido = (d.Domicilio_Completo && (d.Domicilio_Completo + '').trim()) ? esc(d.Domicilio_Completo) : '<span class=\"text-muted\">No hay direcciones registradas</span>';\n                    var tickets = d.tickets || [];\n                    var ticketActual = tickets.filter(function(tk) { return tk.id_ticket == ticketIdRastreoActual; })[0];\n                    var htmlTickets = '';\n                    if (ticketActual) {\n                        var fCreacion = ticketActual.fecha_creacion ? new Date(ticketActual.fecha_creacion).toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '—';\n                        var fVenc = ticketActual.fecha_vencimiento ? new Date(ticketActual.fecha_vencimiento).toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '—';\n                        htmlTickets = '<div class=\"small bg-light rounded p-2 mb-2\"><strong>' + esc(ticketActual.folio || '—') + '</strong> · ' + esc(ticketActual.tipo_nombre || '') + ' · ' + esc(ticketActual.estado_nombre || '') + '<br><span class=\"text-muted small\">Descripción:</span> ' + esc(ticketActual.descripcion_inicial || '—') + '<br>Creación: ' + fCreacion + ' · Venc: ' + fVenc + '</div>';\n                    } else { htmlTickets = '<span class=\"text-muted small\">Ticket actual (sin detalle adicional).</span>'; }\n                    $('#rastreoTopLeft').html(htmlTopLeft); $('#rastreoTopRight').html(htmlTicketInfo);\n                    $('#rastreoTickets').html(htmlTickets);\n                    $('#rastreoDireccionesContenido').html('<span class=\"text-muted\">Cargando direcciones...</span>');\n                    rastreoDireccionesParaMapa = [];\n                    $('#btnAsignarRastreo').html((asignadoNombre ? '<i class=\"fa-solid fa-user-pen me-1\"></i>Reasignar a...' : '<i class=\"fa-solid fa-user-plus me-1\"></i>Asignar...'));\n                    idCreditoRastreoActual = idCredito;\n                    rastreoDatosClienteActual = { nombre: (d.Nombre_cliente || d.nombre_completo || '—'), credito: idCred, telefono: (tel || '—'), direccion: (d.Domicilio_Completo || '—') };\n                    var kA = \"sabueso_ia_\" + idCredito + \"_\" + (idTicket || 0) + \"_analizar\"; var kU = \"sabueso_ia_\" + idCredito + \"_ubicaciones\"; var kG = \"sabueso_ia_\" + idCredito + \"_gestiones\";\n                    try { if (typeof localStorage !== \"undefined\") { rastreoUltimoAnalizarIA = localStorage.getItem(kA) || \"\"; rastreoUltimoResumenUbicaciones = localStorage.getItem(kU) || \"\"; rastreoUltimoResumenGestiones = localStorage.getItem(kG) || \"\"; } else { rastreoUltimoAnalizarIA = \"\"; rastreoUltimoResumenUbicaciones = \"\"; rastreoUltimoResumenGestiones = \"\"; } } catch (e) { rastreoUltimoAnalizarIA = \"\"; rastreoUltimoResumenUbicaciones = \"\"; rastreoUltimoResumenGestiones = \"\"; }\n                    if (rastreoUltimoAnalizarIA) { \$(\"#btnLecturaIAAnalizar\").show(); \$(\"#btnBorrarIAAnalizar\").show(); } else { \$(\"#btnLecturaIAAnalizar\").hide(); \$(\"#btnBorrarIAAnalizar\").hide(); }\n                    if (rastreoUltimoResumenUbicaciones) { \$(\"#btnLecturaIAUbicaciones\").show(); \$(\"#btnBorrarIAUbicaciones\").show(); } else { \$(\"#btnLecturaIAUbicaciones\").hide(); \$(\"#btnBorrarIAUbicaciones\").hide(); }\n                    if (rastreoUltimoResumenGestiones) { \$(\"#btnLecturaIAGestiones\").show(); \$(\"#btnBorrarIAGestiones\").show(); } else { \$(\"#btnLecturaIAGestiones\").hide(); \$(\"#btnBorrarIAGestiones\").hide(); }\n                    \$(\"#modalRastreoCredito\").modal(\"show\");\n                },\n                onError: function(err) {\n                    var errMsg = (typeof err === 'string' ? err : (err && err.mensaje)) || 'No se pudieron cargar los datos del crédito.';\n                    Swal.fire({ icon: 'error', title: 'Rastreo', text: errMsg });\n                }\n            });\n        }\n        function mostrarAsignarOpciones() {\n            if (!ticketIdRastreoActual) { Swal.fire({ icon: 'warning', title: 'Asignar', text: 'No hay ticket seleccionado.' }); return; }\n            Swal.fire({ title: 'Asignar ticket', text: '¿A quién desea asignar este ticket?', icon: 'question', showDenyButton: true, showCancelButton: true, confirmButtonText: 'Tomar asignación', denyButtonText: 'Asignar a...', cancelButtonText: 'Cancelar' }).then(function(res) {\n                if (res.isConfirmed) asignarTicketA(miUsuarioId);\n                else if (res.isDenied) abrirModalAsignarA();\n            });\n        }\n        function asignarTicketA(idPersona) {\n            if (!ticketIdRastreoActual || !idPersona) return;\n            http.request({ endpoint: \"/sabueso/asignarTicket\", metodo: \"POST\", data: JSON.stringify({ id_ticket: ticketIdRastreoActual, id_persona: idPersona }), contentType: \"application/json\", processData: false, onSuccess: function(r) {\n                Swal.fire({ icon: 'success', title: 'Asignado', text: r.mensaje || 'Ticket asignado.' });\n                $('#modalRastreoCredito, #modalAsignarA').modal('hide');\n                ticketIdRastreoActual = null;\n                getTicketsPanelAdmin();\n            }, onError: function(e) { Swal.fire({ icon: 'error', title: 'Error', text: (e && e.mensaje) || 'No se pudo asignar.' }); } });\n        }\n        function abrirModalAsignarA() {\n            http.request({ endpoint: \"/sabueso/getPersonasSabueso\", metodo: \"POST\", onSuccess: function(resp) {\n                var list = resp.datos || [];\n                var html = list.length ? list.map(function(p) { return '<div class=\"d-flex justify-content-between align-items-center py-2 border-bottom\"><span>' + (p.nombre_completo || p.id) + '</span><button type=\"button\" class=\"btn btn-sm btn-primary\" onclick=\"asignarTicketA(' + p.id + ')\">Asignárselo</button></div>'; }).join('') : '<p class=\"text-muted mb-0\">No hay personas en el departamento Sabueso.</p>';\n                $('#modalAsignarABody').html(html);\n                $('#modalRastreoCredito').modal('hide');\n                $('#modalAsignarA').modal('show');\n            }, onError: function() { Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar la lista.' }); } });\n        }\n        function eliminarTicketPanel(idTicket) {\n            if (!idTicket) return;\n            Swal.fire({ title: '¿Eliminar ticket?', text: 'Esta acción no se puede deshacer.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#6c757d', confirmButtonText: 'Sí, eliminar' }).then(function(res) {\n                if (!res.isConfirmed) return;\n                http.request({\n                    endpoint: \"/sabueso/eliminarTicket\",\n                    metodo: \"POST\",\n                    data: JSON.stringify({ id_ticket: idTicket }),\n                    contentType: \"application/json\",\n                    processData: false,\n                    onSuccess: function(resp) {\n                        Swal.fire({ icon: 'success', title: 'Eliminado', text: resp.mensaje || 'Ticket eliminado.' });\n                        getTicketsPanelAdmin();\n                    },\n                    onError: function(err) {\n                        Swal.fire({ icon: 'error', title: 'Error', text: (err && err.mensaje) || 'No se pudo eliminar.' });\n                    }\n                });\n            });\n        }\n        </script>";
        $evidenciasScript = 'var miUsuarioId = ' . (int)$usuarioId . '; var miPersonaId = ' . (int)$personaId . '; var miUsuarioNombre = ' . json_encode($usuarioNombre ?? '', JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ';
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
                var html = list.length ? list.map(function(m) { var f = m.fecha_creacion ? new Date(m.fecha_creacion).toLocaleString(\'es-MX\', { day: \'2-digit\', month: \'2-digit\', hour: \'2-digit\', minute: \'2-digit\' }) : \'\'; var msg = (m.mensaje || \'\').replace(/</g, \'&lt;\').replace(/>/g, \'&gt;\'); var mine = (m.id_persona && m.id_persona == miPersonaId); var cls = \'bitacora-msg\' + (mine ? \' bitacora-msg-mine\' : \'\'); return \'<div class="\' + cls + \'"><div class="bitacora-avatar">\' + initials(m.persona_nombre) + \'</div><div class="bitacora-msg-body"><div class="bitacora-bubble"><div class="bitacora-msg-header"><strong>\' + (m.persona_nombre || \'—\') + \'</strong> \' + f + \'</div>\' + msg + \'</div></div></div>\'; }).join(\'\') : \'<span class="text-muted">Sin mensajes.</span>\';
                $(\'#rastreoBitacoraContenido\').html(html);
                var el = document.getElementById(\'rastreoBitacoraContenido\'); if (el) el.scrollTop = el.scrollHeight;
            } });
        }
        function cargarGestionesRastreo() {
            if (!idCreditoRastreoActual) { $(\'#rastreoGestionesContenido\').html(\'<span class="text-muted">Seleccione un crédito.</span>\'); return; }
            http.request({ endpoint: "/sabueso/getGestionesCredito", metodo: "POST", data: JSON.stringify({ id_credito: idCreditoRastreoActual }), contentType: "application/json", processData: false, onSuccess: function(r) {
                var list = (r.datos || []);
                function esc(s) { if (s==null||s===undefined) return \'\'; return (s+\'\').replace(/&/g, \'&amp;\').replace(/</g, \'&lt;\').replace(/>/g, \'&gt;\').replace(/"/g, \'&quot;\'); }
                function v(s) { var x = (s==null||s===undefined||s===\'\') ? \'—\' : s; return esc(x); }
                var html = \'\';
                list.forEach(function(g) {
                    var fecha = (g.fecha_dispositivo || g.fecha_hora || \'\').toString().substring(0, 16);
                    html += \'<div class="gestion-card">\';
                    html += \'<div class="gestion-meta"><span class="gestion-app">\' + v(g.app) + \'</span>\' + (fecha ? \'<span class="gestion-fecha">\' + fecha.replace(\'T\', \' \') + \'</span>\' : \'\') + \'</div>\';
                    if (g.medio_contactacion_ccc || g.dictamen_ccc || g.medio_contactacion_campo || g.dictamen_campo) {
                        html += \'<div class="gestion-row"><span class="gestion-label">Contactación</span><span class="gestion-val">CCC: \' + v(g.medio_contactacion_ccc) + \' · \' + v(g.dictamen_ccc) + \' | Campo: \' + v(g.medio_contactacion_campo) + \' · \' + v(g.dictamen_campo) + \'</span></div>\';
                    }
                    html += \'<div class="gestion-row"><span class="gestion-label">Promesa</span><span class="gestion-val">\' + v(g.promesa_pago) + \'</span></div>\';
                    if (g.porque_atraso_pago) html += \'<div class="gestion-row"><span class="gestion-label">Motivo atraso</span><span class="gestion-val">\' + v(g.porque_atraso_pago) + \'</span></div>\';
                    if (g.comentarios_generales) html += \'<div class="gestion-comentarios">\' + v(g.comentarios_generales) + \'</div>\';
                    html += \'</div>\';
                });
                $(\'#rastreoGestionesContenido\').html(html || \'<span class="text-muted">Sin gestiones para este crédito.</span>\');
            } });
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
            var i, e, comAttr;
            for (i = lista.length - 1; i >= 0; i--) {
                e = lista[i];
                if (e && e.url) {
                    comAttr = (e.comentario || \'\').replace(/&/g, \'&amp;\').replace(/"/g, \'&quot;\').replace(/</g, \'&lt;\').replace(/[\r\n]+/g, \' \');
                    html += \'<div class="col-6"><div class="evidencia-slot" data-slot="\' + (lista.length - i) + \'" data-id="\' + (e.id || \'\') + \'" data-comentario="\' + comAttr + \'" title="Clic para ver o eliminar"><img src="\' + (e.url || \'\') + \'" alt="Evidencia"></div></div>\';
                }
            }
            $(\'#rastreoEvidenciasSlots\').html(html);
            $(\'#rastreoEvidenciasSlots .evidencia-slot\').off(\'click\').on(\'click\', function() {
                var id = ($(this).attr(\'data-id\') || \'\').trim();
                var slot = parseInt($(this).attr(\'data-slot\') || 0, 10);
                if (id) {
                    evidenciaModalId = parseInt(id, 10); evidenciaModalSlot = null;
                    var comentario = ($(this).attr(\'data-comentario\') || \'\').replace(/&amp;/g, \'&\').replace(/&quot;/g, \'"\').replace(/&lt;/g, \'<\');
                    var comentarioHtml = comentario ? \'<p class="text-muted small mt-2 mb-0 text-start evidencia-comentario-texto"><strong>Comentario:</strong> \' + comentario.replace(/</g, \'&lt;\').replace(/>/g, \'&gt;\') + \'</p>\' : \'\';
                    $(\'#modalEvidenciaRastreoBody\').html(\'<img src="/sabueso/verEvidencia?id=\' + id + \'" class="img-fluid" alt="Evidencia">\' + comentarioHtml);
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
                            addressesOrPuntos.forEach(function(p, i) {
                                var lat = parseFloat(p.latitud || p.lat), lon = parseFloat(p.longitud || p.lng);
                                if (isNaN(lat) || isNaN(lon)) return;
                                hasPoints = true;
                                var pos = { lat: lat, lng: lon };
                                bounds.extend(pos);
                                var visitas = p.cantidad_registros || 1;
                                var isFirst = (i === 0);
                                var color = isFirst ? \'#ef4444\' : (p.punto_de_interes ? \'#3b82f6\' : \'#9ca3af\');
                                var marker = new google.maps.Marker({ position: pos, map: rastreoMapaLeaflet, title: (p.punto_de_interes ? \'Punto de interés \' : \'Menos frecuente \') + (i + 1) + \' — \' + visitas + \' visitas\' });
                                new google.maps.Circle({ map: rastreoMapaLeaflet, center: pos, radius: isFirst ? 80 : (40 + visitas * 5), fillColor: color, fillOpacity: 0.35, strokeColor: color, strokeWeight: 2 });
                                var dirTexto = (p.texto || \'\').trim();
                                var datosHtml = \'<strong>Cliente:</strong> \' + (rastreoDatosClienteActual.nombre || \'—\') + \'<br><strong>Crédito:</strong> \' + (rastreoDatosClienteActual.credito || \'—\') + \'<br><strong>Teléfono:</strong> \' + (rastreoDatosClienteActual.telefono || \'—\') + \'<br><strong>Ubicación:</strong> \' + (dirTexto || \'Obteniendo dirección...\') + \'<br><strong>Visitas:</strong> \' + visitas + \'<br><strong>Tipo:</strong> \' + (p.punto_de_interes ? \'Punto de interés\' : \'Menos frecuente\');
                                var infow = new google.maps.InfoWindow({ content: datosHtml });
                                if (!dirTexto && typeof google.maps.Geocoder !== \'undefined\') {
                                    var geocoder = new google.maps.Geocoder();
                                    geocoder.geocode({ location: pos }, function(results, status) {
                                        if (status === \'OK\' && results[0] && infow) {
                                            var addr = (results[0].formatted_address || \'\').replace(/</g, \'&lt;\');
                                            var html = \'<strong>Cliente:</strong> \' + (rastreoDatosClienteActual.nombre || \'—\') + \'<br><strong>Crédito:</strong> \' + (rastreoDatosClienteActual.credito || \'—\') + \'<br><strong>Teléfono:</strong> \' + (rastreoDatosClienteActual.telefono || \'—\') + \'<br><strong>Ubicación:</strong> \' + addr + \'<br><strong>Visitas:</strong> \' + visitas + \'<br><strong>Tipo:</strong> \' + (p.punto_de_interes ? \'Punto de interés\' : \'Menos frecuente\');
                                            infow.setContent(html);
                                        }
                                    });
                                }
                                (function(m, w) { m.addListener(\'click\', function() { w.open(rastreoMapaLeaflet, m); }); })(marker, infow);
                            });
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
                L.tileLayer(\'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}.png\', { attribution: \'&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>\', subdomains: \'abcd\', maxZoom: 20 }).addTo(rastreoMapaLeaflet);
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
            } catch (e) { rastreoMapaLeaflet = null; }
        }
        function initMapaRastreoGrande(addressesOrPuntos) {
            var cont = document.getElementById(\'rastreoMapaGrandeContenedor\');
            if (!cont) return;
            if (rastreoMapaGrande) { if (typeof rastreoMapaGrande.remove === \'function\') rastreoMapaGrande.remove(); rastreoMapaGrande = null; }
            if (googleMapsApiKey && googleMapsApiKey.length > 0 && typeof google !== \'undefined\' && google.maps) {
                rastreoMapaGrande = new google.maps.Map(cont, { center: { lat: 19.43, lng: -99.13 }, zoom: 10, mapTypeControl: true, streetViewControl: true, fullscreenControl: true, zoomControl: true });
                var bounds = new google.maps.LatLngBounds();
                var hasPoints = false;
                if (addressesOrPuntos && addressesOrPuntos.length) {
                    var esPuntos = addressesOrPuntos[0] && (addressesOrPuntos[0].latitud !== undefined || addressesOrPuntos[0].lat !== undefined);
                    if (esPuntos) {
                        addressesOrPuntos.forEach(function(p, i) {
                            var lat = parseFloat(p.latitud || p.lat), lon = parseFloat(p.longitud || p.lng);
                            if (isNaN(lat) || isNaN(lon)) return;
                            hasPoints = true;
                            var pos = { lat: lat, lng: lon };
                            bounds.extend(pos);
                            var visitas = p.cantidad_registros || 1;
                            var isFirst = (i === 0);
                            var color = isFirst ? \'#ef4444\' : (p.punto_de_interes ? \'#3b82f6\' : \'#9ca3af\');
                            var marker = new google.maps.Marker({ position: pos, map: rastreoMapaGrande, title: (p.punto_de_interes ? \'Punto de interés \' : \'Menos frecuente \') + (i + 1) + \' — \' + visitas + \' visitas\' });
                            new google.maps.Circle({ map: rastreoMapaGrande, center: pos, radius: isFirst ? 80 : (40 + visitas * 5), fillColor: color, fillOpacity: 0.35, strokeColor: color, strokeWeight: 2 });
                            var dirTexto = (p.texto || \'\').trim();
                            var datosHtml = \'<strong>Cliente:</strong> \' + (rastreoDatosClienteActual.nombre || \'—\') + \'<br><strong>Crédito:</strong> \' + (rastreoDatosClienteActual.credito || \'—\') + \'<br><strong>Teléfono:</strong> \' + (rastreoDatosClienteActual.telefono || \'—\') + \'<br><strong>Ubicación:</strong> \' + (dirTexto || \'Obteniendo dirección...\') + \'<br><strong>Visitas:</strong> \' + visitas + \'<br><strong>Tipo:</strong> \' + (p.punto_de_interes ? \'Punto de interés\' : \'Menos frecuente\');
                            var infow = new google.maps.InfoWindow({ content: datosHtml });
                            if (!dirTexto && typeof google.maps.Geocoder !== \'undefined\') {
                                var geocoder = new google.maps.Geocoder();
                                geocoder.geocode({ location: pos }, function(results, status) {
                                    if (status === \'OK\' && results[0] && infow) {
                                        var addr = (results[0].formatted_address || \'\').replace(/</g, \'&lt;\');
                                        var html = \'<strong>Cliente:</strong> \' + (rastreoDatosClienteActual.nombre || \'—\') + \'<br><strong>Crédito:</strong> \' + (rastreoDatosClienteActual.credito || \'—\') + \'<br><strong>Teléfono:</strong> \' + (rastreoDatosClienteActual.telefono || \'—\') + \'<br><strong>Ubicación:</strong> \' + addr + \'<br><strong>Visitas:</strong> \' + visitas + \'<br><strong>Tipo:</strong> \' + (p.punto_de_interes ? \'Punto de interés\' : \'Menos frecuente\');
                                        infow.setContent(html);
                                    }
                                });
                            }
                            (function(m, w) { m.addListener(\'click\', function() { w.open(rastreoMapaGrande, m); }); })(marker, infow);
                        });
                        if (hasPoints) rastreoMapaGrande.fitBounds(bounds, 50);
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
                L.tileLayer(\'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}.png\', { attribution: \'&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>\', subdomains: \'abcd\', maxZoom: 20 }).addTo(rastreoMapaGrande);
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
            $(\'#rastreoMapaLeaflet\').on(\'click\', function() { $(\'#modalMapaGrande\').modal(\'show\'); });
            $(\'#modalMapaGrande\').on(\'shown.bs.modal\', function() {
                initMapaRastreoGrande(rastreoDireccionesParaMapa);
            });
            $(\'#modalMapaGrande\').on(\'hidden.bs.modal\', function() {
                if (rastreoMapaGrande) { if (typeof rastreoMapaGrande.remove === \'function\') rastreoMapaGrande.remove(); rastreoMapaGrande = null; }
            });
            $(\'#modalRastreoCredito\').on(\'shown.bs.modal\', function() {
                cargarChatRastreo(); cargarEvidenciasRastreo(); cargarGestionesRastreo();
                $(\'#rastreoResumenIAGestionesContenido\').empty().hide();
                $(\'#rastreoAnalizarIAContenido\').empty();
                http.request({ endpoint: \'/sabueso/getUbicacionesCredito\', metodo: \'POST\', data: JSON.stringify({ id_credito: idCreditoRastreoActual }), contentType: \'application/json\', processData: false, onSuccess: function(r) {
                    $(\'#rastreoResumenIAContenido\').empty();
                    rastreoDireccionesParaMapa = (r.puntos_mapa && r.puntos_mapa.length) ? r.puntos_mapa : [];
                    if (r.success && r.direcciones_resumen && r.direcciones_resumen.length) {
                        var html = r.direcciones_resumen.map(function(d) {
                            var visitas = d.cantidad_registros != null ? d.cantidad_registros : 0;
                            var badgeCls = d.punto_de_interes ? \'badge bg-primary\' : \'badge bg-secondary\';
                            var badgeVisitas = \'<span class="\' + badgeCls + \' ms-1">\' + visitas + \' visitas</span>\';
                            var etiqueta = (d.texto || \'\').trim() || \'Ubicación\';
                            return \'<div class="small mb-2 d-flex align-items-start gap-1"><span class="text-muted">📍 Ubicación \' + d.orden + \':</span> <span class="direccion-linea">\' + etiqueta + \' — <span class="direccion-value" data-lat="\' + d.lat + \'" data-lng="\' + d.lng + \'">Obteniendo dirección...</span></span> \' + badgeVisitas + \'</div>\';
                        }).join(\'\');
                        $(\'#rastreoDireccionesContenido\').html(html);
                        (function throttleReverseGeocode() {
                            var delay = 0;
                            $(\'#rastreoDireccionesContenido .direccion-value[data-lat][data-lng]\').each(function() {
                                var el = this; var lat = $(el).data(\'lat\'); var lng = $(el).data(\'lng\');
                                delay += 1100;
                                (function(elm, la, ln, d) {
                                    setTimeout(function() {
                                        fetch(\'https://nominatim.openstreetmap.org/reverse?lat=\' + la + \'&lon=\' + ln + \'&format=json\', { headers: { \'Accept\': \'application/json\', \'User-Agent\': \'SpartaLedger/1.0 (cobranza)\' } }).then(function(r) { return r.json(); }).then(function(data) { $(elm).text(data.display_name || \'Sin dirección\'); }).catch(function() { $(elm).text(\'Sin dirección\'); });
                                    }, d);
                                })(el, lat, lng, delay);
                            });
                        })();
                    } else {
                        $(\'#rastreoDireccionesContenido\').html(\'<span class="text-muted">Sin ubicaciones en maxi app para este crédito.</span>\');
                    }
                    initMapaRastreo(rastreoDireccionesParaMapa);
                }, onError: function() {
                    $(\'#rastreoDireccionesContenido\').html(\'<span class="text-muted">Sin ubicaciones en maxi app para este crédito.</span>\');
                    initMapaRastreo([]);
                } });
            });
            $(\'#modalRastreoCredito\').on(\'hidden.bs.modal\', function() {
                if (rastreoMapaLeaflet) { if (typeof rastreoMapaLeaflet.remove === \'function\') rastreoMapaLeaflet.remove(); rastreoMapaLeaflet = null; }
            });
            $(\'#inputEvidenciaRastreo\').on(\'change\', function() {
                var f = this.files && this.files[0];
                if (!f || !ticketIdRastreoActual) return;
                if (evidenciaPreviewObjectUrl) URL.revokeObjectURL(evidenciaPreviewObjectUrl);
                evidenciaPreviewObjectUrl = URL.createObjectURL(f);
                var maxCom = 300;
                $(\'#modalEvidenciaRastreoBody\').html(\'<div class="mb-2"><img src="\' + evidenciaPreviewObjectUrl + \'" class="img-fluid rounded" alt="Vista previa" style="max-height: 280px;"></div><div class="mb-2 text-start"><label class="form-label small mb-1" style="color: #ffffff !important;">Comentario (opcional)</label><textarea class="form-control form-control-sm" id="evidenciaComentarioModal" rows="2" placeholder="Comentario para esta evidencia..." maxlength="\' + maxCom + \'" style="color: #ffffff !important; background-color: #4a5568; border-color: #718096;"></textarea><span class="evidencia-comentario-counter d-block small mt-1" id="evidenciaComentarioCounter">0/\' + maxCom + \'</span></div><button type="button" class="btn btn-sm btn-primary" id="btnEvidenciaGuardarModal"><i class="fa-solid fa-save me-1"></i>Guardar evidencia</button>\');
                $(\'#modalEvidenciaEliminar\').hide(); $(\'#modalEvidenciaRastreo\').modal(\'show\');
                $(\'#evidenciaComentarioModal\').on(\'input\', function() { var n = ($(this).val() || \'\').length; $(\'#evidenciaComentarioCounter\').text(n + \'/\' + maxCom); });
                $(\'#btnEvidenciaGuardarModal\').off(\'click\').on(\'click\', function() {
                    var fd = new FormData();
                    fd.append(\'id_ticket\', ticketIdRastreoActual); fd.append(\'evidencia\', f);
                    var comentario = ($(\'#evidenciaComentarioModal\').val() || \'\').trim(); if (comentario) fd.append(\'comentario\', comentario);
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
            $(\'#btnAnalizarRastreo\').on(\'click\', function() {
                var btn = $(\'#btnAnalizarRastreo\'); var txt = $(\'#btnAnalizarRastreoText\');
                if (btn.prop(\'disabled\')) return;
                if (!idCreditoRastreoActual) { Swal.fire({ icon: \'warning\', title: \'Analizar IA\', text: \'No hay crédito seleccionado.\' }); return; }
                txt.text(\'Analizando...\'); btn.prop(\'disabled\', true);
                $(\'#rastreoAnalizarIAContenido\').html(\'<p class="text-muted mb-0"><span class="spinner-border spinner-border-sm me-2"></span>Analizando con IA...</p>\').show();
                $(\'#modalPrediccionIABody\').html(\'<p class="text-muted mb-0"><span class="spinner-border spinner-border-sm me-2"></span>Generando predicción...</p>\');
                http.request({ endpoint: \'/sabueso/analizarIA\', metodo: \'POST\', data: JSON.stringify({ id_credito: idCreditoRastreoActual, id_ticket: ticketIdRastreoActual || 0 }), contentType: \'application/json\', processData: false, onSuccess: function(r) {
                    if (r.success && r.texto) {
                        rastreoUltimoAnalizarIA = r.texto;
                        try { if (typeof localStorage !== \'undefined\') localStorage.setItem(\'sabueso_ia_\' + idCreditoRastreoActual + \'_\' + (ticketIdRastreoActual || 0) + \'_analizar\', r.texto); } catch (e) {}
                        $(\'#btnLecturaIAAnalizar, #btnBorrarIAAnalizar\').show();
                        $(\'#rastreoAnalizarIAContenido\').empty();
                        $(\'#modalPrediccionIABody\').html(formatGeminiText(r.texto));
                        $(\'#modalPrediccionIA\').modal(\'show\');
                    } else {
                        $(\'#rastreoAnalizarIAContenido\').empty();
                        $(\'#modalPrediccionIABody\').html(\'<p class="text-danger mb-0">\' + (r.mensaje || \'No se obtuvo predicción.\').replace(/</g, \'&lt;\') + \'</p>\');
                        $(\'#modalPrediccionIA\').modal(\'show\');
                    }
                    txt.text(\'Analizar\'); btn.prop(\'disabled\', false);
                }, onError: function(e) {
                    var msg = (typeof e === \'string\' ? e : (e && e.mensaje)) || \'No se pudo conectar con el servicio de IA.\';
                    $(\'#rastreoAnalizarIAContenido\').empty();
                    $(\'#modalPrediccionIABody\').html(\'<p class="text-danger mb-0">\' + String(msg).replace(/</g, \'&lt;\') + \'</p>\');
                    $(\'#modalPrediccionIA\').modal(\'show\');
                    txt.text(\'Analizar\'); btn.prop(\'disabled\', false);
                    Swal.fire({ icon: \'error\', title: \'Analizar IA\', text: msg });
                } });
            });
            $(\'#btnLecturaIAAnalizar\').on(\'click\', function() {
                if (rastreoUltimoAnalizarIA) {
                    $(\'#modalPrediccionIALabel\').html(\'<i class="fa-solid fa-wand-magic-sparkles me-2"></i>Predicción IA – Cómo localizar al acreditado\');
                    $(\'#modalPrediccionIABody\').html(formatGeminiText(rastreoUltimoAnalizarIA));
                    $(\'#modalPrediccionIA\').modal(\'show\');
                } else { Swal.fire({ icon: \'info\', title: \'Lectura de IA\', text: \'Primero ejecute Analizar con IA.\' }); }
            });
            $(\'#btnResumirUbicacionesIA\').on(\'click\', function() {
                var btn = $(\'#btnResumirUbicacionesIA\');
                if (btn.prop(\'disabled\') || !idCreditoRastreoActual) return;
                btn.prop(\'disabled\', true);
                var cont = $(\'#rastreoResumenIAContenido\');
                cont.html(\'<span class="spinner-border spinner-border-sm me-2"></span>Resumiendo ubicaciones con IA...\').removeClass(\'text-danger\').addClass(\'text-muted\').show();
                http.request({ endpoint: \'/sabueso/resumirUbicacionesIA\', metodo: \'POST\', data: JSON.stringify({ id_credito: idCreditoRastreoActual }), contentType: \'application/json\', processData: false, showLoader: false, onSuccess: function(r) {
                    try {
                        if (r && r.success && r.texto && (r.texto + \'\').trim()) {
                            var txt = formatGeminiText(r.texto);
                            rastreoUltimoResumenUbicaciones = r.texto;
                            try { if (typeof localStorage !== \'undefined\') localStorage.setItem(\'sabueso_ia_\' + idCreditoRastreoActual + \'_ubicaciones\', r.texto); } catch (e) {}
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
                    $(\'#modalLecturaIABody\').html(formatGeminiText(rastreoUltimoResumenUbicaciones));
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
                try { if (typeof localStorage !== \'undefined\') localStorage.removeItem(\'sabueso_ia_\' + idCreditoRastreoActual + \'_\' + (ticketIdRastreoActual || 0) + \'_analizar\'); } catch (e) {}
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
        });';
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
            ['data' => 'folio_tipo', 'title' => 'Folio / Tipo'],
            ['data' => 'estado', 'title' => 'Estado'],
            ['data' => 'prioridad', 'title' => 'Prioridad'],
            ['data' => 'credito', 'title' => 'Crédito'],
            ['data' => 'fechas', 'title' => 'Fechas'],
        ];
        if ($esAdmin) {
            $base[] = ['data' => 'creador', 'title' => 'Quién levantó'];
            $base[] = ['data' => 'asignado', 'title' => 'Asignado a'];
        }
        $base[] = ['data' => 'acciones', 'title' => 'Acciones', 'orderable' => false];

        return [
            'esAdminJs'  => $esAdmin ? 'true' : 'false',
            'columnsJs'  => json_encode($base, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT | JSON_HEX_APOS),
        ];
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

        $idOrigenWhatsApp = TicketDAO::getIdOrigenWhatsApp();
        $idPersonaBot = TicketDAO::getIdPersonaBotWhatsApp();
        if ($idOrigenWhatsApp < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'No está configurado el origen "WhatsApp". Ejecute backend/sql/ticket_whatsapp_setup.sql']);
            return;
        }
        if ($idPersonaBot < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'No está configurado el usuario "Bot WhatsApp". Ejecute backend/sql/ticket_whatsapp_setup.sql']);
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
     * API: eliminar ticket (borrado físico en BD).
     */
    public function eliminarTicket()
    {
        $raw = file_get_contents('php://input');
        $datos = json_decode($raw, true) ?: [];
        $idTicket = (int)($datos['id_ticket'] ?? 0);
        if ($idTicket < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'ID de ticket inválido.']);
            return;
        }
        $resultado = TicketDAO::eliminar($idTicket);
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
        $todo['tickets'] = TicketDAO::getTicketsPorIdCredito($idCredito);
        self::respuestaJSON(['success' => true, 'mensaje' => 'OK', 'datos' => $todo]);
    }

    /**
     * API: histórico de gestiones por id_credito (Contactación y dictamen, Promesas y comentarios).
     * Para el panel de rastreo solo se devuelven las dos últimas gestiones.
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
            $gestiones = array_slice($gestiones, -2);
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
        self::respuestaJSON([
            'success' => $resultado['success'] ?? false,
            'mensaje' => $resultado['mensaje'] ?? ''
        ]);
    }

    /**
     * API: ubicaciones filtradas por id_credito (idCliente desde segundometro, tabla ubicacion en AWS).
     * Devuelve direcciones_resumen (para lista del modal) y puntos_mapa (lat/lng para Leaflet).
     */
    public function getUbicacionesCredito()
    {
        $raw = file_get_contents('php://input');
        $datos = json_decode($raw, true) ?: [];
        $idCredito = isset($datos['id_credito']) && $datos['id_credito'] !== '' ? (int)$datos['id_credito'] : 0;
        if ($idCredito < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'ID de crédito requerido.', 'direcciones_resumen' => [], 'puntos_mapa' => []]);
            return;
        }
        try {
            $resultado = UbicacionDAO::getUbicacionesFiltradasPorIdCredito($idCredito);
            self::respuestaJSON([
                'success' => $resultado['success'] ?? true,
                'mensaje' => $resultado['mensaje'] ?? '',
                'id_cliente' => $resultado['id_cliente'] ?? null,
                'direcciones_resumen' => $resultado['direcciones_resumen'] ?? [],
                'puntos_mapa' => $resultado['puntos_mapa'] ?? [],
            ]);
        } catch (\Exception $e) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Error al obtener ubicaciones.', 'direcciones_resumen' => [], 'puntos_mapa' => []]);
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
            return ['success' => false, 'texto' => '', 'mensaje' => 'No está configurada GEMINI_API_KEY en config.php.'];
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
     * API: análisis con IA (Google Gemini). Recibe id_credito e id_ticket, reúne contexto y devuelve predicción.
     * Solo usuarios con permiso al Panel Admin (módulo 19) pueden usar esta función.
     */
    public function analizarIA()
    {
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

        $contexto = $this->construirContextoAnalizarIA($idCredito, $idTicket);

        $promptSistema = 'Actúa como un Auditor Forense de Cobranza de élite. Tu objetivo es encontrar la verdad oculta cruzando todos los datos (GPS, pagos, fotos, bitácora). NO seas breve. Sé explícito, detallado y narrativo. Tu análisis debe ser lo suficientemente profundo para que el gestor no tenga dudas. Usa formato Markdown (negritas con **texto**) para resaltar lo importante.';
        $promptUsuarioTexto = "Analiza este caso y genera un INFORME DE INTELIGENCIA ESTRATÉGICA (sin límites de extensión):

**1. 📊 PROBABILIDAD REAL DE RECUPERACIÓN**
[Barra visual] (XX%)
*Análisis de Solvencia y Voluntad:* (Explica detalladamente tu razonamiento. Cruza el historial de pagos con las excusas recientes. ¿Tienen dinero y no quieren pagar, o realmente están quebrados?).

**2. 🔎 PERFIL PSICOLÓGICO Y CONDUCTUAL**
(Analiza al deudor y al gestor. ¿El deudor es un mentiroso compulsivo, una víctima de circunstancias o un evasor profesional? ¿El gestor está haciendo su trabajo o simula visitas? Cita fechas y comentarios específicos de la bitácora).

**3. 🎯 LA JUGADA MAESTRA (Estrategia de Localización)**
(Instrucción precisa y detallada. ¿Dónde ir? ¿A qué hora exacta? ¿Qué decirle? Basa esto en los patrones de GPS y horas de contacto exitoso previas).

**4. ⚠️ ALERTAS DE FRAUDE O ANOMALÍAS**
(Extiéndete aquí si las fotos se ven falsas, si las coordenadas no coinciden con la dirección, o si el gestor repite la misma excusa).

Contexto del caso:
" . $contexto;

        $parts = [['text' => $promptUsuarioTexto]];

        if ($idTicket > 0) {
            $evid = TicketDAO::getEvidenciasPorTicket($idTicket);
            $listaEvid = isset($evid['datos']) && is_array($evid['datos']) ? array_slice($evid['datos'], -12) : [];
            $baseUploads = __DIR__ . '/../uploads/';
            foreach ($listaEvid as $e) {
                $ruta = isset($e['ruta_archivo']) ? trim($e['ruta_archivo']) : '';
                if ($ruta === '') continue;
                $path = $baseUploads . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $ruta);
                if (!is_file($path)) continue;
                $data = @file_get_contents($path);
                if ($data === false || strlen($data) > 4 * 1024 * 1024) continue;
                $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path);
                if (strpos($mime, 'image/') !== 0) continue;
                $parts[] = [
                    'inlineData' => [
                        'mimeType' => $mime,
                        'data' => base64_encode($data),
                    ],
                ];
            }
            if (count($parts) > 1) {
                $parts[] = ['text' => "\n\nAUDITORÍA DE EVIDENCIA VISUAL (IMPORTANTE): Mira las fotos adjuntas. ¿Son fotos reales de una visita o parecen descargadas/capturas de pantalla? ¿Coincide la fachada en todas? ¿Se ve nomenclatura? Si detectas fraude del gestor (fotos negras, borrosas a propósito), denúncialo en la sección de ALERTAS."];
            }
        }

        $resultado = $this->llamarGemini($promptSistema, $parts, 8192);
        if (!$resultado['success']) {
            self::respuestaJSON(['success' => false, 'mensaje' => $resultado['mensaje'], 'texto' => $resultado['texto']]);
            return;
        }
        self::respuestaJSON(['success' => true, 'mensaje' => 'OK', 'texto' => $resultado['texto']]);
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
                $lineas[] = "\n=== BITÁCORA / COMENTARIOS DEL TICKET (" . count($listaChat) . " mensajes) ===";
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
                    $lineas[] = '  - Comentario: ' . ($e['comentario'] ?? '—') . ' (fecha: ' . ($e['fecha_subida'] ?? '') . ')';
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
     * API: resumir solo ubicaciones con IA (Gemini). Recibe id_credito, devuelve texto corto (resumen + orden de visita).
     * Solo usuarios con permiso al Panel Admin (módulo 19) pueden usar esta función.
     */
    public function resumirUbicacionesIA()
    {
        $modulos = $_SESSION['modulos'] ?? [];
        if (!is_array($modulos) || !in_array(19, $modulos)) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'No tiene permiso para usar Resumir ubicaciones con IA.', 'texto' => '']);
            return;
        }
        $raw = file_get_contents('php://input');
        $datos = json_decode($raw, true) ?: [];
        $idCredito = isset($datos['id_credito']) && $datos['id_credito'] !== '' ? (int)$datos['id_credito'] : 0;
        if ($idCredito < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'ID de crédito requerido.', 'texto' => '']);
            return;
        }

        $contexto = $this->construirContextoSoloUbicaciones($idCredito);
        $promptSistema = 'Eres un experto en inteligencia geoespacial y rastreo de personas.';
        $promptUsuario = "Analiza minuciosamente los siguientes puntos GPS y frecuencias de visita.\nContexto:\n" . $contexto . "\n\nOBJETIVO: CRONOGRAMA DE LOCALIZACIÓN.\n\nNo hagas un resumen general. Quiero que detectes:\n1. **La Madriguera:** ¿Dónde pernocta realmente? (Analiza puntos nocturnos).\n2. **La Rutina Laboral:** ¿Dónde pasa las horas de oficina?\n3. **Puntos de Fuga:** ¿A dónde va los fines de semana?\n\nEntrega una lista de horarios y lugares recomendados para emboscar al deudor con alta probabilidad de éxito. Sé extenso en tu deducción. Usa **negritas** para resaltar.";

        $resultado = $this->llamarGemini($promptSistema, $promptUsuario, 4096);
        if (!$resultado['success']) {
            self::respuestaJSON(['success' => false, 'mensaje' => $resultado['mensaje'], 'texto' => $resultado['texto']]);
            return;
        }
        self::respuestaJSON(['success' => true, 'mensaje' => 'OK', 'texto' => $resultado['texto']]);
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
     * API: subir evidencia (imagen). POST id_ticket + file (evidencia).
     */
    public function subirEvidenciaTicket()
    {
        $idTicket = (int)($_POST['id_ticket'] ?? 0);
        $idPersona = (int)($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        if ($idTicket < 1 || $idPersona < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Datos inválidos.']);
            return;
        }
        if (empty($_FILES['evidencia']) || $_FILES['evidencia']['error'] !== UPLOAD_ERR_OK) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'No se recibió una imagen válida.']);
            return;
        }
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($_FILES['evidencia']['tmp_name']);
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($mime, $allowed, true)) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Solo se permiten imágenes (JPEG, PNG, GIF, WebP).']);
            return;
        }
        $dir = __DIR__ . '/../uploads/sabueso_evidencias';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $ext = pathinfo($_FILES['evidencia']['name'], PATHINFO_EXTENSION) ?: 'jpg';
        $nombreArchivo = 'ev_' . $idTicket . '_' . $idPersona . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . preg_replace('/[^a-z0-9]/i', '', $ext);
        $rutaCompleta = $dir . '/' . $nombreArchivo;
        if (!move_uploaded_file($_FILES['evidencia']['tmp_name'], $rutaCompleta)) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Error al guardar el archivo.']);
            return;
        }
        $rutaRelativa = 'sabueso_evidencias/' . $nombreArchivo;
        $comentario = isset($_POST['comentario']) ? trim((string)$_POST['comentario']) : null;
        $resultado = TicketDAO::guardarEvidencia($idTicket, $idPersona, $rutaRelativa, $_FILES['evidencia']['name'], $comentario);
        $idEvidencia = isset($resultado['datos']['id']) ? (int)$resultado['datos']['id'] : null;
        self::respuestaJSON([
            'success' => $resultado['success'] ?? false,
            'mensaje' => $resultado['mensaje'] ?? '',
            'datos' => ['id' => $idEvidencia, 'url' => '/sabueso/verEvidencia?id=' . $idEvidencia]
        ]);
    }

    /**
     * API: eliminar evidencia.
     */
    public function eliminarEvidenciaTicket()
    {
        $raw = file_get_contents('php://input');
        $datos = json_decode($raw, true) ?: [];
        $idEvidencia = (int)($datos['id_evidencia'] ?? 0);
        if ($idEvidencia < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'ID de evidencia requerido.']);
            return;
        }
        $row = TicketDAO::getEvidenciaPorId($idEvidencia);
        if ($row && !empty($row['ruta_archivo'])) {
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
        readfile($path);
        exit;
    }
}
