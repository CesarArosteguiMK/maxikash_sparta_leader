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
        this.display.className = 'select-search-display';
        this.display.setAttribute('role', 'button');
        this.display.setAttribute('tabindex', '0');
        var opt0 = this.select.options[this.select.selectedIndex];
        this.display.textContent = opt0 ? opt0.text : 'Selecciona una persona';
        this.selectedValue = this.select.value;
        this.wrapper.appendChild(this.display);
        this.dropdown = document.createElement('div');
        this.dropdown.className = 'select-search-dropdown';
        this.wrapper.appendChild(this.dropdown);
        this.searchInput = document.createElement('input');
        this.searchInput.type = 'text';
        this.searchInput.className = 'select-search-input';
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
            noResults.className = 'select-search-option no-results';
            noResults.textContent = 'No se encontraron resultados';
            this.optionsContainer.appendChild(noResults);
            return;
        }
        var self = this;
        filteredOptions.forEach(function (option) {
            var optionDiv = document.createElement('div');
            optionDiv.className = 'select-search-option';
            optionDiv.textContent = option.text;
            optionDiv.dataset.value = option.value;
            if (String(option.value) === String(self.selectedValue)) optionDiv.classList.add('selected');
            optionDiv.addEventListener('click', function () { self.selectOption(option); });
            self.optionsContainer.appendChild(optionDiv);
        });
    };
    TmSearchableSelect.prototype.selectOption = function (option) {
        this.selectedValue = option.value;
        this.select.value = option.value;
        this.display.textContent = option.text;
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
        var estadoBadge =
            t.asignado_nombre && (t.asignado_nombre + '').trim()
                ? '<span class="badge bg-label-success">Asignado</span>'
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
            '</div><div class="mt-1"><span class="badge bg-label-primary small d-inline-flex align-items-center gap-1"><i class="fa-solid ' +
            icCat +
            '" style="font-size:0.7rem;line-height:1" aria-hidden="true"></i>' +
            catLabel +
            '</span></div>';
        var creditoVal =
            t.id_credito != null && t.id_credito > 0 ? '#' + t.id_credito : t.asunto || t.tipo_categoria || '—';
        var creditoHtml = '<small>' + String(creditoVal).replace(/&/g, '&amp;').replace(/</g, '&lt;') + '</small>';
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
                tabla.columns([10, 11]).visible(false);
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
        if (dt) dt.columns([10, 11]).visible(false);
        $(document).on('click', SEL_TABLA + ' [data-tm-cerrar]', function () {
            cerrarTicket(parseInt($(this).attr('data-tm-cerrar'), 10));
        });
        $(document).on('click', SEL_TABLA + ' [data-tm-eliminar]', function () {
            eliminarTicket(parseInt($(this).attr('data-tm-eliminar'), 10));
        });
        $(document).on('click', SEL_TABLA + ' [data-tm-ver]', function () {
            var id = parseInt($(this).attr('data-tm-ver'), 10);
            if (isNaN(id) || id < 1) return;
            var cache = window._ticketsModuloCache;
            var t = cache && cache[id];
            if (!t) return;
            var fechaCreacion = t.fecha_creacion
                ? new Date(t.fecha_creacion).toLocaleString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })
                : '—';
            var fechaVenc = t.fecha_vencimiento
                ? new Date(t.fecha_vencimiento).toLocaleString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })
                : '—';
            $('#resumenTicketDe').text((t.creador_nombre || '').trim() || '—');
            $('#resumenTicketFecha').text(fechaCreacion);
            $('#resumenTicketVence').text(fechaVenc);
            $('#resumenTicketFolio').text(t.folio || '—');
            var estadoTxt = (t.estado_ticket_nombre || '—').trim() || '—';
            $('#resumenTicketEstado').text(estadoTxt);
            $('#resumenTicketEstadoPill').text((estadoTxt !== '—' ? '\u25CF ' : '') + estadoTxt);
            var prioridadTxt = (t.prioridad_nombre || '—').trim() || '—';
            $('#resumenTicketPrioridad').text(prioridadTxt);
            $('#resumenTicketPrioridadPill').text((prioridadTxt !== '—' ? '\u25CF ' : '') + prioridadTxt);
            $('#resumenTicketPrioridadSide').text((prioridadTxt !== '—' ? '\u25CF ' : '') + prioridadTxt);
            var refVal = t.id_credito != null && t.id_credito > 0 ? 'Crédito #' + t.id_credito : (t.asunto || t.tipo_categoria || '—');
            $('#resumenTicketRef').text(refVal);
            $('#resumenTicketRefSide').text(refVal);
            $('#resumenTicketCreado').text(fechaCreacion);
            $('#resumenTicketAsunto').text((t.asunto || t.tipo_categoria || '—').trim() || '—');
            $('#resumenTicketDescripcion').text((t.descripcion_inicial || '').trim() || '—');
            var notaTexto = (t.nota || '').trim();
            var urlDir = (t.url_direccion || '').trim();
            $('#resumenTicketLinkWrap').empty();
            if (notaTexto || urlDir) {
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
                    var linkHtml = '<a href="' + attrEsc(urlDir) + '" target="_blank" rel="noopener" class="rt-btn-mapa"><i class="fa-solid ' + iconLink + '"></i> ' + labelLink + '</a>';
                    $('#resumenTicketLinkWrap').html(linkHtml);
                }
            } else {
                $('#resumenTicketExtraWrap').addClass('d-none');
            }
            if (t.ds_resultado && String(t.ds_resultado).trim()) {
                $('#resumenTicketDs').html(t.ds_resultado_html || attrEsc(t.ds_resultado));
                $('#resumenTicketDsWrap').removeClass('d-none');
            } else {
                $('#resumenTicketDsWrap').addClass('d-none');
            }
            var idTicket = t.id_ticket;
            var idPersonaActual = t.id_persona_asignada != null && t.id_persona_asignada > 0 ? parseInt(t.id_persona_asignada, 10) : 0;
            var $sel = $('#resumenTicketAsignarSelect');
            $sel.off('change.resumenTicket').empty().append('<option value="">Selecciona una persona</option>');
            $sel.data('resumen-id-ticket', idTicket);
            var modo = (cfg().modo || '').trim();
            var esTerritorial = modo === 'territorial';
            var esGestor = modo === 'gestor';
            var labelCampo = function (campo) {
                if (String(campo) === '8_21') return 'Campo 8–21';
                return 'Campo 1–7';
            };

            // UI según modo
            var $asignarBlock = $('#resumenTicketAsignarBlock');
            if (esGestor) $asignarBlock.addClass('d-none');
            else $asignarBlock.removeClass('d-none');

            if (esTerritorial) {
                $('#resumenTicketAsignadoACapoLabel')
                    .removeClass('d-none')
                    .text('Asignado a: ' + (cfg().nombreCapo || '—'));
                $('#resumenTicketCampoLabel')
                    .text(labelCampo(cfg().campoCapo || '1_7'))
                    .removeClass('d-none');
                $('#resumenTicketMotivoWrap').removeClass('d-none');
                // En territorial ya no se elige segmento por radio; es fijo por el capo actual
                $('#resumenTicketAsignarBlock .btn-group[role="group"]').addClass('d-none');
                $('#resumenTicketAsignarMotivo').val('');
                // Cambiar texto del placeholder
                $sel.empty().append('<option value="">Selecciona un gestor</option>');
            } else {
                $('#resumenTicketAsignadoACapoLabel').addClass('d-none');
                $('#resumenTicketMotivoWrap').addClass('d-none');
                $('#resumenTicketAsignarBlock .btn-group[role="group"]').removeClass('d-none');
            }

            function campoAsignarActual() {
                if (esTerritorial) return (cfg().campoCapo === '8_21' ? '8_21' : '1_7');
                var v = $('input[name="tmAsignarCampo"]:checked').val();
                return v === '8_21' ? '8_21' : '1_7';
            }

            var pidAnterior = idPersonaActual > 0 ? String(idPersonaActual) : '';
            function cargarSelectAsignacionJefes() {
                ensureTmAsignarSearchableSelect();
                var campo = campoAsignarActual();
                var $hint = $('#resumenTicketAsignarHint');
                $hint.addClass('d-none').text('');
                $sel.empty().append('<option value="">' + (esTerritorial ? 'Selecciona un gestor' : 'Selecciona una persona') + '</option>');
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
                            var sub = (p.nombre_puesto || '').trim();
                            var txt = attrEsc((p.nombre_completo || p.nombre || '').trim() || '');
                            if (sub) txt += ' — ' + attrEsc(sub);
                            $sel.append('<option value="' + (p.id || '') + '">' + txt + '</option>');
                        });
                        if (idPersonaActual > 0) {
                            if ($sel.find('option[value="' + idPersonaActual + '"]').length) {
                                $sel.val(String(idPersonaActual));
                            } else {
                                $sel.append('<option value="' + idPersonaActual + '">' + attrEsc((t.asignado_nombre || 'Asignado actual').trim() || ('ID ' + idPersonaActual)) + ' (actual)</option>');
                                $sel.val(String(idPersonaActual));
                            }
                        } else {
                            $sel.val('');
                        }
                        pidAnterior = $sel.val() ? String($sel.val()) : '';
                        if (_tmAsignarSearchableInst && typeof _tmAsignarSearchableInst.refresh === 'function') {
                            _tmAsignarSearchableInst.refresh();
                        }
                    },
                    onError: function () {
                        $sel.append('<option value="" disabled>Error al cargar lista</option>');
                        $hint.removeClass('d-none').text(esTerritorial ? 'No se pudo cargar la lista de gestores.' : 'No se pudo cargar la lista de líderes.');
                    }
                });
            }
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
                        if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'Seleccione un gestor.' });
                        $sel.val(pidAnterior);
                        if (_tmAsignarSearchableInst && typeof _tmAsignarSearchableInst.refresh === 'function') _tmAsignarSearchableInst.refresh();
                        return;
                    }
                    var idP = parseInt(pidStr, 10);
                    if (isNaN(idP) || idP < 1) return;
                    var motivo = ($('#resumenTicketAsignarMotivo').val() || '').trim();
                    if (!motivo) {
                        if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'Debe escribir el motivo del cambio.' });
                        $sel.val(pidAnterior);
                        if (_tmAsignarSearchableInst && typeof _tmAsignarSearchableInst.refresh === 'function') _tmAsignarSearchableInst.refresh();
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
                                var nom = $sel.find('option:selected').text();
                                if (window._ticketsModuloCache && window._ticketsModuloCache[tid]) {
                                    window._ticketsModuloCache[tid].asignado_nombre = nom;
                                    window._ticketsModuloCache[tid].id_persona_asignada = idP;
                                }
                                pidAnterior = String(idP);
                                if (typeof Swal !== 'undefined') Swal.fire({ icon: 'success', title: 'Reasignado', timer: 1500, showConfirmButton: false });
                                if (typeof getTicketsModuloPanel === 'function') getTicketsModuloPanel();
                            } else {
                                if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: (r && r.mensaje) ? r.mensaje : 'Error' });
                            }
                        },
                        onError: function (e) {
                            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: (e && e.mensaje) || 'Error' });
                        }
                    });
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
                                if (typeof Swal !== 'undefined') Swal.fire({ icon: 'success', title: 'Asignación quitada', timer: 1500, showConfirmButton: false });
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
                                if (typeof Swal !== 'undefined') Swal.fire({ icon: 'success', title: 'Asignado', timer: 1500, showConfirmButton: false });
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
                        if (modalLista && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                            var inst = bootstrap.Modal.getInstance(modalLista);
                            if (inst) inst.hide();
                        }
                        if (iframe) {
                            var modo = (cfg().modo || '').trim();
                            var readOnly = modo === 'gestor' || modo === 'territorial';
                            var extra = readOnly ? '&readonly=1' : '';
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
