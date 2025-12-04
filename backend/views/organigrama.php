
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

        <!-- Tabla subordinados -->
        <div id="subordinados" class="mt-4"></div>
    </div>
</div>

<!-- Modal para agregar solicitud -->
<div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="text-center w-100">
                    <h4 class="address-title mb-2">Edición de Capital Humano</h4>
                    <p class="address-subtitle">Solo se puede reasignar puesto y jefe</p>
                </div>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="form-group col-8">
                        <label for="proyecto" class="form-label">Nombre del Colaborador</label>
                        <input type="text" id="proyecto" name="proyecto" class="form-control" placeholder="" maxlength="500" disabled>
                        <div class="fv-message text-danger small" style="min-height: 1.25rem"></div>
                    </div>
                    <div class="form-group col-4">
                        <label for="proyecto" class="form-label">Departamento</label>
                        <input type="text" id="proyecto" name="proyecto" class="form-control" placeholder="" maxlength="500" disabled>
                        <div class="fv-message text-danger small" style="min-height: 1.25rem"></div>
                    </div>
                    <div class="form-group col-6">
                        <label for="tipoSolicitud" class="form-label">Puesto</label>
                        <select class="form-select" id="tipoSolicitud" name="tipoSolicitud">
                            <option value="1">Viáticos (por comprobar)</option>
                            <option value="2">Gastos (reembolso)</option>
                        </select>
                        <div class="fv-message text-danger small" style="min-height: 1.25rem"></div>
                    </div>
                    <div class="form-group col-6">
                        <label for="tipoSolicitud" class="form-label">Jefe</label>
                        <select class="form-select" id="tipoSolicitud" name="tipoSolicitud">
                            <option value="1">Viáticos (por comprobar)</option>
                            <option value="2">Gastos (reembolso)</option>
                        </select>
                        <div class="fv-message text-danger small" style="min-height: 1.25rem"></div>
                    </div>
                </div>
                <div id="comprobantesGastos" class="row" style="display: none;">
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
                                        <button type="button" id="btnAgregarComprobante" class="btn btn-success btn-sm">
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



<script>
    document.getElementById("depSelect").addEventListener("change", function() {
        let dep_id = this.value;
        let personaSelect = document.getElementById("personaSelect");
        personaSelect.innerHTML = "<option>Cargando...</option>";
        personaSelect.disabled = true;

        document.getElementById("resultado").innerHTML = "";
        document.getElementById("countPuestos").innerHTML = "";
        document.getElementById("subordinados").innerHTML = "";

        if (!dep_id) return;



        const getSolicitudes = () => {
            consultaServidor("/CapHum/getPuestosPorDepartamento", { idDepartamento: dep_id }, (respuesta) => {
                const personaSelect = document.getElementById('personaSelect');

                // Limpiar combo
                personaSelect.innerHTML = "";

                // Validar éxito
                if (!respuesta.success) {
                    personaSelect.innerHTML = "<option>Error al cargar personas</option>";
                    personaSelect.disabled = true;
                    return;
                }

                // Asegurarnos que datos es un array
                const personas = Array.isArray(respuesta.datos) ? respuesta.datos : Object.values(respuesta.datos);

                if (personas.length === 0) {
                    personaSelect.innerHTML = "<option>No hay personas</option>";
                    personaSelect.disabled = true;
                    return;
                }

                // Llenar combo
                personaSelect.innerHTML = '<option value="">Selecciona una opción --</option>';
                personas.forEach(p => {
                    personaSelect.innerHTML += `<option value="${p.id}">${p.nombre}</option>`;
                });

                personaSelect.disabled = false;
            });
        };

        $(document).ready(() => {
            getSolicitudes()
        });
        return; // termina la función



        // Conteo por puesto
        fetch("/nivel_jerarquico/count/" + dep_id)
            .then(res => res.json())
            .then(data => {
                if (!data || data.length === 0) {
                    document.getElementById("countPuestos").innerHTML =
                        "<p class='text-muted'>No hay información de puestos.</p>";
                    return;
                }
                let html = `
                <h5 class="mt-4">Resumen por Puesto</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mt-2">
                        <thead class="table-light">
                            <tr>
                                <th>Puesto</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
                data.forEach(row => {
                    html += `<tr><td>${row.puesto}</td><td><strong>${row.total_empleados}</strong></td></tr>`;
                });
                html += `</tbody></table></div>`;
                document.getElementById("countPuestos").innerHTML = html;
            });
    });

    document.getElementById("personaSelect").addEventListener("change", function() {
        let persona_id = this.value;
        if (!persona_id) return;


        // Organigrama
        fetch("/CapHum/nivelJerarquicoColaborador/" + persona_id)
            .then(res => res.json())
            .then(res => {
                if (!res.success) {
                    mostrarMensajeAll({ tipo:'error', titulo:'Error', mensaje:'No se encontraron resultados' });
                    return;
                }

                // Asegurarse de que el div 'chart' exista antes de dibujar
                const chartContainer = document.getElementById('chart');
                if (!chartContainer) {
                    console.error("El div #chart no existe en el DOM");
                    return;
                }

                loadGoogleCharts(function() {
                    drawOrgChart(res.rows, chartContainer);
                });
            });

        function flattenOrg(json) {
            const rows = [];
            const recurse = (node, padre = null) => {
                // Agregar el nodo actual
                rows.push({
                    id: node.id,
                    nombre: node.nombre,
                    jefe: padre,
                    nombre_puesto: node.nombre_puesto || '', // aquí tomamos el puesto del nodo
                    collapsed: padre !== null // raíz no colapsada
                });

                // Recorrer subordinados
                if (node.subordinados) {
                    node.subordinados.forEach(sub => recurse(sub, node.nombre));
                }
            };

            // Nodo raíz
            recurse({
                nombre: "JEFE " + json.id_jefe,
                subordinados: json.subordinados
            });

            return rows;
        }

        window.abrirModal = function(valor) {
            console.log("Valor recibido:", valor); // para debug
            // Aquí puedes poner el valor en algún campo del modal si quieres
            document.getElementById('proyecto').value = valor;

            // Abrir el modal con Bootstrap 5
            var modal = new bootstrap.Modal(document.getElementById('modalEditar'));
            modal.show();
        };


        function drawOrgChart(rows) {
            const data = new google.visualization.DataTable();
            data.addColumn('string', 'Nombre');
            data.addColumn('string', 'Jefe');



            rows.forEach(r => {

                data.addRow([
                    {
                        v: r[0],
                        f: `<div style="padding:0px;font-weight:bold;cursor:pointer;color:#2a6ebb;"
                        onclick="abrirModal('${r[0]}')">
                        ${r[0]}
                    </div>`,
                        p: { collapsed: r[1] !== null } // solo raíz expandido
                    },
                    r[1]
                ]);
            });

            const chart = new google.visualization.OrgChart(document.getElementById('chart'));
            chart.draw(data, { allowHtml: true, allowCollapse: true });
        }

        // Tabla subordinados
        fetch("/nivel_jerarquico/colaborador_tabla/" + persona_id)
            .then(res => res.json())
            .then(data => {
                if (!data || data.length === 0) {
                    document.getElementById("subordinados").innerHTML =
                        "<p class='text-muted'>No hay empleados bajo esta persona.</p>";
                    return;
                }

                const puestos = [...new Set(data.map(p => p.puesto).filter(Boolean))].sort();
                let filtrosHTML = `
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label"><strong>Filtrar por Puesto:</strong></label>
                        <select id="filtroPuesto" class="form-select">
                            <option value="">-- Todos --</option>
                            ${puestos.filter(p => p !== 'Gestor 1-7').map(p => `<option value="${p}">${p}</option>`).join('')}
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><strong>Filtrar por Colaborador:</strong></label>
                        <select id="filtroColaborador" class="form-select" disabled>
                            <option value="">-- Selecciona un puesto --</option>
                        </select>
                    </div>
                </div>
            `;

                let tablaHTML = `
                <div class="table-responsive">
                    <table id="subordinadosTable" class="table table-striped table-bordered table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Nombre Completo</th>
                                <th>Departamento</th>
                                <th>Puesto</th>
                                <th>Estatus</th>
                                <th>Nombre Jefe</th>
                                <th>Puesto Jefe</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
                data.forEach(p => {
                    tablaHTML += `
                    <tr>
                        <td>${p.nombre_completo}</td>
                        <td>${p.departamento || ''}</td>
                        <td>${p.puesto || ''}</td>
                        <td>${p.estatus || ''}</td>
                        <td>${p.nombre_jefe || ''}</td>
                        <td>${p.puesto_jefe || ''}</td>
                        <td>
                            <a href="/editar_persona/${p.id}" class="btn btn-sm btn-warning mb-1" title="Editar">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <a href="/documentacion_persona/${p.id}" class="btn btn-sm btn-info mb-1" title="Documentación">
                                <i class="fa-solid fa-file"></i>
                            </a>
                            ${p.estatus !== 'Baja' ? `
                            <a href="/baja_persona/${p.id}" class="btn btn-sm btn-danger mb-1" title="Dar de Baja">
                                <i class="fa-solid fa-user-slash"></i>
                            </a>` : ''}
                        </td>
                    </tr>
                `;
                });
                tablaHTML += `</tbody></table></div>`;

                document.getElementById("subordinados").innerHTML = `
                <h5 class="mt-4">Empleados bajo ${this.options[this.selectedIndex].text}</h5>
                ${filtrosHTML}
                ${tablaHTML}
            `;

                const table = $('#subordinadosTable').DataTable({
                    "language": {
                        "search": "Buscar:",
                        "lengthMenu": "Mostrar _MENU_ registros",
                        "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                        "paginate": {
                            "first": "Primero",
                            "last": "Último",
                            "next": "Siguiente",
                            "previous": "Anterior"
                        }
                    },
                    "pageLength": 10
                });

                // Filtro por puesto
                $('#filtroPuesto').on('change', function() {
                    const puesto = $(this).val();
                    table.columns(2).search(puesto).draw();

                    const colaboradorSelect = $('#filtroColaborador');
                    colaboradorSelect.html('');
                    if (!puesto) {
                        colaboradorSelect.html('<option value="">-- Selecciona un puesto --</option>');
                        colaboradorSelect.prop('disabled', true);
                        return;
                    }

                    const colaboradores = [...new Set(data
                        .filter(p => p.puesto === puesto)
                        .map(p => p.nombre_completo)
                        .filter(Boolean)
                    )].sort();

                    colaboradorSelect.html('<option value="">-- Todos --</option>');
                    colaboradores.forEach(nombre => {
                        colaboradorSelect.append(`<option value="${nombre}">${nombre}</option>`);
                    });
                    colaboradorSelect.prop('disabled', false);
                });

                // Filtro por colaborador
                $('#filtroColaborador').on('change', function() {
                    const nombreSeleccionado = $(this).val();
                    if (!nombreSeleccionado) {
                        table.columns(0).search('').draw();
                        return;
                    }
                    table.columns(0).search('^' + nombreSeleccionado + '$', true, false).draw();
                });
            });
    });
</script>
