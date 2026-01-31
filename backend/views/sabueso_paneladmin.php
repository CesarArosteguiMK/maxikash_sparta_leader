<style>
    .credito-modal-list { display: flex; flex-direction: column; gap: 0.75rem; }
    .credito-modal-item { display: flex; flex-direction: column; gap: 0.15rem; }
    .credito-modal-item .fw-medium { word-break: break-word; }
    #modalRastreoCredito .modal-body.rastreo-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem 1.5rem; }
    @media (max-width: 992px) { #modalRastreoCredito .modal-body.rastreo-grid { grid-template-columns: 1fr; } }
    #modalRastreoCredito .rastreo-block-full { grid-column: 1 / -1; }
    #modalRastreoCredito.modal .modal-dialog { max-width: 95vw; width: 95vw; height: 90vh; max-height: 90vh; margin: 2rem auto; }
    #modalRastreoCredito .modal-content { height: 100%; display: flex; flex-direction: column; }
    #modalRastreoCredito .modal-body { flex: 1; overflow-y: auto; }
    #modalRastreoCredito .rastreo-seccion-direcciones,
    #modalRastreoCredito .rastreo-seccion-evidencias,
    #modalRastreoCredito .rastreo-seccion-bitacora { min-height: 200px; border: 1px solid var(--bs-border-color); border-radius: 0.375rem; background: var(--bs-body-bg); }
    @media (min-width: 992px) {
        #modalRastreoCredito .rastreo-col-evidencias,
        #modalRastreoCredito .rastreo-col-bitacora { border-left: 1px solid #b0b0b0; }
    }
    #modalRastreoCredito .evidencia-slot { width: 100%; aspect-ratio: 1; max-height: 120px; border: 2px dashed var(--bs-border-color); border-radius: 0.375rem; background: var(--bs-light); display: flex; align-items: center; justify-content: center; cursor: pointer; overflow: hidden; }
    #modalRastreoCredito .evidencia-slot img { width: 100%; height: 100%; object-fit: cover; }
    #modalRastreoCredito .evidencia-slot:hover { border-color: var(--bs-primary); background: rgba(105, 108, 255, 0.08); }
    #tablaTicketsPanel th:nth-child(6) { min-width: 10rem; white-space: nowrap; }
    #tablaTicketsPanel .d-flex.flex-wrap.gap-1 { flex-wrap: wrap; gap: 0.35rem !important; }
    #tablaTicketsPanel .d-flex.flex-wrap.gap-1 .btn { flex-shrink: 0; }
    @media (max-width: 768px) {
        #tablaTicketsPanel .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    }
    /* Modal evidencia: z-index se fija por JS al abrir para quedar por delante de Iniciar rastreo */
    #modalEvidenciaRastreo .modal-dialog {
        max-width: 95vw;
        width: 100%;
    }
    @media (min-width: 992px) {
        #modalEvidenciaRastreo .modal-dialog { max-width: 1000px; }
    }
    #modalEvidenciaRastreo .modal-content {
        border: none;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 8px 32px rgba(0,0,0,0.25);
    }
    #modalEvidenciaRastreo .modal-header {
        background: #1e3a5f;
        color: #e2e8f0;
        border-bottom: 1px solid #2d4a6f;
        padding: 0.5rem 0.75rem;
    }
    #modalEvidenciaRastreo .modal-header .modal-title {
        font-weight: 500;
        font-size: 0.9rem;
        color: #e2e8f0;
    }
    #modalEvidenciaRastreo .modal-header .btn-close-evidencia {
        width: auto;
        height: auto;
        padding: 0.35rem 0.65rem;
        margin: 0;
        background: rgba(255,255,255,0.2);
        border: 1px solid rgba(255,255,255,0.5);
        border-radius: 6px;
        color: #fff;
        font-size: 0.85rem;
        opacity: 1;
    }
    #modalEvidenciaRastreo .modal-header .btn-close-evidencia:hover {
        background: rgba(255,255,255,0.3);
        color: #fff;
    }
    #modalEvidenciaRastreo .modal-body {
        background: #2d3748;
        padding: 0.75rem;
        min-height: 80px;
    }
    #modalEvidenciaRastreo .modal-body img {
        border-radius: 6px;
        max-height: 85vh;
        width: auto;
        max-width: 100%;
        display: inline-block;
    }
    #modalEvidenciaRastreo .modal-body .text-muted {
        color: #94a3b8 !important;
        font-size: 0.85rem;
    }
    #modalEvidenciaRastreo .modal-footer {
        background: #1e293b;
        border-top: 1px solid #334155;
        padding: 0.5rem 0.75rem;
    }
    #modalEvidenciaRastreo .modal-footer .btn-danger {
        background: #7f1d1d;
        border-color: #7f1d1d;
        color: #fecaca;
    }
    #modalEvidenciaRastreo .modal-footer .btn-danger:hover {
        background: #991b1b;
        border-color: #991b1b;
        color: #fff;
    }
    #modalEvidenciaRastreo .modal-footer .btn-secondary {
        background: #475569;
        border-color: #475569;
        color: #e2e8f0;
    }
    #modalEvidenciaRastreo .modal-footer .btn-secondary:hover {
        background: #64748b;
        border-color: #64748b;
        color: #fff;
    }
</style>
<div class="card">
    <div class="card-header border-bottom">
        <h5 class="card-title mb-0">
            <i class="fa-solid fa-list me-2"></i>Panel Admin – Todos los tickets
        </h5>
    </div>
    <div class="card-datatable table-responsive">
        <table id="tablaTicketsPanel" class="dt-responsive table border-top">
            <thead>
                <tr>
                    <th></th>
                    <th>Folio / Tipo</th>
                    <th>Estado</th>
                    <th>Prioridad</th>
                    <th>Crédito</th>
                    <th>Fechas</th>
                    <th>Quién levantó</th>
                    <th>Asignado a</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Modal Iniciar rastreo: datos de la persona/crédito del ticket (casi pantalla completa) -->
<div class="modal fade" id="modalRastreoCredito" tabindex="-1" aria-labelledby="modalRastreoCreditoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-sm">
            <div class="modal-header py-2 border-bottom">
                <h6 class="modal-title text-primary mb-0" id="modalRastreoCreditoLabel">
                    <i class="fa-solid fa-magnifying-glass-plus me-2"></i>Iniciar rastreo – Datos del crédito
                </h6>
                <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body py-3">
                <!-- Arriba: ID, nombre completo, teléfono cliente -->
                <div class="row g-3 mb-4 pb-3 border-bottom" id="rastreoTop">
                    <!-- Se llena por JS -->
                </div>
                <!-- Medio: quién levantó el ticket, cuándo se levantó, asignado a (si aplica) -->
                <div class="row g-3 mb-4 pb-3 border-bottom" id="rastreoMedio">
                    <!-- Se llena por JS -->
                </div>
                <!-- Ticket(s) levantado(s): folio, tipo, estado, descripción, fechas (estilo bonito) -->
                <div class="mb-4 pb-3 border-bottom" id="rastreoTicketsWrap">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="fa-solid fa-ticket text-primary"></i>
                        <span class="fw-semibold small text-muted">Ticket(s) levantado(s)</span>
                    </div>
                    <div id="rastreoTickets" class="credito-modal-list">
                        <!-- Se llena por JS: tarjetas con folio, tipo, estado, descripción, creación, vencimiento -->
                    </div>
                </div>
                <!-- Abajo: 3 columnas – direcciones, cargar evidencias, bitácora (chat) -->
                <div class="row g-3 rastreo-grid">
                    <div class="col-12 col-lg-4">
                        <div class="rastreo-seccion-direcciones p-3 h-100" id="rastreoDirecciones">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="fa-solid fa-magnifying-glass text-primary"></i>
                                <span class="fw-semibold small text-muted">Todas las direcciones registradas</span>
                            </div>
                            <div id="rastreoDireccionesContenido" class="small">
                                <!-- Se llena por JS -->
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-4 rastreo-col-evidencias">
                        <div class="rastreo-seccion-evidencias p-3 h-100" id="rastreoEvidencias">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <i class="fa-solid fa-images text-primary"></i>
                                <span class="fw-semibold small text-muted">Cargar evidencias</span>
                            </div>
                            <div class="row g-2" id="rastreoEvidenciasSlots">
                                <div class="col-6"><div class="evidencia-slot" data-slot="0" data-id="" title="Clic para cargar"><i class="fa-solid fa-plus text-muted"></i></div></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-4 rastreo-col-bitacora">
                        <div class="rastreo-seccion-bitacora p-3 h-100 d-flex flex-column" id="rastreoBitacora">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="fa-solid fa-comments text-primary"></i>
                                <span class="fw-semibold small text-muted">Bitácora</span>
                            </div>
                            <div id="rastreoBitacoraContenido" class="small flex-grow-1 overflow-auto mb-2" style="min-height: 120px;">
                                <!-- Mensajes del chat por JS -->
                            </div>
                            <div class="d-flex gap-2">
                                <input type="text" class="form-control form-control-sm" id="rastreoBitacoraInput" placeholder="Escribir mensaje..." maxlength="500">
                                <button type="button" class="btn btn-sm btn-primary" id="rastreoBitacoraEnviar" title="Enviar"><i class="fa-solid fa-paper-plane"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2 border-top d-flex flex-wrap gap-2 justify-content-end align-items-center">
                <button type="button" class="btn btn-sm btn-outline-primary" id="btnAsignarRastreo" onclick="mostrarAsignarOpciones()" title="Asignar este ticket">
                    <i class="fa-solid fa-user-plus me-1"></i>Asignar...
                </button>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Asignar a: lista de personas del departamento Sabueso -->
<div class="modal fade" id="modalAsignarA" tabindex="-1" aria-labelledby="modalAsignarALabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-sm">
            <div class="modal-header py-2 border-bottom">
                <h6 class="modal-title text-primary mb-0" id="modalAsignarALabel">
                    <i class="fa-solid fa-user-plus me-2"></i>Asignar a...
                </h6>
                <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body py-3" id="modalAsignarABody">
                <!-- Se llena por JS con personas del departamento Sabueso -->
            </div>
            <div class="modal-footer py-2 border-top">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Input oculto para subir evidencias -->
<input type="file" id="inputEvidenciaRastreo" accept="image/*" style="display: none;">

<!-- Modal evidencia: ver imagen + Eliminar / Cerrar, o cargar si está vacío -->
<div class="modal fade" id="modalEvidenciaRastreo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center">
                <h6 class="modal-title mb-0"><i class="fa-solid fa-image me-2"></i>Evidencia</h6>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body text-center" id="modalEvidenciaRastreoBody">
                <!-- Vista previa de imagen o zona para arrastrar/soltar -->
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-sm btn-danger" id="modalEvidenciaEliminar" style="display: none;"><i class="fa-solid fa-trash me-1"></i>Eliminar</button>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
