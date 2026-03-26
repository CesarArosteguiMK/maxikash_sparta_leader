<style>
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

    /* Toggle de asignación */
    #asignacion-info-container [onclick] {
        transition: background-color 0.2s ease;
        padding: 0.25rem;
        border-radius: 0.25rem;
    }

    #asignacion-info-container [onclick]:hover {
        background-color: rgba(0,0,0,0.03);
    }

    #asignacion-toggle-icon {
        transition: transform 0.3s ease;
    }

    /* Estilos para stack de créditos */
    #creditos-stack {
        max-height: 520px; /* Altura aproximada para 2 créditos completos */
        overflow-y: auto;
        overflow-x: hidden;
        padding-right: 0.25rem;
    }

    #creditos-stack::-webkit-scrollbar {
        width: 6px;
    }

    #creditos-stack::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    #creditos-stack::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 10px;
    }

    #creditos-stack::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    .credit-card-item {
        animation: slideInDown 0.3s ease-out;
        margin-bottom: 1rem;
    }

    @keyframes slideInDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .credit-card-item.removing {
        animation: slideOutUp 0.3s ease-out;
    }

    @keyframes slideOutUp {
        to {
            opacity: 0;
            transform: translateY(-20px);
        }
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

    /* Texto de estado con gradiente */
    .text-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-weight: bold;
    }

    /* Estilos para información del despacho tipo labels */
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
        color: #696cff;
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

    /* Estilos para Select con Búsqueda - Despachos */
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
        border-color: #696cff;
    }

    .select-search-display.active {
        border-color: #696cff;
        box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.25);
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
        background-color: #696cff;
        color: #fff;
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

    /* Estilos para edición de tipo persona */
    .edit-tipo-icon {
        transition: color 0.2s ease;
    }

    .edit-tipo-icon:hover {
        color: #696cff !important;
    }

    /* Estilos para acordeón padre de documentos */
    #accordionDocumentosPadre .accordion-item {
        border: 1px solid #d9dee3;
        margin-bottom: 0;
    }

    #accordionDocumentosPadre .accordion-button {
        background-color: transparent;
        font-weight: 500;
        font-size: 0.9375rem;
        color: #697a8d;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #d9dee3;
    }

    #accordionDocumentosPadre .accordion-button:not(.collapsed) {
        background-color: transparent;
        color: #696cff;
        box-shadow: none;
        border-bottom: 1px solid #d9dee3;
    }

    #accordionDocumentosPadre .accordion-button:not(.collapsed) i {
        color: #696cff;
    }

    #accordionDocumentosPadre .accordion-button:focus {
        box-shadow: none;
        border-color: #d9dee3;
    }

    #accordionDocumentosPadre .accordion-button::after {
        background-size: 1.25rem;
    }

    #accordionDocumentosPadre .accordion-body {
        background-color: #fff;
        padding: 1rem 1.25rem;
    }

    /* Estilos para acordeón de documentos internos */
    #accordionDocumentos .accordion-item {
        border: 1px solid #d9dee3;
        margin-bottom: 0.75rem;
        border-radius: 0.375rem;
    }

    #accordionDocumentos .accordion-button {
        background-color: transparent;
        font-weight: 400;
        font-size: 0.875rem;
        padding: 0.75rem 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #697a8d;
    }

    #accordionDocumentos .accordion-button:not(.collapsed) {
        background-color: rgba(105, 108, 255, 0.08);
        color: #696cff;
        box-shadow: none;
    }

    #accordionDocumentos .accordion-button:focus {
        box-shadow: none;
        border-color: #d9dee3;
    }

    #accordionDocumentos .accordion-body {
        padding: 1rem;
        font-size: 0.875rem;
        background-color: #fff;
    }

    #accordionDocumentos .badge {
        font-size: 0.7rem;
        padding: 0.35em 0.65em;
        margin-left: auto !important;
        flex-shrink: 0;
        font-weight: 500;
    }

    #accordionDocumentos .accordion-button::after {
        margin-left: 0.5rem !important;
        background-size: 1.125rem;
    }

    #accordionDocumentos .alert-info {
        background-color: rgba(3, 195, 236, 0.12);
        border: 1px solid rgba(3, 195, 236, 0.4);
        color: #03a9f4;
        padding: 0.75rem;
        border-radius: 0.375rem;
    }

    /* Estilos para modal visualizador PDF */
    #modalVisualizadorPDF .modal-xl {
        max-width: 95%;
    }

    #modalVisualizadorPDF .modal-body {
        background-color: #525659;
    }

    #iframeVisualizadorPDF {
        background-color: #fff;
    }

    @media (max-width: 768px) {
        #modalVisualizadorPDF .modal-body {
            height: 70vh !important;
        }
    }

</style>

<!-- Título de la página -->
<h4 class="mb-4">
    <i class="fa-solid fa-briefcase me-2"></i>
    Asignación de Créditos
</h4>

<div class="row g-4 mb-4">
    <!-- PANEL IZQUIERDO -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title mb-4">
                    <i class="fa-solid fa-user-tie me-2"></i>Datos
                </h5>

                <div class="mt-2 mb-3">

    <div class="d-flex gap-4">
        <div class="form-check">
            <input class="form-check-input" type="radio" name="id_celula" id="radioDespacho" value="1" checked>
            <label class="form-check-label" for="radioDespacho" style="cursor: pointer;">
                Despacho
            </label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="id_celula" id="radioCallCenter" value="2">
            <label class="form-check-label" for="radioCallCenter" style="cursor: pointer;">
                Gestión Call Center
            </label>
        </div>
    </div>
</div>

<div class="mb-3">
    <label for="select-despacho" class="form-label fw-bold text-muted small">Seleccionar</label>
    <select id="select-despacho" class="form-select">
        </select>
</div>

                <div class="mb-3">
                    <label class="form-label" for="select-despacho">Seleccionar</label>
                    <select id="select-despacho" class="form-select">
                        <option value="">Seleccione...</option>
                    </select>
                </div>

                <!-- Información del Despacho con diseño tipo labels -->
                <div id="info-despacho-container" style="display: none;">
                    <hr class="my-3">
                    <small class="card-text text-uppercase text-body-secondary small">Información del Despacho</small>
                    <ul class="list-unstyled my-2 py-1 info-compact">
                        <li id="info-nombre-container">
                            <i class="fa fa-user fa-lg text-primary"></i>
                            <div class="info-label">
                                <span class="fw-medium">Nombre:</span>
                                <span id="info-nombre">-</span>
                            </div>
                        </li>

                        <li id="info-puesto-container">
                            <i class="fa fa-briefcase fa-lg text-primary"></i>
                            <div class="info-label">
                                <span class="fw-medium">Puesto:</span>
                                <span id="info-puesto">-</span>
                            </div>
                        </li>

                        <li id="info-telefono-container">
                            <i class="fa fa-phone fa-lg text-primary"></i>
                            <div class="info-label">
                                <span class="fw-medium">Teléfono:</span>
                                <span id="info-telefono">-</span>
                            </div>
                        </li>

                        <li id="info-correo-container">
                            <i class="fa fa-envelope fa-lg text-primary"></i>
                            <div class="info-label">
                                <span class="fw-medium">Correo:</span>
                                <span id="info-correo">-</span>
                            </div>
                        </li>

                        <li id="info-direccion-container">
                            <i class="fa fa-map-marker-alt fa-lg text-primary"></i>
                            <div class="info-label">
                                <span class="fw-medium">Dirección:</span>
                                <span id="info-direccion">-</span>
                            </div>
                        </li>

                        <li id="info-tipo-container">
                            <i class="fa fa-id-card fa-lg text-primary"></i>
                            <div class="info-label">
                                <span class="fw-medium">Tipo:</span>
                                <div class="d-flex align-items-center gap-2">
                                    <span id="info-tipo">-</span>
                                    <i class="fa fa-pencil-alt text-muted edit-tipo-icon"
                                       style="cursor: pointer; font-size: 0.85rem;"
                                       onclick="toggleEditTipo()"
                                       title="Editar tipo de persona"></i>
                                    <select id="select-tipo-persona" class="form-select form-select-sm"
                                            style="display: none; width: auto; min-width: 120px;"
                                            onchange="actualizarTipoPersona()"
                                            onblur="setTimeout(cancelarEditTipo, 200)">
                                        <option value="">Seleccionar...</option>
                                        <option value="FISICA">FISICA</option>
                                        <option value="MORAL">MORAL</option>
                                    </select>
                                </div>
                            </div>
                        </li>

                        <li id="info-sin-datos-container" style="display: none;">
                            <i class="fa fa-exclamation-circle fa-lg text-muted"></i>
                            <div class="info-label">
                                <span class="text-muted">Sin datos de contacto</span>
                            </div>
                        </li>
                    </ul>
                </div>

                <hr class="my-4">

                <!-- Acordeón padre para Documentos del Despacho -->
                <div class="accordion" id="accordionDocumentosPadre">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingDocumentos">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseDocumentos" aria-expanded="false" aria-controls="collapseDocumentos">
                                <i class="fa-solid fa-file-alt me-2"></i>
                                Documentos del Despacho
                            </button>
                        </h2>
                        <div id="collapseDocumentos" class="accordion-collapse collapse"
                             aria-labelledby="headingDocumentos" data-bs-parent="#accordionDocumentosPadre">
                            <div class="accordion-body p-2">
                                <div class="accordion" id="accordionDocumentos">
                                    <!-- Los documentos se cargarán dinámicamente aquí -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <h5 class="card-title mb-3">
                    <i class="fa-solid fa-comment me-2"></i>Mis comentarios
                </h5>

                <div class="mb-3">
                    <textarea id="comentarios-despacho" class="form-control" rows="3" placeholder="Notas internas..."></textarea>
                </div>

                <button class="btn btn-primary w-100" id="btn-guardar-comentarios">
                    <i class="fa-solid fa-save me-1"></i>Guardar Comentarios
                </button>
            </div>
        </div>
    </div>

    <!-- PANEL DERECHO -->
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title mb-0">
                        <i class="fa-solid fa-magnifying-glass me-2"></i>Buscar y asignar crédito
                    </h5>
                    <button class="btn btn-outline-secondary btn-sm" id="btn-refresh-tabla" onclick="refreshTablaCreditos()" title="Actualizar tabla de créditos asignados">
                        <i class="fa-solid fa-rotate-right me-1"></i>Actualizar tabla
                    </button>
                </div>

                <div class="alert alert-info mb-4">
                    <i class="fa-solid fa-info-circle me-2"></i>
                    Busque un crédito por su ID y asígnelo al despacho seleccionado
                </div>

                <!-- Filtros -->
                <div class="row justify-content-between mb-3">
                    <div class="col-12">
                        <label class="form-label">Filtro</label>
                        <div class="input-group input-group-merge">
                            <div class="form-check form-check-inline me-3">
                                <input class="form-check-input" type="radio" name="modoBusquedaDespacho" id="modoBusquedaID" value="id" checked>
                                <label class="form-check-label" for="modoBusquedaID">ID de crédito</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulario de búsqueda -->
                <form id="formBusquedaCredito">
                    <div class="row g-3 align-items-end">
                        <!-- ID de Crédito -->
                        <div class="col-md-9" id="divIDCredito">
                            <label for="idCredito" class="form-label">ID de crédito</label>
                            <div class="input-group input-group-merge">
                                <input type="number" class="form-control" id="idCredito" name="idCredito"
                                       placeholder="Ej.: 12345">
                                <span class="input-group-text"><i class="fa fa-hashtag"></i></span>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100" id="btn-buscar-credito">
                                <i class="fa-solid fa-search me-1"></i>Buscar
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Stack de créditos buscados -->
                <div id="creditos-stack" class="mt-4">
                    <!-- Los créditos se agregarán dinámicamente aquí -->
                </div>

                <!-- Botón para limpiar lista -->
                <div id="btn-limpiar-container" class="mt-3" style="display: none;">
                    <button class="btn btn-outline-danger w-100" onclick="limpiarListaCreditos()">
                        <i class="fa-solid fa-trash me-2"></i>Limpiar lista
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para visualizar PDF -->
<div class="modal fade" id="modalVisualizadorPDF" tabindex="-1" aria-labelledby="modalVisualizadorPDFLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalVisualizadorPDFLabel">
                    <i class="fa-solid fa-file-pdf me-2"></i>Visualizador de Documento
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="height: 80vh;">
                <iframe id="iframeVisualizadorPDF" style="width: 100%; height: 100%; border: none;" src=""></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fa-solid fa-times me-1"></i>Cerrar
                </button>
                <a id="btnDescargarPDFModal" href="#" class="btn btn-primary" download>
                    <i class="fa-solid fa-download me-1"></i>Descargar
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL: Historial de Gestores y Convenios                    -->
<!-- Agregar justo antes del cierre del body, junto a los otros  -->
<!-- modales del archivo asignacion_creditosDespacho.php         -->
<!-- ============================================================ -->

<div class="modal fade" id="modalHistorialGestores" tabindex="-1"
     aria-labelledby="modalHistorialGestoresLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border: none; border-radius: 0.5rem; overflow: hidden;">

            <!-- HEADER -->
            <div class="modal-header" style="background: #696cff; padding: 1.25rem 1.5rem; border: none;">
                <div class="d-flex align-items-center gap-2">
                    <div style="width:36px; height:36px; border-radius:8px; background:rgba(255,255,255,0.2);
                                display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <i class="fa-solid fa-users" style="color:white; font-size:14px;"></i>
                    </div>
                    <div>
                        <div style="color:white; font-weight:500; font-size:15px; line-height:1.2;">
                            Historial de Gestores y Convenios
                        </div>
                        <div style="color:rgba(0, 0, 0, 0.04); font-size:12px;" id="hgc-credito-label">
                            Crédito #—
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Cerrar"></button>
            </div>

            <!-- TABS NAV -->
            <div style="border-bottom:1px solid #e0e0e0; background:#f8f7ff; padding: 0 1.5rem;">
                <ul class="nav" id="hgcTabs" role="tablist" style="gap:0; margin:0; flex-wrap:nowrap;">

                    <li class="nav-item" role="presentation">
                        <button class="hgc-tab-btn active" id="hgc-tab-datos"
                                data-panel="hgc-panel-datos"
                                style="padding:0.875rem 1.25rem; border:none; background:transparent;
                                       cursor:pointer; font-size:13px; font-weight:500; color:#696cff;
                                       border-bottom:2px solid #696cff; display:flex; align-items:center; gap:6px;">
                            <i class="fa-solid fa-table-cells-large" style="font-size:12px;"></i>
                            Datos Generales
                        </button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button class="hgc-tab-btn" id="hgc-tab-historial"
                                data-panel="hgc-panel-historial"
                                style="padding:0.875rem 1.25rem; border:none; background:transparent;
                                       cursor:pointer; font-size:13px; font-weight:500; color:#697a8d;
                                       border-bottom:2px solid transparent; display:flex; align-items:center; gap:6px;">
                            <i class="fa-solid fa-clock-rotate-left" style="font-size:12px;"></i>
                            Historial de Gestores
                            <span id="hgc-badge-historial"
                                  style="background:#696cff; color:white; font-size:10px;
                                         padding:2px 7px; border-radius:10px; font-weight:500;">0</span>
                        </button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button class="hgc-tab-btn" id="hgc-tab-convenios"
                                data-panel="hgc-panel-convenios"
                                style="padding:0.875rem 1.25rem; border:none; background:transparent;
                                       cursor:pointer; font-size:13px; font-weight:500; color:#697a8d;
                                       border-bottom:2px solid transparent; display:flex; align-items:center; gap:6px;">
                            <i class="fa-solid fa-file-contract" style="font-size:12px;"></i>
                            Convenios
                            <span id="hgc-badge-convenios"
                                  style="background:#28a745; color:white; font-size:10px;
                                         padding:2px 7px; border-radius:10px; font-weight:500;">0</span>
                        </button>
                    </li>

                </ul>
            </div>

            <!-- BODY -->
            <div class="modal-body" style="padding:1.5rem; min-height:420px;">

                <!-- Spinner de carga global -->
                <div id="hgc-spinner" class="text-center py-5" style="display:none;">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="mt-2 text-muted small">Cargando información...</div>
                </div>

                <!-- -------------------------------------------------- -->
                <!-- PANEL 1: Datos Generales                            -->
                <!-- -------------------------------------------------- -->
                <div id="hgc-panel-datos">

                    <!-- Métricas rápidas -->
                    <div class="row g-3 mb-4">
                        <div class="col-4">
                            <div class="text-center p-3 rounded-2" style="background:#fff3f3; border:0.5px solid #ffc5c5;">
                                <div class="text-muted mb-1" style="font-size:11px; text-transform:uppercase; letter-spacing:.5px;">
                                    Saldo vencido
                                </div>
                                <div style="font-size:20px; font-weight:500; color:#dc3545;" id="hgc-saldo">—</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-center p-3 rounded-2" style="background:#fff8f0; border:0.5px solid #ffd8a8;">
                                <div class="text-muted mb-1" style="font-size:11px; text-transform:uppercase; letter-spacing:.5px;">
                                    Días de mora
                                </div>
                                <div style="font-size:20px; font-weight:500; color:#fd7e14;" id="hgc-mora">—</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-center p-3 rounded-2" style="background:#f0f0ff; border:0.5px solid #c5c6ff;">
                                <div class="text-muted mb-1" style="font-size:11px; text-transform:uppercase; letter-spacing:.5px;">
                                    Gestores asignados
                                </div>
                                <div style="font-size:20px; font-weight:500; color:#696cff;" id="hgc-total-gestores">—</div>
                            </div>
                        </div>
                    </div>

                    <!-- Card cliente -->
                    <div class="card mb-3" style="border:0.5px solid #e0e0e0;">
                        <div class="card-body p-3">
                            <div class="text-uppercase text-muted mb-3"
                                 style="font-size:11px; letter-spacing:.5px; font-weight:500;">
                                Datos del cliente
                            </div>

                            <!-- Cabecera cliente -->
                            <div class="d-flex align-items-center gap-3 pb-3 mb-3"
                                 style="border-bottom:0.5px solid #f0f0f0;">
                                <div id="hgc-avatar"
                                     style="width:44px; height:44px; border-radius:50%; background:#e8e8ff;
                                            display:flex; align-items:center; justify-content:center;
                                            font-weight:500; font-size:13px; color:#696cff; flex-shrink:0;">
                                    —
                                </div>
                                <div class="flex-grow-1">
                                    <div style="font-weight:500; font-size:15px;" id="hgc-nombre-cliente">—</div>
                                    <div class="text-muted" style="font-size:12px;" id="hgc-curp">—</div>
                                </div>
                                <span id="hgc-estatus-badge" class="badge bg-success">Activo</span>
                            </div>

                            <!-- Grid de datos -->
                            <div class="row g-3" style="font-size:13px;">
                                <div class="col-6 col-md-3">
                                    <div class="text-muted">Teléfono</div>
                                    <div class="fw-medium mt-1" id="hgc-telefono">—</div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="text-muted">Sucursal</div>
                                    <div class="fw-medium mt-1" id="hgc-sucursal">—</div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="text-muted">Fecha desembolso</div>
                                    <div class="fw-medium mt-1" id="hgc-fecha-desembolso">—</div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="text-muted">ID Crédito</div>
                                    <div class="fw-medium mt-1" id="hgc-id-credito-detalle">—</div>
                                </div>
                                <div class="col-12">
                                    <div class="text-muted">Dirección</div>
                                    <div class="fw-medium mt-1" id="hgc-direccion">—</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card gestor actual -->
                    <div class="card" style="border:0.5px solid #c5c6ff; background:#f0f0ff;" id="hgc-gestor-actual-card">
                        <div class="card-body p-3">
                            <div class="text-uppercase mb-2"
                                 style="font-size:11px; letter-spacing:.5px; font-weight:500; color:#696cff;">
                                <i class="fa-solid fa-user-check me-1"></i> Gestor actual
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <div id="hgc-gestor-avatar"
                                     style="width:38px; height:38px; border-radius:50%; background:#696cff;
                                            display:flex; align-items:center; justify-content:center;
                                            font-size:12px; font-weight:500; color:white; flex-shrink:0;">
                                    —
                                </div>
                                <div class="flex-grow-1">
                                    <div style="font-weight:500; font-size:14px;" id="hgc-gestor-nombre">—</div>
                                    <div class="text-muted" style="font-size:12px;" id="hgc-gestor-info">—</div>
                                </div>
                                <span class="badge bg-primary">Activo</span>
                            </div>
                        </div>
                    </div>

                    <!-- Sin gestor activo -->
                    <div class="alert alert-warning mt-3" id="hgc-sin-gestor" style="display:none;">
                        <i class="fa-solid fa-circle-exclamation me-2"></i>
                        Este crédito no tiene gestor activo actualmente.
                    </div>

                </div><!-- /panel-datos -->

                <!-- -------------------------------------------------- -->
                <!-- PANEL 2: Historial de Gestores                      -->
                <!-- -------------------------------------------------- -->
                <div id="hgc-panel-historial" style="display:none;">

                    <p class="text-muted mb-3" style="font-size:12px;">
                        Todos los gestores que han tenido asignado este crédito, del más reciente al más antiguo.
                    </p>

                    <!-- Timeline -->
                    <div id="hgc-timeline" style="position:relative;">
                        <!-- Línea vertical decorativa -->
                        <div style="position:absolute; left:16px; top:24px; bottom:8px; width:2px;
                                    background:#e0e0e0; z-index:0;"></div>
                        <!-- Los items se generan dinámicamente -->
                    </div>

                    <!-- Estado vacío -->
                    <div id="hgc-historial-vacio" class="text-center py-5 text-muted" style="display:none;">
                        <i class="fa-solid fa-clock-rotate-left fa-2x mb-2 d-block opacity-25"></i>
                        Sin historial de gestores para este crédito
                    </div>

                </div><!-- /panel-historial -->

                <!-- -------------------------------------------------- -->
                <!-- PANEL 3: Convenios                                  -->
                <!-- -------------------------------------------------- -->
                <div id="hgc-panel-convenios" style="display:none;">

                    <p class="text-muted mb-3" style="font-size:12px;">
                        Acuerdos de pago registrados para este crédito.
                    </p>

                    <div id="hgc-convenios-lista">
                        <!-- Los convenios se generan dinámicamente -->
                    </div>

                    <!-- Estado vacío -->
                    <div id="hgc-convenios-vacio" class="text-center py-5 text-muted" style="display:none;">
                        <i class="fa-solid fa-file-contract fa-2x mb-2 d-block opacity-25"></i>
                        Sin convenios registrados para este crédito
                    </div>

                </div><!-- /panel-convenios -->

            </div><!-- /modal-body -->

            <!-- FOOTER -->
            <div class="modal-footer" style="background:#f8f7ff; border-top:0.5px solid #e0e0e0; padding:.875rem 1.5rem;">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="fa-solid fa-times me-1"></i>Cerrar
                </button>
                <button type="button" class="btn btn-primary btn-sm" id="hgc-btn-exportar" style="display:none;">
                    <i class="fa-solid fa-download me-1"></i>Exportar
                </button>
            </div>

        </div><!-- /modal-content -->
    </div><!-- /modal-dialog -->
</div><!-- /modal -->

<!-- TABLA DE CRÉDITOS ASIGNADOS -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fa-solid fa-list me-2"></i>Créditos asignados al despacho
        </h5>
        <div class="d-flex gap-2">
            <button class="btn btn-success btn-sm" id="btn-exportar-excel">
                <i class="fa-solid fa-file-excel me-1"></i>Exportar Excel
            </button>
            <button class="btn btn-primary btn-sm" id="btn-importar-excel" type="button" data-bs-toggle="modal" data-bs-target="#modal-importar-excel">
                <i class="fa-solid fa-file-import me-1"></i>Importar Excel
            </button>
        </div>
    </div>

    <div class="card-datatable table-responsive">
        <table class="table border-top" id="tabla-creditos">
            <thead>
                <tr>
                    <th>ID Crédito</th>
                    <th>Estado</th>
                    <th>Fecha Asignación</th>
                    <th>Asignado Por</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tbody-creditos">
            </tbody>
        </table>
    </div>
</div>

<style>
/* Popover con tabla: anclado al botón junto a “Descargar plantilla” */
#import-despacho-import-popover-wrap {
    position: relative;
    z-index: 2;
}
#import-despacho-import-popover-wrap.is-popover-open {
    z-index: 1060;
}
#import-despacho-import-popover {
    position: absolute;
    left: 0;
    top: 100%;
    margin-top: 10px;
    z-index: 1061;
    width: 100%;
    max-width: 100%;
    display: none;
}
#import-despacho-import-popover .import-despacho-popover-inner {
    position: relative;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.15);
    padding: 0.65rem 0.75rem;
}
#import-despacho-import-popover .import-despacho-popover-arrow {
    position: absolute;
    width: 0;
    height: 0;
    left: var(--arrow-left, 120px);
    transform: translateX(-50%);
    top: -8px;
    border-left: 8px solid transparent;
    border-right: 8px solid transparent;
    border-bottom: 8px solid #fff;
    filter: drop-shadow(0 -1px 0 #e5e7eb);
}
#import-despacho-import-popover .import-despacho-popover-title {
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 0.4rem;
    color: #334155;
}
#import-despacho-import-popover .table-despacho-import {
    font-size: 12px;
    margin-bottom: 0;
}
#import-despacho-import-popover .table-despacho-import th {
    background: #f1f5f9;
    color: #334155;
    font-weight: 600;
    white-space: nowrap;
}
#import-despacho-import-popover .table-despacho-import td {
    vertical-align: middle;
    word-break: break-word;
}
#import-despacho-import-popover .import-despacho-popover-msg {
    font-size: 12px;
    color: #64748b;
    line-height: 1.4;
    margin: 0;
}
#import-despacho-import-popover .import-despacho-popover-scroll {
    max-height: min(55vh, 380px);
    overflow-y: auto;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
}
</style>

<!-- MODAL: IMPORTAR EXCEL -->
<div class="modal fade" id="modal-importar-excel" tabindex="-1" aria-labelledby="modal-importar-excel-label" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;">
            <div class="modal-header" style="background:#f8f7ff;">
                <h5 class="modal-title" id="modal-importar-excel-label">
                    <i class="fa-solid fa-file-import me-2"></i>Importar Excel
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div class="p-3" style="background:#f8fafc; border:1px solid #e5e7eb; border-radius:12px;">
                    <div class="fw-semibold mb-2">Subir el excel usando la plantilla</div>
                    <div id="import-despacho-import-popover-wrap">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="btn-descargar-plantilla-excel">
                                <i class="fa-solid fa-download me-1"></i>Descargar plantilla.xlsx
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-ver-datos-despacho-import" title="Catálogo completo: nombre del despacho e id_despacho (sin elegir arriba)">
                                <i class="fa-solid fa-table me-1"></i>Nombre e id_despacho
                            </button>
                            <span class="text-muted small">Usa la plantilla para que no falle la validación de columnas.</span>
                        </div>
                        <div id="import-despacho-import-popover" role="tooltip" aria-hidden="true">
                            <div class="import-despacho-popover-inner">
                                <div class="import-despacho-popover-arrow" aria-hidden="true"></div>
                                <div class="import-despacho-popover-title">Catálogo nombre → id_despacho</div>
                                <div id="import-despacho-import-popover-content"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <div class="fw-semibold mb-1" style="font-size:13px;">Notas</div>
                        <ul class="mb-0 small text-muted">
                            <li>No debe cambiar el nombre de las columnas.</li>
                            <li>La plantilla define la estructura esperada: columnas <code>id_credito</code> e <code>id_despacho</code> (esta última es clave para asignar al despacho correcto).</li>
                            <li>El encabezado debe estar en la fila 1 (como en la plantilla).</li>
                            <li>Los datos se leen desde la primera hoja del Excel.</li>
                            <li>Se verifica que los datos sean correctos (IDs numéricos y válidos).</li>
                            <li>Si un crédito ya está asignado al mismo despacho en la base, se reporta como duplicado (no se vuelve a insertar).</li>
                        </ul>
                    </div>
                </div>

                <div class="mt-3">
                    <label class="form-label fw-semibold">Adjuntar excel</label>
                    <input class="form-control" type="file" id="input-excel-import" accept=".xlsx,.xls" multiple>
                    <div class="form-text text-muted">Puedes subir varios archivos. El sistema cargará uno por uno.</div>
                </div>

                <div id="import-progreso" class="mt-4" style="display:none;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="fw-semibold">Progreso</div>
                        <div id="import-progress-text" class="text-muted small">0%</div>
                    </div>
                    <div class="progress mt-2" style="height:10px;">
                        <div id="import-progress-bar" class="progress-bar" role="progressbar" style="width:0%;"></div>
                    </div>
                </div>

                <div id="import-resultado" class="mt-3" style="display:none;">
                    <div class="fw-semibold mb-2">Resultado</div>
                    <div id="import-result" class="small"></div>
                </div>
            </div>

            <div class="modal-footer" style="background:#f8f7ff;">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary btn-sm" id="btn-subir-excel-importar">
                    <i class="fa-solid fa-upload me-1"></i>Subir
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Variables globales
let despachoSeleccionado = null;
/** Datos del despacho actual (para el popover de la palabra "plantilla") */
let despachoInfoPlantilla = null;
let importPlantillaPopoverVisible = false;
let creditosEncontrados = []; // Array de créditos en el stack
let searchableSelectDespacho;

/**
 * Clase para Select con búsqueda
 */
class SearchableSelect {
    constructor(selectElement) {
        this.select = selectElement;
        this.options = [];
        this.selectedValue = '';
        this.isOpen = false;

        this.createWrapper();
        this.attachEvents();
        this.loadOptions();
    }

    createWrapper() {
        // Crear wrapper
        this.wrapper = document.createElement('div');
        this.wrapper.className = 'select-search-wrapper';

        // Crear display
        this.display = document.createElement('div');
        this.display.className = 'select-search-display';
        this.display.innerHTML = `
            <span>Seleccione un despacho...</span>
            <i class="fas fa-chevron-down select-search-arrow"></i>
        `;

        // Crear dropdown
        this.dropdown = document.createElement('div');
        this.dropdown.className = 'select-search-dropdown';
        this.dropdown.innerHTML = `
            <input type="text" class="select-search-input" placeholder="Buscar despacho...">
            <div class="select-search-options"></div>
        `;

        // Agregar elementos
        this.wrapper.appendChild(this.display);
        this.wrapper.appendChild(this.dropdown);

        // Insertar después del select y ocultar el select original
        this.select.parentNode.insertBefore(this.wrapper, this.select.nextSibling);
        this.select.style.display = 'none';

        // Referencias
        this.searchInput = this.dropdown.querySelector('.select-search-input');
        this.optionsContainer = this.dropdown.querySelector('.select-search-options');
        this.arrow = this.display.querySelector('.select-search-arrow');
    }

    loadOptions() {
        this.options = Array.from(this.select.options)
            .filter(opt => opt.value !== '')
            .map(opt => ({
                value: opt.value,
                text: opt.textContent
            }));

        this.renderOptions(this.options);
    }

    renderOptions(filteredOptions) {
        this.optionsContainer.innerHTML = '';

        if (filteredOptions.length === 0) {
            const noResults = document.createElement('div');
            noResults.className = 'select-search-option no-results';
            noResults.textContent = 'No se encontraron resultados';
            this.optionsContainer.appendChild(noResults);
            return;
        }

        filteredOptions.forEach(option => {
            const optionDiv = document.createElement('div');
            optionDiv.className = 'select-search-option';
            optionDiv.textContent = option.text;
            optionDiv.dataset.value = option.value;

            if (option.value === this.selectedValue) {
                optionDiv.classList.add('selected');
            }

            optionDiv.addEventListener('click', () => {
                this.selectOption(option);
            });

            this.optionsContainer.appendChild(optionDiv);
        });
    }

    selectOption(option) {
        this.selectedValue = option.value;
        this.select.value = option.value;
        this.display.querySelector('span').textContent = option.text;

        // Disparar evento change en el select original
        const event = new Event('change', { bubbles: true });
        this.select.dispatchEvent(event);

        this.close();
    }

    open() {
        this.isOpen = true;
        this.dropdown.classList.add('show');
        this.display.classList.add('active');
        this.arrow.classList.add('open');
        this.searchInput.value = '';
        this.searchInput.focus();
        this.loadOptions();
    }

    close() {
        this.isOpen = false;
        this.dropdown.classList.remove('show');
        this.display.classList.remove('active');
        this.arrow.classList.remove('open');
        this.searchInput.value = '';
    }

    attachEvents() {
        // Click en display
        this.display.addEventListener('click', (e) => {
            e.stopPropagation();
            if (this.isOpen) {
                this.close();
            } else {
                this.open();
            }
        });

        // Input de búsqueda
        this.searchInput.addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase().trim();
            const filtered = this.options.filter(option =>
                option.text.toLowerCase().includes(searchTerm)
            );
            this.renderOptions(filtered);
        });

        // Evitar que el click en el dropdown lo cierre
        this.dropdown.addEventListener('click', (e) => {
            e.stopPropagation();
        });

        // Cerrar al hacer click fuera
        document.addEventListener('click', () => {
            if (this.isOpen) {
                this.close();
            }
        });
    }

    refresh() {
        this.loadOptions();
        const selectedOption = this.select.options[this.select.selectedIndex];
        if (selectedOption) {
            this.display.querySelector('span').textContent = selectedOption.text;
            this.selectedValue = selectedOption.value;
        } else {
            this.display.querySelector('span').textContent = 'Seleccione un despacho...';
            this.selectedValue = '';
        }
    }
}

// Cargar despachos al iniciar
document.addEventListener('DOMContentLoaded', function() {
    cargarDespachos();

    // Event Listeners
    document.getElementById('select-despacho').addEventListener('change', function() {
        despachoSeleccionado = this.value;
        if (despachoSeleccionado) {
            cargarDatosDespacho(despachoSeleccionado);
            cargarCreditosAsignados(despachoSeleccionado);
        }
    });

    // Buscar crédito al enviar formulario
    document.getElementById('formBusquedaCredito').addEventListener('submit', function(e) {
        e.preventDefault();
        buscarCredito();
    });

    // El botón de asignar ahora está en cada crédito del stack
    document.getElementById('btn-guardar-comentarios').addEventListener('click', guardarComentarios);
    document.getElementById('btn-exportar-excel').addEventListener('click', exportarExcel);

    // Importación de Excel (modal)
    document.getElementById('btn-importar-excel').addEventListener('click', prepararModalImportacionExcel);
    document.getElementById('btn-descargar-plantilla-excel').addEventListener('click', descargarPlantillaExcelImportacion);
    document.getElementById('btn-subir-excel-importar').addEventListener('click', iniciarImportacionExcel);

    const modalImport = document.getElementById('modal-importar-excel');
    if (modalImport) {
        modalImport.addEventListener('click', function (ev) {
            const trigger = ev.target.closest('#btn-ver-datos-despacho-import');
            if (trigger) {
                ev.preventDefault();
                ev.stopPropagation();
                toggleImportPlantillaPopover(trigger);
                return;
            }
            if (!importPlantillaPopoverVisible) return;
            const pop = document.getElementById('import-despacho-import-popover');
            const btn = document.getElementById('btn-ver-datos-despacho-import');
            if (pop && pop.contains(ev.target)) return;
            if (btn && btn.contains(ev.target)) return;
            closeImportPlantillaPopover();
        });
        modalImport.addEventListener('hidden.bs.modal', function () {
            closeImportPlantillaPopover();
        });
    }
});

// Función para cargar lista de despachos
function cargarDespachos() {
    fetch('/despachos/obtenerListaDespachos')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('select-despacho');
            select.innerHTML = '<option value="">Seleccione un despacho...</option>';

            if (data.success && data.despachos && data.despachos.length > 0) {
                // Filtro de unicidad: evitar duplicados visuales por multipuestos
                const idsAgregados = new Set();

                data.despachos.forEach((despacho) => {
                    if (idsAgregados.has(despacho.id_persona)) {
                        return;
                    }
                    idsAgregados.add(despacho.id_persona);

                    const option = document.createElement('option');
                    option.value = despacho.id_persona;
                    // Solo mostramos el nombre puro para evitar confusión visual
                    option.textContent = despacho.nombre_completo;
                    select.appendChild(option);
                });

                // Inicializar SearchableSelect después de cargar opciones únicas
                if (!searchableSelectDespacho) {
                    searchableSelectDespacho = new SearchableSelect(select);
                } else {
                    searchableSelectDespacho.refresh();
                }
            }
        })
        .catch(error => {
            console.error('❌ Error al cargar despachos:', error);
            Swal.fire('Error', 'No se pudieron cargar los despachos: ' + error.message, 'error');
        });
}

// Función para cargar datos del despacho seleccionado
function cargarDatosDespacho(idPersona) {
    fetch(`/despachos/obtenerDatosDespacho/${idPersona}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Mostrar el contenedor de información
                document.getElementById('info-despacho-container').style.display = 'block';

                // Función auxiliar para verificar si un valor está vacío
                const estaVacio = (valor) => {
                    return !valor || valor === '' || valor === 'Sin dirección registrada' || valor === '-' || valor === null;
                };

                // Función auxiliar para mostrar/ocultar campo
                const mostrarCampo = (containerId, valor, elementId) => {
                    const container = document.getElementById(containerId);
                    const element = document.getElementById(elementId);

                    if (!estaVacio(valor)) {
                        container.style.display = 'grid';
                        element.textContent = valor;
                        return true; // Tiene datos
                    } else {
                        container.style.display = 'none';
                        return false; // No tiene datos
                    }
                };

                // Llenar y mostrar/ocultar cada campo
                mostrarCampo('info-nombre-container', data.datos.nombre_completo, 'info-nombre');
                mostrarCampo('info-puesto-container', data.datos.puesto, 'info-puesto');
                const tieneTelefono = mostrarCampo('info-telefono-container', data.datos.telefono, 'info-telefono');
                const tieneCorreo = mostrarCampo('info-correo-container', data.datos.correo, 'info-correo');
                const tieneDireccion = mostrarCampo('info-direccion-container', data.datos.direccion, 'info-direccion');
                mostrarCampo('info-tipo-container', data.datos.tipo_persona, 'info-tipo');

                // Si no tiene teléfono, correo ni dirección, mostrar "Sin Datos"
                const sinDatosContainer = document.getElementById('info-sin-datos-container');
                if (!tieneTelefono && !tieneCorreo && !tieneDireccion) {
                    sinDatosContainer.style.display = 'grid';
                } else {
                    sinDatosContainer.style.display = 'none';
                }

                // Cargar comentarios si existen
                document.getElementById('comentarios-despacho').value = data.comentarios || '';

                // Cargar documentos del despacho
                cargarDocumentosDespacho(idPersona);

                despachoInfoPlantilla = {
                    id_persona: idPersona,
                    id_despacho: data.datos.id_despacho != null && data.datos.id_despacho !== '' ? data.datos.id_despacho : null,
                    nombre_completo: data.datos.nombre_completo || '',
                    puesto: data.datos.puesto || '',
                    telefono: data.datos.telefono || '',
                    correo: data.datos.correo || ''
                };
            }
        })
        .catch(error => {
            console.error('Error al cargar datos del despacho:', error);
        });
}

// Función para buscar crédito
function buscarCredito() {
    const idCredito = document.getElementById('idCredito').value.trim();

    if (!idCredito) {
        Swal.fire('Advertencia', 'Ingrese un ID de crédito', 'warning');
        return;
    }

    fetch('/despachos/buscarCredito', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            tipo: 'id_credito',
            valor: idCredito
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.credito) {
            // Verificar si el crédito ya está en el stack (comparación flexible)
            const creditoExistente = creditosEncontrados.find(c => String(c.credito.id_credito) === String(data.credito.id_credito));

            if (creditoExistente) {
                // Actualizar con datos frescos del servidor
                const itemActualizado = { credito: data.credito, asignacion: data.asignacion };

                // 1. Remover del array y re-insertar con datos frescos al inicio
                creditosEncontrados = creditosEncontrados.filter(item => String(item.credito.id_credito) !== String(data.credito.id_credito));
                creditosEncontrados.unshift(itemActualizado);

                // 2. Remover visualmente y volver a agregar al inicio con datos actualizados
                const card = document.getElementById(`credit-${data.credito.id_credito}`);
                if (card) {
                    card.style.transition = 'all 0.3s ease';
                    card.style.transform = 'translateX(-10px)';
                    card.style.opacity = '0';

                    setTimeout(() => {
                        card.remove();
                        // Re-agregar al inicio con datos frescos
                        agregarCreditoAlStack(data.credito, data.asignacion);

                        const newCard = document.getElementById(`credit-${data.credito.id_credito}`);
                        if (newCard) {
                            newCard.style.transform = 'translateX(10px)';
                            newCard.style.opacity = '0';
                            setTimeout(() => {
                                newCard.style.transition = 'all 0.3s ease';
                                newCard.style.transform = 'translateX(0)';
                                newCard.style.opacity = '1';
                            }, 50);
                        }
                    }, 300);
                }

                // Limpiar campo de búsqueda
                document.getElementById('idCredito').value = '';
                return;
            }

            // Agregar al array (crédito nuevo)
            creditosEncontrados.unshift({
                credito: data.credito,
                asignacion: data.asignacion
            });

            // Agregar visualmente al stack
            agregarCreditoAlStack(data.credito, data.asignacion);

            // Limpiar campo de búsqueda
            document.getElementById('idCredito').value = '';
        } else {
            Swal.fire('No encontrado', 'No se encontró el crédito', 'info');
        }
    })
    .catch(error => {
        console.error('Error al buscar crédito:', error);
        Swal.fire('Error', 'Error al buscar el crédito', 'error');
    });
}

// Función para agregar crédito al stack visual
function agregarCreditoAlStack(credito, asignacion) {
    const stack = document.getElementById('creditos-stack');

    // Mostrar botón de limpiar lista
    document.getElementById('btn-limpiar-container').style.display = 'block';
    const creditoId = `credit-${credito.id_credito}`;

    // Crear card del crédito
    const card = document.createElement('div');
    card.className = 'card border border-primary credit-card-item';
    card.id = creditoId;

    const esActivo = asignacion && (asignacion.estatus === '1' || asignacion.estatus === 1);
    const statusText = asignacion ? (esActivo ? 'Crédito asignado actualmente' : 'Crédito tuvo asignación (inactiva)') : '';
    const statusClass = asignacion ? (esActivo ? 'text-gradient-primary' : 'text-muted') : '';

    const asignacionHTML = asignacion ? `
        <div class="mt-2">
            <hr class="my-2">
            <div class="d-flex align-items-center justify-content-between" style="cursor: pointer;" onclick="toggleAsignacionInfo('${creditoId}')">
                <small class="${statusClass}">
                    <i class="fa-solid fa-info-circle me-1"></i>
                    <span>${statusText}</span>
                </small>
                <i class="fa-solid fa-chevron-down" id="toggle-icon-${creditoId}"></i>
            </div>
            <div class="collapse" id="details-${creditoId}">
                <div class="mt-2 p-2 bg-light rounded" style="font-size: 0.8rem;">
                    <div class="row g-2">
                        <div class="col-12">
                            <strong class="text-primary">Asignado a:</strong>
                            <span>${asignacion.nombre_despacho || 'No especificado'}</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Puesto:</small><br>
                            <span>${asignacion.puesto_despacho || 'No especificado'}</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Estatus:</small><br>
                            <span class="badge ${esActivo ? 'bg-success' : 'bg-secondary'}">${esActivo ? 'Activo' : 'Inactivo'}</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Fecha asignación:</small><br>
                            <span>${asignacion.fecha_asignacion || '-'}</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Asignado por:</small><br>
                            <span>${asignacion.asignado_por || 'Sistema'}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    ` : '';

    card.innerHTML = `
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1" style="font-size: 0.875rem;">
                    <div class="d-flex align-items-center mb-1">
                        <strong class="me-2">ID CREDITO ${credito.id_credito}</strong>
                        <span class="badge bg-warning">${credito.dias_mora || 0} días</span>
                    </div>
                    <div class="mb-1"><strong>Nombre:</strong> ${credito.nombre_cliente}</div>
                    <div class="mb-1"><strong>Dirección:</strong> <span class="text-muted">${credito.direccion || 'Sin dirección'}</span></div>
                    <div><strong>Saldo:</strong> <span class="text-danger fw-bold">${formatearMoneda(credito.saldo_actual || 0)}</span></div>
                </div>
                <div class="d-flex flex-column gap-2 ms-3">
                    <button class="btn btn-gradient-success btn-sm" onclick="asignarCreditoDelStack('${credito.id_credito}')" title="Asignar crédito">
                        <i class="fa-solid fa-check"></i>
                    </button>
                    <button class="btn btn-gradient-danger btn-sm" onclick="descartarCredito('${credito.id_credito}')" title="Descartar">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>
            </div>
            ${asignacionHTML}
        </div>
    `;

    // Insertar al inicio del stack
    stack.insertBefore(card, stack.firstChild);
}

// Función para descartar crédito del stack
function descartarCredito(idCredito) {
    const card = document.getElementById(`credit-${idCredito}`);
    if (card) {
        card.classList.add('removing');
        setTimeout(() => {
            card.remove();
            // Remover del array (comparación flexible)
            creditosEncontrados = creditosEncontrados.filter(item => String(item.credito.id_credito) !== String(idCredito));

            // Ocultar botón de limpiar si no hay más créditos
            if (creditosEncontrados.length === 0) {
                document.getElementById('btn-limpiar-container').style.display = 'none';
            }
        }, 300);
    }
}

// Función para limpiar toda la lista de créditos
function limpiarListaCreditos() {
    Swal.fire({
        title: '¿Limpiar lista?',
        text: 'Se eliminarán todos los créditos consultados de la lista',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, limpiar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Vaciar array
            creditosEncontrados = [];

            // Limpiar DOM
            const stack = document.getElementById('creditos-stack');
            stack.innerHTML = '';

            // Ocultar botón
            document.getElementById('btn-limpiar-container').style.display = 'none';

            Swal.fire('Limpiado', 'La lista ha sido vaciada', 'success');
        }
    });
}

// Función para toggle de información de asignación
function toggleAsignacionInfo(creditoId) {
    const collapseElement = document.getElementById(`details-${creditoId}`);
    const icon = document.getElementById(`toggle-icon-${creditoId}`);

    const bsCollapse = new bootstrap.Collapse(collapseElement, {
        toggle: true
    });

    // Cambiar icono
    setTimeout(() => {
        if (collapseElement.classList.contains('show')) {
            icon.className = 'fa-solid fa-chevron-up';
        } else {
            icon.className = 'fa-solid fa-chevron-down';
        }
    }, 100);
}

// Función para asignar crédito desde el stack
function asignarCreditoDelStack(idCredito) {
    if (!despachoSeleccionado) {
        Swal.fire('Advertencia', 'Seleccione un despacho primero', 'warning');
        return;
    }

    // Comparación flexible para manejar string vs number
    const creditoItem = creditosEncontrados.find(item => String(item.credito.id_credito) === String(idCredito));
    if (!creditoItem) {
        console.error('Crédito no encontrado. ID buscado:', idCredito);
        console.error('Créditos disponibles:', creditosEncontrados);
        Swal.fire('Error', 'Crédito no encontrado en la lista', 'error');
        return;
    }

    // VALIDACIÓN: Verificar si el crédito ya está asignado activamente
    // Para deshabilitar esta validación, comente las siguientes 7 líneas
    if (creditoItem.asignacion) {
        const esActivo = creditoItem.asignacion.estatus === '1' || creditoItem.asignacion.estatus === 1;
        if (esActivo) {
            Swal.fire('No permitido', `Este crédito ya está asignado a: ${creditoItem.asignacion.nombre_despacho}`, 'warning');
            return;
        }
    }
    // FIN VALIDACIÓN

    const creditoEncontrado = creditoItem.credito;

    Swal.fire({
        title: '¿Confirmar asignación?',
        text: `¿Desea asignar el crédito ${creditoEncontrado.id_credito} al despacho seleccionado?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, asignar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('/despachos/asignarCredito', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id_persona: despachoSeleccionado,
                    id_credito: creditoEncontrado.id_credito
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Éxito', 'Crédito asignado correctamente', 'success');
                    descartarCredito(idCredito);
                    cargarCreditosAsignados(despachoSeleccionado);
                } else {
                    Swal.fire('Error', data.message || 'No se pudo asignar el crédito', 'error');
                }
            })
            .catch(error => {
                console.error('Error al asignar crédito:', error);
                Swal.fire('Error', 'Error al asignar el crédito', 'error');
            });
        }
    });
}

// Función para cargar créditos asignados
function cargarCreditosAsignados(idPersona) {
    fetch(`/despachos/obtenerCreditosAsignados/${idPersona}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.creditos) {
                // Inicializar DataTable si no existe
                if (!$.fn.DataTable.isDataTable('#tabla-creditos')) {
                    configuraTabla('#tabla-creditos', {
                        registrosPorPagina: 10,
                        columns: [
                            { data: 0, title: 'ID Crédito' },
                            { data: 1, title: 'Estado' },
                            { data: 2, title: 'Fecha Asignación' },
                            { data: 3, title: 'Asignado Por' },
                            { data: 4, title: 'Acciones', orderable: false }
                        ]
                    });
                }

                // Formatear datos para DataTable (arrays)
                const datosFormateados = data.creditos.map(credito => {
                    const esActivo = credito.estado === '1' || credito.estado === 1 || credito.estado === 'Activo';
                    const estadoBadge = esActivo ? 'bg-success' : 'bg-secondary';
                    const estadoTexto = esActivo ? 'Activo' : 'Inactivo';

                    // Switch toggle visual
                    const switchEstatus = `
                        <div class="form-check form-switch d-flex justify-content-center align-items-center" style="gap:0.5rem;">
                            <input class="form-check-input switch-credito" type="checkbox"
                                   id="switch-${credito.id_credito}"
                                   data-credito="${credito.id_credito}"
                                   ${esActivo ? 'checked' : ''}
                                   style="cursor: pointer; width: 2.5rem; height: 1.25rem;">
                            <button class="btn btn-outline-primary btn-sm btn-seguimiento" title="Seguimiento" data-credito="${credito.id_credito}">
                                <i class="fa-solid fa-arrow-right"></i>
                            </button>
                        </div>`;

                    return [
                        `<strong>${credito.id_credito}</strong>`,
                        `<span class="badge ${estadoBadge}">${estadoTexto}</span>`,
                        credito.fecha_asignacion,
                        credito.asignado_por || 'Sistema',
                        switchEstatus
                    ];
                });

                // Actualizar tabla manteniendo página actual
                actualizaDatosTabla('#tabla-creditos', datosFormateados, true);

                // Agregar event listeners a los switches y botones después de actualizar la tabla
                setTimeout(() => {
                    document.querySelectorAll('.switch-credito').forEach(switchElement => {
                        switchElement.addEventListener('change', function(e) {
                            const idCredito = this.getAttribute('data-credito');
                            const nuevoEstatus = this.checked ? '1' : '0';
                            const estadoAnterior = !this.checked;

                            // Revertir el switch temporalmente
                            this.checked = estadoAnterior;

                            // Pedir confirmación
                            cambiarEstatusCredito(idCredito, nuevoEstatus, this);
                        });
                    });
                    // Botón seguimiento
                    document.querySelectorAll('.btn-seguimiento').forEach(btn => {
                        btn.addEventListener('click', function(e) {
    const idCredito = this.getAttribute('data-credito');
    abrirModalHistorial(idCredito); // ← esto
});
                    });
                }, 100);
            }
        })
        .catch(error => {
            console.error('Error al cargar créditos asignados:', error);
        });
}

// Función para cambiar estatus de crédito
function cambiarEstatusCredito(idCredito, nuevoEstatus, switchElement) {
    const accion = nuevoEstatus === '1' ? 'activar' : 'desactivar';
    const titulo = nuevoEstatus === '1' ? '¿Activar crédito?' : '¿Desactivar crédito?';
    const texto = `¿Desea ${accion} el crédito ${idCredito}?`;
    const btnTexto = nuevoEstatus === '1' ? 'Sí, activar' : 'Sí, desactivar';

    Swal.fire({
        title: titulo,
        text: texto,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: btnTexto,
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Usuario confirmó, cambiar el switch
            if (switchElement) {
                switchElement.checked = nuevoEstatus === '1';
            }

            fetch('/despachos/cambiarEstatusCredito', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id_credito: idCredito,
                    nuevo_estatus: nuevoEstatus
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Éxito', data.message, 'success');
                    cargarCreditosAsignados(despachoSeleccionado);
                } else {
                    Swal.fire('Error', data.message || 'No se pudo cambiar el estatus', 'error');
                    // Revertir el switch si hubo error
                    if (switchElement) {
                        switchElement.checked = nuevoEstatus !== '1';
                    }
                }
            })
            .catch(error => {
                console.error('Error al cambiar estatus:', error);
                Swal.fire('Error', 'Error al cambiar el estatus del crédito', 'error');
                // Revertir el switch si hubo error
                if (switchElement) {
                    switchElement.checked = nuevoEstatus !== '1';
                }
            });
        }
        // Si cancela, el switch ya está revertido, no hacer nada
    });
}

// Función para guardar comentarios
function guardarComentarios() {
    if (!despachoSeleccionado) {
        Swal.fire('Advertencia', 'Seleccione un despacho primero', 'warning');
        return;
    }

    const comentarios = document.getElementById('comentarios-despacho').value;

    fetch('/despachos/guardarComentarios', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            id_despacho: despachoSeleccionado,
            comentarios: comentarios
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Éxito', 'Comentarios guardados correctamente', 'success');
        } else {
            Swal.fire('Error', 'No se pudieron guardar los comentarios', 'error');
        }
    })
    .catch(error => {
        console.error('Error al guardar comentarios:', error);
        Swal.fire('Error', 'Error al guardar los comentarios', 'error');
    });
}

// Función para exportar a Excel
function exportarExcel() {
    if (!despachoSeleccionado) {
        Swal.fire('Advertencia', 'Seleccione un despacho primero', 'warning');
        return;
    }

    // Mostrar mensaje de carga
    Swal.fire({
        title: 'Generando reporte...',
        html: 'Por favor espere mientras se genera el archivo Excel.<br><small>Este proceso puede tardar varios minutos si hay muchos créditos asignados.</small>',
        icon: 'info',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Iniciar descarga
    window.location.href = `/despachos/exportarExcel/${despachoSeleccionado}`;

    // Cerrar mensaje después de un tiempo (el archivo se descargará en segundo plano)
    setTimeout(() => {
        Swal.close();
        Swal.fire({
            title: 'Descarga iniciada',
            text: 'El archivo debería descargarse en unos momentos',
            icon: 'success',
            timer: 3000,
            showConfirmButton: false
        });
    }, 3000);
}

// ============================================================================
// IMPORTAR EXCEL (asignación masiva)
// ============================================================================
function prepararModalImportacionExcel() {
    closeImportPlantillaPopover();
    const selSync = document.getElementById('select-despacho');
    if (selSync && selSync.value) {
        despachoSeleccionado = selSync.value;
    }
    const input = document.getElementById('input-excel-import');
    if (input) input.value = '';

    const progreso = document.getElementById('import-progreso');
    const resultado = document.getElementById('import-resultado');
    const barra = document.getElementById('import-progress-bar');
    const texto = document.getElementById('import-progress-text');
    const contenedorResultado = document.getElementById('import-result');

    if (progreso) progreso.style.display = 'none';
    if (resultado) resultado.style.display = 'none';
    if (barra) barra.style.width = '0%';
    if (barra) barra.textContent = '0%';
    if (texto) texto.textContent = '0%';
    if (contenedorResultado) contenedorResultado.innerHTML = '';
}

function descargarPlantillaExcelImportacion() {
    window.location.href = '/despachos/descargarPlantillaExcelAsignacionCreditosDespacho';
}

function escapeHtmlImportPopover(str) {
    if (str == null || str === '') return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function tablaDespachoImportPopoverHtml(cuerpoFilasHtml) {
    return `
        <div class="import-despacho-popover-scroll">
        <table class="table table-sm table-bordered table-despacho-import mb-0">
            <thead>
                <tr>
                    <th scope="col">Nombre del despacho</th>
                    <th scope="col">id_despacho</th>
                </tr>
            </thead>
            <tbody>${cuerpoFilasHtml}</tbody>
        </table>
        </div>`;
}

function closeImportPlantillaPopover() {
    const el = document.getElementById('import-despacho-import-popover');
    const wrap = document.getElementById('import-despacho-import-popover-wrap');
    if (el) {
        el.style.display = 'none';
        el.setAttribute('aria-hidden', 'true');
    }
    if (wrap) {
        wrap.classList.remove('is-popover-open');
    }
    importPlantillaPopoverVisible = false;
}

async function toggleImportPlantillaPopover(anchorEl) {
    const pop = document.getElementById('import-despacho-import-popover');
    const content = document.getElementById('import-despacho-import-popover-content');
    if (!pop || !content || !anchorEl) return;

    if (importPlantillaPopoverVisible) {
        closeImportPlantillaPopover();
        return;
    }

    const wrap = document.getElementById('import-despacho-import-popover-wrap');
    if (wrap) {
        wrap.classList.add('is-popover-open');
    }

    pop.style.display = 'block';
    pop.setAttribute('aria-hidden', 'false');
    importPlantillaPopoverVisible = true;

    if (wrap && anchorEl) {
        const wrapRect = wrap.getBoundingClientRect();
        const anchorRect = anchorEl.getBoundingClientRect();
        const anchorCenterX = anchorRect.left + anchorRect.width / 2 - wrapRect.left;
        const pw = wrap.offsetWidth || 320;
        const arrowLeft = Math.max(18, Math.min(pw - 18, anchorCenterX));
        pop.style.setProperty('--arrow-left', `${Math.round(arrowLeft)}px`);
    }

    content.innerHTML = tablaDespachoImportPopoverHtml(
        '<tr><td colspan="2" class="text-center text-muted py-2">Cargando catálogo desde la base de datos…</td></tr>'
    );

    try {
        const res = await fetch('/despachos/obtenerCatalogoDespachosImportacionExcel');
        const data = await res.json();

        if (!data.success || !Array.isArray(data.filas)) {
            content.innerHTML = tablaDespachoImportPopoverHtml(
                `<tr><td colspan="2" class="text-danger small">${escapeHtmlImportPopover(data.message || 'No fue posible obtener el catálogo.')}</td></tr>`
            );
            return;
        }

        if (data.filas.length === 0) {
            content.innerHTML = tablaDespachoImportPopoverHtml(
                '<tr><td colspan="2" class="text-muted small">No hay registros de gestor/supervisor de despacho.</td></tr>'
            );
            return;
        }

        const rowsHtml = data.filas.map((f) => {
            const nom = f.nombre_completo || '—';
            const idDespHtml = f.id_despacho != null && f.id_despacho !== ''
                ? escapeHtmlImportPopover(String(f.id_despacho))
                : '<span class="text-warning">—</span>';
            return `<tr><td>${escapeHtmlImportPopover(nom)}</td><td>${idDespHtml}</td></tr>`;
        }).join('');

        content.innerHTML = tablaDespachoImportPopoverHtml(rowsHtml);
    } catch (err) {
        console.error(err);
        content.innerHTML = tablaDespachoImportPopoverHtml(
            '<tr><td colspan="2" class="text-danger small">Error de red al consultar la base de datos.</td></tr>'
        );
    }
}

function setImportProgress(percent, text) {
    const progreso = document.getElementById('import-progreso');
    const resultado = document.getElementById('import-resultado');
    const barra = document.getElementById('import-progress-bar');
    const texto = document.getElementById('import-progress-text');
    if (progreso) progreso.style.display = 'block';
    if (resultado) resultado.style.display = 'block';

    const p = Math.max(0, Math.min(100, Number(percent) || 0));
    if (barra) {
        barra.style.width = `${p}%`;
        barra.textContent = `${Math.round(p)}%`;
    }
    if (texto) texto.textContent = text || `${Math.round(p)}%`;
}

async function iniciarImportacionExcel() {
    if (!despachoSeleccionado) {
        Swal.fire('Advertencia', 'Seleccione un despacho primero', 'warning');
        return;
    }

    const input = document.getElementById('input-excel-import');
    const files = input ? Array.from(input.files || []) : [];
    if (files.length === 0) {
        Swal.fire('Advertencia', 'Adjunta al menos un archivo Excel', 'warning');
        return;
    }

    const btn = document.getElementById('btn-subir-excel-importar');
    if (btn) btn.disabled = true;

    const contenedorResultado = document.getElementById('import-result');
    const resultadoWrap = document.getElementById('import-resultado');
    if (contenedorResultado) contenedorResultado.innerHTML = '';
    if (resultadoWrap) resultadoWrap.style.display = 'block';

    const totalFiles = files.length;
    let resumen = {
        totalArchivos: totalFiles,
        totalCreditosLeidos: 0,
        totalInsertados: 0,
        totalDuplicados: 0,
        totalErrores: 0,
        duplicadosEjemplo: [],
        duplicadosDetalleEjemplos: [],
        errores: []
    };

    try {
        for (let i = 0; i < totalFiles; i++) {
            const file = files[i];
            const fileIndex = i + 1;

            // Subir y esperar respuesta del servidor
            const data = await new Promise((resolve, reject) => {
                const xhr = new XMLHttpRequest();
                const formData = new FormData();
                formData.append('id_persona', despachoSeleccionado);
                formData.append('excel', file, file.name);

                xhr.open('POST', '/despachos/importarExcelAsignacionCreditosDespacho', true);
                xhr.responseType = 'json';

                xhr.upload.onprogress = (e) => {
                    if (!e.lengthComputable) return;
                    const fractionLoaded = e.total > 0 ? e.loaded / e.total : 0;
                    const overall = ((i + fractionLoaded) / totalFiles) * 100;
                    setImportProgress(overall, `Subiendo... Archivo ${fileIndex} de ${totalFiles} (${Math.round(overall)}%)`);
                };

                xhr.onload = () => {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        resolve(xhr.response);
                    } else {
                        reject(new Error(`HTTP ${xhr.status}`));
                    }
                };

                xhr.onerror = () => reject(new Error('Error de red al subir el archivo'));

                xhr.send(formData);
            });

            if (!data) {
                resumen.totalErrores += 1;
                resumen.errores.push({ archivo: file.name, razon: 'Respuesta vacía del servidor' });
                continue;
            }

            if (data.success) {
                resumen.totalCreditosLeidos += data.total_creditos_validos || 0;
                resumen.totalInsertados += data.insertados || 0;
                resumen.totalDuplicados += data.duplicados || 0;

                if (Array.isArray(data.duplicados_creditos)) {
                    for (const id of data.duplicados_creditos) {
                        if (!resumen.duplicadosEjemplo.includes(id)) {
                            resumen.duplicadosEjemplo.push(id);
                        }
                    }
                }

                if (Array.isArray(data.duplicados_detalle)) {
                    for (const d of data.duplicados_detalle) {
                        if (resumen.duplicadosDetalleEjemplos.length >= 36) break;
                        resumen.duplicadosDetalleEjemplos.push({
                            archivo: file.name,
                            id_despacho: d.id_despacho,
                            id_credito: d.id_credito
                        });
                    }
                }

                if (Array.isArray(data.errores) && data.errores.length > 0) {
                    resumen.totalErrores += data.errores.length;
                    for (const er of data.errores) {
                        resumen.errores.push({ archivo: file.name, ...er });
                    }
                }
            } else {
                resumen.totalErrores += 1;
                resumen.errores.push({ archivo: file.name, razon: data.message || 'Error al importar el archivo' });
            }

            // Marcar el archivo como completado
            const completedOverall = ((i + 1) / totalFiles) * 100;
            setImportProgress(completedOverall, `Procesado archivo ${fileIndex} de ${totalFiles}`);
        }

        // Final
        setImportProgress(100, 'Finalizado');

        if (contenedorResultado) {
            const hayErrores = resumen.totalErrores > 0;
            const hayDuplicados = resumen.totalDuplicados > 0;

            let html = '';
            html += `<div class="mb-2"><strong>Archivos:</strong> ${resumen.totalArchivos}</div>`;
            html += `<div class="mb-2"><strong>Créditos válidos leídos:</strong> ${resumen.totalCreditosLeidos}</div>`;
            html += `<div class="mb-2"><strong>Insertados:</strong> ${resumen.totalInsertados}</div>`;
            html += `<div class="mb-2"><strong>Duplicados:</strong> ${resumen.totalDuplicados}</div>`;
            html += `<div class="mb-3"><strong>Errores:</strong> ${resumen.totalErrores}</div>`;

            if (!hayErrores && !hayDuplicados) {
                html += `<div class="alert alert-success mb-0">Se subieron todos los créditos correctamente.</div>`;
            } else {
                html += `<div class="alert ${hayErrores ? 'alert-danger' : 'alert-warning'} mb-0">`;
                html += hayErrores ? 'Se subieron algunos créditos, pero hubo errores.' : 'Se subieron algunos créditos, pero hubo duplicados.';
                html += `</div>`;
            }

            if (hayDuplicados && resumen.duplicadosDetalleEjemplos.length > 0) {
                html += `<div class="mt-3"><div class="fw-semibold mb-1">Ejemplos de duplicados:</div>`;
                html += `<div class="text-muted small">` + resumen.duplicadosDetalleEjemplos.slice(0, 12).map(d =>
                    `Archivo <strong>${d.archivo}</strong>: despacho <strong>${d.id_despacho}</strong> · crédito <strong>${d.id_credito}</strong> ya estaba asignado`
                ).join('<br>') + `</div></div>`;
            } else if (hayDuplicados && resumen.duplicadosEjemplo.length > 0) {
                html += `<div class="mt-3"><div class="fw-semibold mb-1">Ejemplos de duplicados:</div>`;
                html += `<div class="text-muted small">` + resumen.duplicadosEjemplo.slice(0, 12).map(id => `crédito <strong>${id}</strong> ya existe en la base`).join('<br>') + `</div></div>`;
            }

            if (hayErrores && resumen.errores.length > 0) {
                html += `<div class="mt-3"><div class="fw-semibold mb-1">Detalles de errores (parcial):</div>`;
                const preview = resumen.errores.slice(0, 20);
                html += `<div class="text-muted small">`;
                preview.forEach((e) => {
                    if (e.razon) {
                        html += `Archivo: <strong>${e.archivo}</strong> - ${e.razon}<br>`;
                    } else if (e.fila && e.reason) {
                        html += `Archivo: <strong>${e.archivo}</strong> - fila <strong>${e.fila}</strong>: ${e.reason}<br>`;
                    } else {
                        html += `Archivo: <strong>${e.archivo}</strong> - Error: ${JSON.stringify(e)}<br>`;
                    }
                });
                html += `</div></div>`;
            }

            if (contenedorResultado) contenedorResultado.innerHTML = html;
        }

        if (btn) btn.disabled = false;
        if (input) input.value = '';
    } catch (err) {
        console.error(err);
        if (btn) btn.disabled = false;
        setImportProgress(0, 'Error');
        Swal.fire('Error', 'No se pudo completar la importación', 'error');
    }
}

// Función para refrescar la tabla de créditos asignados
function refreshTablaCreditos() {
    if (!despachoSeleccionado) {
        Swal.fire('Advertencia', 'Seleccione un despacho primero', 'warning');
        return;
    }

    const btn = document.getElementById('btn-refresh-tabla');
    const icon = btn.querySelector('i');
    icon.classList.add('fa-spin');
    btn.disabled = true;

    cargarCreditosAsignados(despachoSeleccionado);

    setTimeout(() => {
        icon.classList.remove('fa-spin');
        btn.disabled = false;
    }, 800);
}

// Función auxiliar para formatear moneda
function formatearMoneda(valor) {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(valor);
}

// ============================================================================
// GESTIÓN DE DOCUMENTOS
// ============================================================================

let catalogoDocumentos = [];
let documentosDespacho = [];

// Cargar catálogo de documentos al iniciar
async function cargarCatalogoDocumentos() {
    try {
        const response = await fetch('/despachos/obtenerCatalogoDocumentos');
        const data = await response.json();

        if (data.success) {
            catalogoDocumentos = data.catalogo;
        } else {
            console.error('Error al cargar catálogo:', data.message);
        }
    } catch (error) {
        console.error('Error al cargar catálogo de documentos:', error);
    }
}

// Cargar documentos del despacho seleccionado
async function cargarDocumentosDespacho(idPersona) {
    try {
        const response = await fetch('/despachos/obtenerDocumentosDespacho', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id_persona: idPersona })
        });

        const data = await response.json();

        if (data.success) {
            documentosDespacho = data.documentos;
            renderizarAcordeonDocumentos();
        } else {
            console.error('Error al cargar documentos:', data.message);
            documentosDespacho = [];
            renderizarAcordeonDocumentos();
        }
    } catch (error) {
        console.error('Error al cargar documentos del despacho:', error);
        documentosDespacho = [];
        renderizarAcordeonDocumentos();
    }
}

// Renderizar acordeón de documentos
function renderizarAcordeonDocumentos() {
    const accordion = document.getElementById('accordionDocumentos');

    if (!despachoSeleccionado) {
        accordion.innerHTML = '<p class="text-muted text-center">Seleccione un despacho para ver sus documentos</p>';
        return;
    }

    if (catalogoDocumentos.length === 0) {
        accordion.innerHTML = '<p class="text-muted text-center">No hay documentos en el catálogo</p>';
        return;
    }

    let html = '';

    catalogoDocumentos.forEach((doc, index) => {
        // Buscar si existe el documento subido
        const documentoSubido = documentosDespacho.find(d =>
            String(d.id_catalogo_documento) === String(doc.id)
        );

        const tieneDocumento = !!documentoSubido;
        const estatus = documentoSubido?.estatus || null;

        // Verificar si es PDF
        const esPDF = tieneDocumento && documentoSubido.nombre_archivo.toLowerCase().endsWith('.pdf');

        // Clase de badge según el estatus
        let badgeClass = 'bg-secondary';
        let badgeText = 'Sin subir';

        if (tieneDocumento) {
            switch (estatus) {
                case 'Vigente':
                    badgeClass = 'bg-success';
                    badgeText = '✓ Vigente';
                    break;
                case 'Vencido':
                    badgeClass = 'bg-warning';
                    badgeText = '⚠ Vencido';
                    break;
                case 'Rechazado':
                    badgeClass = 'bg-danger';
                    badgeText = '✗ Rechazado';
                    break;
            }
        }

        html += `
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading${index}">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapse${index}" aria-expanded="false" aria-controls="collapse${index}">
                        <span style="flex: 1; display: flex; align-items: center;">
                            <i class="fa-solid fa-file-pdf me-2"></i>
                            ${doc.nombre_documento}
                        </span>
                        <span class="badge ${badgeClass}">${badgeText}</span>
                    </button>
                </h2>
                <div id="collapse${index}" class="accordion-collapse collapse"
                     aria-labelledby="heading${index}" data-bs-parent="#accordionDocumentos">
                    <div class="accordion-body">
                        ${doc.descripcion ? `<p class="text-muted small mb-3">${doc.descripcion}</p>` : ''}

                        ${tieneDocumento ? `
                            <!-- Documento ya subido -->
                            <div class="alert alert-info mb-3">
                                <i class="fa-solid fa-info-circle me-1"></i>
                                <strong>Documento cargado</strong><br>
                                <small>
                                    Fecha: ${documentoSubido.fecha_carga}<br>
                                    Por: ${documentoSubido.cargado_por}<br>
                                    Archivo: ${documentoSubido.nombre_archivo}
                                </small>
                            </div>

                            <div class="d-flex gap-2 justify-content-center">
                                ${esPDF ? `
                                    <button class="btn btn-sm btn-success"
                                            onclick="visualizarDocumento(${documentoSubido.id}, '${documentoSubido.nombre_archivo}')"
                                            title="Ver documento"
                                            style="width: 40px; height: 40px;">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                ` : ''}
                                <a href="/despachos/descargarDocumento/${documentoSubido.id}"
                                   class="btn btn-sm btn-primary" download
                                   title="Descargar documento"
                                   style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fa-solid fa-download"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-secondary"
                                        onclick="reemplazarDocumento(${doc.id})"
                                        title="Reemplazar documento"
                                        style="width: 40px; height: 40px;">
                                    <i class="fa-solid fa-sync"></i>
                                </button>
                            </div>
                        ` : `
                            <!-- Formulario de carga -->
                            <form onsubmit="subirDocumento(event, ${doc.id})" id="form-doc-${doc.id}">
                                <div class="mb-3">
                                    <label class="form-label">Seleccionar archivo</label>
                                    <input type="file" class="form-control form-control-sm"
                                           name="archivo" required
                                           accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                    <small class="form-text text-muted">
                                        PDF, JPG, PNG, DOC, DOCX (máx. 5MB)
                                    </small>
                                </div>
                                <button type="submit" class="btn btn-success btn-sm w-100">
                                    <i class="fa-solid fa-upload me-1"></i>Subir Documento
                                </button>
                            </form>
                        `}
                    </div>
                </div>
            </div>
        `;
    });

    accordion.innerHTML = html;
}

// Subir documento
async function subirDocumento(event, idCatalogoDocumento) {
    event.preventDefault();

    if (!despachoSeleccionado) {
        Swal.fire('Error', 'No hay despacho seleccionado', 'error');
        return;
    }

    const form = event.target;
    const formData = new FormData(form);
    formData.append('id_persona', despachoSeleccionado);
    formData.append('id_catalogo_documento', idCatalogoDocumento);

    try {
        // Mostrar loading
        Swal.fire({
            title: 'Subiendo documento...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const response = await fetch('/despachos/subirDocumento', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            Swal.fire('Éxito', 'Documento subido correctamente', 'success');
            // Recargar documentos
            await cargarDocumentosDespacho(despachoSeleccionado);
        } else {
            Swal.fire('Error', data.message || 'No se pudo subir el documento', 'error');
        }
    } catch (error) {
        console.error('Error al subir documento:', error);
        Swal.fire('Error', 'Error al subir el documento', 'error');
    }
}

// Reemplazar documento existente
function reemplazarDocumento(idCatalogoDocumento) {
    Swal.fire({
        title: '¿Reemplazar documento?',
        text: 'El documento actual será reemplazado por el nuevo',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, reemplazar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Encontrar el documento en el array
            const index = catalogoDocumentos.findIndex(d => d.id === idCatalogoDocumento);
            if (index !== -1) {
                // Remover el documento actual de la lista
                const docIndex = documentosDespacho.findIndex(d =>
                    String(d.id_catalogo_documento) === String(idCatalogoDocumento)
                );
                if (docIndex !== -1) {
                    documentosDespacho.splice(docIndex, 1);
                }
                // Re-renderizar para mostrar el formulario de carga
                renderizarAcordeonDocumentos();
            }
        }
    });
}

// Inicializar catálogo al cargar la página
cargarCatalogoDocumentos();

// ============================================================================
// VISUALIZADOR DE PDF
// ============================================================================

function visualizarDocumento(idDocumento, nombreArchivo) {
    const iframe = document.getElementById('iframeVisualizadorPDF');
    const btnDescargar = document.getElementById('btnDescargarPDFModal');
    const modalLabel = document.getElementById('modalVisualizadorPDFLabel');

    // Construir URL para visualizar el documento
    const urlDocumento = `/despachos/descargarDocumento/${idDocumento}`;

    // Configurar iframe
    iframe.src = urlDocumento;

    // Configurar botón de descarga
    btnDescargar.href = urlDocumento;
    btnDescargar.download = nombreArchivo;

    // Actualizar título del modal
    modalLabel.innerHTML = `<i class="fa-solid fa-file-pdf me-2"></i>${nombreArchivo}`;

    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('modalVisualizadorPDF'));
    modal.show();
}

// ============================================================================
// EDICIÓN DE TIPO PERSONA
// ============================================================================

function toggleEditTipo() {
    if (!despachoSeleccionado) {
        Swal.fire('Advertencia', 'Seleccione un despacho primero', 'warning');
        return;
    }

    const spanTipo = document.getElementById('info-tipo');
    const selectTipo = document.getElementById('select-tipo-persona');
    const iconEdit = document.querySelector('.edit-tipo-icon');

    if (selectTipo.style.display === 'none') {
        // Mostrar select, ocultar span e icono
        const valorActual = spanTipo.textContent.trim();
        selectTipo.value = valorActual !== '-' ? valorActual : '';
        spanTipo.style.display = 'none';
        iconEdit.style.display = 'none';
        selectTipo.style.display = 'block';
        selectTipo.focus();
    }
}

function cancelarEditTipo() {
    const spanTipo = document.getElementById('info-tipo');
    const selectTipo = document.getElementById('select-tipo-persona');
    const iconEdit = document.querySelector('.edit-tipo-icon');

    if (selectTipo.style.display !== 'none') {
        selectTipo.style.display = 'none';
        spanTipo.style.display = 'inline';
        iconEdit.style.display = 'inline';
    }
}

async function actualizarTipoPersona() {
    const selectTipo = document.getElementById('select-tipo-persona');
    const nuevoTipo = selectTipo.value;

    if (!nuevoTipo) {
        // Si no seleccionó nada, revertir a modo vista
        const spanTipo = document.getElementById('info-tipo');
        const iconEdit = document.querySelector('.edit-tipo-icon');
        selectTipo.style.display = 'none';
        spanTipo.style.display = 'inline';
        iconEdit.style.display = 'inline';
        return;
    }

    try {
        const response = await fetch('/despachos/actualizarTipoPersona', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id_persona: despachoSeleccionado,
                tipo_persona: nuevoTipo
            })
        });

        const data = await response.json();

        if (data.success) {
            // Actualizar interfaz
            const spanTipo = document.getElementById('info-tipo');
            const iconEdit = document.querySelector('.edit-tipo-icon');

            spanTipo.textContent = nuevoTipo;
            selectTipo.style.display = 'none';
            spanTipo.style.display = 'inline';
            iconEdit.style.display = 'inline';
        } else {
            Swal.fire('Error', data.message || 'No se pudo actualizar el tipo de persona', 'error');
            // Revertir a modo vista sin cambios
            const spanTipo = document.getElementById('info-tipo');
            const iconEdit = document.querySelector('.edit-tipo-icon');
            selectTipo.style.display = 'none';
            spanTipo.style.display = 'inline';
            iconEdit.style.display = 'inline';
        }
    } catch (error) {
        console.error('Error al actualizar tipo persona:', error);
        Swal.fire('Error', 'Error al actualizar el tipo de persona', 'error');
        // Revertir a modo vista
        const spanTipo = document.getElementById('info-tipo');
        const iconEdit = document.querySelector('.edit-tipo-icon');
        selectTipo.style.display = 'none';
        spanTipo.style.display = 'inline';
        iconEdit.style.display = 'inline';
    }
}

// ============================================================================
// MODAL HISTORIAL DE GESTORES
// ============================================================================


function hgcSwitchTab(panelId) {
    const panels  = ['hgc-panel-datos', 'hgc-panel-historial', 'hgc-panel-convenios'];
    const tabBtns = document.querySelectorAll('.hgc-tab-btn');

    // Ocultar todos los paneles
    panels.forEach(p => {
        const el = document.getElementById(p);
        if (el) el.style.display = 'none';
    });

    // Mostrar el panel solicitado
    const target = document.getElementById(panelId);
    if (target) target.style.display = 'block';

    // Actualizar estilos de tabs
    tabBtns.forEach(btn => {
        const isActive = btn.dataset.panel === panelId;
        btn.style.color        = isActive ? '#696cff' : '#697a8d';
        btn.style.borderBottom = isActive ? '2px solid #696cff' : '2px solid transparent';
        btn.classList.toggle('active', isActive);
    });
}

/**
 * Genera las iniciales de un nombre completo
 */
function hgcInitiales(nombre) {
    if (!nombre || nombre === '—') return '?';
    const partes = nombre.trim().split(' ').filter(Boolean);
    if (partes.length === 1) return partes[0][0].toUpperCase();
    return (partes[0][0] + partes[partes.length - 1][0]).toUpperCase();
}

/**
 * Muestra / oculta el spinner global del modal
 */
function hgcSetLoading(visible) {
    const spinner  = document.getElementById('hgc-spinner');
    const paneDatos = document.getElementById('hgc-panel-datos');
    if (spinner)   spinner.style.display   = visible ? 'block' : 'none';
    if (paneDatos) paneDatos.style.display = visible ? 'none'  : 'block';
}

// ── Apertura del modal ───────────────────────────────────────

/**
 * Abre el modal y carga toda la información del crédito
 * @param {string|number} idCredito
 */
async function abrirModalHistorial(idCredito) {
    // Resetear tabs al primero
    hgcSwitchTab('hgc-panel-datos');

    // Limpiar campos antes de cargar
    hgcLimpiarModal();

    // Mostrar spinner
    hgcSetLoading(true);

    // Abrir modal Bootstrap
    const modalEl = document.getElementById('modalHistorialGestores');
    let modal = bootstrap.Modal.getInstance(modalEl);
    if (!modal) modal = new bootstrap.Modal(modalEl);
    modal.show();

    // Actualizar label del header
    document.getElementById('hgc-credito-label').textContent = `Crédito #${idCredito}`;

    try {
        // Llamadas paralelas: datos del crédito + historial completo de asignaciones
        const [resCreditoProm, resHistorialProm] = await Promise.allSettled([
            fetch('/despachos/buscarCredito', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ tipo: 'id_credito', valor: idCredito })
            }).then(r => r.json()),

            fetch('/despachos/obtenerHistorialGestores', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_credito: idCredito })
            }).then(r => r.json())
        ]);

        const dataCredito   = resCreditoProm.status   === 'fulfilled' ? resCreditoProm.value   : null;
        const dataHistorial = resHistorialProm.status === 'fulfilled' ? resHistorialProm.value : null;

        // Poblar datos generales
        if (dataCredito && dataCredito.success) {
            hgcPoblarDatosGenerales(dataCredito.credito, dataCredito.asignacion);
        }

        // Poblar historial de gestores
        if (dataHistorial && dataHistorial.success) {
            hgcPoblarHistorial(dataHistorial.historial);
        } else {
            hgcPoblarHistorial([]);
        }

        // Convenios — cuando tengas el endpoint listo, descomentar:
        // const resConvenios = await fetch('/despachos/obtenerConveniosCredito', { ... });
        // hgcPoblarConvenios(resConvenios.convenios);

        // Por ahora convenios vacíos
        hgcPoblarConvenios([]);

    } catch (error) {
        console.error('Error al cargar datos del modal:', error);
        Swal.fire('Error', 'No se pudo cargar la información del crédito', 'error');
    } finally {
        hgcSetLoading(false);
    }
}

// ── Población de paneles ─────────────────────────────────────

/**
 * Poblar Panel 1: Datos Generales
 */
function hgcPoblarDatosGenerales(credito, asignacion) {
    if (!credito) return;

    // Métricas
    document.getElementById('hgc-saldo').textContent =
        new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' })
            .format(credito.saldo_actual || 0);

    document.getElementById('hgc-mora').textContent  = credito.dias_mora || 0;
    document.getElementById('hgc-id-credito-detalle').textContent = `#${credito.id_credito}`;

    // Iniciales del cliente
    const iniciales = hgcInitiales(credito.nombre_cliente);
    document.getElementById('hgc-avatar').textContent          = iniciales;
    document.getElementById('hgc-nombre-cliente').textContent  = credito.nombre_cliente  || '—';
    document.getElementById('hgc-curp').textContent            = credito.curp            || '—';
    document.getElementById('hgc-telefono').textContent        = credito.telefono        || '—';
    document.getElementById('hgc-sucursal').textContent        = credito.sucursal        || '—';
    document.getElementById('hgc-fecha-desembolso').textContent= credito.fecha_desembolso|| '—';
    document.getElementById('hgc-direccion').textContent       = credito.direccion       || '—';

    // Gestor actual
    const cardGestor  = document.getElementById('hgc-gestor-actual-card');
    const sinGestor   = document.getElementById('hgc-sin-gestor');
    const esActivo    = asignacion && (asignacion.estatus === '1' || asignacion.estatus === 1);

    if (asignacion && esActivo) {
        cardGestor.style.display = 'block';
        sinGestor.style.display  = 'none';

        const inicGestor = hgcInitiales(asignacion.nombre_despacho);
        document.getElementById('hgc-gestor-avatar').textContent = inicGestor;
        document.getElementById('hgc-gestor-nombre').textContent = asignacion.nombre_despacho || '—';
        document.getElementById('hgc-gestor-info').textContent   =
            `${asignacion.puesto_despacho || 'Gestor'} · Desde ${asignacion.fecha_asignacion || '—'}`;
    } else {
        cardGestor.style.display = 'none';
        sinGestor.style.display  = 'block';
    }
}

/**
 * Poblar Panel 2: Historial de Gestores (timeline)
 */
function hgcPoblarHistorial(historial) {
    const timeline = document.getElementById('hgc-timeline');
    const vacio    = document.getElementById('hgc-historial-vacio');
    const badge    = document.getElementById('hgc-badge-historial');

    badge.textContent = historial.length;

    // Actualizar métrica de gestores en panel 1
    document.getElementById('hgc-total-gestores').textContent = historial.length;

    if (!historial.length) {
        timeline.innerHTML    = '';
        vacio.style.display   = 'block';
        return;
    }

    vacio.style.display = 'none';

    timeline.innerHTML = historial.map((item, idx) => {
        const esActual   = item.estatus === '1' || item.estatus === 1;
        const iniciales  = hgcInitiales(item.nombre_despacho);
        const badgeHtml  = esActual
            ? `<span style="background:#696cff; color:white; font-size:10px; padding:2px 8px; border-radius:10px; white-space:nowrap;">Actual</span>`
            : `<span style="background:#f0f0f0; color:#697a8d; font-size:10px; padding:2px 8px; border-radius:10px; white-space:nowrap; border:0.5px solid #e0e0e0;">Inactivo</span>`;

        const avatarStyle = esActual
            ? 'background:#696cff; color:white;'
            : 'background:#f0f0f0; border:0.5px solid #e0e0e0; color:#697a8d;';

        const cardBorder = esActual ? 'border:0.5px solid #c5c6ff;' : 'border:0.5px solid #e0e0e0;';

        const fechaFin = item.fecha_baja
            ? `<span>Hasta: <strong style="color:#566a7f;">${item.fecha_baja}</strong></span>`
            : '';

        return `
        <div style="display:flex; gap:14px; margin-bottom:1.25rem; position:relative; z-index:1;">
            <div style="width:34px; height:34px; border-radius:50%; ${avatarStyle}
                        display:flex; align-items:center; justify-content:center;
                        font-size:11px; font-weight:500; flex-shrink:0;">
                ${iniciales}
            </div>
            <div style="background:#fff; ${cardBorder} border-radius:0.375rem;
                        padding:0.875rem 1rem; flex:1;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                    <div>
                        <div style="font-weight:500; font-size:14px;">${item.nombre_despacho || '—'}</div>
                        <div style="font-size:12px; color:#697a8d;">${item.puesto_despacho || '—'}</div>
                    </div>
                    ${badgeHtml}
                </div>
                <div style="margin-top:8px; display:flex; flex-wrap:wrap; gap:12px; font-size:12px; color:#697a8d;">
                    <span>Desde: <strong style="color:#566a7f;">${item.fecha_asignacion || '—'}</strong></span>
                    ${fechaFin}
                    <span>Por: <strong style="color:#566a7f;">${item.asignado_por || 'Sistema'}</strong></span>
                </div>
            </div>
        </div>`;
    }).join('');
}

/**
 * Poblar Panel 3: Convenios
 * (placeholder hasta que lleguen las tablas de convenios)
 */
function hgcPoblarConvenios(convenios) {
    const lista = document.getElementById('hgc-convenios-lista');
    const vacio = document.getElementById('hgc-convenios-vacio');
    const badge = document.getElementById('hgc-badge-convenios');

    badge.textContent = convenios.length;

    if (!convenios.length) {
        lista.innerHTML       = '';
        vacio.style.display   = 'block';
        return;
    }

    vacio.style.display = 'none';

    lista.innerHTML = convenios.map((conv, idx) => {
        const pagados    = conv.pagos_realizados  || 0;
        const total      = conv.total_parcialidades || 1;
        const porcentaje = Math.round((pagados / total) * 100);

        const badgeEstatus = conv.estatus === 'Vigente'
            ? `<span style="background:#e8f5e9; color:#2e7d32; font-size:11px; padding:3px 10px; border-radius:20px; font-weight:500;">Vigente</span>`
            : `<span style="background:#fff3e0; color:#e65100; font-size:11px; padding:3px 10px; border-radius:20px; font-weight:500;">${conv.estatus}</span>`;

        return `
        <div class="card mb-3" style="border:0.5px solid #e0e0e0;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div style="font-weight:500; font-size:14px;">Convenio #${String(idx+1).padStart(3,'0')}</div>
                        <div style="font-size:12px; color:#697a8d;">
                            Registrado el ${conv.fecha_registro || '—'} por ${conv.registrado_por || 'Sistema'}
                        </div>
                    </div>
                    ${badgeEstatus}
                </div>

                <div class="row g-2 mb-3 p-2 rounded-2" style="background:#f8f9fa; font-size:12px;">
                    <div class="col-4">
                        <div style="color:#697a8d;">Monto total</div>
                        <div style="font-weight:500; margin-top:2px;">
                            ${new Intl.NumberFormat('es-MX',{style:'currency',currency:'MXN'}).format(conv.monto_total||0)}
                        </div>
                    </div>
                    <div class="col-4">
                        <div style="color:#697a8d;">Parcialidades</div>
                        <div style="font-weight:500; margin-top:2px;">${conv.total_parcialidades || 0} pagos</div>
                    </div>
                    <div class="col-4">
                        <div style="color:#697a8d;">Pago mensual</div>
                        <div style="font-weight:500; margin-top:2px;">
                            ${new Intl.NumberFormat('es-MX',{style:'currency',currency:'MXN'}).format(conv.monto_parcialidad||0)}
                        </div>
                    </div>
                </div>

                <div>
                    <div class="d-flex justify-content-between mb-1" style="font-size:12px;">
                        <span style="color:#697a8d;">Avance de pagos</span>
                        <span style="font-weight:500;">${pagados} de ${total} pagados</span>
                    </div>
                    <div style="height:6px; background:#f0f0f0; border-radius:3px; overflow:hidden;">
                        <div style="height:100%; width:${porcentaje}%; background:#696cff; border-radius:3px;
                                    transition:width .4s ease;"></div>
                    </div>
                </div>
            </div>
        </div>`;
    }).join('');
}

// ── Limpiar modal ────────────────────────────────────────────

function hgcLimpiarModal() {
    const campos = [
        'hgc-saldo','hgc-mora','hgc-total-gestores',
        'hgc-avatar','hgc-nombre-cliente','hgc-curp',
        'hgc-telefono','hgc-sucursal','hgc-fecha-desembolso',
        'hgc-id-credito-detalle','hgc-direccion',
        'hgc-gestor-avatar','hgc-gestor-nombre','hgc-gestor-info'
    ];
    campos.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.textContent = '—';
    });
    document.getElementById('hgc-badge-historial').textContent = '0';
    document.getElementById('hgc-badge-convenios').textContent = '0';
    document.getElementById('hgc-timeline').innerHTML          = '';
    document.getElementById('hgc-convenios-lista').innerHTML   = '';
    document.getElementById('hgc-gestor-actual-card').style.display = 'none';
    document.getElementById('hgc-sin-gestor').style.display         = 'none';
    document.getElementById('hgc-historial-vacio').style.display    = 'none';
    document.getElementById('hgc-convenios-vacio').style.display    = 'none';
}

// ── Inicializar event listeners de tabs ──────────────────────

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.hgc-tab-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            hgcSwitchTab(this.dataset.panel);
        });
    });
});

// ===========================================
// AUTO-COLAPSAR SUB-ACORDEONES
// ===========================================

// Cuando el acordeón padre se cierra, cerrar todos los sub-acordeones
const collapseDocumentos = document.getElementById('collapseDocumentos');
if (collapseDocumentos) {
    collapseDocumentos.addEventListener('hidden.bs.collapse', function () {
        // Encontrar todos los sub-acordeones abiertos y cerrarlos
        const accordionDocumentos = document.getElementById('accordionDocumentos');
        if (accordionDocumentos) {
            const subAccordions = accordionDocumentos.querySelectorAll('.accordion-collapse.show');
            subAccordions.forEach(element => {
                const bsCollapse = bootstrap.Collapse.getInstance(element);
                if (bsCollapse) {
                    bsCollapse.hide();
                } else {
                    // Si no existe instancia, crear una y cerrarla
                    new bootstrap.Collapse(element, {toggle: false}).hide();
                }
            });
        }
    });
}



</script>

<?= $script ?? '' ?>
