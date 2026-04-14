<style>
    #tablaTicketsCerradosEliminados th:nth-child(4) { min-width: 10rem; white-space: nowrap; }
    #tablaTicketsCerradosEliminados .btn-ver-cerrado { padding: 0.35rem 0.6rem; }
    .cerrado-ver-seccion { margin-bottom: 1.25rem; }
    .cerrado-ver-seccion:last-child { margin-bottom: 0; }
    @media (max-width: 768px) {
        #tablaTicketsCerradosEliminados .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    }
</style>
<div class="card">
    <div class="card-header border-bottom">
        <h5 class="card-title mb-0">
            <i class="fa-solid fa-archive me-2"></i>Cerrado / Eliminado – Tickets cerrados o eliminados
        </h5>
    </div>
    <div class="card-datatable table-responsive">
        <table id="tablaTicketsCerradosEliminados" class="dt-responsive table border-top">
            <thead>
                <tr>
                    <th></th>
                    <th>Folio / Tipo</th>
                    <th>Crédito</th>
                    <th>Fechas</th>
                    <th>Quién levantó</th>
                    <th>Asignado a</th>
                    <th>Acción</th>
                    <th>Quién eliminó/cerró</th>
                    <th>Fecha cierre/eliminación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Modal Ver ticket cerrado/eliminado (solo lectura) -->
<div class="modal fade" id="modalVerCerradoEliminado" tabindex="-1" aria-labelledby="modalVerCerradoEliminadoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="modalVerCerradoEliminadoLabel">
                    <i class="fa-solid fa-eye me-2"></i>Ver ticket cerrado/eliminado
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <!-- Se llena por JS con datos del crédito, ticket, historial asignación -->
            </div>
        </div>
    </div>
</div>
