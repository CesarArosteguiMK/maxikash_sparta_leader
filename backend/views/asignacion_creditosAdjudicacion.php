<style>
    /* ===================================================================
       ADJUDICACIÓN — Paleta ámbar #f59e0b (diferenciación vs. Despachos)
       =================================================================== */
    :root {
        --adj-amber:        #f59e0b;
        --adj-amber-dark:   #d97706;
        --adj-amber-light:  #fffbeb;
        --adj-amber-border: #fcd34d;
        --adj-amber-text:   #92400e;
    }

    /* Franja de color en cards */
    .adj-card-accent { border-top: 3px solid var(--adj-amber) !important; }

    /* Badge de paso de workflow */
    .adj-step-badge {
        display: inline-flex; align-items: center; justify-content: center;
        width: 1.5rem; height: 1.5rem; background: var(--adj-amber);
        color: #1a1a1a; border-radius: 50%; font-size: 0.72rem; font-weight: 700;
        flex-shrink: 0; line-height: 1;
    }

    /* Mini-cards para info del responsable */
    .adj-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.5rem;
    }
    .adj-info-card {
        background: var(--adj-amber-light);
        border: 1px solid var(--adj-amber-border);
        border-radius: 0.5rem;
        padding: 0.625rem 0.75rem;
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
    }
    .adj-info-card i {
        color: var(--adj-amber);
        font-size: 0.875rem;
        margin-top: 3px;
        flex-shrink: 0;
        width: 0.875rem;
        text-align: center;
    }
    .adj-info-card-label {
        font-size: 0.67rem;
        color: var(--adj-amber-text);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
        margin-bottom: 2px;
    }
    .adj-info-card-value {
        font-size: 0.8125rem;
        color: #1f2937;
        font-weight: 500;
        word-break: break-word;
        line-height: 1.3;
    }

    /* Alerta contextual ámbar */
    .alert-adj-amber {
        background: var(--adj-amber-light);
        border: 1px solid var(--adj-amber-border);
        border-left: 4px solid var(--adj-amber);
        color: var(--adj-amber-text);
        border-radius: 0.375rem;
        padding: 0.75rem 1rem;
    }
    .alert-adj-amber i { color: var(--adj-amber); }

    /* Botón ámbar primario */
    .btn-adj-amber {
        background: var(--adj-amber);
        border: none;
        color: #1a1a1a;
        font-weight: 500;
        transition: all 0.2s ease;
    }
    .btn-adj-amber:hover, .btn-adj-amber:focus {
        background: var(--adj-amber-dark);
        color: #1a1a1a;
        box-shadow: 0 4px 8px rgba(245,158,11,0.35);
        transform: translateY(-1px);
    }

    .credit-result-box {
        display: none;
    }

    .credit-result-box.show {
        display: block;
    }

    .metric-card {
        transition: all 0.3s ease;
    }

    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    /* Estilos para stack de créditos */
    #adj-creditos-stack {
        max-height: 520px;
        overflow-y: auto;
        overflow-x: hidden;
        padding-right: 0.25rem;
    }

    #adj-creditos-stack::-webkit-scrollbar {
        width: 6px;
    }

    #adj-creditos-stack::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    #adj-creditos-stack::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 10px;
    }

    #adj-creditos-stack::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    .credit-card-item {
        animation: slideInDown 0.3s ease-out;
        margin-bottom: 1rem;
    }

    @keyframes slideInDown {
        from { opacity: 0; transform: translateY(-20px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .credit-card-item.removing {
        animation: slideOutUp 0.3s ease-out;
    }

    @keyframes slideOutUp {
        to { opacity: 0; transform: translateY(-20px); }
    }

    /* Botones con gradientes */
    .btn-gradient-success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border: none;
        color: white;
        transition: all 0.3s ease;
    }

    .btn-gradient-success:hover {
        background: linear-gradient(135deg, #218838 0%, #1a9e7a 100%);
        transform: scale(1.05);
        box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
        color: white;
    }

    .btn-gradient-danger {
        background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
        border: none;
        color: white;
        transition: all 0.3s ease;
    }

    .btn-gradient-danger:hover {
        background: linear-gradient(135deg, #c82333 0%, #e8590c 100%);
        transform: scale(1.05);
        box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
        color: white;
    }

    /* Estilos para información del responsable tipo labels */
    .info-compact {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .info-compact li {
        display: grid;
        grid-template-columns: 2rem 1fr;
        align-items: start;
        gap: 0.75rem;
        padding: 0.625rem 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .info-compact li:last-child {
        border-bottom: none;
    }

    .info-compact i {
        font-size: 1.1rem;
        color: var(--adj-amber);
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
    }

    .info-compact .info-label {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
        min-width: 0;
    }

    .info-compact .info-label span:first-child {
        color: #697a8d;
        font-size: 0.875rem;
        white-space: nowrap;
    }

    .info-compact .info-label span:last-child {
        color: #566a7f;
        font-weight: 600;
        text-align: right;
        flex: 1;
        min-width: 0;
        word-wrap: break-word;
    }

    /* Estilos para Select con Búsqueda */
    .select-search-wrapper {
        position: relative;
        width: 100%;
    }

    .select-search-display {
        cursor: pointer;
        padding: 0.5rem 1rem;
        border: 1px solid #d9dee3;
        border-radius: 0.375rem;
        background-color: #fff;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: border-color 0.2s;
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        color: #697a8d;
    }

    .select-search-display:hover {
        border-color: var(--adj-amber);
    }

    .select-search-display.active {
        border-color: var(--adj-amber);
        box-shadow: 0 0 0 0.2rem rgba(245, 158, 11, 0.25);
    }

    .select-search-arrow {
        transition: transform 0.3s;
        color: #697a8d;
    }

    .select-search-arrow.open {
        transform: rotate(180deg);
    }

    .select-search-dropdown {
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        background-color: #fff;
        border: 1px solid #d9dee3;
        border-radius: 0.375rem;
        box-shadow: 0 0.25rem 1rem rgba(161, 172, 184, 0.45);
        z-index: 1050;
        max-height: 0;
        overflow: hidden;
        opacity: 0;
        transition: max-height 0.3s ease, opacity 0.3s ease;
    }

    .select-search-dropdown.show {
        max-height: 400px;
        opacity: 1;
    }

    .select-search-input {
        width: 100%;
        padding: 0.75rem 1rem;
        border: none;
        border-bottom: 1px solid #d9dee3;
        outline: none;
        font-size: 0.9375rem;
    }

    .select-search-input:focus {
        background-color: #f8f9fa;
    }

    .select-search-options {
        max-height: 300px;
        overflow-y: auto;
    }

    .select-search-option {
        padding: 0.75rem 1rem;
        cursor: pointer;
        transition: background-color 0.2s;
        font-size: 0.9375rem;
    }

    .select-search-option:hover {
        background-color: #f5f5f9;
    }

    .select-search-option.selected {
        background-color: var(--adj-amber);
        color: #1a1a1a;
    }

    .select-search-option.no-results {
        padding: 1rem;
        text-align: center;
        color: #999;
        cursor: default;
    }

    .select-search-option.no-results:hover {
        background-color: transparent;
    }
</style>

<!-- Título de la página -->
<h4 class="mb-2">
    <i class="fa-solid fa-motorcycle me-2" style="color: var(--adj-amber);"></i>
    Asignación de Créditos para Motos Adjudicadas
</h4>

<!-- Banner contextual del módulo -->
<div class="mb-4 d-flex align-items-center gap-2 px-3 py-2 rounded-2"
     style="background:var(--adj-amber-light); border:1px solid var(--adj-amber-border); border-left:4px solid var(--adj-amber);">
    <i class="fa-solid fa-circle-info" style="color:var(--adj-amber); flex-shrink:0;"></i>
    <span style="font-size:0.875rem; color:var(--adj-amber-text);">
        <strong>Módulo Adjudicación</strong> — Selecciona un gestor, busca créditos y gestiona sus asignaciones de recuperación.
    </span>
</div>

<div class="row g-4 mb-4">
    <!-- PANEL IZQUIERDO -->
    <div class="col-md-4">
        <div class="card h-100 adj-card-accent">
            <div class="card-body">
                <h5 class="card-title mb-4 d-flex align-items-center gap-2">
                    <span class="adj-step-badge">1</span>
                    <i class="fa-solid fa-user-tie me-1"></i>Gestor responsable de adjudicación
                </h5>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label for="adj-select-responsable" class="form-label fw-bold text-muted small mb-0">Seleccionar gestor</label>
                        <button type="button" class="btn btn-sm" onclick="adjAbrirModalRegistrarGestor()"
                                style="background:var(--adj-amber-light); border:1px solid var(--adj-amber-border); color:var(--adj-amber-text); font-size:0.75rem;">
                            <i class="fa-solid fa-user-plus me-1"></i>Registrar gestor
                        </button>
                    </div>
                    <select id="adj-select-responsable" class="form-select">
                    </select>
                </div>

                <!-- Información del Gestor — mini-cards en grid -->
                <div id="adj-info-responsable-container" style="display: none;">
                    <hr class="my-3">
                    <small style="font-size:0.68rem; text-transform:uppercase; letter-spacing:.5px;
                                  font-weight:600; color:var(--adj-amber-text);">
                        Información del gestor
                    </small>
                    <div class="adj-info-grid mt-2">

                        <div class="adj-info-card" id="adj-info-nombre-container" style="grid-column: 1 / -1;">
                            <i class="fa fa-user"></i>
                            <div style="min-width:0;">
                                <div class="adj-info-card-label">Nombre</div>
                                <div class="adj-info-card-value" id="adj-info-nombre">—</div>
                            </div>
                        </div>

                        <div class="adj-info-card" id="adj-info-puesto-container">
                            <i class="fa fa-briefcase"></i>
                            <div style="min-width:0;">
                                <div class="adj-info-card-label">Puesto</div>
                                <div class="adj-info-card-value" id="adj-info-puesto">—</div>
                            </div>
                        </div>

                        <div class="adj-info-card" id="adj-info-telefono-container">
                            <i class="fa fa-phone"></i>
                            <div style="min-width:0; flex:1;">
                                <div class="d-flex align-items-center justify-content-between gap-1">
                                    <div class="adj-info-card-label" style="margin-bottom:0;">Teléfono</div>
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-xs adj-btn-contacto"
                                                onclick="adjAbrirGestionTelefonos()"
                                                title="Registrar / gestionar teléfonos adicionales"
                                                style="font-size:10px; padding:2px 6px; background:var(--adj-amber-light); border:1px solid var(--adj-amber-border); color:var(--adj-amber-text); border-radius:4px;">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                        <button type="button" class="btn btn-xs adj-btn-contacto"
                                                onclick="adjAbrirGestionTelefonos()"
                                                id="adj-btn-ver-telefonos"
                                                title="Ver todos los teléfonos"
                                                style="font-size:10px; padding:2px 6px; background:#f0f4ff; border:1px solid #c7d2fe; color:#3730a3; border-radius:4px; display:none;">
                                            <i class="fa fa-list"></i> <span id="adj-tel-count"></span>
                                        </button>
                                    </div>
                                </div>
                                <div class="adj-info-card-value" id="adj-info-telefono" style="margin-top:2px;">—</div>
                            </div>
                        </div>

                        <div class="adj-info-card" id="adj-info-correo-container" style="grid-column: 1 / -1;">
                            <i class="fa fa-envelope"></i>
                            <div style="min-width:0; flex:1;">
                                <div class="d-flex align-items-center justify-content-between gap-1">
                                    <div class="adj-info-card-label" style="margin-bottom:0;">Correo</div>
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-xs adj-btn-contacto"
                                                onclick="adjAbrirGestionCorreos()"
                                                title="Registrar / gestionar correos adicionales"
                                                style="font-size:10px; padding:2px 6px; background:var(--adj-amber-light); border:1px solid var(--adj-amber-border); color:var(--adj-amber-text); border-radius:4px;">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                        <button type="button" class="btn btn-xs adj-btn-contacto"
                                                onclick="adjAbrirGestionCorreos()"
                                                id="adj-btn-ver-correos"
                                                title="Ver todos los correos"
                                                style="font-size:10px; padding:2px 6px; background:#f0f4ff; border:1px solid #c7d2fe; color:#3730a3; border-radius:4px; display:none;">
                                            <i class="fa fa-list"></i> <span id="adj-correo-count"></span>
                                        </button>
                                    </div>
                                </div>
                                <div class="adj-info-card-value" id="adj-info-correo" style="margin-top:2px;">—</div>
                            </div>
                        </div>

                        <div class="adj-info-card" id="adj-info-sin-datos-container"
                             style="display:none; grid-column: 1 / -1; background:#f9fafb; border-color:#e5e7eb;">
                            <i class="fa fa-exclamation-circle" style="color:#9ca3af;"></i>
                            <div>
                                <div class="adj-info-card-value" style="color:#6b7280;">Sin datos de contacto</div>
                            </div>
                        </div>

                    </div>
                </div>

                <div id="adj-seccion-responsable-extras" style="display:none;">
                    <hr class="my-4">
                    <h5 class="card-title mb-3">
                        <i class="fa-solid fa-comment me-2"></i>Mis comentarios
                    </h5>
                    <div class="mb-3">
                        <textarea id="adj-comentarios-responsable" class="form-control" rows="3" placeholder="Notas internas..."></textarea>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- PANEL DERECHO -->
    <div class="col-md-8" id="adj-panel-buscar-credito" style="display:none;">
        <div class="card h-100 adj-card-accent">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                        <span class="adj-step-badge">2</span>
                        <i class="fa-solid fa-magnifying-glass me-1"></i>Buscar y asignar crédito
                    </h5>
                    <button class="btn btn-outline-secondary btn-sm" id="adj-btn-refresh-tabla"
                            onclick="adjRefreshTablaCreditos()" title="Actualizar tabla de créditos asignados">
                        <i class="fa-solid fa-rotate-right me-1"></i>Actualizar tabla
                    </button>
                </div>

                <div class="alert-adj-amber mb-4">
                    <i class="fa-solid fa-motorcycle me-2"></i>
                    Busque un crédito por su ID y asígnelo al responsable seleccionado para adjudicación
                </div>

                <!-- Filtros -->
                <div class="row justify-content-between mb-3">
                    <div class="col-12">
                        <label class="form-label">Filtro</label>
                        <div class="input-group input-group-merge">
                            <div class="form-check form-check-inline me-3">
                                <input class="form-check-input" type="radio" name="adj-modoBusqueda" id="adj-modoBusquedaID" value="id" checked>
                                <label class="form-check-label" for="adj-modoBusquedaID">ID de crédito</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulario de búsqueda -->
                <form id="adj-formBusquedaCredito">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-9" id="adj-divIDCredito">
                            <label for="adj-idCredito" class="form-label">ID de crédito</label>
                            <div class="input-group input-group-merge">
                                <input type="number" class="form-control" id="adj-idCredito" name="idCredito"
                                       placeholder="Ej.: 12345">
                                <span class="input-group-text"><i class="fa fa-hashtag"></i></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-adj-amber w-100" id="adj-btn-buscar-credito">
                                <i class="fa-solid fa-search me-1"></i>Buscar
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Stack de créditos buscados -->
                <div id="adj-creditos-stack" class="mt-4">
                    <!-- Los créditos se agregarán dinámicamente aquí -->
                </div>

                <!-- Botón para limpiar lista -->
                <div id="adj-btn-limpiar-container" class="mt-3" style="display: none;">
                    <button class="btn btn-outline-danger w-100" onclick="adjLimpiarListaCreditos()">
                        <i class="fa-solid fa-trash me-2"></i>Limpiar lista
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TABLA DE CRÉDITOS ASIGNADOS -->
<div class="card adj-card-accent" id="adj-seccion-tabla-creditos" style="display:none;">
    <div class="card-header d-flex justify-content-between align-items-center"
         style="background: linear-gradient(135deg, #fffbeb 0%, #fef9ed 100%); border-bottom: 1px solid var(--adj-amber-border);">
        <h5 class="mb-0 d-flex align-items-center gap-2">
            <span class="adj-step-badge">3</span>
            <i class="fa-solid fa-list me-1"></i>Créditos asignados para adjudicación
        </h5>
        <div class="d-flex gap-2">
            <button class="btn btn-success btn-sm" id="adj-btn-exportar-excel">
                <i class="fa-solid fa-file-excel me-1"></i>Exportar Excel
            </button>
        </div>
    </div>
    <div class="card-datatable table-responsive">
        <table class="table border-top" id="adj-tabla-creditos">
            <thead>
                <tr>
                    <th>ID Crédito</th>
                    <th>Estado</th>
                    <th>Fecha Asignación</th>
                    <th>Fecha Desasignación</th>
                    <th>Asignado Por</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="adj-tbody-creditos">
            </tbody>
        </table>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL: Historial de Asignaciones Adjudicación               -->
<!-- ============================================================ -->
<div class="modal fade" id="adj-modalHistorial" tabindex="-1"
     aria-labelledby="adj-modalHistorialLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border: none; border-radius: 0.5rem; overflow: hidden;">

            <!-- HEADER -->
            <div class="modal-header" style="background: #f59e0b; padding: 1.25rem 1.5rem; border: none;">
                <div class="d-flex align-items-center gap-2">
                    <div style="width:36px; height:36px; border-radius:8px; background:rgba(0,0,0,0.12);
                                display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <i class="fa-solid fa-motorcycle" style="color:#1a1a1a; font-size:14px;"></i>
                    </div>
                    <div>
                        <div style="color:#1a1a1a; font-weight:600; font-size:15px; line-height:1.2;">
                            Historial de Adjudicación
                        </div>
                        <div style="color:rgba(26,26,26,0.7); font-size:12px;" id="adj-hgc-credito-label">
                            Crédito #—
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Cerrar" style="filter: brightness(0);"></button>
            </div>

            <!-- TABS NAV -->
            <div style="border-bottom:1px solid #e0e0e0; background:#fffbeb; padding: 0 1.5rem;">
                <ul class="nav" id="adj-hgcTabs" role="tablist" style="gap:0; margin:0; flex-wrap:nowrap;">
                    <li class="nav-item" role="presentation">
                        <button class="adj-hgc-tab-btn active" id="adj-hgc-tab-datos"
                                data-panel="adj-hgc-panel-datos"
                                style="padding:0.875rem 1.25rem; border:none; background:transparent;
                                       cursor:pointer; font-size:13px; font-weight:500; color:#d97706;
                                       border-bottom:2px solid #f59e0b; display:flex; align-items:center; gap:6px;">
                            <i class="fa-solid fa-table-cells-large" style="font-size:12px;"></i>
                            Datos Generales
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="adj-hgc-tab-btn" id="adj-hgc-tab-historial"
                                data-panel="adj-hgc-panel-historial"
                                style="padding:0.875rem 1.25rem; border:none; background:transparent;
                                       cursor:pointer; font-size:13px; font-weight:500; color:#697a8d;
                                       border-bottom:2px solid transparent; display:flex; align-items:center; gap:6px;">
                            <i class="fa-solid fa-clock-rotate-left" style="font-size:12px;"></i>
                            Historial
                            <span id="adj-hgc-badge-historial"
                                  style="background:#f59e0b; color:#1a1a1a; font-size:10px;
                                         padding:2px 7px; border-radius:10px; font-weight:600;">0</span>
                        </button>
                    </li>
                </ul>
            </div>

            <!-- BODY -->
            <div class="modal-body" style="padding:1.5rem; min-height:420px;">

                <!-- Spinner -->
                <div id="adj-hgc-spinner" class="text-center py-5" style="display:none;">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="mt-2 text-muted small">Cargando información...</div>
                </div>

                <!-- PANEL 1: Datos Generales -->
                <div id="adj-hgc-panel-datos">
                    <div class="row g-3 mb-2">
                        <div class="col-4">
                            <div class="text-center p-3 rounded-2" style="background:#fff3f3; border:0.5px solid #ffc5c5;">
                                <div class="text-muted mb-1" style="font-size:11px; text-transform:uppercase; letter-spacing:.5px;">Saldo vencido</div>
                                <div style="font-size:20px; font-weight:500; color:#dc3545;" id="adj-hgc-saldo">—</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-center p-3 rounded-2" style="background:#fff8f0; border:0.5px solid #ffd8a8;">
                                <div class="text-muted mb-1" style="font-size:11px; text-transform:uppercase; letter-spacing:.5px;">Días de mora</div>
                                <div style="font-size:20px; font-weight:500; color:#fd7e14;" id="adj-hgc-mora">—</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-center p-3 rounded-2" style="background:#fffbeb; border:0.5px solid #fcd34d;">
                                <div class="text-muted mb-1" style="font-size:11px; text-transform:uppercase; letter-spacing:.5px;">Asignaciones</div>
                                <div style="font-size:20px; font-weight:500; color:#d97706;" id="adj-hgc-total-gestores">—</div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-4">
                            <div class="text-center p-3 rounded-2" style="background:#f0f4ff; border:0.5px solid #c7d2fe;">
                                <div class="text-muted mb-1" style="font-size:11px; text-transform:uppercase; letter-spacing:.5px;">Bucket</div>
                                <div style="font-size:16px; font-weight:500; color:#3730a3;" id="adj-hgc-bucket">—</div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3" style="border:0.5px solid #e0e0e0;">
                        <div class="card-body p-3">
                            <div class="text-uppercase text-muted mb-3" style="font-size:11px; letter-spacing:.5px; font-weight:500;">
                                Datos del cliente
                            </div>
                            <div class="d-flex align-items-center gap-3 pb-3 mb-3" style="border-bottom:0.5px solid #f0f0f0;">
                                <div id="adj-hgc-avatar"
                                     style="width:44px; height:44px; border-radius:50%; background:#fef3c7;
                                            display:flex; align-items:center; justify-content:center;
                                            font-weight:500; font-size:13px; color:#d97706; flex-shrink:0;">—</div>
                                <div class="flex-grow-1">
                                    <div style="font-weight:500; font-size:15px;" id="adj-hgc-nombre-cliente">—</div>
                                    <div class="text-muted" style="font-size:12px;" id="adj-hgc-curp">—</div>
                                </div>
                                <span id="adj-hgc-estatus-badge" class="badge bg-success">Activo</span>
                            </div>
                            <div class="row g-3" style="font-size:13px;">
                                <div class="col-6 col-md-3">
                                    <div class="text-muted">Teléfono</div>
                                    <div class="fw-medium mt-1" id="adj-hgc-telefono">—</div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="text-muted">Sucursal</div>
                                    <div class="fw-medium mt-1" id="adj-hgc-sucursal">—</div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="text-muted">Fecha desembolso</div>
                                    <div class="fw-medium mt-1" id="adj-hgc-fecha-desembolso">—</div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="text-muted">ID Crédito</div>
                                    <div class="fw-medium mt-1" id="adj-hgc-id-credito-detalle">—</div>
                                </div>
                                <div class="col-12">
                                    <div class="text-muted">Dirección</div>
                                    <div class="fw-medium mt-1" id="adj-hgc-direccion">—</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card" style="border:0.5px solid #fcd34d; background:#fffbeb;" id="adj-hgc-gestor-actual-card">
                        <div class="card-body p-3">
                            <div class="text-uppercase mb-2" style="font-size:11px; letter-spacing:.5px; font-weight:500; color:#d97706;">
                                <i class="fa-solid fa-user-check me-1"></i> Responsable actual
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <div id="adj-hgc-gestor-avatar"
                                     style="width:38px; height:38px; border-radius:50%; background:#f59e0b;
                                            display:flex; align-items:center; justify-content:center;
                                            font-size:12px; font-weight:500; color:#1a1a1a; flex-shrink:0;">—</div>
                                <div class="flex-grow-1">
                                    <div style="font-weight:500; font-size:14px;" id="adj-hgc-gestor-nombre">—</div>
                                    <div class="text-muted" style="font-size:12px;" id="adj-hgc-gestor-info">—</div>
                                </div>
                                <span class="badge" style="background:#f59e0b; color:#1a1a1a;">Activo</span>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning mt-3" id="adj-hgc-sin-gestor" style="display:none;">
                        <i class="fa-solid fa-circle-exclamation me-2"></i>
                        Este crédito no tiene responsable activo para adjudicación actualmente.
                    </div>
                </div><!-- /panel-datos -->

                <!-- PANEL 2: Historial -->
                <div id="adj-hgc-panel-historial" style="display:none;">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <p class="text-muted mb-0" style="font-size:12px;">
                            Todos los responsables que han tenido asignado este crédito para adjudicación.
                        </p>
                        <div style="display:flex; gap:6px; flex-shrink:0;">
                            <button id="adj-hgc-sort-desc" onclick="adjHgcSetSort('desc')" title="Más reciente primero"
                                style="padding:4px 10px; font-size:11px; font-weight:500; border-radius:20px;
                                       border:1.5px solid #f59e0b; background:#f59e0b; color:#1a1a1a;
                                       cursor:pointer; transition:all .2s;">
                                <i class="fa-solid fa-arrow-down-wide-short me-1"></i>Reciente
                            </button>
                            <button id="adj-hgc-sort-asc" onclick="adjHgcSetSort('asc')" title="Más antiguo primero"
                                style="padding:4px 10px; font-size:11px; font-weight:500; border-radius:20px;
                                       border:1.5px solid #d9dee3; background:white; color:#697a8d;
                                       cursor:pointer; transition:all .2s;">
                                <i class="fa-solid fa-arrow-up-wide-short me-1"></i>Antiguo
                            </button>
                        </div>
                    </div>
                    <div id="adj-hgc-timeline" style="position:relative;">
                        <div style="position:absolute; left:16px; top:24px; bottom:8px; width:2px;
                                    background:#e0e0e0; z-index:0;"></div>
                    </div>
                    <div id="adj-hgc-historial-vacio" class="text-center py-5 text-muted" style="display:none;">
                        <i class="fa-solid fa-clock-rotate-left fa-2x mb-2 d-block opacity-25"></i>
                        Sin historial de adjudicación para este crédito
                    </div>
                </div><!-- /panel-historial -->

            </div><!-- /modal-body -->

            <div class="modal-footer" style="background:#fffbeb; border-top:0.5px solid var(--adj-amber-border); padding:.875rem 1.5rem;">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="fa-solid fa-times me-1"></i>Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL: Registrar nuevo gestor                               -->
<!-- ============================================================ -->
<div class="modal fade" id="adj-modalRegistrarGestor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
        <div class="modal-content" style="border:none; border-radius:0.5rem; overflow:hidden;">

            <div class="modal-header" style="background:#f59e0b; border:none; padding:1.25rem 1.5rem;">
                <div class="d-flex align-items-center gap-2">
                    <div style="width:34px;height:34px;border-radius:8px;background:rgba(0,0,0,0.12);
                                display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fa-solid fa-user-plus" style="color:#1a1a1a;font-size:13px;"></i>
                    </div>
                    <div>
                        <div style="color:#1a1a1a;font-weight:600;font-size:15px;line-height:1.2;">Registrar nuevo gestor</div>
                        <div style="color:rgba(26,26,26,0.65);font-size:12px;">Agregar al personal de adjudicación</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:brightness(0);"></button>
            </div>

            <div class="modal-body" style="padding:1.5rem;">

                <div class="mb-4">
                    <label class="form-label fw-semibold" style="font-size:0.85rem;">
                        <i class="fa fa-user me-1" style="color:#f59e0b;"></i>Persona
                    </label>
                    <div id="adj-reg-spinner" class="text-center py-2" style="display:none;">
                        <span class="spinner-border spinner-border-sm" style="color:#f59e0b;"></span>
                        <span class="text-muted ms-2" style="font-size:0.85rem;">Cargando personas...</span>
                    </div>
                    <select id="adj-reg-persona-select" class="form-select">
                        <option value="">Seleccione una persona...</option>
                    </select>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold" style="font-size:0.85rem;">
                            <i class="fa fa-phone me-1" style="color:#f59e0b;"></i>Teléfono de contacto
                        </label>
                        <input type="text" id="adj-reg-telefono" class="form-control"
                               placeholder="Ej: 5512345678" maxlength="20">
                        <div class="form-text text-muted" style="font-size:0.75rem;">Se pre-llena del expediente; puede editarse.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold" style="font-size:0.85rem;">
                            <i class="fa fa-envelope me-1" style="color:#f59e0b;"></i>Correo electrónico
                        </label>
                        <input type="email" id="adj-reg-correo" class="form-control"
                               placeholder="correo@ejemplo.com" maxlength="120">
                        <div class="form-text text-muted" style="font-size:0.75rem;">Se pre-llena del expediente; puede editarse.</div>
                    </div>
                </div>

                <div class="alert-adj-amber mt-4" style="font-size:0.8rem;">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    El gestor quedará en estatus <strong>Activo</strong> y podrá recibir asignaciones de inmediato.
                </div>
            </div>

            <div class="modal-footer" style="background:#fffbeb; border-top:1px solid var(--adj-amber-border); padding:0.875rem 1.5rem;">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="fa fa-times me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-adj-amber btn-sm" id="adj-reg-btn-guardar" onclick="adjRegistrarGestor()">
                    <i class="fa fa-save me-1"></i>Guardar gestor
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ================================================================== -->
<!-- MODAL: Gestionar Teléfonos del Gestor                             -->
<!-- ================================================================== -->
<div class="modal fade" id="adj-modal-telefonos" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:440px;">
        <div class="modal-content" style="border:none; border-radius:.5rem; overflow:hidden;">
            <div class="modal-header" style="background:#f59e0b; border:none; padding:1.1rem 1.5rem;">
                <div class="d-flex align-items-center gap-2">
                    <div style="width:32px;height:32px;border-radius:7px;background:rgba(0,0,0,.12);
                                display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fa fa-phone" style="color:#1a1a1a;font-size:12px;"></i>
                    </div>
                    <div>
                        <div style="color:#1a1a1a;font-weight:600;font-size:14px;line-height:1.2;">Teléfonos adicionales</div>
                        <div style="color:rgba(26,26,26,.65);font-size:11px;" id="adj-tel-modal-subtitulo">Gestor seleccionado</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:brightness(0);"></button>
            </div>
            <div class="modal-body" style="padding:1.25rem;">
                <!-- Registro nuevo -->
                <div class="d-flex gap-2 mb-3">
                    <input type="text" id="adj-nuevo-telefono" class="form-control form-control-sm"
                           placeholder="Nuevo número..." maxlength="10"
                           onkeydown="if(event.key==='Enter') adjGuardarTelefono();">
                    <button type="button" class="btn btn-adj-amber btn-sm px-3" onclick="adjGuardarTelefono()">
                        <i class="fa fa-plus"></i>
                    </button>
                </div>
                <!-- Lista -->
                <div id="adj-lista-telefonos" style="min-height:40px;">
                    <div class="text-muted text-center py-2" style="font-size:.82rem;">Cargando...</div>
                </div>
            </div>
            <div class="modal-footer" style="background:#fffbeb; border-top:1px solid var(--adj-amber-border); padding:.75rem 1.25rem;">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="fa fa-times me-1"></i>Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ================================================================== -->
<!-- MODAL: Gestionar Correos del Gestor                                -->
<!-- ================================================================== -->
<div class="modal fade" id="adj-modal-correos" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:440px;">
        <div class="modal-content" style="border:none; border-radius:.5rem; overflow:hidden;">
            <div class="modal-header" style="background:#f59e0b; border:none; padding:1.1rem 1.5rem;">
                <div class="d-flex align-items-center gap-2">
                    <div style="width:32px;height:32px;border-radius:7px;background:rgba(0,0,0,.12);
                                display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fa fa-envelope" style="color:#1a1a1a;font-size:12px;"></i>
                    </div>
                    <div>
                        <div style="color:#1a1a1a;font-weight:600;font-size:14px;line-height:1.2;">Correos adicionales</div>
                        <div style="color:rgba(26,26,26,.65);font-size:11px;" id="adj-correo-modal-subtitulo">Gestor seleccionado</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:brightness(0);"></button>
            </div>
            <div class="modal-body" style="padding:1.25rem;">
                <!-- Registro nuevo -->
                <div class="d-flex gap-2 mb-3">
                    <input type="email" id="adj-nuevo-correo" class="form-control form-control-sm"
                           placeholder="nuevo@correo.com" maxlength="150"
                           onkeydown="if(event.key==='Enter') adjGuardarCorreo();">
                    <button type="button" class="btn btn-adj-amber btn-sm px-3" onclick="adjGuardarCorreo()">
                        <i class="fa fa-plus"></i>
                    </button>
                </div>
                <!-- Lista -->
                <div id="adj-lista-correos" style="min-height:40px;">
                    <div class="text-muted text-center py-2" style="font-size:.82rem;">Cargando...</div>
                </div>
            </div>
            <div class="modal-footer" style="background:#fffbeb; border-top:1px solid var(--adj-amber-border); padding:.75rem 1.25rem;">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="fa fa-times me-1"></i>Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// ============================================================================
// ADJUDICACIÓN — Variables globales
// ============================================================================
let adjResponsableSeleccionado = null;
let adjCreditosEncontrados = [];
let adjSearchableSelect;

// Registro de gestores
let adjPersonasMap    = {};     // id → { nombre_completo, telefono, correo }
let adjPersonasCargadas   = false;
let adjRegSelectInstance  = null;

// ============================================================================
// GESTIÓN DE TELÉFONOS ADICIONALES DEL GESTOR
// ============================================================================
function adjAbrirGestionTelefonos() {
    if (!adjResponsableSeleccionado) return;
    const nombre = document.getElementById('adj-info-nombre')?.textContent || '';
    document.getElementById('adj-tel-modal-subtitulo').textContent = nombre || 'Gestor seleccionado';
    document.getElementById('adj-nuevo-telefono').value = '';
    const modalEl = document.getElementById('adj-modal-telefonos');
    let m = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    m.show();
    adjCargarListaTelefonos();
}

function adjCargarListaTelefonos() {
    const lista = document.getElementById('adj-lista-telefonos');
    lista.innerHTML = '<div class="text-muted text-center py-2" style="font-size:.82rem;">Cargando...</div>';
    fetch(`/Adjudicacion/obtenerTelefonos/${adjResponsableSeleccionado}`)
        .then(r => r.json())
        .then(data => {
            if (!data.success) { lista.innerHTML = '<div class="text-danger" style="font-size:.82rem;">' + (data.message||'Error') + '</div>'; return; }
            const tels = data.telefonos || [];
            // Actualizar badge en la tarjeta
            const btnVer = document.getElementById('adj-btn-ver-telefonos');
            const countEl = document.getElementById('adj-tel-count');
            if (tels.length > 0) { btnVer.style.display = ''; countEl.textContent = tels.length; }
            else { btnVer.style.display = 'none'; }
            if (tels.length === 0) { lista.innerHTML = '<div class="text-muted text-center py-2" style="font-size:.82rem;">Sin teléfonos adicionales registrados.</div>'; return; }
            const _telActual = (document.getElementById('adj-info-telefono').textContent || '').trim();
            lista.innerHTML = tels.map(t => {
                const esActual = _telActual && _telActual !== '\u2014' && _telActual !== '-' && _telActual === t.numero;
                return `<div class="d-flex align-items-center justify-content-between py-1 px-2 mb-1 rounded"
                      style="background:#fffbeb; border:1px solid #fcd34d;">
                    <span style="font-size:.84rem; cursor:pointer; text-decoration:underline dotted; color:#92400e;"
                          title="Usar este n\u00famero" onclick="adjSeleccionarTelefono('${t.numero}')">
                        \uD83D\uDCDE ${t.numero}${esActual ? ' <span style="font-size:.72rem; background:#d1fae5; color:#065f46; border:1px solid #6ee7b7; border-radius:9px; padding:1px 8px; margin-left:5px; font-weight:600;">actual</span>' : ''}
                    </span>
                    <button type="button" class="btn btn-sm" title="Eliminar"
                            style="padding:2px 7px; font-size:11px; background:#fee2e2; border:1px solid #fca5a5; color:#dc2626; border-radius:4px;"
                            onclick="adjEliminarTelefono(${t.id})">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>`;
            }).join('');
        })
        .catch(() => { lista.innerHTML = '<div class="text-danger" style="font-size:.82rem;">Error de conexión.</div>'; });
}

function adjSeleccionarTelefono(numero) {
    const campo = document.getElementById('adj-info-telefono');
    const actual = campo.textContent.trim();
    const aplicar = () => {
        campo.textContent = numero;
        bootstrap.Modal.getInstance(document.getElementById('adj-modal-telefonos'))?.hide();
        Swal.fire({ icon: 'success', title: 'Teléfono aplicado', text: numero, timer: 1800, showConfirmButton: false });
    };
    if (!actual || actual === '—' || actual === '-') {
        aplicar();
    } else {
        Swal.fire({
            title: '¿Reemplazar teléfono?',
            html: `El campo ya muestra <strong>${actual}</strong>.<br>¿Lo reemplazamos con <strong>${numero}</strong>?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Reemplazar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#f59e0b',
        }).then(res => { if (res.isConfirmed) aplicar(); });
    }
}

function adjSeleccionarCorreo(correo) {
    const campo = document.getElementById('adj-info-correo');
    const actual = campo.textContent.trim();
    const aplicar = () => {
        campo.textContent = correo;
        bootstrap.Modal.getInstance(document.getElementById('adj-modal-correos'))?.hide();
        Swal.fire({ icon: 'success', title: 'Correo aplicado', text: correo, timer: 1800, showConfirmButton: false });
    };
    if (!actual || actual === '—' || actual === '-') {
        aplicar();
    } else {
        Swal.fire({
            title: '¿Reemplazar correo?',
            html: `El campo ya muestra <strong>${actual}</strong>.<br>¿Lo reemplazamos con <strong>${correo}</strong>?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Reemplazar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#f59e0b',
        }).then(res => { if (res.isConfirmed) aplicar(); });
    }
}

function adjGuardarTelefono() {
    const numero = document.getElementById('adj-nuevo-telefono').value.trim();
    if (!numero) { Swal.fire('Advertencia', 'Ingrese un número de teléfono.', 'warning'); return; }
    fetch('/Adjudicacion/registrarTelefono', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_persona: adjResponsableSeleccionado, numero })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('adj-nuevo-telefono').value = '';
            const campTel = document.getElementById('adj-info-telefono');
            const valTel = campTel.textContent.trim();
            if (!valTel || valTel === '\u2014' || valTel === '-') campTel.textContent = numero;
            adjCargarListaTelefonos();
        } else {
            Swal.fire('Error', data.message || 'No se pudo guardar.', 'error');
        }
    })
    .catch(() => Swal.fire('Error', 'Error de conexión.', 'error'));
}

function adjEliminarTelefono(idTelefono) {
    Swal.fire({ title: '¿Eliminar teléfono?', icon: 'warning', showCancelButton: true,
                confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar',
                confirmButtonColor: '#dc2626' })
    .then(res => {
        if (!res.isConfirmed) return;
        fetch('/Adjudicacion/eliminarTelefono', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_telefono: idTelefono, id_persona: adjResponsableSeleccionado })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) adjCargarListaTelefonos();
            else Swal.fire('Error', data.message || 'No se pudo eliminar.', 'error');
        })
        .catch(() => Swal.fire('Error', 'Error de conexión.', 'error'));
    });
}

// ============================================================================
// GESTIÓN DE CORREOS ADICIONALES DEL GESTOR
// ============================================================================
function adjAbrirGestionCorreos() {
    if (!adjResponsableSeleccionado) return;
    const nombre = document.getElementById('adj-info-nombre')?.textContent || '';
    document.getElementById('adj-correo-modal-subtitulo').textContent = nombre || 'Gestor seleccionado';
    document.getElementById('adj-nuevo-correo').value = '';
    const modalEl = document.getElementById('adj-modal-correos');
    let m = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    m.show();
    adjCargarListaCorreos();
}

function adjCargarListaCorreos() {
    const lista = document.getElementById('adj-lista-correos');
    lista.innerHTML = '<div class="text-muted text-center py-2" style="font-size:.82rem;">Cargando...</div>';
    fetch(`/Adjudicacion/obtenerCorreos/${adjResponsableSeleccionado}`)
        .then(r => r.json())
        .then(data => {
            if (!data.success) { lista.innerHTML = '<div class="text-danger" style="font-size:.82rem;">' + (data.message||'Error') + '</div>'; return; }
            const correos = data.correos || [];
            // Actualizar badge
            const btnVer = document.getElementById('adj-btn-ver-correos');
            const countEl = document.getElementById('adj-correo-count');
            if (correos.length > 0) { btnVer.style.display = ''; countEl.textContent = correos.length; }
            else { btnVer.style.display = 'none'; }
            if (correos.length === 0) { lista.innerHTML = '<div class="text-muted text-center py-2" style="font-size:.82rem;">Sin correos adicionales registrados.</div>'; return; }
            const _correoActual = (document.getElementById('adj-info-correo').textContent || '').trim();
            lista.innerHTML = correos.map(c => {
                const esActual = _correoActual && _correoActual !== '\u2014' && _correoActual !== '-' && _correoActual === c.correo;
                return `<div class="d-flex align-items-center justify-content-between py-1 px-2 mb-1 rounded"
                      style="background:#fffbeb; border:1px solid #fcd34d;">
                    <span style="font-size:.84rem; cursor:pointer; text-decoration:underline dotted; color:#92400e;"
                          title="Usar este correo" onclick="adjSeleccionarCorreo('${c.correo}')">
                        \u2709\uFE0F ${c.correo}${esActual ? ' <span style="font-size:.72rem; background:#d1fae5; color:#065f46; border:1px solid #6ee7b7; border-radius:9px; padding:1px 8px; margin-left:5px; font-weight:600;">actual</span>' : ''}
                    </span>
                    <button type="button" class="btn btn-sm" title="Eliminar"
                            style="padding:2px 7px; font-size:11px; background:#fee2e2; border:1px solid #fca5a5; color:#dc2626; border-radius:4px;"
                            onclick="adjEliminarCorreo(${c.id})">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>`;
            }).join('');
        })
        .catch(() => { lista.innerHTML = '<div class="text-danger" style="font-size:.82rem;">Error de conexión.</div>'; });
}

function adjGuardarCorreo() {
    const correo = document.getElementById('adj-nuevo-correo').value.trim();
    if (!correo) { Swal.fire('Advertencia', 'Ingrese un correo electrónico.', 'warning'); return; }
    fetch('/Adjudicacion/registrarCorreo', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_persona: adjResponsableSeleccionado, correo })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('adj-nuevo-correo').value = '';
            const campCorreo = document.getElementById('adj-info-correo');
            const valCorreo = campCorreo.textContent.trim();
            if (!valCorreo || valCorreo === '\u2014' || valCorreo === '-') campCorreo.textContent = correo;
            adjCargarListaCorreos();
        } else {
            Swal.fire('Error', data.message || 'No se pudo guardar.', 'error');
        }
    })
    .catch(() => Swal.fire('Error', 'Error de conexión.', 'error'));
}

function adjEliminarCorreo(idCorreo) {
    Swal.fire({ title: '¿Eliminar correo?', icon: 'warning', showCancelButton: true,
                confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar',
                confirmButtonColor: '#dc2626' })
    .then(res => {
        if (!res.isConfirmed) return;
        fetch('/Adjudicacion/eliminarCorreo', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_correo: idCorreo, id_persona: adjResponsableSeleccionado })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) adjCargarListaCorreos();
            else Swal.fire('Error', data.message || 'No se pudo eliminar.', 'error');
        })
        .catch(() => Swal.fire('Error', 'Error de conexión.', 'error'));
    });
}

// ============================================================================
// INIT
// ============================================================================
document.addEventListener('DOMContentLoaded', function () {
    adjCargarResponsables();

    document.getElementById('adj-select-responsable').addEventListener('change', function () {
        adjResponsableSeleccionado = this.value;
        if (adjResponsableSeleccionado) {
            adjCargarDatosResponsable(adjResponsableSeleccionado);
            adjCargarCreditosAsignados(adjResponsableSeleccionado);
        }
    });

    document.getElementById('adj-formBusquedaCredito').addEventListener('submit', function (e) {
        e.preventDefault();
        adjBuscarCredito();
    });

    let _comentarioTimer = null;
    document.getElementById('adj-comentarios-responsable').addEventListener('input', function () {
        clearTimeout(_comentarioTimer);
        _comentarioTimer = setTimeout(adjGuardarComentarios, 800);
    });

    document.getElementById('adj-btn-exportar-excel').addEventListener('click', adjExportarExcel);

    // Tabs del modal historial
    document.querySelectorAll('.adj-hgc-tab-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const panelId = this.dataset.panel;

            document.querySelectorAll('.adj-hgc-tab-btn').forEach(b => {
                b.style.color = '#697a8d';
                b.style.borderBottom = '2px solid transparent';
            });
            this.style.color = '#d97706';
            this.style.borderBottom = '2px solid #f59e0b';

            document.querySelectorAll('[id^="adj-hgc-panel-"]').forEach(p => {
                p.style.display = 'none';
            });
            document.getElementById(panelId).style.display = 'block';
        });
    });
});

// ============================================================================
// CARGAR RESPONSABLES
// ============================================================================
function adjCargarResponsables() {
    fetch('/Adjudicacion/obtenerListaResponsables')
        .then(r => r.json())
        .then(data => {
            const select = document.getElementById('adj-select-responsable');
            select.innerHTML = '<option value="">Seleccione un responsable...</option>';

            if (data.success && data.responsables && data.responsables.length > 0) {
                data.responsables.forEach(resp => {
                    const option = document.createElement('option');
                    option.value = resp.id_persona;
                    option.textContent = resp.nombre_completo;
                    select.appendChild(option);
                });

                // Destruir wrapper previo para evitar duplicados
                if (adjSearchableSelect && adjSearchableSelect.wrapper) {
                    adjSearchableSelect.wrapper.remove();
                    select.style.display = '';
                }
                adjSearchableSelect = new AdjSearchableSelect(select);
            }
        })
        .catch(() => {
            // API no disponible aún — se mostrará vacío
        });
}

// ============================================================================
// CARGAR DATOS DEL RESPONSABLE
// ============================================================================
function adjCargarDatosResponsable(idPersona) {
    fetch(`/Adjudicacion/obtenerDatosResponsable/${idPersona}`)
        .then(r => r.json())
        .then(data => {
            if (data.success && data.datos) {
                const d = data.datos;

                document.getElementById('adj-info-nombre').textContent   = d.nombre_completo || '-';
                document.getElementById('adj-info-puesto').textContent   = d.puesto ? d.puesto.split(' - ')[0].trim() : '-';
                document.getElementById('adj-info-telefono').textContent = d.telefono        || '-';
                document.getElementById('adj-info-correo').textContent   = d.correo          || '-';

                document.getElementById('adj-info-responsable-container').style.display = 'block';
                document.getElementById('adj-seccion-responsable-extras').style.display = 'block';
                document.getElementById('adj-panel-buscar-credito').style.display = 'block';

                document.getElementById('adj-comentarios-responsable').value = data.comentarios || '';
            }
        })
        .catch(() => {
            // Mostrar el panel de búsqueda aunque falle la carga de datos
            document.getElementById('adj-panel-buscar-credito').style.display = 'block';
        });
}

// ============================================================================
// BUSCAR CRÉDITO
// ============================================================================
function adjBuscarCredito() {
    const idCredito = document.getElementById('adj-idCredito').value.trim();

    if (!idCredito) {
        Swal.fire('Advertencia', 'Ingrese un ID de crédito', 'warning');
        return;
    }

    if (!adjResponsableSeleccionado) {
        Swal.fire('Advertencia', 'Seleccione un responsable primero', 'warning');
        return;
    }

    // Verificar si ya está en el stack
    const yaEstaEnStack = adjCreditosEncontrados.find(c => String(c.credito.id_credito) === String(idCredito));
    if (yaEstaEnStack) {
        Swal.fire('Aviso', 'Este crédito ya está en la lista', 'info');
        return;
    }

    const btn = document.getElementById('adj-btn-buscar-credito');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Buscando...';

    fetch('/Adjudicacion/buscarCredito', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ tipo: 'id_credito', valor: idCredito })
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-search me-1"></i>Buscar';

        if (data.success) {
            adjCreditosEncontrados.push({ credito: data.credito, asignacion: data.asignacion });
            adjRenderizarStack();
            document.getElementById('adj-idCredito').value = '';
        } else if (data.credito_regla) {
            // Crédito vigente/al corriente — no apto para adjudicación
            Swal.fire({
                icon: 'info',
                title: 'Crédito en regla',
                html: `
                    <div class="text-start" style="font-size:0.9rem;">
                        <p class="mb-2">Este crédito <strong>no puede asignarse a adjudicación</strong>.</p>
                        <div class="d-flex align-items-center gap-2 p-2 rounded"
                             style="background:#d1fae5; border:1px solid #6ee7b7;">
                            <i class="fa-solid fa-circle-check" style="color:#059669; font-size:1.1rem;"></i>
                            <div>
                                <div style="font-size:0.75rem; color:#065f46; font-weight:600; text-transform:uppercase; letter-spacing:.3px;">Estatus del crédito</div>
                                <div style="font-size:1rem; font-weight:700; color:#065f46;">${data.status_credito}</div>
                            </div>
                        </div>
                        <p class="mt-2 mb-0 text-muted" style="font-size:0.8rem;">Solo los créditos con estatus <strong>"Vencido"</strong> pueden adjudicarse.</p>
                    </div>`,
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#f59e0b',
            });
        } else {
            Swal.fire('No encontrado', data.message || 'Crédito no encontrado', 'warning');
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-search me-1"></i>Buscar';
        Swal.fire('Error', 'Error al conectar con el servidor', 'error');
    });
}

// ============================================================================
// STACK DE CRÉDITOS
// ============================================================================
function adjRenderizarStack() {
    const stack = document.getElementById('adj-creditos-stack');
    const limpiarBtn = document.getElementById('adj-btn-limpiar-container');
    stack.innerHTML = '';

    if (adjCreditosEncontrados.length === 0) {
        limpiarBtn.style.display = 'none';
        return;
    }

    limpiarBtn.style.display = 'block';

    adjCreditosEncontrados.forEach(item => {
        const credito    = item.credito;
        const asignacion = item.asignacion;
        const creditoId  = credito.id_credito;
        const esActivo   = asignacion && (asignacion.estatus === '1' || asignacion.estatus === 1);

        const card = document.createElement('div');
        card.className = 'credit-card-item';
        card.id = `adj-credito-card-${creditoId}`;
        card.style.cssText = 'border:1px solid #e5e7eb; border-left:4px solid #f59e0b; border-radius:0.5rem; padding:0.875rem; background:#fff;';

        card.innerHTML = `
            <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:0.75rem;">
                <div style="flex:1; min-width:0;">
                    <div style="font-size:12px; color:#697a8d; margin-bottom:4px;">
                        <strong style="color:#566a7f;">#${creditoId}</strong>
                        ${esActivo ? `<span class="badge bg-warning text-dark ms-2" style="font-size:10px;">
                            <i class="fa-solid fa-user-tie me-1" style="font-size:0.7rem;"></i>Asignado a ${asignacion.nombre_despacho || 'responsable'}
                        </span>` : ''}
                    </div>
                    <div class="mb-1"><strong>Nombre:</strong> ${credito.nombre_cliente || '—'}</div>
                    <div class="mb-1"><strong>Dirección:</strong> <span class="text-muted">${credito.direccion || 'Sin dirección'}</span></div>
                    <div class="mb-1"><strong>Saldo:</strong> <span class="text-danger fw-bold">${adjFormatearMoneda(credito.saldo_actual || 0)}</span></div>
                    ${credito.bucket ? `<div><strong>Bucket:</strong> <span style="color:#3730a3;">${credito.bucket}</span></div>` : ''}
                </div>
                <div class="d-flex flex-column gap-2 ms-3">
                    ${!esActivo ? `
                    <button class="btn btn-gradient-success btn-sm" onclick="adjAsignarCreditoDelStack('${creditoId}')" title="Asignar crédito">
                        <i class="fa-solid fa-check me-1"></i>Asignar
                    </button>` : `
                    <button class="btn btn-outline-danger btn-sm" onclick="adjDesasignarCredito('${creditoId}')" title="Desasignar crédito">
                        <i class="fa-solid fa-xmark me-1"></i>Liberar
                    </button>`}
                    <button class="btn btn-outline-secondary btn-sm" onclick="adjVerHistorial('${creditoId}')" title="Ver historial">
                        <i class="fa-solid fa-clock-rotate-left me-1"></i>Historial
                    </button>
                    <button class="btn btn-gradient-danger btn-sm" onclick="adjDescartarCredito('${creditoId}')" title="Quitar de la lista">
                        <i class="fa-solid fa-trash me-1"></i>Quitar
                    </button>
                    <button class="btn btn-sm mt-1" onclick="adjIrEstadoCuenta('${creditoId}')" title="Ver estado de cuenta"
                            style="background:#e0f2fe; color:#0369a1; border:1px solid #7dd3fc; font-size:0.78rem;">
                        <i class="fa-solid fa-file-invoice-dollar me-1"></i>Estado de cuenta
                    </button>
                </div>
            </div>
            <div id="adj-details-${creditoId}">
                <div class="mt-2 p-2 bg-light rounded" style="font-size:0.8rem; display:none;">
                    <div class="row g-2">
                        <div class="col-6">
                            <small class="text-muted">Estatus asignación:</small><br>
                            <span class="badge ${esActivo ? 'bg-success' : 'bg-secondary'}">${esActivo ? 'Activo' : 'Sin asignación'}</span>
                        </div>
                        ${asignacion ? `
                        <div class="col-6">
                            <small class="text-muted">Desde:</small><br>
                            <span>${asignacion.fecha_asignacion || '—'}</span>
                        </div>` : ''}
                    </div>
                </div>
            </div>
        `;

        stack.appendChild(card);
    });
}

function adjDescartarCredito(creditoId) {
    const card = document.getElementById(`adj-credito-card-${creditoId}`);
    if (card) {
        card.classList.add('removing');
        setTimeout(() => {
            adjCreditosEncontrados = adjCreditosEncontrados.filter(c => String(c.credito.id_credito) !== String(creditoId));
            adjRenderizarStack();
        }, 300);
    }
}

function adjLimpiarListaCreditos() {
    adjCreditosEncontrados = [];
    adjRenderizarStack();
}

// ============================================================================
// ASIGNAR / DESASIGNAR CRÉDITO
// ============================================================================
function adjAsignarCreditoDelStack(idCredito) {
    if (!adjResponsableSeleccionado) {
        Swal.fire('Advertencia', 'Seleccione un responsable primero', 'warning');
        return;
    }

    const creditoItem = adjCreditosEncontrados.find(c => String(c.credito.id_credito) === String(idCredito));
    if (!creditoItem) return;

    if (creditoItem.asignacion) {
        const esActivo = creditoItem.asignacion.estatus === '1' || creditoItem.asignacion.estatus === 1;
        if (esActivo) {
            Swal.fire('No permitido', `Este crédito ya está asignado a: ${creditoItem.asignacion.nombre_despacho}`, 'warning');
            return;
        }
    }

    const creditoEncontrado  = creditoItem.credito;
    const nombreResponsable  = document.getElementById('adj-select-responsable').selectedOptions[0]?.textContent?.trim() || adjResponsableSeleccionado;

    Swal.fire({
        title: 'Asignar crédito para adjudicación',
        html: `
            <div class="text-start" style="font-size:0.95rem;">
                <div class="mb-2"><span class="text-muted">Responsable:</span> <strong>${nombreResponsable}</strong></div>
                <div class="mb-2"><span class="text-muted">Crédito:</span> <strong>${creditoEncontrado.id_credito}</strong></div>
                <div><span class="text-muted">Cliente:</span> ${creditoEncontrado.nombre_cliente || '—'}</div>
            </div>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, asignar',
        cancelButtonText: 'Cancelar'
    }).then(result => {
        if (!result.isConfirmed) return;

        Swal.fire({ title: 'Asignando crédito...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        fetch('/Adjudicacion/asignarCredito', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id_persona: adjResponsableSeleccionado,
                id_credito: creditoEncontrado.id_credito
            })
        })
        .then(r => r.json())
        .then(data => {
            Swal.close();
            if (data.success) {
                Swal.fire({ title: 'Éxito', text: 'Crédito asignado correctamente', icon: 'success', timer: 2000, showConfirmButton: false });
                adjDescartarCredito(idCredito);
                adjCargarCreditosAsignados(adjResponsableSeleccionado);
            } else {
                Swal.fire('Error', data.message || 'No se pudo asignar el crédito', 'error');
            }
        })
        .catch(() => {
            Swal.close();
            Swal.fire('Error', 'Error al asignar el crédito', 'error');
        });
    });
}

function adjDesasignarCredito(idCredito) {
    Swal.fire({
        title: '¿Liberar crédito?',
        text: `El crédito #${idCredito} será desasignado de adjudicación.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, liberar',
        cancelButtonText: 'Cancelar'
    }).then(result => {
        if (!result.isConfirmed) return;

        Swal.fire({ title: 'Desasignando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        fetch('/Adjudicacion/desasignarCredito', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_credito: idCredito })
        })
        .then(r => r.json())
        .then(data => {
            Swal.close();
            if (data.success) {
                Swal.fire({ title: 'Liberado', text: `El crédito ${idCredito} fue liberado correctamente`, icon: 'success', timer: 2000, showConfirmButton: false });

                // Refrescar la card en el stack
                fetch('/Adjudicacion/buscarCredito', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ tipo: 'id_credito', valor: idCredito })
                })
                .then(r => r.json())
                .then(data2 => {
                    if (data2.success) {
                        const idx = adjCreditosEncontrados.findIndex(c => String(c.credito.id_credito) === String(idCredito));
                        if (idx !== -1) {
                            adjCreditosEncontrados[idx] = { credito: data2.credito, asignacion: data2.asignacion };
                            adjRenderizarStack();
                        }
                    }
                    adjCargarCreditosAsignados(adjResponsableSeleccionado);
                })
                .catch(() => adjCargarCreditosAsignados(adjResponsableSeleccionado));
            } else {
                Swal.fire('Error', data.message || 'No se pudo liberar el crédito', 'error');
            }
        })
        .catch(() => {
            Swal.close();
            Swal.fire('Error', 'Error al liberar el crédito', 'error');
        });
    });
}

// ============================================================================
// TABLA DE CRÉDITOS ASIGNADOS
// ============================================================================
function adjCargarCreditosAsignados(idPersona) {
    fetch(`/Adjudicacion/obtenerCreditosAsignados/${idPersona}`)
        .then(r => r.json())
        .then(data => {
            if (data.success && data.creditos) {
                if (!$.fn.DataTable.isDataTable('#adj-tabla-creditos')) {
                    configuraTabla('#adj-tabla-creditos', {
                        registrosPorPagina: 10,
                        columns: [
                            { data: 0, title: 'ID Crédito' },
                            { data: 1, title: 'Estado' },
                            { data: 2, title: 'Fecha Asignación' },
                            { data: 3, title: 'Fecha Desasignación' },
                            { data: 4, title: 'Asignado Por' },
                            { data: 5, title: 'Acciones', orderable: false }
                        ]
                    });
                }

                actualizaDatosTabla('#adj-tabla-creditos', data.creditos);
                document.getElementById('adj-seccion-tabla-creditos').style.display = 'block';
            }
        })
        .catch(() => {
            document.getElementById('adj-seccion-tabla-creditos').style.display = 'block';
        });
}

function adjRefreshTablaCreditos() {
    if (adjResponsableSeleccionado) {
        adjCargarCreditosAsignados(adjResponsableSeleccionado);
    }
}

// ============================================================================
// GUARDAR COMENTARIOS
// ============================================================================
function adjGuardarComentarios() {
    if (!adjResponsableSeleccionado) return;
    const comentario = document.getElementById('adj-comentarios-responsable').value;
    fetch('/Adjudicacion/guardarComentarios', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_persona: adjResponsableSeleccionado, comentario })
    }).catch(() => {});
}

// ============================================================================
// EXPORTAR EXCEL
// ============================================================================
function adjExportarExcel() {
    if (!adjResponsableSeleccionado) {
        Swal.fire('Advertencia', 'Seleccione un responsable primero', 'warning');
        return;
    }
    window.location.href = `/Adjudicacion/exportarExcel/${adjResponsableSeleccionado}`;
}

// ============================================================================
// VER HISTORIAL (modal)
// ============================================================================
function adjVerHistorial(idCredito) {
    const modalEl = document.getElementById('adj-modalHistorial');
    let modal = bootstrap.Modal.getInstance(modalEl);
    if (!modal) modal = new bootstrap.Modal(modalEl);
    modal.show();

    document.getElementById('adj-hgc-credito-label').textContent = `Crédito #${idCredito}`;
    document.getElementById('adj-hgc-spinner').style.display = 'block';
    document.getElementById('adj-hgc-panel-datos').style.display   = 'none';
    document.getElementById('adj-hgc-panel-historial').style.display = 'none';

    fetch('/Adjudicacion/buscarCredito', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ tipo: 'id_credito', valor: idCredito })
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('adj-hgc-spinner').style.display = 'none';
        document.getElementById('adj-hgc-panel-datos').style.display = 'block';

        if (data.success) {
            adjHgcPoblarDatos(data.credito, data.asignacion);
        }
    })
    .catch(() => {
        document.getElementById('adj-hgc-spinner').style.display = 'none';
        document.getElementById('adj-hgc-panel-datos').style.display = 'block';
    });

    // Cargar historial
    fetch(`/Adjudicacion/historialCredito/${idCredito}`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                adjHgcRenderizarTimeline(data.historial || []);
                document.getElementById('adj-hgc-badge-historial').textContent = (data.historial || []).length;
            }
        })
        .catch(() => {});
}

function adjHgcPoblarDatos(credito, asignacion) {
    const inits = (nombre) => {
        if (!nombre) return '—';
        const partes = nombre.trim().split(/\s+/);
        return partes.length >= 2 ? (partes[0][0] + partes[1][0]).toUpperCase() : nombre[0].toUpperCase();
    };

    document.getElementById('adj-hgc-nombre-cliente').textContent = credito.nombre_cliente  || '—';
    document.getElementById('adj-hgc-curp').textContent            = credito.curp            || '—';
    document.getElementById('adj-hgc-saldo').textContent           = adjFormatearMoneda(credito.saldo_actual || 0);
    document.getElementById('adj-hgc-bucket').textContent          = credito.bucket          || '—';
    document.getElementById('adj-hgc-mora').textContent            = credito.dias_mora       || '—';
    document.getElementById('adj-hgc-telefono').textContent        = credito.telefono        || '—';
    document.getElementById('adj-hgc-sucursal').textContent        = credito.sucursal        || '—';
    document.getElementById('adj-hgc-fecha-desembolso').textContent= credito.fecha_desembolso|| '—';
    document.getElementById('adj-hgc-id-credito-detalle').textContent = credito.id_credito   || '—';
    document.getElementById('adj-hgc-direccion').textContent       = credito.direccion       || '—';
    document.getElementById('adj-hgc-avatar').textContent          = inits(credito.nombre_cliente);

    const esActivo = asignacion && (asignacion.estatus === '1' || asignacion.estatus === 1);
    if (asignacion && esActivo) {
        document.getElementById('adj-hgc-gestor-actual-card').style.display = 'block';
        document.getElementById('adj-hgc-sin-gestor').style.display         = 'none';
        document.getElementById('adj-hgc-gestor-nombre').textContent = asignacion.nombre_despacho || '—';
        document.getElementById('adj-hgc-gestor-info').textContent   = asignacion.puesto_despacho || '—';
        document.getElementById('adj-hgc-gestor-avatar').textContent = inits(asignacion.nombre_despacho);
    } else {
        document.getElementById('adj-hgc-gestor-actual-card').style.display = 'none';
        document.getElementById('adj-hgc-sin-gestor').style.display         = 'block';
    }
}

let adjHgcSortDir = 'desc';

function adjHgcSetSort(dir) {
    adjHgcSortDir = dir;
    const items = document.querySelectorAll('#adj-hgc-timeline .adj-timeline-item');
    const arr   = Array.from(items);
    const cont  = document.getElementById('adj-hgc-timeline');
    const linea = cont.querySelector('div[style*="position:absolute"]');
    arr.sort((a, b) => {
        const da = new Date(a.dataset.fecha || 0);
        const db = new Date(b.dataset.fecha || 0);
        return dir === 'desc' ? db - da : da - db;
    });
    arr.forEach(el => cont.appendChild(el));

    document.getElementById('adj-hgc-sort-desc').style.background = dir === 'desc' ? '#f59e0b' : 'white';
    document.getElementById('adj-hgc-sort-desc').style.color      = dir === 'desc' ? '#1a1a1a'  : '#697a8d';
    document.getElementById('adj-hgc-sort-asc').style.background  = dir === 'asc'  ? '#f59e0b' : 'white';
    document.getElementById('adj-hgc-sort-asc').style.color       = dir === 'asc'  ? '#1a1a1a'  : '#697a8d';
}

function adjHgcRenderizarTimeline(historial) {
    const cont  = document.getElementById('adj-hgc-timeline');
    const vacio = document.getElementById('adj-hgc-historial-vacio');

    // Limpiar items previos (mantener la línea decorativa)
    cont.querySelectorAll('.adj-timeline-item').forEach(el => el.remove());

    if (!historial || historial.length === 0) {
        vacio.style.display = 'block';
        return;
    }
    vacio.style.display = 'none';

    historial.forEach(item => {
        const esActivo   = item.estatus === '1' || item.estatus === 1;
        const badgeColor = esActivo ? '#28a745' : '#adb5bd';
        const badgeLabel = esActivo ? 'Activo' : 'Inactivo';
        const fechaFin   = item.fecha_baja ? `<span>Hasta: <strong style="color:#566a7f;">${item.fecha_baja}</strong></span>` : '';
        const cardBorder = esActivo ? 'border:1px solid #c3e6cb;' : 'border:1px solid #dee2e6;';

        const el = document.createElement('div');
        el.className    = 'adj-timeline-item';
        el.dataset.fecha = item.fecha_asignacion || '';
        el.style.cssText = 'display:flex; align-items:flex-start; gap:12px; margin-bottom:12px; position:relative; z-index:1;';
        el.innerHTML = `
            <div style="width:32px; height:32px; border-radius:50%; background:${badgeColor};
                        display:flex; align-items:center; justify-content:center;
                        flex-shrink:0; margin-top:4px; box-shadow:0 0 0 3px white, 0 0 0 4px ${badgeColor}40;">
                <i class="fa-solid fa-user" style="color:white; font-size:11px;"></i>
            </div>
            <div style="background:#fff; ${cardBorder} border-radius:0.375rem; padding:0.875rem 1rem; flex:1;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                    <div>
                        <div style="font-weight:500; font-size:14px;">${item.nombre_despacho || '—'}</div>
                        <div style="font-size:12px; color:#697a8d;">${item.puesto_despacho || '—'}</div>
                    </div>
                    <span style="background:${badgeColor}; color:white; font-size:10px;
                                 padding:2px 8px; border-radius:10px; font-weight:500; flex-shrink:0;">
                        ${badgeLabel}
                    </span>
                </div>
                <div style="margin-top:8px; display:flex; flex-wrap:wrap; gap:12px; font-size:12px; color:#697a8d;">
                    <span>Desde: <strong style="color:#566a7f;">${item.fecha_asignacion || '—'}</strong></span>
                    ${fechaFin}
                </div>
            </div>
        `;
        cont.appendChild(el);
    });
}

// ============================================================================
// REGISTRAR GESTOR
// ============================================================================
function adjAbrirModalRegistrarGestor() {
    document.getElementById('adj-reg-telefono').value = '';
    document.getElementById('adj-reg-correo').value   = '';

    const modalEl = document.getElementById('adj-modalRegistrarGestor');
    let modal = bootstrap.Modal.getInstance(modalEl);
    if (!modal) modal = new bootstrap.Modal(modalEl);
    modal.show();

    if (!adjPersonasCargadas) {
        adjCargarTodasPersonas();
    }
}

function adjCargarTodasPersonas() {
    const spinner = document.getElementById('adj-reg-spinner');
    spinner.style.display = 'block';

    fetch('/Adjudicacion/obtenerTodasPersonas')
        .then(r => r.json())
        .then(data => {
            spinner.style.display = 'none';
            if (!data.success || !data.personas) return;

            adjPersonasMap = {};
            const select = document.getElementById('adj-reg-persona-select');
            select.innerHTML = '<option value="">Seleccione una persona...</option>';

            data.personas.forEach(p => {
                adjPersonasMap[String(p.id)] = p;
                const opt = document.createElement('option');
                opt.value       = p.id;
                opt.textContent = p.nombre_completo;
                select.appendChild(opt);
            });

            // Destruir wrapper previo si existe
            if (adjRegSelectInstance && adjRegSelectInstance.wrapper) {
                adjRegSelectInstance.wrapper.remove();
                select.style.display = '';
            }

            adjRegSelectInstance = new AdjSearchableSelect(select);

            // Auto-llenar tel y correo al seleccionar
            select.addEventListener('change', function () {
                const persona = adjPersonasMap[String(this.value)];
                document.getElementById('adj-reg-telefono').value = persona ? (persona.telefono || '') : '';
                document.getElementById('adj-reg-correo').value   = persona ? (persona.correo   || '') : '';
            });

            adjPersonasCargadas = true;
        })
        .catch(() => {
            spinner.style.display = 'none';
        });
}

function adjRegistrarGestor() {
    const select    = document.getElementById('adj-reg-persona-select');
    const idPersona = select.value;
    const telefono  = document.getElementById('adj-reg-telefono').value.trim();
    const correo    = document.getElementById('adj-reg-correo').value.trim();

    if (!idPersona) {
        Swal.fire('Advertencia', 'Seleccione una persona', 'warning');
        return;
    }

    const btn = document.getElementById('adj-reg-btn-guardar');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Guardando...';

    fetch('/Adjudicacion/registrarGestor', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_persona: idPersona, telefono, correo })
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-save me-1"></i>Guardar gestor';
        if (data.success) {
            Swal.fire({ icon: 'success', title: '¡Registrado!', text: data.message, timer: 2000, showConfirmButton: false });
            bootstrap.Modal.getInstance(document.getElementById('adj-modalRegistrarGestor')).hide();
            adjCargarResponsables();
        } else {
            Swal.fire('Error', data.message || 'No se pudo registrar el gestor', 'error');
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-save me-1"></i>Guardar gestor';
        Swal.fire('Error', 'Error al conectar con el servidor', 'error');
    });
}

// ============================================================================
// ESTADO DE CUENTA — navegar con crédito pre-cargado
// ============================================================================
function adjIrEstadoCuenta(idCredito) {
    sessionStorage.setItem('adj_credito_ec', String(idCredito));
    window.location.href = '/EstadoCuenta/Consulta';
}

// ============================================================================
// UTILIDADES
// ============================================================================
function adjFormatearMoneda(valor) {
    return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(valor);
}

// ============================================================================
// SELECT CON BÚSQUEDA (SearchableSelect adaptado para adjudicación)
// ============================================================================
class AdjSearchableSelect {
    constructor(selectElement) {
        this.select       = selectElement;
        this.options      = [];
        this.selectedValue = '';
        this.isOpen       = false;
        this._createWrapper();
        this._attachEvents();
        this._loadOptions();
    }

    _createWrapper() {
        this.wrapper = document.createElement('div');
        this.wrapper.className = 'select-search-wrapper';

        this.display = document.createElement('div');
        this.display.className = 'select-search-display';
        this.display.innerHTML = '<span>Seleccione...</span><i class="fas fa-chevron-down select-search-arrow"></i>';

        this.dropdown = document.createElement('div');
        this.dropdown.className = 'select-search-dropdown';
        this.dropdown.innerHTML = `
            <input type="text" class="select-search-input" placeholder="Buscar responsable...">
            <div class="select-search-options"></div>`;

        this.wrapper.appendChild(this.display);
        this.wrapper.appendChild(this.dropdown);
        this.select.parentNode.insertBefore(this.wrapper, this.select.nextSibling);
        this.select.style.display = 'none';

        this.searchInput       = this.dropdown.querySelector('.select-search-input');
        this.optionsContainer  = this.dropdown.querySelector('.select-search-options');
        this.arrow             = this.display.querySelector('.select-search-arrow');
    }

    _loadOptions() {
        this.options = Array.from(this.select.options)
            .filter(o => o.value !== '')
            .map(o => ({ value: o.value, text: o.textContent }));
        this._renderOptions(this.options);
    }

    _renderOptions(list) {
        this.optionsContainer.innerHTML = '';
        if (list.length === 0) {
            const el = document.createElement('div');
            el.className = 'select-search-option no-results';
            el.textContent = 'No se encontraron resultados';
            this.optionsContainer.appendChild(el);
            return;
        }
        list.forEach(opt => {
            const el = document.createElement('div');
            el.className = 'select-search-option' + (opt.value === this.selectedValue ? ' selected' : '');
            el.textContent = opt.text;
            el.dataset.value = opt.value;
            el.addEventListener('click', () => this._selectOption(opt));
            this.optionsContainer.appendChild(el);
        });
    }

    _selectOption(opt) {
        this.selectedValue = opt.value;
        this.select.value  = opt.value;
        this.display.querySelector('span').textContent = opt.text;
        this.select.dispatchEvent(new Event('change', { bubbles: true }));
        this._close();
    }

    _open() {
        this.isOpen = true;
        this.dropdown.classList.add('show');
        this.display.classList.add('active');
        this.arrow.classList.add('open');
        this.searchInput.value = '';
        this.searchInput.focus();
        this._loadOptions();
    }

    _close() {
        this.isOpen = false;
        this.dropdown.classList.remove('show');
        this.display.classList.remove('active');
        this.arrow.classList.remove('open');
        this.searchInput.value = '';
    }

    _attachEvents() {
        this.display.addEventListener('click', e => {
            e.stopPropagation();
            this.isOpen ? this._close() : this._open();
        });
        this.searchInput.addEventListener('input', e => {
            const term = e.target.value.toLowerCase().trim();
            this._renderOptions(this.options.filter(o => o.text.toLowerCase().includes(term)));
        });
        this.dropdown.addEventListener('click', e => e.stopPropagation());
        document.addEventListener('click', () => { if (this.isOpen) this._close(); });
    }
}
</script>
