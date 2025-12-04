<h4>Usuarios Registrados</h4>

<div id="resumenSolicitudes" class="row mb-5 g-2"></div>

<div class="card">
    <div class="row justify-content-between m-4">
        <div class="col-4"></div>
        <div class="col-4 d-flex align-self-end justify-content-end">
            <button id="btnAgregar" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#modalNuevaSolicitud"><i class="fa fa-plus">&nbsp;</i>Nueva Empresa</button>
        </div>
    </div>

    <div class="card-datatable table-responsive">
        <table id="historialSolicitudes" class="dt-responsive table border-top">
            <thead>
                <tr>
                    <th></th>
                    <th>Nombre de la Empresa</th>
                    <th>RFC</th>
                    <th>Razón Social</th>
                    <th>Estatus</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal para agregar solicitud -->
<div class="modal fade" id="modalNuevaSolicitud" tabindex="-1" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="text-center w-100">
                    <h4 class="address-title mb-2">Nueva solicitud de viáticos/gastos</h4>
                    <p class="address-subtitle">Capture los datos solicitados</p>
                </div>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="form-group col-4">
                        <label for="tipoSolicitud" class="form-label">Tipo</label>
                        <select class="form-select" id="tipoSolicitud" name="tipoSolicitud">
                            <option value="1">Viáticos (por comprobar)</option>
                            <option value="2">Gastos (reembolso)</option>
                        </select>
                        <div class="fv-message text-danger small" style="min-height: 1.25rem"></div>
                    </div>
                    <div class="form-group col-5">
                        <label for="fechasNuevaSolicitud" class="form-label">Periodo del proyecto</label>
                        <div class="input-group input-group-merge cursor-pointer">
                            <input type="text" id="fechasNuevaSolicitud" name="fechasNuevaSolicitud" class="form-control cursor-pointer" readonly>
                            <span class="input-group-text">
                                <i class="fa fa-calendar-days"></i>
                            </span>
                        </div>
                        <div class="fv-message text-danger small" style="min-height: 1.25rem"></div>
                    </div>
                    <div class="form-group col-3">
                        <label id="lblMontoVG" for="montoVG" class="form-label numeral-input">Monto Solicitado</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text">
                                <i class="fa fa-dollar-sign"></i>
                            </span>
                            <input type="text" id="montoVG" name="montoVG" class="form-control" placeholder="0.00">
                        </div>
                        <div class="fv-message text-danger small" style="min-height: 1.25rem"></div>
                    </div>
                    <div class="form-group col-12">
                        <label for="proyecto" class="form-label">Proyecto</label>
                        <input type="text" id="proyecto" name="proyecto" class="form-control" placeholder="Proyecto o actividad a cubrir. Ej.: Capacitación en corporativo el día..." maxlength="500">
                        <div class="fv-message text-danger small" style="min-height: 1.25rem"></div>
                    </div>
                </div>
                <div id="comprobantesGastos" class="row" style="display: none;">
                    <div class="col-12">
                        <h5 class="text-center">Comprobantes de Gastos</h5>
                        <div class="table-responsive text-nowrap">
                            <table id="tablaComprobantes" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Concepto</th>
                                        <th>Fecha</th>
                                        <th>Monto</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyComprobantes">
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4" class="text-center">
                                            <button type="button" id="btnAgregarComprobante" class="btn btn-success btn-sm">
                                                <i class="fa fa-plus">&nbsp;</i>Agregar
                                            </button>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" id="cancelaSolicitud" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">Cancelar</button>
                <button type="button" id="registraSolicitud" class="btn btn-primary">Guardar</button>
            </div>
        </div>
    </div>
</div>
