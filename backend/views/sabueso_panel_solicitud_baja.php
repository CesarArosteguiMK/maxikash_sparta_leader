<style>
    #tablaSolicitudesBaja th:nth-child(4) { min-width: 10rem; }
    #tablaSolicitudesBaja .btn-ver-solicitud { padding: 0.35rem 0.6rem; }
    .solicitud-ver-seccion { margin-bottom: 1.25rem; }
    .solicitud-ver-seccion:last-child { margin-bottom: 0; }
    @media (max-width: 768px) {
        #tablaSolicitudesBaja .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    }
</style>
<div class="card">
    <div class="card-header border-bottom">
        <h5 class="card-title mb-0">
            <i class="fa-solid fa-user-xmark me-2"></i>Panel Admin – Solicitudes de baja
        </h5>
    </div>
    <div class="card-datatable table-responsive">
        <table id="tablaSolicitudesBaja" class="dt-responsive table border-top">
            <thead>
                <tr>
                    <th></th>
                    <th>Fecha</th>
                    <th>Motivo</th>
                    <th>Colaborador a dar de baja</th>
                    <th>Quién solicitó</th>
                    <th>Adjunto</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Modal Ver solicitud de baja (solo lectura) -->
<div class="modal fade" id="modalVerSolicitudBaja" tabindex="-1" aria-labelledby="modalVerSolicitudBajaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="modalVerSolicitudBajaLabel">
                    <i class="fa-solid fa-eye me-2"></i>Ver solicitud de baja
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <!-- Se llena por JS -->
            </div>
        </div>
    </div>
</div>
