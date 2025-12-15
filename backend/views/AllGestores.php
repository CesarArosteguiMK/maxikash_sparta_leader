<div class="content-wrapper">
        <!-- Users List Table -->
        <div class="card">

            <!-- Filtros -->
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">Filtros de búsqueda</h5>

                <div class="row pt-4 g-6">
                    <div class="col-md-4">
                        <select id="UserRole" class="form-select text-capitalize">
                            <option value="">Selecciona Puesto</option>
                            <option value="Admin">Admin</option>
                            <option value="Author">Author</option>
                            <option value="Editor">Editor</option>
                            <option value="Maintainer">Maintainer</option>
                            <option value="Subscriber">Subscriber</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <select id="UserPlan" class="form-select text-capitalize">
                            <option value="">Select Plan</option>
                            <option value="Basic">Basic</option>
                            <option value="Company">Company</option>
                            <option value="Enterprise">Enterprise</option>
                            <option value="Team">Team</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <select id="FilterTransaction" class="form-select text-capitalize">
                            <option value="">Select Status</option>
                            <option value="Pending">Pending</option>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mb-3">
                <button
                        type="button"
                        class="btn btn-primary add-new"
                        data-bs-toggle="offcanvas"
                        data-bs-target="#offcanvasAddUser"
                >
                    <i class="icon-base bx bx-plus icon-sm me-2"></i>
                    <span class="d-none d-sm-inline-block">Agregar Gestor</span>
                </button>
            </div>

            <!-- DataTable -->
            <div class="card-datatable p-2">
                <!-- Aquí inicia tu tabla limpia -->
                <table class="table datatables-users border-top">
                    <thead class="table-dark">
                    <tr>
                        <th>Nombre Completo</th>
                        <th>Departamento</th>
                        <th>Puesto</th>
                        <th>Estatus</th>
                        <th>Acciones</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?= $tablaGestores ?>
                    </tbody>
                </table>
                <!-- Fin tabla -->
            </div>
        </div>

        <!-- Offcanvas Add User -->
        <div class="offcanvas offcanvas-end" id="offcanvasAddUser">
            <div class="offcanvas-header border-bottom">
                <h5 class="offcanvas-title">Registrar Nuevo Gestor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
            </div>

            <div class="offcanvas-body p-6">

                <form id="addNewUserForm" onsubmit="return false">

                    <div class="mb-6">
                        <label class="form-label">Nombres *</label>
                        <input type="text" class="form-control" placeholder="Ej. Alberto">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Apellido Paterno *</label>
                        <input type="text" class="form-control" placeholder="Ej. Aguilar">
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Apellido Materno *</label>
                        <input type="text" class="form-control" placeholder="Ej. Soto">
                    </div>


                    <div class="mb-2">
                        <label class="form-label">Telefono *</label>
                        <input type="text" class="form-control phone-mask" placeholder="Ej. 5500000000">
                    </div>

                    <div class="mb-2">
                        <label for="proyecto" class="form-label">Departamento *</label>
                        <input type="text" id="proyecto" name="proyecto" class="form-control" placeholder="" maxlength="500" disabled>
                    </div>

                    <div class="mb-6">
                        <label for="proyecto" class="form-label">Jefe *</label>
                        <input type="text" id="proyecto" name="proyecto" class="form-control" placeholder="" maxlength="500" disabled>
                    </div>

                    <button type="submit" class="btn btn-primary me-3 data-submit">Guardar</button>

                    <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">Cancelar</button>

                </form>
            </div>
        </div>

        <!-- Modal RFC -->
        <div class="modal fade" id="modalRFC" tabindex="-1" aria-labelledby="modalRFCLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalRFCLabel">Dar de Baja al Gestor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <p><strong>Gestor:</strong> <?= htmlspecialchars($dataCliente["rfc"] ?? '—') ?></p>

                    <!-- 🆕 Motivo de baja -->
                    <div class="mb-3">
                        <label for="motivoBaja" class="form-label"><strong>Motivo de baja</strong></label>
                        <textarea class="form-control" id="motivoBaja" rows="3" placeholder="Escribe el motivo..."></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>

                    <!-- 🆕 Botón para confirmar baja -->
                    <button type="button" class="btn btn-danger" onclick="confirmarBaja()">
                        Confirmar Baja
                    </button>
                </div>
            </div>
        </div>
    </div>

        <div class="modal fade" id="modalPersonaEdita" tabindex="-1" aria-labelledby="modalAddUserLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAddUserLabel">Editar Gestor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <form id="addNewUserForm" onsubmit="return false">
                        <div class="row g-3">
                            <!-- Nombres -->
                            <div class="col-md-4">
                                <label class="form-label">Nombres *</label>
                                <input type="text" class="form-control" placeholder="Ej. Alberto">
                            </div>
                            <!-- Apellido Paterno -->
                            <div class="col-md-4">
                                <label class="form-label">Apellido Paterno *</label>
                                <input type="text" class="form-control" placeholder="Ej. Aguilar">
                            </div>
                            <!-- Apellido Materno -->
                            <div class="col-md-4">
                                <label class="form-label">Apellido Materno *</label>
                                <input type="text" class="form-control" placeholder="Ej. Soto">
                            </div>
                            <!-- Teléfono -->
                            <div class="col-md-4">
                                <label class="form-label">Teléfono *</label>
                                <input type="text" class="form-control phone-mask" placeholder="Ej. 5500000000">
                            </div>
                            <!-- Departamento -->
                            <div class="col-md-4">
                                <label class="form-label">Departamento *</label>
                                <input type="text" class="form-control" placeholder="" maxlength="500" disabled>
                            </div>
                            <!-- Jefe -->
                            <div class="col-md-4">
                                <label class="form-label">Jefe *</label>
                                <input type="text" class="form-control" placeholder="" maxlength="500" disabled>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="modal-footer">
                    <button type="reset" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" onclick="guardarGestor()">Guardar</button>
                </div>
            </div>
        </div>
    </div>
    </div>

</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {

        // Inicializar DataTable
        let tabla = $('.datatables-users').DataTable({
            responsive: true,
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
            }
        });

        // --- Filtros ---
        const filtroDepartamento = document.getElementById("UserRole");
        const filtroPuesto = document.getElementById("UserPlan");
        const filtroStatus = document.getElementById("FilterTransaction");

        function aplicarFiltros() {
            let departamento = filtroDepartamento.value.toLowerCase();
            let puesto = filtroPuesto.value.toLowerCase();
            let status = filtroStatus.value.toLowerCase();

            tabla
                .column(1).search(departamento) // Departamento
                .column(2).search(puesto)       // Puesto
                .column(3).search(status)       // Estatus
                .draw();
        }

        filtroDepartamento.addEventListener("change", aplicarFiltros);
        filtroPuesto.addEventListener("change", aplicarFiltros);
        filtroStatus.addEventListener("change", aplicarFiltros);

        // Reset al cargar
        //aplicarFiltros();

        // --- Pie de página automático ---
        const currentYear = new Date().getFullYear();
        const footer = document.querySelector("footer .footer-text");

        if (footer) {
            footer.innerHTML = `© ${currentYear} Todos los derechos reservados`;
        }

    });

</script>

