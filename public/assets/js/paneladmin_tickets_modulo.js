/**
 * Panel Admin por módulo (validaciones, viáticos, etc.) — URL y script propios.
 */
(function () {
    var SEL_TABLA = '#tablaTicketsModulo';

    function cfg() {
        return window.TICKETS_MODULO_CONFIG || { categoria: '', labelBadge: '', formularios: false };
    }

    function attrEsc(s) {
        if (s == null || s === undefined) return '';
        var x = (s + '').split('&').join('&amp;').split('<').join('&lt;');
        return x.split('"').join('&quot;');
    }

    function aplicarTitulosColumnas(dt, T) {
        if (!dt || !T) return;
        function sc(i, t) {
            try {
                var h = dt.column(i).header();
                if (h && h.length) h[0].innerHTML = t;
            } catch (e) {}
        }
        sc(2, T.folio);
        sc(3, T.estado);
        sc(4, T.prioridad);
        sc(5, T.ref);
        sc(6, T.fechas);
        sc(7, T.creador);
        sc(8, T.asignado);
        sc(9, T.tiempo);
        sc(10, T.ds);
    }

    function mapRow(t) {
        var CAT = (cfg().categoria || '').trim();
        var catLabel = (cfg().labelBadge || CAT || '—').replace(/</g, '&lt;');
        var fechaCreacion = t.fecha_creacion
            ? new Date(t.fecha_creacion).toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' })
            : '—';
        var fechaVenc = t.fecha_vencimiento
            ? new Date(t.fecha_vencimiento).toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' })
            : '—';
        var prioridadNombre = (t.prioridad_nombre || '').toLowerCase();
        var prioridadBadge = '<span class="badge bg-label-secondary">' + (t.prioridad_nombre || '—') + '</span>';
        if (prioridadNombre.indexOf('alta') !== -1) prioridadBadge = '<span class="badge bg-danger text-white">' + (t.prioridad_nombre || '—') + '</span>';
        else if (prioridadNombre.indexOf('medio') !== -1 || prioridadNombre.indexOf('media') !== -1)
            prioridadBadge = '<span class="badge" style="background-color:#fd7e14;color:#fff;">' + (t.prioridad_nombre || '—') + '</span>';
        else if (prioridadNombre.indexOf('bajo') !== -1 || prioridadNombre.indexOf('baja') !== -1)
            prioridadBadge = '<span class="badge" style="background-color:#ffc107;color:#212529;">' + (t.prioridad_nombre || '—') + '</span>';
        else if (prioridadNombre.indexOf('sin prioridad') !== -1)
            prioridadBadge = '<span class="badge bg-secondary" style="background-color:#6c757d!important;color:#fff;">' + (t.prioridad_nombre || '—') + '</span>';
        var estadoBadge =
            t.asignado_nombre && (t.asignado_nombre + '').trim()
                ? '<span class="badge bg-success text-white">Asignado</span>'
                : '<span class="badge bg-label-secondary">Abierto</span>';
        var catK = ((t.categoria_gestion || CAT || 'sabueso') + '').toLowerCase().trim();
        var icoM = {
            sabueso: 'fa-dog',
            plantilla: 'fa-file-lines',
            atencion_cliente: 'fa-headset',
            validaciones: 'fa-clipboard-check',
            viaticos: 'fa-receipt',
            aplicaciones_de_pago: 'fa-credit-card',
            credito_problematico: 'fa-triangle-exclamation',
            aclaracion_credito: 'fa-circle-question'
        };
        var icCat = icoM[catK] || 'fa-list';
        var folioTipoHtml =
            '<div class="fw-semibold">' +
            (t.folio || '—') +
            '</div><div class="small text-muted mt-1">' +
            (t.tipo_ticket_nombre || '—') +
            '</div><div class="mt-1 d-inline-flex align-items-center gap-1 flex-nowrap"><i class="fa-solid ' +
            icCat +
            ' text-primary" style="font-size:0.78rem;line-height:1;flex-shrink:0;margin-top:1px" aria-hidden="true"></i><span class="badge bg-label-primary small mb-0">' +
            catLabel +
            '</span></div>';
        var creditoVal =
            t.id_credito != null && t.id_credito > 0 ? '#' + t.id_credito : t.asunto || t.tipo_categoria || '—';
        var creditoHtml = '<small>' + String(creditoVal).replace(/&/g, '&amp;').replace(/</g, '&lt;') + '</small>';
        var acciones =
            '<div class="d-flex flex-column gap-1 align-items-stretch" style="min-width:2.5rem;">' +
            '<button type="button" class="btn btn-sm btn-secondary" data-tm-cerrar="' +
            t.id_ticket +
            '" title="Cerrar ticket"><i class="fa fa-minus"></i></button>' +
            '<button type="button" class="btn btn-sm btn-danger" data-tm-eliminar="' +
            t.id_ticket +
            '" title="Eliminar ticket"><i class="fa fa-trash"></i></button></div>';
        return {
            _fecha_creacion: t.fecha_creacion || '',
            folio_tipo: folioTipoHtml,
            estado: estadoBadge,
            prioridad: prioridadBadge,
            credito: creditoHtml,
            fechas:
                '<div class="small d-flex align-items-center gap-1"><i class="fa fa-calendar-plus-o text-muted" style="width: 1rem;"></i><span>Creación: ' +
                fechaCreacion +
                '</span></div><div class="small text-muted d-flex align-items-center gap-1 mt-1"><i class="fa fa-calendar-times-o" style="width: 1rem;"></i><span>Vencimiento: ' +
                fechaVenc +
                '</span></div>',
            creador: '<small class="d-flex align-items-center gap-1"><i class="fa fa-user"></i>' + (t.creador_nombre || '—') + '</small>',
            asignado:
                t.asignado_nombre && t.asignado_nombre.trim()
                    ? '<small class="d-flex align-items-center gap-1"><i class="fa fa-user-check text-success"></i>' + t.asignado_nombre + '</small>'
                    : '<span class="text-muted">—</span>',
            tiempo_visitar: '—',
            ds_resultado: t.ds_resultado_html != null && t.ds_resultado_html !== '' ? t.ds_resultado_html : '—',
            dictamen_visto: '',
            acciones: acciones,
            _id_ticket: t.id_ticket
        };
    }

    window.getTicketsModuloPanel = function () {
        var CAT = (cfg().categoria || '').trim();
        if (!CAT) return;
        var esPrimera = window._ticketsModuloPanelPrimeraCarga === true;
        if (esPrimera && typeof showWait === 'function') showWait();
        http.request({
            endpoint: '/sabueso/getTicketsPanelAdmin',
            metodo: 'POST',
            data: JSON.stringify({ filtros: { categoria_gestion: CAT } }),
            contentType: 'application/json',
            processData: false,
            showLoader: false,
            onSuccess: function (resp) {
                if (esPrimera) window._ticketsModuloPanelPrimeraCarga = false;
                var datos = (resp.datos || []).map(mapRow);
                var tabla = $(SEL_TABLA).DataTable();
                var pagAntes = tabla.page.info().page;
                tabla.clear().rows.add(datos);
                var np = tabla.page.info().pages;
                if (pagAntes >= np) pagAntes = Math.max(0, np - 1);
                if (esPrimera) {
                    tabla.one('draw.dt.tmPrimera', function () {
                        $('#wrapTablaTicketsModulo').show();
                        document.body.classList.remove('panel-tickets-modulo-cargando');
                        if (typeof Swal !== 'undefined') Swal.close();
                    });
                }
                tabla.page(pagAntes).draw(false);
                aplicarTitulosColumnas(tabla, window.TICKETS_MODULO_TITULOS);
                tabla.columns([9, 10, 11]).visible(false);
                $(SEL_TABLA + ' [data-bs-toggle="tooltip"]').tooltip();
            },
            onError: function () {
                window._ticketsModuloPanelPrimeraCarga = false;
                $('#wrapTablaTicketsModulo').show();
                document.body.classList.remove('panel-tickets-modulo-cargando');
                if (typeof Swal !== 'undefined') Swal.close();
                $(SEL_TABLA).DataTable().clear().draw();
            }
        });
    };

    function cerrarTicket(id) {
        if (!id || typeof Swal === 'undefined') return;
        Swal.fire({
            title: '¿Cerrar ticket?',
            text: 'El ticket quedará cerrado.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#fd7e14',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, cerrar'
        }).then(function (res) {
            if (!res.isConfirmed) return;
            http.request({
                endpoint: '/sabueso/cerrarTicket',
                metodo: 'POST',
                data: JSON.stringify({ id_ticket: id }),
                contentType: 'application/json',
                processData: false,
                onSuccess: function (r) {
                    Swal.fire({ icon: 'success', title: 'Cerrado', text: r.mensaje || 'Listo.' });
                    window.getTicketsModuloPanel();
                },
                onError: function (e) {
                    Swal.fire({ icon: 'error', title: 'Error', text: (e && e.mensaje) || 'No se pudo cerrar.' });
                }
            });
        });
    }

    function eliminarTicket(id) {
        if (!id || typeof Swal === 'undefined') return;
        Swal.fire({
            title: '¿Eliminar ticket?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar'
        }).then(function (res) {
            if (!res.isConfirmed) return;
            http.request({
                endpoint: '/sabueso/eliminarTicket',
                metodo: 'POST',
                data: JSON.stringify({ id_ticket: id }),
                contentType: 'application/json',
                processData: false,
                onSuccess: function (r) {
                    Swal.fire({ icon: 'success', title: 'Eliminado', text: r.mensaje || 'Listo.' });
                    window.getTicketsModuloPanel();
                },
                onError: function (e) {
                    Swal.fire({ icon: 'error', title: 'Error', text: (e && e.mensaje) || 'No se pudo eliminar.' });
                }
            });
        });
    }

    function runInit() {
        if (typeof window.$ === 'undefined' || typeof window.TICKETS_MODULO_COLUMNS === 'undefined' || !cfg().categoria) {
            setTimeout(runInit, 50);
            return;
        }
        document.body.classList.add('panel-tickets-modulo-cargando');
        window._ticketsModuloPanelPrimeraCarga = true;
        configuraTabla(SEL_TABLA, {
            registrosPorPagina: 10,
            order: [[1, 'desc']],
            columns: window.TICKETS_MODULO_COLUMNS
        });
        var dt;
        try {
            dt = $(SEL_TABLA).DataTable();
        } catch (e) {}
        if (dt && window.TICKETS_MODULO_TITULOS) aplicarTitulosColumnas(dt, window.TICKETS_MODULO_TITULOS);
        if (dt) dt.columns([9, 10, 11]).visible(false);
        $(document).on('click', SEL_TABLA + ' [data-tm-cerrar]', function () {
            cerrarTicket(parseInt($(this).attr('data-tm-cerrar'), 10));
        });
        $(document).on('click', SEL_TABLA + ' [data-tm-eliminar]', function () {
            eliminarTicket(parseInt($(this).attr('data-tm-eliminar'), 10));
        });
        window.getTicketsModuloPanel();
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', runInit);
    else runInit();

    if (cfg().formularios) {
        (function formulariosValidacion() {
            function wait$() {
                if (typeof window.$ === 'undefined') {
                    setTimeout(wait$, 50);
                    return;
                }
                $(function () {
                    var TIPO_LABEL = { abierta: 'Abierta', cerrada: 'Cerrada', multiple: 'Múltiple', si_no: 'Sí / No', escala: 'Escala 1–5', fecha: 'Fecha', numero: 'Número' };
                    function fpModal(id) {
                        var el = document.getElementById(id);
                        if (!el || typeof bootstrap === 'undefined' || !bootstrap.Modal) return null;
                        return bootstrap.Modal.getOrCreateInstance(el);
                    }
                    function fpCargarDesdeServidor(callback) {
                        if (typeof http === 'undefined') {
                            if (callback) callback([]);
                            return;
                        }
                        http.request({
                            endpoint: '/validaciones/getPreguntasFormulario',
                            metodo: 'GET',
                            onSuccess: function (resp) {
                                var datos = Array.isArray(resp.datos) ? resp.datos : [];
                                if (callback) callback(datos);
                            },
                            onError: function () {
                                if (callback) callback([]);
                            }
                        });
                    }
                    function fpEsc(s) {
                        if (s == null) return '';
                        return String(s)
                            .replace(/&/g, '&amp;')
                            .replace(/</g, '&lt;')
                            .replace(/>/g, '&gt;')
                            .replace(/"/g, '&quot;');
                    }
                    // Scrim encima del modal padre cuando se abre Tipo de pregunta o Editar pregunta
                    var parentFormEl = document.getElementById('modalFormulariosValidacion');
                    var scrimFormEl = document.getElementById('scrimFormulariosModulo');
                    var fpChildModalIds = ['modalTipoPreguntaValidacion', 'modalEditarPreguntaValidacion'];
                    function fpIsAnyChildOpen() {
                        return fpChildModalIds.some(function (id) {
                            var el = document.getElementById(id);
                            return el && el.classList.contains('show');
                        });
                    }
                    fpChildModalIds.forEach(function (id) {
                        var el = document.getElementById(id);
                        if (!el) return;
                        el.addEventListener('shown.bs.modal', function () {
                            if (parentFormEl) parentFormEl.classList.add('modal-formularios-below-scrim');
                            // Scrim en body antes del hijo; hijo al final de body y z-index alto para quedar siempre encima
                            if (scrimFormEl) {
                                scrimFormEl.style.display = 'block';
                                if (scrimFormEl.parentNode !== document.body) document.body.appendChild(scrimFormEl);
                                document.body.insertBefore(scrimFormEl, el);
                            }
                            document.body.appendChild(el);
                            el.style.setProperty('z-index', '1100', 'important');
                        });
                        el.addEventListener('hidden.bs.modal', function () {
                            if (!fpIsAnyChildOpen()) {
                                if (scrimFormEl) scrimFormEl.style.display = 'none';
                                if (parentFormEl) parentFormEl.classList.remove('modal-formularios-below-scrim');
                            }
                        });
                    });
                    if (parentFormEl) {
                        parentFormEl.addEventListener('hidden.bs.modal', function () {
                            if (scrimFormEl) scrimFormEl.style.display = 'none';
                            parentFormEl.classList.remove('modal-formularios-below-scrim');
                        });
                    }

                    function fpRenderSub(p) {
                        var sub = '';
                        if (p.tipo === 'cerrada' && p.opciones && p.opciones.length)
                            sub = '<div class="small text-muted mt-1">' + p.opciones.map(function (o) { return fpEsc(typeof o === 'string' ? o : o.texto || ''); }).join(' · ') + '</div>';
                        else if (p.tipo === 'multiple' && p.opciones && p.opciones.length)
                            sub = '<div class="small text-muted mt-1">' + p.opciones.length + ' opciones</div>';
                        else if (p.tipo === 'escala')
                            sub = '<div class="small text-muted mt-1">1–5' + (p.escala_min || p.escala_max ? ' · ' + fpEsc((p.escala_min || '') + ' → ' + (p.escala_max || '')) : '') + '</div>';
                        else if (p.tipo === 'numero' && (p.num_min != null || p.num_max != null)) sub = '<div class="small text-muted mt-1">Rango numérico</div>';
                        return sub;
                    }
                    function fpRefrescarListaPrincipal(datos) {
                        if (datos === undefined) {
                            fpCargarDesdeServidor(fpRefrescarListaPrincipal);
                            return;
                        }
                        var predefinidas = datos.filter(function (p) { return p.es_predefinida === 1 || p.es_predefinida === true; });
                        var personalizadas = datos.filter(function (p) { return p.es_predefinida === 0 || p.es_predefinida === false; });
                        var $pre = $('#listaPreguntasPredefinidasValidacion');
                        if (!predefinidas.length)
                            $pre.html('<li class="list-group-item small text-muted fst-italic py-3 text-center">(Sin preguntas predefinidas aún)</li>');
                        else {
                            $pre.empty();
                            predefinidas.forEach(function (p) {
                                var checked = p.activa === 1 || p.activa === true ? ' checked' : '';
                                var sub = fpRenderSub(p);
                                $pre.append(
                                    '<li class="list-group-item d-flex align-items-start gap-2">' +
                                    '<input type="checkbox" class="form-check-input mt-1 fp-toggle-activa" data-id="' + (p.id || '') + '"' + checked + ' title="Incluir en cuestionario">' +
                                    '<div class="min-w-0 flex-grow-1"><span class="badge bg-label-success me-1">' + fpEsc(TIPO_LABEL[p.tipo] || p.tipo) + '</span><span class="small">' +
                                    fpEsc((p.texto || '').substring(0, 200)) + ((p.texto || '').length > 200 ? '…' : '') + '</span>' + sub + '</div></li>'
                                );
                            });
                        }
                        var $ul = $('#listaPreguntasNuevasValidacion');
                        $ul.empty();
                        if (!personalizadas.length) {
                            $('#formValidacionSinNuevas').show();
                        } else {
                            $('#formValidacionSinNuevas').hide();
                            personalizadas.forEach(function (p) {
                                var checked = p.activa === 1 || p.activa === true ? ' checked' : '';
                                var sub = fpRenderSub(p);
                                $ul.append(
                                    '<li class="list-group-item d-flex justify-content-between align-items-start gap-2">' +
                                    '<div class="d-flex align-items-start gap-2 min-w-0 flex-grow-1">' +
                                    '<input type="checkbox" class="form-check-input mt-1 fp-toggle-activa" data-id="' + (p.id || '') + '"' + checked + ' title="Incluir en cuestionario">' +
                                    '<div class="min-w-0"><span class="badge bg-label-success me-1">' + fpEsc(TIPO_LABEL[p.tipo] || p.tipo) + '</span><span class="small">' +
                                    fpEsc((p.texto || '').substring(0, 200)) + ((p.texto || '').length > 200 ? '…' : '') + '</span>' + sub + '</div></div>' +
                                    '<button type="button" class="btn btn-sm btn-outline-danger flex-shrink-0 fp-quitar-pregunta" data-id="' + (p.id || '') + '" title="Eliminar"><i class="fa-solid fa-trash"></i></button></li>'
                                );
                            });
                        }
                    }
                    function fpResetEditor() {
                        $('#fpEditorError').addClass('d-none').text('');
                        $('#fpTipoActual').val('');
                        $('.fp-editor-section').addClass('d-none');
                        $('#fpTextoAbierta,#fpTextoCerrada,#fpTextoMultiple,#fpTextoSiNo,#fpTextoEscala,#fpTextoFecha,#fpTextoNumero').val('');
                        $('#fpEscalaMin,#fpEscalaMax,#fpNumMin,#fpNumMax').val('');
                        $('#fpListaOpcionesCerrada,#fpListaOpcionesMultiple').empty();
                    }
                    function fpRowCerrada(val, checked) {
                        var $r = $(
                            '<div class="input-group input-group-sm fp-row-cerrada mb-1"><span class="input-group-text p-2"><input class="form-check-input mt-0 fp-radio-correcta" type="radio" name="fpCorrectaCerrada"' +
                                (checked ? ' checked' : '') +
                                '></span><input type="text" class="form-control fp-input-op-cerrada" placeholder="Texto"><button class="btn btn-outline-secondary fp-remove-op" type="button"><i class="fa-solid fa-times"></i></button></div>'
                        );
                        if (val) $r.find('.fp-input-op-cerrada').val(val);
                        return $r;
                    }
                    function fpRowMultiple(val, checked) {
                        var $r = $(
                            '<div class="input-group input-group-sm fp-row-multiple mb-1"><span class="input-group-text p-2"><input class="form-check-input mt-0 fp-check-mult" type="checkbox"' +
                                (checked ? ' checked' : '') +
                                '></span><input type="text" class="form-control fp-input-op-mult" placeholder="Opción"><button class="btn btn-outline-secondary fp-remove-op" type="button"><i class="fa-solid fa-times"></i></button></div>'
                        );
                        if (val) $r.find('.fp-input-op-mult').val(val);
                        return $r;
                    }
                    $('#fpAgregarOpcionCerrada').on('click', function () {
                        $('#fpListaOpcionesCerrada').append(fpRowCerrada('', false));
                    });
                    $('#fpAgregarOpcionMultiple').on('click', function () {
                        $('#fpListaOpcionesMultiple').append(fpRowMultiple('', false));
                    });
                    $(document).on('click', '#modalEditarPreguntaValidacion .fp-remove-op', function () {
                        $(this).closest('.input-group').remove();
                    });
                    function fpAbrirEditor(tipo) {
                        fpResetEditor();
                        $('#fpTipoActual').val(tipo);
                        var tit = {
                            abierta: 'Pregunta abierta',
                            cerrada: 'Pregunta cerrada (una correcta)',
                            multiple: 'Selección múltiple',
                            si_no: 'Pregunta Sí / No',
                            escala: 'Pregunta con escala 1–5',
                            fecha: 'Pregunta con fecha',
                            numero: 'Pregunta numérica'
                        };
                        $('#modalEditarPreguntaValidacionLabel').text(tit[tipo] || 'Pregunta');
                        if (tipo === 'abierta') $('#fpWrapAbierta').removeClass('d-none');
                        else if (tipo === 'cerrada') {
                            $('#fpWrapCerrada').removeClass('d-none');
                            $('#fpListaOpcionesCerrada').append(fpRowCerrada('', true)).append(fpRowCerrada('', false));
                        } else if (tipo === 'multiple') {
                            $('#fpWrapMultiple').removeClass('d-none');
                            $('#fpListaOpcionesMultiple').append(fpRowMultiple('', true)).append(fpRowMultiple('', false));
                        } else if (tipo === 'si_no') $('#fpWrapSiNo').removeClass('d-none');
                        else if (tipo === 'escala') $('#fpWrapEscala').removeClass('d-none');
                        else if (tipo === 'fecha') $('#fpWrapFecha').removeClass('d-none');
                        else if (tipo === 'numero') $('#fpWrapNumero').removeClass('d-none');
                        var mTipo = fpModal('modalTipoPreguntaValidacion');
                        if (mTipo) mTipo.hide();
                        setTimeout(function () {
                            var m = fpModal('modalEditarPreguntaValidacion');
                            if (m) m.show();
                        }, 350);
                    }
                    $(document).on('click', '.fp-btn-tipo', function () {
                        fpAbrirEditor($(this).attr('data-fp-tipo'));
                    });
                    $('#btnFormValidacionNuevasPreguntas').on('click', function () {
                        var m = fpModal('modalTipoPreguntaValidacion');
                        if (m) m.show();
                    });
                    $('#btnPanelAdminFormulariosValidacion').on('click', function () {
                        fpCargarDesdeServidor(function (datos) {
                            fpRefrescarListaPrincipal(datos);
                        });
                        var m = fpModal('modalFormulariosValidacion');
                        if (m) m.show();
                    });
                    $(document).on('change', '.fp-toggle-activa', function () {
                        var id = parseInt($(this).attr('data-id'), 10);
                        var activa = $(this).prop('checked') ? 1 : 0;
                        if (isNaN(id) || id < 1 || typeof http === 'undefined') return;
                        http.request({
                            endpoint: '/validaciones/togglePreguntaFormulario',
                            metodo: 'POST',
                            data: JSON.stringify({ id: id, activa: activa }),
                            contentType: 'application/json',
                            processData: false,
                            onSuccess: function () {
                                if (typeof Swal !== 'undefined') Swal.fire({ icon: 'success', title: activa ? 'Incluida' : 'Desmarcada', timer: 1200, showConfirmButton: false });
                            },
                            onError: function (e) {
                                if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'Error', text: (e && e.mensaje) || 'No se pudo actualizar.' });
                                fpCargarDesdeServidor(fpRefrescarListaPrincipal);
                            }
                        });
                    });
                    $(document).on('click', '.fp-quitar-pregunta', function () {
                        var id = parseInt($(this).attr('data-id'), 10);
                        if (isNaN(id) || id < 1 || typeof http === 'undefined') return;
                        var doEliminar = function () {
                            http.request({
                                endpoint: '/validaciones/eliminarPreguntaFormulario',
                                metodo: 'POST',
                                data: JSON.stringify({ id: id }),
                                contentType: 'application/json',
                                processData: false,
                                onSuccess: function () {
                                    if (typeof Swal !== 'undefined') Swal.fire({ icon: 'success', title: 'Eliminada' });
                                    fpCargarDesdeServidor(fpRefrescarListaPrincipal);
                                },
                                onError: function (e) {
                                    if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'Error', text: (e && e.mensaje) || 'No se pudo eliminar.' });
                                }
                            });
                        };
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ title: '¿Eliminar?', text: 'Esta pregunta personalizada se quitará del listado.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, eliminar' }).then(function (r) {
                                if (r.isConfirmed) doEliminar();
                            });
                        } else {
                            if (confirm('¿Eliminar esta pregunta personalizada?')) doEliminar();
                        }
                    });
                    function fpCerrarEditorVolverTipo() {
                        var mEd = fpModal('modalEditarPreguntaValidacion');
                        if (mEd) mEd.hide();
                        setTimeout(function () {
                            var m = fpModal('modalTipoPreguntaValidacion');
                            if (m) m.show();
                        }, 350);
                    }
                    $('#fpBtnCancelarEditor, #fpBtnCerrarEditorX').on('click', fpCerrarEditorVolverTipo);
                    $('#fpBtnGuardarPregunta').on('click', function () {
                        var tipo = ($('#fpTipoActual').val() || '').trim();
                        var $err = $('#fpEditorError');
                        $err.addClass('d-none').text('');
                        var obj = { tipo: tipo, texto: '', es_predefinida: 0 };
                        if (tipo === 'abierta') {
                            obj.texto = ($('#fpTextoAbierta').val() || '').trim();
                            if (!obj.texto) {
                                $err.removeClass('d-none').text('Escriba el enunciado.');
                                return;
                            }
                        } else if (tipo === 'cerrada') {
                            obj.texto = ($('#fpTextoCerrada').val() || '').trim();
                            var rowsC = [];
                            $('#fpListaOpcionesCerrada .fp-row-cerrada').each(function () {
                                var tx = ($(this).find('.fp-input-op-cerrada').val() || '').trim();
                                if (tx) rowsC.push({ t: tx, cor: $(this).find('.fp-radio-correcta').prop('checked') });
                            });
                            if (!obj.texto) {
                                $err.removeClass('d-none').text('Escriba la pregunta.');
                                return;
                            }
                            if (rowsC.length < 2) {
                                $err.removeClass('d-none').text('Mínimo dos opciones.');
                                return;
                            }
                            var ic = rowsC.map(function (r, i) { return r.cor ? i : -1; }).filter(function (i) { return i >= 0; });
                            if (!ic.length) {
                                $err.removeClass('d-none').text('Marque la opción correcta.');
                                return;
                            }
                            obj.opciones = rowsC.map(function (r) { return r.t; });
                            obj.indice_correcto = ic[0];
                        } else if (tipo === 'multiple') {
                            obj.texto = ($('#fpTextoMultiple').val() || '').trim();
                            var rowsM = [];
                            $('#fpListaOpcionesMultiple .fp-row-multiple').each(function () {
                                var tx = ($(this).find('.fp-input-op-mult').val() || '').trim();
                                if (tx) rowsM.push({ t: tx, cor: $(this).find('.fp-check-mult').prop('checked') });
                            });
                            if (!obj.texto || rowsM.length < 2) {
                                $err.removeClass('d-none').text('Pregunta y al menos dos opciones.');
                                return;
                            }
                            var idxCor = [];
                            rowsM.forEach(function (r, i) { if (r.cor) idxCor.push(i); });
                            if (!idxCor.length) {
                                $err.removeClass('d-none').text('Marque al menos una correcta.');
                                return;
                            }
                            obj.opciones = rowsM.map(function (r) { return r.t; });
                            obj.indices_correctos = idxCor;
                        } else if (tipo === 'si_no') {
                            obj.texto = ($('#fpTextoSiNo').val() || '').trim();
                            if (!obj.texto) { $err.removeClass('d-none').text('Escriba la pregunta.'); return; }
                        } else if (tipo === 'escala') {
                            obj.texto = ($('#fpTextoEscala').val() || '').trim();
                            obj.escala_min = ($('#fpEscalaMin').val() || '').trim();
                            obj.escala_max = ($('#fpEscalaMax').val() || '').trim();
                            if (!obj.texto) { $err.removeClass('d-none').text('Escriba la pregunta.'); return; }
                        } else if (tipo === 'fecha') {
                            obj.texto = ($('#fpTextoFecha').val() || '').trim();
                            if (!obj.texto) { $err.removeClass('d-none').text('Escriba la pregunta.'); return; }
                        } else if (tipo === 'numero') {
                            obj.texto = ($('#fpTextoNumero').val() || '').trim();
                            var nmin = $('#fpNumMin').val();
                            var nmax = $('#fpNumMax').val();
                            if (nmin !== '' && !isNaN(parseFloat(nmin))) obj.num_min = parseFloat(nmin);
                            if (nmax !== '' && !isNaN(parseFloat(nmax))) obj.num_max = parseFloat(nmax);
                            if (!obj.texto) { $err.removeClass('d-none').text('Escriba la pregunta.'); return; }
                            if (obj.num_min != null && obj.num_max != null && obj.num_min > obj.num_max) {
                                $err.removeClass('d-none').text('Mínimo no puede ser mayor que máximo.');
                                return;
                            }
                        } else {
                            $err.removeClass('d-none').text('Tipo no válido.');
                            return;
                        }
                        if (typeof http === 'undefined') return;
                        http.request({
                            endpoint: '/validaciones/guardarPreguntaFormulario',
                            metodo: 'POST',
                            data: JSON.stringify(obj),
                            contentType: 'application/json',
                            processData: false,
                            onSuccess: function (resp) {
                                if (resp.success) {
                                    if (fpModal('modalEditarPreguntaValidacion')) fpModal('modalEditarPreguntaValidacion').hide();
                                    if (fpModal('modalTipoPreguntaValidacion')) fpModal('modalTipoPreguntaValidacion').hide();
                                    fpCargarDesdeServidor(fpRefrescarListaPrincipal);
                                    if (typeof Swal !== 'undefined') Swal.fire({ icon: 'success', title: 'Guardado', timer: 1800, showConfirmButton: false });
                                } else {
                                    $err.removeClass('d-none').text(resp.mensaje || 'Error al guardar.');
                                }
                            },
                            onError: function (e) {
                                $err.removeClass('d-none').text((e && e.mensaje) || 'Error al guardar.');
                            }
                        });
                    });
                    $(document).on('click', '.fp-quitar-pregunta', function () {
                        var idx = parseInt($(this).attr('data-idx'), 10);
                        var d = fpLoad();
                        if (!isNaN(idx) && d.nuevas[idx] !== undefined) {
                            d.nuevas.splice(idx, 1);
                            fpSave(d);
                            fpRefrescarListaPrincipal();
                        }
                    });
                    $('#modalFormulariosValidacion').on('hidden.bs.modal', function () {
                        if (fpModal('modalTipoPreguntaValidacion')) fpModal('modalTipoPreguntaValidacion').hide();
                        if (fpModal('modalEditarPreguntaValidacion')) fpModal('modalEditarPreguntaValidacion').hide();
                    });
                });
            }
            wait$();
        })();
    }
})();
