<?php

namespace Controllers;

use Core\Controller;
use Models\Ticket as TicketDAO;
use Models\Empresa as EmpresaDAO;

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
        $columnsJson = $this->getColumnsConfig(true);
        $script = <<<SCRIPT
        <script>
        var esAdminTicket = true;
SCRIPT;
        $script .= "\n\n        $(document).ready(function() {\n            configuraTabla(\"#tablaTicketsPanel\", {\n                registrosPorPagina: 10,\n                columns: " . $columnsJson['columnsJs'] . "\n            });\n            getTicketsPanelAdmin();\n        });\n\n        function getTicketsPanelAdmin() {\n            http.request({\n                endpoint: \"/sabueso/getTicketsPanelAdmin\",\n                metodo: \"POST\",\n                onSuccess: function(resp) {\n                    var datos = (resp.datos || []).map(function(t) {\n                        var fechaCreacion = t.fecha_creacion ? new Date(t.fecha_creacion).toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '—';\n                        var fechaVenc = t.fecha_vencimiento ? new Date(t.fecha_vencimiento).toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '—';\n                        var prioridadNombre = (t.prioridad_nombre || '').toLowerCase();\n                        var prioridadBadge = '<span class=\"badge bg-label-secondary\">' + (t.prioridad_nombre || '—') + '</span>';\n                        if (prioridadNombre.indexOf('alta') !== -1) prioridadBadge = '<span class=\"badge bg-danger text-white\">' + (t.prioridad_nombre || '—') + '</span>';\n                        else if (prioridadNombre.indexOf('medio') !== -1 || prioridadNombre.indexOf('media') !== -1) prioridadBadge = '<span class=\"badge\" style=\"background-color:#fd7e14;color:#fff;\">' + (t.prioridad_nombre || '—') + '</span>';\n                        else if (prioridadNombre.indexOf('bajo') !== -1 || prioridadNombre.indexOf('baja') !== -1) prioridadBadge = '<span class=\"badge\" style=\"background-color:#ffc107;color:#212529;\">' + (t.prioridad_nombre || '—') + '</span>';\n                        else if (prioridadNombre.indexOf('sin prioridad') !== -1) prioridadBadge = '<span class=\"badge bg-secondary\" style=\"background-color:#6c757d!important;color:#fff;\">' + (t.prioridad_nombre || '—') + '</span>';\n                        var row = {\n                            folio_tipo: '<div class=\"fw-semibold\">' + (t.folio || '—') + '</div><div class=\"small text-muted mt-1\">' + (t.tipo_ticket_nombre || '—') + '</div>',\n                            estado: '<span class=\"badge bg-label-secondary\">' + (t.estado_ticket_nombre || '—') + '</span>',\n                            prioridad: prioridadBadge,\n                            credito: '<small>#' + (t.id_credito != null ? t.id_credito : '—') + '</small>',\n                            fechas: '<div class=\"small d-flex align-items-center gap-1\"><i class=\"fa fa-calendar-plus-o text-muted\" style=\"width: 1rem;\"></i><span>Creación: ' + fechaCreacion + '</span></div><div class=\"small text-muted d-flex align-items-center gap-1 mt-1\"><i class=\"fa fa-calendar-times-o\" style=\"width: 1rem;\"></i><span>Vencimiento: ' + fechaVenc + '</span></div>',\n                            creador: '<small class=\"d-flex align-items-center gap-1\"><i class=\"fa fa-user\"></i>' + (t.creador_nombre || '—') + '</small>',\n                            acciones: '<button class=\"btn btn-sm btn-primary me-1\" onclick=\"abrirRastreo(' + (t.id_credito != null ? t.id_credito : 0) + ')\" title=\"Iniciar rastreo\"><i class=\"fa-solid fa-magnifying-glass-plus\"></i></button><button class=\"btn btn-sm btn-danger\" onclick=\"eliminarTicketPanel(' + (t.id_ticket) + ')\" title=\"Eliminar\"><i class=\"fa fa-trash\"></i></button>'\n                        };\n                        return row;\n                    });\n                    var tabla = $('#tablaTicketsPanel').DataTable();\n                    tabla.clear().rows.add(datos).draw();\n                },\n                onError: function() {\n                    var tabla = $('#tablaTicketsPanel').DataTable();\n                    tabla.clear().draw();\n                }\n            });\n        }\n        function abrirRastreo(idCredito) {\n            if (!idCredito || isNaN(parseInt(idCredito, 10))) { Swal.fire({ icon: 'warning', title: 'Rastreo', text: 'No hay ID de crédito para este ticket.' }); return; }\n            http.request({\n                endpoint: \"/sabueso/getDatosCredito\",\n                metodo: \"POST\",\n                data: JSON.stringify({ id_credito: parseInt(idCredito, 10) }),\n                contentType: \"application/json\",\n                processData: false,\n                showLoader: true,\n                onSuccess: function(resp) {\n                    var d = resp.datos || null;\n                    if (!d) { Swal.fire({ icon: 'info', title: 'Rastreo', text: resp.mensaje || 'No se encontraron datos para este crédito.' }); return; }\n                    var html = '<div class=\"credito-modal-list\">';\n                    html += '<div class=\"credito-modal-item\"><i class=\"fa-solid fa-user text-primary me-2\"></i><span class=\"text-muted small\">Nombre</span><div class=\"fw-medium\">' + (d.Nombre_cliente || d.nombre_completo || '—') + '</div></div>';\n                    html += '<div class=\"credito-modal-item\"><i class=\"fa-solid fa-hashtag text-primary me-2\"></i><span class=\"text-muted small\">ID de crédito</span><div class=\"fw-medium\">' + (d.id_credito || d.Id_credito || '—') + '</div></div>';\n                    if (d.Id_cliente) html += '<div class=\"credito-modal-item\"><i class=\"fa-solid fa-id-card text-primary me-2\"></i><span class=\"text-muted small\">ID cliente</span><div class=\"fw-medium\">' + d.Id_cliente + '</div></div>';\n                    html += '<div class=\"credito-modal-item\"><i class=\"fa-solid fa-map-marker-alt text-primary me-2\"></i><span class=\"text-muted small\">Dirección</span><div class=\"fw-medium\">' + (d.Domicilio_Completo || '—') + '</div></div>';\n                    var tel = d.telefono_referencia1 || d.telefono_referencia2 || '';\n                    if (tel) html += '<div class=\"credito-modal-item\"><i class=\"fa-solid fa-phone text-primary me-2\"></i><span class=\"text-muted small\">Teléfono</span><div class=\"fw-medium\">' + tel + '</div></div>';\n                    if (d.correo || d.email) html += '<div class=\"credito-modal-item\"><i class=\"fa-solid fa-envelope text-primary me-2\"></i><span class=\"text-muted small\">Correo</span><div class=\"fw-medium\">' + (d.correo || d.email || '—') + '</div></div>';\n                    var tickets = d.tickets || [];\n                    if (tickets.length > 0) {\n                        html += '<div class=\"credito-modal-item mt-3 pt-3 border-top\"><span class=\"text-muted small d-block mb-2\"><i class=\"fa-solid fa-ticket me-1\"></i>Ticket(s) levantado(s)</span>';\n                        tickets.forEach(function(tk) {\n                            var fCreacion = tk.fecha_creacion ? new Date(tk.fecha_creacion).toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '—';\n                            var fVenc = tk.fecha_vencimiento ? new Date(tk.fecha_vencimiento).toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '—';\n                            html += '<div class=\"small bg-light rounded p-2 mb-2\"><strong>' + (tk.folio || '—') + '</strong> · ' + (tk.tipo_nombre || '') + ' · ' + (tk.estado_nombre || '') + '<br><span class=\"text-muted small\">Descripción:</span> ' + (tk.descripcion_inicial || '—') + '<br>Creación: ' + fCreacion + ' · Venc: ' + fVenc + '</div>';\n                        });\n                        html += '</div>';\n                    }\n                    html += '</div>';\n                    $('#modalRastreoCreditoBody').html(html);\n                    $('#modalRastreoCredito').modal('show');\n                },\n                onError: function(err) {\n                    var errMsg = (typeof err === 'string' ? err : (err && err.mensaje)) || 'No se pudieron cargar los datos del crédito.';\n                    Swal.fire({ icon: 'error', title: 'Rastreo', text: errMsg });\n                }\n            });\n        }\n        function eliminarTicketPanel(idTicket) {\n            if (!idTicket) return;\n            Swal.fire({ title: '¿Eliminar ticket?', text: 'Esta acción no se puede deshacer.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#6c757d', confirmButtonText: 'Sí, eliminar' }).then(function(res) {\n                if (!res.isConfirmed) return;\n                http.request({\n                    endpoint: \"/sabueso/eliminarTicket\",\n                    metodo: \"POST\",\n                    data: JSON.stringify({ id_ticket: idTicket }),\n                    contentType: \"application/json\",\n                    processData: false,\n                    onSuccess: function(resp) {\n                        Swal.fire({ icon: 'success', title: 'Eliminado', text: resp.mensaje || 'Ticket eliminado.' });\n                        getTicketsPanelAdmin();\n                    },\n                    onError: function(err) {\n                        Swal.fire({ icon: 'error', title: 'Error', text: (err && err.mensaje) || 'No se pudo eliminar.' });\n                    }\n                });\n            });\n        }\n        </script>";
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
        }
        $base[] = ['data' => 'acciones', 'title' => 'Acciones', 'orderable' => false];

        return [
            'esAdminJs'  => $esAdmin ? 'true' : 'false',
            'columnsJs'  => json_encode($base),
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
}
