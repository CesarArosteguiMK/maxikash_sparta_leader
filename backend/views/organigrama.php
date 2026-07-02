<style>
    #chart-container {
        width: 100%;
        min-height: 720px;
        max-height: none;
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
    #chart-container .organigrama-chart-scroll {
        flex: 1;
        min-height: 620px;
        overflow: auto;
        position: relative;
        z-index: 1;
        cursor: grab;
        user-select: none;
        -webkit-user-select: none;
    }
    #chart-container .organigrama-chart-scroll.is-panning {
        cursor: grabbing;
    }
    #chart-container .organigrama-chart-scroll.is-panning * {
        cursor: grabbing !important;
    }
    #chart {
        width: max-content;
        min-width: 180%;
        box-sizing: border-box;
        padding: 0 35% 90px;
        margin: 0 auto;
    }
    @media (max-width: 991.98px) {
        #chart {
            min-width: 220%;
            padding-left: 50%;
            padding-right: 50%;
        }
    }
    #chart:has(.organigrama-msg-glass),
    #chart:has(.organigrama-loading-glass) {
        width: 100%;
        min-width: 100%;
        min-height: 520px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0;
        transform: none !important;
    }
    #chart-container .organigrama-chart-scroll:has(.organigrama-msg-glass),
    #chart-container .organigrama-chart-scroll:has(.organigrama-loading-glass) {
        overflow: hidden;
        cursor: default;
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
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.12);
        margin-bottom: 1rem;
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
        transform-origin: top center;
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
    #dirSelect,
    #areaSelect,
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

<h4 class="mb-4" id="organigramaEasterTitle">Organigrama por Estructura</h4>

<div class="card min-vh-100">
    <div class="card-body p-4">
        <!-- Fila 1: Departamento, Persona (máx rango), Puesto (si tiene varios), Nivel 1 (dinámico) -->
        <div class="row mb-3 align-items-end">
            <div class="col-md-3 mb-3" id="dirSelectSlot">
                <label for="dirSelect" class="form-label"><strong>Direccion:</strong></label>
                <select id="dirSelect" class="form-select">
                    <option value="">Seleccione direccion</option>
                </select>
            </div>
            <div class="col-md-3 mb-3 d-none" id="areaSelectSlot">
                <label for="areaSelect" class="form-label"><strong>Area:</strong></label>
                <select id="areaSelect" class="form-select" disabled>
                    <option value="">Seleccione direccion primero</option>
                </select>
            </div>
            <div class="col-md-3 mb-3 d-none" id="depSelectSlot">
                <label for="depSelect" class="form-label"><strong>Departamento:</strong></label>
                <select id="depSelect" class="form-select" disabled>
                    <?php echo $Departamentos; ?>
                </select>
            </div>
            <div class="col-md-3 mb-3 d-none" id="personaSelectSlot">
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

        <div class="d-flex justify-content-start align-items-center gap-2 mb-2">
            <span class="badge bg-label-secondary text-secondary">Zoom</span>
            <div class="btn-group shadow-sm" role="group" aria-label="Controles de zoom del organigrama">
                <button type="button" id="zoom-out" class="btn btn-outline-secondary btn-sm" title="Alejar">
                    <i class="fa-solid fa-minus"></i>
                </button>
                <button type="button" id="zoom-in" class="btn btn-outline-primary btn-sm" title="Acercar">
                    <i class="fa-solid fa-plus"></i>
                </button>
            </div>
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

<div class="modal fade" id="modalCambiarJefeVacante" tabindex="-1" aria-labelledby="modalCambiarJefeVacanteLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCambiarJefeVacanteLabel">
                    <i class="fa fa-briefcase me-2"></i>Gestionar vacante
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="vacante_jefe_id_vacante">
                <input type="hidden" id="vacante_jefe_id_departamento">
                <input type="hidden" id="vacante_jefe_id_puesto">
                <input type="hidden" id="vacante_jefe_id_superior">
                <div class="alert alert-warning bg-warning-subtle border border-warning-subtle text-warning-emphasis small mb-3">
                    <div class="fw-semibold" id="vacante_jefe_resumen">Vacante</div>
                    <div>Actualiza el nombre, cambia su jefe o elimina la vacante reasignando su equipo.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nombre de la vacante *</label>
                    <div class="d-flex gap-2">
                        <input type="text" id="vacante_nombre_vacante" class="form-control" maxlength="180" placeholder="Nombre de la vacante">
                        <button type="button" class="btn btn-outline-primary flex-shrink-0" onclick="guardarNombreVacanteOrganigrama()">
                            <i class="fa fa-save me-1"></i>Nombre
                        </button>
                    </div>
                </div>
                <div class="border-top pt-3 mb-3">
                    <label class="form-label">Jefe destino *</label>
                    <div class="d-flex gap-2">
                        <select id="vacante_jefe_id_jefe" class="form-select">
                            <option value="">Seleccione un jefe</option>
                        </select>
                        <button type="button" class="btn btn-outline-primary flex-shrink-0" onclick="guardarJefeVacanteOrganigrama()">
                            <i class="fa fa-save me-1"></i>Jefe
                        </button>
                    </div>
                </div>
                <div class="border-top pt-3">
                    <div class="fw-semibold text-danger mb-2">Eliminar vacante</div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="vacante_modo_eliminar" id="vacante_modo_jefe_superior" value="jefe_superior" checked>
                        <label class="form-check-label" for="vacante_modo_jefe_superior">
                            Mover sus subordinados al jefe superior actual
                        </label>
                        <div class="form-text" id="vacante_jefe_superior_hint">Se usara el jefe al que esta ligada esta vacante.</div>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="vacante_modo_eliminar" id="vacante_modo_jefe_destino" value="jefe_destino">
                        <label class="form-check-label" for="vacante_modo_jefe_destino">
                            Mover sus subordinados a otra persona
                        </label>
                    </div>
                    <select id="vacante_eliminar_id_jefe" class="form-select d-none">
                        <option value="">Seleccione un jefe destino</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="eliminarVacanteOrganigrama()">
                    <i class="fa fa-trash me-1"></i>Eliminar vacante
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCambiarJefePersona" tabindex="-1" aria-labelledby="modalCambiarJefePersonaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCambiarJefePersonaLabel">
                    <i class="fa fa-user-tie me-2"></i>Cambiar jefe
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="persona_jefe_id_persona">
                <input type="hidden" id="persona_jefe_id_departamento">
                <input type="hidden" id="persona_jefe_id_puesto">
                <div class="alert alert-primary bg-primary-subtle border border-primary-subtle text-primary-emphasis small mb-3">
                    <div class="fw-semibold" id="persona_jefe_resumen">Persona</div>
                    <div>Selecciona el jefe al que quedara ligada esta persona.</div>
                </div>
                <label class="form-label">Jefe destino *</label>
                <select id="persona_jefe_id_jefe" class="form-select">
                    <option value="">Seleccione un jefe</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="guardarJefePersonaOrganigrama()">
                    <i class="fa fa-save me-1"></i>Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
    window.puedeEditarTodosOrganigrama = <?= json_encode(!empty($puedeEditarTodos ?? false)) ?>;
    window.organigramaDepartamentosCatalogo = <?= json_encode($DepartamentosOrganigrama ?? [], JSON_UNESCAPED_UNICODE) ?>;

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
        let organigramaRowsBase = [];
        var personaSearchSelect = null;
        var dirSearchSelect = null;
        var areaSearchSelect = null;
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

        function normalizarTextoOrg(valor, fallback) {
            var texto = String(valor || '').replace(/\s+/g, ' ').trim();
            return texto || fallback || '';
        }

        function refrescarSelectBuscadorOrg(instance) {
            if (instance && typeof instance.refresh === 'function') {
                instance.refresh();
            }
        }

        function setVisibleOrgSlot(id, visible) {
            var el = document.getElementById(id);
            if (!el) return;
            el.classList.toggle('d-none', !visible);
        }

        function actualizarVisibilidadFiltrosOrganigrama() {
            var dirValue = document.getElementById("dirSelect")?.value || "";
            var areaValue = document.getElementById("areaSelect")?.value || "";
            var depValue = document.getElementById("depSelect")?.value || "";

            setVisibleOrgSlot("areaSelectSlot", !!dirValue);
            setVisibleOrgSlot("depSelectSlot", !!dirValue && !!areaValue);
            setVisibleOrgSlot("personaSelectSlot", !!dirValue && !!areaValue && !!depValue);
        }

        function resetSeleccionOrganigrama(mensajePersona) {
            var personaSelect = document.getElementById("personaSelect");
            var puestoSlot = document.getElementById("personaPuestoSlot");
            var puestoSelect = document.getElementById("personaPuestoSelect");

            if (puestoSlot) puestoSlot.style.display = "none";
            destroyPuestoSearchIfAny();
            if (puestoSelect) {
                puestoSelect.innerHTML = "<option value=\"\">-- Selecciona un puesto --</option>";
                puestoSelect.value = "";
            }
            destroyPersonaSearchIfAny();
            if (personaSelect) {
                personaSelect.innerHTML = "<option value=\"\">" + (mensajePersona || "-- Selecciona un departamento primero --") + "</option>";
                personaSelect.disabled = true;
                personaSelect.value = "";
            }

            document.getElementById("resultado").innerHTML = "";
            document.getElementById("orgTituloSeleccion").textContent = "";
            document.getElementById("chart").innerHTML = "";
            document.getElementById("personaLevel1Slot").innerHTML = "";
            document.getElementById("personaLevelsContainer").innerHTML = "";
            organigramaRows = [];
            organigramaRowsBase = [];
            mostrarLoadingOrganigrama(false);
            actualizarHistorialPuestos();
            document.getElementById("btnGuardarOrganigrama").disabled = true;
            actualizarVisibilidadFiltrosOrganigrama();
        }

        function renderDireccionesOrganigrama() {
            var dirSelect = document.getElementById("dirSelect");
            var areaSelect = document.getElementById("areaSelect");
            var depSelect = document.getElementById("depSelect");
            depSelect.innerHTML = "<option value=\"\">Seleccione area primero</option>";
            depSelect.disabled = true;
            var catalogo = Array.isArray(window.organigramaDepartamentosCatalogo) ? window.organigramaDepartamentosCatalogo : [];
            var mapa = new Map();

            catalogo.forEach(function (dep) {
                var id = String(dep.id_direccion || 0);
                var nombre = normalizarTextoOrg(dep.nombre_direccion, "Sin direccion");
                if (!mapa.has(id)) mapa.set(id, nombre);
            });

            dirSelect.innerHTML = '<option value="">Seleccione direccion</option>';
            Array.from(mapa.entries()).sort(function (a, b) { return a[1].localeCompare(b[1]); }).forEach(function (row) {
                var opt = document.createElement("option");
                opt.value = row[0];
                opt.textContent = row[1];
                dirSelect.appendChild(opt);
            });
            dirSelect.disabled = mapa.size === 0;

            areaSelect.innerHTML = '<option value="">Seleccione direccion primero</option>';
            areaSelect.disabled = true;
            depSelect.innerHTML = '<option value="">Seleccione area primero</option>';
            depSelect.disabled = true;

            refrescarSelectBuscadorOrg(dirSearchSelect);
            refrescarSelectBuscadorOrg(areaSearchSelect);
            refrescarSelectBuscadorOrg(depSearchSelect);
        }

        function renderAreasOrganigrama(idDireccion) {
            var areaSelect = document.getElementById("areaSelect");
            var depSelect = document.getElementById("depSelect");
            var catalogo = Array.isArray(window.organigramaDepartamentosCatalogo) ? window.organigramaDepartamentosCatalogo : [];
            var mapa = new Map();
            var idDir = String(idDireccion || "");

            catalogo.forEach(function (dep) {
                if (String(dep.id_direccion || 0) !== idDir) return;
                var id = String(dep.id_area || 0);
                var nombre = normalizarTextoOrg(dep.nombre_area, "Sin area");
                if (!mapa.has(id)) mapa.set(id, nombre);
            });

            areaSelect.innerHTML = '<option value="">Seleccione area</option>';
            Array.from(mapa.entries()).sort(function (a, b) { return a[1].localeCompare(b[1]); }).forEach(function (row) {
                var opt = document.createElement("option");
                opt.value = row[0];
                opt.textContent = row[1];
                areaSelect.appendChild(opt);
            });
            areaSelect.disabled = !idDir || mapa.size === 0;

            depSelect.innerHTML = '<option value="">Seleccione area primero</option>';
            depSelect.disabled = true;
            refrescarSelectBuscadorOrg(areaSearchSelect);
            refrescarSelectBuscadorOrg(depSearchSelect);
        }

        function renderDepartamentosOrganigrama(idArea) {
            var depSelect = document.getElementById("depSelect");
            var catalogo = Array.isArray(window.organigramaDepartamentosCatalogo) ? window.organigramaDepartamentosCatalogo : [];
            var mapa = new Map();
            var idAreaStr = String(idArea || "");

            catalogo.forEach(function (dep) {
                if (String(dep.id_area || 0) !== idAreaStr) return;
                var id = String(dep.id || "");
                if (!id) return;
                var nombre = normalizarTextoOrg(dep.nombre, "Departamento");
                if (!mapa.has(id)) mapa.set(id, nombre);
            });

            depSelect.innerHTML = '<option value="">Seleccione departamento</option>';
            Array.from(mapa.entries()).sort(function (a, b) { return a[1].localeCompare(b[1]); }).forEach(function (row) {
                var opt = document.createElement("option");
                opt.value = row[0];
                opt.textContent = row[1];
                depSelect.appendChild(opt);
            });
            depSelect.disabled = !idAreaStr || mapa.size === 0;
            refrescarSelectBuscadorOrg(depSearchSelect);
        }

        function getSubordinadosDirectos(idJefe) {
            return organigramaRows.filter(function (r) {
                if (!r || r.id == null) return false;
                if (String(r.id) === String(idJefe)) return false;
                return String(r.jefe) === String(idJefe);
            });
        }

        function esIdVacanteOrganigrama(id) {
            return /^vacante-\d+$/.test(String(id || ''));
        }

        function construirSubarbolOrganigramaLocal(rootId) {
            var rootKey = String(rootId || '');
            var baseRows = Array.isArray(organigramaRowsBase) && organigramaRowsBase.length ? organigramaRowsBase : [];
            var currentRows = Array.isArray(organigramaRows) ? organigramaRows : [];
            var baseTieneRoot = baseRows.some(function (r) { return r && String(r.id) === rootKey; });
            var sourceRows = baseTieneRoot ? baseRows : currentRows;
            var byParent = {};
            var byId = {};

            sourceRows.forEach(function (r) {
                if (!r || r.id == null) return;
                var id = String(r.id);
                byId[id] = r;
                var parent = r.jefe == null ? null : String(r.jefe);
                if (!byParent[parent]) byParent[parent] = [];
                byParent[parent].push(r);
            });

            if (!byId[rootKey]) return [];

            var result = [];
            var visited = {};
            function visit(id, parentForResult) {
                var key = String(id);
                var row = byId[key];
                if (!row || visited[key]) return;
                visited[key] = true;
                result.push({
                    id: row.id,
                    id_vacante: row.id_vacante || null,
                    id_departamento: row.id_departamento || null,
                    id_puesto: row.id_puesto || null,
                    id_jefe: row.id_jefe || null,
                    nombre_vacante: row.nombre_vacante || '',
                    nombre_puesto_base: row.nombre_puesto_base || '',
                    nombre: row.nombre || '',
                    puesto: row.puesto || row.nombre_puesto || '',
                    jefe: parentForResult,
                    tipo_estado: row.tipo_estado || '',
                    estado_label: row.estado_label || ''
                });
                (byParent[key] || []).forEach(function (child) {
                    visit(child.id, row.id);
                });
            }

            visit(rootKey, null);
            return result;
        }

        function dibujarOrganigramaLocalDesdeRoot(rootId, luegoSubordinados) {
            var chartContainer = document.getElementById("chart");
            var rows = construirSubarbolOrganigramaLocal(rootId);

            mostrarLoadingOrganigrama(false);
            if (rows.length === 0) {
                chartContainer.innerHTML = getOrganigramaMsgGlassHtml("No hay datos para mostrar.");
                document.getElementById("btnGuardarOrganigrama").disabled = true;
                if (luegoSubordinados) luegoSubordinados([]);
                return;
            }

            organigramaRows = rows;
            var root = rows[0] || null;
            var titulo = root ? ("Organigrama: " + (root.nombre || "")) : "";
            if (root && (root.puesto || root.nombre_puesto)) {
                titulo += " / " + (root.puesto || root.nombre_puesto);
            }
            document.getElementById("orgTituloSeleccion").textContent = titulo;
            cargarOrdenPuestosYActualizarHistorial();
            chartContainer.innerHTML = "";
            loadGoogleCharts(function () {
                drawOrgChart(rows, chartContainer);
            });
            document.getElementById("btnGuardarOrganigrama").disabled = false;

            var subs = getSubordinadosDirectos(rootId);
            var seen = {};
            subs = subs.filter(function (r) {
                var id = String(r.id);
                if (seen[id]) return false;
                seen[id] = true;
                return true;
            });
            var subsConEquipo = subs.filter(function (op) { return getSubordinadosDirectos(op.id).length > 0; });
            if (luegoSubordinados) luegoSubordinados(subsConEquipo);
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
                organigramaRowsBase = [];
                document.getElementById("btnGuardarOrganigrama").disabled = true;
                document.getElementById("orgTituloSeleccion").textContent = "";
                actualizarHistorialPuestos();
                if (luegoSubordinados) luegoSubordinados([]);
                return;
            }
            if (esIdVacanteOrganigrama(personaId)) {
                dibujarOrganigramaLocalDesdeRoot(personaId, luegoSubordinados);
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
                        organigramaRowsBase = [];
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
                    organigramaRowsBase = rows.slice();
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
                    organigramaRowsBase = [];
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

        function buscarVacanteOrganigrama(rawId) {
            var id = String(rawId || '');
            var matcher = function (r) {
                if (!r) return false;
                return String(r.id) === id
                    || String(r.id) === ('vacante-' + id)
                    || String(r.id_vacante || '') === id;
            };
            var actual = (organigramaRows || []).find(matcher) || null;
            var base = (organigramaRowsBase || []).find(matcher) || null;
            if (actual && base) {
                return Object.assign({}, base, actual);
            }
            return actual || base || null;
        }

        function llenarJefesOrganigrama(select, opciones) {
            opciones = opciones || {};
            select.innerHTML = '<option value="">Cargando jefes...</option>';
            select.disabled = true;

            return fetch('/CapHum/getJefeDirecto', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id_departamento: opciones.id_departamento || '',
                    id_puesto: opciones.id_puesto || '',
                    id_persona: opciones.id_persona || 0,
                    solo_personas: !!opciones.soloPersonas
                })
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    select.innerHTML = '<option value="">Seleccione un jefe</option>';
                    if (!data.success) {
                        select.innerHTML = '<option value="">No se pudieron cargar jefes</option>';
                        return;
                    }

                    (data.datos || []).forEach(function (jefe) {
                        if (opciones.soloPersonas && jefe.tipo_jefe === 'vacante') return;
                        if (opciones.excluirPersona && String(jefe.id) === String(opciones.excluirPersona)) return;

                        var opt = document.createElement('option');
                        opt.value = jefe.id;
                        opt.textContent = jefe.nombre_completo || ('Jefe #' + jefe.id);
                        if (String(jefe.id) === String(opciones.seleccionado || '')) {
                            opt.selected = true;
                        }
                        select.appendChild(opt);
                    });
                    if (select.options.length <= 1) {
                        select.innerHTML = '<option value="">No hay jefes persona disponibles</option>';
                        return;
                    }
                    select.disabled = false;
                })
                .catch(function () {
                    select.innerHTML = '<option value="">Error al cargar jefes</option>';
                });
        }

        function abrirModalVacanteOrganigrama(rawId) {
            if (!window.puedeEditarTodosOrganigrama) {
                Swal.fire('Sin permiso', 'No tienes permiso para modificar vacantes.', 'warning');
                return;
            }

            var vacante = buscarVacanteOrganigrama(rawId);
            if (!vacante) {
                Swal.fire('Atencion', 'No se encontro la vacante seleccionada.', 'warning');
                return;
            }

            var idVacante = parseInt(vacante.id_vacante || String(vacante.id || '').replace('vacante-', ''), 10);
            var idDepartamento = vacante.id_departamento || document.getElementById('depSelect')?.value || '';
            var idPuesto = vacante.id_puesto || '';
            var select = document.getElementById('vacante_jefe_id_jefe');
            var selectEliminar = document.getElementById('vacante_eliminar_id_jefe');
            var input = document.getElementById('vacante_jefe_id_vacante');
            var inputDepto = document.getElementById('vacante_jefe_id_departamento');
            var inputPuesto = document.getElementById('vacante_jefe_id_puesto');
            var inputSuperior = document.getElementById('vacante_jefe_id_superior');
            var inputNombre = document.getElementById('vacante_nombre_vacante');
            var resumen = document.getElementById('vacante_jefe_resumen');
            var hintSuperior = document.getElementById('vacante_jefe_superior_hint');

            if (!idVacante || !idDepartamento) {
                Swal.fire('Atencion', 'La vacante no tiene informacion suficiente para cambiar el jefe.', 'warning');
                return;
            }

            input.value = idVacante;
            if (inputDepto) inputDepto.value = idDepartamento;
            if (inputPuesto) inputPuesto.value = idPuesto;
            if (inputSuperior) inputSuperior.value = vacante.id_jefe || '';
            var nombreEditableVacante = (vacante.nombre_vacante || vacante.nombre || vacante.puesto || '').replace(/\s*\(\s*vacante\s*\)\s*$/i, '').trim();
            if (inputNombre) inputNombre.value = nombreEditableVacante;
            resumen.textContent = 'Vacante #' + idVacante + ' - ' + (nombreEditableVacante ? (nombreEditableVacante + ' (Vacante)') : (vacante.puesto || 'Sin puesto'));
            if (hintSuperior) {
                hintSuperior.textContent = vacante.id_jefe
                    ? 'Los subordinados quedaran ligados al jefe superior actual de esta vacante.'
                    : 'Esta vacante no tiene jefe superior; elige otra persona antes de eliminar.';
            }
            select.innerHTML = '<option value="">Cargando jefes...</option>';
            select.disabled = true;
            if (selectEliminar) {
                selectEliminar.classList.add('d-none');
                selectEliminar.innerHTML = '<option value="">Cargando jefes...</option>';
                selectEliminar.disabled = true;
            }
            var radioSuperior = document.getElementById('vacante_modo_jefe_superior');
            if (radioSuperior) radioSuperior.checked = true;

            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalCambiarJefeVacante')).show();

            llenarJefesOrganigrama(select, {
                id_departamento: idDepartamento,
                id_puesto: idPuesto,
                seleccionado: vacante.id_jefe || '',
                soloPersonas: true
            });
            if (selectEliminar) {
                llenarJefesOrganigrama(selectEliminar, {
                    id_departamento: idDepartamento,
                    id_puesto: idPuesto,
                    soloPersonas: true
                });
            }
        }

        document.querySelectorAll('input[name="vacante_modo_eliminar"]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                var selectEliminar = document.getElementById('vacante_eliminar_id_jefe');
                if (!selectEliminar) return;
                selectEliminar.classList.toggle('d-none', this.value !== 'jefe_destino');
            });
        });

        function manejarRespuestaJsonOrganigrama(res, fallbackError) {
            return res.text().then(function (txt) {
                var data = null;
                try {
                    data = txt ? JSON.parse(txt) : null;
                } catch (e) {
                    throw new Error(res.status === 401 || res.redirected
                        ? 'Sesion expirada. Vuelve a iniciar sesion.'
                        : (fallbackError || 'Respuesta no valida del servidor.'));
                }
                if (!res.ok) {
                    throw new Error((data && (data.mensaje || data.message || data.error)) || fallbackError || 'No se pudo completar la accion.');
                }
                return data || {};
            });
        }

        function refrescarOrganigramaActual() {
            var rootId = obtenerIdRootActual();
            if (rootId) {
                cargarOrganigramaDesdeRoot(rootId);
            }
        }

        function guardarNombreVacanteOrganigrama() {
            var idVacante = document.getElementById('vacante_jefe_id_vacante')?.value || '';
            var nombreVacante = (document.getElementById('vacante_nombre_vacante')?.value || '').replace(/\s*\(\s*vacante\s*\)\s*$/i, '').trim();
            var btnGuardar = document.querySelector('#modalCambiarJefeVacante button[onclick="guardarNombreVacanteOrganigrama()"]');

            if (!idVacante || nombreVacante.length < 3) {
                Swal.fire('Falta informacion', 'Escribe un nombre valido para la vacante.', 'warning');
                return;
            }

            if (btnGuardar) {
                btnGuardar.disabled = true;
                btnGuardar.dataset.originalText = btnGuardar.innerHTML;
                btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Guardando...';
            }

            fetch('/CapHum/actualizarNombreVacante', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Front-Request': 'true'
                },
                body: JSON.stringify({ id_vacante: idVacante, nombre_vacante: nombreVacante })
            })
                .then(function (res) { return manejarRespuestaJsonOrganigrama(res, 'No se pudo actualizar el nombre de la vacante.'); })
                .then(function (data) {
                    if (!data.success) {
                        Swal.fire('Error', data.mensaje || 'No se pudo actualizar el nombre.', 'error');
                        return;
                    }
                    Swal.fire('Listo', data.mensaje || 'Nombre actualizado.', 'success');
                    refrescarOrganigramaActual();
                })
                .catch(function (err) {
                    console.error('actualizarNombreVacante:', err);
                    Swal.fire('Error', err && err.message ? err.message : 'No se pudo actualizar el nombre.', 'error');
                })
                .finally(function () {
                    if (btnGuardar) {
                        btnGuardar.disabled = false;
                        btnGuardar.innerHTML = btnGuardar.dataset.originalText || '<i class="fa fa-save me-1"></i>Nombre';
                    }
                });
        }

        function guardarJefeVacanteOrganigrama() {
            var idVacante = document.getElementById('vacante_jefe_id_vacante')?.value || '';
            var idJefe = document.getElementById('vacante_jefe_id_jefe')?.value || '';
            var btnGuardar = document.querySelector('#modalCambiarJefeVacante button[onclick="guardarJefeVacanteOrganigrama()"]');

            if (!idVacante || !idJefe) {
                Swal.fire('Falta informacion', 'Selecciona el jefe destino.', 'warning');
                return;
            }

            if (btnGuardar) {
                btnGuardar.disabled = true;
                btnGuardar.dataset.originalText = btnGuardar.innerHTML;
                btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Guardando...';
            }

            fetch('/CapHum/actualizarJefeVacante', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Front-Request': 'true'
                },
                body: JSON.stringify({ id_vacante: idVacante, id_jefe: idJefe })
            })
                .then(function (res) { return manejarRespuestaJsonOrganigrama(res, 'No se pudo actualizar la vacante.'); })
                .then(function (data) {
                    if (!data.success) {
                        Swal.fire('Error', data.mensaje || 'No se pudo actualizar la vacante.', 'error');
                        return;
                    }
                    Swal.fire('Listo', data.mensaje || 'Jefe actualizado.', 'success');
                    refrescarOrganigramaActual();
                })
                .catch(function (err) {
                    console.error('actualizarJefeVacante:', err);
                    Swal.fire('Error', err && err.message ? err.message : 'No se pudo actualizar la vacante.', 'error');
                })
                .finally(function () {
                    if (btnGuardar) {
                        btnGuardar.disabled = false;
                        btnGuardar.innerHTML = btnGuardar.dataset.originalText || '<i class="fa fa-save me-1"></i>Jefe';
                    }
                });
        }

        function eliminarVacanteOrganigrama() {
            var idVacante = document.getElementById('vacante_jefe_id_vacante')?.value || '';
            var modoRadio = document.querySelector('input[name="vacante_modo_eliminar"]:checked');
            var modo = modoRadio ? modoRadio.value : '';
            var idJefeDestino = modo === 'jefe_superior'
                ? (document.getElementById('vacante_jefe_id_superior')?.value || '')
                : (document.getElementById('vacante_eliminar_id_jefe')?.value || '');
            var btnEliminar = document.querySelector('#modalCambiarJefeVacante .btn.btn-danger');

            if (!idVacante || !modo) {
                Swal.fire('Falta informacion', 'Selecciona la vacante y el modo de movimiento.', 'warning');
                return;
            }
            if (!idJefeDestino) {
                Swal.fire('Falta informacion', 'Selecciona un jefe destino para mover los subordinados.', 'warning');
                return;
            }

            Swal.fire({
                title: 'Eliminar vacante',
                text: 'Los subordinados se moveran al jefe destino seleccionado. Esta accion dejara la vacante como eliminada.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Si, eliminar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#d33'
            }).then(function (result) {
                if (!result.isConfirmed) return;

                if (btnEliminar) {
                    btnEliminar.disabled = true;
                    btnEliminar.dataset.originalText = btnEliminar.innerHTML;
                    btnEliminar.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Eliminando...';
                }

                fetch('/CapHum/eliminarVacanteOrganigrama', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Front-Request': 'true'
                    },
                    body: JSON.stringify({
                        id_vacante: idVacante,
                        modo_movimiento: modo,
                        id_jefe_destino: idJefeDestino
                    })
                })
                    .then(function (res) { return manejarRespuestaJsonOrganigrama(res, 'No se pudo eliminar la vacante.'); })
                    .then(function (data) {
                        if (!data.success) {
                            Swal.fire('Error', data.mensaje || 'No se pudo eliminar la vacante.', 'error');
                            return;
                        }
                        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalCambiarJefeVacante')).hide();
                        Swal.fire('Listo', data.mensaje || 'Vacante eliminada.', 'success');
                        refrescarOrganigramaActual();
                    })
                    .catch(function (err) {
                        console.error('eliminarVacanteOrganigrama:', err);
                        Swal.fire('Error', err && err.message ? err.message : 'No se pudo eliminar la vacante.', 'error');
                    })
                    .finally(function () {
                        if (btnEliminar) {
                            btnEliminar.disabled = false;
                            btnEliminar.innerHTML = btnEliminar.dataset.originalText || '<i class="fa fa-trash me-1"></i>Eliminar vacante';
                        }
                });
            });
        }

        window.guardarNombreVacanteOrganigrama = guardarNombreVacanteOrganigrama;
        window.guardarJefeVacanteOrganigrama = guardarJefeVacanteOrganigrama;
        window.eliminarVacanteOrganigrama = eliminarVacanteOrganigrama;

        function abrirModalPersonaJefeOrganigrama(rawId) {
            if (!window.puedeEditarTodosOrganigrama) {
                Swal.fire('Sin permiso', 'No tienes permiso para modificar jefes.', 'warning');
                return;
            }

            var idPersona = parseInt(rawId, 10);
            if (!idPersona) {
                Swal.fire('Atencion', 'No se encontro la persona seleccionada.', 'warning');
                return;
            }

            var select = document.getElementById('persona_jefe_id_jefe');
            var inputPersona = document.getElementById('persona_jefe_id_persona');
            var inputDepartamento = document.getElementById('persona_jefe_id_departamento');
            var inputPuesto = document.getElementById('persona_jefe_id_puesto');
            var resumen = document.getElementById('persona_jefe_resumen');

            inputPersona.value = idPersona;
            resumen.textContent = 'Cargando persona...';
            select.innerHTML = '<option value="">Cargando jefes...</option>';
            select.disabled = true;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalCambiarJefePersona')).show();

            fetch('/CapHum/getDetalles', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ idPersona: idPersona })
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (!data.success) {
                        Swal.fire('Error', data.mensaje || 'No se pudo cargar la persona.', 'error');
                        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalCambiarJefePersona')).hide();
                        return;
                    }

                    var persona = data.datos || {};
                    var nombre = [
                        persona.nombres || '',
                        persona.segundo_nombre || '',
                        persona.apellidop || '',
                        persona.apellidom || ''
                    ].join(' ').replace(/\s+/g, ' ').trim();

                    inputDepartamento.value = persona.id_departamento || '';
                    inputPuesto.value = persona.id_puesto || '';
                    resumen.textContent = (persona.numero_empleado ? '# ' + persona.numero_empleado + ' - ' : '') + (nombre || 'Persona');

                    if (!persona.id_departamento) {
                        select.innerHTML = '<option value="">La persona no tiene departamento</option>';
                        return;
                    }

                    llenarJefesOrganigrama(select, {
                        id_departamento: persona.id_departamento,
                        id_puesto: persona.id_puesto || '',
                        id_persona: idPersona,
                        excluirPersona: idPersona,
                        seleccionado: persona.id_jefe || ''
                    });
                })
                .catch(function () {
                    Swal.fire('Error', 'No se pudo cargar la persona.', 'error');
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalCambiarJefePersona')).hide();
                });
        }

        function guardarJefePersonaOrganigrama() {
            var idPersona = document.getElementById('persona_jefe_id_persona')?.value || '';
            var idJefe = document.getElementById('persona_jefe_id_jefe')?.value || '';

            if (!idPersona || !idJefe) {
                Swal.fire('Falta informacion', 'Selecciona el jefe destino.', 'warning');
                return;
            }

            fetch('/CapHum/actualizarJefePersonaOrganigrama', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_persona: idPersona, id_jefe: idJefe })
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (!data.success) {
                        Swal.fire('Error', data.mensaje || 'No se pudo actualizar el jefe.', 'error');
                        return;
                    }
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalCambiarJefePersona')).hide();
                    Swal.fire('Listo', data.mensaje || 'Jefe actualizado.', 'success');
                    var rootId = obtenerIdRootActual();
                    if (rootId) {
                        cargarOrganigramaDesdeRoot(rootId);
                    }
                })
                .catch(function () {
                    Swal.fire('Error', 'No se pudo actualizar el jefe.', 'error');
                });
        }

        window.guardarJefeVacanteOrganigrama = guardarJefeVacanteOrganigrama;
        window.guardarJefePersonaOrganigrama = guardarJefePersonaOrganigrama;

        /* ============================= */
        /*   SELECT DIRECCION / AREA      */
        /* ============================= */
        document.getElementById("dirSelect").addEventListener("change", function () {
            renderAreasOrganigrama(this.value);
            resetSeleccionOrganigrama("-- Selecciona un departamento primero --");
            actualizarVisibilidadFiltrosOrganigrama();
        });

        document.getElementById("areaSelect").addEventListener("change", function () {
            renderDepartamentosOrganigrama(this.value);
            resetSeleccionOrganigrama("-- Selecciona un departamento primero --");
            actualizarVisibilidadFiltrosOrganigrama();
        });

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
            organigramaRowsBase = [];
            mostrarLoadingOrganigrama(false);
            actualizarHistorialPuestos();
            document.getElementById("btnGuardarOrganigrama").disabled = true;
            actualizarVisibilidadFiltrosOrganigrama();

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
                organigramaRowsBase = [];
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
            var dirSelect = document.getElementById("dirSelect");
            var areaSelect = document.getElementById("areaSelect");
            dirSelect.value = "";
            if (dirSearchSelect) dirSearchSelect.refresh();
            areaSelect.innerHTML = "<option value=\"\">Seleccione direccion primero</option>";
            areaSelect.disabled = true;
            if (areaSearchSelect) areaSearchSelect.refresh();
            var depSelect = document.getElementById("depSelect");
            depSelect.selectedIndex = 0; // "Seleccione una opción"
            depSelect.innerHTML = "<option value=\"\">Seleccione area primero</option>";
            depSelect.disabled = true;
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
            organigramaRowsBase = [];
            mostrarLoadingOrganigrama(false);
            actualizarHistorialPuestos();
            document.getElementById("btnGuardarOrganigrama").disabled = true;
            try {
                scale = window.innerWidth < 768 ? 0.65 : 0.82;
                var chartLimpiar = document.getElementById("chart");
                if (chartLimpiar) chartLimpiar.style.transform = `scale(${scale})`;
            } catch (eLim) {}
            actualizarVisibilidadFiltrosOrganigrama();
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
                var tipoEstado = r.tipo_estado || '';
                var estadoLabel = r.estado_label ? escapeHtml(r.estado_label) : '';
                var wrapperClass = 'org-nombre';
                var estadoAttr = ' data-org-id="' + escapeHtml(String(r.id)) + '"'
                    + (tipoEstado ? ' data-org-estado="' + escapeHtml(tipoEstado) + '"' : '');
                var badgeHtml = '';
                if (tipoEstado === 'vacante') {
                    wrapperClass += ' org-estado-vacante text-dark';
                    badgeHtml = '<div class="badge text-bg-warning rounded-1 mt-1">Vacante</div>';
                } else if (tipoEstado === 'ausencia') {
                    wrapperClass += ' org-estado-ausencia text-dark';
                    badgeHtml = '<div class="badge bg-white text-danger rounded-1 mt-1">' + (estadoLabel || 'Ausencia') + '</div>';
                } else if (tipoEstado === 'baja') {
                    wrapperClass += ' org-estado-baja text-dark';
                    badgeHtml = '<div class="badge text-bg-secondary rounded-1 mt-1">Baja</div>';
                }
                data.addRow([
                    {
                        v: String(r.id),
                        f: '<div class="' + wrapperClass + '"' + estadoAttr + ' style="font-weight:bold;cursor:pointer;color:#2a6ebb">' +
                            nombreSafe +
                            (puestoSafe ? '<div class="org-puesto">' + puestoSafe + '</div>' : '') +
                            badgeHtml +
                            '</div>',
                        p: { collapsed: r.jefe !== null }
                    },
                    r.jefe
                ]);
            });

            const chart = new google.visualization.OrgChart(container);

            function aplicarColorEstadoNodos() {
                container.querySelectorAll('.org-estado-vacante, .org-estado-ausencia, .org-estado-baja').forEach(function (el) {
                    var node = el.closest('.google-visualization-orgchart-node');
                    if (!node) return;
                    var esVacante = el.classList.contains('org-estado-vacante');
                    var esAusencia = el.classList.contains('org-estado-ausencia');
                    var esBaja = el.classList.contains('org-estado-baja');
                    node.dataset.orgId = el.dataset.orgId || '';
                    node.dataset.orgEstado = el.dataset.orgEstado || '';
                    node.style.cursor = 'pointer';
                    node.style.setProperty('background-image', 'none', 'important');
                    node.style.setProperty('background', 'transparent', 'important');
                    node.querySelectorAll('div, table, tbody, tr, td').forEach(function (child) {
                        if (child.classList.contains('badge')) return;
                        child.style.setProperty('background', 'transparent', 'important');
                        child.style.setProperty('background-image', 'none', 'important');
                    });

                    if (esVacante) {
                        node.classList.add('bg-warning-subtle', 'border', 'border-warning', 'text-dark');
                        node.style.setProperty('background-color', 'var(--bs-warning-bg-subtle)', 'important');
                    } else if (esAusencia) {
                        node.classList.add('bg-label-danger', 'border', 'border-danger-subtle', 'text-dark');
                        node.style.setProperty('background-color', 'color-mix(in sRGB, var(--bs-paper-bg) var(--bs-bg-label-tint-amount), var(--bs-danger))', 'important');
                    } else if (esBaja) {
                        node.classList.add('bg-secondary-subtle', 'border', 'border-secondary-subtle', 'text-dark');
                        node.style.setProperty('background-color', 'var(--bs-secondary-bg-subtle)', 'important');
                    }
                });
            }

            google.visualization.events.addListener(chart, 'ready', () => {
                aplicarColorEstadoNodos();
                ajustarEscalaChart();
            });

            google.visualization.events.addListener(chart, 'select', function () {
                var selection = chart.getSelection();
                if (!selection.length || selection[0].row == null) return;
                var idSeleccionado = data.getValue(selection[0].row, 0);
                var fila = rows.find(function (r) {
                    return String(r.id) === String(idSeleccionado);
                });
                if (fila && fila.tipo_estado === 'vacante') {
                    chart.setSelection([]);
                    abrirModalVacanteOrganigrama(idSeleccionado);
                } else if (fila) {
                    chart.setSelection([]);
                    abrirModalPersonaJefeOrganigrama(idSeleccionado);
                }
            });

            chart.draw(data, {
                allowHtml: true,
                allowCollapse: true
            });

            if (container.__organigramaVacanteClickHandler) {
                container.removeEventListener('click', container.__organigramaVacanteClickHandler, true);
            }
            container.__organigramaVacanteClickHandler = function (event) {
                var node = event.target.closest('.google-visualization-orgchart-node');
                if (!node || !node.dataset.orgId) return;
                event.preventDefault();
                event.stopPropagation();
                if (node.dataset.orgEstado === 'vacante') {
                    abrirModalVacanteOrganigrama(node.dataset.orgId);
                } else {
                    abrirModalPersonaJefeOrganigrama(node.dataset.orgId);
                }
            };
            container.addEventListener('click', container.__organigramaVacanteClickHandler, true);

            setTimeout(aplicarColorEstadoNodos, 50);
            setTimeout(aplicarColorEstadoNodos, 250);
        }

        /* ============================= */
        /*   ESCALA Y SCROLL             */
        /* ============================= */
        function ajustarEscalaChart() {
            const chartDiv = document.getElementById("chart");
            const escala = window.innerWidth < 768 ? 0.65 : 0.82;

            chartDiv.style.transform = 'scale(1)'; // reset
            chartDiv.style.transform = `scale(${escala})`;
            chartDiv.style.transformOrigin = 'top center';
            if (typeof scale !== 'undefined') scale = escala;
            centrarOrganigrama();
        }

        function centrarOrganigrama() {
            const scroll = document.querySelector("#chart-container .organigrama-chart-scroll");
            const chartDiv = document.getElementById("chart");
            if (!scroll || !chartDiv) return;
            const tabla = chartDiv.querySelector("table");
            if (tabla) tabla.classList.add("mx-auto");
            requestAnimationFrame(function () {
                const nodoRaiz = chartDiv.querySelector(".google-visualization-orgchart-node .org-nombre")?.closest(".google-visualization-orgchart-node");
                if (!nodoRaiz) {
                    scroll.scrollLeft = 0;
                    return;
                }
                const scrollRect = scroll.getBoundingClientRect();
                const nodoRect = nodoRaiz.getBoundingClientRect();
                const centroNodo = nodoRect.left + (nodoRect.width / 2);
                const ajusteVisual = Math.min(90, scroll.clientWidth * 0.06);
                const centroScroll = scrollRect.left + (scroll.clientWidth / 2) + ajusteVisual;
                const destino = scroll.scrollLeft + centroNodo - centroScroll;
                const maxScroll = Math.max(0, scroll.scrollWidth - scroll.clientWidth);
                scroll.scrollLeft = Math.max(0, Math.min(maxScroll, destino));
            });
        }
        window.centrarOrganigrama = centrarOrganigrama;

        function inicializarPanOrganigrama() {
            const scroll = document.querySelector("#chart-container .organigrama-chart-scroll");
            if (!scroll || scroll.dataset.panReady === "1") return;

            scroll.dataset.panReady = "1";
            let isDown = false;
            let moved = false;
            let startX = 0;
            let startY = 0;
            let scrollLeft = 0;
            let scrollTop = 0;

            scroll.addEventListener("pointerdown", function (e) {
                if (e.button !== 0) return;
                isDown = true;
                moved = false;
                startX = e.clientX;
                startY = e.clientY;
                scrollLeft = scroll.scrollLeft;
                scrollTop = scroll.scrollTop;
                scroll.classList.add("is-panning");
                scroll.setPointerCapture(e.pointerId);
            });

            scroll.addEventListener("pointermove", function (e) {
                if (!isDown) return;
                const dx = e.clientX - startX;
                const dy = e.clientY - startY;
                if (Math.abs(dx) > 3 || Math.abs(dy) > 3) moved = true;
                if (!moved) return;
                e.preventDefault();
                scroll.scrollLeft = scrollLeft - dx;
                scroll.scrollTop = scrollTop - dy;
            });

            function terminarPan(e) {
                if (!isDown) return;
                isDown = false;
                scroll.classList.remove("is-panning");
                try { scroll.releasePointerCapture(e.pointerId); } catch (err) {}
            }

            scroll.addEventListener("pointerup", terminarPan);
            scroll.addEventListener("pointercancel", terminarPan);
            scroll.addEventListener("pointerleave", terminarPan);

            scroll.addEventListener("click", function (e) {
                if (!moved) return;
                e.preventDefault();
                e.stopPropagation();
                moved = false;
            }, true);
        }

        dirSearchSelect = new SearchableSelect(document.getElementById("dirSelect"));
        areaSearchSelect = new SearchableSelect(document.getElementById("areaSelect"));
        depSearchSelect = new SearchableSelect(document.getElementById("depSelect"));
        renderDireccionesOrganigrama();
        actualizarVisibilidadFiltrosOrganigrama();
        inicializarPanOrganigrama();

        window.addEventListener('resize', ajustarEscalaChart);

    });

    let scale = window.innerWidth < 768 ? 0.65 : 0.82; // escala inicial
    const chart = document.getElementById('chart');
    const STEP = 0.01; // paso más pequeño

    document.getElementById('zoom-in').addEventListener('click', () => {
        scale = Math.min(1.4, scale + 0.05);
        chart.style.transform = `scale(${scale})`;
        chart.style.transformOrigin = 'top center';
        if (window.centrarOrganigrama) window.centrarOrganigrama();
    });

    document.getElementById('zoom-out').addEventListener('click', () => {
        scale = Math.max(0.45, scale - 0.05);
        chart.style.transform = `scale(${scale})`;
        chart.style.transformOrigin = 'top center';
        if (window.centrarOrganigrama) window.centrarOrganigrama();
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
