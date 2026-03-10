/**
 * Script para Sabueso > Ticket. Requiere: window.sabuesoTicketColumns (array de columnas DataTables)
 */
(function() {
    var esAdminTicket = false;
    var apiBase = (function(){ var p = window.location.pathname || ''; var i = p.indexOf('/sabueso'); return i !== -1 ? p.substring(0, i) : ''; })();
    window.abrirEvidenciaDictamenGrande = function(src) {
        if (!src) return;
        var $m = $('#modalVerEvidenciaDictamenTicket');
        var $img = $('#modalVerEvidenciaDictamenTicketImg');
        if ($m.length && $img.length) { $img.attr('src', src); $m.modal('show'); }
    };
    $('#modalDetalleDictamen, #modalVerEvidenciaDictamenTicket').on('hidden.bs.modal', function() {
        setTimeout(function() {
            var open = document.querySelectorAll('.modal.show');
            if (open.length === 0) {
                document.querySelectorAll('.modal-backdrop').forEach(function(b) { b.remove(); });
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            }
        }, 50);
    });

    function actualizarCountdownsDictamen(selector) {
        var sel = selector || '#tablaTickets, #tablaTicketsPanel';
        $(sel + ' .dictamen-countdown[data-fecha-envio]').each(function() {
            var el = this;
            var f = $(el).attr('data-fecha-envio');
            if (!f) return;
            var envio = new Date(f);
            var fin = new Date(envio.getTime() + 12 * 60 * 60 * 1000);
            var now = new Date();
            var ms = fin - now;
            var txt = '-';
            var txtCorto = '-';
            var expired = ms <= 0;
            if (ms > 0) {
                var h = Math.floor(ms / 3600000);
                var m = Math.floor((ms % 3600000) / 60000);
                txt = 'Tiempo restante: ' + h + 'h ' + m + 'm';
                txtCorto = h + 'h ' + m + 'm';
            } else {
                txt = 'Plazo vencido';
                txtCorto = 'Plazo vencido';
            }
            $(el).attr('title', txt).attr('data-bs-title', txt).toggleClass('text-danger', expired);
            var $txt = $(el).find('.dictamen-countdown-text');
            if ($txt.length) $txt.text(txtCorto).toggleClass('text-danger', expired);
        });
    }

    $(document).ready(function() {
        var columns = window.sabuesoTicketColumns || [];
        configuraTabla("#tablaTickets", {
            registrosPorPagina: 10,
            order: [[1, 'desc']],
            columns: columns
        });
        getTickets();
        setInterval(function() {
            if (typeof actualizarCountdownsDictamen === 'function') actualizarCountdownsDictamen('#tablaTickets');
        }, 1000);
        $('#modalLevantarTicket').on('shown.bs.modal', function() { cargarCatalogosTicket(); });
        $(document).on('click', '#btnAbrirModalLevantarTicket', function() {
            $('#modal_id_tipo_ticket, #modal_id_prioridad, #modal_id_origen_ticket').val('');
            $('#modal_id_credito, #modal_descripcion_inicial').val('');
            clearFechaVencimiento();
            setButtonLevantarLoading(false);
            enviandoTicket = false;
            var el = document.getElementById('modalLevantarTicket');
            if (el) {
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    var m = bootstrap.Modal.getOrCreateInstance(el);
                    if (m) m.show();
                } else if (typeof $ !== 'undefined' && $.fn.modal) {
                    $('#modalLevantarTicket').modal('show');
                }
            }
        });
    });

    function getTickets() {
        http.request({
            endpoint: "/sabueso/getTickets",
            metodo: "POST",
            onSuccess: function(resp) {
                var datos = (resp.datos || []).map(function(t) {
                    var fechaCreacion = t.fecha_creacion
                        ? new Date(t.fecha_creacion).toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' })
                        : '\u2014';
                    var fechaVenc = t.fecha_vencimiento
                        ? new Date(t.fecha_vencimiento).toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' })
                        : '\u2014';
                    var prioridadNombre = (t.prioridad_nombre || '').toLowerCase();
                    var prioridadBadge = '<span class="badge bg-label-secondary">' + (t.prioridad_nombre || '\u2014') + '</span>';
                    if (prioridadNombre.indexOf('alta') !== -1) prioridadBadge = '<span class="badge bg-danger text-white">' + (t.prioridad_nombre || '\u2014') + '</span>';
                    else if (prioridadNombre.indexOf('medio') !== -1 || prioridadNombre.indexOf('media') !== -1) prioridadBadge = '<span class="badge" style="background-color:#fd7e14;color:#fff;">' + (t.prioridad_nombre || '\u2014') + '</span>';
                    else if (prioridadNombre.indexOf('bajo') !== -1 || prioridadNombre.indexOf('baja') !== -1) prioridadBadge = '<span class="badge" style="background-color:#ffc107;color:#212529;">' + (t.prioridad_nombre || '\u2014') + '</span>';
                    else if (prioridadNombre.indexOf('sin prioridad') !== -1) prioridadBadge = '<span class="badge bg-secondary" style="background-color:#6c757d!important;color:#fff;">' + (t.prioridad_nombre || '\u2014') + '</span>';
                    var estadoBadge = (t.asignado_nombre && (t.asignado_nombre + '').trim()) ? '<span class="badge bg-success text-white">Asignado</span>' : '<span class="badge bg-label-secondary">Abierto</span>';
                    var vistoHtml = '';
                    if ((t.dictamen_estado || '') === 'enviado_al_gestor') {
                        var vistoTexto = t.dictamen_fecha_visto ? (new Date(t.dictamen_fecha_visto).toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' }) + ' ' + new Date(t.dictamen_fecha_visto).toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' })) : 'No visto';
                        var iconoOjo = (t.dictamen_fecha_visto && (t.dictamen_fecha_visto + '').trim()) ? 'fa-eye' : 'fa-eye-slash';
                        var tituloOjo = (t.dictamen_fecha_visto && (t.dictamen_fecha_visto + '').trim()) ? ('Dictamen enviado. Visto: ' + vistoTexto) : 'Dictamen enviado. No visto. Clic para ver';
                        vistoHtml = '<span class="d-inline-flex align-items-center gap-1 justify-content-end btn-dictamen-ojito" role="button" tabindex="0" data-bs-toggle="tooltip" data-bs-title="' + (tituloOjo + '').replace(/"/g, '&quot;') + '" data-id-ticket="' + (t.id_ticket || '') + '"><i class="fa ' + iconoOjo + ' text-info small"></i></span>';
                    }
                    var tiempoVisitarHtml = '\u2014';
                    var fEnv = (t.dictamen_fecha_envio || '').trim();
                    var esNuevo = fEnv && new Date(fEnv) >= new Date('2026-03-09T00:00:00');
                    if ((t.dictamen_estado || '') === 'enviado_al_gestor' && esNuevo && fEnv) {
                        var envio = new Date(fEnv);
                        var fin = new Date(envio.getTime() + 12 * 60 * 60 * 1000);
                        var now = new Date();
                        var ms = fin - now;
                        var txtInicial = ms > 0 ? (Math.floor(ms / 3600000) + 'h ' + Math.floor((ms % 3600000) / 60000) + 'm') : 'Plazo vencido';
                        tiempoVisitarHtml = '<span class="d-inline-flex align-items-center gap-1 dictamen-countdown cursor-pointer" role="button" tabindex="0" data-fecha-envio="' + fEnv.replace(/"/g, '&quot;') + '" data-id-ticket="' + (t.id_ticket || '') + '" data-bs-toggle="tooltip"><i class="fa-solid fa-clock text-info small"></i><span class="dictamen-countdown-text">' + txtInicial + '</span></span>';
                    }
                    var row = {
                        _fecha_creacion: (t.fecha_creacion || ''),
                        folio_tipo: '<div class="fw-semibold">' + (t.folio || '\u2014') + '</div><div class="small text-muted mt-1">' + (t.tipo_ticket_nombre || '\u2014') + '</div>',
                        estado: estadoBadge,
                        prioridad: prioridadBadge,
                        credito: '<small>#' + (t.id_credito != null ? t.id_credito : '\u2014') + '</small>',
                        fechas: '<div class="small d-flex align-items-center gap-1"><i class="fa fa-calendar-plus-o text-muted" style="width: 1rem;"></i><span>Creación: ' + fechaCreacion + '</span></div><div class="small text-muted d-flex align-items-center gap-1 mt-1"><i class="fa fa-calendar-times-o" style="width: 1rem;"></i><span>Vencimiento: ' + fechaVenc + '</span></div>',
                        tiempo_visitar: tiempoVisitarHtml,
                        dictamen_visto: vistoHtml,
                        acciones: '',
                        _id_ticket: t.id_ticket,
                        _dictamen_estado: t.dictamen_estado || '',
                        _dictamen_fecha_visto: t.dictamen_fecha_visto || ''
                    };
                    return row;
                });
                var tabla = $('#tablaTickets').DataTable();
                tabla.clear().rows.add(datos).draw();
                tabla.rows().every(function() {
                    var d = this.data();
                    var node = this.node();
                    if (!d || !node) return;
                    if (d._dictamen_estado === 'enviado_al_gestor') {
                        $(node).addClass('fila-dictamen-enviado').attr('data-id-ticket', d._id_ticket || '');
                        if (!d._dictamen_fecha_visto || (d._dictamen_fecha_visto + '').trim() === '') $(node).addClass('fila-dictamen-no-visto');
                        else $(node).removeClass('fila-dictamen-no-visto');
                    } else {
                        $(node).removeClass('fila-dictamen-no-visto');
                    }
                });
                if (typeof actualizarCountdownsDictamen === 'function') actualizarCountdownsDictamen('#tablaTickets');
                $('#tablaTickets [data-bs-toggle="tooltip"]').tooltip();
            },
            onError: function() {
                var tabla = $('#tablaTickets').DataTable();
                tabla.clear().draw();
            }
        });
    }
    $('#tablaTickets').on('draw.dt', function() {
        var tabla;
        try { tabla = $(this).DataTable(); } catch(e) { return; }
        tabla.rows().every(function() {
            var d = this.data();
            var node = this.node();
            if (!d || !node) return;
            if (d._dictamen_estado === 'enviado_al_gestor') {
                $(node).addClass('fila-dictamen-enviado').attr('data-id-ticket', d._id_ticket || '');
                if (!d._dictamen_fecha_visto || (d._dictamen_fecha_visto + '').trim() === '') $(node).addClass('fila-dictamen-no-visto');
                else $(node).removeClass('fila-dictamen-no-visto');
            } else {
                $(node).removeClass('fila-dictamen-no-visto');
            }
        });
        $('#tablaTickets [data-bs-toggle="tooltip"]').tooltip();
    });
    $(document).on('click', '#tablaTickets tbody tr.fila-dictamen-enviado', function(e) {
        if ($(e.target).closest('button, .btn').length) return;
        var id = $(this).attr('data-id-ticket') || $(this).data('id-ticket');
        if (id && window.abrirModalDetalleDictamen) window.abrirModalDetalleDictamen(parseInt(id, 10));
    });
    $(document).on('click', '#tablaTickets .fa-eye, #tablaTickets .fa-eye-slash, #tablaTickets .fa-clock, #tablaTickets .dictamen-countdown', function(e) {
        e.stopPropagation();
        var id = $(this).closest('tr').attr('data-id-ticket') || $(this).closest('[data-id-ticket]').attr('data-id-ticket') || $(this).closest('tr').data('id-ticket');
        if (id && window.abrirModalDetalleDictamen) window.abrirModalDetalleDictamen(parseInt(id, 10));
    });
    window.abrirModalDetalleDictamen = function(idTicket) {
        if (!idTicket || typeof http === 'undefined') return;
        $('#modalDetalleDictamen .dictamen-detalle-imagen-principal').html('<img id="modalDetalleDictamenImgPrincipal" src="" alt="Evidencia" class="img-fluid w-100" style="object-fit: contain; max-height: 280px;">');
        $('#modalDetalleDictamenMiniaturas').empty();
        $('#modalDetalleDictamenTipo, #modalDetalleDictamenDescripcion, #modalDetalleDictamenEnviado, #modalDetalleDictamenVisto').text('');
        $('#modalDetalleDictamenDomiciliosWrap').hide();
        $('#modalDetalleDictamenDomicilios').empty();
        $('#modalDetalleDictamen').modal('show');
        http.request({
            endpoint: '/sabueso/getDictamenDetalle',
            metodo: 'POST',
            data: JSON.stringify({ id_ticket: idTicket }),
            contentType: 'application/json',
            processData: false,
            onSuccess: function(r) {
                if (!r.success || !r.datos) {
                    $('#modalDetalleDictamenTipo').text(r.mensaje || 'No se pudo cargar.');
                    return;
                }
                var d = r.datos;
                var dm = d.dictamen || {};
                $('#modalDetalleDictamenTipo').text(dm.tipo || '\u2014');
                var descMostrar = (dm.descripcion_base !== undefined ? dm.descripcion_base : dm.descripcion) || '\u2014';
                $('#modalDetalleDictamenDescripcion').html(window.linkifyDescripcionDictamen ? window.linkifyDescripcionDictamen(descMostrar) : descMostrar);
                var domicilios = d.domicilios || [];
                if (domicilios.length > 0) {
                    var $domWrap = $('#modalDetalleDictamenDomicilios');
                    $domWrap.empty();
                    domicilios.forEach(function(dom) {
                        var desc = (dom.desc || '').trim();
                        var link = (dom.link || '').trim();
                        var $card = $('<div class="d-flex align-items-center gap-2 p-2 rounded border bg-light bg-opacity-50"></div>');
                        if (desc) $card.append($('<span class="flex-grow-1 text-break"></span>').text(desc));
                        if (link) {
                            var safe = link.replace(/"/g, '&quot;');
                            $card.append($('<a href="' + safe + '" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary text-nowrap"><i class="fa-brands fa-google me-1"></i>Ver en Maps</a>'));
                        }
                        $domWrap.append($card);
                    });
                    $('#modalDetalleDictamenDomiciliosWrap').show();
                } else {
                    $('#modalDetalleDictamenDomiciliosWrap').hide();
                }
                $('#modalDetalleDictamenEnviado').text(dm.fecha_actualizacion ? (new Date(dm.fecha_actualizacion).toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })) : '\u2014');
                var fechaEnvio = dm.fecha_actualizacion ? new Date(dm.fecha_actualizacion).getTime() : 0;
                var pasaron12h = fechaEnvio > 0 && (Date.now() - fechaEnvio) > (12 * 60 * 60 * 1000);
                var $nota12h = $('#modalDetalleDictamenNota12h span');
                if ($nota12h.length) $nota12h.text(pasaron12h ? 'Ya transcurrieron sus 12 horas para visitar al cliente' : 'Vas a tener 12 horas para visitar al cliente');
                $('#modalDetalleDictamenVisto').text((function(){
                    var dm2 = d.dictamen || {};
                    if (!dm2.fecha_visto_gestor) return 'No visto';
                    var f = new Date(dm2.fecha_visto_gestor).toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
                    var quien = (dm2.visto_gestor_nombre || '').trim();
                    return quien ? ('Por ' + quien + ' el ' + f) : f;
                })());
                var evidencias = d.evidencias || [];
                var base = (typeof apiBase !== 'undefined' ? apiBase : '') || '';
                var url0 = '';
                if (evidencias.length > 0 && evidencias[0]) {
                    url0 = base + (evidencias[0].url || ('/sabueso/verEvidencia?id=' + (evidencias[0].id || '')));
                }
                if (!evidencias || evidencias.length === 0) {
                    $('#modalDetalleDictamen .dictamen-detalle-imagen-principal').html('<div class="d-flex align-items-center justify-content-center h-100 text-muted" style="min-height:200px;"><i class="fa-solid fa-image me-2"></i>Sin evidencias</div>');
                } else {
                    $('#modalDetalleDictamen .dictamen-detalle-imagen-principal').html('<img id="modalDetalleDictamenImgPrincipal" src="" alt="Evidencia" class="img-fluid w-100" style="object-fit: contain; max-height: 280px; cursor: pointer;">');
                    var $imgP = $('#modalDetalleDictamenImgPrincipal');
                    $imgP.attr('src', url0).on('click', function() {
                        if (url0 && window.abrirEvidenciaDictamenGrande) window.abrirEvidenciaDictamenGrande(url0);
                    });
                }
                var $min = $('#modalDetalleDictamenMiniaturas');
                $min.empty();
                var idsVistos = {};
                (evidencias || []).forEach(function(ev, idx) {
                    if (idx === 0) return;
                    var idEv = ev.id || (ev.url || '') + idx;
                    if (idsVistos[idEv]) return;
                    idsVistos[idEv] = true;
                    var url = base + (ev.url || ('/sabueso/verEvidencia?id=' + (ev.id || '')));
                    if (!url) return;
                    var $thumb = $('<div class="rounded overflow-hidden border" style="width: 60px; height: 60px; cursor: pointer;"><img src="' + url.replace(/"/g, '&quot;') + '" alt="" class="img-fluid w-100 h-100" style="object-fit: cover;"></div>');
                    $thumb.on('click', function() {
                        $('#modalDetalleDictamenImgPrincipal').attr('src', url);
                        if (window.abrirEvidenciaDictamenGrande) window.abrirEvidenciaDictamenGrande(url);
                    });
                    $min.append($thumb);
                });
                http.request({
                    endpoint: '/sabueso/marcarDictamenVisto',
                    metodo: 'POST',
                    data: JSON.stringify({ id_ticket: idTicket }),
                    contentType: 'application/json',
                    processData: false,
                    onSuccess: function(mr) {
                        $('#tablaTickets tr[data-id-ticket="' + idTicket + '"]').removeClass('fila-dictamen-no-visto');
                        if (typeof getTickets === 'function') getTickets();
                    },
                    onError: function() {}
                });
            },
            onError: function() {
                $('#modalDetalleDictamenTipo').text('Error al cargar.');
            }
        });
    };

    var fechaVencimientoClickHandler = null;
    function configurarFechaVencimiento() {
        setTimeout(function() {
            var oldInput = document.getElementById('modal_fecha_vencimiento');
            if (!oldInput) return;
            if (fechaVencimientoClickHandler && oldInput) {
                oldInput.removeEventListener('click', fechaVencimientoClickHandler);
                fechaVencimientoClickHandler = null;
            }
            try {
                if (oldInput._flatpickr && typeof oldInput._flatpickr.destroy === 'function') oldInput._flatpickr.destroy();
                if (typeof flatpickr !== 'undefined' && typeof flatpickr.getInstance === 'function') {
                    var existing = flatpickr.getInstance(oldInput);
                    if (existing && typeof existing.destroy === 'function') existing.destroy();
                }
            } catch (e) {}
            var currentValue = oldInput.value || '';
            var newInput = document.createElement('input');
            newInput.type = 'text';
            newInput.id = 'modal_fecha_vencimiento';
            newInput.className = 'form-control';
            newInput.placeholder = 'YYYY-MM-DD';
            newInput.value = currentValue;
            newInput.setAttribute('autocomplete', 'off');
            if (oldInput.parentNode) oldInput.parentNode.replaceChild(newInput, oldInput);
            setTimeout(function() {
                var input = document.getElementById('modal_fecha_vencimiento');
                if (!input) return;
                var manana = new Date();
                manana.setDate(manana.getDate() + 1);
                var minStr = manana.getFullYear() + '-' + String(manana.getMonth() + 1).padStart(2, '0') + '-' + String(manana.getDate()).padStart(2, '0');
                if (typeof flatpickr !== 'undefined') {
                    try {
                        var fp = flatpickr(input, { minDate: manana, dateFormat: 'Y-m-d', allowInput: false, clickOpens: true, defaultDate: null, appendTo: document.body, static: false });
                        if (fp && fp.calendarContainer) { fp.calendarContainer.style.zIndex = '99999'; }
                        fechaVencimientoClickHandler = function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            setTimeout(function() {
                                if (fp && typeof fp.open === 'function') fp.open();
                                if (fp && fp.calendarContainer) fp.calendarContainer.style.zIndex = '99999';
                            }, 10);
                        };
                        input.addEventListener('click', fechaVencimientoClickHandler);
                    } catch (err) {
                        input.type = 'date';
                        input.setAttribute('min', minStr);
                        input.value = '';
                    }
                } else {
                    input.type = 'date';
                    input.setAttribute('min', minStr);
                    input.value = '';
                }
            }, 50);
        }, 200);
    }
    function clearFechaVencimiento() {
        var input = document.getElementById('modal_fecha_vencimiento');
        if (!input) return;
        try {
            if (input._flatpickr && typeof input._flatpickr.clear === 'function') input._flatpickr.clear();
            else input.value = '';
        } catch (e) { input.value = ''; }
    }
    function setButtonLevantarLoading(loading) {
        var btn = document.getElementById('btnLevantarTicket');
        var textEl = document.getElementById('btnLevantarTicketText');
        if (!btn || !textEl) return;
        if (loading) {
            btn.disabled = true;
            textEl.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Registrando ticket…';
        } else {
            btn.disabled = false;
            textEl.innerHTML = '<i class="fa-solid fa-check me-1"></i>Levantar ticket';
        }
    }
    var enviandoTicket = false;
    var datosCreditoActual = null;

    function buscarCreditoModal() {
        var id = ($('#buscar_id_credito').val() || '').toString().trim();
        if (!id || isNaN(parseInt(id, 10))) {
            Swal.fire({ icon: 'warning', title: 'ID de crédito', text: 'Escriba un ID de crédito numérico y pulse Buscar.' });
            return;
        }
        http.request({
            endpoint: "/sabueso/getDatosCredito",
            metodo: "POST",
            data: JSON.stringify({ id_credito: parseInt(id, 10) }),
            contentType: "application/json",
            processData: false,
            showLoader: true,
            onSuccess: function(resp) {
                datosCreditoActual = resp.datos || null;
                $('#buscar_id_credito').val('');
                if (!resp.success || !datosCreditoActual) {
                    var msg = resp.mensaje || 'El ID de crédito no existe o es incorrecto. Verifique el número e intente de nuevo.';
                    setTimeout(function() { Swal.fire({ icon: 'error', title: 'ID de crédito incorrecto', text: msg }); }, 0);
                    return;
                }
                var d = datosCreditoActual;
                var html = '<div class="credito-modal-list">';
                html += '<div class="credito-modal-item"><i class="fa-solid fa-user text-primary me-2"></i><span class="text-muted small">Nombre</span><div class="fw-medium">' + (d.Nombre_cliente || d.nombre_completo || '\u2014') + '</div></div>';
                html += '<div class="credito-modal-item"><i class="fa-solid fa-hashtag text-primary me-2"></i><span class="text-muted small">ID de crédito</span><div class="fw-medium">' + (d.id_credito || d.Id_credito || '\u2014') + '</div></div>';
                if (d.Id_cliente) html += '<div class="credito-modal-item"><i class="fa-solid fa-id-card text-primary me-2"></i><span class="text-muted small">ID cliente</span><div class="fw-medium">' + d.Id_cliente + '</div></div>';
                html += '<div class="credito-modal-item"><i class="fa-solid fa-map-marker-alt text-primary me-2"></i><span class="text-muted small">Dirección</span><div class="fw-medium">' + (d.Domicilio_Completo || '\u2014') + '</div></div>';
                var tel = d.telefono_referencia1 || d.telefono_referencia2 || '';
                if (tel) html += '<div class="credito-modal-item"><i class="fa-solid fa-phone text-primary me-2"></i><span class="text-muted small">Teléfono</span><div class="fw-medium">' + tel + '</div></div>';
                if (d.correo || d.email) html += '<div class="credito-modal-item"><i class="fa-solid fa-envelope text-primary me-2"></i><span class="text-muted small">Correo</span><div class="fw-medium">' + (d.correo || d.email || '\u2014') + '</div></div>';
                var tickets = d.tickets || [];
                if (tickets.length > 0) {
                    html += '<div class="credito-modal-item mt-3 pt-3 border-top"><span class="text-muted small d-block mb-2"><i class="fa-solid fa-ticket me-1"></i>Ticket(s) levantado(s)</span>';
                    tickets.forEach(function(tk) {
                        var fCreacion = tk.fecha_creacion ? new Date(tk.fecha_creacion).toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '\u2014';
                        var fVenc = tk.fecha_vencimiento ? new Date(tk.fecha_vencimiento).toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '\u2014';
                        html += '<div class="small bg-light rounded p-2 mb-2"><strong>' + (tk.folio || '\u2014') + '</strong> · ' + (tk.tipo_nombre || '') + ' · ' + (tk.estado_nombre || '') + '<br><span class="text-muted small">Descripción:</span> ' + (tk.descripcion_inicial || '\u2014') + '<br>Creación: ' + fCreacion + ' · Venc: ' + fVenc + '</div>';
                    });
                    html += '</div>';
                }
                html += '</div>';
                $('#modalDatosCreditoBody').html(html);
                $('#modalDatosCredito').modal('show');
            },
            onError: function(err) {
                datosCreditoActual = null;
                $('#buscar_id_credito').val('');
                var errMsg = (typeof err === 'string' ? err : (err && err.mensaje)) || 'El ID de crédito no existe o es incorrecto. Verifique el número e intente de nuevo.';
                setTimeout(function() { Swal.fire({ icon: 'error', title: 'ID de crédito incorrecto', text: errMsg }); }, 0);
            }
        });
    }
    function usarCreditoEnTicket() {
        if (datosCreditoActual && (datosCreditoActual.id_credito || datosCreditoActual.Id_credito)) {
            var idCred = datosCreditoActual.id_credito || datosCreditoActual.Id_credito;
            $('#modal_id_credito').val(idCred);
            $('#modalDatosCredito').modal('hide');
            var el = document.getElementById('modalLevantarTicket');
            if (el && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(el).show();
            } else if (typeof $ !== 'undefined' && $.fn.modal) {
                $('#modalLevantarTicket').modal('show');
            }
        }
    }
    window.buscarCreditoModal = buscarCreditoModal;
    window.usarCreditoEnTicket = usarCreditoEnTicket;

    function cargarCatalogosTicket() {
        http.request({
            endpoint: "/sabueso/getCatalogosTicket",
            metodo: "POST",
            onSuccess: function(resp) {
                var c = resp.datos || {};
                var tipos = c.tipos || [], estados = c.estados || [], prioridades = c.prioridades || [], origenes = c.origenes || [];
                function options(arr) {
                    var h = '<option value="">Seleccione...</option>';
                    arr.forEach(function(o) { h += '<option value="' + (o.id) + '">' + (o.nombre || o.id) + '</option>'; });
                    return h;
                }
                $('#modal_id_tipo_ticket').html(options(tipos));
                $('#modal_id_prioridad').html(options(prioridades));
                $('#modal_id_origen_ticket').html(options(origenes));
                var origenSistema = origenes.filter(function(o) { return (o.nombre || '').toLowerCase().indexOf('sistema') !== -1; })[0];
                if (origenSistema && origenSistema.id) $('#modal_id_origen_ticket').val(origenSistema.id);
                else if (origenes.length > 0 && origenes[0].id) $('#modal_id_origen_ticket').val(origenes[0].id);
                setTimeout(configurarFechaVencimiento, 150);
            }
        });
    }

    function enviarLevantarTicket() {
        var payload = {
            id_tipo_ticket: $('#modal_id_tipo_ticket').val(),
            id_prioridad: $('#modal_id_prioridad').val(),
            id_origen_ticket: $('#modal_id_origen_ticket').val(),
            id_credito: ($('#modal_id_credito').val() || '').toString().trim(),
            descripcion_inicial: ($('#modal_descripcion_inicial').val() || '').toString().trim(),
            fecha_vencimiento: ($('#modal_fecha_vencimiento').val() || '').toString().trim()
        };
        if (!payload.id_tipo_ticket || !payload.id_prioridad || !payload.id_origen_ticket || !payload.descripcion_inicial) {
            Swal.fire({ icon: 'warning', title: 'Faltan datos', text: 'Complete tipo, prioridad, origen y descripción.' });
            return;
        }
        if (!payload.id_credito || isNaN(parseInt(payload.id_credito, 10)) || parseInt(payload.id_credito, 10) < 1) {
            Swal.fire({ icon: 'warning', title: 'ID de crédito obligatorio', text: 'Debe indicar un ID de crédito válido.' });
            return;
        }
        if (!payload.fecha_vencimiento) {
            Swal.fire({ icon: 'warning', title: 'Fecha de vencimiento obligatoria', text: 'Debe seleccionar la fecha de vencimiento.' });
            return;
        }
        if (enviandoTicket) return;
        enviandoTicket = true;
        Swal.fire({ title: 'Registrando ticket', text: 'Se está registrando el ticket. Espere un momento...', showConfirmButton: false, allowOutsideClick: false, allowEscapeKey: false, didOpen: function() { if (typeof Swal !== 'undefined' && typeof Swal.showLoading === 'function') Swal.showLoading(); } });
        setButtonLevantarLoading(true);
        http.request({
            endpoint: "/sabueso/crearTicket",
            metodo: "POST",
            data: JSON.stringify(payload),
            contentType: "application/json",
            processData: false,
            showLoader: false,
            onSuccess: function(resp) {
                if (!resp.success) {
                    enviandoTicket = false;
                    if (typeof Swal !== 'undefined' && Swal.isVisible && Swal.isVisible()) Swal.close();
                    setButtonLevantarLoading(false);
                    Swal.fire({ icon: 'error', title: 'Error', text: resp.mensaje || 'No se pudo crear el ticket. Verifique el ID de crédito.' });
                    return;
                }
                enviandoTicket = false;
                if (typeof Swal !== 'undefined' && Swal.isVisible && Swal.isVisible()) Swal.close();
                $('#modalLevantarTicket').modal('hide');
                setButtonLevantarLoading(false);
                $('#modal_id_tipo_ticket, #modal_id_prioridad, #modal_id_origen_ticket').val('');
                $('#modal_id_credito, #modal_descripcion_inicial').val('');
                clearFechaVencimiento();
                getTickets();
                setTimeout(function() {
                    Swal.fire({ icon: 'success', title: 'Ticket creado', text: resp.mensaje || 'Folio: ' + (resp.datos && resp.datos.folio ? resp.datos.folio : '') });
                }, 100);
            },
            onError: function(err) {
                enviandoTicket = false;
                if (typeof Swal !== 'undefined' && Swal.isVisible && Swal.isVisible()) Swal.close();
                setButtonLevantarLoading(false);
                Swal.fire({ icon: 'error', title: 'Error', text: (err && err.mensaje) || 'No se pudo crear el ticket.' });
            }
        });
    }
    window.enviarLevantarTicket = enviarLevantarTicket;

    function eliminarTicket(idTicket) {
        if (!idTicket) return;
        Swal.fire({ title: '¿Eliminar ticket?', text: 'Esta acción no se puede deshacer.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#6c757d', confirmButtonText: 'Sí, eliminar' }).then(function(res) {
            if (!res.isConfirmed) return;
            http.request({
                endpoint: "/sabueso/eliminarTicket",
                metodo: "POST",
                data: JSON.stringify({ id_ticket: idTicket }),
                contentType: "application/json",
                processData: false,
                onSuccess: function(resp) {
                    Swal.fire({ icon: 'success', title: 'Eliminado', text: resp.mensaje || 'Ticket eliminado.' });
                    getTickets();
                },
                onError: function(err) {
                    Swal.fire({ icon: 'error', title: 'Error', text: (err && err.mensaje) || 'No se pudo eliminar.' });
                }
            });
        });
    }
    window.eliminarTicket = eliminarTicket;
})();
