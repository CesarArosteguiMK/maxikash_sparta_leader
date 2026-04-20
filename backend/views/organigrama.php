<style>
    #chart-container {
        width: 100%;
        max-height: 680px;
        display: flex;
        flex-direction: column;
        position: relative;
        border: 1px solid rgba(0, 0, 0, 0.08);
        padding: 10px;
        background: rgba(255, 255, 255, 0.6);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
    }
    body.dark-mode #chart-container {
        background: rgba(30, 41, 59, 0.5);
        border-color: rgba(71, 85, 105, 0.4);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    }
    #chart-container .organigrama-header {
        position: relative;
        z-index: 6;
        display: block;
        flex-shrink: 0;
        margin-bottom: 8px;
        background: transparent;
        padding: 0;
        pointer-events: auto;
    }
    #chart-container .organigrama-titulo-linea {
        font-size: 1rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: 0;
        display: inline-block;
        width: fit-content;
        max-width: 100%;
        padding: 0.32rem 0.62rem;
        border-radius: 9px;
        border: 1px solid rgba(255, 255, 255, 0.52);
        background: rgba(255, 255, 255, 0.24);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        color: #4a4a4a;
        font-weight: 600;
    }
    body.dark-mode #chart-container .organigrama-titulo-linea {
        color: #cbd5e1;
        border-color: rgba(148, 163, 184, 0.35);
        background: rgba(15, 23, 42, 0.42);
    }
    #chart-container > #organigrama-historial-puestos {
        position: absolute;
        left: 10px;
        top: 46px;
        z-index: 5;
        margin: 0;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.12);
    }
    body.dark-mode #chart-container > #organigrama-historial-puestos {
        box-shadow: 0 2px 16px rgba(0, 0, 0, 0.35);
    }
    #chart-container .organigrama-chart-scroll {
        flex: 1;
        min-height: 260px;
        overflow: auto;
        position: relative;
        z-index: 1;
    }
    @media (max-width: 991.98px) {
        #chart-container .organigrama-titulo-linea {
            max-width: 100%;
        }
    }
    /* Overlay de carga: sin cuadro visible, no depende del zoom (está fuera de #chart) */
    #organigrama-loading-overlay {
        position: absolute;
        inset: 0;
        z-index: 10;
        display: none;
        align-items: center;
        justify-content: center;
        background: transparent;
        border: none;
        pointer-events: none;
    }
    #organigrama-loading-overlay.show { display: flex; }
    #organigrama-loading-overlay .organigrama-loading-inner {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        padding: 1rem;
        text-align: center;
    }
    #organigrama-loading-overlay .organigrama-loading-inner .spinner-glass {
        width: 48px;
        height: 48px;
        margin: 0 auto 0.75rem;
        border: 3px solid rgba(105, 108, 255, 0.25);
        border-top-color: #696cff;
        border-radius: 50%;
        animation: organigrama-spin 0.8s linear infinite;
    }
    #organigrama-loading-overlay .organigrama-loading-inner p { margin: 0; color: inherit; }
    body.dark-mode #organigrama-loading-overlay .organigrama-loading-inner .spinner-glass {
        border-color: rgba(129, 140, 248, 0.25);
        border-top-color: #818cf8;
    }
    #organigrama-historial-puestos {
        padding: 10px 14px;
        background: rgba(0, 0, 0, 0.04);
        border: 1px solid rgba(0, 0, 0, 0.08);
        border-radius: 8px;
        font-size: 0.9rem;
        max-width: 320px;
    }
    #organigrama-historial-puestos .historial-titulo {
        font-weight: 600;
        margin-bottom: 6px;
        color: #475569;
    }
    #organigrama-historial-puestos .historial-linea {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        gap: 0.75rem;
        padding: 2px 0;
        border-bottom: 1px solid rgba(0, 0, 0, 0.06);
    }
    #organigrama-historial-puestos .historial-linea span:first-child { flex: 1; min-width: 0; }
    #organigrama-historial-puestos .historial-linea .historial-numero {
        flex-shrink: 0;
        font-weight: 600;
        margin-left: 0.25rem;
    }
    #organigrama-historial-puestos .historial-linea:last-child { border-bottom: none; }
    body.dark-mode #organigrama-historial-puestos {
        background: rgba(255, 255, 255, 0.06);
        border-color: rgba(255, 255, 255, 0.1);
    }
    body.dark-mode #organigrama-historial-puestos .historial-titulo { color: #94a3b8; }
    body.dark-mode #organigrama-historial-puestos .historial-linea { border-bottom-color: rgba(255, 255, 255, 0.08); }

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

    /* Selects: mismo ancho máximo (departamento/puesto usan el mismo wrapper con búsqueda que persona/niveles) */
    #depSelect,
    #personaPuestoSelect,
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
    }

    /* Select con búsqueda (departamento, puesto, persona y niveles) */
    .select-search-wrapper { position: relative; width: 100%; max-width: 360px; }
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
        background: transparent !important;
        border: 0 !important;
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
        padding: 0 !important;
        max-width: 100% !important;
    }
    #chart-container.organigrama-export .organigrama-header {
        position: static !important;
        z-index: auto !important;
        display: block !important;
        pointer-events: auto !important;
        margin-bottom: 8px !important;
    }
    #chart-container.organigrama-export #organigrama-historial-puestos {
        position: static !important;
        top: auto !important;
        left: auto !important;
        margin-bottom: 8px !important;
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

    /* ===== LIQUID GLASS: mensajes y cargando en organigrama ===== */
    .organigrama-loading-glass,
    .organigrama-msg-glass {
        background: rgba(255, 255, 255, 0.85) !important;
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.5);
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
        padding: 1.5rem 2rem;
        text-align: center;
        color: #475569;
    }
    .organigrama-loading-glass .spinner-glass,
    .organigrama-msg-glass .spinner-glass {
        width: 48px;
        height: 48px;
        margin: 0 auto 1rem;
        border: 3px solid rgba(105, 108, 255, 0.2);
        border-top-color: #696cff;
        border-radius: 50%;
        animation: organigrama-spin 0.8s linear infinite;
    }
    @keyframes organigrama-spin { to { transform: rotate(360deg); } }
    body.dark-mode .organigrama-loading-glass,
    body.dark-mode .organigrama-msg-glass {
        background: rgba(30, 41, 59, 0.9) !important;
        border-color: rgba(71, 85, 105, 0.5);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        color: #e2e8f0;
    }
    body.dark-mode .organigrama-loading-glass .spinner-glass,
    body.dark-mode .organigrama-msg-glass .spinner-glass {
        border-color: rgba(105, 108, 255, 0.25);
        border-top-color: #818cf8;
    }
    /* Easter egg: triple clic en título → "El equipo espartano" + fuegos + espadas (script separado, no toca el organigrama) */
    #organigramaEasterTitle { cursor: default; }
    .org-easter-toast { position: fixed; left: 50%; top: 50%; transform: translate(-50%, -50%); z-index: 1052; background: rgba(30, 41, 59, 0.95); color: #fbbf24; padding: 18px 32px; border-radius: 12px; font-size: 1.05rem; font-weight: 600; box-shadow: 0 8px 32px rgba(0,0,0,0.25); border: 1px solid rgba(251, 191, 36, 0.4); opacity: 0; animation: orgEasterIn 0.35s ease forwards; pointer-events: none; text-align: center; }
    .org-easter-toast .org-easter-swords { font-size: 1.4rem; letter-spacing: 0.25em; display: inline-block; animation: orgEasterSwordsPulse 0.6s ease-in-out infinite; }
    @keyframes orgEasterIn { 0% { opacity: 0; transform: translate(-50%, -50%) scale(0.9); } 100% { opacity: 1; transform: translate(-50%, -50%) scale(1); } }
    @keyframes orgEasterOut { 0% { opacity: 1; transform: translate(-50%, -50%) scale(1); } 100% { opacity: 0; transform: translate(-50%, -50%) scale(0.95); } }
    @keyframes orgEasterSwordsPulse { 0%, 100% { opacity: 1; transform: scale(1); } 50% { opacity: 0.9; transform: scale(1.08); } }
    .org-easter-fw-wrap { position: fixed; inset: 0; z-index: 1048; pointer-events: none; overflow: hidden; }
    .org-easter-fw-dot { position: absolute; width: 12px; height: 12px; border-radius: 50%; pointer-events: none; box-shadow: 0 0 10px 2px currentColor, 0 0 20px currentColor; }
    @keyframes orgEasterFwBurst { 0% { opacity: 1; transform: translate(-50%, -50%) scale(1); } 100% { opacity: 0; transform: translate(calc(-50% + var(--ofw-tx)), calc(-50% + var(--ofw-ty))) scale(0.3); } }
</style>

<h4 class="mb-4" id="organigramaEasterTitle">Organigrama por Departamento</h4>

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

        <!-- Cuadro del organigrama: título + historial fijos debajo, luego chart (scroll/zoom). El historial no se mueve con el diagrama. -->
        <div id="chart-container" class="mt-4">
            <div class="organigrama-header">
                <div id="orgTituloSeleccion" class="organigrama-titulo-linea"></div>
            </div>
            <div id="organigrama-historial-puestos" class="no-export" style="display: none;" aria-label="Historial de puestos del organigrama actual"></div>
            <div class="organigrama-chart-scroll">
                <div id="organigrama-loading-overlay" class="organigrama-loading-overlay" aria-live="polite"></div>
                <div id="chart"></div>
            </div>
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
            <h5 class="offcanvas-title" id="offcanvasEditUserTitle">Editar Usuario</h5>
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
                    <label class="form-label">Nombre *</label>
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
        if (titulo) titulo.textContent = 'Editar Usuario';
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
        if (titulo) titulo.textContent = 'Visualizar Usuario';
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
                this.options.push({ value: option.value, text: option.text });
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
            this._docClose = function () { if (self.isOpen) self.close(); };
            document.addEventListener('click', this._docClose);
            this._mutationObserver = new MutationObserver(function () {
                self.loadOptions();
                var selectedOption = self.select.options[self.select.selectedIndex];
                if (selectedOption) {
                    self.display.textContent = selectedOption.text;
                    self.selectedValue = selectedOption.value;
                }
            });
            this._mutationObserver.observe(this.select, { childList: true, subtree: true });
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
        SearchableSelect.prototype.destroy = function () {
            if (this._docClose) {
                document.removeEventListener('click', this._docClose);
                this._docClose = null;
            }
            if (this._mutationObserver) {
                this._mutationObserver.disconnect();
                this._mutationObserver = null;
            }
            if (this.wrapper && this.wrapper.parentNode && this.select) {
                this.wrapper.parentNode.insertBefore(this.select, this.wrapper);
                this.wrapper.remove();
            }
            this.wrapper = null;
            this.display = null;
            this.dropdown = null;
            this.searchInput = null;
            this.optionsContainer = null;
        };

        let organigramaRows = [];
        var personaSearchSelect = null;
        var depSearchSelect = null;
        var puestoSearchSelect = null;

        function destroyPersonaSearchIfAny() {
            if (personaSearchSelect) {
                personaSearchSelect.destroy();
                personaSearchSelect = null;
            }
        }
        function destroyPuestoSearchIfAny() {
            if (puestoSearchSelect) {
                puestoSearchSelect.destroy();
                puestoSearchSelect = null;
            }
        }

        function getSubordinadosDirectos(idJefe) {
            return organigramaRows.filter(function (r) {
                if (!r || r.id == null) return false;
                if (String(r.id) === String(idJefe)) return false;
                return String(r.jefe) === String(idJefe);
            });
        }

        /** HTML Liquid Glass: cargando (spinner + texto) - usado dentro de #chart para mensajes */
        function getOrganigramaLoadingHtml() {
            return "<div class=\"organigrama-loading-glass\" role=\"status\" aria-live=\"polite\">" +
                "<div class=\"spinner-glass\" aria-hidden=\"true\"></div>" +
                "<p class=\"mb-0 fw-semibold\">Cargando información...</p>" +
                "<p class=\"small text-muted mb-0 mt-1\">Leyendo organigrama</p>" +
                "</div>";
        }
        /** HTML para overlay de carga: sin cuadro (transparente), solo spinner + texto */
        function getOrganigramaLoadingOverlayHtml() {
            return "<div class=\"organigrama-loading-inner\" role=\"status\">" +
                "<div class=\"spinner-glass\" aria-hidden=\"true\"></div>" +
                "<p class=\"fw-semibold mb-0\">Cargando información...</p>" +
                "<p class=\"small text-muted mb-0 mt-1\">Leyendo organigrama</p>" +
                "</div>";
        }
        function mostrarLoadingOrganigrama(mostrar) {
            var overlay = document.getElementById("organigrama-loading-overlay");
            if (!overlay) return;
            if (mostrar) {
                overlay.innerHTML = getOrganigramaLoadingOverlayHtml();
                overlay.classList.add("show");
            } else {
                overlay.innerHTML = "";
                overlay.classList.remove("show");
            }
        }
        /** HTML Liquid Glass: mensaje estático (sin spinner) */
        function getOrganigramaMsgGlassHtml(texto) {
            var t = texto != null ? String(texto) : "";
            return "<div class=\"organigrama-msg-glass\"><p class=\"mb-0\">" + (typeof escapeHtml === "function" ? escapeHtml(t) : t) + "</p></div>";
        }

        /** Orden de puestos por nivel (como en menú departamento). Se rellena al cargar puestos del departamento. */
        var ordenPuestosPorNivel = [];
        /** Obtiene el orden jerárquico de puestos del departamento (mismo que menú departamento) y luego actualiza el historial. */
        function cargarOrdenPuestosYActualizarHistorial() {
            var depSelect = document.getElementById("depSelect");
            var depId = depSelect ? depSelect.value : "";
            if (!depId) {
                ordenPuestosPorNivel = [];
                actualizarHistorialPuestos();
                return;
            }
            fetch("/departamentos/getPuestosPorDepartamento", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ id_departamento: depId })
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    ordenPuestosPorNivel = (data.datos && Array.isArray(data.datos))
                        ? data.datos.map(function (d) { return (d.puesto_nombre || d.nombre || "").trim(); }).filter(Boolean)
                        : [];
                    actualizarHistorialPuestos();
                })
                .catch(function () {
                    ordenPuestosPorNivel = [];
                    actualizarHistorialPuestos();
                });
        }
        /** Historial de puestos: cuenta por nombre de puesto. Orden = mismo que menú departamento (por nivel). Separación clara entre puesto y número. */
        function actualizarHistorialPuestos() {
            var el = document.getElementById("organigrama-historial-puestos");
            if (!el) return;
            if (!organigramaRows || organigramaRows.length === 0) {
                el.style.display = "none";
                el.innerHTML = "";
                return;
            }
            var counts = {};
            organigramaRows.forEach(function (r) {
                if (!r || r.id == null) return;
                var nombrePuesto = (r.puesto || r.nombre_puesto || "").trim() || "Sin puesto";
                counts[nombrePuesto] = (counts[nombrePuesto] || 0) + 1;
            });
            var puestosOrdenados = Object.keys(counts).sort(function (a, b) {
                var ia = ordenPuestosPorNivel.indexOf(a);
                var ib = ordenPuestosPorNivel.indexOf(b);
                if (ia === -1 && ib === -1) return a.localeCompare(b);
                if (ia === -1) return 1;
                if (ib === -1) return -1;
                return ia - ib;
            });
            var lineas = puestosOrdenados.map(function (puesto) {
                return "<div class=\"historial-linea\"><span>" + escapeHtml(puesto) + "</span><span class=\"historial-numero\">: " + counts[puesto] + "</span></div>";
            });
            el.innerHTML = "<div class=\"historial-titulo\">Historial de puestos</div>" + lineas.join("");
            el.style.display = lineas.length ? "block" : "none";
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
                mostrarLoadingOrganigrama(false);
                document.getElementById("chart").innerHTML = "";
                organigramaRows = [];
                document.getElementById("btnGuardarOrganigrama").disabled = true;
                document.getElementById("orgTituloSeleccion").textContent = "";
                actualizarHistorialPuestos();
                if (luegoSubordinados) luegoSubordinados([]);
                return;
            }
            var url = "/CapHum/nivelJerarquicoColaborador/" + personaId;
            var params = [];
            if (idPuesto) params.push("id_puesto=" + encodeURIComponent(idPuesto));
            var depId = document.getElementById("depSelect").value;
            if (depId) params.push("id_departamento=" + encodeURIComponent(depId));
            if (params.length) url += "?" + params.join("&");
            var chartContainer = document.getElementById("chart");
            chartContainer.innerHTML = "";
            mostrarLoadingOrganigrama(true);
            fetch(url)
                .then(function (res) {
                    if (!res.ok) {
                        throw new Error("Error " + res.status);
                    }
                    return res.json();
                })
                .then(function (res) {
                    if (!res.success) {
                        mostrarLoadingOrganigrama(false);
                        organigramaRows = [];
                        document.getElementById("btnGuardarOrganigrama").disabled = true;
                        document.getElementById("orgTituloSeleccion").textContent = "";
                        actualizarHistorialPuestos();
                        var msg = (res.mensaje || "No se encontraron resultados");
                        chartContainer.innerHTML = getOrganigramaMsgGlassHtml(msg);
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
                    cargarOrdenPuestosYActualizarHistorial();
                    if (rows.length === 0) {
                        mostrarLoadingOrganigrama(false);
                        chartContainer.innerHTML = getOrganigramaMsgGlassHtml("No hay datos para mostrar.");
                        document.getElementById("btnGuardarOrganigrama").disabled = true;
                        if (luegoSubordinados) luegoSubordinados([]);
                        return;
                    }
                    chartContainer.innerHTML = "";
                    loadGoogleCharts(function () {
                        drawOrgChart(rows, chartContainer);
                        mostrarLoadingOrganigrama(false);
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
                    mostrarLoadingOrganigrama(false);
                    organigramaRows = [];
                    document.getElementById("chart").innerHTML = getOrganigramaMsgGlassHtml("No se pudo cargar el organigrama. Compruebe la conexión.");
                    document.getElementById("btnGuardarOrganigrama").disabled = true;
                    document.getElementById("orgTituloSeleccion").textContent = "";
                    actualizarHistorialPuestos();
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
            destroyPuestoSearchIfAny();
            puestoSelect.innerHTML = "<option value=\"\">-- Selecciona un puesto --</option>";
            puestoSelect.value = "";

            document.getElementById("resultado").innerHTML = "";
            document.getElementById("orgTituloSeleccion").textContent = "";
            document.getElementById("chart").innerHTML = "";
            document.getElementById("personaLevel1Slot").innerHTML = "";
            document.getElementById("personaLevelsContainer").innerHTML = "";
            organigramaRows = [];
            mostrarLoadingOrganigrama(false);
            actualizarHistorialPuestos();
            document.getElementById("btnGuardarOrganigrama").disabled = true;

            if (!dep_id) {
                destroyPersonaSearchIfAny();
                personaSelect.innerHTML = "<option value=\"\">-- Selecciona un departamento primero --</option>";
                personaSelect.disabled = true;
                personaSelect.value = "";
                return;
            }

            destroyPersonaSearchIfAny();
            personaSelect.innerHTML = "<option>Cargando...</option>";
            personaSelect.disabled = true;

            fetch("/CapHum/getPersonasOrganigrama", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ idDepartamento: dep_id })
            })
                .then(res => res.json())
                .then(respuesta => {
                    personaSelect.innerHTML = "";

                    if (!respuesta.success) {
                        destroyPersonaSearchIfAny();
                        personaSelect.innerHTML = "<option>Error al cargar personas</option>";
                        personaSelect.disabled = true;
                        return;
                    }

                    const personas = Array.isArray(respuesta.datos)
                        ? respuesta.datos
                        : Object.values(respuesta.datos);

                    if (!personas.length) {
                        destroyPersonaSearchIfAny();
                        personaSelect.innerHTML = "<option>No hay personas</option>";
                        personaSelect.disabled = true;
                        return;
                    }

                    personaSelect.innerHTML = '<option value="">Selecciona una persona</option>';
                    personas.forEach(p => {
                        personaSelect.innerHTML += '<option value="' + p.id + '">' + p.nombre + '</option>';
                    });

                    personaSelect.disabled = false;
                    personaSearchSelect = new SearchableSelect(personaSelect);
                })
                .catch(err => {
                    console.error("Error al cargar personas:", err);
                    destroyPersonaSearchIfAny();
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
            var puestoSlot = document.getElementById("personaPuestoSlot");
            var puestoSelect = document.getElementById("personaPuestoSelect");
            puestoSlot.style.display = "none";
            destroyPuestoSearchIfAny();
            puestoSelect.innerHTML = "<option value=\"\">-- Selecciona un puesto --</option>";

            if (!persona_id) {
                document.getElementById("chart").innerHTML = "";
                organigramaRows = [];
                document.getElementById("btnGuardarOrganigrama").disabled = true;
                document.getElementById("orgTituloSeleccion").textContent = "";
                actualizarHistorialPuestos();
                if (personaSearchSelect) personaSearchSelect.refresh();
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
                        document.getElementById("chart").innerHTML = getOrganigramaMsgGlassHtml("No se pudieron cargar los puestos.");
                        return;
                    }
                    var puestos = Array.isArray(data.datos) ? data.datos : [];
                    if (puestos.length === 0) {
                        document.getElementById("chart").innerHTML = getOrganigramaMsgGlassHtml("Esta persona no tiene puestos asignados.");
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
                    destroyPuestoSearchIfAny();
                    puestoSearchSelect = new SearchableSelect(puestoSelect);
                    document.getElementById("chart").innerHTML = getOrganigramaMsgGlassHtml("Selecciona un puesto para ver el organigrama.");
                    document.getElementById("btnGuardarOrganigrama").disabled = true;
                    document.getElementById("orgTituloSeleccion").textContent = "";
                })
                .catch(function (err) {
                    console.error("Error al cargar puestos:", err);
                    document.getElementById("chart").innerHTML = getOrganigramaMsgGlassHtml("Error al cargar puestos.");
                });
        });

        /* ============================= */
        /*   SELECT PUESTO (cuando la persona tiene varios) */
        /* ============================= */
        document.getElementById("personaPuestoSelect").addEventListener("change", function () {
            var persona_id = document.getElementById("personaSelect").value;
            var id_puesto = this.value;
            if (!persona_id || !id_puesto) {
                document.getElementById("chart").innerHTML = getOrganigramaMsgGlassHtml("Selecciona un puesto para ver el organigrama.");
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
            if (depSearchSelect) depSearchSelect.refresh();
            var personaSelect = document.getElementById("personaSelect");
            destroyPersonaSearchIfAny();
            personaSelect.innerHTML = "<option value=\"\">-- Selecciona un departamento primero --</option>";
            personaSelect.disabled = true;
            personaSelect.value = "";
            var personaPuestoSlot = document.getElementById("personaPuestoSlot");
            var personaPuestoSelect = document.getElementById("personaPuestoSelect");
            personaPuestoSlot.style.display = "none";
            destroyPuestoSearchIfAny();
            personaPuestoSelect.innerHTML = "<option value=\"\">-- Selecciona un puesto --</option>";
            document.getElementById("personaLevel1Slot").innerHTML = "";
            document.getElementById("personaLevelsContainer").innerHTML = "";
            document.getElementById("chart").innerHTML = "";
            document.getElementById("orgTituloSeleccion").textContent = "";
            document.getElementById("resultado").innerHTML = "";
            organigramaRows = [];
            mostrarLoadingOrganigrama(false);
            actualizarHistorialPuestos();
            document.getElementById("btnGuardarOrganigrama").disabled = true;
            try {
                scale = 1;
                var chartLimpiar = document.getElementById("chart");
                if (chartLimpiar) chartLimpiar.style.transform = "scale(1)";
            } catch (eLim) {}
        });

        /* ============================= */
        /*   BOTÓN GUARDAR ORGANIGRAMA (descargar imagen COMPLETA, no solo el fragmento visible) */
        /* ============================= */
        document.getElementById("btnGuardarOrganigrama").addEventListener("click", function () {
            var chartDiv = document.getElementById("chart");
            var tituloEl = document.getElementById("orgTituloSeleccion");
            if (!chartDiv || !chartDiv.querySelector(".google-visualization-orgchart-table")) {
                if (typeof Swal !== "undefined") Swal.fire("Aviso", "No hay organigrama para guardar. Selecciona departamento y persona primero.", "info");
                return;
            }
            if (typeof html2canvas === "undefined") {
                if (typeof Swal !== "undefined") Swal.fire("Error", "No se puede exportar la imagen. Recarga la página.", "error");
                return;
            }
            /* Crear wrapper con título + chart completo (scale 1) para capturar TODO el organigrama */
            var fullW = chartDiv.scrollWidth;
            var fullH = chartDiv.scrollHeight;
            var tituloH = tituloEl ? tituloEl.offsetHeight : 0;
            var gap = 12;
            var wrapper = document.createElement("div");
            wrapper.style.cssText = "position:fixed;left:-99999px;top:0;overflow:visible;background:#fff;padding:12px;box-sizing:border-box;";
            wrapper.style.width = (fullW + 24) + "px";
            wrapper.style.height = (tituloH + gap + fullH + 24) + "px";

            var titleClone = tituloEl ? tituloEl.cloneNode(true) : null;
            if (titleClone) {
                titleClone.style.cssText = "color:#0f172a !important;font-size:1.1rem !important;font-weight:700 !important;margin:0 0 " + gap + "px 0 !important;";
                wrapper.appendChild(titleClone);
            }

            var chartClone = chartDiv.cloneNode(true);
            chartClone.style.transform = "scale(1)";
            chartClone.style.transformOrigin = "top left";
            chartClone.style.width = fullW + "px";
            chartClone.style.height = fullH + "px";
            wrapper.appendChild(chartClone);

            /* Estilos de exportación en el clone */
            chartClone.querySelectorAll(".google-visualization-orgchart-node").forEach(function (n) {
                n.style.setProperty("background", "#fff", "important");
                n.style.setProperty("border-color", "#94a3b8", "important");
            });
            chartClone.querySelectorAll(".org-nombre").forEach(function (el) { el.style.setProperty("color", "#1e40af", "important"); });
            chartClone.querySelectorAll(".org-puesto").forEach(function (el) { el.style.setProperty("color", "#475569", "important"); });
            chartClone.querySelectorAll("[class*='google-visualization-orgchart-line']").forEach(function (el) {
                el.style.setProperty("border-color", "#64748b", "important");
            });

            document.body.appendChild(wrapper);
            requestAnimationFrame(function () {
                requestAnimationFrame(function () {
                    html2canvas(wrapper, { scale: 2, useCORS: true, logging: false, allowTaint: false })
                        .then(function (canvas) {
                            document.body.removeChild(wrapper);
                            var link = document.createElement("a");
                            link.download = "organigrama.png";
                            link.href = canvas.toDataURL("image/png");
                            link.style.display = "none";
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);
                        })
                        .catch(function (err) {
                            document.body.removeChild(wrapper);
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

        depSearchSelect = new SearchableSelect(document.getElementById("depSelect"));

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
<!-- Easter egg Organigrama: script separado para no interferir con la lógica del organigrama -->
<script>
(function() {
    function initOrgEaster() {
        try {
            var el = document.getElementById('organigramaEasterTitle');
            if (!el) return;
            var count = 0, t = null;
            el.addEventListener('click', function() {
                count++;
                if (count === 3) {
                    count = 0;
                    if (t) clearTimeout(t);
                    var durationMs = 2500;
                    var wrap = document.createElement('div');
                    wrap.className = 'org-easter-fw-wrap';
                    var colors = ['#fbbf24', '#f59e0b', '#006847', '#ce1126', '#ffffff', '#6366f1'];
                    var positions = [0.12, 0.28, 0.5, 0.72, 0.88, 0.35, 0.65];
                    for (var f = 0; f < 7; f++) {
                        var fwWrap = document.createElement('div');
                        fwWrap.style.cssText = 'position:absolute;left:' + (positions[f] * 100) + '%;top:' + (15 + Math.random() * 20) + '%;width:0;height:0;';
                        var numRays = 28 + Math.floor(Math.random() * 16);
                        var distBase = 90 + Math.random() * 50;
                        for (var r = 0; r < numRays; r++) {
                            var angle = (r / numRays) * Math.PI * 2 + Math.random() * 0.4;
                            var dist = distBase + Math.random() * 40;
                            var tx = Math.cos(angle) * dist + 'px';
                            var ty = Math.sin(angle) * dist - 20 + 'px';
                            var dot = document.createElement('div');
                            dot.className = 'org-easter-fw-dot';
                            dot.style.cssText = 'left:0;top:0;background:' + colors[Math.floor(Math.random() * colors.length)] + ';color:' + colors[Math.floor(Math.random() * colors.length)] + ';animation:orgEasterFwBurst ' + (1.2 + Math.random() * 0.4) + 's ease-out ' + (f * 0.12) + 's forwards;--ofw-tx:' + tx + ';--ofw-ty:' + ty + ';';
                            fwWrap.appendChild(dot);
                        }
                        wrap.appendChild(fwWrap);
                    }
                    document.body.appendChild(wrap);
                    var toast = document.createElement('div');
                    toast.className = 'org-easter-toast';
                    toast.innerHTML = '<span class="org-easter-swords">\u2694\uFE0F</span> El equipo espartano <span class="org-easter-swords">\u2694\uFE0F</span>';
                    document.body.appendChild(toast);
                    var audio = new Audio('/assets/audio/fireworks.mp3');
                    audio.volume = 0.5;
                    audio.play().catch(function(){});
                    function replay() { audio.currentTime = 0; audio.play().catch(function(){}); }
                    audio.addEventListener('ended', replay);
                    setTimeout(function() {
                        audio.pause();
                        audio.removeEventListener('ended', replay);
                        toast.style.animation = 'orgEasterOut 0.35s ease forwards';
                        setTimeout(function() {
                            if (toast.parentNode) toast.parentNode.removeChild(toast);
                            if (wrap.parentNode) wrap.parentNode.removeChild(wrap);
                        }, 350);
                    }, durationMs);
                    return;
                }
                if (t) clearTimeout(t);
                t = setTimeout(function() { count = 0; }, 600);
            });
        } catch (e) { console.warn('Org Easter egg:', e); }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initOrgEaster);
    } else {
        initOrgEaster();
    }
})();
</script>


