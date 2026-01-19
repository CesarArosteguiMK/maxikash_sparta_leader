<style>
    /* ===== INLINE EDIT LIMPIO ===== */
    [contenteditable="true"] {
        outline: none;                 /* quita línea negra */
        border-radius: 6px;
        padding: 2px 6px;              /* aire */
        background-color: #f4f5ff;     /* fondo suave */
        box-shadow: inset 0 0 0 1px #696cff40;
        transition: all 0.15s ease;
    }

    /* Hover sutil */
    [contenteditable="true"]:hover {
        background-color: #eef0ff;
    }

    /* Focus elegante */
    [contenteditable="true"]:focus {
        background-color: #ffffff;
        box-shadow: 0 0 0 2px #696cff50;
    }

    /* Evita salto de altura */
    .puesto-nombre {
        min-height: 1.5rem;
    }

    /* ===== PUESTOS ===== */
    .puesto-item .editar-puesto {
        opacity: 0;
        cursor: pointer;
        color: #6c757d;
        font-size: 0.85rem;
        transition: all 0.2s ease;
    }

    .puesto-item:hover .editar-puesto {
        opacity: 1;
    }

    .puesto-item .editar-puesto:hover {
        color: #696cff;
        transform: scale(1.2);
    }

    /* ===== TÍTULO (SIEMPRE VISIBLE) ===== */
    .editar-titulo {
        color: #6c757d;
        font-size: 0.85rem;
        cursor: pointer;
    }

    .editar-titulo:hover {
        color: #696cff;
        transform: scale(1.15);
    }

</style>


<h4>Departamentos Registrados</h4>

    <p class="mb-6">A role provided access to predefined menus and features so that depending on assigned role an administrator can have access to what user needs.</p>
    <!-- Role cards -->

    <div id="departamentosCards" class="row g-7"></div>


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

    <!-- Modal Detalle Departamento -->
    <div class="modal fade" id="modalDetalleDepartamento" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <!-- Header -->
                <div class="modal-header align-items-start">
                    <div class="w-100 text-center">

                        <!-- Título editable -->
                        <div class="d-flex justify-content-center align-items-center gap-2">
                            <h4 class="mb-1"
                                id="tituloDepartamento"
                                contenteditable="true"
                                onblur="guardarTituloDepartamento()">
                                Call Center
                            </h4>
                            <i class="fa-solid fa-pen editar-puesto"></i>
                        </div>

                        <input type="hidden"
                               id="id_departamento"
                               class="form-control"
                               placeholder="dep">


                        <p class="text-muted mb-0">
                            Puestos registrados en el departamento
                        </p>
                    </div>

                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <!-- Body -->
                <div class="modal-body">
                    <div class="card">
                        <div class="card-body">

                            <!-- Header lista -->
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h6 class="mb-0 fw-semibold">Listado de puestos</h6>
                                <button class="btn btn-sm btn-outline-primary"
                                        onclick="mostrarInputNuevoPuesto()">
                                    <i class="fa fa-plus-circle"> </i>&emsp;Nuevo puesto
                                </button>
                            </div>

                            <!-- Input nuevo puesto -->
                            <div id="nuevoPuestoContainer" class="d-none mb-4">
                                <div class="input-group input-group-sm">
                                    <input type="text"
                                           id="inputNuevoPuesto"
                                           class="form-control"
                                           placeholder="Nombre del puesto">
                                    <button class="btn btn-primary"
                                            onclick="guardarNuevoPuesto()">
                                        <i class="fa fa-save"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Lista de puestos -->
                            <ul class="p-0 m-0" id="listaPuestos">

                                <!-- Puesto -->
                                <li class="d-flex mb-4 align-items-center puesto-item">
                                    <div class="avatar flex-shrink-0 me-4">
                  <span class="avatar-initial rounded bg-label-primary">
                    <i class="fa fa-phone"></i>
                  </span>
                                    </div>

                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-2">
                                            <h6 class="mb-0 fw-normal puesto-nombre"
                                                contenteditable="false">
                                                Asesor Telefónico
                                            </h6>
                                            <i class="fa fa-pencil editar-puesto"
                                               onclick="editarPuesto(this)"></i>
                                        </div>
                                        <p class="text-muted mb-0">
                                            Atención y seguimiento a clientes
                                        </p>
                                    </div>
                                </li>

                                <!-- Puesto -->
                                <li class="d-flex mb-4 align-items-center puesto-item">
                                    <div class="avatar flex-shrink-0 me-4">
                  <span class="avatar-initial rounded bg-label-success">
                    <i class="fa fa-user icon-lg"></i>
                  </span>
                                    </div>

                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-2">
                                            <h6 class="mb-0 fw-normal puesto-nombre"
                                                contenteditable="false">
                                                Supervisor de Call Center
                                            </h6>
                                            <i class="fa fa-pencil editar-puesto"
                                               onclick="editarPuesto(this)"></i>
                                        </div>
                                        <p class="text-muted mb-0">
                                            Monitoreo y control de calidad
                                        </p>
                                    </div>
                                </li>

                            </ul>

                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="modal-footer d-flex justify-content-between">
                    <small class="text-muted">
                        * Edición inline, cambios listos para persistir
                    </small>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cerrar
                    </button>
                </div>

            </div>
        </div>
    </div>


