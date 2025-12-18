
<h4 class="mb-4">Organigrama por Departamento</h4>

<div class="card">
    <div class="card-body">
        <!-- Filtros -->
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <label for="depSelect" class="form-label"><strong>Selecciona un departamento:</strong></label>
                <select id="depSelect" class="form-select">
                    <?php echo $Departamentos; ?>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="personaSelect" class="form-label"><strong>Selecciona persona (máximo rango):</strong></label>
                <select id="personaSelect" class="form-select" disabled>
                    <option value="">-- Selecciona un departamento primero --</option>
                </select>
            </div>
        </div>

        <!-- Resumen por puesto -->
        <div id="countPuestos" class="mt-4"></div>

        <!-- Organigrama -->
        <div id="resultado" class="mt-4"></div>

        <!-- Organigrama -->
        <div id="chart" class="mt-4"></div>

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


<script>
    document.getElementById("depSelect").addEventListener("change", function () {
        let dep_id = this.value;
        let personaSelect = document.getElementById("personaSelect");

        personaSelect.innerHTML = "<option>Cargando...</option>";
        personaSelect.disabled = true;

        document.getElementById("resultado").innerHTML = "";
        document.getElementById("countPuestos").innerHTML = "";
        document.getElementById("chart").innerHTML = "";

        if (!dep_id) return;

        consultaServidor(
            "/CapHum/getPersonasOrganigrama",
            { idDepartamento: dep_id },
            (respuesta) => {

                personaSelect.innerHTML = "";

                if (!respuesta.success) {
                    personaSelect.innerHTML = "<option>Error al cargar personas</option>";
                    personaSelect.disabled = true;
                    return;
                }

                const personas = Array.isArray(respuesta.datos)
                    ? respuesta.datos
                    : Object.values(respuesta.datos);

                if (!personas.length) {
                    personaSelect.innerHTML = "<option>No hay personas</option>";
                    personaSelect.disabled = true;
                    return;
                }

                personaSelect.innerHTML = '<option value="">Selecciona una persona</option>';
                personas.forEach(p => {
                    personaSelect.innerHTML += `<option value="${p.id}">${p.nombre}</option>`;
                });

                personaSelect.disabled = false;
            }
        );
    });

    /* ============================= */
    /*   SELECT PERSONA              */
    /* ============================= */
    document.getElementById("personaSelect").addEventListener("change", function () {
        let persona_id = this.value;
        if (!persona_id) return;

        fetch("/CapHum/nivelJerarquicoColaborador/" + persona_id)
            .then(res => res.json())
            .then(res => {
                if (!res.success) {
                    mostrarMensajeAll({
                        tipo: 'error',
                        titulo: 'Error',
                        mensaje: 'No se encontraron resultados'
                    });
                    return;
                }

                const chartContainer = document.getElementById("chart");
                chartContainer.innerHTML = "";

                loadGoogleCharts(() => {
                    drawOrgChart(res.rows, chartContainer);
                });
            });
    });

    /* ============================= */
    /*   MODAL / CANVAS EDITAR       */
    /* ============================= */
    window.abrirModal = function (valor) {
        console.log("Valor recibido:", valor);

        // Abrir el offcanvas
        const offcanvasEl = document.getElementById("offcanvasEditUser");
        if (!offcanvasEl) {
            console.error("El offcanvas #offcanvasEditUser no existe en el DOM");
            return;
        }
        const offcanvas = new bootstrap.Offcanvas(offcanvasEl);
        offcanvas.show();

    };


    /* ============================= */
    /*   ORGANIGRAMA                 */
    /* ============================= */
    function drawOrgChart(rows, container) {
        const data = new google.visualization.DataTable();
        data.addColumn("string", "Nombre");
        data.addColumn("string", "Jefe");

        rows.forEach(r => {
            data.addRow([
                {
                    v: String(r.id), // valor interno: ID
                    f: `
                <div style="font-weight:bold;cursor:pointer;color:#2a6ebb"
                     onclick="abrirModal('${r.id}')">
                    ${r.nombre}
                </div>`,
                    p: { collapsed: r.jefe !== null }
                },
                r.jefe // nombre del jefe
            ]);
        });

        const chart = new google.visualization.OrgChart(container);
        chart.draw(data, {
            allowHtml: true,
            allowCollapse: true
        });
    }

</script>


