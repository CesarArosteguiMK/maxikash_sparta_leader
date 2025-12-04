<h4>Departamentos Registrados</h4>

<div id="resumenSolicitudes" class="row mb-5 g-2"></div>

<div class="card">
    <div class="row justify-content-between m-4">
        <div class="col-4"></div>
        <div class="col-4 d-flex align-self-end justify-content-end">
            <button id="btnAgregar" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#modalNuevaSolicitud"><i class="fa fa-plus">&nbsp;</i>Nuevo Departamento</button>
        </div>
    </div>

    <div class="card-datatable table-responsive">
        <table id="historialSolicitudes" class="dt-responsive table border-top">
            <thead>
                <tr>
                    <th></th>
                    <th>Puesto</th>
                    <th>Departamento</th>
                    <th>Estatus</th>
                    <th>Accciones</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal para agregar departamento -->
<div class="modal fade" id="modalNuevaSolicitud" tabindex="-1" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="text-center w-100">
                    <h4 class="address-title mb-2">Registrar Nuevo Departamento</h4>
                    <p class="address-subtitle">Capture los datos solicitados</p>
                </div>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="form-group col-6">
                        <label for="nombre" class="form-label">Nombre del Departamento</label>
                        <input type="input" id="nombre" name="nombre" class="form-control" placeholder="Ej.: CallCenter" maxlength="500">
                        <div class="fv-message text-danger small" style="min-height: 1.25rem"></div>
                    </div>

                    <div class="form-group col-6">
                        <label for="descripcion" class="form-label">Descripción del Departamento</label>
                        <input type="text" id="descripcion" name="descripcion" class="form-control" placeholder="Ej.: CallCenter centro de llamadas cobranza" maxlength="500">
                        <div class="fv-message text-danger small" style="min-height: 1.25rem"></div>
                    </div>
                </div>
                <div class="form-group col-4">
                    <label for="tipoSolicitud" class="form-label">Estatus</label>
                    <select class="form-select" id="estatus" name="estatus">
                        <option value="1">Activo</option>
                    </select>
                    <div class="fv-message text-danger small" style="min-height: 1.25rem"></div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" id="cancelaSolicitud" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">Cancelar</button>
                <button type="button" id="registraSolicitud" class="btn btn-primary">Guardar</button>
            </div>
        </div>
    </div>
</div>
