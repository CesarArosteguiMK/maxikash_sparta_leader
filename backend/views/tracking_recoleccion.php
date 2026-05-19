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
    background: var(--track-bg-card);
    border: 1px solid var(--track-border);
    color: var(--track-color-dark);
    border-radius: .75rem;
    padding: 1.25rem 1.5rem;
    margin-bottom: 1.25rem;
}
body.dark-mode .track-header {
    background: var(--track-bg-card);
    border-color: var(--track-border);
    color: var(--track-color-dark);
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
.track-tabs .nav-link,
.track-tabs .nav-link:link,
.track-tabs .nav-link:visited,
.track-tabs .nav-link:hover,
.track-tabs .nav-link:focus {
    color: var(--track-color-dark) !important;
    border-radius: .5rem .5rem 0 0;
}
.track-tabs .nav-link.active {
    background: var(--track-color) !important;
    color: #fff !important;
    border-color: var(--track-color) !important;
}
body.dark-mode .track-tabs .nav-link,
body.dark-mode .track-tabs .nav-link:link,
body.dark-mode .track-tabs .nav-link:visited,
body.dark-mode .track-tabs .nav-link:hover,
body.dark-mode .track-tabs .nav-link:focus {
    color: var(--track-color) !important;
}

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
.eta-row .form-control,
.eta-row .form-select {
    font-size: .72rem;
    padding: .1rem .3rem;
    height: auto;
    line-height: 1.4;
}
body.dark-mode .eta-row .form-control,
body.dark-mode .eta-row .form-select {
    background-color: #1e2d2c;
    color: #e2e8f0;
    border-color: #2d4444;
}

/* ── Mapa ── */
#trackMapContainer {
    width: 100%;
    height: 475px;
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

/* ── Dark mode: modales + formularios ── */
body.dark-mode .modal-content {
    background-color: #161f1f;
    color: #e2e8f0;
    border-color: #334444;
}
body.dark-mode .modal-header { border-bottom-color: #334444; }
body.dark-mode .modal-footer {
    background-color: #161f1f;
    border-top-color: #334444;
}
body.dark-mode .modal-body { background-color: #161f1f; }
body.dark-mode .form-control,
body.dark-mode .form-select {
    background-color: #1e2d2c;
    color: #e2e8f0;
    border-color: #2d4444;
}
body.dark-mode .form-control::placeholder { color: #6b8080; }
body.dark-mode .form-control:disabled,
body.dark-mode .form-select:disabled {
    background-color: #111a1a;
    color: #52686a;
    border-color: #1e2d2c;
}
body.dark-mode .form-label { color: #b0cece; }
body.dark-mode .form-text  { color: #52686a; }
body.dark-mode .input-group-text {
    background-color: #1e2d2c;
    color: #b0cece;
    border-color: #2d4444;
}

/* ── Select2 / chosen override ── */
.select2-container .select2-selection--multiple {
    min-height: 38px;
    border-color: #ced4da !important;
}

/* ── Botón pin ubicación en fila de crédito ── */
.btn-pin-ubicacion {
    flex-shrink: 0;
}
.btn-pin-ubicacion.tiene-pin {
    color: var(--track-color-dark);
    border-color: var(--track-color);
    background: var(--track-color-light);
}
</style>

<div class="container-fluid py-3 px-3 px-md-4">

    <!-- ── Cabecera ── -->
    <div class="track-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h4><i class="fa-solid fa-route me-2"></i>Tracking Recolección — Motos Adjudicadas</h4>
            <div class="track-subtitle">Créditos disponibles para planeación de ruta física</div>
        </div>
        <button class="btn btn-light fw-semibold" id="btnNuevaRuta">
            <i class="fa-solid fa-plus me-1"></i>Registrar ruta
        </button>
    </div>

    <!-- ── Pestañas principales ── -->
    <ul class="nav nav-tabs track-tabs mb-3" id="trackMainTabs">
        <li class="nav-item">
            <button class="nav-link active" id="tabCreditosBtn" data-bs-toggle="tab" data-bs-target="#tabCreditos">
                <i class="fa-solid fa-motorcycle me-1"></i>Créditos disponibles
                <span id="badgeCreditos" class="badge rounded-pill ms-1"
                      style="background:var(--track-color);font-size:.7rem;">0</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="tabBorradorBtn" data-bs-toggle="tab" data-bs-target="#tabBorradores">
                <i class="fa-solid fa-file-pen me-1"></i>Borradores
                <span id="badgeBorradores" class="badge rounded-pill ms-1"
                      style="background:var(--track-color);font-size:.7rem;">0</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="tabRutasBtn" data-bs-toggle="tab" data-bs-target="#tabRutas">
                <i class="fa-solid fa-map-marked-alt me-1"></i>Rutas registradas
                <span id="badgeRutas" class="badge rounded-pill ms-1"
                      style="background:var(--track-color);font-size:.7rem;">0</span>
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

        <!-- ══ Tab: Borradores ══ -->
        <div class="tab-pane fade" id="tabBorradores">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="tablaBorradores" class="table table-hover table-bordered mb-0 w-100" style="font-size:.82rem;">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nombre de ruta</th>
                                    <th>Estado / Municipio</th>
                                    <th>Fecha programada</th>
                                    <th>Hora</th>
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
                                    <th>Hora</th>
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
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold">
                            Fecha programada <span class="text-danger">*</span>
                        </label>
                        <input type="date" class="form-control form-control-sm"
                               id="rutaFecha" min="">
                        <div class="form-text text-muted" style="font-size:.72rem;">
                            Mínimo 2 días desde hoy  - Deja una fecha tentativa si aún no está definida para que puedas guardar correctamente el borrador de la ruta.
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold">
                            Hora de salida
                        </label>
                        <div class="d-flex gap-1 align-items-center">
                            <select class="form-select form-select-sm" id="rutaHoraH" style="width:80px;flex-shrink:0;">
                                <?php for ($h = 1; $h <= 12; $h++): ?>
                                <option value="<?= $h ?>"><?= $h ?></option>
                                <?php endfor; ?>
                            </select>
                            <input type="text" class="form-control form-control-sm text-center fw-semibold"
                                   id="rutaHoraM" inputmode="numeric" maxlength="2"
                                   placeholder="00" autocomplete="off"
                                   style="width:80px;flex-shrink:0;letter-spacing:.05em;">
                            <select class="form-select form-select-sm" id="rutaHoraAmPm" style="width:80px;flex-shrink:0;">
                                <option value="AM">AM</option>
                                <option value="PM">PM</option>
                            </select>
                        </div>
                        <div id="rutaHoraActInfo" class="mt-1 d-none" style="font-size:.72rem;"></div>
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
                <div class="mb-2" id="secAgregarCredito">
                    <label class="form-label small fw-semibold">
                        Agregar crédito a la ruta
                    </label>
                    <!-- Filtros de ubicación para créditos -->
                    <div class="row g-2 mb-2" id="crdFiltrosUbicacion">
                        <div class="col-12 col-md-4">
                            <select class="form-select form-select-sm" id="crdFiltroEstado">
                                <option value="">— Todos los estados —</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <select class="form-select form-select-sm" id="crdFiltroMunicipio" disabled>
                                <option value="">— Todos los municipios —</option>
                            </select>
                        </div>
                    </div>
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
                        <span class="small text-muted" id="reorderHint">
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
                    <button type="button" class="btn btn-sm" id="btnActualizarRuta"
                            style="background:#0d6efd;color:#fff;display:none;">
                        <i class="fa-solid fa-arrows-rotate me-1"></i>Actualizar ruta
                    </button>
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
     Modal — Seleccionar ubicación en mapa (map picker)
══════════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalMapPicker" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header py-2" style="background:var(--track-color-dark);color:#fff;">
                <h6 class="modal-title mb-0">
                    <i class="fa-solid fa-map-pin me-2"></i>
                    Seleccionar ubicación en el mapa
                </h6>
                <button type="button" class="btn-close" id="btnCerrarMapPicker" style="filter:invert(1);"></button>
            </div>
            <div class="modal-body p-2">
                <p class="small text-muted mb-2 px-1">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    Haz clic en el mapa para colocar el pin de la ubicación del crédito
                    <strong id="mapPickerCreditoLabel"></strong>.
                </p>
                <div id="mapPickerContainer" style="width:100%;height:420px;border-radius:.5rem;overflow:hidden;border:1px solid var(--track-border);"></div>
                <div class="mt-2 px-1 d-flex align-items-center gap-2 flex-wrap">
                    <span class="small text-muted" id="mapPickerCoordsLabel">
                        <i class="fa-solid fa-crosshairs me-1"></i>Sin selección
                    </span>
                </div>
            </div>
            <div class="modal-footer py-2 d-flex justify-content-between">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnCancelarMapPicker">
                    <i class="fa-solid fa-xmark me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-sm" id="btnConfirmarMapPicker"
                        style="background:var(--track-color);color:#fff;" disabled>
                    <i class="fa-solid fa-check me-1"></i>Confirmar ubicación
                </button>
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
    estatusRuta:          null, // estatus_ruta de la ruta cargada (null = nueva)
    soloLectura:          false,// modal en modo vista bloqueada
    cargando:             false, // cargando ruta existente (evita haychangios)
    haycambios:           false,
    tablaCreditosDT:      null,
    tablaRutasDT:         null,
    tablaRutasBorradorDT: null,
    sortableInstance:     null,
    mapInstance:          null,
    mapLoaded:            false,
    geocoder:             null,
    mapMarkers:           [],   // marcadores activos en el mapa
    directionsRenderer:   null, // renderer de ruta activo
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
    _trkInicializarTablaBorradorDT();
    _trkInicializarModal();

    // Observar cambios de clase en body para actualizar estilos del mapa
    new MutationObserver(() => {
        if (!_trk.mapInstance) return;
        const isDark = document.body.classList.contains('dark-mode');
        _trk.mapInstance.setOptions({ styles: isDark ? _TRK_DARK_MAP_STYLES : [] });
        // El picker tiene su propio mapa
        if (_trkPicker.mapInstance) {
            _trkPicker.mapInstance.setOptions({ styles: isDark ? _TRK_DARK_MAP_STYLES : [] });
        }
    }).observe(document.body, { attributeFilter: ['class'] });

    document.getElementById('tabRutasBtn').addEventListener('click', () => _trkCargarRutas());
    document.getElementById('tabBorradorBtn').addEventListener('click', () => _trkCargarBorradores());
    document.getElementById('btnNuevaRuta').addEventListener('click', () => _trkAbrirModalNuevo());

    // Validación estricta del input de minutos
    const $horaM = document.getElementById('rutaHoraM');
    $horaM.addEventListener('keydown', function (e) {
        // Permitir: backspace, delete, tab, escape, flechas, home, end
        const allowed = ['Backspace','Delete','Tab','Escape','ArrowLeft','ArrowRight','Home','End'];
        if (allowed.includes(e.key)) return;
        // Bloquear todo excepto dígitos 0-9
        if (!/^[0-9]$/.test(e.key)) {
            e.preventDefault();
        }
    });
    $horaM.addEventListener('input', function () {
        // Eliminar cualquier carácter que no sea dígito (copia/pega, etc.)
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 2);
    });
    $horaM.addEventListener('blur', function () {
        const raw = this.value.replace(/[^0-9]/g, '');
        if (raw === '') {
            this.value = '00';
            return;
        }
        const n = parseInt(raw, 10);
        if (isNaN(n) || n > 59) {
            this.value = '00';
            this.classList.add('is-invalid');
            setTimeout(() => this.classList.remove('is-invalid'), 1500);
            if (n === 69 || n === 67 || n === 91) {
                Swal.fire({
                    icon: 'error',
                    title: 'Minutos incorrectos',
                    text: `"${n}" no es válido. Deben ser entre 00 y 59.`,
                    footer: 'Que gracioso...',
                    confirmButtonText: 'Aceptar',
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Minutos incorrectos',
                    text: `"${n}" no es válido. Deben ser entre 00 y 59.`,
                    confirmButtonText: 'Aceptar',
                });
            }
        } else {
            this.value = String(n).padStart(2, '0');
            this.classList.remove('is-invalid');
        }
    });

    // Carga inicial de todas las pestañas en paralelo
    _trkCargarTodo();
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
    return trkFetch('/TrackingRecoleccion/obtenerEstados')
        .then(r => {
            const estados = r.datos || [];
            const $selFiltro = $('#filtroEstado');
            estados.forEach(e => $selFiltro.append(`<option value="${e}">${e}</option>`));
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
            {
                data: 'estatus_proceso',
                defaultContent: '—',
                render: v => v ? v.replace(/_/g, ' ') : '—',
            },
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

    return trkFetch(url)
        .then(r => {
            _trk.creditosDisponibles = r.datos || [];
            // TODO (pendiente autorización): descomentar para filtrar solo créditos listos para ruta
            // _trk.creditosDisponibles = _trkFiltrarListosParaRuta(_trk.creditosDisponibles);
            if (_trk.tablaCreditosDT) {
                _trk.tablaCreditosDT.clear().rows.add(_trk.creditosDisponibles).draw();
            }
            _trkPoblarFiltroEstadosCrd();
            _trkRefrescarSelectCreditos();
            const badge = document.getElementById('badgeCreditos');
            if (badge) badge.textContent = String(_trk.creditosDisponibles.length);
        })
        .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Error al cargar créditos.', confirmButtonText: 'Aceptar' }));
}

// ─── Filtro: solo créditos con estatus "Cierre Documentados" ─────────────
// Pendiente de autorización — para activar: descomentar la línea en _trkCargarCreditosPaso2
// Una vez activo, el estatus se mostrará en tabla como "Listo para ruta" en lugar de "Cierre Documentados"
// function _trkFiltrarListosParaRuta(creditos) {
//     return creditos
//         .filter(c => c.estatus_proceso === 'Cierre Documentados')
//         .map(c => ({ ...c, estatus_proceso: 'Listo para ruta' }));
// }

// ─── Tabla de rutas ─────────────────────────────────────
function _trkRenderUbicacionRuta(raw) {
    if (!raw) return '—';
    const map = new Map();
    raw.split('@@').forEach(p => {
        const sep  = p.indexOf('|||');
        const est  = sep >= 0 ? p.slice(0, sep).trim()  : '';
        const mun  = sep >= 0 ? p.slice(sep + 3).trim() : '';
        if (!est) return;
        if (!map.has(est)) map.set(est, new Set());
        if (mun && mun !== '|') map.get(est).add(mun);
    });
    if (!map.size) return '—';
    return [...map.entries()]
        .map(([est, munis]) => {
            const munStr = [...munis].filter(Boolean).join(', ');
            return munStr ? `${est} / ${munStr}` : est;
        })
        .join('<br>');
}

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
                render: r => _trkRenderUbicacionRuta(r.ubicaciones_lista),
            },
            { data: 'fecha_programada_fmt', defaultContent: '—' },
            {
                data: null,
                title: 'Hora',
                render: r => {
                    const hi  = r.hora_inicial;
                    const ha1 = r.act_hora_1;
                    if (!hi && !ha1) return '—';
                    if (ha1) {
                        return `<div class="d-flex flex-column gap-1">
                            <span class="badge bg-warning text-dark" title="Hora actualizada">${_trkFormatHora(ha1)}</span>
                            <small class="text-muted text-decoration-line-through" title="Hora original">${_trkFormatHora(hi)}</small>
                        </div>`;
                    }
                    return `<span class="badge bg-light text-dark border">${_trkFormatHora(hi)}</span>`;
                },
            },
            {
                data: 'estatus_ruta',
                render: v => RUTA_LABEL[v] || `<span class="badge bg-secondary">${v}</span>`,
            },
            {
                data: null,
                render: r => {
                    const total = parseInt(r.total_creditos) || 0;
                    const conf  = parseInt(r.confirmados)    || 0;
                    const pend  = parseInt(r.pendientes)     || 0;
                    const rech  = parseInt(r.rechazados)     || 0;
                    const lista = (r.creditos_lista || '').split('||').filter(Boolean).join('<br>');
                    const ttAttr = lista ? ` data-bs-toggle="tooltip" data-bs-placement="right" data-bs-html="true" data-bs-title="${lista}"` : '';
                    let html = `<div class="d-flex flex-column gap-1 align-items-start">`;
                    html += `<span class="badge bg-secondary trk-cred-badge"${ttAttr} style="cursor:default;white-space:nowrap;">${total} crédito${total !== 1 ? 's' : ''}</span>`;
                    if (conf > 0) html += `<small class="text-success fw-semibold" style="white-space:nowrap;">${conf} confirmado${conf !== 1 ? 's' : ''}</small>`;
                    if (pend > 0) html += `<small class="text-warning fw-semibold" style="white-space:nowrap;">${pend} pendiente${pend !== 1 ? 's' : ''}</small>`;
                    if (rech > 0) html += `<small class="text-danger  fw-semibold" style="white-space:nowrap;">${rech} rechazado${rech !== 1 ? 's' : ''}</small>`;
                    html += '</div>';
                    return html;
                },
            },
            {
                data: 'usuarios_responsables',
                defaultContent: '—',
                render: v => {
                    if (!v) return '—';
                    return v.split(',').map(n => `<span style="display:block;white-space:nowrap;">${n.trim()}</span>`).join('');
                },
            },
            {
                data: null,
                orderable: false,
                render: r => r.estatus_ruta === 'borrador'
                    ? `<button class="btn btn-sm btn-outline-warning py-0 px-2 btn-editar-ruta"
                           data-id="${r.id_ruta}" title="Editar ruta (borrador)">
                           <i class="fa-solid fa-pen-to-square"></i>
                       </button>`
                    : `<button class="btn btn-sm btn-outline-primary py-0 px-2 btn-ver-ruta"
                           data-id="${r.id_ruta}" title="Ver ruta">
                           <i class="fa-solid fa-eye"></i>
                       </button>`,
            },
        ],
        drawCallback: function () {
            document.querySelectorAll('#tablaRutas [data-bs-toggle="tooltip"]').forEach(el => {
                bootstrap.Tooltip.getOrCreateInstance(el, { trigger: 'hover', html: true });
            });
        },
    });

    $('#tablaRutas').on('click', '.btn-editar-ruta', function () {
        _trkCargarRutaEnModal($(this).data('id'), false);
    });
    $('#tablaRutas').on('click', '.btn-ver-ruta', function () {
        _trkCargarRutaEnModal($(this).data('id'), true);
    });
}

function _trkCargarRutas() {
    return trkFetch('/TrackingRecoleccion/obtenerRutas', { method: 'POST' })
        .then(r => {
            const rutas = r.datos || [];
            if (_trk.tablaRutasDT) {
                _trk.tablaRutasDT.clear().rows.add(rutas).draw();
            }
            const badge = document.getElementById('badgeRutas');
            if (badge) badge.textContent = String(rutas.length);
        })
        .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Error al cargar rutas.', confirmButtonText: 'Aceptar' }));
}

// ─── Tabla de borradores ─────────────────────────────────────
function _trkInicializarTablaBorradorDT() {
    _trk.tablaRutasBorradorDT = $('#tablaBorradores').DataTable({
        language: {
            emptyTable:  'No hay rutas en borrador',
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
            { data: 'nombre_ruta', defaultContent: '—' },
            {
                data: null,
                render: r => _trkRenderUbicacionRuta(r.ubicaciones_lista),
            },
            { data: 'fecha_programada_fmt', defaultContent: '—' },
            {
                data: null,
                title: 'Hora',
                render: r => {
                    const hi  = r.hora_inicial;
                    const ha1 = r.act_hora_1;
                    if (!hi && !ha1) return '—';
                    if (ha1) {
                        return `<div class="d-flex flex-column gap-1">
                            <span class="badge bg-warning text-dark" title="Hora actualizada">${_trkFormatHora(ha1)}</span>
                            <small class="text-muted text-decoration-line-through" title="Hora original">${_trkFormatHora(hi)}</small>
                        </div>`;
                    }
                    return `<span class="badge bg-light text-dark border">${_trkFormatHora(hi)}</span>`;
                },
            },
            {
                data: null,
                render: r => {
                    const total = parseInt(r.total_creditos) || 0;
                    const conf  = parseInt(r.confirmados)    || 0;
                    const pend  = parseInt(r.pendientes)     || 0;
                    const rech  = parseInt(r.rechazados)     || 0;
                    const lista = (r.creditos_lista || '').split('||').filter(Boolean).join('<br>');
                    const ttAttr = lista ? ` data-bs-toggle="tooltip" data-bs-placement="right" data-bs-html="true" data-bs-title="${lista}"` : '';
                    let html = `<div class="d-flex flex-column gap-1 align-items-start">`;
                    html += `<span class="badge bg-secondary trk-cred-badge"${ttAttr} style="cursor:default;white-space:nowrap;">${total} crédito${total !== 1 ? 's' : ''}</span>`;
                    if (conf > 0) html += `<small class="text-success fw-semibold" style="white-space:nowrap;">${conf} confirmado${conf !== 1 ? 's' : ''}</small>`;
                    if (pend > 0) html += `<small class="text-warning fw-semibold" style="white-space:nowrap;">${pend} pendiente${pend !== 1 ? 's' : ''}</small>`;
                    if (rech > 0) html += `<small class="text-danger  fw-semibold" style="white-space:nowrap;">${rech} rechazado${rech !== 1 ? 's' : ''}</small>`;
                    html += '</div>';
                    return html;
                },
            },
            {
                data: 'usuarios_responsables',
                defaultContent: '—',
                render: v => {
                    if (!v) return '—';
                    return v.split(',').map(n => `<span style="display:block;white-space:nowrap;">${n.trim()}</span>`).join('');
                },
            },
            {
                data: null,
                orderable: false,
                render: r => `<button class="btn btn-sm btn-outline-warning py-0 px-2 btn-editar-borrador"
                                   data-id="${r.id_ruta}" title="Editar borrador">
                                   <i class="fa-solid fa-pen-to-square"></i>
                               </button>`,
            },
        ],
        drawCallback: function () {
            document.querySelectorAll('#tablaBorradores [data-bs-toggle="tooltip"]').forEach(el => {
                bootstrap.Tooltip.getOrCreateInstance(el, { trigger: 'hover', html: true });
            });
        },
    });

    $('#tablaBorradores').on('click', '.btn-editar-borrador', function () {
        _trkCargarRutaEnModal($(this).data('id'), false);
    });
}

function _trkCargarBorradores() {
    return trkFetch('/TrackingRecoleccion/obtenerBorradores')
        .then(r => {
            const borradores = r.datos || [];
            if (_trk.tablaRutasBorradorDT) {
                _trk.tablaRutasBorradorDT.clear().rows.add(borradores).draw();
            }
            // Actualizar contador en la pestaña
            const $badge = document.getElementById('badgeBorradores');
            if ($badge) $badge.textContent = borradores.length > 0 ? borradores.length : '0';
        })
        .catch(() => {});
}

// ─── Usuarios ────────────────────────────────────────────
function _trkCargarUsuarios() {
    return trkFetch('/TrackingRecoleccion/obtenerUsuariosRecoleccion')
        .then(r => {
            _trk.usuariosDisponibles = r.datos || [];
            const $sel = $('#rutaUsuarios');
            _trk.usuariosDisponibles.forEach(u => {
                $sel.append(`<option value="${u.id}">${u.nombre}</option>`);
            });
        });
}

// ─── Carga inicial de todos los datos en paralelo ─────────
function _trkCargarTodo() {
    Swal.fire({
        title: 'Obteniendo datos...',
        html: '<span style="font-size:.875rem;color:#64748b;">Cargando información del módulo</span>',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => Swal.showLoading(),
    });

    Promise.all([
        _trkCargarEstados().catch(() => {}),
        _trkCargarUsuarios().catch(() => {}),
        _trkCargarCreditosPaso2().catch(() => {}),
        _trkCargarBorradores().catch(() => {}),
        _trkCargarRutas().catch(() => {}),
    ]).then(() => Swal.close());
}

// ─── Modal — apertura ────────────────────────────────────
function _trkInicializarModal() {
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
        maximumSelectionLength: 4,
        dropdownParent: $('#modalRegistrarRuta'),
        language: {
            noResults: () => 'Sin resultados',
            searching: () => 'Buscando…',
            maximumSelected: () => 'Máximo 4 usuarios responsables',
        },
    });
    $('#rutaUsuarios').on('change', function () {
        _trk.usuariosSeleccionados = Array.from(this.selectedOptions).map(o => ({
            id: Number(o.value),
            nombre: o.text,
        }));
        _trkMarcarCambio();
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
        _trkMarcarCambio();
    });

    // Filtro de créditos por estado
    $('#crdFiltroEstado').on('change', function () {
        const est = $(this).val();
        const $mun = $('#crdFiltroMunicipio');
        $mun.html('<option value="">— Todos los municipios —</option>');
        if (est) {
            const munis = [...new Set(
                _trk.creditosDisponibles
                    .filter(c => c.estado === est && c.municipio)
                    .map(c => c.municipio)
            )].sort();
            munis.forEach(m => $mun.append(`<option value="${m}">${m}</option>`));
            $mun.prop('disabled', false);
        } else {
            $mun.prop('disabled', true);
        }
        _trkRefrescarSelectCreditos();
    });

    // Filtro de créditos por municipio
    $('#crdFiltroMunicipio').on('change', _trkRefrescarSelectCreditos);

    // Mapa refresh
    $('#btnRefreshMap').on('click', _trkRenderizarMapa);

    // Guardar
    $('#btnGuardarBorrador').on('click', () => _trkGuardarRuta('borrador'));
    $('#btnEnviarRuta').on('click', () => _trkGuardarRuta('enviar'));
    $('#btnActualizarRuta').on('click', async () => {
        const ok = await Swal.fire({
            icon: 'question',
            title: '¿Guardar cambios?',
            text: 'Se actualizarán los datos de esta ruta.',
            showCancelButton: true,
            confirmButtonText: 'Sí, actualizar',
            cancelButtonText: 'No, regresar',
            confirmButtonColor: '#0d6efd',
        });
        if (ok.isConfirmed) _trkGuardarRuta('actualizar');
    });

    // Cerrar con aviso
    const _closeFn = async () => {
        if (!_trk.soloLectura && _trk.haychangios) {
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
            _trkMarcarCambio();
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
    // Pre-seleccionar estado/municipio del crédito en los filtros
    if (cred.estado) {
        $('#crdFiltroEstado').val(cred.estado).trigger('change');
        if (cred.municipio) {
            $('#crdFiltroMunicipio').val(cred.municipio).trigger('change');
        }
    }
    const modal = new bootstrap.Modal(document.getElementById('modalRegistrarRuta'));
    modal.show();
}

function _trkResetModal() {
    _trk.idRutaEditando        = null;
    _trk.estatusRuta           = null;
    _trk.soloLectura           = false;
    _trk.cargando              = false;
    _trk.creditosEnRuta        = [];
    _trk.usuariosSeleccionados = [];
    _trk.haychangios           = false;
    _trkDesbloquearModal();
    $('#rutaNombre').val('');
    const minDate = (() => {
        const d = new Date();
        d.setDate(d.getDate() + 2);
        return d.toISOString().split('T')[0];
    })();
    $('#rutaFecha').val('').attr('min', minDate);
    // Reset hora a 8:00 AM
    $('#rutaHoraH').val('8');
    $('#rutaHoraM').val('00');
    $('#rutaHoraAmPm').val('AM');
    $('#rutaHoraActInfo').addClass('d-none').text('');
    // Reset filtros de créditos
    $('#crdFiltroEstado').val('');
    $('#crdFiltroMunicipio').html('<option value="">— Todos los municipios —</option>').prop('disabled', true);
    _trkPoblarFiltroEstadosCrd();
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
function _trkPoblarFiltroEstadosCrd() {
    const estados = [...new Set(
        _trk.creditosDisponibles.map(c => c.estado).filter(Boolean)
    )].sort();
    const $est = $('#crdFiltroEstado');
    $est.html('<option value="">— Todos los estados —</option>');
    estados.forEach(e => $est.append(`<option value="${e}">${e}</option>`));
}

function _trkRefrescarSelectCreditos() {
    const estFiltro = $('#crdFiltroEstado').val();
    const munFiltro = $('#crdFiltroMunicipio').val();
    const $sel = $('#rutaCreditoSelect');
    const idsEnRuta = new Set(_trk.creditosEnRuta.map(c => String(c.id_credito)));
    $sel.html('<option value="">— Buscar crédito (ID · Modelo · BIN) —</option>');
    _trk.creditosDisponibles.forEach(c => {
        if (idsEnRuta.has(String(c.id_credito))) return;
        if (estFiltro && c.estado !== estFiltro) return;
        if (munFiltro && c.municipio !== munFiltro) return;
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
    _trkMarcarCambio();
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
        const modelo    = [c.moto_marca, c.moto_modelo].filter(Boolean).join(' ') || '—';
        const badgeConf = CONF_LABEL[c.estatus_confirmacion_gestor] || CONF_LABEL['pendiente'];
        const tienePin  = c.latitud_manual && c.longitud_manual;
        const pinClass  = tienePin ? 'btn-pin-ubicacion tiene-pin' : 'btn-pin-ubicacion';
        const pinTitle  = tienePin ? 'Ubicación manual asignada (clic para cambiar)' : 'Asignar ubicación en mapa';

        // Los créditos en ruta nunca se bloquean
        const filaLectura = false;

        // Elementos que sólo aparecen en modo edición
        const dragHandle  = filaLectura ? '' : '<i class="fa-solid fa-grip-vertical drag-handle"></i>';
        const confControl = filaLectura
            ? badgeConf
            : `<select class="form-select form-select-sm py-0 ms-1 select-conf-gestor"
                    style="max-width:130px;font-size:.75rem;"
                    data-id="${c.id_credito}">
                <option value="pendiente"   ${c.estatus_confirmacion_gestor === 'pendiente'   ? 'selected' : ''}>Pendiente</option>
                <option value="confirmado"  ${c.estatus_confirmacion_gestor === 'confirmado'  ? 'selected' : ''}>Confirmado</option>
                <option value="rechazado"   ${c.estatus_confirmacion_gestor === 'rechazado'   ? 'selected' : ''}>Rechazado</option>
                <option value="en_revision" ${c.estatus_confirmacion_gestor === 'en_revision' ? 'selected' : ''}>En revisión</option>
            </select>`;
        const actionBtns = filaLectura ? '' : `
            <button class="btn btn-sm btn-outline-secondary ${pinClass}" data-id="${c.id_credito}" title="${pinTitle}" style="font-size:.72rem;padding:.15rem .4rem;">
                <i class="fa-solid fa-map-pin"></i>
            </button>
            <button class="btn btn-outline-danger btn-remove-cred" data-id="${c.id_credito}" title="Quitar">
                <i class="fa-solid fa-trash-alt"></i>
            </button>`;

        const etaIni  = _trkParseHora12(c.hora_eta_ini);
        const etaFin  = _trkParseHora12(c.hora_eta_fin);
        const optsIni = _trkEtaHoraOpts(etaIni.h);
        const optsFin = _trkEtaHoraOpts(etaFin.h);

        const html = `
        <div class="track-credito-row" data-id="${c.id_credito}">
            ${dragHandle}
            <span class="orden-num">${idx + 1}</span>
            <div class="d-flex flex-column gap-0 flex-grow-1" style="min-width:0;">
                <span class="fw-semibold text-truncate">#${c.id_credito} — ${c.nombre_cliente || '—'}</span>
                <span class="text-muted" style="font-size:.75rem;">
                    ${modelo} · BIN: ${c.bin || '—'}
                    &nbsp;|&nbsp;${c.estado || '—'}, ${c.municipio || '—'}
                </span>
                <div class="eta-row d-flex align-items-center gap-1 mt-1 flex-wrap">
                    <span class="text-muted fw-semibold" style="font-size:.7rem;white-space:nowrap;">ETA:</span>
                    <input type="date" class="form-control eta-fecha" data-id="${c.id_credito}" value="${c.fecha_eta || ''}" style="max-width:130px;" title="Fecha estimada de llegada">
                    <select class="form-select form-select-sm eta-h" data-id="${c.id_credito}" data-tipo="ini" style="width:62px;flex-shrink:0;" title="Hora inicio">${optsIni}</select>
                    <input type="text" class="form-control text-center fw-semibold eta-m" data-id="${c.id_credito}" data-tipo="ini" inputmode="numeric" maxlength="2" placeholder="00" autocomplete="off" value="${etaIni.m}" style="width:48px;flex-shrink:0;letter-spacing:.05em;" title="Minutos inicio">
                    <select class="form-select form-select-sm eta-ap" data-id="${c.id_credito}" data-tipo="ini" style="width:62px;flex-shrink:0;" title="AM/PM inicio">
                        <option value="AM"${etaIni.ampm === 'AM' ? ' selected' : ''}>AM</option>
                        <option value="PM"${etaIni.ampm === 'PM' ? ' selected' : ''}>PM</option>
                    </select>
                    <span class="text-muted" style="font-size:.7rem;line-height:1;">–</span>
                    <select class="form-select form-select-sm eta-h" data-id="${c.id_credito}" data-tipo="fin" style="width:62px;flex-shrink:0;" title="Hora fin">${optsFin}</select>
                    <input type="text" class="form-control text-center fw-semibold eta-m" data-id="${c.id_credito}" data-tipo="fin" inputmode="numeric" maxlength="2" placeholder="00" autocomplete="off" value="${etaFin.m}" style="width:48px;flex-shrink:0;letter-spacing:.05em;" title="Minutos fin">
                    <select class="form-select form-select-sm eta-ap" data-id="${c.id_credito}" data-tipo="fin" style="width:62px;flex-shrink:0;" title="AM/PM fin">
                        <option value="AM"${etaFin.ampm === 'AM' ? ' selected' : ''}>AM</option>
                        <option value="PM"${etaFin.ampm === 'PM' ? ' selected' : ''}>PM</option>
                    </select>
                </div>
            </div>
            ${confControl}
            ${actionBtns}
        </div>`;
        $list.append(html);
    });

    // Eventos de créditos (siempre activos, incluso en modo ver ruta)
    $list.find('.btn-remove-cred').off('click').on('click', function () {
            _trkQuitarCredito($(this).data('id'));
        });
        $list.find('.btn-pin-ubicacion').off('click').on('click', function () {
            const id = $(this).data('id');
            const cred = _trk.creditosEnRuta.find(c => String(c.id_credito) === String(id));
            if (cred) _trkAbrirMapPicker(cred);
        });
        $list.find('.select-conf-gestor').off('change').on('change', function () {
            const id  = $(this).data('id');
            const val = $(this).val();
            const c   = _trk.creditosEnRuta.find(x => String(x.id_credito) === String(id));
            if (c) {
                c.estatus_confirmacion_gestor = val;
                _trkRenderListaCreditos();
            }
            _trkMarcarCambio();
        });
        $list.find('.eta-fecha').off('change').on('change', function () {
            const id = $(this).data('id');
            const c  = _trk.creditosEnRuta.find(x => String(x.id_credito) === String(id));
            if (c) c.fecha_eta = $(this).val() || null;
            _trkMarcarCambio();
        });
        $list.find('.eta-h, .eta-ap').off('change').on('change', function () {
            const id   = $(this).data('id');
            const tipo = $(this).data('tipo');
            const c    = _trk.creditosEnRuta.find(x => String(x.id_credito) === String(id));
            if (c) {
                if (tipo === 'ini') c.hora_eta_ini = _trkLeerEtaHora(id, 'ini');
                else                c.hora_eta_fin = _trkLeerEtaHora(id, 'fin');
            }
            _trkMarcarCambio();
        });
        $list.find('.eta-m')
            .off('keydown input blur')
            .on('keydown', function (e) {
                const allowed = ['Backspace','Delete','Tab','Escape','ArrowLeft','ArrowRight','Home','End'];
                if (allowed.includes(e.key)) return;
                if (!/^[0-9]$/.test(e.key)) e.preventDefault();
            })
            .on('input', function () {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 2);
            })
            .on('blur', function () {
                const raw = this.value.replace(/[^0-9]/g, '');
                if (raw === '') { this.value = '00'; }
                const n = parseInt(raw || '0', 10);
                if (isNaN(n) || n > 59) {
                    this.value = '00';
                    $(this).addClass('is-invalid');
                    setTimeout(() => $(this).removeClass('is-invalid'), 1500);
                    Swal.fire({
                        icon: 'error', title: 'Minutos incorrectos',
                        text: `"${n}" no es válido. Deben ser entre 00 y 59.`,
                        confirmButtonText: 'Aceptar',
                    });
                } else {
                    this.value = String(n).padStart(2, '0');
                    $(this).removeClass('is-invalid');
                }
                const id   = $(this).data('id');
                const tipo = $(this).data('tipo');
                const c    = _trk.creditosEnRuta.find(x => String(x.id_credito) === String(id));
                if (c) {
                    if (tipo === 'ini') c.hora_eta_ini = _trkLeerEtaHora(id, 'ini');
                    else                c.hora_eta_fin = _trkLeerEtaHora(id, 'fin');
                }
                _trkMarcarCambio();
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
    } else {
        // El script ya está cargando (desde el picker); esperar
        const waitForMaps = setInterval(() => {
            if (typeof google !== 'undefined' && google.maps) {
                clearInterval(waitForMaps);
                _trkDibujarMapa(creditos);
            }
        }, 150);
        setTimeout(() => clearInterval(waitForMaps), 10000);
    }
}

// ─── Estilos oscuros para Google Maps ───────────────────
const _TRK_DARK_MAP_STYLES = [
    { elementType: 'geometry',             stylers: [{ color: '#1d2c2c' }] },
    { elementType: 'labels.text.fill',     stylers: [{ color: '#8ec3b0' }] },
    { elementType: 'labels.text.stroke',   stylers: [{ color: '#1a2e2c' }] },
    { featureType: 'road',                 elementType: 'geometry',        stylers: [{ color: '#2c3e3e' }] },
    { featureType: 'road',                 elementType: 'geometry.stroke', stylers: [{ color: '#1a2e2c' }] },
    { featureType: 'road',                 elementType: 'labels.text.fill',stylers: [{ color: '#9ca5b3' }] },
    { featureType: 'road.highway',         elementType: 'geometry',        stylers: [{ color: '#0f4a4a' }] },
    { featureType: 'road.highway',         elementType: 'geometry.stroke', stylers: [{ color: '#0d3838' }] },
    { featureType: 'water',                elementType: 'geometry',        stylers: [{ color: '#0e1d1d' }] },
    { featureType: 'water',                elementType: 'labels.text.fill',stylers: [{ color: '#515c6d' }] },
    { featureType: 'poi',                  elementType: 'geometry',        stylers: [{ color: '#263636' }] },
    { featureType: 'poi.park',             elementType: 'geometry',        stylers: [{ color: '#1c3030' }] },
    { featureType: 'transit',              elementType: 'geometry',        stylers: [{ color: '#2f3948' }] },
    { featureType: 'administrative',       elementType: 'geometry.stroke', stylers: [{ color: '#334444' }] },
];

function _trkDibujarMapa(creditos) {
    const mapDiv = document.getElementById('trackMap');
    if (!mapDiv || typeof google === 'undefined') return;

    if (!_trk.mapInstance) {
        _trk.mapInstance = new google.maps.Map(mapDiv, {
            zoom: 10,
            center: { lat: 20.6597, lng: -103.3496 },
            styles: document.body.classList.contains('dark-mode') ? _TRK_DARK_MAP_STYLES : [],
        });
        _trk.geocoder = new google.maps.Geocoder();
    }

    // ── Limpiar mapa anterior ─────────────────────────────────
    _trk.mapMarkers.forEach(m => m.setMap(null));
    _trk.mapMarkers = [];
    if (_trk.directionsRenderer) {
        _trk.directionsRenderer.setMap(null);
        _trk.directionsRenderer = null;
    }

    const map    = _trk.mapInstance;
    const bounds = new google.maps.LatLngBounds();

    // ── Separar por estatus ───────────────────────────────────
    const paraRuta     = creditos
        .filter(c => c.estatus_confirmacion_gestor === 'confirmado')
        .sort((a, b) => (a.orden_ruta || 99) - (b.orden_ruta || 99));
    const soloPin      = creditos.filter(c =>
        c.estatus_confirmacion_gestor === 'pendiente' ||
        c.estatus_confirmacion_gestor === 'en_revision'
    );
    // rechazados → completamente omitidos del mapa

    // ── Icono SVG numerado (confirmados) ─────────────────────
    const svgIcon = (num) => {
        const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="34" height="34">
            <circle cx="17" cy="17" r="15" fill="#e53935" stroke="#fff" stroke-width="2.5"/>
            <text x="17" y="22" text-anchor="middle" fill="#fff"
                  font-size="${num > 9 ? 11 : 13}" font-weight="bold" font-family="Arial,sans-serif">${num}</text>
        </svg>`;
        return {
            url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg),
            scaledSize: new google.maps.Size(34, 34),
            anchor:     new google.maps.Point(17, 17),
        };
    };

    // ── Icono por estatus (no confirmados) ───────────────────
    const statusIcon = (estatus) => {
        const color = estatus === 'en_revision' ? '#fb8c00' : '#fdd835';
        const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="26" height="26">
            <circle cx="13" cy="13" r="11" fill="${color}" stroke="#fff" stroke-width="2"/>
        </svg>`;
        return {
            url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg),
            scaledSize: new google.maps.Size(26, 26),
            anchor:     new google.maps.Point(13, 13),
        };
    };

    // ── Resolver coordenadas de un crédito ───────────────────
    const resolverPos = (c) => new Promise(resolve => {
        const lat = parseFloat(c.latitud_manual ?? c.latitud);
        const lng = parseFloat(c.longitud_manual ?? c.longitud);
        if (!isNaN(lat) && !isNaN(lng) && lat !== 0 && lng !== 0) {
            resolve({ lat, lng });
            return;
        }
        if (_trk.geocoder && (c.direccion || (c.municipio && c.estado))) {
            const addr = [c.direccion, c.municipio, c.estado, 'M\u00e9xico'].filter(Boolean).join(', ');
            _trk.geocoder.geocode({ address: addr }, (res, st) => {
                if (st === 'OK' && res[0]) {
                    resolve({ lat: res[0].geometry.location.lat(), lng: res[0].geometry.location.lng() });
                } else {
                    resolve(null);
                }
            });
        } else {
            resolve(null);
        }
    });

    // ── Resolver todo en paralelo y dibujar ──────────────────
    Promise.all([
        ...paraRuta.map(c  => resolverPos(c).then(pos => ({ c, pos, tipo: 'ruta' }))),
        ...soloPin.map(c   => resolverPos(c).then(pos => ({ c, pos, tipo: 'pin'  }))),
    ]).then(results => {
        // Marcadores sin ruta (pendiente / en_revision)
        results.filter(r => r.tipo === 'pin' && r.pos).forEach(({ c, pos }) => {
            const m = new google.maps.Marker({
                map,
                position: pos,
                icon:  statusIcon(c.estatus_confirmacion_gestor),
                title: `#${c.id_credito} \u2014 ${c.nombre_cliente || ''} (${c.estatus_confirmacion_gestor})`,
                zIndex: 1,
            });
            _trk.mapMarkers.push(m);
            bounds.extend(pos);
        });

        // Marcadores numerados (confirmados)
        const rutaConPos = results
            .filter(r => r.tipo === 'ruta' && r.pos)
            .sort((a, b) => (a.c.orden_ruta || 99) - (b.c.orden_ruta || 99));

        rutaConPos.forEach(({ c, pos }, idx) => {
            const m = new google.maps.Marker({
                map,
                position: pos,
                icon:  svgIcon(idx + 1),
                title: `#${c.id_credito} \u2014 ${c.nombre_cliente || ''}`,
                zIndex: 10 + idx,
            });
            _trk.mapMarkers.push(m);
            bounds.extend(pos);
        });

        if (!bounds.isEmpty()) map.fitBounds(bounds);

        if (rutaConPos.length < 2) {
            if (rutaConPos.length === 1) {
                map.setCenter(rutaConPos[0].pos);
                map.setZoom(14);
            }
            return;
        }

        // ── Trazar polilínea de ruta (solo confirmados) ──────
        const origin      = new google.maps.LatLng(rutaConPos[0].pos.lat, rutaConPos[0].pos.lng);
        const destination = new google.maps.LatLng(rutaConPos[rutaConPos.length - 1].pos.lat, rutaConPos[rutaConPos.length - 1].pos.lng);
        const waypoints   = rutaConPos.slice(1, -1).map(r => ({
            location: new google.maps.LatLng(r.pos.lat, r.pos.lng),
            stopover: true,
        }));

        _trk.directionsRenderer = new google.maps.DirectionsRenderer({
            map,
            suppressMarkers:          true,  // usamos nuestros propios marcadores
            suppressInfoWindows:       true,
            preserveViewport:          true,
            polylineOptions: { strokeColor: '#1565C0', strokeWeight: 4, strokeOpacity: 0.85 },
        });

        new google.maps.DirectionsService().route({
            origin,
            destination,
            waypoints,
            travelMode:               google.maps.TravelMode.DRIVING,
            provideRouteAlternatives: false,
        }, (result, status) => {
            if (status === 'OK') {
                _trk.directionsRenderer.setDirections(result);
            }
            map.fitBounds(bounds);
        });
    });
}

// ─── Map Picker (Plan B: clic en mapa para asignar coords) ──────────────────
const _trkPicker = {
    modal:        null,
    mapInstance:  null,
    marker:       null,
    creditoId:    null,
    selectedLat:  null,
    selectedLng:  null,
};

function _trkAbrirMapPicker(cred) {
    if (!window._trackGoogleMapsKey) {
        Swal.fire({ icon: 'warning', title: 'Sin API Key', text: 'Google Maps no está disponible (falta API key).', confirmButtonText: 'Aceptar' });
        return;
    }

    _trkPicker.creditoId   = cred.id_credito;
    _trkPicker.selectedLat = null;
    _trkPicker.selectedLng = null;

    // Etiqueta en el modal
    document.getElementById('mapPickerCreditoLabel').textContent =
        ` — #${cred.id_credito} ${cred.nombre_cliente ? '(' + cred.nombre_cliente + ')' : ''}`;
    document.getElementById('mapPickerCoordsLabel').innerHTML =
        '<i class="fa-solid fa-crosshairs me-1"></i>Sin selección';
    document.getElementById('btnConfirmarMapPicker').disabled = true;

    // Mostrar modal
    if (!_trkPicker.modal) {
        _trkPicker.modal = new bootstrap.Modal(document.getElementById('modalMapPicker'));
        document.getElementById('btnCerrarMapPicker').addEventListener('click',  () => _trkPicker.modal.hide());
        document.getElementById('btnCancelarMapPicker').addEventListener('click', () => _trkPicker.modal.hide());
        document.getElementById('btnConfirmarMapPicker').addEventListener('click', _trkConfirmarMapPicker);
    }
    _trkPicker.modal.show();

    // Inicializar mapa después de que el modal sea visible (necesario para que el div tenga dimensiones)
    document.getElementById('modalMapPicker').addEventListener('shown.bs.modal', _trkInicializarMapPicker, { once: true });
}

function _trkInicializarMapPicker() {
    const cred = _trk.creditosEnRuta.find(c => String(c.id_credito) === String(_trkPicker.creditoId));
    if (!cred) return;

    // Centro: coordenadas manuales existentes > coords del crédito > GDL
    let centerLat = 20.6597, centerLng = -103.3496;
    if (cred.latitud_manual && cred.longitud_manual) {
        centerLat = parseFloat(cred.latitud_manual);
        centerLng = parseFloat(cred.longitud_manual);
    } else if (cred.latitud && cred.longitud) {
        centerLat = parseFloat(cred.latitud);
        centerLng = parseFloat(cred.longitud);
    }

    const pickerDiv = document.getElementById('mapPickerContainer');

    const initMap = () => {
        if (!_trkPicker.mapInstance) {
            _trkPicker.mapInstance = new google.maps.Map(pickerDiv, {
                zoom: 15,
                center: { lat: centerLat, lng: centerLng },
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: false,
            });

            _trkPicker.mapInstance.addListener('click', (e) => {
                const lat = e.latLng.lat();
                const lng = e.latLng.lng();
                _trkPicker.selectedLat = lat;
                _trkPicker.selectedLng = lng;

                if (!_trkPicker.marker) {
                    _trkPicker.marker = new google.maps.Marker({
                        map: _trkPicker.mapInstance,
                        position: e.latLng,
                        draggable: true,
                        animation: google.maps.Animation.DROP,
                        title: 'Ubicación seleccionada',
                    });
                    _trkPicker.marker.addListener('dragend', (ev) => {
                        _trkPicker.selectedLat = ev.latLng.lat();
                        _trkPicker.selectedLng = ev.latLng.lng();
                        _trkActualizarLabelCoordsicker();
                    });
                } else {
                    _trkPicker.marker.setPosition(e.latLng);
                }

                _trkActualizarLabelCoordsicker();
                document.getElementById('btnConfirmarMapPicker').disabled = false;
            });
        } else {
            // Reusar mapa: re-centrar y limpiar marcador anterior
            _trkPicker.mapInstance.setCenter({ lat: centerLat, lng: centerLng });
            _trkPicker.mapInstance.setZoom(15);
            if (_trkPicker.marker) {
                _trkPicker.marker.setMap(null);
                _trkPicker.marker = null;
            }
        }

        // Si ya tenía coords manuales, mostrar marcador previo
        if (cred.latitud_manual && cred.longitud_manual) {
            const prevPos = { lat: parseFloat(cred.latitud_manual), lng: parseFloat(cred.longitud_manual) };
            _trkPicker.marker = new google.maps.Marker({
                map: _trkPicker.mapInstance,
                position: prevPos,
                draggable: true,
                animation: google.maps.Animation.DROP,
                title: 'Ubicación guardada',
            });
            _trkPicker.selectedLat = prevPos.lat;
            _trkPicker.selectedLng = prevPos.lng;
            _trkPicker.marker.addListener('dragend', (ev) => {
                _trkPicker.selectedLat = ev.latLng.lat();
                _trkPicker.selectedLng = ev.latLng.lng();
                _trkActualizarLabelCoordsicker();
            });
            _trkActualizarLabelCoordsicker();
            document.getElementById('btnConfirmarMapPicker').disabled = false;
        }

        google.maps.event.trigger(_trkPicker.mapInstance, 'resize');
    };

    if (typeof google !== 'undefined' && google.maps) {
        initMap();
    } else if (_trk.mapLoaded) {
        // El script ya está cargando (desde el mapa de ruta); esperar a que esté listo
        const waitForMaps = setInterval(() => {
            if (typeof google !== 'undefined' && google.maps) {
                clearInterval(waitForMaps);
                initMap();
            }
        }, 150);
        setTimeout(() => clearInterval(waitForMaps), 10000);
    } else {
        // Cargar Maps si aún no está
        const script = document.createElement('script');
        script.src   = `https://maps.googleapis.com/maps/api/js?key=${window._trackGoogleMapsKey}&libraries=geometry&callback=_trkMapPickerReady`;
        script.async = true;
        script.defer = true;
        document.head.appendChild(script);
        window._trkMapPickerReady = initMap;
        _trk.mapLoaded = true;
    }
}

function _trkActualizarLabelCoordsicker() {
    const lat = _trkPicker.selectedLat;
    const lng = _trkPicker.selectedLng;
    if (lat === null || lng === null) return;
    document.getElementById('mapPickerCoordsLabel').innerHTML =
        `<i class="fa-solid fa-location-dot me-1" style="color:var(--track-color);"></i>` +
        `Lat: <strong>${lat.toFixed(6)}</strong> &nbsp; Lng: <strong>${lng.toFixed(6)}</strong>`;
}

function _trkConfirmarMapPicker() {
    const lat = _trkPicker.selectedLat;
    const lng = _trkPicker.selectedLng;
    if (lat === null || lng === null) return;

    const cred = _trk.creditosEnRuta.find(c => String(c.id_credito) === String(_trkPicker.creditoId));
    if (cred) {
        cred.latitud_manual  = lat;
        cred.longitud_manual = lng;
        // Sobrescribir también las props que usa el mapa de ruta
        cred.latitud  = lat;
        cred.longitud = lng;
        _trkMarcarCambio();
    }

    _trkPicker.modal.hide();
    _trkRenderListaCreditos();
    _trkRenderizarMapa();
}

// ─── Guardar ruta ────────────────────────────────────────
// ─── Helpers de hora AM/PM ──────────────────────────────
function _trkFormatHora(horaStr) {
    if (!horaStr) return '—';
    const parts = horaStr.split(':');
    const hh    = parseInt(parts[0], 10);
    const mm    = parts[1] || '00';
    const ampm  = hh >= 12 ? 'PM' : 'AM';
    const h12   = hh % 12 || 12;
    return `${h12}:${mm} ${ampm}`;
}

function _trkHoraToPayload() {
    const h    = parseInt($('#rutaHoraH').val(), 10) || 12;
    const m    = $('#rutaHoraM').val() || '00';
    const ampm = $('#rutaHoraAmPm').val() || 'AM';
    let hh;
    if (ampm === 'PM') {
        hh = (h === 12) ? 12 : h + 12;
    } else {
        hh = (h === 12) ? 0 : h;
    }
    return String(hh).padStart(2, '0') + ':' + m;
}

// Convierte un string HH:MM (24h) al objeto {h, m, ampm} en formato 12h
function _trkParseHora12(horaStr) {
    if (!horaStr) return { h: 8, m: '00', ampm: 'AM' };
    const parts = horaStr.split(':');
    const hh    = parseInt(parts[0], 10) || 0;
    const mm    = (parts[1] || '00').slice(0, 2);
    return { h: hh % 12 || 12, m: mm, ampm: hh >= 12 ? 'PM' : 'AM' };
}

// Genera <option> 1-12 con el seleccionado marcado
function _trkEtaHoraOpts(sel) {
    return Array.from({length: 12}, (_, i) => i + 1)
        .map(h => `<option value="${h}"${h === sel ? ' selected' : ''}>${h}</option>`)
        .join('');
}

// Lee los selects H/M/AP del DOM y devuelve HH:MM en 24h
function _trkLeerEtaHora(idCredito, tipo) {
    const $row = $(`#rutaCreditosList .track-credito-row[data-id="${idCredito}"]`);
    const h    = parseInt($row.find(`.eta-h[data-tipo="${tipo}"]`).val(), 10) || 12;
    const m    = $row.find(`.eta-m[data-tipo="${tipo}"]`).val() || '00';
    const ampm = $row.find(`.eta-ap[data-tipo="${tipo}"]`).val() || 'AM';
    let hh;
    if (ampm === 'PM') { hh = (h === 12) ? 12 : h + 12; }
    else               { hh = (h === 12) ? 0  : h; }
    return String(hh).padStart(2, '0') + ':' + m;
}

function _trkGuardarRuta(modo) {
    const nombre    = $('#rutaNombre').val().trim();
    const fecha     = $('#rutaFecha').val();

    // Estado/municipio: preferir el filtro seleccionado; si vacío y hay créditos, derivar del primero
    const primerCred = _trk.creditosEnRuta[0] || {};
    const estado    = $('#crdFiltroEstado').val()    || primerCred.estado    || '';
    const municipio = $('#crdFiltroMunicipio').val() || primerCred.municipio || '';

    if (!nombre) {
        Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'El nombre de la ruta es obligatorio.', confirmButtonText: 'Aceptar' });
        document.getElementById('rutaNombre').focus();
        return;
    }
    if (_trk.creditosEnRuta.length === 0) {
        Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'Debe agregar al menos un crédito a la ruta.', confirmButtonText: 'Aceptar' });
        return;
    }
    if (!fecha) {
        Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'La fecha programada es obligatoria.', confirmButtonText: 'Aceptar' });
        return;
    }

    if (modo !== 'borrador' && modo !== 'actualizar') {
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
        hora_salida:      _trkHoraToPayload(),
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
            fecha_eta:                   c.fecha_eta    || null,
            hora_eta_ini:                c.hora_eta_ini || null,
            hora_eta_fin:                c.hora_eta_fin || null,
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
            _trkCargarBorradores();
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
                    <div class="col-6 col-md-2"><b class="d-block small text-muted">Hora de salida</b>${
                        d.act_hora_1
                            ? `<span class="badge bg-warning text-dark me-1" title="Hora actualizada">${_trkFormatHora(d.act_hora_1)}</span><s class="text-muted small">${_trkFormatHora(d.hora_inicial)}</s>`
                            : (d.hora_inicial ? `<span class="badge bg-light text-dark border">${_trkFormatHora(d.hora_inicial)}</span>` : '—')
                    }</div>
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

// ─── Marcar cambios pendientes ────────────────────────
function _trkMarcarCambio() {
    if (_trk.cargando) return;
    _trk.haychangios = true;
    if (_trk.soloLectura) {
        $('#btnActualizarRuta').prop('disabled', false)
            .css({ background: '#0d6efd', cursor: 'pointer' });
    }
}

// ─── Bloquear / Desbloquear modal ───────────────────────
function _trkBloquearModal() {
    _trk.soloLectura = true;
    // Solo bloquear campos de cabecera de la ruta
    $('#rutaNombre, #rutaFecha, #rutaHoraH, #rutaHoraM, #rutaHoraAmPm').prop('disabled', true);
    // Usuarios: bloquear también
    $('#rutaUsuarios').prop('disabled', true);
    if ($('#rutaUsuarios').hasClass('select2-hidden-accessible')) {
        $('#rutaUsuarios').prop('disabled', true);
    }
    // Ocultar sección de agregar nuevos créditos (no añadir, solo editar existentes)
    $('#secAgregarCredito').hide();
    $('#reorderHint').hide();
    // Swap de botones: ocultar borrador/enviar, mostrar actualizar (gris hasta que haya cambios)
    $('#btnGuardarBorrador, #btnEnviarRuta').hide();
    $('#btnActualizarRuta').show().prop('disabled', true)
        .css({ background: '#94a3b8', cursor: 'not-allowed' });
    // Badge en título
    const $label = $('#modalRegistrarRutaLabel');
    if (!$label.find('.badge-solo-lectura').length) {
        $label.append('<span class="badge bg-secondary badge-solo-lectura ms-2" style="font-size:.63rem;vertical-align:middle;">Ver ruta</span>');
    }
}

function _trkDesbloquearModal() {
    _trk.soloLectura = false;
    $('#rutaNombre, #rutaFecha, #rutaUsuarios, #rutaHoraH, #rutaHoraM, #rutaHoraAmPm, #crdFiltroEstado').prop('disabled', false);
    if ($('#rutaUsuarios').hasClass('select2-hidden-accessible')) {
        $('#rutaUsuarios').prop('disabled', false);
    }
    $('#btnGuardarBorrador, #btnEnviarRuta').show();
    $('#btnActualizarRuta').hide();
    $('#secAgregarCredito').show();
    $('#reorderHint').show();
    $('#modalRegistrarRutaLabel .badge-solo-lectura').remove();
}

// ─── Abrir ruta existente en el modal ───────────────────
function _trkCargarRutaEnModal(idRuta, soloLectura) {
    _trkResetModal();
    _trk.idRutaEditando = idRuta;
    _trk.cargando       = true;

    // Actualizar título mientras carga
    const icon = soloLectura ? 'eye' : 'pen-to-square';
    document.getElementById('modalRegistrarRutaLabel').innerHTML =
        `<i class="fa-solid fa-${icon} me-2"></i>${soloLectura ? 'Ver ruta' : 'Editar ruta'}`;

    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalRegistrarRuta'));
    modal.show();

    trkFetch(`/TrackingRecoleccion/obtenerDetalleRuta?id_ruta=${idRuta}`)
        .then(r => {
            if (!r.success || !r.datos) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar la ruta.', confirmButtonText: 'Aceptar' });
                modal.hide();
                return;
            }
            const d = r.datos;

            // Título final con nombre de la ruta
            document.getElementById('modalRegistrarRutaLabel').innerHTML =
                `<i class="fa-solid fa-${icon} me-2"></i>${soloLectura ? 'Ver ruta' : 'Editar ruta'}: <em>${d.nombre_ruta || ''}</em>`;

            // Campos básicos
            $('#rutaNombre').val(d.nombre_ruta || '');
            $('#rutaFecha').val(d.fecha_programada || '');

            // Créditos (cargar directamente en array)
            _trk.creditosEnRuta = (d.detalle || []).map((det, i) => ({
                id_credito:                  det.id_credito,
                nombre_cliente:              det.nombre_cliente || '',
                moto_marca:                  '',
                moto_modelo:                 det.modelo || '',
                bin:                         det.bin || '',
                estado:                      det.estado || '',
                municipio:                   det.municipio || '',
                direccion:                   det.direccion || '',
                latitud:                     det.latitud  || null,
                longitud:                    det.longitud || null,
                orden_ruta:                  det.orden_ruta || (i + 1),
                estatus_confirmacion_gestor: det.estatus_confirmacion_gestor || 'pendiente',
                fecha_eta:                   det.fecha_eta    || null,
                hora_eta_ini:                det.hora_eta_ini || null,
                hora_eta_fin:                det.hora_eta_fin || null,
            }));
            _trkRenderListaCreditos();
            _trkRefrescarSelectCreditos();
            _trkRenderizarMapa();

            // Usuarios
            if (d.usuarios && d.usuarios.length) {
                _trk.usuariosSeleccionados = d.usuarios.map(u => ({
                    id: u.id_usuario, nombre: u.nombre_usuario,
                }));
                const ids = d.usuarios.map(u => String(u.id_usuario));
                $('#rutaUsuarios').val(ids).trigger('change');
            }

            // Estado + Municipio via filtros de créditos
            $('#crdFiltroEstado').val(d.estado || '').trigger('change');
            if (d.municipio) {
                $('#crdFiltroMunicipio').val(d.municipio);
            }
            _trk.estatusRuta  = d.estatus_ruta || null;
            _trk.cargando     = false;
            _trk.haychangios  = false;

            // Aplicar bloqueo si es solo lectura
            if (soloLectura) _trkBloquearModal();

            // Poblar hora desde act_hora_1 (si hay cambio) o hora_inicial
            const horaVigente = d.act_hora_1 || d.hora_inicial || null;
            if (horaVigente) {
                const hParts = horaVigente.split(':');
                const hh = parseInt(hParts[0], 10);
                const mm = hParts[1] || '00';
                const ampm = hh >= 12 ? 'PM' : 'AM';
                const h12  = hh % 12 || 12;
                $('#rutaHoraH').val(String(h12));
                $('#rutaHoraM').val(mm);
                $('#rutaHoraAmPm').val(ampm);
                // Si hay hora actualizada, mostrar la original tachada como referencia
                if (d.act_hora_1 && d.hora_inicial) {
                    $('#rutaHoraActInfo')
                        .removeClass('d-none')
                        .html(`<span class="text-warning"><i class="fa-solid fa-clock-rotate-left me-1"></i>Hora original: <s>${_trkFormatHora(d.hora_inicial)}</s></span>`);
                } else {
                    $('#rutaHoraActInfo').addClass('d-none').text('');
                }
            }

            _trk.haychangios = false;
        })
        .catch(() => {
            _trk.cargando = false;
            Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexión.', confirmButtonText: 'Aceptar' });
            modal.hide();
        });
}
</script>
