<?php /** @var string $google_maps_api_key_js */ ?>
<style>
/* ═══════════════════════════════════════════════════════
   Tracking Recolección — variables de color (teal/cyan)
═══════════════════════════════════════════════════════ */
:root {
    --track-color:        #0d9488;
    --track-color-light:  #ccfbf1;
    --track-color-dark:   #0f766e;
    --track-color-badge:  #14b8a6;
    --track-bg-card:      #f0fdfa;
    --track-border:       #99f6e4;
}
body.dark-mode {
    --track-color:        #2dd4bf;
    --track-color-light:  #134e4a;
    --track-color-dark:   #5eead4;
    --track-color-badge:  #2dd4bf;
    --track-bg-card:      #1a2e2c;
    --track-border:       #0d4040;
}

/* ── Cabecera del módulo ── */
.track-header {
    background: linear-gradient(135deg, var(--track-color) 0%, var(--track-color-dark) 100%);
    color: #fff;
    border-radius: .75rem;
    padding: 1.25rem 1.5rem;
    margin-bottom: 1.25rem;
}
.track-header h4 { margin: 0; font-weight: 700; letter-spacing: .5px; }
.track-header .track-subtitle { opacity: .85; font-size: .85rem; margin-top: .2rem; }

/* ── Filtros ── */
.track-filters {
    background: var(--track-bg-card);
    border: 1px solid var(--track-border);
    border-radius: .75rem;
    padding: 1rem 1.25rem;
    margin-bottom: 1.25rem;
}
body.dark-mode .track-filters { background: #1e2d2c; }

/* ── Tabla créditos ── */
#tablaCreditos thead th {
    font-size: .8rem;
    vertical-align: middle;
    white-space: nowrap;
}
#tablaCreditos tbody tr { vertical-align: middle; }

/* ── Tabla rutas ── */
#tablaRutas thead th {
    font-size: .8rem;
    vertical-align: middle;
    white-space: nowrap;
}

/* ── Badges de estatus de confirmación gestor ── */
.badge-conf-pendiente   { background: #fbbf24; color: #000; }
.badge-conf-confirmado  { background: #22c55e; color: #fff; }
.badge-conf-rechazado   { background: #ef4444; color: #fff; }
.badge-conf-en_revision { background: #60a5fa; color: #fff; }

/* ── Badges estatus ruta ── */
.badge-ruta-borrador              { background: #94a3b8; color: #fff; }
.badge-ruta-pendiente_confirmacion{ background: #f59e0b; color: #000; }
.badge-ruta-lista_envio           { background: #3b82f6; color: #fff; }
.badge-ruta-enviada               { background: #8b5cf6; color: #fff; }
.badge-ruta-en_proceso            { background: #0d9488; color: #fff; }
.badge-ruta-concluida             { background: #22c55e; color: #fff; }
.badge-ruta-cancelada             { background: #ef4444; color: #fff; }

/* ── Modal ── */
#modalRegistrarRuta .modal-header {
    background: var(--track-color);
    color: #fff;
    border-radius: .375rem .375rem 0 0;
}
#modalRegistrarRuta .modal-header .btn-close { filter: invert(1); }

/* ── Tabs del modal ── */
.track-tabs .nav-link { color: var(--track-color-dark); border-radius: .5rem .5rem 0 0; }
.track-tabs .nav-link.active {
    background: var(--track-color);
    color: #fff;
    border-color: var(--track-color);
}
body.dark-mode .track-tabs .nav-link { color: var(--track-color); }

/* ── Lista de créditos en modal (sortable) ── */
.track-credito-row {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: .5rem;
    padding: .5rem .75rem;
    margin-bottom: .4rem;
    display: flex;
    align-items: center;
    gap: .5rem;
    cursor: grab;
    user-select: none;
    font-size: .82rem;
}
body.dark-mode .track-credito-row {
    background: #1e293b;
    border-color: #334155;
    color: #e2e8f0;
}
.track-credito-row:active { cursor: grabbing; }
.track-credito-row .drag-handle { color: #94a3b8; font-size: 1rem; }
.track-credito-row .orden-num {
    min-width: 1.4rem;
    font-weight: 700;
    color: var(--track-color);
}
.track-credito-row .btn-remove-cred {
    margin-left: auto;
    padding: .1rem .35rem;
    font-size: .75rem;
}

/* ── Mapa ── */
#trackMapContainer {
    width: 100%;
    height: 340px;
    border-radius: .5rem;
    border: 2px solid var(--track-border);
    overflow: hidden;
    background: #e5e7eb;
}
#trackMap { width: 100%; height: 100%; }
.map-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #6b7280;
    font-size: .9rem;
    flex-direction: column;
    gap: .4rem;
}

/* ── Resumen del modal ── */
.track-summary-chip {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    background: var(--track-color-light);
    color: var(--track-color-dark);
    border-radius: 1rem;
    padding: .2rem .6rem;
    font-size: .78rem;
    font-weight: 600;
    margin: .15rem;
}
body.dark-mode .track-summary-chip { background: #134e4a; color: #5eead4; }

/* ── Select2 / chosen override ── */
.select2-container .select2-selection--multiple {
    min-height: 38px;
    border-color: #ced4da !important;
}
</style>

<div class="container-fluid py-3 px-3 px-md-4">

    <!-- ── Cabecera ── -->
    <div class="track-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h4><i class="fa-solid fa-route me-2"></i>Tracking Recolección — Motos Adjudicadas</h4>
            <div class="track-subtitle">Créditos de adj_operacion disponibles para planeación de ruta física</div>
        </div>
        <button class="btn btn-light fw-semibold" id="btnNuevaRuta">
            <i class="fa-solid fa-plus me-1"></i>Registrar ruta
        </button>
    </div>

    <!-- ── Pestañas principales ── -->
    <ul class="nav nav-tabs track-tabs mb-3" id="trackMainTabs">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabCreditos">
                <i class="fa-solid fa-motorcycle me-1"></i>Créditos disponibles
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="tabRutasBtn" data-bs-toggle="tab" data-bs-target="#tabRutas">
                <i class="fa-solid fa-map-marked-alt me-1"></i>Rutas registradas
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- ══ Tab: Créditos disponibles ══ -->
        <div class="tab-pane fade show active" id="tabCreditos">

            <!-- Filtros -->
            <div class="track-filters">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-sm-4 col-lg-3">
                        <label class="form-label mb-1 small fw-semibold">Estado</label>
                        <select class="form-select form-select-sm" id="filtroEstado">
                            <option value="">— Todos los estados —</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-4 col-lg-3">
                        <label class="form-label mb-1 small fw-semibold">Municipio</label>
                        <select class="form-select form-select-sm" id="filtroMunicipio" disabled>
                            <option value="">— Todos los municipios —</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-4 col-lg-2">
                        <button class="btn btn-sm w-100" id="btnFiltrarCreditos"
                                style="background:var(--track-color);color:#fff;">
                            <i class="fa-solid fa-search me-1"></i>Filtrar
                        </button>
                    </div>
                    <div class="col-12 col-sm-4 col-lg-2">
                        <button class="btn btn-sm btn-outline-secondary w-100" id="btnLimpiarFiltros">
                            <i class="fa-solid fa-eraser me-1"></i>Limpiar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tabla de créditos -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="tablaCreditos" class="table table-hover table-bordered mb-0 w-100" style="font-size:.82rem;">
                            <thead>
                                <tr>
                                    <th>ID Crédito</th>
                                    <th>Cliente</th>
                                    <th>Estado</th>
                                    <th>Municipio</th>
                                    <th>Modelo</th>
                                    <th>BIN / NIV</th>
                                    <th>Estatus Proceso</th>
                                    <th>Confirmación Gestor</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ Tab: Rutas registradas ══ -->
        <div class="tab-pane fade" id="tabRutas">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="tablaRutas" class="table table-hover table-bordered mb-0 w-100" style="font-size:.82rem;">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nombre de ruta</th>
                                    <th>Estado / Municipio</th>
                                    <th>Fecha programada</th>
                                    <th>Estatus</th>
                                    <th>Créditos</th>
                                    <th>Responsables</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- /tab-content -->

</div><!-- /container-fluid -->

<!-- ══════════════════════════════════════════════════════════
     Modal — Registrar / editar ruta
══════════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalRegistrarRuta" tabindex="-1" aria-labelledby="modalRegistrarRutaLabel"
     data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalRegistrarRutaLabel">
                    <i class="fa-solid fa-route me-2"></i>Registrar ruta de recolección
                </h5>
                <button type="button" class="btn-close" id="btnCerrarModal"></button>
            </div>

            <div class="modal-body">

                <!-- ── Sección 1: Datos de la ruta ── -->
                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold">
                            Nombre de ruta <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control form-control-sm"
                               id="rutaNombre" maxlength="100" placeholder="Ej. Ruta GDL Norte Junio">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-semibold">
                            Estado <span class="text-danger">*</span>
                        </label>
                        <select class="form-select form-select-sm" id="rutaEstado">
                            <option value="">— Seleccionar —</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-semibold">
                            Municipio <span class="text-danger">*</span>
                        </label>
                        <select class="form-select form-select-sm" id="rutaMunicipio" disabled>
                            <option value="">— Seleccionar —</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-2">
                        <label class="form-label small fw-semibold">
                            Fecha programada <span class="text-danger">*</span>
                        </label>
                        <input type="date" class="form-control form-control-sm"
                               id="rutaFecha" min="">
                        <div class="form-text text-muted" style="font-size:.72rem;">
                            Mínimo 2 días desde hoy
                        </div>
                    </div>
                </div>

                <!-- ── Sección 2: Usuarios responsables ── -->
                <div class="mb-3">
                    <label class="form-label small fw-semibold">
                        Usuarios responsables de recolección
                        <span class="text-muted">(requerido para enviar)</span>
                    </label>
                    <select class="form-select form-select-sm" id="rutaUsuarios" multiple="multiple">
                    </select>
                </div>

                <!-- ── Sección 3: Agregar créditos ── -->
                <div class="mb-2">
                    <label class="form-label small fw-semibold">
                        Agregar crédito a la ruta
                    </label>
                    <div class="input-group input-group-sm">
                        <select class="form-select" id="rutaCreditoSelect">
                            <option value="">— Buscar crédito (ID · Modelo · BIN) —</option>
                        </select>
                        <button class="btn" id="btnAgregarCredito"
                                style="background:var(--track-color);color:#fff;">
                            <i class="fa-solid fa-plus me-1"></i>Agregar
                        </button>
                    </div>
                </div>

                <!-- ── Lista de créditos en la ruta (sortable) ── -->
                <div class="mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="small fw-semibold text-muted">
                            Créditos en la ruta
                            (<span id="rutaCreditosCount">0</span>)
                        </span>
                        <span class="small text-muted">
                            <i class="fa-solid fa-arrows-up-down me-1"></i>
                            Arrastra para reordenar
                        </span>
                    </div>
                    <div id="rutaCreditosList" style="max-height:280px;overflow-y:auto;border:1px dashed var(--track-border);border-radius:.5rem;padding:.5rem;">
                        <div class="text-center text-muted py-3 small" id="rutaCreditosEmpty">
                            <i class="fa-solid fa-motorcycle opacity-25 fa-2x mb-1 d-block"></i>
                            Aún no hay créditos en esta ruta
                        </div>
                    </div>
                </div>

                <!-- ── Sección 4: Mapa de la ruta ── -->
                <div class="mb-2">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="small fw-semibold">
                            <i class="fa-solid fa-map-location-dot me-1" style="color:var(--track-color);"></i>
                            Mapa de la ruta
                        </span>
                        <button class="btn btn-sm btn-outline-secondary" id="btnRefreshMap" style="font-size:.75rem;">
                            <i class="fa-solid fa-refresh me-1"></i>Actualizar mapa
                        </button>
                    </div>
                    <div id="trackMapContainer">
                        <div class="map-placeholder" id="mapPlaceholder">
                            <i class="fa-solid fa-map fa-2x opacity-30"></i>
                            <span>Agrega créditos para visualizar la ruta</span>
                        </div>
                        <div id="trackMap" style="display:none;"></div>
                    </div>
                    <div class="alert alert-warning py-1 px-2 mt-1 small d-none" id="mapAlertCoords">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i>
                        Algunos créditos no tienen coordenadas ni dirección registrada.
                        La ruta en el mapa puede estar incompleta.
                    </div>
                </div>

            </div><!-- /modal-body -->

            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnCerrarModalFooter">
                    <i class="fa-solid fa-xmark me-1"></i>Cancelar
                </button>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm" id="btnGuardarBorrador"
                            style="background:#94a3b8;color:#fff;">
                        <i class="fa-solid fa-floppy-disk me-1"></i>Guardar borrador
                    </button>
                    <button type="button" class="btn btn-sm" id="btnEnviarRuta"
                            style="background:var(--track-color);color:#fff;">
                        <i class="fa-solid fa-paper-plane me-1"></i>Enviar ruta
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     Modal — Detalle de ruta (solo lectura)
══════════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalDetalleRuta" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background:var(--track-color-dark);color:#fff;">
                <h6 class="modal-title">
                    <i class="fa-solid fa-map-marked-alt me-2"></i>
                    Detalle de ruta
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1);"></button>
            </div>
            <div class="modal-body" id="detalleRutaBody">
                <div class="text-center py-4">
                    <div class="spinner-border" style="color:var(--track-color);"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     Google Maps API
══════════════════════════════════════════════════════════ -->
<?php if (!empty($google_maps_api_key_js)) : ?>
<script>
    window._trackGoogleMapsKey = <?= json_encode((string) $google_maps_api_key_js) ?>;
</script>
<?php else : ?>
<script>window._trackGoogleMapsKey = null;</script>
<?php endif; ?>

<!-- SortableJS (drag-and-drop sin jQuery UI) -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

<script>
/* ═══════════════════════════════════════════════════════
   tracking_recoleccion.js — lógica del módulo
═══════════════════════════════════════════════════════ */
'use strict';

// ─── Estado local ───────────────────────────────────────
const _trk = {
    creditosDisponibles:  [],   // todos los créditos disponibles del servidor
    creditosEnRuta:       [],   // créditos actualmente en el modal
    usuariosDisponibles:  [],   // personas activas
    usuariosSeleccionados:[],   // ids seleccionados
    idRutaEditando:       null, // null = nueva ruta
    haycambios:           false,
    tablaCreditosDT:      null,
    tablaRutasDT:         null,
    sortableInstance:     null,
    mapInstance:          null,
    mapLoaded:            false,
    geocoder:             null,
};

// ─── Utilidades ─────────────────────────────────────────
const trkFetch = (url, opts = {}) =>
    fetch(url, { credentials: 'same-origin', ...opts })
        .then(r => r.json());



const trkConfirm = (msg) => new Promise(resolve => {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¿Salir sin guardar?',
            text: msg,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, salir',
            cancelButtonText: 'Quedarme',
            confirmButtonColor: '#ef4444',
        }).then(r => resolve(r.isConfirmed));
    } else {
        resolve(confirm(msg));
    }
});

// Mapeo estatus
const CONF_LABEL = {
    pendiente:   '<span class="badge badge-conf-pendiente">Pendiente</span>',
    confirmado:  '<span class="badge badge-conf-confirmado">Confirmado</span>',
    rechazado:   '<span class="badge badge-conf-rechazado">Rechazado</span>',
    en_revision: '<span class="badge badge-conf-en_revision">En revisión</span>',
};
const RUTA_LABEL = {
    borrador:               '<span class="badge badge-ruta-borrador">Borrador</span>',
    pendiente_confirmacion: '<span class="badge badge-ruta-pendiente_confirmacion">Pend. confirmación</span>',
    lista_envio:            '<span class="badge badge-ruta-lista_envio">Lista para enviar</span>',
    enviada:                '<span class="badge badge-ruta-enviada">Enviada</span>',
    en_proceso:             '<span class="badge badge-ruta-en_proceso">En proceso</span>',
    concluida:              '<span class="badge badge-ruta-concluida">Concluida</span>',
    cancelada:              '<span class="badge badge-ruta-cancelada">Cancelada</span>',
};

// ─── Inicialización ─────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    _trkInicializarFiltros();
    _trkInicializarTablaCreditosDT();
    _trkInicializarTablaRutasDT();
    _trkCargarEstados();
    _trkCargarCreditosPaso2();
    _trkCargarUsuarios();
    _trkInicializarModal();

    // Cambiar tab → cargar rutas
    document.getElementById('tabRutasBtn').addEventListener('click', () => {
        _trkCargarRutas();
    });

    document.getElementById('btnNuevaRuta').addEventListener('click', () => {
        _trkAbrirModalNuevo();
    });
});

// ─── Filtros ─────────────────────────────────────────────
function _trkInicializarFiltros() {
    $('#filtroEstado').on('change', function () {
        const est = $(this).val();
        const $mun = $('#filtroMunicipio');
        $mun.html('<option value="">— Cargando… —</option>').prop('disabled', true);
        if (!est) {
            $mun.html('<option value="">— Todos los municipios —</option>').prop('disabled', true);
            return;
        }
        trkFetch(`/TrackingRecoleccion/obtenerMunicipios?estado=${encodeURIComponent(est)}`)
            .then(r => {
                $mun.html('<option value="">— Todos los municipios —</option>');
                (r.datos || []).forEach(m => {
                    $mun.append(`<option value="${m}">${m}</option>`);
                });
                $mun.prop('disabled', false);
            })
            .catch(() => $mun.html('<option value="">— Error —</option>'));
    });

    $('#btnFiltrarCreditos').on('click', function () {
        _trkCargarCreditosPaso2();
    });

    $('#btnLimpiarFiltros').on('click', function () {
        $('#filtroEstado').val('');
        $('#filtroMunicipio').html('<option value="">— Todos los municipios —</option>').prop('disabled', true);
        _trkCargarCreditosPaso2();
    });
}

function _trkCargarEstados() {
    trkFetch('/TrackingRecoleccion/obtenerEstados')
        .then(r => {
            const estados = r.datos || [];
            const $selFiltro = $('#filtroEstado');
            const $selModal  = $('#rutaEstado');
            estados.forEach(e => {
                $selFiltro.append(`<option value="${e}">${e}</option>`);
                $selModal.append(`<option value="${e}">${e}</option>`);
            });
        });
}

// ─── Tabla de créditos ──────────────────────────────────
function _trkInicializarTablaCreditosDT() {
    _trk.tablaCreditosDT = $('#tablaCreditos').DataTable({
        language: {
            emptyTable:  'No hay créditos disponibles',
            info:        'Mostrando de _START_ a _END_ de _TOTAL_ registros',
            infoEmpty:   'Sin registros para mostrar',
            zeroRecords: 'No se encontraron registros',
            lengthMenu:  'Mostrar _MENU_ registros',
            search:      'Buscar:',
        },
        pageLength: 25,
        responsive: true,
        columns: [
            { data: 'id_credito' },
            { data: 'nombre_cliente' },
            { data: 'estado',    defaultContent: '—' },
            { data: 'municipio', defaultContent: '—' },
            {
                data: null,
                render: r => [r.moto_marca, r.moto_modelo].filter(Boolean).join(' ') || '—',
            },
            { data: 'bin', defaultContent: '—' },
            { data: 'estatus_proceso', defaultContent: '—' },
            {
                data: null,
                render: r => CONF_LABEL['pendiente'],   // siempre pendiente en esta tabla
            },
            {
                data: null,
                orderable: false,
                render: r => `<button class="btn btn-sm btn-outline-success py-0 px-1 btn-agregar-a-ruta"
                                  data-id="${r.id_credito}"
                                  title="Agregar a ruta">
                                  <i class="fa-solid fa-plus"></i>
                              </button>`,
            },
        ],
    });

    $('#tablaCreditos').on('click', '.btn-agregar-a-ruta', function () {
        const idCred = $(this).data('id');
        const cred   = _trk.creditosDisponibles.find(c => String(c.id_credito) === String(idCred));
        if (!cred) return;
        _trkAbrirModalConCredito(cred);
    });
}

function _trkCargarCreditosPaso2() {
    const estado    = $('#filtroEstado').val();
    const municipio = $('#filtroMunicipio').val();
    let url = '/TrackingRecoleccion/obtenerCreditosPaso2';
    const params = [];
    if (estado)    params.push(`estado=${encodeURIComponent(estado)}`);
    if (municipio) params.push(`municipio=${encodeURIComponent(municipio)}`);
    if (params.length) url += '?' + params.join('&');

    trkFetch(url)
        .then(r => {
            _trk.creditosDisponibles = r.datos || [];
            if (_trk.tablaCreditosDT) {
                _trk.tablaCreditosDT.clear().rows.add(_trk.creditosDisponibles).draw();
            }
            _trkRefrescarSelectCreditos();
        })
        .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Error al cargar créditos.', confirmButtonText: 'Aceptar' }));
}

// ─── Tabla de rutas ─────────────────────────────────────
function _trkInicializarTablaRutasDT() {
    _trk.tablaRutasDT = $('#tablaRutas').DataTable({
        language: {
            emptyTable:  'No hay rutas registradas',
            info:        'Mostrando de _START_ a _END_ de _TOTAL_ registros',
            infoEmpty:   'Sin registros para mostrar',
            zeroRecords: 'No se encontraron registros',
            lengthMenu:  'Mostrar _MENU_ registros',
            search:      'Buscar:',
        },
        pageLength: 20,
        responsive: true,
        columns: [
            { data: 'id_ruta' },
            { data: 'nombre_ruta' },
            {
                data: null,
                render: r => [r.estado, r.municipio].filter(Boolean).join(' / ') || '—',
            },
            { data: 'fecha_programada_fmt', defaultContent: '—' },
            {
                data: 'estatus_ruta',
                render: v => RUTA_LABEL[v] || `<span class="badge bg-secondary">${v}</span>`,
            },
            {
                data: null,
                render: r => `<span class="badge bg-secondary">${r.total_creditos || 0}</span>
                              &nbsp;
                              <span title="Confirmados" class="badge badge-conf-confirmado">${r.confirmados || 0}</span>
                              <span title="Pendientes"  class="badge badge-conf-pendiente">${r.pendientes || 0}</span>
                              <span title="Rechazados"  class="badge badge-conf-rechazado">${r.rechazados || 0}</span>`,
            },
            { data: 'usuarios_responsables', defaultContent: '—' },
            {
                data: null,
                orderable: false,
                render: r => `<button class="btn btn-sm btn-outline-primary py-0 px-1 btn-ver-ruta"
                                  data-id="${r.id_ruta}" title="Ver detalle">
                                  <i class="fa-solid fa-eye"></i>
                              </button>`,
            },
        ],
    });

    $('#tablaRutas').on('click', '.btn-ver-ruta', function () {
        _trkVerDetalleRuta($(this).data('id'));
    });
}

function _trkCargarRutas() {
    trkFetch('/TrackingRecoleccion/obtenerRutas', { method: 'POST' })
        .then(r => {
            const rutas = r.datos || [];
            if (_trk.tablaRutasDT) {
                _trk.tablaRutasDT.clear().rows.add(rutas).draw();
            }
        })
        .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Error al cargar rutas.', confirmButtonText: 'Aceptar' }));
}

// ─── Usuarios ────────────────────────────────────────────
function _trkCargarUsuarios() {
    trkFetch('/TrackingRecoleccion/obtenerUsuariosRecoleccion')
        .then(r => {
            _trk.usuariosDisponibles = r.datos || [];
            const $sel = $('#rutaUsuarios');
            _trk.usuariosDisponibles.forEach(u => {
                $sel.append(`<option value="${u.id}">${u.nombre}</option>`);
            });
        });
}

// ─── Modal — apertura ────────────────────────────────────
function _trkInicializarModal() {
    // Estado
    $('#rutaEstado').on('change', function () {
        const est = $(this).val();
        const $mun = $('#rutaMunicipio');
        $mun.html('<option value="">— Cargando… —</option>').prop('disabled', true);
        if (!est) {
            $mun.html('<option value="">— Seleccionar —</option>').prop('disabled', true);
            return;
        }
        trkFetch(`/TrackingRecoleccion/obtenerMunicipios?estado=${encodeURIComponent(est)}`)
            .then(r => {
                $mun.html('<option value="">— Seleccionar —</option>');
                (r.datos || []).forEach(m => $mun.append(`<option value="${m}">${m}</option>`));
                $mun.prop('disabled', false);
            });
        _trk.haychangios = true;
    });

    // Fecha mínima
    const minDate = (() => {
        const d = new Date();
        d.setDate(d.getDate() + 2);
        return d.toISOString().split('T')[0];
    })();
    document.getElementById('rutaFecha').min = minDate;

    // Usuarios multi-select → Select2
    $('#rutaUsuarios').select2({
        width: '100%',
        placeholder: '— Buscar y seleccionar usuarios —',
        allowClear: true,
        closeOnSelect: false,
        dropdownParent: $('#modalRegistrarRuta'),
        language: {
            noResults: () => 'Sin resultados',
            searching: () => 'Buscando…',
        },
    });
    $('#rutaUsuarios').on('change', function () {
        _trk.usuariosSeleccionados = Array.from(this.selectedOptions).map(o => ({
            id: Number(o.value),
            nombre: o.text,
        }));
        _trk.haychangios = true;
    });

    // Agregar crédito
    $('#btnAgregarCredito').on('click', function () {
        const $sel = $('#rutaCreditoSelect');
        const idCred = $sel.val();
        if (!idCred) return;
        const cred = _trk.creditosDisponibles.find(c => String(c.id_credito) === String(idCred));
        if (!cred) return;
        _trkAgregarCreditoALista(cred);
        $sel.val('');
        _trk.haychangios = true;
    });

    // Mapa refresh
    $('#btnRefreshMap').on('click', _trkRenderizarMapa);

    // Guardar
    $('#btnGuardarBorrador').on('click', () => _trkGuardarRuta('borrador'));
    $('#btnEnviarRuta').on('click', () => _trkGuardarRuta('enviar'));

    // Cerrar con aviso
    const _closeFn = async () => {
        if (_trk.haychangios) {
            const ok = await trkConfirm('Tienes cambios sin guardar. ¿Deseas salir sin guardar?');
            if (!ok) return;
        }
        bootstrap.Modal.getInstance(document.getElementById('modalRegistrarRuta'))?.hide();
    };
    document.getElementById('btnCerrarModal').addEventListener('click', _closeFn);
    document.getElementById('btnCerrarModalFooter').addEventListener('click', _closeFn);

    // Drag-and-drop
    _trk.sortableInstance = Sortable.create(document.getElementById('rutaCreditosList'), {
        handle: '.drag-handle',
        animation: 150,
        onEnd: () => {
            _trkRecalcularOrden();
            _trk.haychangios = true;
        },
    });
}

function _trkAbrirModalNuevo() {
    _trkResetModal();
    const modal = new bootstrap.Modal(document.getElementById('modalRegistrarRuta'));
    modal.show();
}

function _trkAbrirModalConCredito(cred) {
    _trkResetModal();
    _trkAgregarCreditoALista(cred);
    // Pre-seleccionar estado/municipio del crédito
    if (cred.estado) {
        const $est = $('#rutaEstado');
        if ($est.find(`option[value="${cred.estado}"]`).length) {
            $est.val(cred.estado).trigger('change');
            setTimeout(() => {
                if (cred.municipio) {
                    const $mun = $('#rutaMunicipio');
                    // Esperar a que carguen los municipios
                    const wait = setInterval(() => {
                        if ($mun.find(`option[value="${cred.municipio}"]`).length) {
                            clearInterval(wait);
                            $mun.val(cred.municipio);
                        }
                    }, 200);
                    setTimeout(() => clearInterval(wait), 3000);
                }
            }, 500);
        }
    }
    const modal = new bootstrap.Modal(document.getElementById('modalRegistrarRuta'));
    modal.show();
}

function _trkResetModal() {
    _trk.idRutaEditando    = null;
    _trk.creditosEnRuta    = [];
    _trk.usuariosSeleccionados = [];
    _trk.haychangios       = false;
    $('#rutaNombre').val('');
    $('#rutaEstado').val('');
    $('#rutaMunicipio').html('<option value="">— Seleccionar —</option>').prop('disabled', true);
    const minDate = (() => {
        const d = new Date();
        d.setDate(d.getDate() + 2);
        return d.toISOString().split('T')[0];
    })();
    $('#rutaFecha').val('').attr('min', minDate);
    if ($('#rutaUsuarios').hasClass('select2-hidden-accessible')) {
        $('#rutaUsuarios').val(null).trigger('change');
    } else {
        $('#rutaUsuarios').val([]);
    }
    document.getElementById('rutaCreditosList').innerHTML =
        `<div class="text-center text-muted py-3 small" id="rutaCreditosEmpty">
            <i class="fa-solid fa-motorcycle opacity-25 fa-2x mb-1 d-block"></i>
            Aún no hay créditos en esta ruta
        </div>`;
    $('#rutaCreditosCount').text(0);
    _trkRefrescarSelectCreditos();
    _trkOcultarMapa();
    $('#mapAlertCoords').addClass('d-none');
    document.getElementById('modalRegistrarRutaLabel').innerHTML =
        '<i class="fa-solid fa-route me-2"></i>Registrar ruta de recolección';
}

// ─── Créditos en el modal ────────────────────────────────
function _trkRefrescarSelectCreditos() {
    const $sel = $('#rutaCreditoSelect');
    const idsEnRuta = new Set(_trk.creditosEnRuta.map(c => String(c.id_credito)));
    $sel.html('<option value="">— Buscar crédito (ID · Modelo · BIN) —</option>');
    _trk.creditosDisponibles.forEach(c => {
        if (idsEnRuta.has(String(c.id_credito))) return;
        const modelo = [c.moto_marca, c.moto_modelo].filter(Boolean).join(' ');
        const label  = `#${c.id_credito} · ${modelo || '(sin modelo)'} · ${c.bin || '—'}`;
        $sel.append(`<option value="${c.id_credito}">${label}</option>`);
    });
}

function _trkAgregarCreditoALista(cred) {
    // RN-03: no duplicados
    if (_trk.creditosEnRuta.find(c => String(c.id_credito) === String(cred.id_credito))) {
        Swal.fire({ icon: 'warning', title: 'Aviso', text: 'Este crédito ya está en la ruta.', confirmButtonText: 'Aceptar' });
        return;
    }
    cred.orden_ruta = _trk.creditosEnRuta.length + 1;
    cred.estatus_confirmacion_gestor = cred.estatus_confirmacion_gestor || 'pendiente';
    _trk.creditosEnRuta.push(cred);
    _trkRenderListaCreditos();
    _trkRefrescarSelectCreditos();
    _trkRenderizarMapa();
}

function _trkQuitarCredito(idCred) {
    _trk.creditosEnRuta = _trk.creditosEnRuta.filter(c => String(c.id_credito) !== String(idCred));
    _trkRecalcularOrden();
    _trkRenderListaCreditos();
    _trkRefrescarSelectCreditos();
    _trkRenderizarMapa();
    _trk.haychangios = true;
}

function _trkRenderListaCreditos() {
    const $list = $('#rutaCreditosList');
    const isEmpty = _trk.creditosEnRuta.length === 0;
    $('#rutaCreditosCount').text(_trk.creditosEnRuta.length);

    if (isEmpty) {
        $list.html(`<div class="text-center text-muted py-3 small" id="rutaCreditosEmpty">
            <i class="fa-solid fa-motorcycle opacity-25 fa-2x mb-1 d-block"></i>
            Aún no hay créditos en esta ruta
        </div>`);
        return;
    }

    $list.html('');
    _trk.creditosEnRuta.forEach((c, idx) => {
        const modelo = [c.moto_marca, c.moto_modelo].filter(Boolean).join(' ') || '—';
        const badgeConf = CONF_LABEL[c.estatus_confirmacion_gestor] || CONF_LABEL['pendiente'];
        const html = `
        <div class="track-credito-row" data-id="${c.id_credito}">
            <i class="fa-solid fa-grip-vertical drag-handle"></i>
            <span class="orden-num">${idx + 1}</span>
            <div class="d-flex flex-column gap-0 flex-grow-1" style="min-width:0;">
                <span class="fw-semibold text-truncate">#${c.id_credito} — ${c.nombre_cliente || '—'}</span>
                <span class="text-muted" style="font-size:.75rem;">
                    ${modelo} · BIN: ${c.bin || '—'}
                    &nbsp;|&nbsp;${c.estado || '—'}, ${c.municipio || '—'}
                </span>
            </div>
            ${badgeConf}
            <select class="form-select form-select-sm py-0 ms-1 select-conf-gestor"
                    style="max-width:130px;font-size:.75rem;"
                    data-id="${c.id_credito}">
                <option value="pendiente"   ${c.estatus_confirmacion_gestor === 'pendiente'   ? 'selected' : ''}>Pendiente</option>
                <option value="confirmado"  ${c.estatus_confirmacion_gestor === 'confirmado'  ? 'selected' : ''}>Confirmado</option>
                <option value="rechazado"   ${c.estatus_confirmacion_gestor === 'rechazado'   ? 'selected' : ''}>Rechazado</option>
                <option value="en_revision" ${c.estatus_confirmacion_gestor === 'en_revision' ? 'selected' : ''}>En revisión</option>
            </select>
            <button class="btn btn-outline-danger btn-remove-cred" data-id="${c.id_credito}" title="Quitar">
                <i class="fa-solid fa-trash-alt"></i>
            </button>
        </div>`;
        $list.append(html);
    });

    // Eventos
    $list.find('.btn-remove-cred').off('click').on('click', function () {
        _trkQuitarCredito($(this).data('id'));
    });
    $list.find('.select-conf-gestor').off('change').on('change', function () {
        const id  = $(this).data('id');
        const val = $(this).val();
        const c   = _trk.creditosEnRuta.find(x => String(x.id_credito) === String(id));
        if (c) {
            c.estatus_confirmacion_gestor = val;
            _trkRenderListaCreditos();   // re-render para badge
        }
        _trk.haychangios = true;
    });
}

function _trkRecalcularOrden() {
    const items = document.querySelectorAll('#rutaCreditosList .track-credito-row');
    const newOrder = Array.from(items).map(el => el.dataset.id);
    _trk.creditosEnRuta.sort((a, b) => {
        const ia = newOrder.indexOf(String(a.id_credito));
        const ib = newOrder.indexOf(String(b.id_credito));
        return (ia === -1 ? 99 : ia) - (ib === -1 ? 99 : ib);
    });
    _trk.creditosEnRuta.forEach((c, i) => { c.orden_ruta = i + 1; });
    // Actualizar numeración visual
    items.forEach((el, i) => {
        const numEl = el.querySelector('.orden-num');
        if (numEl) numEl.textContent = i + 1;
    });
}



// ─── Mapa ────────────────────────────────────────────────
function _trkOcultarMapa() {
    document.getElementById('trackMap').style.display      = 'none';
    document.getElementById('mapPlaceholder').style.display = 'flex';
}

function _trkRenderizarMapa() {
    const creditos = _trk.creditosEnRuta;
    if (!creditos.length) {
        _trkOcultarMapa();
        return;
    }
    // Verificar si alguno tiene coordenadas o dirección
    const sinUbicacion = creditos.filter(c =>
        !(c.latitud && c.longitud) && !String(c.direccion || '').trim()
    );
    if (sinUbicacion.length > 0) {
        document.getElementById('mapAlertCoords').classList.remove('d-none');
    } else {
        document.getElementById('mapAlertCoords').classList.add('d-none');
    }
    if (!window._trackGoogleMapsKey) {
        document.getElementById('mapPlaceholder').innerHTML =
            '<i class="fa-solid fa-triangle-exclamation fa-2x opacity-30"></i>' +
            '<span>Google Maps no disponible (falta API key)</span>';
        return;
    }

    document.getElementById('trackMap').style.display      = 'block';
    document.getElementById('mapPlaceholder').style.display = 'none';

    if (!_trk.mapLoaded) {
        // Cargar Google Maps API dinámicamente
        const script     = document.createElement('script');
        script.src       = `https://maps.googleapis.com/maps/api/js?key=${window._trackGoogleMapsKey}&libraries=geometry&callback=_trkMapCallback`;
        script.async     = true;
        script.defer     = true;
        document.head.appendChild(script);
        _trk.mapLoaded = true;
        window._trkMapCallback = () => _trkDibujarMapa(creditos);
    } else if (typeof google !== 'undefined' && google.maps) {
        _trkDibujarMapa(creditos);
    }
}

function _trkDibujarMapa(creditos) {
    const mapDiv = document.getElementById('trackMap');
    if (!mapDiv || typeof google === 'undefined') return;

    if (!_trk.mapInstance) {
        _trk.mapInstance = new google.maps.Map(mapDiv, {
            zoom: 10,
            center: { lat: 20.6597, lng: -103.3496 }, // GDL por defecto
        });
        _trk.geocoder = new google.maps.Geocoder();
    }
    const map     = _trk.mapInstance;
    const bounds  = new google.maps.LatLngBounds();
    const markers = [];

    const processNext = (idx) => {
        if (idx >= creditos.length) {
            if (markers.length > 1) {
                // Trazar ruta con Directions si existen al menos 2 marcadores
                const waypoints = markers.slice(1, -1).map(m => ({
                    location: m.getPosition(),
                    stopover: true,
                }));
                const directionsService  = new google.maps.DirectionsService();
                const directionsRenderer = new google.maps.DirectionsRenderer({ map });
                directionsService.route({
                    origin:      markers[0].getPosition(),
                    destination: markers[markers.length - 1].getPosition(),
                    waypoints,
                    travelMode: google.maps.TravelMode.DRIVING,
                }, (result, status) => {
                    if (status === 'OK') {
                        directionsRenderer.setDirections(result);
                    } else {
                        map.fitBounds(bounds);
                    }
                });
            } else if (markers.length === 1) {
                map.setCenter(markers[0].getPosition());
                map.setZoom(14);
            }
            return;
        }
        const c = creditos[idx];
        const lat = parseFloat(c.latitud);
        const lng = parseFloat(c.longitud);
        if (!isNaN(lat) && !isNaN(lng) && lat !== 0 && lng !== 0) {
            const pos = { lat, lng };
            const m = new google.maps.Marker({
                map,
                position: pos,
                label: String(c.orden_ruta || (idx + 1)),
                title: `#${c.id_credito} — ${c.nombre_cliente || ''}`,
            });
            markers.push(m);
            bounds.extend(pos);
            processNext(idx + 1);
        } else if (_trk.geocoder && (c.direccion || (c.municipio && c.estado))) {
            const address = [c.direccion, c.municipio, c.estado, 'México'].filter(Boolean).join(', ');
            _trk.geocoder.geocode({ address }, (results, status) => {
                if (status === 'OK' && results[0]) {
                    const pos = results[0].geometry.location;
                    const m = new google.maps.Marker({
                        map,
                        position: pos,
                        label: String(c.orden_ruta || (idx + 1)),
                        title: `#${c.id_credito} — ${c.nombre_cliente || ''}`,
                    });
                    markers.push(m);
                    bounds.extend(pos);
                }
                processNext(idx + 1);
            });
        } else {
            processNext(idx + 1);
        }
    };
    processNext(0);
}

// ─── Guardar ruta ────────────────────────────────────────
function _trkGuardarRuta(modo) {
    const nombre    = $('#rutaNombre').val().trim();
    const estado    = $('#rutaEstado').val();
    const municipio = $('#rutaMunicipio').val();
    const fecha     = $('#rutaFecha').val();

    if (!nombre) {
        Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'El nombre de la ruta es obligatorio.', confirmButtonText: 'Aceptar' });
        document.getElementById('rutaNombre').focus();
        return;
    }
    if (_trk.creditosEnRuta.length === 0) {
        Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'Debe agregar al menos un crédito a la ruta.', confirmButtonText: 'Aceptar' });
        return;
    }
    if (!estado) {
        Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'Selecciona el estado.', confirmButtonText: 'Aceptar' });
        return;
    }
    if (!municipio) {
        Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'Selecciona el municipio.', confirmButtonText: 'Aceptar' });
        return;
    }
    if (!fecha) {
        Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'La fecha programada es obligatoria.', confirmButtonText: 'Aceptar' });
        return;
    }

    if (modo !== 'borrador') {
        if (_trk.usuariosSeleccionados.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'Asigna al menos un usuario responsable para enviar la ruta.', confirmButtonText: 'Aceptar' });
            return;
        }
        const noConfirmados = _trk.creditosEnRuta.filter(c => c.estatus_confirmacion_gestor !== 'confirmado');
        if (noConfirmados.length > 0) {
            Swal.fire({ icon: 'warning', title: 'Pendiente', text: 'Todos los créditos deben tener confirmación del gestor para enviar la ruta.', confirmButtonText: 'Aceptar' });
            return;
        }
    }

    const payload = {
        id_ruta:          _trk.idRutaEditando || 0,
        nombre_ruta:      nombre,
        estado,
        municipio,
        fecha_programada: fecha,
        modo,
        usuarios: _trk.usuariosSeleccionados.map(u => u.id),
        creditos: _trk.creditosEnRuta.map(c => ({
            id_credito:                  c.id_credito,
            modelo:                      [c.moto_marca, c.moto_modelo].filter(Boolean).join(' '),
            bin:                         c.bin || '',
            estado:                      c.estado || '',
            municipio:                   c.municipio || '',
            direccion:                   c.direccion || '',
            latitud:                     c.latitud  || null,
            longitud:                    c.longitud || null,
            orden_ruta:                  c.orden_ruta,
            estatus_confirmacion_gestor: c.estatus_confirmacion_gestor || 'pendiente',
        })),
    };

    const $btnGuardar = $('#btnGuardarBorrador, #btnEnviarRuta');
    $btnGuardar.prop('disabled', true);

    trkFetch('/TrackingRecoleccion/guardarRuta', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
    })
    .then(r => {
        if (r.success) {
            _trk.haychangios = false;
            Swal.fire({ icon: 'success', title: '¡Listo!', text: modo === 'borrador' ? 'Borrador guardado correctamente.' : 'Ruta enviada correctamente.', timer: 2000, showConfirmButton: false });
            bootstrap.Modal.getInstance(document.getElementById('modalRegistrarRuta'))?.hide();
            _trkCargarCreditosPaso2();
            _trkCargarRutas();
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: r.mensaje || r.message || 'Error al guardar la ruta.', confirmButtonText: 'Aceptar' });
        }
    })
    .catch(() => Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'Error de conexión al guardar.', confirmButtonText: 'Aceptar' }))
    .finally(() => $btnGuardar.prop('disabled', false));
}

// ─── Ver detalle de ruta ─────────────────────────────────
function _trkVerDetalleRuta(idRuta) {
    const $body = $('#detalleRutaBody');
    $body.html('<div class="text-center py-4"><div class="spinner-border" style="color:var(--track-color);"></div></div>');
    const modal = new bootstrap.Modal(document.getElementById('modalDetalleRuta'));
    modal.show();

    trkFetch(`/TrackingRecoleccion/obtenerDetalleRuta?id_ruta=${idRuta}`)
        .then(r => {
            if (!r.success || !r.datos) {
                $body.html('<div class="alert alert-danger">No se pudo cargar el detalle.</div>');
                return;
            }
            const d = r.datos;
            const estatusBadge = RUTA_LABEL[d.estatus_ruta] || d.estatus_ruta;
            const usuarios = (d.usuarios || []).map(u => u.nombre_usuario).join(', ') || '—';
            let rowsHtml = '';
            (d.detalle || []).forEach((det, i) => {
                rowsHtml += `<tr>
                    <td class="text-center">${det.orden_ruta || (i + 1)}</td>
                    <td>${det.id_credito || '—'}</td>
                    <td>${det.nombre_cliente || '—'}</td>
                    <td>${det.modelo || '—'}</td>
                    <td>${det.bin || '—'}</td>
                    <td>${det.estado || '—'} / ${det.municipio || '—'}</td>
                    <td>${det.estatus_proceso || '—'}</td>
                    <td>${CONF_LABEL[det.estatus_confirmacion_gestor] || det.estatus_confirmacion_gestor || '—'}</td>
                </tr>`;
            });
            $body.html(`
                <div class="row g-2 mb-3">
                    <div class="col-6 col-md-3"><b class="d-block small text-muted">Nombre</b>${d.nombre_ruta}</div>
                    <div class="col-6 col-md-2"><b class="d-block small text-muted">Estado</b>${d.estado || '—'}</div>
                    <div class="col-6 col-md-2"><b class="d-block small text-muted">Municipio</b>${d.municipio || '—'}</div>
                    <div class="col-6 col-md-2"><b class="d-block small text-muted">Fecha programada</b>${d.fecha_programada_fmt || d.fecha_programada || '—'}</div>
                    <div class="col-6 col-md-1"><b class="d-block small text-muted">Estatus</b>${estatusBadge}</div>
                    <div class="col-6 col-md-2"><b class="d-block small text-muted">Responsables</b><span class="small">${usuarios}</span></div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered" style="font-size:.8rem;">
                        <thead style="background:var(--track-color);color:#fff;">
                            <tr>
                                <th>#</th><th>Crédito</th><th>Cliente</th><th>Modelo</th>
                                <th>BIN</th><th>Estado / Municipio</th>
                                <th>Proceso</th><th>Confirmación</th>
                            </tr>
                        </thead>
                        <tbody>${rowsHtml || '<tr><td colspan="8" class="text-center text-muted">Sin créditos</td></tr>'}</tbody>
                    </table>
                </div>
            `);
        })
        .catch(() => $body.html('<div class="alert alert-danger">Error de conexión.</div>'));
}
</script>
