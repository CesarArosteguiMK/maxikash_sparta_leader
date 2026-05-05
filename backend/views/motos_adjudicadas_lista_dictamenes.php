<?php
/** @var string $google_maps_api_key_js JSON (comillas incluidas) para asignar a window.MADJ_GOOGLE_MAPS_KEY */
if (!isset($google_maps_api_key_js)) {
    $gmk = defined('GOOGLE_MAPS_API_KEY') ? (string) GOOGLE_MAPS_API_KEY : '';
    $google_maps_api_key_js = json_encode($gmk, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
}
?>
<script>window.MADJ_GOOGLE_MAPS_KEY = <?= $google_maps_api_key_js ?>;</script>

<div class="card mb-4">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <h5 class="card-title mb-0">Lista de dictámenes — Motos adjudicadas</h5>
        </div>
        <button type="button" class="btn btn-primary btn-sm" id="btnDictamenesRefrescar">
            <i class="fa-solid fa-rotate-right me-1"></i>Refrescar
        </button>
    </div>
    <div class="card-body">
        <div class="card-datatable table-responsive">
            <table id="tablaDictamenesMotos" class="dt-responsive table table-striped table-hover border-top w-100">
                <thead>
                    <tr>
                        <th>Cliente / Crédito</th>
                        <th>Marca / modelo</th>
                        <th>Fecha registro</th>
                        <th class="text-center text-nowrap">Acción</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDictamenDetalle" tabindex="-1" aria-labelledby="modalDictamenDetalleTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDictamenDetalleTitulo">Detalle del dictamen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="modalDictamenDetalleCuerpo"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDictamenMedia" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDictamenMediaTitulo">Evidencia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body text-center" id="modalDictamenMediaCuerpo"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    var tablaDictamenes = null;
    /** @type {google.maps.Map|null} */
    var madjDictamenMapa = null;
    var modalMediaEl = document.getElementById('modalDictamenMedia');
    var modalMediaBody = document.getElementById('modalDictamenMediaCuerpo');
    var modalMediaTitulo = document.getElementById('modalDictamenMediaTitulo');
    var modalDetalleEl = document.getElementById('modalDictamenDetalle');
    /** @type {{ hayCoords:boolean, lat:any, lng:any, nom:string, cred:string }|null} */
    var madjDetalleSesion = null;
    var madjDetalleOcultoPorMedia = false;
    var modalDetalleCuerpo = document.getElementById('modalDictamenDetalleCuerpo');

    function escAttr(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    function fmtFechaRegistro(v) {
        if (v == null || v === '') return '—';
        var d = new Date(String(v).replace(' ', 'T'));
        if (!isNaN(d.getTime())) {
            return d.toLocaleString('es-MX');
        }
        return String(v);
    }

    function coordsValidas(lat, lng) {
        var la = parseFloat(lat);
        var lo = parseFloat(lng);
        if (!isFinite(la) || !isFinite(lo)) return false;
        if (Math.abs(la) < 1e-9 && Math.abs(lo) < 1e-9) return false;
        return la >= -90 && la <= 90 && lo >= -180 && lo <= 180;
    }

    function cargarMapaGoogleDictamen(lat, lng, nomCliente, idCredito) {
        var cont = document.getElementById('madjDictamenMapaContenedor');
        if (!cont) return;
        cont.innerHTML = '';
        madjDictamenMapa = null;

        var apiKey =
            typeof window.MADJ_GOOGLE_MAPS_KEY === 'string' && window.MADJ_GOOGLE_MAPS_KEY
                ? String(window.MADJ_GOOGLE_MAPS_KEY).trim()
                : '';
        var la = parseFloat(lat);
        var lo = parseFloat(lng);
        if (!isFinite(la) || !isFinite(lo)) return;

        if (!apiKey) {
            cont.innerHTML =
                '<p class="text-muted small mb-0 p-2">Falta <code>GOOGLE_MAPS_API_KEY</code> en configuración para mostrar el mapa.</p>';
            return;
        }

        function crearMapa() {
            madjDictamenMapa = new google.maps.Map(cont, {
                center: { lat: la, lng: lo },
                zoom: 16,
                mapTypeControl: true,
                streetViewControl: true,
                fullscreenControl: true,
                zoomControl: true
            });
            var title = ((nomCliente || '') + ' · Crédito ' + (idCredito || '')).substring(0, 140);
            var marker = new google.maps.Marker({
                position: { lat: la, lng: lo },
                map: madjDictamenMapa,
                title: title
            });
            var htmlInfo =
                '<strong>' +
                escAttr(nomCliente || 'Cliente') +
                '</strong><br><span class="small text-muted">Crédito: ' +
                escAttr(String(idCredito != null ? idCredito : '—')) +
                '</span>';
            var infow = new google.maps.InfoWindow({ content: htmlInfo });
            infow.open(madjDictamenMapa, marker);
            setTimeout(function () {
                if (madjDictamenMapa && google.maps && google.maps.event) {
                    google.maps.event.trigger(madjDictamenMapa, 'resize');
                    madjDictamenMapa.setCenter({ lat: la, lng: lo });
                }
            }, 250);
        }

        if (typeof google !== 'undefined' && google.maps) {
            crearMapa();
            return;
        }
        if (document.querySelector('script[src*="maps.googleapis.com/maps/api/js"]')) {
            var intentos = 0;
            var t = setInterval(function () {
                intentos++;
                if (typeof google !== 'undefined' && google.maps) {
                    clearInterval(t);
                    crearMapa();
                } else if (intentos > 50) {
                    clearInterval(t);
                    cont.innerHTML =
                        '<p class="text-danger small mb-0 p-2">No se pudo cargar la API de Google Maps. Intente de nuevo.</p>';
                }
            }, 100);
            return;
        }
        var s = document.createElement('script');
        s.src = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent(apiKey);
        s.async = true;
        s.defer = true;
        s.onload = function () {
            crearMapa();
        };
        s.onerror = function () {
            cont.innerHTML = '<p class="text-danger small mb-0 p-2">Error al cargar el script de Google Maps.</p>';
        };
        document.head.appendChild(s);
    }

    function abrirModalMedia(tipo, url) {
        modalMediaTitulo.textContent = tipo === 'video' ? 'Video' : 'Fotografía';
        if (tipo === 'video') {
            modalMediaBody.innerHTML =
                '<div class="ratio ratio-16x9 bg-dark rounded overflow-hidden">' +
                '<video class="position-absolute top-0 start-0 w-100 h-100 object-fit-contain bg-dark" controls playsinline preload="metadata" src="' +
                escAttr(url) +
                '"></video></div>';
        } else {
            modalMediaBody.innerHTML =
                '<img class="img-fluid rounded shadow-sm" src="' + escAttr(url) + '" alt="Evidencia" />';
        }
        if (typeof bootstrap === 'undefined' || !bootstrap.Modal) {
            return;
        }
        var detInst =
            modalDetalleEl && bootstrap.Modal.getInstance(modalDetalleEl)
                ? bootstrap.Modal.getInstance(modalDetalleEl)
                : modalDetalleEl
                  ? bootstrap.Modal.getOrCreateInstance(modalDetalleEl)
                  : null;
        var detalleAbierto = !!(modalDetalleEl && modalDetalleEl.classList.contains('show'));
        if (detalleAbierto && detInst) {
            madjDetalleOcultoPorMedia = true;
            function despuesDeOcultarDetalle() {
                modalDetalleEl.removeEventListener('hidden.bs.modal', despuesDeOcultarDetalle);
                bootstrap.Modal.getOrCreateInstance(modalMediaEl).show();
            }
            modalDetalleEl.addEventListener('hidden.bs.modal', despuesDeOcultarDetalle);
            detInst.hide();
            return;
        }
        madjDetalleOcultoPorMedia = false;
        bootstrap.Modal.getOrCreateInstance(modalMediaEl).show();
    }

    if (modalMediaEl) {
        modalMediaEl.addEventListener('hidden.bs.modal', function () {
            modalMediaBody.innerHTML = '';
            if (madjDetalleOcultoPorMedia && modalDetalleEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                madjDetalleOcultoPorMedia = false;
                bootstrap.Modal.getOrCreateInstance(modalDetalleEl).show();
            }
        });
    }

    if (modalDetalleEl) {
        modalDetalleEl.addEventListener('shown.bs.modal', function () {
            var s = madjDetalleSesion;
            if (!s || !s.hayCoords) {
                return;
            }
            cargarMapaGoogleDictamen(parseFloat(s.lat), parseFloat(s.lng), s.nom, s.cred);
            var geoEl = document.getElementById('madjDetalleRevGeo');
            if (geoEl) {
                intentarEtiquetaMapa(s.lat, s.lng, geoEl);
            }
        });
        modalDetalleEl.addEventListener('hidden.bs.modal', function () {
            var contMap = document.getElementById('madjDictamenMapaContenedor');
            if (contMap) contMap.innerHTML = '';
            madjDictamenMapa = null;
        });
        modalDetalleEl.addEventListener('click', function (ev) {
            var fotoImg = ev.target.closest('img.js-dict-foto-click');
            if (fotoImg) {
                var u = fotoImg.getAttribute('data-url') || fotoImg.getAttribute('src') || '';
                if (u) abrirModalMedia('foto', u);
                return;
            }
            var btn = ev.target.closest('.js-dict-media-inline');
            if (!btn) return;
            var tipo = btn.getAttribute('data-tipo') || 'foto';
            var url = btn.getAttribute('data-url') || '';
            if (url) abrirModalMedia(tipo, url);
        });
    }

    /** Campo solo lectura (patrón modal detalle Bootstrap 5). */
    function madjCampoReadonly(colClass, etiqueta, valor) {
        var v = String(valor == null ? '' : valor).trim();
        var valAttr = v ? escAttr(v) : '';
        return (
            '<div class="' +
            colClass +
            '">' +
            '<label class="form-label fw-semibold small text-secondary">' +
            escAttr(etiqueta) +
            '</label>' +
            '<input type="text" class="form-control bg-light" value="' +
            valAttr +
            '" placeholder="—" readonly />' +
            '</div>'
        );
    }

    function madjComentarioReadonly(texto) {
        var raw = String(texto == null ? '' : texto);
        var inner = escAttr(raw);
        return (
            '<div class="col-12">' +
            '<label class="form-label fw-semibold small text-secondary">Comentario</label>' +
            '<textarea class="form-control bg-light" readonly rows="3" placeholder="—">' +
            inner +
            '</textarea>' +
            '</div>'
        );
    }

    function intentarEtiquetaMapa(lat, lng, elSalida) {
        if (!elSalida || !coordsValidas(lat, lng)) return;
        var wrap = elSalida.closest ? elSalida.closest('[data-madj-revgeo-wrap]') : null;
        if (wrap) {
            wrap.classList.remove('d-none');
        }
        elSalida.value = 'Obteniendo denominación del lugar…';
        var url =
            'https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=' +
            encodeURIComponent(String(lat)) +
            '&lon=' +
            encodeURIComponent(String(lng)) +
            '&accept-language=es-MX';
        fetch(url, { headers: { Accept: 'application/json' }, referrerPolicy: 'strict-origin-when-cross-origin' })
            .then(function (r) {
                return r.json();
            })
            .then(function (j) {
                if (j && j.display_name) {
                    elSalida.value = j.display_name;
                } else {
                    elSalida.value = '';
                    if (wrap) wrap.classList.add('d-none');
                }
            })
            .catch(function () {
                elSalida.value = 'No se pudo obtener el nombre del lugar automáticamente.';
            });
    }

    function madjFmtMoneyMx(val) {
        if (val === null || val === undefined || val === '') {
            return '—';
        }
        var n = Number(val);
        if (!isFinite(n)) {
            return '—';
        }
        return (
            '$ ' +
            n.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
        );
    }

    /** HTML del bloque S2 a partir de la respuesta JSON del backend (o error). */
    function madjHtmlBloqueS2(idCredito, data) {
        if (!idCredito || idCredito <= 0) {
            return '<p class="text-muted small mb-0">Sin ID de crédito para consultar S2.</p>';
        }
        if (!data || !data.success) {
            return (
                '<p class="text-danger small mb-0">' +
                escAttr((data && data.message) || 'No fue posible obtener datos de S2.') +
                '</p>'
            );
        }
        return (
            '<div class="row g-3">' +
            madjCampoReadonly(
                'col-md-4',
                'Total del crédito otorgado',
                madjFmtMoneyMx(data.monto_otorgado)
            ) +
            madjCampoReadonly(
                'col-md-4',
                'Cuotas pagadas',
                data.cuotas_pagadas != null ? String(data.cuotas_pagadas) : ''
            ) +
            madjCampoReadonly(
                'col-md-4',
                'Total pagado por el cliente',
                madjFmtMoneyMx(data.total_pagado_cliente)
            ) +
            '</div>' +
            '<div class="row g-3">' +
            madjCampoReadonly(
                'col-md-6',
                'Fecha último abono efectivo',
                data.ultimo_efectivo_fecha || ''
            ) +
            madjCampoReadonly(
                'col-md-6',
                'Monto último abono efectivo',
                madjFmtMoneyMx(data.ultimo_efectivo_monto)
            ) +
            '</div>'
        );
    }

    function madjMostrarCargandoDetalleS2() {
        if (typeof Swal === 'undefined' || typeof Swal.fire !== 'function') {
            return;
        }
        Swal.fire({
            title: 'Consultando información en S2…',
            html:
                '<p class="small text-secondary fw-normal mt-1 mb-0 text-center">Espere un momento…</p>',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            backdrop: true,
            didOpen: function () {
                if (typeof Swal.showLoading === 'function') Swal.showLoading();
            }
        });
    }

    function madjOcultarCargandoDetalleS2() {
        if (typeof Swal !== 'undefined' && typeof Swal.close === 'function') {
            Swal.close();
        }
    }

    /**
     * Arma el HTML del modal de detalle (con S2 ya resuelto) y guarda sesión para mapa/geocodificación al mostrarse.
     */
    function aplicarCuerpoModalDetalle(row, dataS2) {
        var nom = String(row.nombre_cliente || '').trim();
        var cred = String(row.id_credito != null ? row.id_credito : '').trim();
        var lat = row.lat;
        var lng = row.lng;
        var foto = String(row.url_foto || '').trim();
        var video = String(row.url_video || '').trim();
        var hayCoords = coordsValidas(lat, lng);

        var marcaTxt = String(row.marca_modelo == null ? '' : row.marca_modelo).trim();
        var marcaMay = marcaTxt ? marcaTxt.toLocaleUpperCase('es-MX') : '';

        var coordTxt = hayCoords ? String(lat) + ', ' + String(lng) : '';

        var idCredNum = parseInt(String(cred || '0'), 10);

        madjDetalleSesion = {
            hayCoords: hayCoords,
            lat: lat,
            lng: lng,
            nom: nom,
            cred: cred
        };

        var html = '<div class="container-fluid px-0">';
        html += '<div class="row g-3">';
        html += madjCampoReadonly('col-md-4', 'Nombre del cliente', nom);
        html += madjCampoReadonly('col-md-4', 'ID de crédito', cred);
        html += madjCampoReadonly('col-md-4', 'Kilometraje', row.kilometraje);
        html += madjCampoReadonly('col-md-4', 'Número de serie', row.numero_serie);
        html += madjCampoReadonly('col-md-4', 'Marca / modelo', marcaMay);
        html += madjCampoReadonly('col-md-4', 'Fecha de registro', fmtFechaRegistro(row.fecha_registro));
        html += madjComentarioReadonly(row.comentarios_generales);
        html += '</div>';

        html +=
            '<p class="fw-semibold small text-uppercase text-secondary mt-4 mb-2">S2</p>' +
            '<div class="mb-0">' +
            madjHtmlBloqueS2(idCredNum, dataS2) +
            '</div>';

        html += '<p class="fw-semibold small text-uppercase text-secondary mt-3 mb-2">Evidencias</p>';
        html += '<div class="row g-3">';
        html += '<div class="col-md-6">';
        html +=
            '<label class="form-label fw-semibold small text-secondary">Fotografía</label>';
        if (foto) {
            html +=
                '<div class="ratio ratio-4x3 border rounded overflow-hidden bg-white">' +
                '<img src="' +
                escAttr(foto) +
                '" alt="Foto" data-url="' +
                escAttr(foto) +
                '" class="js-dict-foto-click position-absolute top-0 start-0 w-100 h-100 object-fit-contain cursor-pointer" title="Clic para ampliar"/>' +
                '</div>';
        } else {
            html +=
                '<input type="text" class="form-control bg-light" value="" placeholder="—" readonly />';
        }
        html += '</div>';
        html += '<div class="col-md-6">';
        html += '<label class="form-label fw-semibold small text-secondary">Video</label>';
        if (video) {
            html +=
                '<div class="ratio ratio-4x3 border rounded overflow-hidden bg-dark">' +
                '<video controls playsinline preload="metadata" src="' +
                escAttr(video) +
                '" class="position-absolute top-0 start-0 w-100 h-100 object-fit-contain bg-dark"></video></div>';
        } else {
            html +=
                '<input type="text" class="form-control bg-light" value="" placeholder="—" readonly />';
        }
        html += '</div></div>';

        html += '<p class="fw-semibold small text-uppercase text-secondary mt-3 mb-2">Ubicación</p>';
        if (hayCoords) {
            html +=
                '<div class="row g-3 mb-3">' +
                '<div class="col-12" data-madj-revgeo-wrap="1">' +
                '<label class="form-label fw-semibold small text-secondary">Lugar (aprox.)</label>' +
                '<textarea class="form-control bg-light" readonly rows="3" id="madjDetalleRevGeo" placeholder="—"></textarea>' +
                '</div></div>';
            html +=
                '<div id="madjDictamenMapaWrap" class="mb-3 col-12 col-xl-8 mx-auto">' +
                '<div class="ratio ratio-21x9 rounded border overflow-hidden">' +
                '<div id="madjDictamenMapaContenedor" class="bg-light"></div>' +
                '</div></div>';
            html += '<div class="row g-3">';
            html += madjCampoReadonly('col-12', 'Coordenadas', coordTxt);
            html += '</div>';
        } else {
            html +=
                '<div class="row g-3">' +
                '<div class="col-12">' +
                '<label class="form-label fw-semibold small text-secondary">Coordenadas</label>' +
                '<input type="text" class="form-control bg-light" value="Sin coordenadas de geolocalización en este dictamen." readonly />' +
                '</div></div>';
        }

        html += '</div>';
        modalDetalleCuerpo.innerHTML = html;
    }

    function abrirModalDetalle(row) {
        var cred = String(row.id_credito != null ? row.id_credito : '').trim();
        var idCredNum = parseInt(String(cred || '0'), 10);

        function mostrarModal(dataS2) {
            aplicarCuerpoModalDetalle(row, dataS2);
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(modalDetalleEl).show();
            }
        }

        if (!idCredNum || idCredNum <= 0) {
            mostrarModal(null);
            return;
        }

        madjMostrarCargandoDetalleS2();
        fetch(
            '/MotosAdjudicadas/obtenerResumenS2ModalDictamen?id_credito=' +
                encodeURIComponent(String(idCredNum)),
            { credentials: 'same-origin' }
        )
            .then(function (r) {
                return r.json();
            })
            .then(function (data) {
                madjOcultarCargandoDetalleS2();
                mostrarModal(data);
            })
            .catch(function () {
                madjOcultarCargandoDetalleS2();
                mostrarModal({ success: false, message: 'Error de red al consultar S2.' });
            });
    }

    function renderClienteCredito(row) {
        var nom = String(row.nombre_cliente || '').trim();
        var cred = String(row.id_credito != null ? row.id_credito : '').trim();
        var linea1 = nom ? escAttr(nom) : '<span class="text-muted">Sin nombre en cartera</span>';
        var linea2 =
            '<span class="small text-muted">Crédito: <strong>' +
            escAttr(cred || '—') +
            '</strong></span>';
        return '<div>' + linea1 + '</div><div>' + linea2 + '</div>';
    }

    function columnas() {
        return [
            {
                data: null,
                render: function (data, type, row) {
                    if (type === 'sort' || type === 'filter') {
                        return (
                            String(row.nombre_cliente || '') +
                            ' ' +
                            String(row.id_credito || '')
                        );
                    }
                    return renderClienteCredito(row);
                }
            },
            {
                data: 'marca_modelo',
                defaultContent: '—',
                render: function (d, type) {
                    var t = String(d == null ? '' : d).trim();
                    if (!t) return '—';
                    var may = t.toLocaleUpperCase('es-MX');
                    if (type === 'sort' || type === 'filter') {
                        return may;
                    }
                    return escAttr(may);
                }
            },
            {
                data: 'fecha_registro',
                defaultContent: '—',
                render: function (d, type) {
                    if (type === 'sort' || type === 'filter') {
                        return d == null ? '' : String(d);
                    }
                    return fmtFechaRegistro(d);
                }
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function () {
                    return (
                        '<button type="button" class="btn btn-sm btn-outline-primary js-dict-ver-detalle" title="Ver detalle">' +
                        '<i class="fa-solid fa-eye"></i>' +
                        '</button>'
                    );
                }
            }
        ];
    }

    function opcionesDataTable(filas) {
        return {
            data: filas,
            columns: columnas(),
            pageLength: 5,
            lengthMenu: [[5, 10, 50, -1], [5, 10, 50, 'Todos']],
            order: [[2, 'desc']],
            autoWidth: false,
            language: {
                decimal: '',
                emptyTable: 'No hay dictámenes para mostrar',
                info: 'Mostrando de _START_ a _END_ de _TOTAL_ registros',
                infoEmpty: 'Mostrando de 0 a 0 de 0 registros',
                infoFiltered: '(filtrado de _MAX_ registros totales)',
                thousands: ',',
                lengthMenu: 'Mostrar _MENU_ registros',
                loadingRecords: 'Cargando...',
                processing: 'Procesando...',
                search: 'Buscar:',
                zeroRecords: 'No se encontraron resultados',
                paginate: {
                    first: '«',
                    last: '»',
                    next: '›',
                    previous: '‹'
                }
            },
            dom:
                '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                '<"row"<"col-sm-12"tr>>' +
                '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            deferRender: true,
            drawCallback: function () {
                var c = jQuery('#tablaDictamenesMotos').closest('div.dt-container');
                // El ul.pagination solo es tan ancho como los botones; alinear el contenedor al 100% del col.
                c.find('div.dt-paging, .dataTables_paginate').addClass(
                    'd-flex w-100 justify-content-center justify-content-md-end flex-wrap'
                );
                c.find('div.dt-paging ul.pagination, .dataTables_paginate > .pagination').addClass(
                    'pagination-sm mb-0'
                );
                c.find('div.dt-length select, .dataTables_length select').addClass(
                    'form-select form-select-sm'
                );
                c.find('div.dt-search input, .dataTables_filter input').addClass(
                    'form-control form-control-sm'
                );
            }
        };
    }

    function dictamenListaMostrarCargando() {
        if (typeof Swal === 'undefined' || typeof Swal.fire !== 'function') return;
        Swal.fire({
            title: 'Procesando su petición',
            html:
                '<p class="small text-secondary fw-normal mt-1 mb-0 text-center">Espere un momento...</p>',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            backdrop: true,
            didOpen: function () {
                if (typeof Swal.showLoading === 'function') Swal.showLoading();
            }
        });
    }

    function dictamenListaOcultarCargando() {
        if (typeof Swal !== 'undefined' && typeof Swal.close === 'function') {
            Swal.close();
        }
    }

    function cargar() {
        var btn = document.getElementById('btnDictamenesRefrescar');
        if (btn) btn.disabled = true;
        dictamenListaMostrarCargando();
        fetch('/MotosAdjudicadas/obtenerListaDictamenes', { credentials: 'same-origin' })
            .then(function (r) {
                return r.json();
            })
            .then(function (data) {
                if (!data || !data.success) {
                    throw new Error((data && data.message) || 'No fue posible cargar la lista.');
                }
                dictamenListaOcultarCargando();
                var filas = Array.isArray(data.rows) ? data.rows : [];
                if (!tablaDictamenes) {
                    tablaDictamenes = jQuery('#tablaDictamenesMotos').DataTable(opcionesDataTable(filas));
                } else {
                    tablaDictamenes.clear();
                    tablaDictamenes.rows.add(filas);
                    tablaDictamenes.draw();
                }
            })
            .catch(function (err) {
                dictamenListaOcultarCargando();
                if (window.Swal && Swal.fire) {
                    Swal.fire({ icon: 'error', title: 'Error', text: err.message || String(err) });
                } else {
                    alert(err.message || String(err));
                }
            })
            .finally(function () {
                if (btn) btn.disabled = false;
            });
    }

    /**
     * El layout inserta esta vista ANTES de jquery.js / DataTables en el HTML.
     * No usar $ hasta que existan en window (reintento corto).
     */
    var intentosBootListaDict = 0;
    function bootListaDictamenes() {
        intentosBootListaDict++;
        if (intentosBootListaDict > 120) {
            console.error('Lista dictámenes: jQuery o DataTables no cargaron.');
            return;
        }
        if (typeof window.jQuery === 'undefined' || typeof window.jQuery.fn.DataTable === 'undefined') {
            setTimeout(bootListaDictamenes, 50);
            return;
        }
        var $ = window.jQuery;
        $('#tablaDictamenesMotos').on('click', 'tbody button.js-dict-ver-detalle', function () {
            if (!tablaDictamenes) return;
            var tr = $(this).closest('tr');
            var row = tablaDictamenes.row(tr).data();
            if (row) abrirModalDetalle(row);
        });
        var btnRef = document.getElementById('btnDictamenesRefrescar');
        if (btnRef) btnRef.addEventListener('click', cargar);
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', cargar);
        } else {
            cargar();
        }
    }
    bootListaDictamenes();
})();
</script>
