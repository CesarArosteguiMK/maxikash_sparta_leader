/**
 * Script para Sabueso > Ticket. Requiere: window.sabuesoTicketColumns (array de columnas DataTables)
 */
(function() {
    var esAdminTicket = false;
    var esperandoCatalogosDesdeContinuar = false;
    var apiBase = (function(){ var p = window.location.pathname || ''; var i = p.indexOf('/sabueso'); return i !== -1 ? p.substring(0, i) : ''; })();
    window.etiquetaTipoDictamenSabueso = window.etiquetaTipoDictamenSabueso || function(tipo) {
        var t = (tipo || '').toString().toLowerCase().trim();
        var m = { ilocalizable: 'ILOCALIZABLE', localizable: 'LOCALIZABLE', dual_zonificacion: 'DUAL || ZONIFICACIÓN', falta_intensidad_gestion: 'FALTA INTENSIDAD DE GESTION',
            localizado: 'Localizado', no_localizado: 'No localizado', promesa_pago: 'Promesa de pago', otro: 'Otro' };
        return m[t] || (tipo && String(tipo).trim() ? String(tipo).trim() : '—');
    };
    window.esTipoDictamenIlocalizable = window.esTipoDictamenIlocalizable || function(tipo) {
        return (tipo || '').toString().toLowerCase().trim() === 'ilocalizable';
    };
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
        $(sel + ' .dictamen-countdown').each(function() {
            var el = this;
            var fLim = $(el).attr('data-fecha-limite');
            var f = $(el).attr('data-fecha-envio');
            if (!f && !fLim) return;
            var fin = fLim ? new Date(fLim) : new Date(new Date(f).getTime() + 12 * 60 * 60 * 1000);
            var now = new Date();
            var ms = fin - now;
            var txt = '-';
            var txtCorto = '-';
            var expired = ms <= 0;
            if (ms > 0) {
                var h = Math.floor(ms / 3600000);
                var m = Math.floor((ms % 3600000) / 60000);
                var extTipo = ($(el).attr('data-extension-tipo') || '').toString();
                var pref = fLim ? (extTipo === 'intensidad' ? 'Intensidad · ' : 'Prórroga · ') : '';
                txt = pref + 'Tiempo restante: ' + h + 'h ' + m + 'm';
                txtCorto = (fLim ? 'P2 ' : '') + h + 'h ' + m + 'm';
            } else {
                var extTipo2 = ($(el).attr('data-extension-tipo') || '').toString();
                txt = fLim ? (extTipo2 === 'intensidad' ? 'Intensidad vencida' : 'Prórroga vencida') : 'Plazo vencido';
                txtCorto = txt;
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
        // Paso 1: abrir modal de categorías con selección visual
        var categoriaSeleccionada = null;
        function actualizarEstadoSeleccionCategoria(categoria) {
            categoriaSeleccionada = categoria || null;
            $('.ticket-categoria-card').removeClass('is-selected');
            var card = $('.ticket-categoria-card[data-categoria="' + (categoriaSeleccionada || '') + '"]');
            if (card.length && card.attr('data-disponible') === '1') {
                card.addClass('is-selected');
            }
            var btnTxt = 'Continuar';
            if (categoriaSeleccionada === 'sabueso') btnTxt = 'Continuar con Sabueso';
            else if (categoriaSeleccionada === 'solicitud_baja') btnTxt = 'Continuar con Solicitud de baja';
            else if (categoriaSeleccionada === 'plantilla') btnTxt = 'Continuar con Plantilla';
            else if (categoriaSeleccionada === 'atencion_cliente') btnTxt = 'Continuar con Atención al cliente';
            else if (categoriaSeleccionada === 'validaciones') btnTxt = 'Continuar con Validaciones';
            else if (categoriaSeleccionada === 'viaticos') btnTxt = 'Continuar con Viáticos';
            else if (categoriaSeleccionada === 'aplicaciones_de_pago') btnTxt = 'Continuar con Aplicaciones de pago';
            else if (categoriaSeleccionada === 'credito_problematico') btnTxt = 'Continuar con Crédito problemático';
            else if (categoriaSeleccionada === 'aclaracion_credito') btnTxt = 'Continuar con Aclaración de crédito';
            $('#btnContinuarCategoriaTicket').html(btnTxt + ' <i class="fa-solid fa-arrow-right ms-1"></i>');
        }
        function abrirModalPickerCategorias() {
            actualizarEstadoSeleccionCategoria(null);
            var elPicker = document.getElementById('modalElegirCategoriaTicket');
            if (elPicker) {
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    bootstrap.Modal.getOrCreateInstance(elPicker).show();
                } else if (typeof $ !== 'undefined' && $.fn.modal) {
                    $('#modalElegirCategoriaTicket').modal('show');
                }
            } else {
                abrirModalLevantarTicketSabuesoDirecto();
            }
        }
        $(document).on('click', '#btnAbrirModalLevantarTicket', function() {
            if (typeof window.categoriasDisponiblesPorPuesto === 'object' && Array.isArray(window.categoriasDisponiblesPorPuesto) && window.categoriasDisponiblesPorPuesto.length === 0) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sin tipos de ticket asignados',
                        text: 'Tu puesto no tiene tipos de ticket configurados. Contacta al administrador.'
                    });
                } else {
                    alert('Tu puesto no tiene tipos de ticket asignados. Contacta al administrador.');
                }
                return;
            }
            intentarAbrirModalLevantarTicket(function() {
                abrirModalPickerCategorias();
            }, false);
        });
        $(document).on('click', '.ticket-categoria-card[data-disponible="1"]', function() {
            var cat = ($(this).attr('data-categoria') || '').toString().trim();
            if (!cat) return;
            actualizarEstadoSeleccionCategoria(cat);
        });
        $(document).on('click', '#btnContinuarCategoriaTicket', function() {
            var disp = (typeof window.categoriasDisponiblesPorPuesto === 'object' && Array.isArray(window.categoriasDisponiblesPorPuesto)) ? window.categoriasDisponiblesPorPuesto : [];
            var permitido = categoriaSeleccionada && disp.indexOf(categoriaSeleccionada) !== -1;
            var card = $('.ticket-categoria-card[data-categoria="' + (categoriaSeleccionada || '') + '"]');
            if (!categoriaSeleccionada || !permitido || !card.length || card.attr('data-disponible') !== '1') {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'warning', title: 'Opción no disponible', text: 'Elige un área asignada a tu puesto para continuar.' });
                } else {
                    alert('Elige un área asignada a tu puesto.');
                }
                return;
            }
            function cerrarPickerYAbrirModal(modalId) {
                var elPicker = document.getElementById('modalElegirCategoriaTicket');
                if (elPicker && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    bootstrap.Modal.getOrCreateInstance(elPicker).hide();
                } else if (typeof $ !== 'undefined' && $.fn.modal) {
                    $('#modalElegirCategoriaTicket').modal('hide');
                }
                var el = document.getElementById(modalId);
                if (el) {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        bootstrap.Modal.getOrCreateInstance(el).show();
                    } else if (typeof $ !== 'undefined' && $.fn.modal) {
                        $('#' + modalId).modal('show');
                    }
                }
            }
            if (categoriaSeleccionada === 'solicitud_baja') {
                var elPicker = document.getElementById('modalElegirCategoriaTicket');
                if (elPicker && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    bootstrap.Modal.getOrCreateInstance(elPicker).hide();
                } else if (typeof $ !== 'undefined' && $.fn.modal) {
                    $('#modalElegirCategoriaTicket').modal('hide');
                }
                abrirModalSolicitudBaja();
                return;
            }
            if (categoriaSeleccionada === 'plantilla') {
                cerrarPickerYAbrirModal('modalTicketPlantilla');
                return;
            }
            if (categoriaSeleccionada === 'atencion_cliente') {
                cerrarPickerYAbrirModal('modalTicketAtencionCliente');
                return;
            }
            if (categoriaSeleccionada === 'validaciones') {
                cerrarPickerYAbrirModal('modalTicketValidacion');
                return;
            }
            if (categoriaSeleccionada === 'viaticos') {
                cerrarPickerYAbrirModal('modalTicketViaticos');
                return;
            }
            if (categoriaSeleccionada === 'aplicaciones_de_pago') {
                cerrarPickerYAbrirModal('modalTicketAplicacionesPago');
                return;
            }
            if (categoriaSeleccionada === 'credito_problematico') {
                cerrarPickerYAbrirModal('modalTicketCreditoProblematico');
                return;
            }
            if (categoriaSeleccionada === 'aclaracion_credito') {
                cerrarPickerYAbrirModal('modalTicketAclaracionCredito');
                return;
            }
            if (categoriaSeleccionada !== 'sabueso') {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'info', title: 'Próximamente', text: 'Esta categoría aún no está disponible.' });
                }
                return;
            }
            var elPicker = document.getElementById('modalElegirCategoriaTicket');
            if (elPicker && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(elPicker).hide();
            } else if (typeof $ !== 'undefined' && $.fn.modal) {
                $('#modalElegirCategoriaTicket').modal('hide');
            }
            // Mostrar "Cargando..." durante todo el proceso (validación + catálogos) hasta que el formulario esté listo
            esperandoCatalogosDesdeContinuar = true;
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Cargando…',
                    text: 'Preparando formulario de ticket.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: function() { if (typeof Swal.showLoading === 'function') Swal.showLoading(); }
                });
            }
            intentarAbrirModalLevantarTicket(function() { abrirModalLevantarTicketSabuesoDirecto(); }, true);
        });
        var archivosSeleccionadosSolicitudBaja = [];
        function renderSolicitudBajaArchivos() {
            var lista = document.getElementById('solicitud_baja_lista_archivos');
            var span = document.getElementById('solicitud_baja_count_archivos');
            if (!lista) return;
            lista.innerHTML = '';
            if (archivosSeleccionadosSolicitudBaja.length === 0) {
                lista.style.display = 'none';
                if (span) span.textContent = 'No se ha seleccionado ningún archivo';
                return;
            }
            lista.style.display = 'block';
            if (span) span.textContent = archivosSeleccionadosSolicitudBaja.length + ' archivo(s) seleccionado(s)';
            archivosSeleccionadosSolicitudBaja.forEach(function(file, index) {
                var esPdf = (file.type || '').toLowerCase().indexOf('pdf') !== -1;
                var iconClass = esPdf ? 'fa fa-file-pdf text-danger' : 'fa fa-image text-primary';
                var div = document.createElement('div');
                div.className = 'd-flex align-items-center justify-content-between p-2 mb-2 border rounded';
                div.style.backgroundColor = '#f8f9fa';
                div.innerHTML = '<div class="d-flex align-items-center gap-2">' +
                    '<i class="fa ' + iconClass + '"></i>' +
                    '<span class="text-break">' + (file.name || 'archivo') + '</span>' +
                    '<span class="badge bg-success rounded-pill"><i class="fa fa-check"></i></span>' +
                    '</div>' +
                    '<div class="d-flex gap-1">' +
                    '<button type="button" class="btn btn-sm btn-info p-1 ver-adjunto-sb" data-idx="' + index + '" title="Ver archivo" style="width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center;"><i class="fa fa-eye" style="font-size: 12px;"></i></button>' +
                    '<button type="button" class="btn btn-sm btn-danger p-1 quitar-adjunto-sb" data-idx="' + index + '" title="Quitar" style="width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center;"><i class="fa fa-times" style="font-size: 12px;"></i></button>' +
                    '</div>';
                lista.appendChild(div);
            });
            $(lista).off('click', '.ver-adjunto-sb').on('click', '.ver-adjunto-sb', function() {
                var idx = parseInt($(this).data('idx'), 10);
                var f = archivosSeleccionadosSolicitudBaja[idx];
                if (f && window.URL && window.URL.createObjectURL) window.open(URL.createObjectURL(f), '_blank');
            });
            $(lista).off('click', '.quitar-adjunto-sb').on('click', '.quitar-adjunto-sb', function() {
                var idx = parseInt($(this).data('idx'), 10);
                archivosSeleccionadosSolicitudBaja.splice(idx, 1);
                renderSolicitudBajaArchivos();
            });
        }
        $(document).on('click', '#btnSolicitudBajaElegirArchivos', function() {
            document.getElementById('solicitud_baja_adjunto').click();
        });
        $(document).on('change', '#solicitud_baja_adjunto', function() {
            var input = this;
            var files = input.files ? Array.from(input.files) : [];
            var permitidos = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            files.forEach(function(file) {
                var t = (file.type || '').toLowerCase();
                if (t.indexOf('pdf') !== -1 || permitidos.indexOf(t) !== -1) archivosSeleccionadosSolicitudBaja.push(file);
            });
            input.value = '';
            renderSolicitudBajaArchivos();
        });
        function abrirModalSolicitudBaja() {
            $('#solicitud_baja_motivo').val('');
            $('#solicitud_baja_detalle_motivo, #solicitud_baja_descripcion, #solicitud_baja_nombre_colaborador').val('');
            $('#solicitud_baja_adjunto').val('');
            archivosSeleccionadosSolicitudBaja = [];
            renderSolicitudBajaArchivos();
            var el = document.getElementById('modalSolicitudBaja');
            if (el) {
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    bootstrap.Modal.getOrCreateInstance(el).show();
                } else if (typeof $ !== 'undefined' && $.fn.modal) {
                    $('#modalSolicitudBaja').modal('show');
                }
            }
        }
        $(document).on('click', '#btnEnviarSolicitudBaja', function() {
            var motivo = ($('#solicitud_baja_motivo').val() || '').toString().trim();
            var detalle = ($('#solicitud_baja_detalle_motivo').val() || '').toString().trim();
            var nombreColab = ($('#solicitud_baja_nombre_colaborador').val() || '').toString().trim();
            if (!motivo) {
                if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'Falta dato', text: 'Seleccione el motivo de la solicitud.' });
                return;
            }
            if (!detalle) {
                if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'Falta dato', text: 'Escriba el detalle del motivo.' });
                return;
            }
            if (!nombreColab) {
                if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'Falta dato', text: 'Indique el nombre del colaborador a dar de baja.' });
                return;
            }
            var descripcion = ($('#solicitud_baja_descripcion').val() || '').toString().trim();
            var fd = new FormData();
            fd.append('motivo_baja', motivo);
            fd.append('detalle_motivo', detalle);
            fd.append('descripcion', descripcion);
            fd.append('nombre_colaborador', nombreColab);
            archivosSeleccionadosSolicitudBaja.forEach(function(file) {
                fd.append('adjunto[]', file);
            });
            var $btn = $('#btnEnviarSolicitudBaja');
            var loadingHtml = $btn.html();
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status"></span>Enviando...');
            var apiBase = (function(){ var p = window.location.pathname || ''; var i = p.indexOf('/sabueso'); return i !== -1 ? p.substring(0, i) : ''; })();
            $.ajax({
                url: (apiBase || '') + '/sabueso/guardarSolicitudBaja',
                type: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                success: function(r) {
                    $btn.prop('disabled', false).html(loadingHtml);
                    if (r && r.success) {
                        if (typeof bootstrap !== 'undefined') {
                            var m = bootstrap.Modal.getInstance(document.getElementById('modalSolicitudBaja'));
                            if (m) m.hide();
                        } else $('#modalSolicitudBaja').modal('hide');
                        if (typeof Swal !== 'undefined') Swal.fire({ icon: 'success', title: 'Solicitud enviada', text: r.mensaje || 'La solicitud de baja se registró correctamente.' });
                    } else {
                        if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'Error', text: (r && r.mensaje) ? r.mensaje : 'No se pudo enviar la solicitud.' });
                    }
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).html(loadingHtml);
                    var msg = 'No se pudo enviar la solicitud.';
                    try {
                        var j = xhr.responseJSON || (xhr.responseText ? JSON.parse(xhr.responseText) : null);
                        if (j && j.mensaje) msg = j.mensaje;
                    } catch (e) {}
                    if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'Error', text: msg });
                }
            });
        });
        function enviarTicketSimple(modalId, endpoint, payload, inputFileId, btnId) {
            var $btn = $(btnId);
            var loadingHtml = $btn.html();
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status"></span>Enviando...');
            var fd = new FormData();
            Object.keys(payload).forEach(function(k) { fd.append(k, payload[k]); });
            if (inputFileId) {
                var input = document.getElementById(inputFileId);
                if (input && input.files && input.files.length > 0) fd.append('adjunto', input.files[0]);
            }
            $.ajax({
                url: (apiBase || '') + endpoint,
                type: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                success: function(r) {
                    $btn.prop('disabled', false).html(loadingHtml);
                    if (r && r.success) {
                        if (typeof bootstrap !== 'undefined') {
                            var m = bootstrap.Modal.getInstance(document.getElementById(modalId));
                            if (m) m.hide();
                        } else $('#' + modalId).modal('hide');
                        if (typeof Swal !== 'undefined') Swal.fire({ icon: 'success', title: 'Enviado', text: r.mensaje || 'Registrado correctamente.' });
                    } else {
                        if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'Error', text: (r && r.mensaje) ? r.mensaje : 'No se pudo enviar.' });
                    }
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).html(loadingHtml);
                    var msg = 'No se pudo enviar.';
                    try {
                        var j = xhr.responseJSON || (xhr.responseText ? JSON.parse(xhr.responseText) : null);
                        if (j && j.mensaje) msg = j.mensaje;
                    } catch (e) {}
                    if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'Error', text: msg });
                }
            });
        }
        $(document).on('click', '#btnEnviarTicketPlantilla', function() {
            var tipo = ($('#ticket_plantilla_tipo').val() || '').toString().trim();
            var desc = ($('#ticket_plantilla_descripcion').val() || '').toString().trim();
            if (!tipo) { if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'Falta dato', text: 'Seleccione el tipo de plantilla.' }); return; }
            if (!desc) { if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'Falta dato', text: 'Escriba la descripción.' }); return; }
            enviarTicketSimple('modalTicketPlantilla', '/sabueso/guardarTicketPlantilla', { tipo_plantilla: tipo, descripcion: desc }, 'ticket_plantilla_adjunto', '#btnEnviarTicketPlantilla');
        });
        $(document).on('click', '#btnEnviarTicketAtencionCliente', function() {
            var asunto = ($('#ticket_atencion_asunto').val() || '').toString().trim();
            var desc = ($('#ticket_atencion_descripcion').val() || '').toString().trim();
            if (!asunto) { if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'Falta dato', text: 'Escriba el asunto.' }); return; }
            if (!desc) { if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'Falta dato', text: 'Escriba la descripción.' }); return; }
            enviarTicketSimple('modalTicketAtencionCliente', '/sabueso/guardarTicketAtencionCliente', {
                asunto: asunto,
                descripcion: desc,
                prioridad: ($('#ticket_atencion_prioridad').val() || 'media').toString().trim(),
                contacto_telefono: ($('#ticket_atencion_telefono').val() || '').toString().trim(),
                contacto_email: ($('#ticket_atencion_email').val() || '').toString().trim()
            }, null, '#btnEnviarTicketAtencionCliente');
        });
        var archivosSeleccionadosValidacion = [];
        function renderListaArchivosValidacion() {
            var lista = document.getElementById('ticket_validacion_lista_archivos');
            var span = document.getElementById('ticket_validacion_count_archivos');
            var inp = document.getElementById('ticket_validacion_adjunto');
            if (!lista) return;
            lista.innerHTML = '';
            if (archivosSeleccionadosValidacion.length === 0) {
                lista.classList.add('d-none');
                if (span) span.textContent = 'Ningún archivo seleccionado';
                if (inp) inp.value = '';
                return;
            }
            lista.classList.remove('d-none');
            if (span) span.textContent = archivosSeleccionadosValidacion.length + ' archivo(s) seleccionado(s)';
            archivosSeleccionadosValidacion.forEach(function(file, index) {
                var esPdf = (file.type || '').toLowerCase().indexOf('pdf') !== -1 || (file.name || '').toLowerCase().endsWith('.pdf');
                var iconClass = esPdf ? 'fa-file-pdf text-danger' : 'fa-image text-primary';
                var div = document.createElement('div');
                div.className = 'd-flex align-items-center justify-content-between p-3 mb-2 rounded-3';
                div.style.cssText = 'background:#f1f5f9;border:1px solid #e2e8f0;';
                div.innerHTML =
                    '<div class="d-flex align-items-center gap-2 flex-grow-1 min-w-0">' +
                    '<i class="fa-solid ' + iconClass + ' fa-lg flex-shrink-0"></i>' +
                    '<span class="text-break small fw-medium">' +
                    (file.name || 'archivo') +
                    '</span>' +
                    '<span class="badge bg-success rounded-2 flex-shrink-0 ms-1"><i class="fa-solid fa-check"></i></span>' +
                    '</div>' +
                    '<div class="d-flex gap-2 flex-shrink-0 ms-2">' +
                    '<button type="button" class="btn btn-info rounded-circle p-0 ver-adjunto-val" data-idx="' +
                    index +
                    '" title="Ver" style="width:36px;height:36px;box-shadow:0 2px 6px rgba(0,0,0,0.12);"><i class="fa-solid fa-eye text-white"></i></button>' +
                    '<button type="button" class="btn rounded-circle p-0 quitar-adjunto-val" data-idx="' +
                    index +
                    '" title="Quitar" style="width:36px;height:36px;background:#f97316;border-color:#f97316;box-shadow:0 2px 6px rgba(0,0,0,0.12);"><i class="fa-solid fa-times text-white"></i></button>' +
                    '</div>';
                lista.appendChild(div);
            });
            $(lista)
                .off('click', '.ver-adjunto-val')
                .on('click', '.ver-adjunto-val', function () {
                    var idx = parseInt($(this).data('idx'), 10);
                    var f = archivosSeleccionadosValidacion[idx];
                    if (f && window.URL && window.URL.createObjectURL) window.open(URL.createObjectURL(f), '_blank');
                });
            $(lista)
                .off('click', '.quitar-adjunto-val')
                .on('click', '.quitar-adjunto-val', function () {
                    var idx = parseInt($(this).data('idx'), 10);
                    archivosSeleccionadosValidacion.splice(idx, 1);
                    renderListaArchivosValidacion();
                });
        }
        $(document).on('click', '#ticket_validacion_btn_archivos', function () {
            document.getElementById('ticket_validacion_adjunto').click();
        });
        $(document).on('change', '#ticket_validacion_adjunto', function () {
            var files = this.files;
            if (!files || !files.length) return;
            for (var i = 0; i < files.length; i++) archivosSeleccionadosValidacion.push(files[i]);
            this.value = '';
            renderListaArchivosValidacion();
        });
        $('#modalTicketValidacion').on('shown.bs.modal', function () {
            archivosSeleccionadosValidacion = [];
            renderListaArchivosValidacion();
        });
        $(document).on('click', '#btnEnviarTicketValidacion', function () {
            var desc = ($('#ticket_validacion_descripcion').val() || '').toString().trim();
            if (!desc) {
                if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'Falta dato', text: 'Escriba la descripción.' });
                return;
            }
            var nota = ($('#ticket_validacion_nota').val() || '').toString().trim();
            var urlDir = ($('#ticket_validacion_url').val() || '').toString().trim();
            var $btn = $('#btnEnviarTicketValidacion');
            var loadingHtml = $btn.html();
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status"></span>Enviando...');
            var fd = new FormData();
            fd.append('descripcion', desc);
            fd.append('nota', nota);
            fd.append('url_direccion', urlDir);
            archivosSeleccionadosValidacion.forEach(function (f) {
                fd.append('adjunto[]', f);
            });
            $.ajax({
                url: (apiBase || '') + '/sabueso/guardarTicketValidacion',
                type: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                success: function (r) {
                    $btn.prop('disabled', false).html(loadingHtml);
                    if (r && r.success) {
                        archivosSeleccionadosValidacion = [];
                        renderListaArchivosValidacion();
                        if (typeof bootstrap !== 'undefined') {
                            var m = bootstrap.Modal.getInstance(document.getElementById('modalTicketValidacion'));
                            if (m) m.hide();
                        } else $('#modalTicketValidacion').modal('hide');
                        if (typeof Swal !== 'undefined') Swal.fire({ icon: 'success', title: 'Enviado', text: r.mensaje || 'Registrado correctamente.' });
                    } else if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'Error', text: (r && r.mensaje) ? r.mensaje : 'No se pudo enviar.' });
                },
                error: function (xhr) {
                    $btn.prop('disabled', false).html(loadingHtml);
                    var msg = 'No se pudo enviar.';
                    try {
                        var j = xhr.responseJSON || (xhr.responseText ? JSON.parse(xhr.responseText) : null);
                        if (j && j.mensaje) msg = j.mensaje;
                    } catch (e) {}
                    if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'Error', text: msg });
                }
            });
        });
        $(document).on('click', '#btnEnviarTicketViaticos', function() {
            var tipo = ($('#ticket_viaticos_tipo').val() || '').toString().trim();
            var desc = ($('#ticket_viaticos_descripcion').val() || '').toString().trim();
            if (!tipo) { if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'Falta dato', text: 'Seleccione el tipo de viático.' }); return; }
            if (!desc) { if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'Falta dato', text: 'Escriba la descripción.' }); return; }
            enviarTicketSimple('modalTicketViaticos', '/sabueso/guardarTicketViaticos', { tipo_viatico: tipo, descripcion: desc }, 'ticket_viaticos_adjunto', '#btnEnviarTicketViaticos');
        });
        $(document).on('click', '#btnEnviarTicketAplicacionesPago', function() {
            var tipo = ($('#ticket_aplicaciones_tipo').val() || '').toString().trim();
            var desc = ($('#ticket_aplicaciones_descripcion').val() || '').toString().trim();
            if (!tipo) { if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'Falta dato', text: 'Seleccione el tipo de solicitud.' }); return; }
            if (!desc) { if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'Falta dato', text: 'Escriba la descripción.' }); return; }
            enviarTicketSimple('modalTicketAplicacionesPago', '/sabueso/guardarTicketAplicacionesPago', { tipo_solicitud: tipo, descripcion: desc }, 'ticket_aplicaciones_adjunto', '#btnEnviarTicketAplicacionesPago');
        });
        $(document).on('click', '#btnEnviarTicketCreditoProblematico', function() {
            var tipo = ($('#ticket_credito_problematico_tipo').val() || '').toString().trim();
            var desc = ($('#ticket_credito_problematico_descripcion').val() || '').toString().trim();
            if (!tipo) { if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'Falta dato', text: 'Seleccione el tipo de solicitud.' }); return; }
            if (!desc) { if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'Falta dato', text: 'Escriba la descripción.' }); return; }
            enviarTicketSimple('modalTicketCreditoProblematico', '/sabueso/guardarTicketCreditoProblematico', { tipo_solicitud: tipo, descripcion: desc }, 'ticket_credito_problematico_adjunto', '#btnEnviarTicketCreditoProblematico');
        });
        $(document).on('click', '#btnEnviarTicketAclaracionCredito', function() {
            var tipo = ($('#ticket_aclaracion_tipo').val() || '').toString().trim();
            var desc = ($('#ticket_aclaracion_descripcion').val() || '').toString().trim();
            if (!tipo) { if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'Falta dato', text: 'Seleccione el tipo de aclaración.' }); return; }
            if (!desc) { if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'Falta dato', text: 'Escriba la descripción.' }); return; }
            enviarTicketSimple('modalTicketAclaracionCredito', '/sabueso/guardarTicketAclaracionCredito', { tipo_aclaracion: tipo, descripcion: desc }, 'ticket_aclaracion_adjunto', '#btnEnviarTicketAclaracionCredito');
        });
        function abrirModalLevantarTicketSabuesoDirecto() {
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
        }
    });

    function getTickets() {
        http.request({
            endpoint: "/sabueso/getTickets",
            metodo: "POST",
            onSuccess: function(resp) {
                var TEXTO_NO_APLICA = '<span class="text-muted">No aplica</span>';
                function escHtmlMenu(s) {
                    if (s == null || s === undefined) return '';
                    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/"/g, '&quot;');
                }
                var raw = resp.datos || [];
                var catSet = {};
                raw.forEach(function(x) {
                    var ck = (x.categoria_gestion || 'sabueso').toString().toLowerCase();
                    catSet[ck] = true;
                });
                var catsUnicas = Object.keys(catSet);
                var listadoMixto = catsUnicas.length > 1;
                /** Columnas DataTables menú Ticket (getColumnsConfig false): 8 tiempo, 9 DS, 10 dictamen/visto */
                var mostrarColsSabueso = raw.length === 0 || listadoMixto || (catsUnicas.length === 1 && catsUnicas[0] === 'sabueso');
                var datos = raw.map(function(t) {
                    var cat = (t.categoria_gestion || 'sabueso').toString().toLowerCase();
                    var esSabueso = cat === 'sabueso';
                    var mostrarNoAplicaSabuesoCols = !esSabueso && listadoMixto;
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
                    if (esSabueso && (t.dictamen_estado || '') === 'enviado_al_gestor') {
                        var vistoTexto = t.dictamen_fecha_visto ? (new Date(t.dictamen_fecha_visto).toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' }) + ' ' + new Date(t.dictamen_fecha_visto).toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' })) : 'No visto';
                        var iconoOjo = (t.dictamen_fecha_visto && (t.dictamen_fecha_visto + '').trim()) ? 'fa-eye' : 'fa-eye-slash';
                        var tituloOjo = (t.dictamen_fecha_visto && (t.dictamen_fecha_visto + '').trim()) ? ('Dictamen enviado. Visto: ' + vistoTexto) : 'Dictamen enviado. No visto. Clic para ver';
                        vistoHtml = '<span class="d-inline-flex align-items-center gap-1 justify-content-end btn-dictamen-ojito" role="button" tabindex="0" data-bs-toggle="tooltip" data-bs-title="' + (tituloOjo + '').replace(/"/g, '&quot;') + '" data-id-ticket="' + (t.id_ticket || '') + '"><i class="fa ' + iconoOjo + ' text-info small"></i></span>';
                    } else if (!esSabueso && mostrarNoAplicaSabuesoCols) {
                        vistoHtml = TEXTO_NO_APLICA;
                    }
                    var tiempoVisitarHtml = '\u2014';
                    if (!esSabueso) {
                        tiempoVisitarHtml = mostrarNoAplicaSabuesoCols ? TEXTO_NO_APLICA : '';
                    } else {
                    var fEnv = (t.dictamen_fecha_envio || '').trim();
                    var esNuevo = fEnv && new Date(fEnv) >= new Date('2026-03-09T00:00:00');
                    if ((t.dictamen_estado || '') === 'enviado_al_gestor' && esNuevo && fEnv) {
                        var envio = new Date(fEnv);
                        var fLim = (t.prorroga_fecha_limite || '').trim();
                        var esExt12h = t.prorroga_activa && fLim;
                        var extTipoRow = ((t.extension_countdown_tipo || '') + '').trim();
                        var esIntRow = extTipoRow === 'intensidad';
                        var fin = esExt12h ? new Date(fLim) : new Date(envio.getTime() + 12 * 60 * 60 * 1000);
                        var now = new Date();
                        var ms = fin - now;
                        var txtInicial = ms > 0 ? (Math.floor(ms / 3600000) + 'h ' + Math.floor((ms % 3600000) / 60000) + 'm') : 'Plazo vencido';
                        var clsExt12 = esExt12h ? (esIntRow ? ' dictamen-countdown-intensidad' : ' dictamen-countdown-prorroga') : '';
                        var dataLim = esExt12h ? (' data-fecha-limite="' + fLim.replace(/"/g, '&quot;') + '"') : '';
                        var dataExtTipo = esExt12h ? (' data-extension-tipo="' + (esIntRow ? 'intensidad' : 'prorroga') + '"') : '';
                        var iconTitle = esExt12h ? (esIntRow ? 'Intensidad +12h (2ª ventana)' : 'Prórroga +12h (2ª ventana)') : '';
                        var tipCuenta = esExt12h ? (esIntRow ? 'Intensidad +12h' : 'Prórroga +12h') : 'Ventana 12h';
                        var clockCls = esExt12h ? (esIntRow ? 'text-info' : 'text-warning') : 'text-info';
                        var supCls = esIntRow ? 'dictamen-intensidad-marca' : 'dictamen-prorroga-marca';
                        var iconBlock = esExt12h ? ('<span class="position-relative d-inline-flex align-items-baseline"><i class="fa-solid fa-clock ' + clockCls + ' small"></i><sup class="' + supCls + '" title="' + iconTitle + '">2</sup></span>') : ('<i class="fa-solid fa-clock text-info small"></i>');
                        var txtCountCls = esExt12h && esIntRow ? ' dictamen-countdown-text text-info' : ' dictamen-countdown-text';
                        tiempoVisitarHtml = '<span class="d-inline-flex align-items-center gap-1 dictamen-countdown cursor-pointer' + clsExt12 + '" role="button" tabindex="0" data-fecha-envio="' + fEnv.replace(/"/g, '&quot;') + '"' + dataLim + dataExtTipo + ' data-id-ticket="' + (t.id_ticket || '') + '" data-bs-toggle="tooltip" data-bs-title="' + tipCuenta + '">' + iconBlock + '<span class="' + txtCountCls.trim() + '">' + txtInicial + '</span></span>';
                    }
                    // Solo concatenar si hubo prórroga (backend envía '' cuando no; evita guión redundante)
                    var prHtml = (t.prorroga_otorgada && t.prorroga_html) ? t.prorroga_html : '';
                    if (prHtml && tiempoVisitarHtml !== '\u2014') {
                        tiempoVisitarHtml = '<div class="d-flex flex-column align-items-center">' + tiempoVisitarHtml + prHtml + '</div>';
                    } else if (prHtml) {
                        tiempoVisitarHtml = prHtml;
                    }
                    }
                    var creditoHtml;
                    if (esSabueso) {
                        creditoHtml = '<small>#' + (t.id_credito != null ? t.id_credito : '\u2014') + '</small>';
                    } else {
                        var idCredNum = t.id_credito != null ? parseInt(t.id_credito, 10) : 0;
                        if (idCredNum > 0) {
                            creditoHtml = '<small>#' + idCredNum + '</small>';
                        } else {
                            var refTxt = ((t.asunto || '') + '').trim() || ((t.tipo_categoria || '') + '').trim() || ((t.nota || '') + '').trim();
                            if (refTxt) {
                                var refCorta = refTxt.length > 80 ? refTxt.substring(0, 77) + '\u2026' : refTxt;
                                creditoHtml = '<small class="text-break" title="' + escHtmlMenu(refTxt) + '">' + escHtmlMenu(refCorta) + '</small>';
                            } else {
                                creditoHtml = TEXTO_NO_APLICA;
                            }
                        }
                    }
                    var dsHtml;
                    if (esSabueso) {
                        dsHtml = (t.ds_resultado_html != null && t.ds_resultado_html !== '') ? t.ds_resultado_html : '<span class="text-muted">\u2014</span>';
                    } else {
                        dsHtml = mostrarNoAplicaSabuesoCols ? TEXTO_NO_APLICA : '';
                    }
                    var gestionLabels = { sabueso: 'Sabueso', plantilla: 'Plantilla', atencion_cliente: 'Atención al cliente', validaciones: 'Validaciones', viaticos: 'Viáticos', beaticos: 'Viáticos', aplicaciones_de_pago: 'Aplicaciones de pago', credito_problematico: 'Crédito problemático', aclaracion_credito: 'Aclaración de crédito' };
                    var gestionTxt = gestionLabels[cat] || cat.replace(/_/g, ' ').replace(/\b\w/g, function(ch) { return ch.toUpperCase(); });
                    var icoG = { sabueso: 'fa-dog', plantilla: 'fa-file-lines', atencion_cliente: 'fa-headset', validaciones: 'fa-clipboard-check', viaticos: 'fa-receipt', aplicaciones_de_pago: 'fa-credit-card', credito_problematico: 'fa-triangle-exclamation', aclaracion_credito: 'fa-circle-question' };
                    var ic = icoG[cat] || 'fa-list';
                    var gestionBadge = '<span class="badge bg-label-primary small d-inline-flex align-items-center gap-1"><i class="fa-solid ' + ic + '" style="font-size:0.7rem;line-height:1" aria-hidden="true"></i>' + gestionTxt + '</span>';
                    var row = {
                        _fecha_creacion: (t.fecha_creacion || ''),
                        folio_tipo: '<div class="fw-semibold">' + (t.folio || '\u2014') + '</div><div class="small text-muted mt-1">' + (t.tipo_ticket_nombre || '\u2014') + '</div>',
                        gestion: gestionBadge,
                        estado: estadoBadge,
                        prioridad: prioridadBadge,
                        credito: creditoHtml,
                        fechas: '<div class="small d-flex align-items-center gap-1"><i class="fa fa-calendar-plus-o text-muted" style="width: 1rem;"></i><span>Creación: ' + fechaCreacion + '</span></div><div class="small text-muted d-flex align-items-center gap-1 mt-1"><i class="fa fa-calendar-times-o" style="width: 1rem;"></i><span>Vencimiento: ' + fechaVenc + '</span></div>',
                        tiempo_visitar: tiempoVisitarHtml,
                        ds_resultado: dsHtml,
                        dictamen_visto: vistoHtml,
                        acciones: '',
                        _id_ticket: t.id_ticket,
                        _categoria_gestion: cat,
                        _dictamen_estado: esSabueso ? (t.dictamen_estado || '') : '',
                        _dictamen_fecha_visto: esSabueso ? (t.dictamen_fecha_visto || '') : ''
                    };
                    return row;
                });
                var tabla = $('#tablaTickets').DataTable();
                tabla.clear().rows.add(datos).draw();
                try {
                    tabla.columns([8, 9, 10]).visible(mostrarColsSabueso);
                    tabla.columns.adjust();
                } catch (eCol) {}
                tabla.rows().every(function() {
                    var d = this.data();
                    var node = this.node();
                    if (!d || !node) return;
                    var catRow = (d._categoria_gestion || 'sabueso').toString().toLowerCase();
                    if (catRow === 'sabueso' && d._dictamen_estado === 'enviado_al_gestor') {
                        $(node).addClass('fila-dictamen-enviado').attr('data-id-ticket', d._id_ticket || '');
                        if (!d._dictamen_fecha_visto || (d._dictamen_fecha_visto + '').trim() === '') $(node).addClass('fila-dictamen-no-visto');
                        else $(node).removeClass('fila-dictamen-no-visto');
                    } else {
                        $(node).removeClass('fila-dictamen-enviado fila-dictamen-no-visto').removeAttr('data-id-ticket');
                    }
                });
                if (typeof actualizarCountdownsDictamen === 'function') actualizarCountdownsDictamen('#tablaTickets');
                $('#tablaTickets [data-bs-toggle="tooltip"]').tooltip();
            },
            onError: function() {
                var tabla = $('#tablaTickets').DataTable();
                tabla.clear().draw();
                try {
                    tabla.columns([8, 9, 10]).visible(true);
                    tabla.columns.adjust();
                } catch (eCol) {}
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
            var catRow = (d._categoria_gestion || 'sabueso').toString().toLowerCase();
            if (catRow === 'sabueso' && d._dictamen_estado === 'enviado_al_gestor') {
                $(node).addClass('fila-dictamen-enviado').attr('data-id-ticket', d._id_ticket || '');
                if (!d._dictamen_fecha_visto || (d._dictamen_fecha_visto + '').trim() === '') $(node).addClass('fila-dictamen-no-visto');
                else $(node).removeClass('fila-dictamen-no-visto');
            } else {
                $(node).removeClass('fila-dictamen-enviado fila-dictamen-no-visto').removeAttr('data-id-ticket');
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
                $('#modalDetalleDictamenTipo').text(typeof window.etiquetaTipoDictamenSabueso === 'function' ? window.etiquetaTipoDictamenSabueso(dm.tipo) : (dm.tipo || '\u2014'));
                var descRaw = (dm.descripcion_base !== undefined ? dm.descripcion_base : dm.descripcion) || '';
                var descMostrar = (String(descRaw).trim() !== '') ? descRaw : ((typeof window.esTipoDictamenIlocalizable === 'function' && window.esTipoDictamenIlocalizable(dm.tipo)) ? '\u2014 (sin comentarios; dictamen ILOCALIZABLE)' : '\u2014');
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
            if (!oldInput || oldInput.getAttribute('type') === 'hidden') return;
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
            intentarAbrirModalLevantarTicket(function() {
                $('#modal_id_credito').val(idCred);
                $('#modalDatosCredito').modal('hide');
                // Ir directo al formulario Sabueso con el crédito ya rellenado
                var el = document.getElementById('modalLevantarTicket');
                if (el && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    bootstrap.Modal.getOrCreateInstance(el).show();
                } else if (typeof $ !== 'undefined' && $.fn.modal) {
                    $('#modalLevantarTicket').modal('show');
                }
            }, true);
        }
    }

    /**
     * Domingo 12:01 p.m. – Lunes 7:00 a.m. CDMX: no abrir modal; mensaje desde servidor.
     */
    function intentarAbrirModalLevantarTicket(abrirModal, aplicarVentanaSabueso) {
        if (aplicarVentanaSabueso !== true) {
            if (typeof abrirModal === 'function') abrirModal();
            return;
        }
        if (typeof http === 'undefined' || typeof http.request !== 'function') {
            if (typeof abrirModal === 'function') abrirModal();
            return;
        }
        http.request({
            endpoint: '/sabueso/ticketLevantarPermitido',
            metodo: 'POST',
            data: JSON.stringify({ categoria: 'sabueso' }),
            contentType: 'application/json',
            processData: false,
            showLoader: false,
            onSuccess: function(resp) {
                if (resp && resp.permitido === false && resp.mensaje) {
                    esperandoCatalogosDesdeContinuar = false;
                    if (typeof Swal !== 'undefined') {
                        if (typeof Swal.isVisible === 'function' && Swal.isVisible()) Swal.close();
                        Swal.fire({
                            icon: 'info',
                            title: 'Registro no disponible',
                            html: '<p class="mb-0 text-start" style="line-height:1.5;">' + String(resp.mensaje).replace(/</g, '&lt;') + '</p>',
                            confirmButtonText: 'Entendido'
                        });
                    } else {
                        alert(resp.mensaje);
                    }
                    return;
                }
                if (typeof abrirModal === 'function') abrirModal();
            },
            onError: function() {
                if (typeof abrirModal === 'function') abrirModal();
            }
        });
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
                if ($('#modal_id_prioridad').length && $('#modal_id_prioridad').is('select')) {
                    $('#modal_id_prioridad').html(options(prioridades));
                }
                $('#modal_id_origen_ticket').html(options(origenes));
                var origenSistema = origenes.filter(function(o) { return (o.nombre || '').toLowerCase().indexOf('sistema') !== -1; })[0];
                if (origenSistema && origenSistema.id) $('#modal_id_origen_ticket').val(origenSistema.id);
                else if (origenes.length > 0 && origenes[0].id) $('#modal_id_origen_ticket').val(origenes[0].id);
                setTimeout(configurarFechaVencimiento, 150);
                if (esperandoCatalogosDesdeContinuar && typeof Swal !== 'undefined' && typeof Swal.isVisible === 'function' && Swal.isVisible()) Swal.close();
                esperandoCatalogosDesdeContinuar = false;
            },
            onError: function() {
                if (esperandoCatalogosDesdeContinuar && typeof Swal !== 'undefined' && typeof Swal.isVisible === 'function' && Swal.isVisible()) Swal.close();
                esperandoCatalogosDesdeContinuar = false;
            }
        });
    }

    function enviarLevantarTicket() {
        // Prioridad siempre Alta y vencimiento +24h en backend; no se envían desde el formulario
        var payload = {
            categoria_gestion: 'sabueso',
            id_tipo_ticket: $('#modal_id_tipo_ticket').val(),
            id_origen_ticket: $('#modal_id_origen_ticket').val(),
            id_credito: ($('#modal_id_credito').val() || '').toString().trim(),
            descripcion_inicial: ($('#modal_descripcion_inicial').val() || '').toString().trim()
        };
        if (!payload.id_tipo_ticket || !payload.id_origen_ticket || !payload.descripcion_inicial) {
            Swal.fire({ icon: 'warning', title: 'Faltan datos', text: 'Complete tipo, origen y descripción.' });
            return;
        }
        if (!payload.id_credito || isNaN(parseInt(payload.id_credito, 10)) || parseInt(payload.id_credito, 10) < 1) {
            Swal.fire({ icon: 'warning', title: 'ID de crédito obligatorio', text: 'Debe indicar un ID de crédito válido.' });
            return;
        }
        if (enviandoTicket) return;

        function procederACrearTicket() {
            enviandoTicket = true;
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Registrando ticket',
                    text: 'Se está registrando el ticket. Espere un momento...',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: function() { if (typeof Swal !== 'undefined' && typeof Swal.showLoading === 'function') Swal.showLoading(); }
                });
            }
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
                    $('#modal_id_tipo_ticket, #modal_id_origen_ticket').val('');
                    if ($('#modal_id_prioridad').is('select')) $('#modal_id_prioridad').val('');
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

        http.request({
            endpoint: "/sabueso/verificarCreditoDuplicadoCreador",
            metodo: "POST",
            data: JSON.stringify({ id_credito: parseInt(payload.id_credito, 10) }),
            contentType: "application/json",
            processData: false,
            showLoader: false,
            onSuccess: function(resp) {
                if (resp.success && resp.ya_tiene) {
                    var tk = resp.ticket_existente || {};
                    var idTk = (tk.id_ticket != null) ? String(tk.id_ticket) : '—';
                    var fechaTkTxt = '—';
                    if (tk.fecha_creacion) {
                        try {
                            fechaTkTxt = new Date(tk.fecha_creacion).toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' });
                        } catch (e) { fechaTkTxt = tk.fecha_creacion; }
                    }
                    Swal.fire({
                        icon: 'warning',
                        title: 'Ticket con este crédito',
                        html: 'Usted ya tiene un ticket registrado con el ID de crédito <strong>' + payload.id_credito + '</strong>.<br>' +
                            '<span class="text-muted small">Ticket ID: <strong>' + idTk + '</strong> · Fecha de apertura: <strong>' + fechaTkTxt + '</strong></span><br><br>' +
                            'Por favor verifique que no se trate de la misma petición o la misma gestión antes de continuar.<br><br>¿Desea proceder con el registro de un nuevo ticket?',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, proceder',
                        cancelButtonText: 'No, cancelar',
                        confirmButtonColor: '#0d6efd',
                        cancelButtonColor: '#6c757d'
                    }).then(function(confirmResult) {
                        if (confirmResult && confirmResult.isConfirmed) procederACrearTicket();
                    });
                } else {
                    procederACrearTicket();
                }
            },
            onError: function() {
                procederACrearTicket();
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
