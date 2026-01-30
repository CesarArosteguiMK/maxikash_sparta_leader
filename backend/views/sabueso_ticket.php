<style>
    #modal_fecha_vencimiento { width: 100%; cursor: pointer; }
    .flatpickr-calendar .flatpickr-monthDropdown-months { appearance: none !important; background-image: none !important; -webkit-appearance: none; -moz-appearance: none; }
    .flatpickr-calendar { transform: scale(1.12); transform-origin: top left; }
    /* Modal datos crédito: compacto vertical */
    .credito-modal-list { display: flex; flex-direction: column; gap: 0.75rem; }
    .credito-modal-item { display: flex; flex-direction: column; gap: 0.15rem; }
    .credito-modal-item .fw-medium { word-break: break-word; }
    #modalDatosCredito .modal-dialog { max-width: 400px; }
</style>
<div class="card">
    <div class="card-header border-bottom d-flex flex-wrap justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="fa-solid fa-magnifying-glass me-2"></i>Tickets
        </h5>
        <button type="button" class="btn btn-primary" onclick="abrirModalLevantarTicket()" title="Crear nuevo ticket">
            <i class="fa-solid fa-plus me-1"></i>Levantar ticket
        </button>
    </div>
    <div class="card-body border-bottom d-flex flex-wrap align-items-center gap-2 py-3">
        <label for="buscar_id_credito" class="form-label mb-0 text-nowrap">ID de crédito</label>
        <div class="input-group input-group-merge flex-grow-1" style="min-width: 280px; max-width: 420px;">
            <input type="number" id="buscar_id_credito" class="form-control" placeholder="Ej: 123456">
            <button type="button" class="btn btn-outline-primary" onclick="buscarCreditoModal()" title="Buscar datos del crédito">
                <i class="fa-solid fa-magnifying-glass"></i> Buscar
            </button>
        </div>
    </div>
    <div class="card-datatable table-responsive">
        <table id="tablaTickets" class="dt-responsive table border-top">
            <thead>
                <tr>
                    <th></th>
                    <th>Folio / Tipo</th>
                    <th>Estado</th>
                    <th>Prioridad</th>
                    <th>Crédito</th>
                    <th>Fechas</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Modal Levantar ticket -->
<div class="modal fade" id="modalLevantarTicket" tabindex="-1" aria-labelledby="modalLevantarTicketLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="modalLevantarTicketLabel">
                    <i class="fa-solid fa-ticket me-2"></i>Levantar ticket
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">El ticket se registrará a tu nombre (perfil actual), con estado <strong>Abierto</strong>, y la fecha de creación será la hora de CDMX. El folio se genera automáticamente.</p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="modal_id_tipo_ticket" class="form-label">Tipo <span class="text-danger">*</span></label>
                        <select id="modal_id_tipo_ticket" class="form-select" required>
                            <option value="">Seleccione...</option>
                        </select>
                    </div>
                    <div class="col-md-6 d-none" aria-hidden="true">
                        <label class="form-label">Estado</label>
                        <input type="text" class="form-control bg-light" value="Abierto" readonly>
                    </div>
                    <div class="col-md-6">
                        <label for="modal_id_prioridad" class="form-label">Prioridad <span class="text-danger">*</span></label>
                        <select id="modal_id_prioridad" class="form-select" required>
                            <option value="">Seleccione...</option>
                        </select>
                    </div>
                    <div class="col-md-6 d-none" aria-hidden="true">
                        <label for="modal_id_origen_ticket" class="form-label">Origen <span class="text-danger">*</span></label>
                        <select id="modal_id_origen_ticket" class="form-select" required aria-hidden="true">
                            <option value="">Seleccione...</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="modal_id_credito" class="form-label">ID de crédito <span class="text-danger">*</span></label>
                        <input type="number" id="modal_id_credito" class="form-control" placeholder="ID de crédito" min="1" step="1" required>
                    </div>
                    <div class="col-md-6">
                        <label for="modal_fecha_vencimiento" class="form-label">Fecha de vencimiento <span class="text-danger">*</span></label>
                        <input type="text" id="modal_fecha_vencimiento" class="form-control" placeholder="YYYY-MM-DD" required autocomplete="off">
                    </div>
                    <div class="col-12">
                        <label for="modal_descripcion_inicial" class="form-label">Descripción inicial <span class="text-danger">*</span></label>
                        <textarea id="modal_descripcion_inicial" class="form-control" rows="4" placeholder="Describa el motivo del ticket..." required></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="enviarLevantarTicket()">
                    <i class="fa-solid fa-check me-1"></i>Levantar ticket
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal datos del crédito (búsqueda por ID) - compacto vertical -->
<div class="modal fade" id="modalDatosCredito" tabindex="-1" aria-labelledby="modalDatosCreditoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-sm">
            <div class="modal-header py-2 border-bottom">
                <h6 class="modal-title text-primary mb-0" id="modalDatosCreditoLabel">
                    <i class="fa-solid fa-user-tag me-2"></i>Datos del crédito
                </h6>
                <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body py-3" id="modalDatosCreditoBody">
                <!-- Se llena por JS cuando existe el crédito -->
            </div>
            <div class="modal-footer py-2 border-top">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-sm btn-primary" onclick="usarCreditoEnTicket()" title="Abrir formulario de ticket con este ID ya rellenado">
                    <i class="fa-solid fa-check me-1"></i>Usar este crédito
                </button>
            </div>
        </div>
    </div>
</div>
