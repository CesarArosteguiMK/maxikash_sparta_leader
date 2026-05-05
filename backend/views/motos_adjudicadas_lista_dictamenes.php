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
            <small class="text-muted">Dictámenes con opción «Moto adjudicada» (legacy)</small>
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
                        <th>Fecha recuperación</th>
                        <th>Marca / modelo</th>
                        <th>Fecha registro</th>
                        <th class="text-center" style="width:1%">Acción</th>
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
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
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
                <a class="btn btn-outline-primary" id="modalDictamenMediaAbrirPestaña" href="#" target="_blank" rel="noopener noreferrer">
                    Abrir en nueva pestaña
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
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
    var modalMediaLink = document.getElementById('modalDictamenMediaAbrirPestaña');
    var modalDetalleEl = document.getElementById('modalDictamenDetalle');
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

    /** Mismo patrón que Sabueso (panel / rastreo): búsqueda por coordenadas. */
    function urlGoogleMapsSearchApi(lat, lng) {
        return (
            'https://www.google.com/maps/search/?api=1&query=' +
            encodeURIComponent(String(lat) + ',' + String(lng))
        );
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
                '<p class="text-muted small mb-0 p-2">Falta <code>GOOGLE_MAPS_API_KEY</code> en configuración. Use «Abrir en Google Maps (nueva pestaña)».</p>';
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
        modalMediaLink.href = url;
        if (tipo === 'video') {
            modalMediaBody.innerHTML =
                '<video class="w-100 rounded" controls playsinline preload="metadata" style="max-height:85vh;object-fit:contain;background:#000" src="' +
                escAttr(url) +
                '"></video>';
        } else {
            modalMediaBody.innerHTML =
                '<img class="img-fluid rounded shadow-sm" src="' + escAttr(url) + '" alt="Evidencia" />';
        }
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(modalMediaEl).show();
        }
    }

    if (modalMediaEl) {
        modalMediaEl.addEventListener('hidden.bs.modal', function () {
            modalMediaBody.innerHTML = '';
            modalMediaLink.href = '#';
        });
    }

    if (modalDetalleEl) {
        modalDetalleEl.addEventListener('hidden.bs.modal', function () {
            var contMap = document.getElementById('madjDictamenMapaContenedor');
            if (contMap) contMap.innerHTML = '';
            madjDictamenMapa = null;
            var wrapMap = document.getElementById('madjDictamenMapaWrap');
            if (wrapMap) wrapMap.classList.add('d-none');
        });
        modalDetalleEl.addEventListener('click', function (ev) {
            var btn = ev.target.closest('.js-dict-media-inline');
            if (!btn) return;
            var tipo = btn.getAttribute('data-tipo') || 'foto';
            var url = btn.getAttribute('data-url') || '';
            if (url) abrirModalMedia(tipo, url);
        });
    }

    function parEtiquetaValor(etq, val) {
        var v = String(val == null ? '' : val).trim();
        return (
            '<dt class="col-sm-4 col-md-3 text-muted">' + escAttr(etq) + '</dt>' +
            '<dd class="col-sm-8 col-md-9">' + (v ? escAttr(v) : '<span class="text-muted">—</span>') + '</dd>'
        );
    }

    function intentarEtiquetaMapa(lat, lng, elSalida) {
        if (!elSalida || !coordsValidas(lat, lng)) return;
        elSalida.textContent = 'Obteniendo denominación del lugar…';
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
                    elSalida.textContent = j.display_name;
                } else {
                    elSalida.textContent = '';
                    elSalida.classList.add('d-none');
                }
            })
            .catch(function () {
                elSalida.textContent =
                    'No se pudo obtener el nombre del lugar automáticamente. Use «Ver en Google Maps» con las coordenadas.';
            });
    }

    function abrirModalDetalle(row) {
        var nom = String(row.nombre_cliente || '').trim();
        var cred = String(row.id_credito != null ? row.id_credito : '').trim();
        var lat = row.lat;
        var lng = row.lng;
        var foto = String(row.url_foto || '').trim();
        var video = String(row.url_video || '').trim();
        var hayCoords = coordsValidas(lat, lng);

        var html = '<div class="container-fluid px-0">';
        html += '<dl class="row mb-0 gy-2">';
        html += parEtiquetaValor('Nombre del cliente', nom || '—');
        html += parEtiquetaValor('ID de crédito', cred || '—');
        html += parEtiquetaValor('Fecha de recuperación', row.fecha_moto_recuperada);
        html += parEtiquetaValor('Kilometraje', row.kilometraje);
        html += parEtiquetaValor('Número de serie', row.numero_serie);
        html += parEtiquetaValor('Marca / modelo', row.marca_modelo);
        html += '<dt class="col-sm-4 col-md-3 text-muted">Comentario</dt>';
        html +=
            '<dd class="col-sm-8 col-md-9"><div class="text-break">' +
            (String(row.comentarios_generales || '').trim()
                ? escAttr(String(row.comentarios_generales).trim()).replace(/\r?\n/g, '<br>')
                : '<span class="text-muted">—</span>') +
            '</div></dd>';
        html += parEtiquetaValor('Fecha de registro', fmtFechaRegistro(row.fecha_registro));
        html += '</dl>';

        html += '<hr class="my-4">';
        html += '<h6 class="mb-3">Evidencias</h6><div class="row g-3">';
        html += '<div class="col-md-6">';
        html += '<p class="small text-muted mb-1">Fotografía</p>';
        if (foto) {
            html +=
                '<img src="' +
                escAttr(foto) +
                '" alt="Foto" class="img-fluid rounded border mb-2" style="max-height:220px;object-fit:contain"/>' +
                '<div><button type="button" class="btn btn-sm btn-outline-primary js-dict-media-inline" data-tipo="foto" data-url="' +
                escAttr(foto) +
                '"><i class="fa-solid fa-expand me-1"></i>Ver en grande</button></div>';
        } else {
            html += '<span class="text-muted">—</span>';
        }
        html += '</div>';
        html += '<div class="col-md-6">';
        html += '<p class="small text-muted mb-1">Video</p>';
        if (video) {
            html +=
                '<div class="rounded border overflow-hidden bg-dark d-flex justify-content-center align-items-center mb-2" style="max-height:280px">' +
                '<video controls playsinline preload="metadata" src="' +
                escAttr(video) +
                '" class="d-block" style="width:100%;max-height:280px;object-fit:contain"></video></div>' +
                '<div><button type="button" class="btn btn-sm btn-outline-primary js-dict-media-inline" data-tipo="video" data-url="' +
                escAttr(video) +
                '"><i class="fa-solid fa-expand me-1"></i>Ver en grande</button></div>';
        } else {
            html += '<span class="text-muted">—</span>';
        }
        html += '</div></div>';

        html += '<hr class="my-4">';
        html += '<h6 class="mb-3">Ubicación</h6>';
        var dirForm = String(row.direccion_actual || '').trim();
        html += '<dl class="row mb-3">';
        html += parEtiquetaValor('Dirección (formulario)', dirForm || '—');
        if (hayCoords) {
            html +=
                '<dt class="col-sm-4 col-md-3 text-muted">Coordenadas</dt><dd class="col-sm-8 col-md-9"><code>' +
                escAttr(String(lat) + ', ' + String(lng)) +
                '</code></dd>';
            html +=
                '<dt class="col-sm-4 col-md-3 text-muted">Lugar (aprox.)</dt><dd class="col-sm-8 col-md-9"><span id="madjDetalleRevGeo" class="text-break"></span></dd>';
        }
        html += '</dl>';

        if (hayCoords) {
            html +=
                '<div class="d-flex flex-wrap gap-2 align-items-center mb-2">' +
                '<button type="button" class="btn btn-primary" id="madjBtnVerMapaGoogle">' +
                '<i class="fa-solid fa-map-location-dot me-1"></i>Ver en Google Maps</button>' +
                '<a class="btn btn-outline-secondary btn-sm" href="' +
                escAttr(urlGoogleMapsSearchApi(lat, lng)) +
                '" target="_blank" rel="noopener noreferrer">' +
                '<i class="fa-solid fa-external-link-alt me-1"></i>Abrir en nueva pestaña</a>' +
                '</div>' +
                '<div id="madjDictamenMapaWrap" class="d-none">' +
                '<div id="madjDictamenMapaContenedor" class="rounded border overflow-hidden" style="height:380px;width:100%"></div>' +
                '</div>';
        } else {
            html += '<p class="text-muted mb-0">Sin coordenadas de geolocalización en este dictamen.</p>';
        }

        html += '</div>';
        modalDetalleCuerpo.innerHTML = html;

        var geoEl = document.getElementById('madjDetalleRevGeo');
        if (hayCoords && geoEl) {
            intentarEtiquetaMapa(lat, lng, geoEl);
        }

        var btnVerMapa = document.getElementById('madjBtnVerMapaGoogle');
        if (btnVerMapa && hayCoords) {
            btnVerMapa.addEventListener('click', function () {
                var wrap = document.getElementById('madjDictamenMapaWrap');
                if (wrap) wrap.classList.remove('d-none');
                cargarMapaGoogleDictamen(parseFloat(lat), parseFloat(lng), nom, cred);
            });
        }

        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(modalDetalleEl).show();
        }
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
                data: 'fecha_moto_recuperada',
                defaultContent: '—',
                render: function (d) {
                    var t = String(d == null ? '' : d).trim();
                    return t ? escAttr(t) : '—';
                }
            },
            {
                data: 'marca_modelo',
                defaultContent: '—',
                render: function (d) {
                    var t = String(d == null ? '' : d).trim();
                    return t ? escAttr(t) : '—';
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
            order: [[3, 'desc']],
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

    function cargar() {
        var btn = document.getElementById('btnDictamenesRefrescar');
        if (btn) btn.disabled = true;
        fetch('/MotosAdjudicadas/obtenerListaDictamenes', { credentials: 'same-origin' })
            .then(function (r) {
                return r.json();
            })
            .then(function (data) {
                if (!data || !data.success) {
                    throw new Error((data && data.message) || 'No fue posible cargar la lista.');
                }
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
