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

    /* Texto dentro de los nodos */
    .google-visualization-orgchart-node div {
        font-size: 11px;   /* prueba 10px–12px */
        line-height: 1;
        white-space: normal;
        word-break: keep-all;   /* 👈 CLAVE */
        overflow-wrap: normal;  /* 👈 CLAVE */
    }


    .org-node {
        cursor: pointer;
        text-align: center;
    }

    .org-nombre {
        font-weight: bold;
        color: #2a6ebb;
        font-size: 6px;
        line-height: 1.2;

    }

    .org-puesto {
        font-size: 5px;          /* 👈 SMALL */
        color: #555;
        line-height: 1.1;
        margin-top: 1px;
        font-style: italic;     /* opcional */
        white-space: normal;
        word-break: keep-all;
        overflow-wrap: normal;   /* 👈 NO break-word aquí */
    }

    /* Selects: departamento corto, persona y niveles un poco más largos para nombres largos */
    #depSelect { width: 100%; max-width: 350px; }
    #personaSelect,
    #personaLevelsContainer .org-level-select,
    #personaLevel1Slot .org-level-select {
        width: 100%;
        max-width: 350px;
    }

    /* Título del organigrama (dentro del cuadro para que salga en la imagen) */
    #orgTituloSeleccion {
        color: #4a4a4a;
        font-weight: 600;
        margin-bottom: 8px;
    }



</style>

<h4 class="mb-4">Organigrama por Departamento</h4>

<div class="card">
    <div class="card-body">
        <!-- Fila 1: Departamento, Persona (máx rango), Nivel 1 (dinámico) -->
        <div class="row mb-3 align-items-end">
            <div class="col-md-4 mb-3">
                <label for="depSelect" class="form-label"><strong>Departamento:</strong></label>
                <select id="depSelect" class="form-select">
                    <?php echo $Departamentos; ?>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label for="personaSelect" class="form-label"><strong>Selecciona persona (máximo rango):</strong></label>
                <select id="personaSelect" class="form-select" disabled>
                    <option value="">-- Selecciona un departamento primero --</option>
                </select>
            </div>
            <div class="col-md-4 mb-3" id="personaLevel1Slot"><!-- Nivel 1 select se inyecta aquí --></div>
        </div>
        <!-- Filas siguientes: Nivel 2, 3, 4... (3 selects por fila) -->
        <div id="personaLevelsContainer" class="mb-4"></div>

        <div id="resultado" class="mt-2"></div>

        <div class="zoom-controls">
            <button id="zoom-out">-</button>
            <button id="zoom-in">+</button>
        </div>

        <!-- Cuadro del organigrama: título + chart (todo se captura al descargar imagen) -->
        <div id="chart-container" class="mt-4">
            <div id="orgTituloSeleccion" class="mb-2"></div>
            <div id="chart"></div>
        </div>

        <div class="mt-4 d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-label-secondary" id="btnLimpiarOrganigrama">
                <i class="fa-solid fa-eraser me-1"></i>Limpiar
            </button>
            <button type="button" class="btn btn-primary" id="btnGuardarOrganigrama" disabled>
                <i class="fa-solid fa-image me-1"></i>Guardar organigrama
            </button>
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
                    <input type="text" id="edit_id" class="form-control phone-mask">
                </div>

                <div class="mb-2">
                    <label class="form-label">Número de Empleado *</label>
                    <input type="text" id="edit_num_empleado" class="form-control phone-mask" disabled>
                </div>

                <div class="mb-2">
                    <label class="form-label">Nombres *</label>
                    <input type="text" id="edit_nombres" class="form-control" oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '').replace(/^\s+/, '').replace(/\s{2,}/g, ' ').toUpperCase()" onblur="this.value = this.value.trim()" style="text-transform: uppercase;">
                </div>

                <div class="mb-2">
                    <label class="form-label">Apellido Paterno *</label>
                    <input type="text" id="edit_apellidop" class="form-control" oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '').replace(/^\s+/, '').replace(/\s{2,}/g, ' ').toUpperCase()" onblur="this.value = this.value.trim()" style="text-transform: uppercase;">
                </div>

                <div class="mb-2">
                    <label class="form-label">Apellido Materno *</label>
                    <input type="text" id="edit_apellidom" class="form-control" oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '').replace(/^\s+/, '').replace(/\s{2,}/g, ' ').toUpperCase()" onblur="this.value = this.value.trim()" style="text-transform: uppercase;">
                </div>

                <div class="mb-2">
                    <label class="form-label">Teléfono *</label>
                    <input type="text" id="edit_telefono" class="form-control phone-mask" oninput="this.value = this.value.replace(/[^0-9]/g, '')" onblur="validarTelefono('edit_telefono')" maxlength="10">
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
                    <input type="text" id="edit_contrasena" class="form-control" maxlength="15" oninput="this.value = this.value.replace(/^\s+/, '').replace(/\s{2,}/g, ' ')" onblur="this.value = this.value.trim()">
                </div>

                <button type="button" class="btn btn-primary me-3" onclick="UpdateGestor()" > Guardar </button>
                <button type="button" class="btn btn-label-danger" data-bs-dismiss="offcanvas" > Cancelar </button>
            </form>
        </div>
    </div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
    /**
     * ==========================================
     * FUNCIÓN VALIDAR TELÉFONO
     * ==========================================
     * Valida que el número no tenga patrones repetitivos
     */
    function validarTelefono(fieldId) {
        const input = document.getElementById(fieldId);
        const telefono = input.value;
        
        // Solo validar si tiene 10 dígitos
        if (telefono.length !== 10) {
            return;
        }
        
        // Verificar patrones repetitivos
        // Ejemplo: 3333333333, 1111111111
        const todosIguales = /^(\d)\1{9}$/.test(telefono);
        
        // Ejemplo: 1212121212, 4242424242
        const patron2Digitos = /^(\d{2})\1{4}$/.test(telefono);
        
        if (todosIguales || patron2Digitos) {
            Swal.fire({
                icon: 'error',
                title: 'Número de teléfono no válido',
                text: 'El número ingresado no es válido. Por favor, ingrese un número telefónico correcto.',
                confirmButtonText: 'Entendido'
            });
            input.value = '';
            input.focus();
        }
    }

    document.addEventListener("DOMContentLoaded", function () {

        let organigramaRows = [];

        function getSubordinadosDirectos(idJefe) {
            return organigramaRows.filter(function (r) {
                if (!r || r.id == null) return false;
                if (String(r.id) === String(idJefe)) return false;
                return String(r.jefe) === String(idJefe);
            });
        }

        function obtenerIdRootActual() {
            var id = document.getElementById("personaSelect").value;
            var cont = document.getElementById("personaLevelsContainer");
            var selects = cont.querySelectorAll("select.org-level-select");
            for (var i = 0; i < selects.length; i++) {
                var v = selects[i].value;
                if (v) id = v;
            }
            return id || null;
        }

        function cargarOrganigramaDesdeRoot(personaId, luegoSubordinados) {
            if (!personaId) {
                document.getElementById("chart").innerHTML = "";
                organigramaRows = [];
                document.getElementById("btnGuardarOrganigrama").disabled = true;
                document.getElementById("orgTituloSeleccion").textContent = "";
                if (luegoSubordinados) luegoSubordinados([]);
                return;
            }
            fetch("/CapHum/nivelJerarquicoColaborador/" + personaId)
                .then(function (res) { return res.json(); })
                .then(function (res) {
                    if (!res.success) {
                        organigramaRows = [];
                        document.getElementById("btnGuardarOrganigrama").disabled = true;
                        document.getElementById("orgTituloSeleccion").textContent = "";
                        if (typeof mostrarMensajeAll === 'function') {
                            mostrarMensajeAll({ tipo: 'error', titulo: 'Error', mensaje: 'No se encontraron resultados' });
                        }
                        if (luegoSubordinados) luegoSubordinados([]);
                        return;
                    }
                    organigramaRows = res.rows || [];
                    (function () {
                        var root = organigramaRows.find(function (r) { return r && (r.jefe === null || r.jefe === undefined); });
                        var titulo = "";
                        if (root) {
                            titulo = "Organigrama: " + (root.nombre || "");
                            var cargo = root.puesto || root.nombre_puesto || "";
                            if (cargo) titulo += " / " + cargo;
                        }
                        document.getElementById("orgTituloSeleccion").textContent = titulo;
                    })();
                    var chartContainer = document.getElementById("chart");
                    chartContainer.innerHTML = "";
                    loadGoogleCharts(function () {
                        drawOrgChart(res.rows, chartContainer);
                    });
                    document.getElementById("btnGuardarOrganigrama").disabled = false;
                    var subs = getSubordinadosDirectos(personaId);
                    var subsConEquipo = subs.filter(function (op) { return getSubordinadosDirectos(op.id).length > 0; });
                    if (luegoSubordinados) luegoSubordinados(subsConEquipo);
                });
        }

        function quitarSelectsDesdeNivel(desdeNivel) {
            if (desdeNivel <= 1) {
                var slot1 = document.getElementById("personaLevel1Slot");
                if (slot1) slot1.innerHTML = "";
            }
            var cont = document.getElementById("personaLevelsContainer");
            var selects = cont.querySelectorAll("select.org-level-select");
            selects.forEach(function (sel) {
                var lvl = parseInt(sel.getAttribute("data-level"), 10);
                if (lvl >= desdeNivel) {
                    var col = sel.closest(".col-md-4");
                    if (col) col.innerHTML = "";
                }
            });
            var rows = cont.querySelectorAll(".row.mb-3");
            rows.forEach(function (row) {
                var cols = row.querySelectorAll(".col-md-4");
                var vacia = true;
                for (var i = 0; i < cols.length; i++) {
                    if (cols[i].children.length > 0) { vacia = false; break; }
                }
                if (vacia) row.remove();
            });
        }

        function getOrCreateRowAt(container, rowIdx) {
            var rows = container.querySelectorAll(".row.mb-3");
            if (rowIdx < rows.length) return rows[rowIdx];
            var row = document.createElement("div");
            row.className = "row mb-3";
            row.innerHTML = '<div class="col-md-4"></div><div class="col-md-4"></div><div class="col-md-4"></div>';
            container.appendChild(row);
            return row;
        }

        function crearSelectNivel(nivel, opciones) {
            var label = document.createElement("label");
            label.className = "form-label";
            label.innerHTML = "<strong>Subordinados (nivel " + nivel + "):</strong>";
            var sel = document.createElement("select");
            sel.className = "form-select org-level-select";
            sel.setAttribute("data-level", nivel);
            var opt0 = document.createElement("option");
            opt0.value = "";
            opt0.textContent = "-- Selecciona --";
            sel.appendChild(opt0);
            opciones.forEach(function (op) {
                var opt = document.createElement("option");
                opt.value = op.id;
                opt.textContent = op.nombre || op.id;
                sel.appendChild(opt);
            });
            sel.addEventListener("change", function () {
                var val = this.value;
                var lvl = parseInt(this.getAttribute("data-level"), 10);
                quitarSelectsDesdeNivel(lvl + 1);
                if (!val) {
                    cargarOrganigramaDesdeRoot(obtenerIdRootActual(), function () {});
                    return;
                }
                cargarOrganigramaDesdeRoot(val, function (subsConEquipo) {
                    if (subsConEquipo.length > 0) anadirSelectNivel(lvl + 1, subsConEquipo);
                });
            });
            return { label: label, sel: sel };
        }

        function anadirSelectNivel(nivel, opciones) {
            if (!opciones || opciones.length === 0) return;
            var frag = crearSelectNivel(nivel, opciones);
            if (nivel === 1) {
                var slot1 = document.getElementById("personaLevel1Slot");
                if (slot1) {
                    slot1.innerHTML = "";
                    slot1.appendChild(frag.label);
                    slot1.appendChild(frag.sel);
                }
                return;
            }
            var cont = document.getElementById("personaLevelsContainer");
            var rowIdx = Math.floor((nivel - 2) / 3);
            var colIdx = (nivel - 2) % 3;
            var row = getOrCreateRowAt(cont, rowIdx);
            var col = row.children[colIdx];
            col.innerHTML = "";
            col.appendChild(frag.label);
            col.appendChild(frag.sel);
        }

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
            document.getElementById("orgTituloSeleccion").textContent = "";
            document.getElementById("chart").innerHTML = "";
            document.getElementById("personaLevel1Slot").innerHTML = "";
            document.getElementById("personaLevelsContainer").innerHTML = "";
            organigramaRows = [];
            document.getElementById("btnGuardarOrganigrama").disabled = true;

            if (!dep_id) return;

            fetch("/CapHum/getPersonasOrganigrama", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ idDepartamento: dep_id })
            })
                .then(res => res.json())
                .then(respuesta => {
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
                })
                .catch(err => {
                    console.error("Error al cargar personas:", err);
                    personaSelect.innerHTML = "<option>Error al cargar personas</option>";
                    personaSelect.disabled = true;
                });
        });

        /* ============================= */
        /*   SELECT PERSONA              */
        /* ============================= */
        document.getElementById("personaSelect").addEventListener("change", function () {
            var persona_id = this.value;
            document.getElementById("personaLevel1Slot").innerHTML = "";
            document.getElementById("personaLevelsContainer").innerHTML = "";

            if (!persona_id) {
                document.getElementById("chart").innerHTML = "";
                organigramaRows = [];
                document.getElementById("btnGuardarOrganigrama").disabled = true;
                document.getElementById("orgTituloSeleccion").textContent = "";
                return;
            }

            cargarOrganigramaDesdeRoot(persona_id, function (subsConEquipo) {
                if (subsConEquipo.length > 0) anadirSelectNivel(1, subsConEquipo);
            });
        });

        /* ============================= */
        /*   BOTÓN LIMPIAR               */
        /* ============================= */
        document.getElementById("btnLimpiarOrganigrama").addEventListener("click", function () {
            var depSelect = document.getElementById("depSelect");
            depSelect.selectedIndex = 0; // "Seleccione una opción"
            var personaSelect = document.getElementById("personaSelect");
            personaSelect.innerHTML = "<option value=\"\">-- Selecciona un departamento primero --</option>";
            personaSelect.disabled = true;
            personaSelect.value = "";
            document.getElementById("personaLevel1Slot").innerHTML = "";
            document.getElementById("personaLevelsContainer").innerHTML = "";
            document.getElementById("chart").innerHTML = "";
            document.getElementById("orgTituloSeleccion").textContent = "";
            document.getElementById("resultado").innerHTML = "";
            organigramaRows = [];
            document.getElementById("btnGuardarOrganigrama").disabled = true;
        });

        /* ============================= */
        /*   BOTÓN GUARDAR ORGANIGRAMA (descargar imagen) */
        /* ============================= */
        document.getElementById("btnGuardarOrganigrama").addEventListener("click", function () {
            var container = document.getElementById("chart-container");
            if (!container || container.querySelectorAll("*").length === 0) {
                if (typeof Swal !== "undefined") Swal.fire("Aviso", "No hay organigrama para guardar. Selecciona departamento y persona primero.", "info");
                return;
            }
            if (typeof html2canvas === "undefined") {
                if (typeof Swal !== "undefined") Swal.fire("Error", "No se puede exportar la imagen. Recarga la página.", "error");
                return;
            }
            html2canvas(container, { scale: 2, useCORS: true, logging: false }).then(function (canvas) {
                var link = document.createElement("a");
                link.download = "organigrama.png";
                link.href = canvas.toDataURL("image/png");
                link.click();
            }).catch(function (err) {
                console.error("html2canvas:", err);
                if (typeof Swal !== "undefined") Swal.fire("Error", "No se pudo generar la imagen.", "error");
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

        function normalizarPuesto(puesto) {
            return puesto
                .replace(/-/g, '-')   // guion no separable
                .replace(/\s+/g, ' ');
        }

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
                            ${r.puesto ? `<div class="org-puesto">${normalizarPuesto(r.puesto)}</div>` : ``}
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


