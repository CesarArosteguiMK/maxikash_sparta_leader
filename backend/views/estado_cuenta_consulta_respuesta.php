<h4>Mis Solicitudes de Viáticos y Gastos</h4>

<div id="resumenSolicitudes" class="row mb-5 g-2">
    <div class="col-auto">
        <div class="card">
            <div class="card-body">
                <div class="card-info text-center">
                    <div class="d-flex flex-column align-items-center justify-content-center"><span class="badge rounded-pill text-bg-dark">Sin solicitudes</span></div>
                    <h4 class="card-title mb-0 me-2">0</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="row justify-content-between m-4">
        <div class="col-4">
            <label class="form-label">Rango de fechas mostrado</label>
            <div class="input-group input-group-merge">
                <input type="text" id="fechasSolicitudes" class="form-control cursor-pointer" readonly>
                <i class="input-group-text fa fa-calendar-days"></i>
                <button id="btnBuscarSolicitudes" class="btn btn-outline-primary">Actualizar</button>
            </div>
        </div>
        <div class="col-4 d-flex align-self-end justify-content-end">
            <button id="btnAgregar" class="btn btn-info"><i class="fa fa-plus">&nbsp;</i>Nueva Solicitud</button>
            <input type="hidden" id="solActivas" value="<?= $activas; ?>">
        </div>
    </div>
    <div class="card-datatable table-responsive">
        <table id="historialSolicitudes" class="dt-responsive table border-top table-hover">
            <thead>
                <tr>
                    <th></th>
                    <th>ID</th>
                    <th>Tipo</th>
                    <th>Proyecto</th>
                    <th>Fechas</th>
                    <th>Monto</th>
                    <th>Estatus</th>
                    <th>Acciones</th>
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
            <div class="form-group col-12 text-center">
                <label id="notificacionEntrega" class="text-info"></label>
                <label id="notificacionHoraEntrega" class="text-info"></label>
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
                            <input type="text" id="montoVG" name="montoVG" class="form-control" value="0.00" disabled>
                        </div>
                        <div class="fv-message text-danger small" style="min-height: 1.25rem"></div>
                    </div>
                    <div class="form-group col-12">
                        <label for="proyecto" class="form-label">Proyecto</label>
                        <input type="text" id="proyecto" name="proyecto" class="form-control mayusculas" placeholder="Proyecto o actividad a cubrir. Ej.: Capacitación en corporativo..." maxlength="100">
                        <div class="fv-message text-danger small" style="min-height: 1.25rem"></div>
                    </div>
                    <div class="form-group col-12 mb-5">
                        <label for="autoriza" class="form-label">Autoriza</label>
                        <input type="text" id="autoriza" name="autoriza" class="form-control" maxlength="100" disabled value="<?= $_SESSION['autorizador_nombre'] ?>">
                    </div>
                    <div class="form-group col-7">
                        <label for="empresa" class="form-label">Empresa</label>
                        <select id="empresa" name="empresa" class="form-select">
                            <?= $empresas ?>
                        </select>
                    </div>
                    <div class="form-group col-5">
                        <label for="sucursalEntrega" class="form-label">Sucursal de entrega</label>
                        <select id="sucursalEntrega" name="sucursalEntrega" class="form-select">
                            <?= $sucursales ?>
                        </select>
                    </div>
                </div>
                <div id="conceptosViaticos" class="row mt-5">
                    <div class="col-12">
                        <h5 class="text-center">Viáticos solicitados</h5>
                        <div class="table-responsive text-nowrap">
                            <table id="tablaConceptos" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Concepto</th>
                                        <th>Observaciones</th>
                                        <th>Monto</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyConceptos">
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4" class="text-center">
                                            <button type="button" class="btn btn-success btn-sm btnAgregarConcepto" id="btnAgregarConcepto">
                                                <i class=" fa fa-plus">&nbsp;</i>Agregar
                                            </button>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <div id="comprobantesGastos" class="row mt-5" style="display: none;">
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
                                            <button type="button" id="btnAgregarComprobanteGastos" class="btn btn-success btn-sm">
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
<!-- / Modal para agregar solicitud -->

<!-- Modal para agregar concepto -->
<div class="modal fade" id="modalAgregarConcepto" tabindex="-1" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="text-center w-100">
                    <h4 class="address-title mb-2">Agregar concepto para viáticos</h4>
                </div>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-8 form-group">
                        <label for="conceptoViaticos" class="form-label">Concepto</label>
                        <select id="conceptoViaticos" name="conceptoViaticos" class="form-select">
                            <option value="" selected disabled>Seleccione un concepto</option>
                            <option value="1">Transporte</option>
                            <option value="2">Alimentos</option>
                            <option value="3">Hospedaje</option>
                            <option value="4">Otros</option>
                        </select>
                        <div class="fv-message text-danger small" style="min-height: 1.25rem"></div>
                    </div>
                    <div class="col-4 form-group">
                        <label for="montoConcepto" class="form-label">Monto</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text">
                                <i class="fa fa-dollar-sign"></i>
                            </span>
                            <input type="text" id="montoConcepto" name="montoConcepto" class="form-control" placeholder="0.00">
                        </div>
                        <div class="fv-message text-danger small" style="min-height: 1.25rem"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col form-group">
                        <label for="conceptoObservaciones" class="form-label">Observaciones</label>
                        <textarea id="conceptoObservaciones" name="conceptoObservaciones" class="form-control mayusculas" placeholder="Observaciones. Ej.: Caseta Mex-Puebla..." rows="2" maxlength="500"></textarea>
                        <div class="fv-message text-danger small" style="min-height: 1.25rem"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="cancelaConcepto" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">Cancelar</button>
                <button type="button" id="actualizarConcepto" class="btn btn-primary" style="display: none" concepto-id="0">Actualizar Concepto</button>
                <button type="button" id="agregarConcepto" class="btn btn-primary">Agregar Concepto</button>
            </div>
        </div>
    </div>
</div>
<!-- / Modal para agregar concepto -->

<!-- Modal para agregar comprobante -->
<div class="modal fade" id="modalAgregarComprobante" tabindex="-1" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="text-center w-100">
                    <h4 class="address-title mb-2">Agregar Comprobante</h4>
                    <p class="address-subtitle">Capture los datos del comprobante</p>
                </div>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="form-group col-12 text-center">
                        <label class="form-label">Comprobante</label>
                    </div>
                    <div class="form-group col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <input type="file" id="comprobante" name="comprobante" class="form-control w-75" accept="image/*,application/pdf">
                            <span class="fs-3">o</span>
                            <button type="button" id="btnTomarFoto" class="btn btn-outline-primary"><i class="fa fa-camera">&nbsp;</i>Tomar foto</button>
                        </div>
                        <div class="fv-message text-danger small" style="min-height: 1.25rem"></div>
                    </div>
                    <div class="form-group col-4">
                        <label for="fechaComprobante" class="form-label">Fecha del comprobante</label>
                        <div class="input-group input-group-merge">
                            <input type="text" id="fechaComprobante" name="fechaComprobante" class="form-control">
                            <i class="input-group-text fa fa-calendar-days"></i>
                        </div>
                        <div class="fv-message text-danger small" style="min-height: 1.25rem"></div>
                    </div>
                    <div class="form-group col-4">
                        <label for="conceptoComprobante" class="form-label">Concepto</label>
                        <select id="conceptoComprobante" name="conceptoComprobante" class="form-select">
                            <?= $conceptos ?>
                        </select>
                        <div class="fv-message text-danger small" style="min-height: 1.25rem"></div>
                    </div>
                    <div class="form-group col-4">
                        <div class="form-group" style="display: none;" id="divNoches">
                            <label for="nochesComprobante" class="form-label">Noches</label>
                            <input type="text" id="nochesComprobante" name="nochesComprobante" class="form-control solo_numeros" value="1" maxlength="2">
                            <div class="fv-message text-danger small" style="min-height: 1.25rem"></div>
                        </div>
                        <label for="montoComprobante" class="form-label">Monto del comprobante</label>
                        <div class="input-group input-group-merge">
                            <i class="input-group-text fa fa-dollar-sign"></i>
                            <input type="text" id="montoComprobante" name="montoComprobante" class="form-control" placeholder="0.00" disabled>
                        </div>
                        <div class="fv-message text-danger small" style="min-height: 1.25rem"></div>

                    </div>
                    <div class="form-group col-12 text-center">
                        <label for="conceptoComprobante" class="form-label">Descripción</label>
                    </div>
                    <div class="form-group col-12 text-center">
                        <label id="descripcionComprobante" class="text-info" style="min-height: 1.5rem"></label>
                    </div>
                    <div class="form-group col-12">
                        <label for="observacionesComprobante" class="form-label">Observaciones</label>
                        <textarea id="observacionesComprobante" name="observacionesComprobante" class="form-control mayusculas" placeholder="Observaciones del gasto. Ej.: Se compro material para el evento..." rows="2" maxlength="500"></textarea>
                        <div class="fv-message text-danger small" style="min-height: 1.25rem"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="cancelaComprobante" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">Cancelar</button>
                <button type="button" id="agregarComprobante" class="btn btn-primary">Agregar Comprobante</button>
            </div>
        </div>
    </div>
</div>
<!-- / Modal para agregar comprobante -->

<!-- Modal para ver solicitud -->
<div class="modal fade" id="modalVerSolicitud" tabindex="-1" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close btnCerrarVer" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="text-center w-100">
                    <h4 class="address-title mb-2">Detalles de la Solicitud</h4>
                </div>
            </div>
            <div class="modal-body">
                <div class="accordion accordion-custom-button" id="acordionVer">
                    <div class="accordion-item active" id="verSolicitud">
                        <h2 class="accordion-header">
                            <button
                                type="button"
                                class="accordion-button"
                                data-bs-toggle="collapse"
                                data-bs-target="#acordionVerSolicitud"
                                aria-expanded="true">
                                <i class="fa fa-circle-info text-info">&nbsp;</i>
                                Solicitud
                            </button>
                        </h2>
                        <div id="acordionVerSolicitud" class="accordion-collapse collapse show" data-bs-parent="#acordionVer">
                            <div class="accordion-body">
                                <div class="row">
                                    <div class="form-group col-4">
                                        <label class="form-label">Tipo de Solicitud</label>
                                        <input type="text" id="verTipoSol" class="form-control" disabled>
                                        <input type="hidden" id="verSolicitudId">
                                    </div>
                                    <div class="form-group col-4">
                                        <label class="form-label">Fecha de registro</label>
                                        <input type="text" id="verFechaReg" class="form-control" disabled>
                                    </div>
                                    <div class="form-group col-4">
                                        <label class="form-label">Estatus</label>
                                        <input type="text" id="verEstatus" class="form-control" disabled>
                                    </div>
                                    <div class="form-group col-12">
                                        <label class="form-label">Proyecto</label>
                                        <input type="text" id="verProyecto" class="form-control" disabled>
                                    </div>
                                    <div class="form-group col-4">
                                        <label class="form-label">Fecha de inicio del proyecto</label>
                                        <input type="text" id="verFechaI" class="form-control" disabled>
                                    </div>
                                    <div class="form-group col-4">
                                        <label class="form-label">Fecha de termino del proyecto</label>
                                        <input type="text" id="verFechaF" class="form-control" disabled>
                                    </div>
                                    <div class="form-group col-4">
                                        <label class="form-label">Monto</label>
                                        <input type="text" id="verMontoSol" class="form-control" disabled>
                                    </div>
                                </div>
                                <div id="verTablaConceptosViaticos" class="row mt-5">
                                    <div class="col-12">
                                        <h5 class="text-center">Conceptos</h5>
                                        <div class="table-responsive text-nowrap">
                                            <table id="tablaVerConceptos" class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th class="d-none"></th>
                                                        <th>Concepto</th>
                                                        <th>Observaciones</th>
                                                        <th>Monto</th>
                                                        <th>Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="tbodyVerConceptos">
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="4" class="text-center">
                                                            <button type="button" class="btn btn-success btn-sm btnAgregarConcepto">
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
                        </div>
                    </div>
                    <div class="accordion-item" id="verAutorizacion">
                        <h2 class="accordion-header">
                            <button
                                type="button"
                                class="accordion-button collapsed"
                                data-bs-toggle="collapse"
                                data-bs-target="#acordionVerAutorizacion"
                                aria-expanded="false">
                                <i id="verAutorizacionIcono" class="fa">&nbsp;</i>
                                Autorización
                            </button>
                        </h2>
                        <div id="acordionVerAutorizacion" class="accordion-collapse collapse" data-bs-parent="#acordionVer">
                            <div class="accordion-body">
                                <div class="row">
                                    <div class="form-group col-8">
                                        <label class="form-label">Autorizado por</label>
                                        <input type="text" id="verAutorizadoPor" class="form-control" disabled>
                                    </div>
                                    <div class="form-group col-4">
                                        <label class="form-label">Fecha</label>
                                        <input type="text" id="verFechaAutorizacion" class="form-control" disabled>
                                    </div>
                                    <div class="form-group col-4">
                                        <label class="form-label">Monto autorizado</label>
                                        <input type="text" id="verMontoAutorizado" class="form-control" disabled>
                                    </div>
                                    <div class="form-group col-8">
                                        <label class="form-label">Observaciones</label>
                                        <input type="text" id="verObsAutorizado" class="form-control" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item" id="verEntregado">
                        <h2 class="accordion-header">
                            <button
                                type="button"
                                class="accordion-button collapsed"
                                data-bs-toggle="collapse"
                                data-bs-target="#acordionVerEntregado"
                                aria-expanded="false">
                                <i id="verEntregadoIcono" class="fa">&nbsp;</i>
                                Entrega
                            </button>
                        </h2>
                        <div id="acordionVerEntregado" class="accordion-collapse collapse" data-bs-parent="#acordionVer">
                            <div class="accordion-body">
                                <div class="row">
                                    <div class="form-group col-8">
                                        <label class="form-label">Entregado por</label>
                                        <input type="text" id="verEntregadoPor" class="form-control" disabled>
                                    </div>
                                    <div class="form-group col-4">
                                        <label class="form-label">Sucursal</label>
                                        <input type="text" id="verSucursalEntrega" class="form-control" disabled>
                                    </div>
                                    <div class="form-group col-4">
                                        <label class="form-label">Fecha</label>
                                        <input type="text" id="verFechaEntrega" class="form-control" disabled>
                                    </div>
                                    <div class="form-group col-4">
                                        <label class="form-label">Método de entrega</label>
                                        <input type="text" id="verMetodoEntrega" class="form-control" disabled>
                                    </div>
                                    <div class="form-group col-4">
                                        <label class="form-label">Monto entregado</label>
                                        <input type="text" id="verMontoEntregado" class="form-control" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item" id="verComprobantes">
                        <h2 class="accordion-header">
                            <button
                                type="button"
                                class="accordion-button collapsed"
                                data-bs-toggle="collapse"
                                data-bs-target="#acordionVerComprobantes"
                                aria-expanded="false">
                                <i class="fa fa-receipt text-primary verComprobacionIcono">&nbsp;</i>
                                Comprobación
                            </button>
                        </h2>
                        <div id="acordionVerComprobantes" class="accordion-collapse collapse" data-bs-parent="#acordionVer">
                            <div class="accordion-body">
                                <div class="row">
                                    <div class="form-group col-4">
                                        <label class="form-label">Fecha limite para registro</label>
                                        <input type="text" id="verFechaLimite" class="form-control" disabled>
                                    </div>
                                    <div class="form-group col-4">
                                        <label class="form-label">Tiempo restante</label>
                                        <input type="text" id="verTiempoRestante" class="form-control" disabled>
                                    </div>
                                    <div class="form-group col-4">
                                        <label class="form-label">Monto comprobado</label>
                                        <input type="text" id="verMontoComprobado" class="form-control" disabled>
                                    </div>
                                </div>
                                <div style="height: 1rem;"></div>
                                <div class="table-responsive text-nowrap">
                                    <table id="tablaComprobantesSolicitud" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th class="d-none">id</th>
                                                <th></th>
                                                <th>Fecha Registro</th>
                                                <th>Concepto</th>
                                                <th>Monto</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbodyVerComprobantesSolicitud">
                                        </tbody>
                                        <tfoot id="tfootVerComprobantesSolicitud">
                                            <tr>
                                                <td colspan="5">
                                                    <div class="d-flex text-center justify-content-between">
                                                        <button type="button" id="btnFinalizarComprobacion" class="btn btn-primary btn-sm">
                                                            <i class="fa-solid fa-flag-checkered">&nbsp;</i>Finalizar Comprobación
                                                        </button>
                                                        <button type="button" id="btnCapturaComprobanteViaticos" class="btn btn-success btn-sm">
                                                            <i class="fa fa-plus">&nbsp;</i>Agregar
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-evenly align-items-center fw-bold">
                                    <span class="d-flex align-items-center">
                                        <i class="badge badge-dot bg-danger"></i>&nbsp;Rechazado
                                    </span>
                                    <span class="d-flex align-items-center">
                                        <i class="badge badge-dot bg-warning"></i>&nbsp;Capturado
                                    </span>
                                    <span class="d-flex align-items-center">
                                        <i class="badge badge-dot bg-success"></i>&nbsp;Aceptado
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btnCerrarVer" data-bs-dismiss="modal" aria-label="Close">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<!-- / Modal para ver solicitud -->