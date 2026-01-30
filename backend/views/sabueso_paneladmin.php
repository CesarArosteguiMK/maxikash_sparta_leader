<style>
    .credito-modal-list { display: flex; flex-direction: column; gap: 0.75rem; }
    .credito-modal-item { display: flex; flex-direction: column; gap: 0.15rem; }
    .credito-modal-item .fw-medium { word-break: break-word; }
    #modalRastreoCredito .modal-dialog { max-width: 400px; }
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
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Modal Iniciar rastreo: datos de la persona/crédito del ticket -->
<div class="modal fade" id="modalRastreoCredito" tabindex="-1" aria-labelledby="modalRastreoCreditoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-sm">
            <div class="modal-header py-2 border-bottom">
                <h6 class="modal-title text-primary mb-0" id="modalRastreoCreditoLabel">
                    <i class="fa-solid fa-magnifying-glass-plus me-2"></i>Iniciar rastreo – Datos del crédito
                </h6>
                <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body py-3" id="modalRastreoCreditoBody">
                <!-- Se llena por JS al hacer clic en Iniciar rastreo -->
            </div>
            <div class="modal-footer py-2 border-top">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
