<?php
/** @var string $google_maps_api_key_js JSON (comillas incluidas) para asignar a window.MADJ_GOOGLE_MAPS_KEY */
if (!isset($google_maps_api_key_js)) {
    $gmk = defined('GOOGLE_MAPS_API_KEY') ? (string) GOOGLE_MAPS_API_KEY : '';
    $google_maps_api_key_js = json_encode($gmk, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
}
?>
<script>window.MADJ_GOOGLE_MAPS_KEY = <?= $google_maps_api_key_js ?>;</script>
<style>
    /* Encabezado alineado con Flujo de Operaciones (banner + jerarquía) */
    .madj-dict-page .madj-dict-card {
        border: 1px solid rgba(0, 0, 0, 0.06);
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
    }
    .madj-dict-page .madj-dict-card .card-body {
        padding-top: 1rem;
    }
    /*
     * Sneat/core.css: backdrop 1089, modal 1090. Antes usábamos ~1072 y el hijo quedaba detrás del padre.
     * Orden: backdrop página (1089) < modal padre (1090) < scrim (1095) < modal hijo (1105).
     */
    .madj-modal-elegir-gestor.modal.show {
        z-index: 1105 !important;
    }

    .modal-backdrop.madj-elegir-gestor-backdrop {
        z-index: 1095 !important;
        background-color: rgba(0, 0, 0, 0.52) !important;
        opacity: 1 !important;
    }

    .madj-dict-page #tablaDictamenesMotos thead th {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-weight: 700;
        color: #334155;
        background-color: #f1f5f9;
        border-bottom: 1px solid #e2e8f0;
        white-space: nowrap;
    }
    .madj-dict-page #tablaDictamenesMotos.table-striped > tbody > tr:nth-of-type(odd) > * {
        --bs-table-accent-bg: rgba(241, 245, 249, 0.55);
    }
</style>

<div class="madj-dict-page">
    <h4 class="mb-2 fw-bold text-body">
        <i class="fa-solid fa-file-signature me-2 text-primary" aria-hidden="true"></i>
        Lista de dictámenes
    </h4>

    <div class="alert alert-primary d-flex align-items-center gap-2 mb-3 py-2 px-3" role="alert">
        <i class="fa-solid fa-circle-info flex-shrink-0" aria-hidden="true"></i>
        <span class="small mb-0">
            <strong>Módulo Motos adjudicadas</strong> — Listado de dictámenes con datos de cliente, marca/modelo y acceso al detalle.
        </span>
    </div>

    <div class="d-flex align-items-center justify-content-end flex-wrap gap-2 mb-3">
        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnDictamenesRefrescar">
            <i class="fa-solid fa-rotate-right me-1"></i>Refrescar
        </button>
    </div>

    <div class="card mb-4 madj-dict-card">
        <div class="card-body pt-0">
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

<div class="modal fade madj-modal-elegir-gestor" id="modalMadjElegirGestor" tabindex="-1" aria-labelledby="modalMadjElegirGestorTitulo" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalMadjElegirGestorTitulo">Seleccionar gestor responsable</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <input type="search" class="form-control form-control-sm mb-2" id="madjFiltroGestorLista" autocomplete="off" placeholder="Buscar responsable…" />
                <div class="list-group list-group-flush border rounded small" id="madjListaGestoresAsignacion" style="max-height: 280px; overflow-y: auto;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
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
    /** @type {{ hayCoords:boolean, lat:any, lng:any, nom:string, cred:string, idCreditoSeg:number|null, rowRef:object, gestorManualElegido:boolean, idPersonaResponsableManual:number|null, nombrePersonaResponsableManual:string }|null} */
    var madjDetalleSesion = null;
    /** @type {null|Array<{id_persona:number,nombre_completo:string}>} */
    var madjCacheResponsablesAdjudicacion = null;
    var madjDetalleOcultoPorMedia = false;
    /** Primer lote de filas para mostrar la tabla rápido; el resto se pide en segundo plano. */
    var MADJ_DICTAMENES_PRIMER_LOTE = 10;
    var madjCargaListaToken = 0;
    var madjNombresSolicitados = {};
    var modalDetalleCuerpo = document.getElementById('modalDictamenDetalleCuerpo');
    var modalElegirGestorEl = document.getElementById('modalMadjElegirGestor');

    /** Scrim entre modal padre e hijo; hijo por encima del padre (tema Sneat: modal 1090). */
    function madjWireModalGestorBackdropSobreDetalle() {
        if (!modalElegirGestorEl || modalElegirGestorEl.getAttribute('data-madj-backdrop-stack') === '1') {
            return;
        }
        modalElegirGestorEl.setAttribute('data-madj-backdrop-stack', '1');

        /* Evita stacking context del layout: el hijo compite en z-index con el padre a nivel viewport. */
        if (modalElegirGestorEl.parentNode !== document.body) {
            document.body.appendChild(modalElegirGestorEl);
        }

        modalElegirGestorEl.addEventListener('shown.bs.modal', function () {
            window.requestAnimationFrame(function () {
                var backs = document.querySelectorAll('.modal-backdrop');
                if (!backs.length) {
                    return;
                }
                var topBd = backs[backs.length - 1];
                topBd.classList.add('madj-elegir-gestor-backdrop');
                topBd.style.setProperty('z-index', '1095', 'important');
                modalElegirGestorEl.style.setProperty('z-index', '1105', 'important');
            });
        });

        modalElegirGestorEl.addEventListener('hide.bs.modal', function () {
            document.querySelectorAll('.modal-backdrop.madj-elegir-gestor-backdrop').forEach(function (b) {
                b.classList.remove('madj-elegir-gestor-backdrop');
                b.style.removeProperty('z-index');
            });
            modalElegirGestorEl.style.removeProperty('z-index');
        });
    }

    madjWireModalGestorBackdropSobreDetalle();

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

    function detenerMediosDentro(contenedor) {
        if (!contenedor || !contenedor.querySelectorAll) {
            return;
        }
        var medias = contenedor.querySelectorAll('video, audio');
        medias.forEach(function (m) {
            try {
                m.pause();
            } catch (e) {}
            try {
                m.currentTime = 0;
            } catch (e2) {}
            try {
                m.removeAttribute('src');
                m.load();
            } catch (e3) {}
        });
    }

    if (modalMediaEl) {
        modalMediaEl.addEventListener('hidden.bs.modal', function () {
            detenerMediosDentro(modalMediaBody);
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
            detenerMediosDentro(modalDetalleEl);
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
        if (data && data.__loading) {
            return madjHtmlBloqueS2Cargando();
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
                'Cuotas contratadas',
                data.cuotas_contratadas != null ? String(data.cuotas_contratadas) : ''
            ) +
            madjCampoReadonly(
                'col-md-4',
                'Cuotas pagadas',
                data.cuotas_pagadas != null ? String(data.cuotas_pagadas) : ''
            ) +
            '</div>' +
            '<div class="row g-3">' +
            madjCampoReadonly(
                'col-md-4',
                'Total pagado por el cliente',
                madjFmtMoneyMx(data.total_pagado_cliente)
            ) +
            madjCampoReadonly(
                'col-md-4',
                'Fecha último abono efectivo',
                data.ultimo_efectivo_fecha || ''
            ) +
            madjCampoReadonly(
                'col-md-4',
                'Monto último abono efectivo',
                madjFmtMoneyMx(data.ultimo_efectivo_monto)
            ) +
            '</div>'
        );
    }

    function madjHtmlBloqueS2Cargando() {
        return (
            '<div class="text-center py-3 text-muted">' +
            '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>' +
            'Consultando información en S2…' +
            '</div>'
        );
    }

    function madjSeguimientoInternoYaPersistido(row) {
        if (!row) {
            return false;
        }
        var com = String(row.ma_seg_comentarios || '').trim();
        if (com === '') {
            return false;
        }
        var ap = row.ma_seg_aplica;
        if (ap === 0 || ap === false || ap === '0') {
            return true;
        }
        if (ap === 1 || ap === true || ap === '1') {
            return !!row.ma_seg_asignacion_ok;
        }

        return false;
    }

    function madjFetchResponsablesAdjudicacion(cb) {
        if (madjCacheResponsablesAdjudicacion) {
            cb(madjCacheResponsablesAdjudicacion);
            return;
        }
        fetch('/Adjudicacion/obtenerListaResponsables', { credentials: 'same-origin' })
            .then(function (r) {
                return r.json();
            })
            .then(function (data) {
                if (data && data.success && Array.isArray(data.responsables)) {
                    madjCacheResponsablesAdjudicacion = data.responsables;
                    cb(madjCacheResponsablesAdjudicacion);
                } else {
                    cb([]);
                }
            })
            .catch(function () {
                cb([]);
            });
    }

    /** Precarga en segundo plano al abrir el detalle, para que «Elegir gestor» sea inmediato. */
    function madjPrefetchResponsablesAdjudicacion() {
        if (madjCacheResponsablesAdjudicacion) {
            return;
        }
        madjFetchResponsablesAdjudicacion(function () {});
    }

    function madjActualizarBadgeGestorSeleccionado() {
        var wrap = document.getElementById('madjGestorSeleccionadoWrap');
        var nomEl = document.getElementById('madjGestorSeleccionadoNombre');
        if (!wrap || !nomEl || !madjDetalleSesion) {
            return;
        }
        var nom = String(madjDetalleSesion.nombrePersonaResponsableManual || '').trim();
        if (madjDetalleSesion.gestorManualElegido && nom !== '') {
            nomEl.textContent = nom;
            wrap.classList.remove('d-none');
            wrap.classList.add('d-inline-flex', 'align-items-center');
        } else {
            nomEl.textContent = '';
            wrap.classList.add('d-none');
            wrap.classList.remove('d-inline-flex', 'align-items-center');
        }
    }

    function madjSetGestorAsignacionSesion(idPersona, nombre) {
        if (!madjDetalleSesion) {
            return;
        }
        madjDetalleSesion.gestorManualElegido = true;
        madjDetalleSesion.idPersonaResponsableManual = idPersona > 0 ? idPersona : null;
        madjDetalleSesion.nombrePersonaResponsableManual = nombre || '';
        madjActualizarBadgeGestorSeleccionado();
    }

    function madjInicializarGestorAsignacionDesdeRow() {
        if (!madjDetalleSesion) {
            return;
        }
        madjDetalleSesion.gestorManualElegido = false;
        madjDetalleSesion.idPersonaResponsableManual = null;
        madjDetalleSesion.nombrePersonaResponsableManual = '';
        madjActualizarBadgeGestorSeleccionado();
    }

    function madjRenderOpcionesGestoresEnModal(lista, filtroTxt) {
        var root = document.getElementById('madjListaGestoresAsignacion');
        if (!root) {
            return;
        }
        var f = String(filtroTxt || '')
            .trim()
            .toLocaleUpperCase('es-MX');
        root.innerHTML = '';
        var hay = false;
        lista.forEach(function (r) {
            var pid = parseInt(String(r.id_persona || '0'), 10);
            var nom = String(r.nombre_completo || '').trim();
            if (pid <= 0 || nom === '') {
                return;
            }
            if (f && nom.toLocaleUpperCase('es-MX').indexOf(f) === -1) {
                return;
            }
            hay = true;
            var a = document.createElement('button');
            a.type = 'button';
            a.className = 'list-group-item list-group-item-action py-2';
            a.textContent = nom;
            a.setAttribute('data-id-persona', String(pid));
            a.setAttribute('data-nombre', nom);
            root.appendChild(a);
        });
        if (!hay) {
            root.innerHTML =
                '<div class="list-group-item text-muted small">No hay coincidencias.</div>';
        }
    }

    function madjWireGestorAsignacionUi(row) {
        madjInicializarGestorAsignacionDesdeRow();
        var btn = document.getElementById('madjBtnElegirGestor');
        var modalEl = document.getElementById('modalMadjElegirGestor');
        var filtro = document.getElementById('madjFiltroGestorLista');
        var listaRoot = document.getElementById('madjListaGestoresAsignacion');
        if (!btn || !modalEl || !listaRoot) {
            return;
        }
        btn.onclick = function () {
            if (filtro) {
                filtro.value = '';
            }
            if (madjCacheResponsablesAdjudicacion) {
                madjRenderOpcionesGestoresEnModal(madjCacheResponsablesAdjudicacion, '');
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    bootstrap.Modal.getOrCreateInstance(modalEl).show();
                }
                return;
            }
            listaRoot.innerHTML =
                '<div class="list-group-item text-center py-3 text-muted small">' +
                '<span class="spinner-border spinner-border-sm me-2 text-primary" role="status" aria-hidden="true"></span>' +
                'Cargando responsables…' +
                '</div>';
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            }
            madjFetchResponsablesAdjudicacion(function (list) {
                madjRenderOpcionesGestoresEnModal(list, filtro ? filtro.value : '');
            });
        };
        if (filtro) {
            filtro.oninput = function () {
                madjFetchResponsablesAdjudicacion(function (list) {
                    madjRenderOpcionesGestoresEnModal(list, filtro.value);
                });
            };
        }
        listaRoot.onclick = function (ev) {
            var t = ev.target;
            if (!t || !t.getAttribute) {
                return;
            }
            if (t.getAttribute('data-id-persona') == null) {
                return;
            }
            var pid = parseInt(String(t.getAttribute('data-id-persona') || '0'), 10);
            var nom = String(t.getAttribute('data-nombre') || '').trim();
            madjSetGestorAsignacionSesion(pid, nom);
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(modalEl).hide();
            }
        };
    }

    /**
     * Arma el HTML del modal de detalle y guarda sesión para mapa/geocodificación al mostrarse.
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
        var puedeElegirGestorAsignacion = !madjSeguimientoInternoYaPersistido(row);

        madjDetalleSesion = {
            hayCoords: hayCoords,
            lat: lat,
            lng: lng,
            nom: nom,
            cred: cred,
            idCreditoSeg: idCredNum > 0 ? idCredNum : null,
            rowRef: row,
            gestorManualElegido: false,
            idPersonaResponsableManual: null,
            nombrePersonaResponsableManual: ''
        };

        var html = '<div class="container-fluid px-0">';
        html +=
            '<section class="border border-2 border-primary rounded p-3 mb-4" aria-label="Datos Legacy">' +
            '<p class="fw-semibold small text-uppercase text-secondary mb-2">Legacy</p>' +
            '<div class="row g-3">';
        html += madjCampoReadonly('col-md-4', 'Nombre del cliente', nom);
        html += madjCampoReadonly('col-md-4', 'ID de crédito', cred);
        html += madjCampoReadonly('col-md-4', 'Kilometraje', row.kilometraje);
        html += madjCampoReadonly('col-md-4', 'Número de serie', row.numero_serie);
        html += madjCampoReadonly('col-md-4', 'Marca / modelo', marcaMay);
        html += madjCampoReadonly('col-md-4', 'Fecha de registro', fmtFechaRegistro(row.fecha_registro));
        html += '</div>';
        html += '<div class="row g-3 mt-0">';
        var gestorLegacyTxt =
            row.gestor_legacy_nombre != null && String(row.gestor_legacy_nombre).trim() !== ''
                ? String(row.gestor_legacy_nombre).trim()
                : '';
        if (puedeElegirGestorAsignacion) {
            html +=
                '<div class="col-md-6">' +
                '<label class="form-label fw-semibold small text-secondary">Gestor a cargo (Legacy)</label>' +
                '<div class="d-flex flex-wrap align-items-center gap-2">' +
                '<div class="input-group input-group-sm flex-grow-1" style="min-width:12rem;">' +
                '<input type="text" class="form-control bg-light" id="madjInputGestorLegacy" readonly value="' +
                escAttr(gestorLegacyTxt) +
                '" placeholder="—" />' +
                '<button type="button" class="btn btn-primary" id="madjBtnElegirGestor" title="Elegir gestor" aria-label="Elegir gestor">' +
                '<i class="fa-solid fa-user-tag"></i>' +
                '</button>' +
                '</div>' +
                '<span id="madjGestorSeleccionadoWrap" class="small text-success d-none gap-1 flex-shrink-0" role="status">' +
                '<i class="fa-solid fa-check" aria-hidden="true"></i>' +
                '<span>Seleccionado: <strong id="madjGestorSeleccionadoNombre"></strong></span>' +
                '</span>' +
                '</div>' +
                '</div>';
        } else {
            html += madjCampoReadonly('col-md-6', 'Gestor a cargo (Legacy)', gestorLegacyTxt);
        }
        html += madjComentarioReadonly(row.comentarios_generales);
        html += '</div></section>';

        html +=
            '<section class="border border-2 border-warning rounded p-3 mb-4" aria-label="Datos S2">' +
            '<p class="fw-semibold small text-uppercase text-secondary mb-2">S2</p>' +
            '<div class="mb-0" id="madjS2Root">' +
            madjHtmlBloqueS2(idCredNum, dataS2) +
            '</div></section>';

        html +=
            '<section class="border border-2 border-primary rounded p-3 mb-4" aria-label="Evidencias Legacy">' +
            '<p class="fw-semibold small text-uppercase text-secondary mb-2">Legacy — Evidencias</p>' +
            '<div class="row g-3">';
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
        html += '</div></div></section>';

        html +=
            '<section class="border border-2 border-primary rounded p-3 mb-0" aria-label="Ubicación Legacy">' +
            '<p class="fw-semibold small text-uppercase text-secondary mb-2">Legacy — Ubicación</p>';
        if (hayCoords) {
            html +=
                '<div class="row g-3 mb-3">' +
                '<div class="col-12" data-madj-revgeo-wrap="1">' +
                '<label class="form-label fw-semibold small text-secondary">Lugar (aprox.)</label>' +
                '<textarea class="form-control bg-light" readonly rows="3" id="madjDetalleRevGeo" placeholder="—"></textarea>' +
                '</div></div>';
            html +=
                '<div id="madjDictamenMapaWrap" class="mb-3 w-100">' +
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

        html += '</section>';

        html +=
            '<section class="border border-2 border-primary rounded p-3 mb-0 mt-4" aria-label="Seguimiento interno">' +
            '<p class="fw-semibold small text-uppercase text-secondary mb-3">Seguimiento interno</p>' +
            '<div class="row g-3">' +
            '<div class="col-12">' +
            '<span id="madjSegBloqueDictamenLbl" class="form-label fw-semibold small text-secondary d-block mb-2">Dictamen de administración de cobranza</span>' +
            '<div class="row g-2">' +
            '<div class="col-auto">' +
            '<select class="form-select form-select-sm" id="madjSegAplicaRecoleccion" aria-label="Aplica para recolección">' +
            '<option value="">— Seleccione —</option>' +
            '<option value="1">Sí aplica para la recolección</option>' +
            '<option value="0">No aplica para la recolección</option>' +
            '</select>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '<div class="col-12">' +
            '<label class="form-label fw-semibold small text-secondary" id="madjSegLblComentarios" for="madjSegComentarios">Comentarios <span class="text-danger">*</span></label>' +
            '<textarea class="form-control" id="madjSegComentarios" rows="3" required aria-required="true" placeholder="Comentarios (obligatorio)…"></textarea>' +
            '</div>' +
            '<div class="col-12 d-flex flex-wrap align-items-center gap-2">' +
            '<button type="button" class="btn btn-primary" id="madjBtnGuardarSeguimiento">Guardar seguimiento</button>' +
            '<span class="small text-muted" id="madjSegGuardadoMsg" role="status"></span>' +
            '</div>' +
            '</div>' +
            '</section>';

        html += '</div>';
        modalDetalleCuerpo.innerHTML = html;
        madjEnlazarFormularioSeguimiento(row);
        if (puedeElegirGestorAsignacion) {
            madjWireGestorAsignacionUi(row);
            madjPrefetchResponsablesAdjudicacion();
        }
    }

    /** Tras guardar o si ya venía de BD: sin botón, campos solo lectura. */
    function madjBloquearUiSeguimientoInterno() {
        var ta = document.getElementById('madjSegComentarios');
        var selAplica = document.getElementById('madjSegAplicaRecoleccion');
        var btn = document.getElementById('madjBtnGuardarSeguimiento');
        var lbl = document.getElementById('madjSegLblComentarios');
        var wrap = btn ? btn.parentElement : null;
        var msg = document.getElementById('madjSegGuardadoMsg');
        if (ta) {
            ta.readOnly = true;
            ta.setAttribute('readonly', 'readonly');
            ta.classList.add('bg-light');
            ta.removeAttribute('required');
            ta.removeAttribute('aria-required');
        }
        if (lbl) {
            lbl.innerHTML =
                'Comentarios <span class="text-muted small fw-normal">(registrado)</span>';
        }
        if (selAplica) {
            selAplica.disabled = true;
            selAplica.classList.add('bg-light');
            selAplica.onchange = null;
        }
        if (btn) {
            btn.remove();
        }
        if (wrap && msg && !document.getElementById('madjSegRegistradoHint')) {
            var hint = document.createElement('span');
            hint.id = 'madjSegRegistradoHint';
            hint.className = 'small text-success me-2';
            hint.textContent = 'Seguimiento registrado.';
            wrap.insertBefore(hint, msg);
        }
        if (msg) {
            msg.textContent = '';
            msg.classList.remove('text-danger', 'text-success', 'text-warning');
            msg.classList.add('text-muted');
        }
        var btnGest = document.getElementById('madjBtnElegirGestor');
        if (btnGest) {
            btnGest.remove();
        }
    }

    function madjEnlazarFormularioSeguimiento(row) {
        var ta = document.getElementById('madjSegComentarios');
        var selAplica = document.getElementById('madjSegAplicaRecoleccion');
        var msg = document.getElementById('madjSegGuardadoMsg');
        var btn = document.getElementById('madjBtnGuardarSeguimiento');
        if (!ta || !selAplica) return;
        ta.value = String(row.ma_seg_comentarios || '');
        var ap = row.ma_seg_aplica;
        if (ap === 1 || ap === true || ap === '1') {
            selAplica.value = '1';
        } else if (ap === 0 || ap === false || ap === '0') {
            selAplica.value = '0';
        } else {
            selAplica.value = '';
        }
        if (msg) {
            msg.textContent = '';
            msg.classList.remove('text-danger', 'text-success');
            msg.classList.add('text-muted');
        }
        selAplica.onchange = function () {
            if (msg) {
                msg.textContent = '';
                msg.classList.remove('text-danger', 'text-success');
                msg.classList.add('text-muted');
            }
        };
        if (btn) {
            btn.onclick = function () {
                madjGuardarSeguimientoInterno();
            };
        }
        if (madjSeguimientoInternoYaPersistido(row)) {
            madjBloquearUiSeguimientoInterno();
        }
    }

    function madjActualizarFilaSeguimientoEnTabla(idCredito, payload) {
        if (!tablaDictamenes || !idCredito) return;
        tablaDictamenes.rows().every(function () {
            var r = this.data() || {};
            if (parseInt(String(r.id_credito || '0'), 10) !== idCredito) {
                return;
            }
            r.ma_seg_comentarios = payload.comentarios;
            r.ma_seg_aplica = payload.aplica;
            this.data(r);
        });
    }

    /** Quita de la tabla los dictámenes del crédito (tras guardar seguimiento ya no deben aparecer). */
    function madjQuitarFilasDictamenPorCredito(idCredito) {
        if (!tablaDictamenes || !idCredito) {
            return;
        }
        var idNum = parseInt(String(idCredito), 10);
        if (idNum <= 0 || typeof tablaDictamenes.rows !== 'function') {
            return;
        }
        tablaDictamenes
            .rows(function (idx, data) {
                return parseInt(String((data && data.id_credito) || '0'), 10) === idNum;
            })
            .remove()
            .draw(false);
    }

    function madjSwalDisponible() {
        return typeof window.Swal !== 'undefined' && typeof Swal.fire === 'function';
    }

    function madjGuardarSeguimientoInterno() {
        var ses = madjDetalleSesion;
        var idCredito = ses && ses.idCreditoSeg ? parseInt(String(ses.idCreditoSeg), 10) : 0;
        var ta = document.getElementById('madjSegComentarios');
        var selAplica = document.getElementById('madjSegAplicaRecoleccion');
        var msg = document.getElementById('madjSegGuardadoMsg');
        var swalOk = madjSwalDisponible();
        var btnColor = '#696cff';
        if (idCredito <= 0 || !ta || !selAplica) return;
        if (ta.readOnly || selAplica.disabled) {
            return;
        }
        var com = String(ta.value || '').trim();
        var aplicaStr = String(selAplica.value || '').trim();
        var lugarAproxEl = document.getElementById('madjDetalleRevGeo');
        var lugarAprox = lugarAproxEl ? String(lugarAproxEl.value || '').trim() : '';
        if (
            lugarAprox === 'Obteniendo denominaciÃ³n del lugarâ€¦' ||
            lugarAprox === 'No se pudo obtener el nombre del lugar automÃ¡ticamente.'
        ) {
            lugarAprox = '';
        }
        if (aplicaStr !== '0' && aplicaStr !== '1') {
            if (swalOk) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Selección requerida',
                    text: 'Seleccione una opción en el desplegable (aplica para recolección).',
                    confirmButtonColor: btnColor
                });
            } else if (msg) {
                msg.textContent = 'Seleccione una opción en el desplegable.';
                msg.classList.add('text-danger');
                msg.classList.remove('text-muted', 'text-success');
            }
            try {
                selAplica.focus();
            } catch (eApl) {}
            return;
        }
        if (!com) {
            if (swalOk) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Comentario requerido',
                    text: 'Escriba un comentario antes de guardar.',
                    confirmButtonColor: btnColor
                });
            } else if (msg) {
                msg.textContent = 'Escriba un comentario antes de guardar.';
                msg.classList.add('text-danger');
                msg.classList.remove('text-muted', 'text-success');
            }
            try {
                ta.focus();
            } catch (e1) {}
            return;
        }
        var apNumPre = parseInt(aplicaStr, 10);
        if (
            apNumPre === 1 &&
            ses &&
            ses.rowRef &&
            !ses.gestorManualElegido &&
            (parseInt(String(ses.rowRef.gestor_legacy_id_persona || '0'), 10) || 0) <= 0
        ) {
            if (swalOk) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Gestor requerido',
                    text: 'No hay Gestor a cargo (Legacy) vinculado a persona. Use «Elegir gestor» y seleccione un responsable.',
                    confirmButtonColor: btnColor
                });
            } else if (msg) {
                msg.textContent = 'Use «Elegir gestor»: no hay persona Legacy vinculada.';
                msg.classList.add('text-danger');
                msg.classList.remove('text-muted', 'text-success');
            }
            return;
        }
        if (swalOk) {
            Swal.fire({
                title: 'Guardando seguimiento',
                html: '<p class="small text-secondary mb-0">Espere un momento…</p>',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: function () {
                    if (typeof Swal.showLoading === 'function') Swal.showLoading();
                }
            });
        } else if (msg) {
            msg.textContent = 'Guardando…';
            msg.classList.remove('text-danger', 'text-success');
            msg.classList.add('text-muted');
        }
        fetch('/MotosAdjudicadas/guardarSeguimientoMaDictamen', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({
                id_credito: idCredito,
                comentarios: com,
                aplica: parseInt(aplicaStr, 10),
                lat: ses && ses.lat != null ? ses.lat : null,
                lng: ses && ses.lng != null ? ses.lng : null,
                lugar_aprox: lugarAprox,
                gestor_manual: !!(ses && ses.gestorManualElegido),
                id_persona_responsable:
                    ses &&
                    ses.gestorManualElegido &&
                    ses.idPersonaResponsableManual != null &&
                    parseInt(aplicaStr, 10) === 1
                        ? parseInt(String(ses.idPersonaResponsableManual), 10)
                        : 0,
                id_persona_responsable_default:
                    ses && ses.rowRef
                        ? parseInt(String(ses.rowRef.gestor_legacy_id_persona || '0'), 10) || 0
                        : 0
            })
        })
            .then(function (r) {
                return r.json();
            })
            .then(function (data) {
                if (swalOk && typeof Swal.close === 'function') {
                    Swal.close();
                }
                if (!data || !data.success) {
                    var errTxt = (data && data.message) || 'No se pudo guardar.';
                    if (swalOk) {
                        Swal.fire({
                            icon: 'error',
                            title: 'No se pudo guardar',
                            text: errTxt,
                            confirmButtonColor: btnColor
                        });
                    } else if (msg) {
                        msg.textContent = errTxt;
                        msg.classList.add('text-danger');
                        msg.classList.remove('text-muted', 'text-success');
                    }
                    return;
                }
                var textoExito = (data && data.message) || 'Seguimiento guardado.';
                var advAsig =
                    data &&
                    data.asignacion &&
                    data.asignacion.success === false &&
                    data.asignacion.message;
                if (msg) {
                    msg.textContent = '';
                    msg.classList.remove('text-danger', 'text-success', 'text-warning');
                    msg.classList.add('text-muted');
                }
                if (swalOk) {
                    if (advAsig) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Guardado con advertencia',
                            text: textoExito,
                            confirmButtonColor: btnColor
                        });
                    } else {
                        Swal.fire({
                            icon: 'success',
                            title: 'Listo',
                            text: textoExito,
                            confirmButtonColor: btnColor,
                            timer: 3800,
                            timerProgressBar: true,
                            showConfirmButton: false
                        });
                    }
                } else if (msg) {
                    msg.textContent = textoExito;
                    msg.classList.remove('text-danger', 'text-muted');
                    msg.classList.add(advAsig ? 'text-warning' : 'text-success');
                }
                var apVal = parseInt(aplicaStr, 10);
                madjActualizarFilaSeguimientoEnTabla(idCredito, {
                    comentarios: com,
                    aplica: apVal
                });
                if (ses && ses.rowRef) {
                    ses.rowRef.ma_seg_comentarios = com;
                    ses.rowRef.ma_seg_aplica = apVal;
                }
                var bloquear = apVal === 0 || !advAsig;
                if (ses && ses.rowRef) {
                    ses.rowRef.ma_seg_asignacion_ok = bloquear;
                }
                if (bloquear) {
                    madjBloquearUiSeguimientoInterno();
                }
                madjQuitarFilasDictamenPorCredito(idCredito);
                if (modalDetalleEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    var instMd = bootstrap.Modal.getInstance(modalDetalleEl);
                    if (instMd) {
                        instMd.hide();
                    }
                }
            })
            .catch(function () {
                if (swalOk && typeof Swal.close === 'function') {
                    Swal.close();
                }
                if (swalOk) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de red',
                        text: 'No se pudo contactar al servidor. Compruebe su conexión e intente de nuevo.',
                        confirmButtonColor: btnColor
                    });
                } else if (msg) {
                    msg.textContent = 'Error de red.';
                    msg.classList.add('text-danger');
                    msg.classList.remove('text-muted', 'text-success');
                }
            });
    }

    function cargarBloqueS2DetalleEnSegundoPlano(idCredito, opts) {
        var root = document.getElementById('madjS2Root');
        if (!root) {
            return;
        }
        if (!idCredito || idCredito <= 0) {
            root.innerHTML = madjHtmlBloqueS2(idCredito, null);
            return;
        }
        var yaMostroCache = opts && opts.hadEmbedded;
        if (!yaMostroCache) {
            root.innerHTML = madjHtmlBloqueS2Cargando();
        }
        fetch(
            '/MotosAdjudicadas/obtenerResumenS2ModalDictamen?id_credito=' +
                encodeURIComponent(String(idCredito)),
            { credentials: 'same-origin' }
        )
            .then(function (r) {
                return r.json();
            })
            .then(function (data) {
                var rootActual = document.getElementById('madjS2Root');
                if (!rootActual) return;
                rootActual.innerHTML = madjHtmlBloqueS2(idCredito, data);
            })
            .catch(function () {
                var rootActual = document.getElementById('madjS2Root');
                if (!rootActual) return;
                rootActual.innerHTML = madjHtmlBloqueS2(idCredito, { success: false, message: 'Error de red al consultar S2.' });
            });
    }

    function abrirModalDetalle(row) {
        var cred = String(row.id_credito != null ? row.id_credito : '').trim();
        var idCredNum = parseInt(String(cred || '0'), 10);
        var emb = row && row.s2_modal_resumen;
        var tieneEmb = !!(emb && emb.success);
        aplicarCuerpoModalDetalle(
            row,
            tieneEmb ? emb : idCredNum > 0 ? { __loading: true } : null
        );
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(modalDetalleEl).show();
        }
        cargarBloqueS2DetalleEnSegundoPlano(idCredNum, { hadEmbedded: tieneEmb });
    }

    function renderClienteCredito(row) {
        var nom = String(row.nombre_cliente || '').trim();
        var cred = String(row.id_credito != null ? row.id_credito : '').trim();
        var linea1 = nom ? escAttr(nom) : '<span class="text-muted">Cargando nombre…</span>';
        var linea2 =
            '<span class="small text-muted">Crédito: <strong>' +
            escAttr(cred || '—') +
            '</strong></span>';
        return '<div>' + linea1 + '</div><div>' + linea2 + '</div>';
    }

    function resolverNombresPendientesEnTabla(maximo) {
        if (!tablaDictamenes) return;
        var ids = [];
        var vistos = {};
        tablaDictamenes.rows({ page: 'current' }).every(function () {
            var r = this.data() || {};
            var idc = parseInt(String(r.id_credito || '0'), 10);
            var nom = String(r.nombre_cliente || '').trim();
            if (idc > 0 && !nom && !madjNombresSolicitados[idc] && !vistos[idc]) {
                ids.push(idc);
                vistos[idc] = true;
            }
        });
        if (maximo && ids.length < maximo) {
            tablaDictamenes.rows().every(function () {
                if (ids.length >= maximo) return;
                var r = this.data() || {};
                var idc = parseInt(String(r.id_credito || '0'), 10);
                var nom = String(r.nombre_cliente || '').trim();
                if (idc > 0 && !nom && !madjNombresSolicitados[idc] && !vistos[idc]) {
                    ids.push(idc);
                    vistos[idc] = true;
                }
            });
        }
        if (maximo && ids.length > maximo) {
            ids = ids.slice(0, maximo);
        }
        if (ids.length === 0) return;

        ids.forEach(function (idc) {
            madjNombresSolicitados[idc] = true;
        });

        fetch(
            '/MotosAdjudicadas/resolverNombresClienteDictamenes?ids=' +
                encodeURIComponent(ids.join(',')),
            { credentials: 'same-origin' }
        )
            .then(function (r) {
                return r.json();
            })
            .then(function (data) {
                if (!data || !data.success || !tablaDictamenes) {
                    return;
                }
                var map = data.nombres || {};
                var huboCambio = false;
                tablaDictamenes.rows().every(function () {
                    var row = this.data() || {};
                    var idc = parseInt(String(row.id_credito || '0'), 10);
                    if (idc <= 0) return;
                    var key = String(idc);
                    var nuevo = typeof map[key] === 'string' ? String(map[key]).trim() : '';
                    if (nuevo && String(row.nombre_cliente || '').trim() !== nuevo) {
                        row.nombre_cliente = nuevo;
                        this.data(row);
                        huboCambio = true;
                    }
                });
                if (huboCambio) {
                    tablaDictamenes.draw(false);
                }
            })
            .catch(function () {
                // Silencioso: seguirá intentando en futuros cambios de página.
            });
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
        madjCargaListaToken++;
        var token = madjCargaListaToken;
        var btn = document.getElementById('btnDictamenesRefrescar');
        if (btn) btn.disabled = true;
        dictamenListaMostrarCargando();
        var urlUnico = '/MotosAdjudicadas/obtenerListaDictamenes?rapido=1';
        fetch(urlUnico, { credentials: 'same-origin' })
            .then(function (r) {
                return r.json();
            })
            .then(function (data) {
                if (token !== madjCargaListaToken) return;
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
                resolverNombresPendientesEnTabla(50);
            })
            .catch(function (err) {
                if (token !== madjCargaListaToken) return;
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
        $('#tablaDictamenesMotos').on('draw.dt', function () {
            resolverNombresPendientesEnTabla(25);
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
