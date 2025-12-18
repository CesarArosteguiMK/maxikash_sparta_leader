<style>
    #loaderTabla {
        text-align: center;
        padding: 40px;
    }
</style>
<div class="content-wrapper">

    <!-- =======================
         CARD PRINCIPAL
    ======================== -->
    <div class="card">

        <!-- =======================
             FILTROS
        ======================== -->
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
                        <option value="">Select Estatus</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- =======================
             BOTÓN AGREGAR
        ======================== -->
        <div class="row justify-content-between m-4">
            <div class="col-8"></div>

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

        <!-- =======================
             TABLA
        ======================== -->
        <div class="card-datatable p-2">
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
        </div>

    </div>

    <!-- =======================
         OFFCANVAS - AGREGAR
    ======================== -->
    <div class="offcanvas offcanvas-end" id="offcanvasAddUser">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title">Registrar Nuevo Gestor</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>

        <div class="offcanvas-body p-6">
            <form id="addNewUserForm" onsubmit="return false">

                <div class="col-md-5 d-none">
                    <div class="mb-2">
                        <label class="form-label">Número de Empleado *</label>
                        <input type="text" id="add_num_telefono" class="form-control phone-mask">
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label">Nombres *</label>
                    <input type="text" id="add_nombres" class="form-control">
                </div>

                <div class="mb-2">
                    <label class="form-label">Apellido Paterno *</label>
                    <input type="text" id="add_apellidop" class="form-control">
                </div>

                <div class="mb-2">
                    <label class="form-label">Apellido Materno *</label>
                    <input type="text" id="add_apellidom" class="form-control">
                </div>

                <div class="mb-2">
                    <label class="form-label">Teléfono *</label>
                    <input type="text" id="add_telefono" class="form-control phone-mask">
                </div>

                <div class="mb-2">
                    <label class="form-label">Departamento *</label>
                    <select id="add_departamento_id" class="form-select">
                        <option value="">Seleccione un departamento</option>
                        <?php foreach ($departamento['datos'] as $dep): ?>
                            <option value="<?= htmlspecialchars($dep['id']) ?>">
                                <?= htmlspecialchars($dep['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-2">
                    <label class="form-label">Puesto *</label>
                    <select id="add_id_puesto" class="form-select" disabled>
                        <option value="">Seleccione un puesto</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label class="form-label">Jefe *</label>
                    <select id="add_id_jefe" class="form-select" disabled>
                        <option value="">Seleccione un jefe</option>
                    </select>
                </div>

                <div class="mb-2">
                    <label class="form-label">Usuario *</label>
                    <input type="text" id="add_usuario" class="form-control">
                </div>

                <div class="mb-7">
                    <label class="form-label">Contraseña *</label>
                    <input type="text" id="add_contrasena" class="form-control">
                </div>

                <button class="btn btn-primary me-3" onclick="guardarGestor()">Guardar</button>
                <button class="btn btn-label-danger" data-bs-dismiss="offcanvas">Cancelar</button>

            </form>
        </div>
    </div>

    <!-- =======================
           OFFCANVAS -
      ======================== -->
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
    <!-- =======================
        OFFCANVAS - EDITAR
   ======================== -->
    <div class="offcanvas offcanvas-end" id="offcanvasEditUser">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title">Editar Gestor</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>

        <div class="offcanvas-body p-6">
            <form id="editNewUserForm" onsubmit="return false">

                <div class="mb-2" style="display: none">
                    <label class="form-label">Id Empleado *</label>
                    <input ype="text" id="edit_id" class="form-control phone-mask"disabled>
                </div>

                <div class="mb-2">
                    <label class="form-label">Número de Empleado *</label>
                    <input ype="text" id="edit_num_empleado" class="form-control phone-mask"disabled>
                </div>

                <div class="mb-2">
                    <label class="form-label">Nombres *</label>
                    <input type="text" id="edit_nombres" class="form-control">
                </div>

                <div class="mb-2">
                    <label class="form-label">Apellido Paterno *</label>
                    <input type="text" id="edit_apellidop" class="form-control">
                </div>

                <div class="mb-2">
                    <label class="form-label">Apellido Materno *</label>
                    <input type="text" id="edit_apellidom" class="form-control">
                </div>

                <div class="mb-2">
                    <label class="form-label">Teléfono *</label>
                    <input type="text" id="edit_telefono" class="form-control phone-mask">
                </div>

                <div class="mb-2">
                    <label class="form-label">Departamento *</label>
                    <select id="edit_departamento_id" class="form-select">
                        <option value="">Seleccione un departamento</option>
                    </select>
                </div>

                <div class="mb-2">
                    <label class="form-label">Puesto *</label>
                    <select id="edit_id_puesto" class="form-select">
                        <option value="">Seleccione un puesto</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label class="form-label">Jefe *</label>
                    <select id="edit_id_jefe" class="form-select">
                        <option value="">Seleccione un jefe</option>
                    </select>
                </div>

                <div class="mb-2">
                    <label class="form-label">Usuario *</label>
                    <input type="text" id="edit_usuario" class="form-control" readonly>
                </div>

                <div class="mb-7">
                    <label class="form-label">Contraseña *</label>
                    <input type="text" id="edit_contrasena" class="form-control">
                </div>

                <button type="button" class="btn btn-primary me-3" onclick="UpdateGestor()" > Guardar </button>
                <button type="button" class="btn btn-label-danger" data-bs-dismiss="offcanvas" > Cancelar </button>
            </form>
        </div>
    </div>

</div>

<!-- =========================
     JS
========================== -->

<script>
    document.addEventListener("DOMContentLoaded", function () {

        let tabla = $('.datatables-users').DataTable({
            responsive: true,
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
            },

            initComplete: function () {

                const filtroDepartamento = document.getElementById("UserRole");
                const filtroPuesto       = document.getElementById("UserPlan");
                const filtroStatus       = document.getElementById("FilterTransaction");

                function limpiarSelect(select, placeholder) {
                    select.innerHTML = `<option value="">${placeholder}</option>`;
                    select.disabled = true;
                }

                function llenarSelect(colIndex, select, placeholder) {
                    let valores = new Set();

                    tabla
                        .column(colIndex, { search: 'applied' })
                        .nodes()
                        .each(function (cell) {
                            let texto = cell.innerText.trim();
                            if (texto !== '') valores.add(texto);
                        });

                    limpiarSelect(select, placeholder);

                    [...valores].sort().forEach(valor => {
                        const option = document.createElement("option");
                        option.value = valor;
                        option.textContent = valor;
                        select.appendChild(option);
                    });

                    select.disabled = false;
                }

                // 🔹 Inicial
                llenarSelect(1, filtroDepartamento, 'Selecciona Departamento');
                limpiarSelect(filtroPuesto, 'Selecciona Puesto');
                limpiarSelect(filtroStatus, 'Selecciona Estatus');

                // 🔹 Departamento → Puesto
                filtroDepartamento.addEventListener("change", function () {

                    tabla
                        .column(1)
                        .search(this.value ? '^' + this.value + '$' : '', true, false)
                        .draw();

                    if (this.value) {
                        llenarSelect(2, filtroPuesto, 'Selecciona Puesto');
                    } else {
                        limpiarSelect(filtroPuesto, 'Selecciona Puesto');
                    }

                    limpiarSelect(filtroStatus, 'Selecciona Estatus');
                });

                // 🔹 Puesto → Estatus
                filtroPuesto.addEventListener("change", function () {

                    tabla
                        .column(2)
                        .search(this.value ? '^' + this.value + '$' : '', true, false)
                        .draw();

                    if (this.value) {
                        llenarSelect(3, filtroStatus, 'Selecciona Estatus');
                    } else {
                        limpiarSelect(filtroStatus, 'Selecciona Estatus');
                    }
                });

                // 🔹 Estatus
                filtroStatus.addEventListener("change", function () {
                    tabla
                        .column(3)
                        .search(this.value ? '^' + this.value + '$' : '', true, false)
                        .draw();
                });
            }
        });
    });
</script>

