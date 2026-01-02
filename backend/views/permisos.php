<div class="content-wrapper">
    <div class="container-fluid container-p-y">

        <h4 class="mb-4">Permisos por Puesto</h4>
        <p class="mb-4 text-muted">
            Asignación de puestos a los que cada usuario puede acceder.
        </p>

        <div class="card">
            <div class="card-datatable table-responsive">
                <table id="tablaPermisos" class="table table-bordered">
                    <thead class="table-light">
                    <tr>
                        <th></th> <!-- columna control responsive -->
                        <th>Persona</th>
                        <th>Puestos asignados</th>
                        <th width="120">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td colspan="3" class="text-center">
                            Cargando información...
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- MODAL -->
<div class="modal fade" id="modalPermisos" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Editar permisos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="formPermisos">
                <div class="modal-body">

                    <input type="hidden" name="idPersona" id="idPersona">

                    <div class="mb-3">
                        <label class="form-label">Persona</label>
                        <input type="text" id="nombrePersona" class="form-control" readonly>
                    </div>

                    <hr>

                    <h6>Puestos disponibles</h6>
                    <div id="contenedor-puestos" class="row"></div>

                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        Guardar cambios
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
