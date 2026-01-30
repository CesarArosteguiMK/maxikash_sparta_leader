<style>
    .credito-modal-list { display: flex; flex-direction: column; gap: 0.75rem; }
    .credito-modal-item { display: flex; flex-direction: column; gap: 0.15rem; }
    .credito-modal-item .fw-medium { word-break: break-word; }
    #modalRastreoCredito .modal-body.rastreo-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem 2rem; }
    @media (max-width: 768px) { #modalRastreoCredito .modal-body.rastreo-grid { grid-template-columns: 1fr; } }
    #modalRastreoCredito .rastreo-block-full { grid-column: 1 / -1; }
    #modalRastreoCredito.modal .modal-dialog { max-width: 95vw; width: 95vw; height: 90vh; max-height: 90vh; margin: 2rem auto; }
    #modalRastreoCredito .modal-content { height: 100%; display: flex; flex-direction: column; }
    #modalRastreoCredito .modal-body { flex: 1; overflow-y: auto; }
    #modalRastreoCredito .rastreo-seccion-direcciones, #modalRastreoCredito .rastreo-seccion-blank { min-height: 200px; border: 1px solid var(--bs-border-color); border-radius: 0.375rem; background: var(--bs-body-bg); }
    #tablaTicketsPanel th:nth-child(6) { min-width: 10rem; white-space: nowrap; }
    #tablaTicketsPanel .d-flex.flex-wrap.gap-1 { flex-wrap: wrap; gap: 0.35rem !important; }
    #tablaTicketsPanel .d-flex.flex-wrap.gap-1 .btn { flex-shrink: 0; }
    @media (max-width: 768px) {
        #tablaTicketsPanel .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    }
</style>
<script>
    var miUsuarioId = <?php echo (int)($miUsuarioId ?? 0); ?>;
    var miUsuarioNombre = <?php echo json_encode($miUsuarioNombre ?? ''); ?>;
    var ticketIdRastreoActual = null;
</script>
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
                <!-- Abajo: dos bloques – direcciones (lupa) y bloque en blanco -->
                <div class="row g-3">
                    <div class="col-md-6">
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
                    <div class="col-md-6">
                        <div class="rastreo-seccion-blank p-3 h-100" id="rastreoBlank">
                            <span class="text-muted small">—</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2 border-top d-flex flex-wrap gap-2 justify-content-end align-items-center">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-sm btn-outline-primary" id="btnAsignarRastreo" onclick="mostrarAsignarOpciones()" title="Asignar este ticket">
                    <i class="fa-solid fa-user-plus me-1"></i>Asignar...
                </button>
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
