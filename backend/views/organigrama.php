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
    #depSelect {
    width: 100%;
    max-width: 360px;
    font-size: 1rem;
    font-weight: 400;
    line-height: 1.5;
    color: #697a8d;
}
    #personaSelect,
    #personaLevelsContainer .org-level-select,
    #personaLevel1Slot .org-level-select {
        width: 100%;
        max-width: 360px;
    }

    /* Título del organigrama (dentro del cuadro para que salga en la imagen) */
    #orgTituloSeleccion {
        color: #4a4a4a;
        font-weight: 600;
        margin-bottom: 8px;
    }

    /* Select con búsqueda (persona y niveles) */
    .select-search-wrapper { position: relative; width: 100%; max-width: 350px; }
    .select-search-wrapper .form-select { display: none; }
    .select-search-display {
        position: relative; width: 100%;
        padding: 0.375rem 2.25rem 0.375rem 0.75rem;
        font-size: 1rem; font-weight: 400; line-height: 1.5;
        color: #697a8d; background-color: #fff;
        border: 1px solid #d9dee3; border-radius: 0.375rem;
        cursor: pointer; transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    .select-search-display:hover { border-color: #b0b7c3; }
    .select-search-display::after {
        content: '▼'; position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%);
        font-size: 0.75rem; color: #697a8d; pointer-events: none;
    }
    .select-search-dropdown {
        position: absolute; top: 100%; left: 0; right: 0; z-index: 1050; display: none;
        margin-top: 0.25rem; background: #fff; border: 1px solid #d9dee3; border-radius: 0.375rem;
        box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,0.1); max-height: 300px; overflow: hidden;
    }
    .select-search-dropdown.show { display: block; }
    .select-search-input {
        width: 100%; padding: 0.5rem 0.75rem; border: none; border-bottom: 1px solid #d9dee3;
        font-size: 0.9375rem; outline: none;
    }
    .select-search-input:focus { border-bottom-color: #696cff; }
    .select-search-options { max-height: 250px; overflow-y: auto; }
    .select-search-option {
        padding: 0.5rem 0.75rem; cursor: pointer; transition: background-color 0.15s ease;
    }
    .select-search-option:hover { background-color: #f5f5f9; }
    .select-search-option.selected { background-color: #696cff; color: #fff; }
    .select-search-option.no-results { padding: 1rem; text-align: center; color: #999; cursor: default; }
    .select-search-option.no-results:hover { background-color: transparent; }

    /* Estilos solo al descargar imagen: alto contraste para que la PNG se vea bien */
    #chart-container.organigrama-export {
        background: #ffffff !important;
        border-color: #cbd5e1 !important;
    }
    #chart-container.organigrama-export #orgTituloSeleccion {
        color: #0f172a !important;
        font-size: 1.1rem !important;
        font-weight: 700 !important;
        margin-bottom: 10px !important;
    }
    #chart-container.organigrama-export .google-visualization-orgchart-node {
        background: #fff !important;
        border: 1px solid #94a3b8 !important;
        box-shadow: 0 2px 8px rgba(0,0,0,.12) !important;
    }
    #chart-container.organigrama-export .google-visualization-orgchart-node div,
    #chart-container.organigrama-export .org-nombre {
        color: #1e40af !important;
    }
    #chart-container.organigrama-export .org-puesto {
        color: #475569 !important;
    }
    #chart-container.organigrama-export .google-visualization-orgchart-lineleft,
    #chart-container.organigrama-export .google-visualization-orgchart-lineright,
    #chart-container.organigrama-export .google-visualization-orgchart-linebottom,
    #chart-container.organigrama-export .google-visualization-orgchart-line {
        border-color: #64748b !important;
    }
</style>

<h4 class="mb-4">Organigrama por Departamento</h4>

<div class="card">
    <div class="card-body">
        <!-- Fila 1: Departamento, Persona (máx rango), Puesto (si tiene varios), Nivel 1 (dinámico) -->
        <div class="row mb-3 align-items-end">
            <div class="col-md-3 mb-3">
                <label for="depSelect" class="form-label"><strong>Departamento:</strong></label>
                <select id="depSelect" class="form-select">
                    <?php echo $Departamentos; ?>
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label for="personaSelect" class="form-label"><strong>Selecciona persona (máximo rango):</strong></label>
                <select id="personaSelect" class="form-select" disabled>
                    <option value="">-- Selecciona un departamento primero --</option>
                </select>
            </div>
            <div class="col-md-3 mb-3" id="personaPuestoSlot" style="display: none;">
                <label for="personaPuestoSelect" class="form-label"><strong>Puesto (elegir):</strong></label>
                <select id="personaPuestoSelect" class="form-select">
                    <option value="">-- Selecciona un puesto --</option>
                </select>
            </div>
            <div class="col-md-3 mb-3" id="personaLevel1Slot"><!-- Nivel 1 select se inyecta aquí --></div>
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
            <h5 class="offcanvas-title" id="offcanvasEditUserTitle">Editar Gestor</h5>
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
                    <label class="form-label">Segundo Nombre (Opcional)</label>
                    <input type="text" id="edit_segundo_nombre" class="form-control" oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '').replace(/^\s+/, '').replace(/\s{2,}/g, ' ').toUpperCase()" onblur="this.value = this.value.trim()" style="text-transform: uppercase;">
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

                <div class="mb-7" id="edit_row_contrasena">
                    <label class="form-label">Contraseña *</label>
                    <input type="text" id="edit_contrasena" class="form-control" maxlength="15" oninput="this.value = this.value.replace(/^\s+/, '').replace(/\s{2,}/g, ' ')" onblur="this.value = this.value.trim()">
                </div>

                <button type="button" class="btn btn-primary me-3" id="edit_btn_guardar" onclick="UpdateGestor()" > Guardar </button>
                <button type="button" class="btn btn-label-danger" data-bs-dismiss="offcanvas" > Cancelar </button>
            </form>
        </div>
    </div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
    window.puedeEditarTodosOrganigrama = <?= json_encode(!empty($puedeEditarTodos ?? false)) ?>;

    function setModoEdicionOrganigrama() {
        var rowContrasena = document.getElementById('edit_row_contrasena');
        var titulo = document.getElementById('offcanvasEditUserTitle');
        var btnGuardar = document.getElementById('edit_btn_guardar');
        var form = document.getElementById('editNewUserForm');
        if (rowContrasena) rowContrasena.style.display = '';
        if (titulo) titulo.textContent = 'Editar Gestor';
        if (btnGuardar) btnGuardar.style.display = '';
        if (form) {
            form.querySelectorAll('input, select').forEach(function(el) { el.disabled = false; });
        }
    }
    function setModoVisualizarOrganigrama() {
        var rowContrasena = document.getElementById('edit_row_contrasena');
        var titulo = document.getElementById('offcanvasEditUserTitle');
        var btnGuardar = document.getElementById('edit_btn_guardar');
        var form = document.getElementById('editNewUserForm');
        if (rowContrasena) rowContrasena.style.display = 'none';
        if (titulo) titulo.textContent = 'Visualizar Gestor';
        if (btnGuardar) btnGuardar.style.display = 'none';
        if (form) {
            form.querySelectorAll('input, select').forEach(function(el) { el.disabled = true; });
        }
        var contrasena = document.getElementById('edit_contrasena');
        if (contrasena) contrasena.value = '';
    }

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

        /* Select con búsqueda (reutilizado para persona y niveles) */
        function SearchableSelect(selectElement) {
            this.select = selectElement;
            this.options = [];
            this.selectedValue = '';
            this.isOpen = false;
            this.init();
        }
        SearchableSelect.prototype.init = function () {
            this.wrapper = document.createElement('div');
            this.wrapper.className = 'select-search-wrapper';
            this.select.parentNode.insertBefore(this.wrapper, this.select);
            this.wrapper.appendChild(this.select);
            this.display = document.createElement('div');
            this.display.className = 'select-search-display';
            this.display.textContent = this.select.options[this.select.selectedIndex] ? this.select.options[this.select.selectedIndex].text : 'Seleccione una opción';
            this.wrapper.appendChild(this.display);
            this.dropdown = document.createElement('div');
            this.dropdown.className = 'select-search-dropdown';
            this.wrapper.appendChild(this.dropdown);
            this.searchInput = document.createElement('input');
            this.searchInput.type = 'text';
            this.searchInput.className = 'select-search-input';
            this.searchInput.placeholder = 'Buscar...';
            this.dropdown.appendChild(this.searchInput);
            this.optionsContainer = document.createElement('div');
            this.optionsContainer.className = 'select-search-options';
            this.dropdown.appendChild(this.optionsContainer);
            this.loadOptions();
            this.attachEvents();
        };
        SearchableSelect.prototype.loadOptions = function () {
            this.options = [];
            Array.from(this.select.options).forEach(function (option) {
                if (option.value !== '') {
                    this.options.push({ value: option.value, text: option.text });
                }
            }, this);
            this.renderOptions(this.options);
        };
        SearchableSelect.prototype.renderOptions = function (filteredOptions) {
            this.optionsContainer.innerHTML = '';
            if (filteredOptions.length === 0) {
                var noResults = document.createElement('div');
                noResults.className = 'select-search-option no-results';
                noResults.textContent = 'No se encontraron resultados';
                this.optionsContainer.appendChild(noResults);
                return;
            }
            filteredOptions.forEach(function (option) {
                var optionDiv = document.createElement('div');
                optionDiv.className = 'select-search-option';
                optionDiv.textContent = option.text;
                optionDiv.dataset.value = option.value;
                if (option.value === this.selectedValue) optionDiv.classList.add('selected');
                optionDiv.addEventListener('click', function () { this.selectOption(option); }.bind(this));
                this.optionsContainer.appendChild(optionDiv);
            }, this);
        };
        SearchableSelect.prototype.selectOption = function (option) {
            this.selectedValue = option.value;
            this.select.value = option.value;
            this.display.textContent = option.text;
            this.select.dispatchEvent(new Event('change', { bubbles: true }));
            this.close();
        };
        SearchableSelect.prototype.open = function () {
            if (this.select.disabled) return;
            this.isOpen = true;
            this.dropdown.classList.add('show');
            this.searchInput.value = '';
            this.searchInput.focus();
            this.loadOptions();
        };
        SearchableSelect.prototype.close = function () {
            this.isOpen = false;
            this.dropdown.classList.remove('show');
            this.searchInput.value = '';
        };
        SearchableSelect.prototype.attachEvents = function () {
            var self = this;
            this.display.addEventListener('click', function (e) {
                e.stopPropagation();
                if (self.isOpen) self.close(); else self.open();
            });
            this.searchInput.addEventListener('input', function (e) {
                var searchTerm = e.target.value.toLowerCase().trim();
                var filtered = self.options.filter(function (o) { return o.text.toLowerCase().includes(searchTerm); });
                self.renderOptions(filtered);
            });
            this.dropdown.addEventListener('click', function (e) { e.stopPropagation(); });
            document.addEventListener('click', function () { if (self.isOpen) self.close(); });
            var observer = new MutationObserver(function () {
                self.loadOptions();
                var selectedOption = self.select.options[self.select.selectedIndex];
                if (selectedOption) {
                    self.display.textContent = selectedOption.text;
                    self.selectedValue = selectedOption.value;
                }
            });
            observer.observe(this.select, { childList: true, subtree: true });
        };
        SearchableSelect.prototype.refresh = function () {
            this.loadOptions();
            var selectedOption = this.select.options[this.select.selectedIndex];
            if (selectedOption) {
                this.display.textContent = selectedOption.text;
                this.selectedValue = selectedOption.value;
            } else {
                this.display.textContent = 'Seleccione una opción';
                this.selectedValue = '';
            }
        };

        let organigramaRows = [];
        var personaSearchSelect = null;

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

        function cargarOrganigramaDesdeRoot(personaId, luegoSubordinados, idPuesto) {
            if (!personaId) {
                document.getElementById("chart").innerHTML = "";
                organigramaRows = [];
                document.getElementById("btnGuardarOrganigrama").disabled = true;
                document.getElementById("orgTituloSeleccion").textContent = "";
                if (luegoSubordinados) luegoSubordinados([]);
                return;
            }
            var url = "/CapHum/nivelJerarquicoColaborador/" + personaId;
            var params = [];
            if (idPuesto) params.push("id_puesto=" + encodeURIComponent(idPuesto));
            var depId = document.getElementById("depSelect").value;
            if (depId) params.push("id_departamento=" + encodeURIComponent(depId));
            if (params.length) url += "?" + params.join("&");
            fetch(url)
                .then(function (res) {
                    if (!res.ok) {
                        throw new Error("Error " + res.status);
                    }
                    return res.json();
                })
                .then(function (res) {
                    if (!res.success) {
                        organigramaRows = [];
                        document.getElementById("btnGuardarOrganigrama").disabled = true;
                        document.getElementById("orgTituloSeleccion").textContent = "";
                        var msg = (res.mensaje || "No se encontraron resultados");
                        if (typeof mostrarMensajeAll === 'function') {
                            mostrarMensajeAll({ tipo: 'error', titulo: 'Error', mensaje: msg });
                        }
                        if (luegoSubordinados) luegoSubordinados([]);
                        return;
                    }
                    var rows = Array.isArray(res.rows) ? res.rows : [];
                    organigramaRows = rows;
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
                    if (rows.length === 0) {
                        chartContainer.innerHTML = "<p class=\"text-muted\">No hay datos para mostrar.</p>";
                        document.getElementById("btnGuardarOrganigrama").disabled = true;
                        if (luegoSubordinados) luegoSubordinados([]);
                        return;
                    }
                    loadGoogleCharts(function () {
                        drawOrgChart(rows, chartContainer);
                    });
                    document.getElementById("btnGuardarOrganigrama").disabled = false;
                    var subs = getSubordinadosDirectos(personaId);
                    /* Quitar duplicados por id (evitar nombres repetidos en el select de nivel) */
                    var seen = {};
                    subs = subs.filter(function (r) {
                        var id = String(r.id);
                        if (seen[id]) return false;
                        seen[id] = true;
                        return true;
                    });
                    var subsConEquipo = subs.filter(function (op) { return getSubordinadosDirectos(op.id).length > 0; });
                    if (luegoSubordinados) luegoSubordinados(subsConEquipo);
                })
                .catch(function (err) {
                    console.error("Organigrama:", err);
                    organigramaRows = [];
                    document.getElementById("chart").innerHTML = "";
                    document.getElementById("btnGuardarOrganigrama").disabled = true;
                    document.getElementById("orgTituloSeleccion").textContent = "";
                    if (typeof mostrarMensajeAll === 'function') {
                        mostrarMensajeAll({ tipo: 'error', titulo: 'Error', mensaje: 'No se pudo cargar el organigrama. Compruebe la conexión.' });
                    }
                    if (luegoSubordinados) luegoSubordinados([]);
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
            /* Evitar opciones duplicadas por id */
            var seenId = {};
            (opciones || []).forEach(function (op) {
                var id = op && op.id != null ? String(op.id) : "";
                if (!id || seenId[id]) return;
                seenId[id] = true;
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
                    new SearchableSelect(frag.sel);
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
            new SearchableSelect(frag.sel);
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

            /* Quitar rápido la selección de puesto si estaba visible de otra búsqueda */
            var puestoSlot = document.getElementById("personaPuestoSlot");
            var puestoSelect = document.getElementById("personaPuestoSelect");
            puestoSlot.style.display = "none";
            puestoSelect.innerHTML = "<option value=\"\">-- Selecciona un puesto --</option>";
            puestoSelect.value = "";

            personaSelect.innerHTML = "<option>Cargando...</option>";
            personaSelect.disabled = true;

            document.getElementById("resultado").innerHTML = "";
            document.getElementById("orgTituloSeleccion").textContent = "";
            document.getElementById("chart").innerHTML = "";
            document.getElementById("personaLevel1Slot").innerHTML = "";
            document.getElementById("personaLevelsContainer").innerHTML = "";
            organigramaRows = [];
            document.getElementById("btnGuardarOrganigrama").disabled = true;
            if (personaSearchSelect) personaSearchSelect.refresh();

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
                        if (personaSearchSelect) personaSearchSelect.refresh();
                        return;
                    }

                    const personas = Array.isArray(respuesta.datos)
                        ? respuesta.datos
                        : Object.values(respuesta.datos);

                    if (!personas.length) {
                        personaSelect.innerHTML = "<option>No hay personas</option>";
                        personaSelect.disabled = true;
                        if (personaSearchSelect) personaSearchSelect.refresh();
                        return;
                    }

                    personaSelect.innerHTML = '<option value="">Selecciona una persona</option>';
                    personas.forEach(p => {
                        personaSelect.innerHTML += '<option value="' + p.id + '">' + p.nombre + '</option>';
                    });

                    personaSelect.disabled = false;
                    if (!personaSearchSelect) {
                        personaSearchSelect = new SearchableSelect(personaSelect);
                    } else {
                        personaSearchSelect.refresh();
                    }
                })
                .catch(err => {
                    console.error("Error al cargar personas:", err);
                    personaSelect.innerHTML = "<option>Error al cargar personas</option>";
                    personaSelect.disabled = true;
                    if (personaSearchSelect) personaSearchSelect.refresh();
                });
        });

        /* ============================= */
        /*   SELECT PERSONA              */
        /* ============================= */
        document.getElementById("personaSelect").addEventListener("change", function () {
            var persona_id = this.value;
            document.getElementById("personaLevel1Slot").innerHTML = "";
            document.getElementById("personaLevelsContainer").innerHTML = "";
            var puestoSlot = document.getElementById("personaPuestoSlot");
            var puestoSelect = document.getElementById("personaPuestoSelect");
            puestoSlot.style.display = "none";
            puestoSelect.innerHTML = "<option value=\"\">-- Selecciona un puesto --</option>";

            if (!persona_id) {
                document.getElementById("chart").innerHTML = "";
                organigramaRows = [];
                document.getElementById("btnGuardarOrganigrama").disabled = true;
                document.getElementById("orgTituloSeleccion").textContent = "";
                return;
            }

            var dep_id = document.getElementById("depSelect").value;
            fetch("/CapHum/getPuestosPorPersona", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ idPersona: persona_id, idDepartamento: dep_id || "" })
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (!data.success || !data.datos) {
                        document.getElementById("chart").innerHTML = "<p class=\"text-muted\">No se pudieron cargar los puestos.</p>";
                        return;
                    }
                    var puestos = Array.isArray(data.datos) ? data.datos : [];
                    if (puestos.length === 0) {
                        document.getElementById("chart").innerHTML = "<p class=\"text-muted\">Esta persona no tiene puestos asignados.</p>";
                        return;
                    }
                    if (puestos.length === 1) {
                        cargarOrganigramaDesdeRoot(persona_id, function (subsConEquipo) {
                            if (subsConEquipo.length > 0) anadirSelectNivel(1, subsConEquipo);
                        }, puestos[0].id);
                        return;
                    }
                    puestoSlot.style.display = "block";
                    puestos.forEach(function (p) {
                        var opt = document.createElement("option");
                        opt.value = p.id;
                        opt.textContent = p.nombre;
                        puestoSelect.appendChild(opt);
                    });
                    document.getElementById("chart").innerHTML = "<p class=\"text-muted\">Selecciona un puesto para ver el organigrama.</p>";
                    document.getElementById("btnGuardarOrganigrama").disabled = true;
                    document.getElementById("orgTituloSeleccion").textContent = "";
                })
                .catch(function (err) {
                    console.error("Error al cargar puestos:", err);
                    document.getElementById("chart").innerHTML = "<p class=\"text-muted\">Error al cargar puestos.</p>";
                });
        });

        /* ============================= */
        /*   SELECT PUESTO (cuando la persona tiene varios) */
        /* ============================= */
        document.getElementById("personaPuestoSelect").addEventListener("change", function () {
            var persona_id = document.getElementById("personaSelect").value;
            var id_puesto = this.value;
            if (!persona_id || !id_puesto) {
                document.getElementById("chart").innerHTML = "<p class=\"text-muted\">Selecciona un puesto para ver el organigrama.</p>";
                document.getElementById("btnGuardarOrganigrama").disabled = true;
                return;
            }
            document.getElementById("personaLevel1Slot").innerHTML = "";
            document.getElementById("personaLevelsContainer").innerHTML = "";
            cargarOrganigramaDesdeRoot(persona_id, function (subsConEquipo) {
                if (subsConEquipo.length > 0) anadirSelectNivel(1, subsConEquipo);
            }, id_puesto);
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
            var personaPuestoSlot = document.getElementById("personaPuestoSlot");
            var personaPuestoSelect = document.getElementById("personaPuestoSelect");
            personaPuestoSlot.style.display = "none";
            personaPuestoSelect.innerHTML = "<option value=\"\">-- Selecciona un puesto --</option>";
            document.getElementById("personaLevel1Slot").innerHTML = "";
            document.getElementById("personaLevelsContainer").innerHTML = "";
            document.getElementById("chart").innerHTML = "";
            document.getElementById("orgTituloSeleccion").textContent = "";
            document.getElementById("resultado").innerHTML = "";
            organigramaRows = [];
            document.getElementById("btnGuardarOrganigrama").disabled = true;
            if (personaSearchSelect) personaSearchSelect.refresh();
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
            /* Forzar estilos en línea para la captura (fondo blanco, título legible) */
            var titulo = document.getElementById("orgTituloSeleccion");
            var nodos = container.querySelectorAll(".google-visualization-orgchart-node");
            var lineas = container.querySelectorAll("[class*='google-visualization-orgchart-line']");
            var guardado = {
                contBg: container.style.cssText,
                tituloStyle: titulo ? titulo.style.cssText : "",
                nodos: [],
                lineas: []
            };
            container.style.setProperty("background", "#ffffff", "important");
            container.style.setProperty("background-image", "none", "important");
            if (titulo) {
                titulo.style.setProperty("color", "#0f172a", "important");
                titulo.style.setProperty("font-size", "1.1rem", "important");
                titulo.style.setProperty("font-weight", "700", "important");
            }
            nodos.forEach(function (n) {
                guardado.nodos.push(n.style.cssText);
                n.style.setProperty("background", "#ffffff", "important");
                n.style.setProperty("border-color", "#94a3b8", "important");
                n.style.setProperty("color", "#1e293b", "important");
            });
            container.querySelectorAll(".org-nombre, .org-puesto").forEach(function (el) {
                el.style.setProperty("color", "#1e40af", "important");
            });
            container.querySelectorAll(".org-puesto").forEach(function (el) {
                el.style.setProperty("color", "#475569", "important");
            });
            lineas.forEach(function (el) {
                guardado.lineas.push(el.style.cssText);
                el.style.setProperty("border-color", "#64748b", "important");
            });
            requestAnimationFrame(function () {
                requestAnimationFrame(function () {
                    html2canvas(container, { scale: 2, useCORS: true, logging: false, allowTaint: false }).then(function (canvas) {
                        container.style.cssText = guardado.contBg;
                        if (titulo) titulo.style.cssText = guardado.tituloStyle;
                        nodos.forEach(function (n, i) {
                            n.style.cssText = guardado.nodos[i] || "";
                        });
                        container.querySelectorAll(".org-nombre, .org-puesto").forEach(function (el) { el.style.cssText = ""; });
                        lineas.forEach(function (el, i) {
                            el.style.cssText = guardado.lineas[i] || "";
                        });
                        var link = document.createElement("a");
                        link.download = "organigrama.png";
                        link.href = canvas.toDataURL("image/png");
                        link.style.display = "none";
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    }).catch(function (err) {
                        container.style.cssText = guardado.contBg;
                        if (titulo) titulo.style.cssText = guardado.tituloStyle;
                        nodos.forEach(function (n, i) { n.style.cssText = guardado.nodos[i] || ""; });
                        container.querySelectorAll(".org-nombre, .org-puesto").forEach(function (el) { el.style.cssText = ""; });
                        lineas.forEach(function (n, i) { n.style.cssText = guardado.lineas[i] || ""; });
                        console.error("html2canvas:", err);
                        if (typeof Swal !== "undefined") Swal.fire("Error", "No se pudo generar la imagen.", "error");
                    });
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
                    document.getElementById("edit_segundo_nombre").value = persona.segundo_nombre ?? '';
                    document.getElementById("edit_apellidop").value = persona.apellidop ?? '';
                    document.getElementById("edit_apellidom").value = persona.apellidom ?? '';
                    document.getElementById("edit_telefono").value = persona.telefono ?? '';
                    document.getElementById("edit_usuario").value = persona.user_name ?? '';
                    document.getElementById("edit_contrasena").value = persona.password ?? '';
                    document.getElementById("edit_departamento_id").value = persona.id_departamento ?? '';
                    document.getElementById("edit_id_puesto").value = persona.id_puesto ?? '';

                    if (window.puedeEditarTodosOrganigrama) {
                        setModoEdicionOrganigrama();
                    } else {
                        document.getElementById("edit_contrasena").value = '';
                        setModoVisualizarOrganigrama();
                    }

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
            if (puesto == null || puesto === undefined) return '';
            return String(puesto)
                .replace(/-/g, '-')   // guion no separable
                .replace(/\s+/g, ' ');
        }

        /** Escapa HTML para que nombres con &, <, >, ", ' no rompan el organigrama */
        function escapeHtml(str) {
            if (str == null || str === undefined) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        /** Escapa el id para uso dentro de onclick="abrirModal('...')" (evita que ids con apóstrofe o backslash rompan el JS) */
        function escapeIdForAttr(id) {
            if (id == null || id === undefined) return '';
            return String(id).replace(/\\/g, '\\\\').replace(/'/g, "\\'");
        }

        /* ============================= */
        /*   ORGANIGRAMA                 */
        /* ============================= */
        function drawOrgChart(rows, container) {
            if (!rows || rows.length === 0) return;
            const data = new google.visualization.DataTable();
            data.addColumn("string", "Nombre");
            data.addColumn("string", "Jefe");

            rows.forEach(r => {
                if (!r || r.id == null) return;
                var idSafe = escapeIdForAttr(r.id);
                var nombreSafe = escapeHtml(r.nombre != null ? r.nombre : '');
                var puestoSafe = r.puesto ? escapeHtml(normalizarPuesto(r.puesto)) : '';
                data.addRow([
                    {
                        v: String(r.id),
                        f: '<div class="org-nombre" style="font-weight:bold;cursor:pointer;color:#2a6ebb" onclick="abrirModal(\'' + idSafe + '\')">' +
                            nombreSafe +
                            (puestoSafe ? '<div class="org-puesto">' + puestoSafe + '</div>' : '') +
                            '</div>',
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


