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

    function txtEsc(s) {
        return attrEsc(s).split("'").join('&#039;');
    }

    function formatFechaCorta(v) {
        if (!v) return '—';
        var d = new Date(v);
        if (isNaN(d.getTime())) return String(v);
        return d.toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }

    function parseJsonArray(v) {
        if (Array.isArray(v)) return v;
        if (v == null || v === '') return [];
        if (typeof v !== 'string') return [];
        try {
            var parsed = JSON.parse(v);
            return Array.isArray(parsed) ? parsed : [];
        } catch (e) {
            return [];
        }
    }

    function labelModoFechasAusencia(v) {
        var modo = (v || '').toString().toLowerCase().trim();
        if (modo === 'continuous') return 'Días consecutivos';
        if (modo === 'separated') return 'Fechas separadas';
        return '';
    }

    function limpiarComentarioAusencia(v) {
        return (v || '').toString()
            .split(/\r?\n/)
            .map(function (linea) { return linea.trim(); })
            .filter(function (linea) {
                var x = linea.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                return linea
                    && x.indexOf('dias solicitados') !== 0
                    && x.indexOf('periodos solicitados') !== 0
                    && x.indexOf('motivo imss') !== 0;
            })
            .join('\n')
            .trim();
    }

    function getInconformidadTicket(t) {
        if (t && (t.inconformidad_comentario || Number(t.inconformidad_enviada || 0) > 0)) {
            return {
                folio_origen: t.folio || '',
                comentario: (t.inconformidad_comentario || '').toString().trim(),
                fecha: t.inconformidad_fecha || ''
            };
        }
        var nota = ((t && t.nota) || '').toString().trim();
        var m = nota.match(/^Inconformidad del ticket\s+([^:]+):\s*([\s\S]*)$/i);
        if (!m) return null;
        return {
            folio_origen: (m[1] || '').trim(),
            comentario: (m[2] || '').trim()
        };
    }

    function formatPeriodoAusencia(p) {
        if (!p || typeof p !== 'object') return '';
        var desde = p.from || p.from_ || p.desde || p.fecha_desde || '';
        var hasta = p.to || p.hasta || p.fecha_hasta || '';
        if (!desde && !hasta) return '';
        if (desde && hasta && String(desde) !== String(hasta)) {
            return formatFechaCorta(desde) + ' - ' + formatFechaCorta(hasta);
        }
        return formatFechaCorta(desde || hasta);
    }

    function getPeriodosAusencia(t) {
        return parseJsonArray((t && (t.solicitud_ausencia_periodos || t.solicitud_vacaciones_periodos)) || '');
    }

    function getDiasSolicitadosAusencia(t) {
        return parseJsonArray((t && (t.solicitud_ausencia_dias_solicitados || t.solicitud_vacaciones_dias_solicitados)) || '');
    }

    function resumenPeriodosAusencia(t, limite) {
        var periodos = getPeriodosAusencia(t)
            .map(formatPeriodoAusencia)
            .filter(function (x) { return x !== ''; });
        if (!periodos.length) return '';
        var max = limite || periodos.length;
        var visibles = periodos.slice(0, max);
        var extra = periodos.length > max ? ' +' + (periodos.length - max) + ' mas' : '';
        return visibles.join(', ') + extra;
    }

    function resumenDiasSolicitadosAusencia(t, limite) {
        var dias = getDiasSolicitadosAusencia(t)
            .map(function (x) { return x ? formatFechaCorta(x) : ''; })
            .filter(function (x) { return x !== ''; });
        if (!dias.length) return '';
        var max = limite || dias.length;
        var visibles = dias.slice(0, max);
        var extra = dias.length > max ? ' +' + (dias.length - max) + ' mas' : '';
        return visibles.join(', ') + extra;
    }

    function renderCampoDetalle(label, value, icon, colClass) {
        var val = value == null || String(value).trim() === '' ? '—' : String(value).trim();
        var col = colClass || 'col-12 col-sm-6';
        return (
            '<div class="' + col + '">' +
            '<div class="border rounded-2 bg-body-tertiary p-3 h-100">' +
            '<div class="text-muted fw-semibold mb-1" style="font-size:.72rem;">' +
            '<i class="fa-solid ' + icon + ' me-1"></i>' + txtEsc(label) +
            '</div>' +
            '<div class="small fw-semibold text-break">' + txtEsc(val) + '</div>' +
            '</div>' +
            '</div>'
        );
    }

    function renderInconformidadDetalle(inconformidad) {
        if (!inconformidad || !inconformidad.comentario) return '';
        return (
            '<div class="col-12">' +
            '<div class="tm-inconformidad-card">' +
            '<div class="tm-inconformidad-title"><i class="fa-solid fa-message-exclamation me-1"></i>Inconformidad</div>' +
            (inconformidad.folio_origen ? '<div class="tm-inconformidad-origin">Ticket original: <strong>' + txtEsc(inconformidad.folio_origen) + '</strong></div>' : '') +
            (inconformidad.fecha ? '<div class="tm-inconformidad-origin">Enviada: <strong>' + txtEsc(formatFechaCorta(inconformidad.fecha)) + '</strong></div>' : '') +
            '<div class="tm-inconformidad-text">' + txtEsc(inconformidad.comentario) + '</div>' +
            '</div>' +
            '</div>'
        );
    }

    function normalizaTipoTicketApp(v) {
        var x = (v == null ? '' : String(v)).trim().replace(/_/g, ' ');
        if (!x) return '';
        return x.charAt(0).toUpperCase() + x.slice(1);
    }

    function formatMontoMx(v) {
        if (v == null || v === '') return '—';
        var n = Number(v);
        if (isNaN(n)) return String(v);
        return '$' + n.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function esTicketAppSimple(cat) {
        cat = (cat || '').toString().toLowerCase().trim();
        return ['ausencia', 'solicitud_vacaciones', 'viaticos', 'reclamo', 'pagos_no_identificados', 'incidencias_cartera', 'aplicaciones_de_pago', 'credito_problematico', 'aclaracion_credito'].indexOf(cat) !== -1;
    }

    function respuestaLabel(v) {
        var x = (v || '').toString().toLowerCase().trim();
        if (x === 'aceptado') return 'Aceptado';
        if (x === 'denegado') return 'Denegado';
        return '';
    }

    function tieneInconformidad(t) {
        return !!getInconformidadTicket(t);
    }

    function fechaMsTicket(v) {
        if (!v) return 0;
        var ms = new Date(v).getTime();
        return isNaN(ms) ? 0 : ms;
    }

    function inconformidadPendiente(t) {
        var inconformidad = getInconformidadTicket(t);
        if (!inconformidad) return false;
        var fechaInconformidad = fechaMsTicket(inconformidad.fecha);
        var fechaRespuesta = fechaMsTicket(t && t.respuesta_fecha);
        if (!fechaRespuesta) return true;
        if (!fechaInconformidad) return true;
        return fechaRespuesta <= fechaInconformidad;
    }

    function estadoVisibleTicket(t) {
        if (inconformidadPendiente(t)) return 'Pendiente';
        return ((t && t.estado_ticket_nombre) || '').toString().trim() || 'â€”';
    }

    function pintarRespuestaTicket(t, esAppSimple) {
        var res = respuestaLabel(t && t.respuesta_resultado);
        var tieneRespuesta = !!res;
        var esSegundaRevision = inconformidadPendiente(t);
        $('#resumenTicketRespuestaWrap').toggleClass('d-none', !tieneRespuesta);
        $('#resumenTicketBtnAceptar, #resumenTicketBtnDenegar').toggleClass('d-none', !esAppSimple || (tieneRespuesta && !esSegundaRevision));
        if (!tieneRespuesta) {
            $('#resumenTicketRespuestaBadge').removeClass('bg-label-success bg-label-danger').addClass('bg-label-secondary').text('—');
            $('#resumenTicketRespuestaFecha').empty();
            $('#resumenTicketRespuestaComentario').text('—');
            return;
        }
        $('#resumenTicketRespuestaWrap')
            .find('.tm-respuesta-titulo')
            .remove();
        if (esSegundaRevision) {
            $('#resumenTicketRespuestaWrap').prepend(
                '<div class="tm-respuesta-titulo fw-semibold mb-2"><i class="fa-solid fa-comment-dots me-1"></i>Comentario del primer dictamen</div>'
            );
        }
        $('#resumenTicketRespuestaBadge')
            .removeClass('bg-label-secondary bg-label-success bg-label-danger')
            .addClass(res === 'Aceptado' ? 'bg-label-success' : 'bg-label-danger')
            .text(res);
        var fecha = t.respuesta_fecha ? new Date(t.respuesta_fecha).toLocaleString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '';
        $('#resumenTicketRespuestaFecha').text(fecha ? 'Respondido: ' + fecha : '');
        $('#resumenTicketRespuestaComentario').text((t.respuesta_comentario || '').trim() || '—');
    }

    function renderResumenDetalleModulo(t) {
        var cat = ((t && t.categoria_gestion) || cfg().categoria || '').toString().toLowerCase().trim();
        var $wrap = $('#resumenTicketModuloDetalleWrap');
        $wrap.empty().addClass('d-none');
        if (!esTicketAppSimple(cat)) return;

        var titulo = 'Resumen de ticket';
        var icono = 'fa-ticket';
        if (cat === 'ausencia' || cat === 'solicitud_vacaciones' || cat === 'viaticos') {
            titulo = 'Resumen de ausencia';
            icono = 'fa-calendar-xmark';
        } else if (cat === 'reclamo') {
            titulo = 'Resumen de reclamo';
            icono = 'fa-gift';
        } else if (cat === 'pagos_no_identificados') {
            titulo = 'Resumen de pago';
            icono = 'fa-magnifying-glass-dollar';
        } else if (cat === 'incidencias_cartera') {
            titulo = 'Resumen de incidencia';
            icono = 'fa-briefcase';
        }

        var html =
            '<p class="text-muted fw-semibold mb-2" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;">' +
            '<i class="fa-solid ' + icono + ' me-1"></i>' + txtEsc(titulo) + '</p>' +
            '<div class="row g-2">';

        if (cat === 'ausencia' || cat === 'solicitud_vacaciones' || cat === 'viaticos') {
            var colAusencia = 'col-12 col-sm-6 col-lg-4';
            var desdeAus = t.solicitud_vacaciones_fecha_desde || t.solicitud_ausencia_fecha_desde || '';
            var hastaAus = t.solicitud_vacaciones_fecha_hasta || t.solicitud_ausencia_fecha_hasta || '';
            var deptoAus = t.solicitud_vacaciones_departamento || t.solicitud_ausencia_departamento || '';
            var inconformidadAus = getInconformidadTicket(t);
            var notaAus = inconformidadAus
                ? inconformidadAus.comentario
                : limpiarComentarioAusencia(t.nota || t.descripcion_inicial || '');
            var modoAus = labelModoFechasAusencia(t.solicitud_ausencia_modo_fechas || t.solicitud_vacaciones_modo_fechas || '');
            var periodosAus = resumenPeriodosAusencia(t);
            var diasSolicitadosAus = resumenDiasSolicitadosAusencia(t, 12);
            html +=
                renderCampoDetalle('Tipo', normalizaTipoTicketApp(t.tipo_categoria || t.asunto || 'Ausencia'), 'fa-tag', colAusencia) +
                renderCampoDetalle('Empleado', t.creador_nombre || t.empleado_nombre, 'fa-user', colAusencia) +
                renderCampoDetalle('Departamento', deptoAus || '—', 'fa-building', colAusencia) +
                renderCampoDetalle('Desde', formatFechaCorta(desdeAus), 'fa-calendar-day', colAusencia) +
                renderCampoDetalle('Hasta', formatFechaCorta(hastaAus), 'fa-calendar-check', colAusencia);
            if (modoAus) {
                html += renderCampoDetalle('Modo de fechas', modoAus, 'fa-calendar-week', colAusencia);
            }
            if (periodosAus && modoAus === 'Fechas separadas') {
                html += renderCampoDetalle('Periodos solicitados', periodosAus, 'fa-calendar-days', 'col-12 col-lg-6');
            }
            if (diasSolicitadosAus && modoAus === 'Fechas separadas') {
                html += renderCampoDetalle('Días solicitados', diasSolicitadosAus, 'fa-list-check', 'col-12 col-lg-6');
            }
            var quienCubre = t.solicitud_vacaciones_quien_cubre || t.solicitud_ausencia_quien_cubre || '';
            if (quienCubre && String(quienCubre).trim()) {
                html += renderCampoDetalle('Quién cubre', quienCubre, 'fa-user-shield', colAusencia);
            }
            if (notaAus) {
                html += renderCampoDetalle('Comentario', notaAus, 'fa-comment-dots', 'col-12');
            }
            html += renderInconformidadDetalle(inconformidadAus);
        } else if (cat === 'reclamo') {
            var colReclamo = 'col-12 col-sm-6 col-lg-5ths';
            html +=
                renderCampoDetalle('Tipo de reclamo', normalizaTipoTicketApp(t.reclamo_tipo || t.tipo_categoria || t.asunto || 'Reclamos de bonos'), 'fa-tag', colReclamo) +
                renderCampoDetalle('Empleado', t.creador_nombre || '—', 'fa-user', colReclamo) +
                renderCampoDetalle('Departamento', t.reclamo_departamento || '—', 'fa-building', colReclamo) +
                renderCampoDetalle('Bono', t.reclamo_bono || '—', 'fa-medal', colReclamo) +
                renderCampoDetalle('Periodo de reclamo', t.reclamo_mes || '—', 'fa-calendar', colReclamo) +
                renderCampoDetalle('Semana', ((t.reclamo_semana || '') + (t.reclamo_semana_rango ? ' · ' + t.reclamo_semana_rango : '')) || '—', 'fa-calendar-week', colReclamo) +
                renderCampoDetalle('Monto esperado', formatMontoMx(t.reclamo_monto_esperado), 'fa-money-bill-wave', colReclamo) +
                renderCampoDetalle('Monto recibido', formatMontoMx(t.reclamo_monto_recibido), 'fa-hand-holding-dollar', colReclamo);
            if (t.reclamo_diferencia != null && t.reclamo_diferencia !== '') {
                html += renderCampoDetalle('Diferencia', formatMontoMx(t.reclamo_diferencia), 'fa-scale-balanced', colReclamo);
            }
            var descRec = (t.reclamo_descripcion || t.descripcion_inicial || t.nota || '').trim();
            if (descRec) {
                html += renderCampoDetalle('Descripcion', descRec, 'fa-comment-dots', colReclamo);
            }
        } else if (cat === 'pagos_no_identificados') {
            html +=
                renderCampoDetalle('Credito', t.id_credito ? '#' + t.id_credito : '—', 'fa-id-card') +
                renderCampoDetalle('Fecha de pago', formatFechaCorta(t.pago_no_identificado_fecha_pago), 'fa-calendar-day') +
                renderCampoDetalle('Monto', t.pago_no_identificado_monto_pago ? '$' + Number(t.pago_no_identificado_monto_pago).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '—', 'fa-dollar-sign');
            if ((t.nota || '').trim()) {
                html += renderCampoDetalle('Comentario', t.nota, 'fa-comment-dots');
            }
        } else if (cat === 'incidencias_cartera') {
            html +=
                renderCampoDetalle('Credito', (t.incidencia_cartera_id_credito || t.id_credito) ? '#' + (t.incidencia_cartera_id_credito || t.id_credito) : '—', 'fa-id-card') +
                renderCampoDetalle('Cliente', t.incidencia_cartera_cliente || '—', 'fa-user') +
                renderCampoDetalle('Motivo', normalizaTipoTicketApp(t.incidencia_cartera_tipo_error || t.tipo_categoria || '—'), 'fa-triangle-exclamation');
            if ((t.incidencia_cartera_descripcion || t.descripcion_inicial || '').trim()) {
                html += renderCampoDetalle('Detalle', t.incidencia_cartera_descripcion || t.descripcion_inicial, 'fa-message');
            }
        }

        html += '</div>';
        $wrap.html(html).removeClass('d-none');
    }

    function tmApiBase() {
        var p = window.location.pathname || '';
        var m = p.match(/^(.*?)(\/(?:sabueso|validaciones|viaticos|aplicacionespago|creditoproblematico|aclaracioncredito)(?:\/|$))/i);
        if (m && m[1]) {
            return m[1];
        }
        return (typeof HTTP_CONFIG !== 'undefined' && HTTP_CONFIG && HTTP_CONFIG.baseURL) ? String(HTTP_CONFIG.baseURL).replace(/\/$/, '') : '';
    }

    function tmAppUrl(path) {
        var p = (path == null ? '' : String(path)).trim();
        if (!p) return '';
        if (/^(?:https?:)?\/\//i.test(p) || /^data:/i.test(p) || /^blob:/i.test(p)) return p;
        if (p.charAt(0) !== '/') p = '/' + p;
        var base = tmApiBase();
        return (base ? base.replace(/\/$/, '') : '') + p;
    }

    function tmUploadUrl(ruta) {
        var r = (ruta == null ? '' : String(ruta)).trim().replace(/\\/g, '/');
        if (!r) return '';
        if (/^(?:https?:)?\/\//i.test(r) || /^data:/i.test(r) || /^blob:/i.test(r)) return r;
        r = r.replace(/^\/+/, '').replace(/^public\/uploads\//i, 'uploads/').replace(/^uploads\//i, '');
        return tmAppUrl('/uploads/' + r);
    }

    /** Abre el iframe del form builder en solo lectura (p. ej. jefe territorial desde resumen de ticket). */
    window.tmAbrirFormularioValidacionLectura = function (idFormulario) {
        var id = parseInt(idFormulario, 10);
        if (isNaN(id) || id < 1) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'info',
                    title: 'Sin formulario',
                    text: 'No hay formulario asociado a este ticket. Un administrador de validaciones puede precargar uno desde su panel.'
                });
            }
            return;
        }
        var iframe = document.getElementById('iframeFormBuilderValidacion');
        var modalEl = document.getElementById('modalFormBuilderValidacion');
        var tituloEl = document.getElementById('modalFormBuilderValidacionTitulo');
        if (!iframe || !modalEl || typeof bootstrap === 'undefined' || !bootstrap.Modal) {
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'No se puede abrir el visor de formulario.' });
            return;
        }
        if (tituloEl) {
            tituloEl.innerHTML =
                '<i class="fa-solid fa-eye me-2"></i>Ver formulario y preguntas <span class="fs-6 fw-normal opacity-75">(solo lectura)</span>';
        }
        iframe.src = '/validaciones/formulario/' + id + '?modal=1&readonly=1&t=' + Date.now();
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    };

    var tmAdjuntoLightboxItems = [];
    var tmAdjuntoLightboxIndex = 0;
    /** true cuando el overlay muestra PDF en iframe (visor del navegador, misma pestaña / mismo documento). */
    var tmAdjuntoLightboxPdfMode = false;

    function tmAdjuntoLightboxShow(idx) {
        if (!tmAdjuntoLightboxItems.length) {
            return;
        }
        tmAdjuntoLightboxPdfMode = false;
        $('#tmAdjuntoLightboxPdf').attr('src', 'about:blank');
        $('#tmAdjuntoLightbox .tm-adjunto-lb-pdf-wrap').addClass('d-none');
        $('#tmAdjuntoLightbox .tm-adjunto-lb-img-wrap').removeClass('d-none');
        var n = tmAdjuntoLightboxItems.length;
        var i = parseInt(idx, 10);
        if (isNaN(i) || i < 0 || i >= n) {
            return;
        }
        tmAdjuntoLightboxIndex = i;
        var it = tmAdjuntoLightboxItems[tmAdjuntoLightboxIndex];
        $('#tmAdjuntoLightboxImg')
            .off('error.tmAdjunto')
            .on('error.tmAdjunto', function () {
                var $img = $(this);
                var fb = ($img.attr('data-tm-fallback-src') || '').trim();
                if (fb && $img.attr('src') !== fb) {
                    $img.attr('data-tm-fallback-src', '').attr('src', fb);
                }
            })
            .attr('data-tm-fallback-src', it.fallbackUrl || '')
            .attr('src', it.url)
            .attr('alt', it.nom || '');
        $('#tmAdjuntoLightboxNombre').text(it.nom || '');
        $('#tmAdjuntoLightboxCounter').text(n > 1 ? '(' + (tmAdjuntoLightboxIndex + 1) + ' / ' + n + ')' : '');
        var solo = n <= 1;
        $('#tmAdjuntoLightboxPrev').prop('disabled', solo).toggleClass('d-none', solo);
        $('#tmAdjuntoLightboxNext').prop('disabled', solo).toggleClass('d-none', solo);
        $('#tmAdjuntoLightbox').addClass('tm-adjunto-lightbox-open').attr('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        $(document).on('keydown.tmAdjuntoLb', tmAdjuntoLightboxOnKey);
    }

    function tmAdjuntoLightboxHide() {
        $('#tmAdjuntoLightbox').removeClass('tm-adjunto-lightbox-open').attr('aria-hidden', 'true');
        $('#tmAdjuntoLightboxImg').attr('src', '');
        $('#tmAdjuntoLightboxPdf').attr('src', 'about:blank');
        tmAdjuntoLightboxPdfMode = false;
        $('#tmAdjuntoLightbox .tm-adjunto-lb-pdf-wrap').addClass('d-none');
        $('#tmAdjuntoLightbox .tm-adjunto-lb-img-wrap').removeClass('d-none');
        document.body.style.overflow = '';
        $(document).off('keydown.tmAdjuntoLb');
    }

    /** PDF de adjuntos al ticket: iframe con visor nativo, sin abrir pestaña nueva. */
    function tmAdjuntoPdfOpenInPlace(url, nom) {
        if (!url) return;
        tmAdjuntoLightboxPdfMode = true;
        $('#tmAdjuntoLightboxImg').attr('src', '');
        $('#tmAdjuntoLightbox .tm-adjunto-lb-img-wrap').addClass('d-none');
        $('#tmAdjuntoLightbox .tm-adjunto-lb-pdf-wrap').removeClass('d-none');
        $('#tmAdjuntoLightboxPdf').attr('src', url);
        $('#tmAdjuntoLightboxNombre').text((nom || '').trim() || 'PDF');
        $('#tmAdjuntoLightboxCounter').text('');
        $('#tmAdjuntoLightboxPrev').addClass('d-none').prop('disabled', true);
        $('#tmAdjuntoLightboxNext').addClass('d-none').prop('disabled', true);
        $('#tmAdjuntoLightbox').addClass('tm-adjunto-lightbox-open').attr('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        $(document).off('keydown.tmAdjuntoLb').on('keydown.tmAdjuntoLb', tmAdjuntoLightboxOnKey);
    }

    function tmAdjuntoLightboxStep(delta) {
        var n = tmAdjuntoLightboxItems.length;
        if (n <= 1) {
            return;
        }
        tmAdjuntoLightboxIndex = (tmAdjuntoLightboxIndex + delta + n) % n;
        tmAdjuntoLightboxShow(tmAdjuntoLightboxIndex);
    }

    function tmAdjuntoLightboxOnKey(e) {
        if (e.key === 'Escape') {
            tmAdjuntoLightboxHide();
        } else if (tmAdjuntoLightboxPdfMode) {
            return;
        } else if (e.key === 'ArrowLeft') {
            tmAdjuntoLightboxStep(-1);
        } else if (e.key === 'ArrowRight') {
            tmAdjuntoLightboxStep(1);
        }
    }

    function tmAdjuntoLightboxEnsureBound() {
        var $root = $('#tmAdjuntoLightbox');
        if ($root.length && $root.parent().length && $root.parent()[0] !== document.body) {
            $root.appendTo('body');
        }
        if (window._tmAdjuntoLightboxBound) {
            return;
        }
        window._tmAdjuntoLightboxBound = true;
        $(document).on('click', '#resumenTicketEvidenciasGrid [data-tm-lb]', function () {
            var i = parseInt($(this).attr('data-tm-lb'), 10);
            if (!isNaN(i)) {
                tmAdjuntoLightboxShow(i);
            }
        });
        $(document).on('error', '#resumenTicketEvidenciasGrid img[data-tm-fallback-src], #tmAdjuntoLightboxImg[data-tm-fallback-src]', function () {
            var $img = $(this);
            var fb = ($img.attr('data-tm-fallback-src') || '').trim();
            if (fb && $img.attr('src') !== fb) {
                $img.attr('data-tm-fallback-src', '').attr('src', fb);
            }
        });
        $(document).on('click', '#resumenTicketEvidenciasGrid [data-tm-adj-pdf-url]', function (e) {
            e.preventDefault();
            var u = $(this).attr('data-tm-adj-pdf-url');
            if (!u) return;
            var nom = ($(this).attr('title') || $(this).text() || '').replace(/\s+/g, ' ').trim();
            tmAdjuntoPdfOpenInPlace(u, nom);
        });
        $(document).on('click', '#tmAdjuntoLightbox [data-tm-lb-close]', function (e) {
            e.preventDefault();
            tmAdjuntoLightboxHide();
        });
        $('#tmAdjuntoLightboxPrev').on('click', function () {
            tmAdjuntoLightboxStep(-1);
        });
        $('#tmAdjuntoLightboxNext').on('click', function () {
            tmAdjuntoLightboxStep(1);
        });
    }

    /** Carga evidencias (ticket_evidencia) y pinta miniaturas / enlaces en el modal Ver ticket. */
    function renderResumenTicketEvidencias(idTicket) {
        tmAdjuntoLightboxHide();
        tmAdjuntoLightboxItems = [];
        var $wrap = $('#resumenTicketEvidenciasWrap');
        var $grid = $('#resumenTicketEvidenciasGrid');
        $grid.empty();
        $wrap.addClass('d-none');
        tmAdjuntoLightboxEnsureBound();
        var tid = parseInt(idTicket, 10);
        if (isNaN(tid) || tid < 1 || typeof http === 'undefined') {
            return;
        }
        function nombreArchivo(ev) {
            return ((ev.nombre_original || '').trim() || 'Adjunto').replace(/</g, '');
        }
        function esImagen(ev) {
            var n = (nombreArchivo(ev) + ' ' + (ev.ruta_archivo || '')).toLowerCase();
            return /\.(jpe?g|png|gif|webp|bmp)$/i.test(n);
        }
        function esPdfEv(ev) {
            return /\.pdf$/i.test((ev.nombre_original || '') + (ev.ruta_archivo || ''));
        }
        http.request({
            endpoint: '/sabueso/getEvidenciasTicket',
            metodo: 'POST',
            data: JSON.stringify({ id_ticket: tid, tipo_origen: 'adjunto_ticket' }),
            contentType: 'application/json',
            processData: false,
            showLoader: false,
            onSuccess: function (r) {
                var list = (r && r.datos) || [];
                if (!list.length) {
                    return;
                }
                $wrap.removeClass('d-none');
                list.forEach(function (ev) {
                    var pathUrl = ev.url || '/sabueso/verEvidencia?id=' + (ev.id || '');
                    var url = tmAppUrl(pathUrl);
                    var fallbackUrl = tmUploadUrl(ev.ruta_archivo || '');
                    var href = attrEsc(url);
                    var fallbackAttr = fallbackUrl ? attrEsc(fallbackUrl) : '';
                    var nom = attrEsc(nombreArchivo(ev));
                    var nomPlain = nombreArchivo(ev);
                    if (esImagen(ev)) {
                        var idx = tmAdjuntoLightboxItems.length;
                        tmAdjuntoLightboxItems.push({ url: url, fallbackUrl: fallbackUrl, nom: nomPlain });
                        $grid.append(
                            '<div class="col-6 col-md-4">' +
                                '<button type="button" class="d-block w-100 p-0 border-0 bg-transparent rounded overflow-hidden" style="cursor:pointer;" data-tm-lb="' +
                                idx +
                                '" title="Ver en grande">' +
                                '<img src="' +
                                href +
                                '" alt="' +
                                attrEsc(nomPlain) +
                                '" loading="lazy" class="tm-ev-img" data-tm-fallback-src="' +
                                fallbackAttr +
                                '" onerror="if(this.dataset.tmFallbackSrc){this.onerror=null;this.src=this.dataset.tmFallbackSrc;this.dataset.tmFallbackSrc=\'\';}"' +
                                '">' +
                                '</button>' +
                                '<div class="small text-muted text-truncate mt-1" title="' +
                                nom +
                                '">' +
                                nom +
                                '</div></div>'
                        );
                    } else if (esPdfEv(ev)) {
                        $grid.append(
                            '<div class="col-12 col-sm-6">' +
                                '<button type="button" class="btn btn-sm btn-outline-secondary w-100 text-start text-truncate" data-tm-adj-pdf-url="' +
                                attrEsc(url) +
                                '" title="Ver PDF">' +
                                '<i class="fa-solid fa-file-pdf text-danger me-2"></i>' +
                                nom +
                                '</button></div>'
                        );
                    } else {
                        $grid.append(
                            '<div class="col-12 col-sm-6">' +
                                '<a href="' +
                                href +
                                '" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-secondary w-100 text-start text-truncate">' +
                                '<i class="fa-solid fa-paperclip me-2"></i>' +
                                nom +
                                '</a></div>'
                        );
                    }
                });
            },
            onError: function () {}
        });
    }

    function countdown24h(fechaVencStr) {
        if (!fechaVencStr) return { text: '—', colorClass: 'text-muted', blink: false };
        var fin = new Date(fechaVencStr).getTime();
        var now = Date.now();
        var rest = Math.floor((fin - now) / 1000);
        var blink = false;
        var colorClass = 'text-success';
        var text;
        if (rest <= 0) {
            text = 'Vencido';
            colorClass = 'text-danger fw-bold';
            blink = true;
        } else {
            var h = Math.floor(rest / 3600);
            var m = Math.floor((rest % 3600) / 60);
            var s = rest % 60;
            var ss = (s < 10 ? '0' : '') + s;
            if (h > 0) text = h + 'h ' + (m < 10 ? '0' : '') + m + 'm ' + ss + 's';
            else if (m > 0) text = m + 'm ' + ss + 's';
            else text = s + 's';
            if (rest <= 30 * 60) {
                colorClass = 'text-danger fw-bold';
                blink = true;
            } else if (rest <= 60 * 60) colorClass = 'text-danger fw-bold';
            else if (rest <= 6 * 60 * 60) colorClass = 'text-warning fw-semibold';
            else if (rest <= 12 * 60 * 60) colorClass = 'text-warning';
            else colorClass = 'text-success';
        }
        return { text: text, colorClass: colorClass, blink: blink };
    }

    /**
     * Select con búsqueda (mismo comportamiento que organigrama.php — SearchableSelect).
     * Incluye opción con value "" (p. ej. "Selecciona una persona") para quitar asignación.
     */
    var _tmAsignarSearchableInst = null;
    function TmSearchableSelect(selectElement) {
        this.select = selectElement;
        this.options = [];
        this.selectedValue = '';
        this.isOpen = false;
        this.init();
    }
    TmSearchableSelect.prototype.init = function () {
        this.wrapper = document.createElement('div');
        this.wrapper.className = 'select-search-wrapper';
        this.select.parentNode.insertBefore(this.wrapper, this.select);
        this.wrapper.appendChild(this.select);
        this.display = document.createElement('div');
        this.display.className = 'select-search-display form-select form-select-sm';
        this.display.setAttribute('role', 'button');
        this.display.setAttribute('tabindex', '0');
        var opt0 = this.select.options[this.select.selectedIndex];
        this.display.textContent = opt0 ? opt0.text : 'Selecciona una persona';
        this.selectedValue = this.select.value;
        this.wrapper.appendChild(this.display);
        this.dropdown = document.createElement('div');
        this.dropdown.className = 'select-search-dropdown border rounded-2 shadow-sm bg-white';
        this.wrapper.appendChild(this.dropdown);
        this.searchInput = document.createElement('input');
        this.searchInput.type = 'text';
        this.searchInput.className = 'select-search-input form-control form-control-sm border-0 border-bottom rounded-0';
        this.searchInput.placeholder = 'Buscar...';
        this.searchInput.setAttribute('autocomplete', 'off');
        this.dropdown.appendChild(this.searchInput);
        this.optionsContainer = document.createElement('div');
        this.optionsContainer.className = 'select-search-options';
        this.dropdown.appendChild(this.optionsContainer);
        this.loadOptions();
        this.attachEvents();
    };
    TmSearchableSelect.prototype.loadOptions = function () {
        this.options = [];
        Array.from(this.select.options).forEach(function (option) {
            this.options.push({ value: option.value, text: option.text });
        }, this);
        this.renderOptions(this.options);
    };
    TmSearchableSelect.prototype.renderOptions = function (filteredOptions) {
        this.optionsContainer.innerHTML = '';
        if (filteredOptions.length === 0) {
            var noResults = document.createElement('div');
            noResults.className = 'select-search-option no-results dropdown-item disabled text-center text-muted small';
            noResults.textContent = 'No se encontraron resultados';
            this.optionsContainer.appendChild(noResults);
            return;
        }
        var self = this;
        filteredOptions.forEach(function (option) {
            var optionDiv = document.createElement('div');
            optionDiv.className = 'select-search-option dropdown-item small';
            optionDiv.textContent = option.text;
            optionDiv.dataset.value = option.value;
            if (String(option.value) === String(self.selectedValue)) optionDiv.classList.add('active');
            optionDiv.addEventListener('click', function () { self.selectOption(option); });
            self.optionsContainer.appendChild(optionDiv);
        });
    };
    TmSearchableSelect.prototype.selectOption = function (option) {
        this.selectedValue = option.value;
        this.select.value = option.value;
        this.display.textContent = option.text;
        // jQuery escucha el change del <select>; en algunos navegadores solo el evento nativo no dispara los handlers.
        if (typeof window.$ !== 'undefined' && this.select) {
            $(this.select).trigger('change');
        }
        this.select.dispatchEvent(new Event('change', { bubbles: true }));
        this.close();
    };
    TmSearchableSelect.prototype.open = function () {
        if (this.select.disabled) return;
        this.isOpen = true;
        this.dropdown.classList.add('show');
        this.searchInput.value = '';
        this.searchInput.focus();
        this.loadOptions();
    };
    TmSearchableSelect.prototype.close = function () {
        this.isOpen = false;
        this.dropdown.classList.remove('show');
        this.searchInput.value = '';
    };
    TmSearchableSelect.prototype.attachEvents = function () {
        var self = this;
        function toggle(e) {
            e.stopPropagation();
            if (self.isOpen) self.close(); else self.open();
        }
        this.display.addEventListener('click', toggle);
        this.display.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                toggle(e);
            }
        });
        this.searchInput.addEventListener('input', function (e) {
            var searchTerm = e.target.value.toLowerCase().trim();
            var filtered = self.options.filter(function (o) {
                return (o.text + '').toLowerCase().includes(searchTerm);
            });
            self.renderOptions(filtered);
        });
        this.dropdown.addEventListener('click', function (e) { e.stopPropagation(); });
        document.addEventListener('click', function () {
            if (self.isOpen) self.close();
        });
        var observer = new MutationObserver(function () {
            self.loadOptions();
            var selectedOption = self.select.options[self.select.selectedIndex];
            if (selectedOption) {
                self.display.textContent = selectedOption.text;
                self.selectedValue = selectedOption.value;
            }
        });
        observer.observe(this.select, { childList: true, subtree: true });
    };
    TmSearchableSelect.prototype.refresh = function () {
        this.loadOptions();
        var selectedOption = this.select.options[this.select.selectedIndex];
        if (selectedOption) {
            this.display.textContent = selectedOption.text;
            this.selectedValue = selectedOption.value;
        } else {
            this.display.textContent = 'Selecciona una persona';
            this.selectedValue = '';
        }
    };
    function ensureTmAsignarSearchableSelect() {
        var el = document.getElementById('resumenTicketAsignarSelect');
        if (!el || el.getAttribute('data-tm-searchable') === '1') return;
        el.setAttribute('data-tm-searchable', '1');
        _tmAsignarSearchableInst = new TmSearchableSelect(el);
    }

    /** Bloquea selector de jefe y segmento (modo no territorial): una vez asignado no se puede volver a elegir. */
    function tmSetAsignacionResumenBloqueada(bloquear) {
        var $sel = $('#resumenTicketAsignarSelect');
        $sel.prop('disabled', !!bloquear);
        $('input[name="tmAsignarCampo"]').prop('disabled', !!bloquear);
        var el = document.getElementById('resumenTicketAsignarSelect');
        var w = el ? el.closest('.select-search-wrapper') : null;
        if (w) {
            w.classList.toggle('tm-asign-select-locked', !!bloquear);
        }
        var disp = w ? w.querySelector('.select-search-display') : null;
        if (disp) {
            disp.style.pointerEvents = bloquear ? 'none' : '';
            disp.style.opacity = bloquear ? '0.92' : '';
            disp.setAttribute('aria-disabled', bloquear ? 'true' : 'false');
        }
        if (_tmAsignarSearchableInst && bloquear && typeof _tmAsignarSearchableInst.close === 'function') {
            _tmAsignarSearchableInst.close();
        }
        if (_tmAsignarSearchableInst && typeof _tmAsignarSearchableInst.refresh === 'function') {
            _tmAsignarSearchableInst.refresh();
        }
    }

    function renderCountdownCell(fechaVencStr) {
        var c = countdown24h(fechaVencStr);
        var blink = c.blink ? ' tm-countdown-blink' : '';
        return '<span class="d-inline-flex align-items-center gap-1"><i class="fa-regular fa-clock me-1" style="font-size:0.85em;opacity:0.85"></i><span class="tm-countdown' + blink + ' ' + c.colorClass + '" data-tm-venc="' + attrEsc(fechaVencStr || '') + '">' + attrEsc(c.text) + '</span></span>';
    }

    function actualizarCountdownsTabla() {
        $(SEL_TABLA + ' .tm-countdown[data-tm-venc]').each(function () {
            var venc = $(this).attr('data-tm-venc');
            if (!venc) return;
            var c = countdown24h(venc);
            $(this).text(c.text).removeClass('text-success text-warning text-danger text-muted fw-bold fw-semibold tm-countdown-blink').addClass(c.colorClass);
            if (c.blink) $(this).addClass('tm-countdown-blink');
        });
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

    /** Oculta columnas que no aplican al módulo (p. ej. Validaciones: sin crédito Sabueso, tiempo visita, DS). */
    function aplicarVisibilidadColumnasModulo(dt) {
        if (!dt) return;
        var ocultar = (cfg().columnas_ocultas && cfg().columnas_ocultas.length) ? cfg().columnas_ocultas : [10, 11];
        try {
            dt.columns(ocultar).visible(false);
        } catch (e) {}
    }

    function mapRow(t) {
        var CAT = (cfg().categoria || '').trim();
        var catLabel = (cfg().labelBadge || CAT || '—').replace(/</g, '&lt;');
        var optsFechaHora = { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' };
        var fechaCreacion = t.fecha_creacion
            ? new Date(t.fecha_creacion).toLocaleString('es-MX', optsFechaHora)
            : '—';
        var fechaVenc = t.fecha_vencimiento
            ? new Date(t.fecha_vencimiento).toLocaleString('es-MX', optsFechaHora)
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
        var estadoNombreRaw = ((t.estado_ticket_nombre || '') + '').trim() || '—';
        var inconformidadRow = inconformidadPendiente(t) ? getInconformidadTicket(t) : null;
        var estadoNombreRaw = inconformidadRow ? 'Pendiente' : estadoNombreRaw;
        var estadoNombre = estadoNombreRaw.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/"/g, '&quot;');
        var en = estadoNombreRaw.toLowerCase();
        var estadoBadge = '<span class="badge bg-label-secondary">' + estadoNombre + '</span>';
        if (en.indexOf('cerrad') !== -1) {
            estadoBadge = '<span class="badge bg-secondary text-white">' + estadoNombre + '</span>';
        } else if (en.indexOf('acept') !== -1) {
            estadoBadge = '<span class="badge bg-success text-white">' + estadoNombre + '</span>';
        } else if (en.indexOf('deneg') !== -1 || en.indexOf('rechaz') !== -1) {
            estadoBadge = '<span class="badge bg-danger text-white">' + estadoNombre + '</span>';
        } else if (en.indexOf('abiert') !== -1) {
            estadoBadge = '<span class="badge bg-success text-white">' + estadoNombre + '</span>';
        } else if (en.indexOf('proceso') !== -1 || en.indexOf('curso') !== -1 || en.indexOf('pendiente') !== -1) {
            estadoBadge = '<span class="badge bg-info text-white">' + estadoNombre + '</span>';
        } else if (en.indexOf('espera') !== -1 || en.indexOf('pausad') !== -1) {
            estadoBadge = '<span class="badge bg-warning text-dark">' + estadoNombre + '</span>';
        } else if (estadoNombreRaw !== '—') {
            estadoBadge = '<span class="badge bg-primary text-white">' + estadoNombre + '</span>';
        }
        var catK = ((t.categoria_gestion || CAT || 'sabueso') + '').toLowerCase().trim();
        var icoM = {
            sabueso: 'fa-dog',
            plantilla: 'fa-file-lines',
            atencion_cliente: 'fa-headset',
            validaciones: 'fa-clipboard-check',
            ausencia: 'fa-calendar-xmark',
            solicitud_vacaciones: 'fa-calendar-xmark',
            viaticos: 'fa-calendar-xmark',
            reclamo: 'fa-gift',
            pagos_no_identificados: 'fa-magnifying-glass-dollar',
            incidencias_cartera: 'fa-briefcase',
            aplicaciones_de_pago: 'fa-gift',
            credito_problematico: 'fa-magnifying-glass-dollar',
            aclaracion_credito: 'fa-briefcase'
        };
        var icCat = icoM[catK] || 'fa-list';
        var folioTipoHtml =
            '<div class="fw-semibold">' +
            (t.folio || '—') +
            '</div><div class="small text-muted mt-1">' +
            (t.tipo_ticket_nombre || '—') +
            '</div><div class="mt-1"><span class="badge bg-label-primary small d-inline-flex align-items-center gap-1"><i class="fa-solid ' +
            icCat +
            '" style="font-size:0.7rem;line-height:1" aria-hidden="true"></i>' +
            catLabel +
            '</span></div>';
        if (inconformidadRow) {
            folioTipoHtml +=
                '<div class="mt-1"><span class="badge bg-label-warning small d-inline-flex align-items-center gap-1">' +
                '<i class="fa-solid fa-message-exclamation" style="font-size:0.7rem;"></i>Segunda revisiÃ³n' +
                '</span></div>';
        }
        if (CAT === 'validaciones') {
            var notaV = (t.nota || '').trim();
            var urlV = (t.url_direccion || '').trim();
            if (notaV) {
                var notaCorta = notaV.length > 140 ? notaV.substring(0, 140) + '…' : notaV;
                folioTipoHtml +=
                    '<div class="small text-muted mt-2 text-break" title="Nota"><i class="fa-solid fa-note-sticky me-1 opacity-75"></i>' +
                    String(notaCorta).replace(/&/g, '&amp;').replace(/</g, '&lt;') +
                    '</div>';
            }
            if (urlV) {
                folioTipoHtml +=
                    '<div class="small mt-1"><a href="' +
                    attrEsc(urlV) +
                    '" target="_blank" rel="noopener" class="link-primary text-break"><i class="fa-solid fa-link me-1"></i>Abrir enlace</a></div>';
            }
        }
        var creditoVal =
            t.id_credito != null && t.id_credito > 0 ? '#' + t.id_credito : t.asunto || t.tipo_categoria || '—';
        if (CAT === 'ausencia' || catK === 'ausencia' || catK === 'solicitud_vacaciones') {
            var deptoAus = t.solicitud_vacaciones_departamento || t.solicitud_ausencia_departamento || '';
            creditoVal = (t.tipo_categoria || t.asunto || 'Ausencia') + (deptoAus ? ' / ' + deptoAus : '');
        } else if (catK === 'reclamo') {
            var reclamoPartes = [];
            reclamoPartes.push(normalizaTipoTicketApp(t.reclamo_tipo || t.tipo_categoria || t.asunto || 'Reclamos de bonos'));
            if (t.reclamo_departamento) reclamoPartes.push(t.reclamo_departamento);
            if (t.reclamo_bono) reclamoPartes.push(t.reclamo_bono);
            creditoVal = reclamoPartes.join(' / ');
        } else if (catK === 'pagos_no_identificados') {
            var montoPagoRow = t.pago_no_identificado_monto_pago ? '$' + Number(t.pago_no_identificado_monto_pago).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '';
            creditoVal = (t.id_credito ? '#' + t.id_credito : 'Credito sin ID') + (montoPagoRow ? ' / ' + montoPagoRow : '');
        } else if (catK === 'incidencias_cartera') {
            var idCredInc = t.incidencia_cartera_id_credito || t.id_credito || '';
            var clienteInc = t.incidencia_cartera_cliente || '';
            creditoVal = (idCredInc ? '#' + idCredInc : 'Credito sin ID') + (clienteInc ? ' / ' + clienteInc : '');
        }
        var creditoHtml = '<small>' + String(creditoVal).replace(/&/g, '&amp;').replace(/</g, '&lt;') + '</small>';
        var esAusencia = CAT === 'ausencia' || catK === 'ausencia' || catK === 'solicitud_vacaciones' || catK === 'viaticos';
        var esAppSimple = esTicketAppSimple(catK);
        var fechasHtml = '';
        if (esAusencia) {
            var fDesdeAus = t.solicitud_vacaciones_fecha_desde || t.solicitud_ausencia_fecha_desde || '';
            var fHastaAus = t.solicitud_vacaciones_fecha_hasta || t.solicitud_ausencia_fecha_hasta || '';
            var modoTablaAus = labelModoFechasAusencia(t.solicitud_ausencia_modo_fechas || t.solicitud_vacaciones_modo_fechas || '');
            var diasTablaAus = getDiasSolicitadosAusencia(t);
            var periodosTablaAus = resumenPeriodosAusencia(t, 2);
            var diasResumenTablaAus = resumenDiasSolicitadosAusencia(t, 3);
            if (modoTablaAus === 'Separadas' && diasTablaAus.length) {
                fechasHtml =
                    '<div class="small d-flex align-items-center gap-1"><i class="fa fa-calendar-days text-muted" style="width: 1rem;"></i><span>' +
                    diasTablaAus.length +
                    ' dia' +
                    (diasTablaAus.length === 1 ? '' : 's') +
                    ' separado' +
                    (diasTablaAus.length === 1 ? '' : 's') +
                    '</span></div><div class="small text-muted d-flex align-items-center gap-1 mt-1"><i class="fa fa-list-check" style="width: 1rem;"></i><span>' +
                    txtEsc(diasResumenTablaAus) +
                    '</span></div>';
            } else if (periodosTablaAus && getPeriodosAusencia(t).length > 1) {
                fechasHtml =
                    '<div class="small d-flex align-items-center gap-1"><i class="fa fa-calendar-days text-muted" style="width: 1rem;"></i><span>' +
                    txtEsc(getPeriodosAusencia(t).length + ' periodos') +
                    '</span></div><div class="small text-muted d-flex align-items-center gap-1 mt-1"><i class="fa fa-calendar-week" style="width: 1rem;"></i><span>' +
                    txtEsc(periodosTablaAus) +
                    '</span></div>';
            } else if (fDesdeAus || fHastaAus) {
                fechasHtml =
                    '<div class="small d-flex align-items-center gap-1"><i class="fa fa-calendar-days text-muted" style="width: 1rem;"></i><span>' +
                    formatFechaCorta(fDesdeAus) +
                    ' - ' +
                    formatFechaCorta(fHastaAus) +
                    '</span></div>';
            } else {
                fechasHtml =
                    '<div class="small d-flex align-items-center gap-1"><i class="fa fa-calendar-plus-o text-muted" style="width: 1rem;"></i><span>Registro: ' +
                    fechaCreacion +
                    '</span></div>';
            }
        } else if (catK === 'pagos_no_identificados') {
            fechasHtml =
                '<div class="small d-flex align-items-center gap-1"><i class="fa fa-calendar-day text-muted" style="width: 1rem;"></i><span>Pago: ' +
                formatFechaCorta(t.pago_no_identificado_fecha_pago) +
                '</span></div><div class="small text-muted d-flex align-items-center gap-1 mt-1"><i class="fa fa-calendar-plus-o" style="width: 1rem;"></i><span>Registro: ' +
                fechaCreacion +
                '</span></div>';
        } else if (esAppSimple) {
            if (catK === 'reclamo' && (t.reclamo_mes || t.reclamo_semana)) {
                var semanaRec = t.reclamo_semana ? ('Semana: ' + t.reclamo_semana + (t.reclamo_semana_rango ? ' · ' + t.reclamo_semana_rango : '')) : '';
                fechasHtml =
                    '<div class="small d-flex align-items-center gap-1"><i class="fa fa-calendar text-muted" style="width: 1rem;"></i><span>Mes: ' +
                    txtEsc(t.reclamo_mes || '—') +
                    '</span></div>' +
                    (semanaRec ? '<div class="small text-muted d-flex align-items-center gap-1 mt-1"><i class="fa fa-calendar-week" style="width: 1rem;"></i><span>' + txtEsc(semanaRec) + '</span></div>' : '');
            } else {
            fechasHtml =
                '<div class="small d-flex align-items-center gap-1"><i class="fa fa-calendar-plus-o text-muted" style="width: 1rem;"></i><span>Registro: ' +
                fechaCreacion +
                '</span></div>';
            }
        } else {
            fechasHtml =
                '<div class="small d-flex align-items-center gap-1"><i class="fa fa-calendar-plus-o text-muted" style="width: 1rem;"></i><span>Creación: ' +
                fechaCreacion +
                '</span></div><div class="small text-muted d-flex align-items-center gap-1 mt-1"><i class="fa fa-calendar-times-o" style="width: 1rem;"></i><span>Vencimiento: ' +
                fechaVenc +
                '</span></div>';
        }
        var modo = (cfg().modo || '').toString().trim();
        var soloCerrar = (CAT === 'validaciones');
        var puedeCerrar = !(modo === 'gestor' || modo === 'territorial');
        var btnCerrar = puedeCerrar
            ? '<button type="button" class="btn btn-sm btn-secondary" data-tm-cerrar="' +
                t.id_ticket +
                '" title="Cerrar ticket"><i class="fa fa-minus"></i></button>'
            : '';
        var acciones =
            '<div class="d-flex flex-column gap-1 align-items-stretch" style="min-width:2.5rem;">' +
            '<button type="button" class="btn btn-sm btn-outline-primary" data-tm-ver="' +
            t.id_ticket +
            '" title="Ver detalle"><i class="fa fa-ticket"></i></button>' +
            btnCerrar +
            (soloCerrar
                ? ''
                : '<button type="button" class="btn btn-sm btn-danger" data-tm-eliminar="' +
                    t.id_ticket +
                    '" title="Eliminar ticket"><i class="fa fa-trash"></i></button>') +
            '</div>';
        return {
            _fecha_creacion: t.fecha_creacion || '',
            folio_tipo: folioTipoHtml,
            estado: estadoBadge,
            prioridad: prioridadBadge,
            credito: creditoHtml,
            fechas: fechasHtml,
            creador: '<small class="d-flex align-items-center gap-1"><i class="fa fa-user"></i>' + (t.creador_nombre || '—') + '</small>',
            asignado:
                t.asignado_nombre && t.asignado_nombre.trim()
                    ? '<small class="d-flex align-items-center gap-1"><i class="fa fa-user-check text-success"></i>' + t.asignado_nombre + '</small>'
                    : '<span class="text-muted">—</span>',
            tiempo_visitar: renderCountdownCell(t.fecha_vencimiento),
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
            endpoint:
                (cfg().modo || '').trim() === 'gestor'
                    ? '/validaciones/getTicketsGestor'
                    : (cfg().modo || '').trim() === 'territorial'
                        ? '/validaciones/getTicketsTerritorial'
                        : '/sabueso/getTicketsPanelAdmin',
            metodo: 'POST',
            data: JSON.stringify({ filtros: { categoria_gestion: CAT } }),
            contentType: 'application/json',
            processData: false,
            showLoader: false,
            onSuccess: function (resp) {
                if (esPrimera) window._ticketsModuloPanelPrimeraCarga = false;
                window._ticketsModuloCache = {};
                (resp.datos || []).forEach(function (t) {
                    if (t && t.id_ticket) window._ticketsModuloCache[t.id_ticket] = t;
                });
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
                aplicarVisibilidadColumnasModulo(tabla);
                $(SEL_TABLA + ' [data-bs-toggle="tooltip"]').tooltip();
                actualizarCountdownsTabla();
                if (!window._tmCountdownInterval) {
                    window._tmCountdownInterval = setInterval(actualizarCountdownsTabla, 1000);
                }
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

    function marcarTicketProcesandoPrimeraVista(t) {
        if (!t || !t.id_ticket || !esTicketAppSimple(t.categoria_gestion || cfg().categoria) || typeof http === 'undefined') return;
        var estadoActual = ((t.estado_ticket_nombre || '') + '').toLowerCase().trim();
        if (respuestaLabel(t.respuesta_resultado) || (estadoActual && estadoActual !== 'pendiente' && estadoActual !== 'abierto' && estadoActual !== '—')) return;
        http.request({
            endpoint: '/sabueso/marcarTicketProcesando',
            metodo: 'POST',
            data: JSON.stringify({ id_ticket: t.id_ticket }),
            contentType: 'application/json',
            processData: false,
            showLoader: false,
            onSuccess: function (r) {
                if (r && r.success && r.datos && r.datos.actualizado) {
                    t.estado_ticket_nombre = 'Procesando';
                    $('#resumenTicketEstado').text('Procesando');
                    $('#resumenTicketEstadoPill').text('● Procesando');
                    if (window._ticketsModuloCache && window._ticketsModuloCache[t.id_ticket]) {
                        window._ticketsModuloCache[t.id_ticket].estado_ticket_nombre = 'Procesando';
                    }
                    if (typeof getTicketsModuloPanel === 'function') getTicketsModuloPanel();
                }
            },
            onError: function () {}
        });
    }

    function responderTicketAppModal(accion) {
        var $modal = $('#modalResumenTicket');
        var id = parseInt($modal.attr('data-id-ticket'), 10);
        if (!id || typeof Swal === 'undefined' || typeof http === 'undefined') return;
        var esAceptar = accion === 'aceptar';
        var modalEl = document.getElementById('modalResumenTicket');
        var bsModal = modalEl && window.bootstrap && window.bootstrap.Modal ? window.bootstrap.Modal.getInstance(modalEl) : null;
        if (bsModal && bsModal._focustrap && typeof bsModal._focustrap.deactivate === 'function') {
            bsModal._focustrap.deactivate();
        }
        Swal.fire({
            title: esAceptar ? 'Aceptar ticket' : 'Denegar ticket',
            input: 'textarea',
            inputLabel: 'Comentario obligatorio',
            inputPlaceholder: esAceptar ? 'Escribe el comentario de aceptación...' : 'Escribe el motivo de denegación...',
            inputAttributes: { 'aria-label': 'Comentario obligatorio' },
            returnFocus: false,
            showCancelButton: true,
            confirmButtonText: esAceptar ? 'Aceptar' : 'Denegar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: esAceptar ? '#28a745' : '#dc3545',
            didOpen: function () {
                var input = Swal.getInput();
                if (input && typeof input.focus === 'function') {
                    setTimeout(function () { input.focus(); }, 50);
                }
            },
            willClose: function () {
                if (bsModal && bsModal._focustrap && typeof bsModal._focustrap.activate === 'function') {
                    bsModal._focustrap.activate();
                }
            },
            preConfirm: function (value) {
                var comentario = (value || '').trim();
                if (!comentario) {
                    Swal.showValidationMessage('El comentario es obligatorio.');
                    return false;
                }
                return comentario;
            }
        }).then(function (res) {
            if (!res.isConfirmed) return;
            http.request({
                endpoint: '/sabueso/responderTicketApp',
                metodo: 'POST',
                data: JSON.stringify({ id_ticket: id, accion: accion, comentario: res.value }),
                contentType: 'application/json',
                processData: false,
                onSuccess: function (r) {
                    if (!r || !r.success) {
                        Swal.fire({ icon: 'error', title: 'Error', text: (r && r.mensaje) || 'No se pudo responder el ticket.' });
                        return;
                    }
                    var resultado = r.datos && r.datos.resultado ? r.datos.resultado : (esAceptar ? 'aceptado' : 'denegado');
                    var estado = resultado === 'aceptado' ? 'Aceptado' : 'Denegado';
                    var row = window._ticketsModuloCache && window._ticketsModuloCache[id] ? window._ticketsModuloCache[id] : {};
                    row.respuesta_resultado = resultado;
                    row.respuesta_comentario = res.value;
                    row.respuesta_fecha = new Date().toISOString();
                    row.estado_ticket_nombre = estado;
                    if (window._ticketsModuloCache) window._ticketsModuloCache[id] = row;
                    $('#resumenTicketEstado').text(estado);
                    $('#resumenTicketEstadoPill').text('● ' + estado);
                    pintarRespuestaTicket(row, true);
                    Swal.fire({ icon: 'success', title: estado, text: r.mensaje || 'Respuesta guardada.' });
                    if (typeof getTicketsModuloPanel === 'function') getTicketsModuloPanel();
                },
                onError: function (e) {
                    Swal.fire({ icon: 'error', title: 'Error', text: (e && e.mensaje) || 'No se pudo responder el ticket.' });
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
        if (dt) aplicarVisibilidadColumnasModulo(dt);
        $(document).on('click', SEL_TABLA + ' [data-tm-cerrar]', function () {
            cerrarTicket(parseInt($(this).attr('data-tm-cerrar'), 10));
        });
        $(document).on('click', SEL_TABLA + ' [data-tm-eliminar]', function () {
            eliminarTicket(parseInt($(this).attr('data-tm-eliminar'), 10));
        });
        $(document).on('click', '#resumenTicketBtnAceptar', function () {
            responderTicketAppModal('aceptar');
        });
        $(document).on('click', '#resumenTicketBtnDenegar', function () {
            responderTicketAppModal('denegar');
        });
        $(document).on('click', '#resumenTicketBtnVerFormularioTerritorial', function () {
            var tid = $('#resumenTicketAsignarSelect').data('resumen-id-ticket');
            if (!tid) return;
            var fidBtn = $(this).attr('data-tm-formulario-id');
            if (fidBtn != null && fidBtn !== '' && parseInt(fidBtn, 10) > 0) {
                window.tmAbrirFormularioValidacionLectura(String(fidBtn));
                return;
            }
            var stored =
                typeof localStorage !== 'undefined' ? localStorage.getItem('tm_formulario_' + tid) || localStorage.getItem('tm_formulario_precargado') : null;
            window.tmAbrirFormularioValidacionLectura(stored);
        });
        $(document).on('click', '#resumenTicketTerritorialBtnReasignar', function () {
            var $sel = $('#resumenTicketAsignarSelect');
            var tid = $sel.data('resumen-id-ticket');
            var pidStr = $sel.val();
            if (!tid || typeof http === 'undefined') return;
            if (!pidStr) {
                if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'Seleccione un gestor.' });
                return;
            }
            var idP = parseInt(pidStr, 10);
            if (isNaN(idP) || idP < 1) return;
            var esPrimeraAsignacionGestor =
                !(typeof window._tmResumenTerritorialAsignadoId === 'number' && window._tmResumenTerritorialAsignadoId > 0);
            var motivo = ($('#resumenTicketAsignarMotivo').val() || '').trim();
            if (!esPrimeraAsignacionGestor && !motivo) {
                if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'Escriba el motivo del cambio.' });
                return;
            }
            http.request({
                endpoint: '/validaciones/reasignarGestorTicketTerritorial',
                metodo: 'POST',
                data: JSON.stringify({
                    id_ticket: tid,
                    id_persona: idP,
                    motivo: motivo
                }),
                contentType: 'application/json',
                processData: false,
                onSuccess: function (r) {
                    if (r && r.success) {
                        var nom = ($sel.find('option:selected').text() || '').replace(/\s+/g, ' ').trim();
                        if (window._ticketsModuloCache && window._ticketsModuloCache[tid]) {
                            window._ticketsModuloCache[tid].asignado_nombre = nom;
                            window._ticketsModuloCache[tid].id_persona_asignada = idP;
                        }
                        $('#resumenTicketAsignadoACapoLabel').text('Gestor de campo: ' + (nom || '—'));
                        window._tmResumenTerritorialAsignadoId = idP;
                        $('#resumenTicketAsignarMotivo').val('');
                        $('#resumenTicketMotivoWrap').addClass('d-none');
                        $('#resumenTicketTerritorialBtnReasignar').addClass('d-none');
                        if (typeof window._tmCargarSelectResumenAsignacion === 'function') {
                            window._tmCargarSelectResumenAsignacion();
                        }
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Asignación guardada',
                                text: (r && r.mensaje) ? r.mensaje : 'El ticket quedó asignado al gestor seleccionado.',
                                confirmButtonText: 'Listo'
                            });
                        }
                        if (typeof getTicketsModuloPanel === 'function') getTicketsModuloPanel();
                    } else {
                        if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: (r && r.mensaje) ? r.mensaje : 'Error' });
                    }
                },
                onError: function (e) {
                    if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: (e && e.mensaje) || 'Error' });
                }
            });
        });
        $(document).on('click', SEL_TABLA + ' [data-tm-ver]', function () {
            var id = parseInt($(this).attr('data-tm-ver'), 10);
            if (isNaN(id) || id < 1) return;
            var cache = window._ticketsModuloCache;
            var t = cache && cache[id];
            if (!t) return;
            var catModal = ((t.categoria_gestion || cfg().categoria || '') + '').toLowerCase().trim();
            var esModalAusencia = catModal === 'ausencia' || catModal === 'solicitud_vacaciones' || catModal === 'viaticos';
            var esModalAppSimple = esTicketAppSimple(catModal);
            $('#modalResumenTicket').toggleClass('modal-resumen-ausencia', esModalAppSimple);
            $('#modalResumenTicket').toggleClass('modal-resumen-reclamo', catModal === 'reclamo');
            var fechaCreacion = t.fecha_creacion
                ? new Date(t.fecha_creacion).toLocaleString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })
                : '—';
            var fechaVenc = t.fecha_vencimiento
                ? new Date(t.fecha_vencimiento).toLocaleString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })
                : '—';
            $('#resumenTicketBtnVerFormularioTerritorial').removeAttr('data-tm-formulario-id');
            $('#resumenTicketDe').text((t.creador_nombre || '').trim() || '—');
            var asignPor =
                t.asignado_por_nombre != null && String(t.asignado_por_nombre).trim() !== ''
                    ? String(t.asignado_por_nombre).trim()
                    : '—';
            $('#resumenTicketAsignadoPor').text(asignPor);
            $('#resumenTicketFecha').text(fechaCreacion);
            $('#resumenTicketVence').text(fechaVenc);
            $('#resumenTicketFolio').text(t.folio || '—');
            var estadoTxt = (t.estado_ticket_nombre || '—').trim() || '—';
            estadoTxt = estadoVisibleTicket(t);
            $('#resumenTicketEstado').text(estadoTxt);
            $('#resumenTicketEstadoPill').text((estadoTxt !== '—' ? '\u25CF ' : '') + estadoTxt);
            var prioridadTxt = (t.prioridad_nombre || '—').trim() || '—';
            $('#resumenTicketPrioridad').text(prioridadTxt);
            $('#resumenTicketPrioridadPill').text((prioridadTxt !== '—' ? '\u25CF ' : '') + prioridadTxt);
            $('#resumenTicketPrioridadSide').text((prioridadTxt !== '—' ? '\u25CF ' : '') + prioridadTxt);
            if (esModalAppSimple) {
                $('#resumenTicketPrioridadPill').empty();
            }
            var refVal = t.id_credito != null && t.id_credito > 0 ? 'Crédito #' + t.id_credito : (t.asunto || t.tipo_categoria || '—');
            if (catModal === 'reclamo') {
                refVal = normalizaTipoTicketApp(t.tipo_categoria || t.asunto || 'Reclamos de bonos');
            } else if (catModal === 'pagos_no_identificados') {
                refVal = t.id_credito ? 'Credito #' + t.id_credito : 'Credito sin ID';
            } else if (catModal === 'incidencias_cartera') {
                var idCredModal = t.incidencia_cartera_id_credito || t.id_credito || '';
                refVal = (idCredModal ? 'Credito #' + idCredModal : 'Credito sin ID') + (t.incidencia_cartera_cliente ? ' - ' + t.incidencia_cartera_cliente : '');
            }
            $('#resumenTicketRef').text(refVal);
            $('#resumenTicketRefSide').text(refVal);
            $('#resumenTicketCreado').text(fechaCreacion);
            $('#resumenTicketAsunto').text((t.asunto || t.tipo_categoria || '—').trim() || '—');
            $('#resumenTicketDescripcion').text((t.descripcion_inicial || '').trim() || '—');
            renderResumenDetalleModulo(t);
            var notaTexto = (t.nota || '').trim();
            var urlDir = (t.url_direccion || '').trim();
            $('#resumenTicketLinkWrap').empty();
            if (!esModalAppSimple && (notaTexto || urlDir)) {
                $('#resumenTicketExtraWrap').removeClass('d-none');
                if (notaTexto) {
                    $('#resumenTicketNota').text(notaTexto).removeClass('d-none');
                } else {
                    $('#resumenTicketNota').empty().addClass('d-none');
                }
                if (urlDir) {
                    var esMaps = /google\.com\/maps|maps\.google/i.test(urlDir);
                    var labelLink = esMaps ? 'Ver en mapa' : 'Abrir enlace';
                    var iconLink = esMaps ? 'fa-map-location-dot' : 'fa-external-link';
                    var linkHtml = '<a href="' + attrEsc(urlDir) + '" target="_blank" rel="noopener" class="btn btn-sm btn-outline-info mt-2"><i class="fa-solid ' + iconLink + ' me-1"></i>' + labelLink + '</a>';
                    $('#resumenTicketLinkWrap').html(linkHtml);
                }
            } else {
                $('#resumenTicketExtraWrap').addClass('d-none');
            }
            if (!esModalAppSimple && t.ds_resultado && String(t.ds_resultado).trim()) {
                $('#resumenTicketDs').html(t.ds_resultado_html || attrEsc(t.ds_resultado));
                $('#resumenTicketDsWrap').removeClass('d-none');
            } else {
                $('#resumenTicketDsWrap').addClass('d-none');
            }
            var idTicket = t.id_ticket;
            $('#modalResumenTicket').attr('data-id-ticket', idTicket || '');
            pintarRespuestaTicket(t, esModalAppSimple);
            marcarTicketProcesandoPrimeraVista(t);
            renderResumenTicketEvidencias(idTicket);
            var modo = (cfg().modo || '').trim();
            var esTerritorial = modo === 'territorial';
            var esGestor = modo === 'gestor';
            var idPersonaActual = t.id_persona_asignada != null && t.id_persona_asignada > 0 ? parseInt(t.id_persona_asignada, 10) : 0;
            /** Territorial: jefe en sesión elige un gestor subordinado. Si en BD el asignado es el propio jefe, aún no hay gestor de campo. */
            var capoIdSesion = esTerritorial ? parseInt(cfg().personaIdSesion, 10) || 0 : 0;
            var idGestorCampoTerritorial = 0;
            if (esTerritorial && idPersonaActual > 0 && (!capoIdSesion || idPersonaActual !== capoIdSesion)) {
                idGestorCampoTerritorial = idPersonaActual;
            }
            window._tmResumenTerritorialAsignadoId = esTerritorial ? idGestorCampoTerritorial : 0;
            var $sel = $('#resumenTicketAsignarSelect');
            $sel.off('change.resumenTicket').empty().append('<option value="">Selecciona una persona</option>');
            $sel.data('resumen-id-ticket', idTicket);
            var labelCampo = function (campo) {
                if (String(campo) === '8_21') return 'Campo 8–21';
                return 'Campo 1–7';
            };

            // UI según modo
            var $asignarBlock = $('#resumenTicketAsignarBlock');
            if (esGestor) $asignarBlock.addClass('d-none');
            else $asignarBlock.removeClass('d-none');

            if (esGestor || esTerritorial) {
                tmSetAsignacionResumenBloqueada(false);
            } else if (idPersonaActual > 0) {
                tmSetAsignacionResumenBloqueada(true);
            } else {
                tmSetAsignacionResumenBloqueada(false);
            }

            if (esTerritorial) {
                $('#resumenTicketAsignarTitulo').text('Gestor asignado al ticket');
                var txtGestorCampo = '—';
                if (idGestorCampoTerritorial > 0) {
                    txtGestorCampo = ((t.asignado_nombre || '').trim() || '—');
                }
                $('#resumenTicketAsignadoACapoLabel').removeClass('d-none').text('Gestor de campo: ' + txtGestorCampo);
                $('#resumenTicketCampoLabel')
                    .text(labelCampo(cfg().campoCapo || '1_7'))
                    .removeClass('d-none');
                $('#resumenTicketMotivoWrap').addClass('d-none');
                $('#resumenTicketTerritorialBtnReasignar').addClass('d-none');
                $('#resumenTicketAsignarMotivo').val('');
                $('#resumenTicketVerFormularioTerritorialWrap').toggleClass('d-none', !cfg().verFormularioTerritorialResumen);
                $('#resumenTicketAsignarBlock .btn-group[role="group"]').addClass('d-none');
                $sel.empty().append('<option value="">Selecciona un gestor</option>');
            } else {
                $('#resumenTicketAsignarTitulo').text('Asignar a');
                $('#resumenTicketAsignadoACapoLabel').addClass('d-none');
                $('#resumenTicketMotivoWrap').addClass('d-none');
                $('#resumenTicketTerritorialBtnReasignar').addClass('d-none');
                if (esGestor) {
                    $('#resumenTicketVerFormularioTerritorialWrap').toggleClass('d-none', !cfg().verFormularioTerritorialResumen);
                } else {
                    $('#resumenTicketVerFormularioTerritorialWrap').addClass('d-none');
                }
                $('#resumenTicketAsignarBlock .btn-group[role="group"]').removeClass('d-none');
            }

            function campoAsignarActual() {
                if (esTerritorial) return (cfg().campoCapo === '8_21' ? '8_21' : '1_7');
                var v = $('input[name="tmAsignarCampo"]:checked').val();
                return v === '8_21' ? '8_21' : '1_7';
            }

            var pidAnterior =
                esTerritorial && idGestorCampoTerritorial > 0
                    ? String(idGestorCampoTerritorial)
                    : !esTerritorial && idPersonaActual > 0
                      ? String(idPersonaActual)
                      : '';
            function cargarSelectAsignacionJefes() {
                ensureTmAsignarSearchableSelect();
                var campo = campoAsignarActual();
                var $hint = $('#resumenTicketAsignarHint');
                $hint.addClass('d-none').text('');
                $sel.empty().append(
                    '<option value="">' + (esTerritorial ? 'Selecciona un gestor' : 'Selecciona una persona') + '</option>'
                );
                if (typeof http === 'undefined') return;
                var endpoint = esTerritorial ? '/validaciones/getGestoresPorCampo' : '/sabueso/getPersonasSabuesoJefesPorCampo';
                var payload = esTerritorial ? {} : { campo: campo };
                http.request({
                    endpoint: endpoint,
                    metodo: 'POST',
                    data: JSON.stringify(payload),
                    contentType: 'application/json',
                    processData: false,
                    showLoader: false,
                    onSuccess: function (resp) {
                        var list = resp.datos || [];
                        if (!list.length && resp.mensaje) {
                            $hint.removeClass('d-none').text(resp.mensaje);
                        }
                        list.forEach(function (p) {
                            var gid = parseInt(p.id, 10);
                            var sub = (p.nombre_puesto || '').trim();
                            var txt = attrEsc((p.nombre_completo || p.nombre || '').trim() || '');
                            if (sub) txt += ' — ' + attrEsc(sub);
                            $sel.append('<option value="' + (p.id || '') + '">' + txt + '</option>');
                        });
                        if (esTerritorial) {
                            var tidSel = $sel.data('resumen-id-ticket');
                            var rowCache = tidSel && window._ticketsModuloCache ? window._ticketsModuloCache[tidSel] : null;
                            var idAsigTerr =
                                typeof window._tmResumenTerritorialAsignadoId === 'number' &&
                                window._tmResumenTerritorialAsignadoId > 0
                                    ? window._tmResumenTerritorialAsignadoId
                                    : idGestorCampoTerritorial;
                            var nomAsigTerr = (rowCache && rowCache.asignado_nombre ? String(rowCache.asignado_nombre) : (t.asignado_nombre || '')).trim();
                            if (idAsigTerr > 0) {
                                if ($sel.find('option[value="' + idAsigTerr + '"]').length) {
                                    $sel.val(String(idAsigTerr));
                                } else {
                                    $sel.append(
                                        '<option value="' +
                                            idAsigTerr +
                                            '">' +
                                            attrEsc(nomAsigTerr || 'ID ' + idAsigTerr) +
                                            ' (actual)</option>'
                                    );
                                    $sel.val(String(idAsigTerr));
                                }
                                pidAnterior = String(idAsigTerr);
                            } else {
                                $sel.val('');
                                pidAnterior = '';
                            }
                        } else if (idPersonaActual > 0) {
                            if ($sel.find('option[value="' + idPersonaActual + '"]').length) {
                                $sel.val(String(idPersonaActual));
                            } else {
                                $sel.append('<option value="' + idPersonaActual + '">' + attrEsc((t.asignado_nombre || 'Asignado actual').trim() || ('ID ' + idPersonaActual)) + ' (actual)</option>');
                                $sel.val(String(idPersonaActual));
                            }
                            pidAnterior = $sel.val() ? String($sel.val()) : '';
                        } else {
                            $sel.val('');
                            pidAnterior = '';
                        }
                        if (_tmAsignarSearchableInst && typeof _tmAsignarSearchableInst.refresh === 'function') {
                            _tmAsignarSearchableInst.refresh();
                        }
                        if (!esTerritorial && !esGestor) {
                            if (idPersonaActual > 0) tmSetAsignacionResumenBloqueada(true);
                            else tmSetAsignacionResumenBloqueada(false);
                        }
                    },
                    onError: function () {
                        $sel.append('<option value="" disabled>Error al cargar lista</option>');
                        $hint.removeClass('d-none').text(esTerritorial ? 'No se pudo cargar la lista de gestores.' : 'No se pudo cargar la lista de líderes.');
                    }
                });
            }
            window._tmCargarSelectResumenAsignacion = cargarSelectAsignacionJefes;
            $('input[name="tmAsignarCampo"]').off('change.tmAsignarCampo');
            if (!esTerritorial) {
                $('input[name="tmAsignarCampo"]').on('change.tmAsignarCampo', function () {
                    cargarSelectAsignacionJefes();
                });
            }
            function onAsignarChange() {
                var tid = $sel.data('resumen-id-ticket');
                var pid = $sel.val();
                if (!tid || typeof http === 'undefined') return;
                if (esTerritorial) {
                    var pidStr = pid != null ? String(pid) : '';
                    if (!pidStr) {
                        $('#resumenTicketMotivoWrap').addClass('d-none');
                        $('#resumenTicketTerritorialBtnReasignar').addClass('d-none');
                        $('#resumenTicketAsignarMotivo').val('');
                        $('#resumenTicketMotivoLabel').removeClass('d-none');
                        $('#resumenTicketAsignarMotivo').removeClass('d-none');
                        return;
                    }
                    var idElegido = parseInt(pidStr, 10);
                    var asignadoActual =
                        typeof window._tmResumenTerritorialAsignadoId === 'number' && window._tmResumenTerritorialAsignadoId > 0
                            ? window._tmResumenTerritorialAsignadoId
                            : 0;
                    if (asignadoActual > 0 && !isNaN(idElegido) && idElegido === asignadoActual) {
                        $('#resumenTicketMotivoWrap').addClass('d-none');
                        $('#resumenTicketTerritorialBtnReasignar').addClass('d-none');
                        $('#resumenTicketAsignarMotivo').val('');
                        $('#resumenTicketMotivoLabel').removeClass('d-none');
                        $('#resumenTicketAsignarMotivo').removeClass('d-none');
                        return;
                    }
                    var esPrimeraAsignacionGestor = !(asignadoActual > 0);
                    $('#resumenTicketMotivoLabel').toggleClass('d-none', esPrimeraAsignacionGestor);
                    $('#resumenTicketAsignarMotivo').toggleClass('d-none', esPrimeraAsignacionGestor).val('');
                    $('#resumenTicketTerritorialBtnReasignar').text(
                        esPrimeraAsignacionGestor ? 'Asignar gestor' : 'Aplicar reasignación'
                    );
                    $('#resumenTicketMotivoWrap').removeClass('d-none');
                    $('#resumenTicketTerritorialBtnReasignar').removeClass('d-none');
                    return;
                } else if (pid === '' || pid === null) {
                    http.request({
                        endpoint: '/sabueso/quitarAsignacionTicket',
                        metodo: 'POST',
                        data: JSON.stringify({ id_ticket: tid }),
                        contentType: 'application/json',
                        processData: false,
                        onSuccess: function (r) {
                            if (r.success) {
                                if (window._ticketsModuloCache && window._ticketsModuloCache[tid]) {
                                    window._ticketsModuloCache[tid].asignado_nombre = '';
                                    window._ticketsModuloCache[tid].id_persona_asignada = null;
                                }
                                tmSetAsignacionResumenBloqueada(false);
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Asignación quitada',
                                        text: 'El ticket quedó sin persona asignada.',
                                        confirmButtonText: 'Listo'
                                    });
                                }
                                if (typeof getTicketsModuloPanel === 'function') getTicketsModuloPanel();
                            } else {
                                if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: r.mensaje || 'Error' });
                            }
                        },
                        onError: function (e) {
                            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: (e && e.mensaje) || 'Error' });
                        }
                    });
                } else {
                    var idP = parseInt(pid, 10);
                    if (isNaN(idP) || idP < 1) return;
                    http.request({
                        endpoint: '/sabueso/asignarTicket',
                        metodo: 'POST',
                        data: JSON.stringify({ id_ticket: tid, id_persona: idP }),
                        contentType: 'application/json',
                        processData: false,
                        onSuccess: function (r) {
                            if (r.success) {
                                var nom = $sel.find('option:selected').text();
                                if (window._ticketsModuloCache && window._ticketsModuloCache[tid]) {
                                    window._ticketsModuloCache[tid].asignado_nombre = nom;
                                    window._ticketsModuloCache[tid].id_persona_asignada = idP;
                                }
                                tmSetAsignacionResumenBloqueada(true);
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Asignación guardada',
                                        text: (r && r.mensaje) ? r.mensaje : 'El ticket quedó asignado a la persona seleccionada.',
                                        confirmButtonText: 'Listo'
                                    });
                                }
                                if (typeof getTicketsModuloPanel === 'function') getTicketsModuloPanel();
                            } else {
                                if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: r.mensaje || 'Error' });
                            }
                        },
                        onError: function (e) {
                            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: (e && e.mensaje) || 'Error' });
                        }
                    });
                }
            }
            $sel.on('change.resumenTicket', onAsignarChange);
            var vencStr = t.fecha_vencimiento || '';
            $('#modalResumenTicket').data('venc', vencStr);
            var c0 = countdown24h(vencStr);
            var $countdownEl = $('#resumenTicketCountdown');
            $countdownEl.text(c0.text).removeClass('text-success text-warning text-danger text-muted fw-bold fw-semibold tm-countdown-blink').addClass(c0.colorClass);
            if (c0.blink) $countdownEl.addClass('tm-countdown-blink');
            if (window._tmModalCountdownInterval) {
                clearInterval(window._tmModalCountdownInterval);
                window._tmModalCountdownInterval = null;
            }
            var modalEl = document.getElementById('modalResumenTicket');
            if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                var modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
                $(modalEl).off('shown.bs.modal hidden.bs.modal').on('shown.bs.modal', function () {
                    window._tmModalCountdownInterval = setInterval(function () {
                        var v = $('#modalResumenTicket').data('venc');
                        if (!v) return;
                        var c = countdown24h(v);
                        $('#resumenTicketCountdown').text(c.text).removeClass('text-success text-warning text-danger text-muted fw-bold fw-semibold tm-countdown-blink').addClass(c.colorClass);
                        if (c.blink) $('#resumenTicketCountdown').addClass('tm-countdown-blink');
                    }, 1000);
                }).on('hidden.bs.modal', function () {
                    if (window._tmModalCountdownInterval) {
                        clearInterval(window._tmModalCountdownInterval);
                        window._tmModalCountdownInterval = null;
                    }
                });
                modalInstance.show();
            }
            if (cfg().verFormularioTerritorialResumen && typeof http !== 'undefined') {
                var tidFormResolver = idTicket;
                var storedFormT =
                    typeof localStorage !== 'undefined'
                        ? localStorage.getItem('tm_formulario_' + tidFormResolver) || localStorage.getItem('tm_formulario_precargado')
                        : null;
                http.request({
                    endpoint: '/validaciones/getFormularios',
                    metodo: 'GET',
                    showLoader: false,
                    onSuccess: function (resp) {
                        var list = Array.isArray(resp.datos) ? resp.datos : [];
                        var activos = list.filter(function (f) {
                            return f.activo === 1 || f.activo === true;
                        });
                        var idRes = 0;
                        if (
                            storedFormT &&
                            activos.some(function (f) {
                                return String(f.id) === String(storedFormT);
                            })
                        ) {
                            idRes = parseInt(storedFormT, 10);
                        } else if (activos.length > 0) {
                            idRes = parseInt(activos[0].id, 10);
                        }
                        if (!isNaN(idRes) && idRes > 0) {
                            $('#resumenTicketBtnVerFormularioTerritorial').attr('data-tm-formulario-id', String(idRes));
                        }
                    }
                });
            }
            if (cfg().formularios) {
                $('#resumenTicketFormularioWrap').removeClass('d-none');
                var $formSel = $('#resumenTicketFormularioSelect');
                var $formPrecargadoNombre = $('#resumenTicketFormularioPrecargadoNombre');
                var modoFormReadOnly = (esGestor || esTerritorial);
                if (modoFormReadOnly) {
                    // En Gestor/Territorial no se permite cambiar el formulario (solo vista).
                    $formSel.prop('disabled', true).addClass('d-none');
                }
                $formSel.off('change.resumenForm').empty().append('<option value="">— Ninguno —</option>');
                var storedForm = typeof localStorage !== 'undefined' ? (localStorage.getItem('tm_formulario_' + idTicket) || localStorage.getItem('tm_formulario_precargado')) : null;
                function actualizarPrecargadoLabel() {
                    var opt = $formSel.find('option:selected');
                    var nombre = opt.length ? opt.text() : '—';
                    $formPrecargadoNombre.text(nombre || '—');
                }
                if (typeof http !== 'undefined') {
                    http.request({
                        endpoint: '/validaciones/getFormularios',
                        metodo: 'GET',
                        showLoader: false,
                        onSuccess: function (resp) {
                            var list = Array.isArray(resp.datos) ? resp.datos : [];
                            var activos = list.filter(function (f) { return f.activo === 1 || f.activo === true; });
                            activos.forEach(function (f) {
                                $formSel.append('<option value="' + (f.id || '') + '">' + attrEsc((f.nombre || '').trim() || 'Sin nombre') + '</option>');
                            });
                            if (storedForm) {
                                $formSel.val(storedForm);
                            } else if (activos.length > 0) {
                                var primerId = activos[0].id;
                                $formSel.val(String(primerId));
                                if (typeof localStorage !== 'undefined') localStorage.setItem('tm_formulario_' + idTicket, String(primerId));
                            }
                            actualizarPrecargadoLabel();
                        }
                    });
                }
                if (!modoFormReadOnly) {
                    $formSel.on('change.resumenForm', function () {
                        var val = $(this).val();
                        if (typeof localStorage !== 'undefined') localStorage.setItem('tm_formulario_' + idTicket, val || '');
                        actualizarPrecargadoLabel();
                    });
                }
            } else {
                $('#resumenTicketFormularioWrap').addClass('d-none');
            }
            if (!esGestor) cargarSelectAsignacionJefes();
        });
        window.getTicketsModuloPanel();
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', runInit);
    else runInit();

    if (cfg().formularios) {
        (function formulariosValidacion() {
            var idFormulario = typeof window.FP_ID_FORMULARIO !== 'undefined' ? parseInt(window.FP_ID_FORMULARIO, 10) : 0;
            if (isNaN(idFormulario)) idFormulario = 0;

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
                        var url = '/validaciones/getPreguntasFormulario';
                        if (idFormulario > 0) url += '?id_formulario=' + idFormulario;
                        http.request({
                            endpoint: url,
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
                    function fpCargarFormularios(callback) {
                        if (typeof http === 'undefined') {
                            if (callback) callback([]);
                            return;
                        }
                        http.request({
                            endpoint: '/validaciones/getFormularios',
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
                    var PRECARGADO_KEY = 'tm_formulario_precargado';
                    var PRECARGADO_NOMBRE_KEY = 'tm_formulario_precargado_nombre';
                    function actualizarPrecargadoEnPantalla() {
                        var nombre = typeof localStorage !== 'undefined' ? localStorage.getItem(PRECARGADO_NOMBRE_KEY) : null;
                        var txt = (nombre && nombre.trim()) ? nombre.trim() : 'Ninguno';
                        $('#panelFormularioPrecargadoNombre').text(txt);
                        $('#panelFormularioPrecargadoWrap').removeClass('d-none').addClass('d-flex');
                        $('#formularioPrecargadoActualNombre').text(txt);
                    }
                    function establecerPrecargado(id, nombre) {
                        if (typeof localStorage === 'undefined') return;
                        localStorage.setItem(PRECARGADO_KEY, String(id || ''));
                        localStorage.setItem(PRECARGADO_NOMBRE_KEY, (nombre || '').trim() || '');
                        actualizarPrecargadoEnPantalla();
                    }
                    function fpRefrescarListaFormularios(datos) {
                        if (datos === undefined) {
                            fpCargarFormularios(fpRefrescarListaFormularios);
                            return;
                        }
                        var $ul = $('#listaFormulariosValidacion');
                        if (!$ul.length) return;
                        if (!datos.length) {
                            $ul.html('<li class="list-group-item small text-muted fst-italic py-3 text-center">(Sin formularios aún)</li>');
                            $('#formValidacionSinFormularios').show();
                            actualizarPrecargadoEnPantalla();
                            return;
                        }
                        $('#formValidacionSinFormularios').hide();
                        $ul.empty();
                        var precargadoId = typeof localStorage !== 'undefined' ? localStorage.getItem(PRECARGADO_KEY) : null;
                        var activos = datos.filter(function (f) { return f.activo === 1 || f.activo === true; });
                        if (!precargadoId && activos.length > 0) {
                            establecerPrecargado(activos[0].id, (activos[0].nombre || '').trim());
                        } else {
                            actualizarPrecargadoEnPantalla();
                        }
                        precargadoId = typeof localStorage !== 'undefined' ? localStorage.getItem(PRECARGADO_KEY) : null;
                        datos.forEach(function (f) {
                            var activo = f.activo === 1 || f.activo === true;
                            var badge = activo ? '<span class="badge bg-label-success">Activo</span>' : '<span class="badge bg-secondary">Inactivo</span>';
                            var nombre = fpEsc((f.nombre || '').trim() || 'Sin nombre');
                            var nombreRaw = (f.nombre || '').trim() || 'Sin nombre';
                            var esPrecargado = String(f.id || '') === String(precargadoId || '');
                            var btnPrecargado = (activo && !esPrecargado)
                                ? '<button type="button" class="btn btn-sm btn-label-success fp-form-usar-precargado" data-id="' + (f.id || '') + '" data-nombre="' + fpEsc(nombreRaw) + '" title="Usar como formulario precargado"><i class="fa-solid fa-star"></i></button>'
                                : '';
                            $ul.append(
                                '<li class="list-group-item d-flex align-items-center gap-2 fp-form-item-formulario" data-id="' + (f.id || '') + '">' +
                                '<div class="flex-grow-1 min-w-0"><span class="me-2">' + badge + '</span><span class="small">' + nombre + '</span>' + (esPrecargado ? ' <span class="badge bg-label-success ms-1">Precargado</span>' : '') + '</div>' +
                                (btnPrecargado ? btnPrecargado + ' ' : '') +
                                '<button type="button" class="btn btn-sm btn-outline-warning fp-form-toggle-formulario" data-id="' + (f.id || '') + '" title="' + (activo ? 'Inhabilitar' : 'Habilitar') + '"><i class="fa-solid fa-toggle-' + (activo ? 'on' : 'off') + '"></i></button>' +
                                '<button type="button" class="btn btn-sm btn-outline-danger fp-form-eliminar-formulario" data-id="' + (f.id || '') + '" title="Eliminar"><i class="fa-solid fa-trash"></i></button>' +
                                '</li>'
                            );
                        });
                        $(document).off('click', '.fp-form-usar-precargado').on('click', '.fp-form-usar-precargado', function (e) {
                            e.preventDefault();
                            e.stopPropagation();
                            var id = $(this).attr('data-id');
                            var nombre = $(this).attr('data-nombre') || '';
                            establecerPrecargado(id, nombre);
                            fpCargarFormularios(fpRefrescarListaFormularios);
                        });
                    }
                    fpCargarFormularios(function (datos) {
                        var activos = (datos || []).filter(function (f) { return f.activo === 1 || f.activo === true; });
                        var precargadoId = typeof localStorage !== 'undefined' ? localStorage.getItem(PRECARGADO_KEY) : null;
                        if (!precargadoId && activos.length > 0) {
                            establecerPrecargado(activos[0].id, (activos[0].nombre || '').trim());
                        } else {
                            actualizarPrecargadoEnPantalla();
                        }
                    });
                    function fpEsc(s) {
                        if (s == null) return '';
                        return String(s)
                            .replace(/&/g, '&amp;')
                            .replace(/</g, '&lt;')
                            .replace(/>/g, '&gt;')
                            .replace(/"/g, '&quot;');
                    }
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
                        var $pre = $('#listaPreguntasPredefinidasValidacion');
                        if (!$pre.length) return;
                        var predefinidas = datos.filter(function (p) { return p.es_predefinida === 1 || p.es_predefinida === true; });
                        var personalizadas = datos.filter(function (p) { return p.es_predefinida === 0 || p.es_predefinida === false; });
                        if (!predefinidas.length)
                            $pre.html('<li class="list-group-item small text-muted fst-italic py-3 text-center">(Sin preguntas predefinidas aún)</li>');
                        else {
                            $pre.empty();
                            predefinidas.forEach(function (p) {
                                var checked = p.activa === 1 || p.activa === true ? ' checked' : '';
                                var sub = fpRenderSub(p);
                                $pre.append(
                                    '<li class="list-group-item d-flex align-items-start gap-2 fp-form-item-validacion" data-id="' + (p.id || '') + '">' +
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
                                    '<li class="list-group-item d-flex justify-content-between align-items-start gap-2 fp-form-item-validacion" data-id="' + (p.id || '') + '">' +
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
                        fpCargarFormularios(function (datos) {
                            fpRefrescarListaFormularios(datos);
                        });
                        var m = fpModal('modalFormulariosValidacion');
                        if (m) m.show();
                    });
                    $('#btnFormularioValidacionCrear').on('click', function () {
                        $('#formValidacionCrearInline').show();
                        $('#formValidacionNombreNuevo').val('').focus();
                    });
                    $('#btnFormularioValidacionCrearCancel').on('click', function () {
                        $('#formValidacionCrearInline').hide();
                        $('#formValidacionNombreNuevo').val('');
                    });
                    $('#formValidacionNombreNuevo').on('keydown', function (e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            $('#btnFormularioValidacionCrearConfirm').click();
                        }
                        if (e.key === 'Escape') {
                            $('#btnFormularioValidacionCrearCancel').click();
                        }
                    });
                    $('#btnFormularioValidacionCrearConfirm').on('click', function () {
                        var nombre = ($('#formValidacionNombreNuevo').val() || '').trim();
                        if (!nombre) {
                            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'Escriba el nombre del formulario' });
                            return;
                        }
                        $('#formValidacionCrearInline').hide();
                        $('#formValidacionNombreNuevo').val('');
                        fpCrearFormulario(nombre);
                    });
                    function fpAbrirFormBuilderEnModal(id) {
                        var modalLista = document.getElementById('modalFormulariosValidacion');
                        var modalBuilder = document.getElementById('modalFormBuilderValidacion');
                        var iframe = document.getElementById('iframeFormBuilderValidacion');
                        var tituloEl = document.getElementById('modalFormBuilderValidacionTitulo');
                        if (modalLista && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                            var inst = bootstrap.Modal.getInstance(modalLista);
                            if (inst) inst.hide();
                        }
                        if (iframe) {
                            var modo = (cfg().modo || '').trim();
                            var readOnly = modo === 'gestor' || modo === 'territorial';
                            var extra = readOnly ? '&readonly=1' : '';
                            if (tituloEl) {
                                tituloEl.innerHTML = readOnly
                                    ? '<i class="fa-solid fa-eye me-2"></i>Ver formulario <span class="fs-6 fw-normal opacity-75">(solo lectura)</span>'
                                    : '<i class="fa-solid fa-pen-to-square me-2"></i>Form Builder – Editar formulario';
                            }
                            iframe.src = '/validaciones/formulario/' + id + '?modal=1&t=' + Date.now() + extra;
                        }
                        if (modalBuilder && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                            bootstrap.Modal.getOrCreateInstance(modalBuilder).show();
                        }
                    }
                    function fpCerrarFormBuilderModal() {
                        var modalBuilder = document.getElementById('modalFormBuilderValidacion');
                        var iframe = document.getElementById('iframeFormBuilderValidacion');
                        if (iframe) iframe.src = 'about:blank';
                        if (modalBuilder && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                            var inst = bootstrap.Modal.getInstance(modalBuilder);
                            if (inst) inst.hide();
                        }
                        fpCargarFormularios(fpRefrescarListaFormularios);
                    }
                    window.addEventListener('message', function (e) {
                        if (e.data === 'formBuilderClose') fpCerrarFormBuilderModal();
                    });
                    var modalBuilderEl = document.getElementById('modalFormBuilderValidacion');
                    if (modalBuilderEl) {
                        modalBuilderEl.addEventListener('hidden.bs.modal', function () {
                            var iframe = document.getElementById('iframeFormBuilderValidacion');
                            if (iframe) iframe.src = 'about:blank';
                            fpCargarFormularios(fpRefrescarListaFormularios);
                        });
                    }
                    function fpCrearFormulario(nombre) {
                        if (!nombre || typeof http === 'undefined') return;
                        http.request({
                            endpoint: '/validaciones/guardarFormulario',
                            metodo: 'POST',
                            data: JSON.stringify({ nombre: nombre }),
                            contentType: 'application/json',
                            processData: false,
                            onSuccess: function (resp) {
                                if (resp.success && resp.datos && resp.datos.id) {
                                    if (typeof Swal !== 'undefined') Swal.fire({ icon: 'success', title: 'Creado', timer: 1500, showConfirmButton: false });
                                    fpAbrirFormBuilderEnModal(resp.datos.id);
                                } else {
                                    if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: resp.mensaje || 'Error' });
                                }
                            },
                            onError: function (e) {
                                if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: (e && e.mensaje) || 'Error al crear.' });
                            }
                        });
                    }
                    $(document).on('click', '.fp-form-item-formulario', function (e) {
                        if ($(e.target).closest('.fp-form-toggle-formulario, .fp-form-eliminar-formulario').length) return;
                        var id = parseInt($(this).attr('data-id'), 10);
                        if (!isNaN(id) && id > 0) fpAbrirFormBuilderEnModal(id);
                    });
                    $(document).on('click', '.fp-form-toggle-formulario', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        var id = parseInt($(this).attr('data-id'), 10);
                        if (isNaN(id) || id < 1 || typeof http === 'undefined') return;
                        var $row = $(this).closest('.fp-form-item-formulario');
                        var activo = $row.find('.badge.bg-label-success').length ? 0 : 1;
                        http.request({
                            endpoint: '/validaciones/toggleFormulario',
                            metodo: 'POST',
                            data: JSON.stringify({ id: id, activo: activo }),
                            contentType: 'application/json',
                            processData: false,
                            onSuccess: function () {
                                fpCargarFormularios(fpRefrescarListaFormularios);
                                if (typeof Swal !== 'undefined') Swal.fire({ icon: 'success', title: activo ? 'Habilitado' : 'Inhabilitado', timer: 1200, showConfirmButton: false });
                            },
                            onError: function (e) {
                                if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: (e && e.mensaje) || 'Error' });
                            }
                        });
                    });
                    $(document).on('click', '.fp-form-eliminar-formulario', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        var id = parseInt($(this).attr('data-id'), 10);
                        if (isNaN(id) || id < 1 || typeof http === 'undefined') return;
                        var doElim = function () {
                            http.request({
                                endpoint: '/validaciones/eliminarFormulario',
                                metodo: 'POST',
                                data: JSON.stringify({ id: id }),
                                contentType: 'application/json',
                                processData: false,
                                onSuccess: function () {
                                    if (typeof Swal !== 'undefined') Swal.fire({ icon: 'success', title: 'Eliminado' });
                                    fpCargarFormularios(fpRefrescarListaFormularios);
                                },
                                onError: function (err) {
                                    if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: (err && err.mensaje) || 'Error' });
                                }
                            });
                        };
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ title: '¿Eliminar formulario?', text: 'Las preguntas quedarán sin asignar.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, eliminar' }).then(function (r) {
                                if (r.isConfirmed) doElim();
                            });
                        } else {
                            if (confirm('¿Eliminar este formulario?')) doElim();
                        }
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

                    // Click en el item (para navegación futura del "formulario").
                    // Por ahora solo llama una función opcional si existe.
                    $(document).on('click', '.fp-form-item-validacion', function (e) {
                        var $t = $(e.target);
                        if ($t.is('input,button,a') || $t.closest('button,input,a').length) return;
                        var id = parseInt($(this).attr('data-id'), 10);
                        if (isNaN(id) || id < 1) return;
                        if (typeof window.fpIrAFormularioValidacion === 'function') window.fpIrAFormularioValidacion(id);
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
                    if (idFormulario > 0 && $('#listaPreguntasPredefinidasValidacion').length) {
                        fpCargarDesdeServidor(fpRefrescarListaPrincipal);
                    }
                    $('#fpBtnGuardarPregunta').on('click', function () {
                        var tipo = ($('#fpTipoActual').val() || '').trim();
                        var $err = $('#fpEditorError');
                        $err.addClass('d-none').text('');
                        var obj = { tipo: tipo, texto: '', es_predefinida: 0 };
                        if (idFormulario > 0) obj.id_formulario = idFormulario;
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
