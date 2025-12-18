<div class="content-wrapper">

    <!-- Filtros -->

        <!-- Users List Table -->
        <div class="card">

            <!-- Filtros -->
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">Filtros de búsqueda</h5>

                <div class="row pt-4 g-6">
                    <div class="col-md-4">
                        <select id="UserRole" class="form-select text-capitalize">
                            <option value="">Selecciona Departamento</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <select id="UserPlan" class="form-select text-capitalize">
                            <option value="">Selecciona Puesto</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <select id="FilterTransaction" class="form-select text-capitalize">
                            <option value="">Selecciona Estatus</option>
                        </select>
                    </div>
                </div>
            </div>


            <div class="row justify-content-between m-4">

                <div class="col-8">

                </div>

                <div class="col-4 d-flex align-items-end justify-content-end">

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

                        <div class="col-md-5" style="display: none;">
                            <div class="mb-2">
                                <label class="form-label">Número de Empleado *</label>
                                <input type="text" id="add_num_telefono" class="form-control phone-mask" placeholder="Ej. 5500">
                            </div>
                        </div>

                    <div class="mb-2">
                        <label class="form-label">Nombres *</label>
                        <input type="text" id="add_nombres" class="form-control" placeholder="Ej. Alberto">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Apellido Paterno *</label>
                        <input type="text" id="add_apellidop" class="form-control" placeholder="Ej. Aguilar">
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Apellido Materno *</label>
                        <input type="text" id="add_apellidom" class="form-control" placeholder="Ej. Soto">
                    </div>


                    <div class="mb-2">
                        <label class="form-label">Telefono *</label>
                        <input type="text" id="add_telefono" class="form-control phone-mask" placeholder="Ej. 5500000000">
                    </div>

                    <div class="mb-2">
                        <label for="add_departamento_id" class="form-label">Departamento *</label>
                        <select id="add_departamento_id" name="add_departamento_id" class="form-select">
                            <option value="">Seleccione un departamento</option>
                            <?php foreach ($departamento['datos'] as $dep): ?>
                                <option value="<?= htmlspecialchars($dep['id']) ?>">
                                    <?= htmlspecialchars($dep['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label for="add_id_puesto" class="form-label">Puestos *</label>
                        <select id="add_id_puesto" name="add_id_puesto" class="form-select">
                            <option value="">Seleccione un puesto</option>
                            <?php foreach ($puestos['datos'] as $dep): ?>
                                <option value="<?= htmlspecialchars($dep['id']) ?>">
                                    <?= htmlspecialchars($dep['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-6">
                        <label for="add_id_jefe" class="form-label">Jefe *</label>
                        <select id="add_id_jefe" name="add_id_jefe" class="form-select">
                            <option value="">Seleccione un jefe</option>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Usuario *</label>
                        <input type="text" id="add_usuario" class="form-control" placeholder="Ej. Soto">
                    </div>


                    <div class="mb-7">
                        <label class="form-label">Contraseña *</label>
                        <input type="text" id="add_contrasena" class="form-control phone-mask" placeholder="Ej. 5500000000">
                    </div>

                    <button type="submit" class="btn btn-primary me-3 data-submit" onclick="guardarGestor()">Guardar</button>

                    <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">Cancelar</button>

                </form>
            </div>
        </div>

        <div class="offcanvas offcanvas-end" id="offcanvasEditUser">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title">Registrar Nuevo Gestor</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>

        <div class="offcanvas-body p-6">

            <form id="editNewUserForm" onsubmit="return false">

                <div class="col-md-5">
                    <div class="mb-2">
                        <label class="form-label">Número de Empleado *</label>
                        <input type="text" id="edit_num_empleado" class="form-control phone-mask" placeholder="Ej. 5500" disabled>
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label">Nombres *</label>
                    <input type="text" id="edit_nombres" class="form-control" placeholder="Ej. Alberto">
                </div>
                <div class="mb-2">
                    <label class="form-label">Apellido Paterno *</label>
                    <input type="text" id="edit_apellidop" class="form-control" placeholder="Ej. Aguilar">
                </div>

                <div class="mb-2">
                    <label class="form-label">Apellido Materno *</label>
                    <input type="text" id="edit_apellidom" class="form-control" placeholder="Ej. Soto">
                </div>


                <div class="mb-2">
                    <label class="form-label">Telefono *</label>
                    <input type="text" id="edit_telefono" class="form-control phone-mask" placeholder="Ej. 5500000000">
                </div>

                <div class="mb-2">
                    <label for="edit_departamento_id" class="form-label">Departamento *</label>
                    <select id="edit_departamento_id" name="edit_departamento_id" class="form-select">
                        <option value="">Seleccione un departamento</option>
                    </select>
                </div>

                <div class="mb-2">
                    <label for="edit_id_puesto" class="form-label">Puestos *</label>
                    <select id="edit_id_puesto" name="edit_id_puesto" class="form-select">
                        <option value="">Seleccione un puesto</option>

                    </select>
                </div>

                <div class="mb-6">
                    <label for="edit_id_jefe" class="form-label">Jefe *</label>
                    <select id="edit_id_jefe" name="edit_id_jefe" class="form-select">
                        <option value="">Seleccione un jefe</option>
                    </select>
                </div>

                <div class="mb-2">
                    <label class="form-label">Usuario *</label>
                    <input type="text" id="edit_usuario" class="form-control" placeholder="Ej. Soto" readonly>
                </div>


                <div class="mb-7">
                    <label class="form-label">Contraseña *</label>
                    <input type="text" id="edit_contrasena" class="form-control phone-mask" placeholder="Ej. 5500000000">
                </div>

                <button type="submit" class="btn btn-primary me-3 data-submit" onclick="UpdateGestor()">Guardar</button>

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
                    <p id="gestor"><strong>Gestor:</strong> </p>

                    <div class="mb-3" style="display: none;">
                        <label for="id" class="form-label"><strong>Id</strong></label>
                    </div>

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
                        <!-- ID oculto -->
                        <input type="hidden" id="edit_id">

                        <div class="row g-3">
                            <!-- Nombres -->
                            <div class="col-md-4">
                                <label class="form-label">Nombres *</label>
                                <input type="text" id="edit_nombres" class="form-control" placeholder="Ej. Alberto">
                            </div>

                            <!-- Apellido Paterno -->
                            <div class="col-md-4">
                                <label class="form-label">Apellido Paterno *</label>
                                <input type="text" id="edit_apellidop" class="form-control" placeholder="Ej. Aguilar">
                            </div>

                            <!-- Apellido Materno -->
                            <div class="col-md-4">
                                <label class="form-label">Apellido Materno *</label>
                                <input type="text" id="edit_apellidom" class="form-control" placeholder="Ej. Soto">
                            </div>

                            <!-- Teléfono -->
                            <div class="col-md-4">
                                <label class="form-label">Teléfono *</label>
                                <input type="text" id="edit_telefono" class="form-control phone-mask" placeholder="Ej. 5500000000">
                            </div>

                            <!-- Departamento -->
                            <div class="col-md-4">
                                <label class="form-label">Departamento *</label>
                                <input type="text" id="edit_departamento" class="form-control" maxlength="500" disabled>
                            </div>

                            <!-- Jefe -->
                            <div class="col-md-4">
                                <label class="form-label">Jefe *</label>
                                <input type="text" id="edit_jefe" class="form-control" maxlength="500" disabled>
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

        let tabla = $('.datatables-users').DataTable({
            responsive: true,
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
            },

            /* =====================================
               🔑 AQUÍ ESTÁ LA CLAVE
            ===================================== */
            initComplete: function () {

                const filtroDepartamento = document.getElementById("UserRole");
                const filtroPuesto       = document.getElementById("UserPlan");
                const filtroStatus       = document.getElementById("FilterTransaction");

                // Limpiar selects (deja solo la primera opción)
                function limpiarSelect(select) {
                    while (select.options.length > 1) {
                        select.remove(1);
                    }
                }

                limpiarSelect(filtroDepartamento);
                limpiarSelect(filtroPuesto);
                limpiarSelect(filtroStatus);

                // Llenar selects desde columnas (extraer TEXTO)
                function llenarSelect(colIndex, select) {
                    let valores = new Set();

                    tabla.column(colIndex).nodes().each(function (cell) {
                        let texto = cell.innerText.trim();
                        if (texto !== '') {
                            valores.add(texto);
                        }
                    });

                    [...valores].sort().forEach(valor => {
                        let option = document.createElement("option");
                        option.value = valor;
                        option.textContent = valor;
                        select.appendChild(option);
                    });
                }

                // Índices reales
                llenarSelect(1, filtroDepartamento); // Departamento
                llenarSelect(2, filtroPuesto);       // Puesto
                llenarSelect(3, filtroStatus);       // Estatus

                // Aplicar filtros
                function aplicarFiltros() {
                    tabla
                        .column(1).search(
                        filtroDepartamento.value ? '^' + filtroDepartamento.value + '$' : '',
                        true, false
                    )
                        .column(2).search(
                        filtroPuesto.value ? '^' + filtroPuesto.value + '$' : '',
                        true, false
                    )
                        .column(3).search(
                        filtroStatus.value ? '^' + filtroStatus.value + '$' : '',
                        true, false
                    )
                        .draw();
                }

                filtroDepartamento.addEventListener("change", aplicarFiltros);
                filtroPuesto.addEventListener("change", aplicarFiltros);
                filtroStatus.addEventListener("change", aplicarFiltros);
            }
        });

    });
</script>




