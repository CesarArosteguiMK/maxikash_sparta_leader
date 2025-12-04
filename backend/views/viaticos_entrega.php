<h4>Entrega de Viáticos y devolución de Gastos</h4>

<div class="card">
    <div class="row justify-content-between m-4">
        <div class="col-4">
            <label for="fechasSolicitudes" class="form-label">Rango de fechas mostrado</label>
            <div class="input-group input-group-merge">
                <input type="text" id="fechasSolicitudes" class="form-control cursor-pointer" readonly>
                <i class="input-group-text fa fa-calendar-days"></i>
                <button id="btnBuscarSolicitudes" class="btn btn-outline-primary">Actualizar</button>
            </div>
        </div>
        <div class="col-4 d-flex align-self-end justify-content-end">
            <button type="button" id="btnReimprimir" class="btn btn-info"><i class="fa fa-print">&nbsp;</i>Re imprimir comprobante</button>
            <input type="hidden" id="solActivas" value="<?= $activas; ?>">
        </div>
    </div>
    <div class="card-datatable table-responsive">
        <table id="historialSolicitudes" class="dt-responsive table border-top">
            <thead>
                <tr>
                    <th></th>
                    <th>ID</th>
                    <th>Tipo</th>
                    <th>Solicitante</th>
                    <th>Fecha de Autorización</th>
                    <th>Fecha de Entrega</th>
                    <th>Monto</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal para ver solicitud -->
<div class="modal fade" id="modalVerEntrega" tabindex="-1" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="text-center w-100">
                    <h4 class="address-title mb-2">Entrega de viáticos/Devolución de gastos</h4>
                    <p class="address-subtitle"></p>
                </div>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-2">
                        <img id="verFotoUsuario" src="/assets/img/misc/user.svg" alt="Foto del usuario" class="rounded-circle" style="width: 100px; height: 100px;">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Solicitante</label>
                        <input type="text" id="verSolicitante" class="form-control" disabled>
                        <input type="hidden" id="verSolicitudId">
                    </div>
                    <div class="col-4">
                        <label class="form-label">Sucursal de entrega</label>
                        <input type="text" id="verSucursal" class="form-control" disabled>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-4">
                        <label class="form-label">Tipo de Solicitud</label>
                        <input type="text" id="verTipoSol" class="form-control" disabled>
                        <input type="hidden" id="verTipoSolId">
                    </div>
                    <div class="form-group col-4">
                        <label class="form-label">Fecha de inicio del proyecto</label>
                        <input type="text" id="verFechaI" class="form-control" disabled>
                    </div>
                    <div class="form-group col-4">
                        <label class="form-label">Fecha de termino del proyecto</label>
                        <input type="text" id="verFechaF" class="form-control" disabled>
                    </div>
                    <div class="form-group col-12">
                        <label class="form-label">Proyecto</label>
                        <input type="text" id="verProyecto" class="form-control" disabled>
                    </div>
                    <div class="form-group col-4">
                        <label class="form-label">Autorizado por</label>
                        <input type="text" id="verAutorizado" class="form-control" disabled>
                    </div>
                    <div class="form-group col-4">
                        <label class="form-label">Fecha de autorización</label>
                        <input type="text" id="verFechaAutorizado" class="form-control" disabled>
                    </div>
                    <div class="form-group col-4">
                        <label class="form-label">Monto Autorizado</label>
                        <input type="text" id="verMontoAutorizado" class="form-control" disabled>
                    </div>
                    <div class="form-group col-4">
                        <label class="form-label">Método de entrega</label>
                        <select id="metodoEntrega" name="metodoEntrega" class="form-select" disabled>
                            <?= $metodosEntrega ?>
                        </select>
                        <div class="fv-message text-danger small" style="min-height: 1.25rem"></div>
                    </div>
                    <div class="form-group col-4">
                        <label class="form-label">Monto Entregado</label>
                        <div class="input-group input-group-merge">
                            <i class="input-group-text fa fa-dollar-sign"></i>
                            <input type="text" id="montoEntrega" name="montoEntrega" class="form-control" placeholder="0.00">
                        </div>
                        <div class="fv-message text-danger small" style="min-height: 1.25rem"></div>
                    </div>
                    <div class="form-group col-12">
                        <label for="observacionesEntrega" class="form-label">Observaciones</label>
                        <textarea id="observacionesEntrega" name="observacionesEntrega" class="form-control mayusculas" placeholder="Observaciones al entregar. Ej.: Se entrego un monto menor debido a..." rows="2" maxlength="500"></textarea>
                        <div class="fv-message text-danger small" style="min-height: 1.25rem"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="cancelar" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">Cancelar</button>
                <button type="button" id="entregar" class="btn btn-primary">Entregar</button>
            </div>
        </div>
    </div>
</div>
<!-- / Modal para ver solicitud -->

<!-- Modal para reimprimir un comprobante -->
<div class="modal fade" id="modalReimprimir" tabindex="-1" aria-hidden="true" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="text-center w-100">
                    <h4 class="address-title mb-2">Re imprimir comprobante de entrega</h4>
                    <p class="address-subtitle"></p>
                </div>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <label for="solicitudReimprimir" class="form-label">Solicitud a re imprimir</label>
                        <input type="text" id="solicitudReimprimir" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="cancelarReimprimir" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">Cancelar</button>
                <button type="button" id="btnReimprimirAjuste" class="btn btn-primary">Re imprimir</button>
            </div>
        </div>
    </div>
</div>
<!-- / Modal para reimprimir un comprobante -->