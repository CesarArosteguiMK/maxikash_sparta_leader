<style>
    #chart-container {
        width: 100%;
        max-height: 600px;
        overflow: auto;
        border: 1px solid #ccc;
        padding: 10px;
    }

    #chart {
        transform-origin: top left; /* El zoom se hace desde la esquina superior izquierda */
        transition: transform 0.2s;  /* Suaviza el zoom */
    }
</style>

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

        <!-- Organigrama -->
        <div id="resultado" class="mt-4"></div>

        <!-- Organigrama -->
        <div class="zoom-controls">
            <button id="zoom-out">-</button>
            <button id="zoom-in">+</button>
        </div>

        <div id="chart-container" class="mt-4">
            <div id="chart"></div>
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

                <div class="mb-2" style="display: none !important;">
                    <label class="form-label">Id Empleado *</label>
                    <input ype="text" id="edit_id" class="form-control phone-mask">
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
    document.addEventListener("DOMContentLoaded", function () {

        function cargarDepartamentosPorTipo(id, seleccionado = null) {
            fetch('/CapHum/getDepartamento', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            })
                .then(res => res.json())
                .then(data => {

                    if (!data.success) {
                        Swal.fire("Error", data.mensaje, "error");
                        return;
                    }

                    const select = document.getElementById('edit_departamento_id');
                    select.innerHTML = '<option value="">Seleccione un departamento</option>';

                    data.datos.forEach(dep => {
                        const option = document.createElement('option');
                        option.value = dep.id;
                        option.textContent = dep.nombre;

                        if (String(dep.id) === String(seleccionado)) {
                            option.selected = true;
                        }

                        select.appendChild(option);
                    });
                });
        }
        function cargarPuestosPorDepartamentos(id_departamento, seleccionado = null) {
            fetch('/CapHum/getPuestosDepartamento', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_departamento })
            })
                .then(res => res.json())
                .then(data => {

                    if (!data.success) {
                        Swal.fire("Error", data.mensaje, "error");
                        return;
                    }

                    const select = document.getElementById('edit_id_puesto');
                    select.innerHTML = '<option value="">Seleccione un puesto</option>';

                    data.datos.forEach(puesto => {
                        const option = document.createElement('option');
                        option.value = puesto.id;
                        option.textContent = puesto.nombre;

                        if (String(puesto.id) === String(seleccionado)) {
                            option.selected = true;
                        }

                        select.appendChild(option);
                    });
                });
        }
        function cargarJefeDirecto(id_departamento, seleccionado = null) {
            fetch('/CapHum/getJefeDirecto',
                {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id_departamento })
                })
                .then(res => res.json())
                .then(data => {

                    if (!data.success) {
                        Swal.fire("Error", data.mensaje, "error");
                        return;
                    }

                    const select = $('#edit_id_jefe');
                    select.empty().append(new Option('Seleccione un jefe', '', false, false));

                    data.datos.forEach(jefe => {
                        select.append(new Option(
                            jefe.nombre_completo,
                            jefe.id,
                            false,
                            String(jefe.id) === String(seleccionado)
                        ));
                    });

                    select.trigger('change'); // 🔥 clave para Select2
                });
        }

        /* ============================= */
        /*   SELECT DEPARTAMENTO          */
        /* ============================= */
        document.getElementById("depSelect").addEventListener("change", function () {
            const dep_id = this.value;
            const personaSelect = document.getElementById("personaSelect");

            personaSelect.innerHTML = "<option>Cargando...</option>";
            personaSelect.disabled = true;

            document.getElementById("resultado").innerHTML = "";
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
            const persona_id = this.value;
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
        window.abrirModal = function (id) {
            if (!id) {
                Swal.fire("Error", "ID inválido", "error");
                return;
            }

            fetch('/CapHum/getDetalles', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ idPersona: id })
            })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        Swal.fire("Error", data.mensaje, "error");
                        return;
                    }

                    const persona = data.datos;

                    cargarDepartamentosPorTipo(persona.id_departamento, persona.id_departamento);
                    cargarPuestosPorDepartamentos(persona.id_departamento, persona.id_puesto);
                    cargarJefeDirecto(persona.id_departamento, persona.id_jefe);

                    document.getElementById("edit_num_empleado").value = persona.numero_empleado ?? '';
                    document.getElementById("edit_id").value = persona.id ?? '';
                    document.getElementById("edit_nombres").value = persona.nombres ?? '';
                    document.getElementById("edit_apellidop").value = persona.apellidop ?? '';
                    document.getElementById("edit_apellidom").value = persona.apellidom ?? '';
                    document.getElementById("edit_telefono").value = persona.telefono ?? '';
                    document.getElementById("edit_usuario").value = persona.user_name ?? '';
                    document.getElementById("edit_contrasena").value = persona.password ?? '';
                    document.getElementById("edit_departamento_id").value = persona.id_departamento ?? '';
                    document.getElementById("edit_id_puesto").value = persona.id_puesto ?? '';

                    const offcanvas = new bootstrap.Offcanvas(
                        document.getElementById('offcanvasEditUser')
                    );
                    offcanvas.show();
                })
                .catch(err => {
                    console.error('FETCH ERROR:', err);
                    Swal.fire("Error", "No se pudo cargar la información", "error");
                });
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
                        v: String(r.id),
                        f: `
                        <div style="font-weight:bold;cursor:pointer;color:#2a6ebb"
                             onclick="abrirModal('${r.id}')">
                            ${r.nombre}
                        </div>`,
                        p: { collapsed: r.jefe !== null }
                    },
                    r.jefe
                ]);
            });

            const chart = new google.visualization.OrgChart(container);
            chart.draw(data, {
                allowHtml: true,
                allowCollapse: true
            });

            google.visualization.events.addListener(chart, 'ready', () => {
                ajustarEscalaChart();
            });
        }

        /* ============================= */
        /*   ESCALA Y SCROLL             */
        /* ============================= */
        function ajustarEscalaChart() {
            const container = document.getElementById("chart-container");
            const chartDiv = document.getElementById("chart");

            chartDiv.style.transform = 'scale(1)'; // reset

            const anchoChart = chartDiv.scrollWidth;
            const anchoContenedor = container.clientWidth;

            let escala = 1;
            if (anchoChart > anchoContenedor) {
                escala = anchoContenedor / anchoChart;
                escala = Math.max(escala, 0.2); // no bajar del 50%
            }

            chartDiv.style.transform = `scale(${escala})`;
            chartDiv.style.transformOrigin = 'top left';
        }

        window.addEventListener('resize', ajustarEscalaChart);

    });

    let scale = 1; // escala inicial
    const chart = document.getElementById('chart');
    const STEP = 0.01; // paso más pequeño

    document.getElementById('zoom-in').addEventListener('click', () => {
        scale += STEP;
        chart.style.transform = `scale(${scale})`;
    });

    document.getElementById('zoom-out').addEventListener('click', () => {
        scale = Math.max(0.1, scale - STEP);
        chart.style.transform = `scale(${scale})`;
    });





</script>


