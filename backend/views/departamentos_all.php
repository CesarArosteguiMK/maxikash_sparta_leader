<style>
    /* ===== INLINE EDIT LIMPIO ===== */
    [contenteditable="true"] {
        outline: none;
        border-radius: 6px;
        padding: 2px 6px;
        background-color: #f4f5ff;
        box-shadow: inset 0 0 0 1px #696cff40;
        transition: all 0.15s ease;
    }
    [contenteditable="true"]:hover {
        background-color: #eef0ff;
    }
    [contenteditable="true"]:focus {
        background-color: #ffffff;
        box-shadow: 0 0 0 2px #696cff50;
    }
    .puesto-nombre {
        min-height: 1.5rem;
    }

    /* ===== PUESTOS ===== */
    .puesto-item .editar-puesto {
        opacity: 0;
        cursor: pointer;
        color: #6c757d;
        font-size: 0.85rem;
        transition: all 0.2s ease;
    }
    .puesto-item:hover .editar-puesto {
        opacity: 1;
    }
    .puesto-item .editar-puesto:hover {
        color: #696cff;
        transform: scale(1.2);
    }

    /* ===== TÍTULO DEPARTAMENTO ===== */
    .titulo-departamento-container {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    .titulo-departamento-container .editar-titulo {
        opacity: 0;
        cursor: pointer;
        color: #6c757d;
        font-size: 0.85rem;
        transition: all 0.2s ease;
    }
    .titulo-departamento-container:hover .editar-titulo {
        opacity: 1;
    }
    .titulo-departamento-container .editar-titulo:hover {
        color: #696cff;
        transform: scale(1.2);
    }

    /* ===== ORDEN DRAG AND DROP ===== */
    #listaPuestos.drag-list .drag-item {
        cursor: grab;
        transition: box-shadow 0.25s ease, opacity 0.25s ease, transform 0.25s ease, background-color 0.2s ease;
    }
    #listaPuestos.drag-list .drag-item:active {
        cursor: grabbing;
    }
    #listaPuestos.drag-list .drag-item:hover {
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
        background-color:rgb(181, 181, 182);
    }
    #listaPuestos.drag-list .drag-item.dragging {
        opacity: 1;
        transform: scale(1.02) translateY(-1px);
        box-shadow: 0 10px 28px #e2e6ea;
        background-color: #fff;
        z-index: 10;
        cursor: grabbing;
    }
    #listaPuestos.drag-list .drag-item.drag-over {
        background-color: rgba(105, 108, 255, 0.08);
        box-shadow: inset 0 2px 0 0 #696cff;
        transition: background-color 0.15s ease, box-shadow 0.15s ease;
    }
    #listaPuestos .puesto-numero,
    .puesto-numero {
        width: 34px;
        height: 34px;
        min-width: 34px;
        max-width: 34px;
        border-radius: 50%;
        background-color: #e2e6ea !important;
        color: #495057 !important;
        border: 1px solid #ced4da !important;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.9rem;
        margin-right: 1rem;
        flex-shrink: 0;
        box-sizing: border-box;
    }

    /* ===== MODAL DESLIZANTE ESTILO SNEAT ===== */
    .modal-add-new-cc {
        max-width: 500px;
    }
    .modal-simple .modal-content {
        border: none;
        box-shadow: 0 2px 20px 0 rgba(76, 78, 100, 0.08);
        border-radius: 0.375rem;
    }
    .modal-simple .modal-body {
        padding: 2rem;
    }
    .modal-simple .btn-close {
        position: absolute;
        top: 1rem;
        right: 1rem;
        z-index: 10;
        padding: 0.5rem;
        background-color: rgba(76, 78, 100, 0.08);
        border-radius: 0.25rem;
        opacity: 1;
    }
    .modal-simple .btn-close:hover {
        background-color: rgba(76, 78, 100, 0.16);
    }

    /* ===== LIQUID GLASS: ACORDEONES POR PAÍS ===== */
    #departamentosAccordion .accordion-item {
        background: rgba(255, 255, 255, 0.85) !important;
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.4) !important;
        border-radius: var(--bs-border-radius-lg) !important;
        overflow: hidden;
        transition: box-shadow 0.3s ease;
    }
    #departamentosAccordion .accordion-item:hover {
        box-shadow: 0 8px 28px rgba(0, 0, 0, 0.08);
    }
    #departamentosAccordion .accordion-button {
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border-radius: 0 !important;
        border: none;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08) inset;
    }
    #departamentosAccordion .accordion-button .badge {
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        background: rgba(0, 0, 0, 0.3) !important;
        border: 1px solid rgba(255, 255, 255, 0.35);
        color: #fff !important;
        font-weight: 600;
        font-size: 0.8rem;
        padding: 0.35em 0.7em;
    }
    #departamentosAccordion .accordion-body {
        background: rgba(255, 255, 255, 0.5) !important;
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
    }

    .org-departamento-block {
        border: 1px solid rgba(99, 102, 241, 0.14);
        border-radius: 0.75rem;
        background: rgba(255, 255, 255, 0.6);
        padding: 1rem;
        margin-bottom: 1rem;
    }
    .org-departamento-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
        color: #2f3f50;
    }
    .organizacion-empresa-title {
        display: flex;
        align-items: center;
        gap: .5rem;
        color: #2f3f50;
        font-weight: 700;
        margin-bottom: .85rem;
    }
    .organizacion-empresa-card {
        width: 100%;
        height: 100%;
        min-height: 164px;
        border: 1px solid rgba(67, 89, 113, 0.18);
        border-radius: 0.75rem;
        background: rgba(255, 255, 255, 0.9);
        box-shadow: 0 6px 18px rgba(67, 89, 113, 0.06);
        padding: 0;
        text-align: left;
        color: #2f3f50;
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease, background .2s ease;
    }
    .organizacion-empresa-card:hover,
    .organizacion-empresa-card.is-active {
        transform: translateY(-2px);
        border-color: rgba(0, 71, 187, 0.42);
        box-shadow: 0 10px 24px rgba(0, 71, 187, 0.12);
        background: rgba(239, 246, 255, 0.95);
    }
    .organizacion-empresa-card .empresa-name {
        font-weight: 700;
        font-size: 1.12rem;
        line-height: 1.2;
    }
    .organizacion-empresa-card .empresa-meta {
        color: #6b7280;
        font-size: .82rem;
        margin-bottom: .15rem;
    }
    .organizacion-empresa-visual {
        width: 112px;
        height: 112px;
        border-radius: 1rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #f3f6fa 0%, #ffffff 100%);
        border: 1px solid rgba(67, 89, 113, 0.18);
        color: #2f3f50;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.85), 0 8px 18px rgba(47,63,80,.08);
    }
    .organizacion-empresa-visual i {
        font-size: 3.8rem;
        line-height: 1;
    }
    .organizacion-back-btn {
        border-color: #1d2f4f !important;
        color: #1d2f4f !important;
        border-radius: 999px !important;
        font-weight: 700 !important;
        padding: .45rem 1rem !important;
        background: #fff !important;
        box-shadow: 0 4px 12px rgba(29, 47, 79, 0.08);
    }
    .organizacion-back-btn:hover {
        background: #1d2f4f !important;
        color: #fff !important;
    }

    /* Tarjetas de áreas dentro de acordeones */
    #departamentosAccordion .dept-card {
        background: rgba(255, 255, 255, 0.88) !important;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.45);
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    #departamentosAccordion .dept-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 28px rgba(0, 0, 0, 0.1) !important;
    }

    /* ===== LIQUID GLASS: MODALES DEPARTAMENTO ===== */
    #addDepartamentoModal .modal-content {
        background: rgba(255, 255, 255, 0.92) !important;
        backdrop-filter: blur(18px);
        -webkit-backdrop-filter: blur(18px);
        border: 1px solid rgba(255, 255, 255, 0.5);
        border-radius: 1rem;
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.1), 0 0 0 1px rgba(255,255,255,0.3) inset;
    }
    #modalDetalleDepartamento .modal-content {
        background: rgba(255, 255, 255, 0.92) !important;
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.45);
        border-radius: 1rem;
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.1), 0 0 0 1px rgba(255,255,255,0.25) inset;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    #modalDetalleDepartamento .modal-header {
        background: rgba(0, 0, 0, 0.03) !important;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border-bottom: 1px solid rgba(0, 0, 0, 0.06);
    }
    #modalDetalleDepartamento .modal-footer {
        background: rgba(0, 0, 0, 0.02) !important;
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        border-top: 1px solid rgba(0, 0, 0, 0.06);
    }
    #modalDetalleDepartamento .modal-dialog {
        max-height: 90vh;
        margin: 1.75rem auto;
        display: flex;
        flex-direction: column;
    }
    #modalDetalleDepartamento .modal-body {
        overflow-y: auto;
        overflow-x: hidden;
        flex: 1 1 auto;
        -webkit-overflow-scrolling: touch;
    }

    /* ===== DARK MODE: ACORDEONES ===== */
    body.dark-mode #departamentosAccordion .accordion-item {
        background: rgba(30, 41, 59, 0.85) !important;
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-color: rgba(51, 65, 85, 0.5) !important;
    }
    body.dark-mode #departamentosAccordion .accordion-body {
        background: rgba(30, 41, 59, 0.5) !important;
    }
    body.dark-mode #departamentosAccordion .dept-card {
        background: rgba(30, 41, 59, 0.85) !important;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-color: rgba(51, 65, 85, 0.5);
    }
    body.dark-mode #departamentosAccordion .dept-card:hover {
        box-shadow: 0 10px 28px rgba(0, 0, 0, 0.3) !important;
    }
    /* Dark Mode: Modales */
    body.dark-mode #addDepartamentoModal .modal-content {
        background: rgba(30, 41, 59, 0.92) !important;
        backdrop-filter: blur(18px);
        -webkit-backdrop-filter: blur(18px);
        border-color: rgba(51, 65, 85, 0.5);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(51,65,85,0.3) inset;
    }
    body.dark-mode #modalDetalleDepartamento .modal-content {
        background: rgba(30, 41, 59, 0.92) !important;
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border-color: rgba(51, 65, 85, 0.5);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(51,65,85,0.3) inset;
    }
    body.dark-mode #modalDetalleDepartamento .modal-header {
        background: rgba(51, 65, 85, 0.6) !important;
        border-bottom-color: #334155;
    }
    body.dark-mode #modalDetalleDepartamento .modal-footer {
        background: rgba(51, 65, 85, 0.4) !important;
        border-top-color: #334155;
    }

    @media (max-width: 575.98px) {
        #modalDetalleDepartamento .modal-dialog {
            margin: 0.5rem;
            max-width: calc(100vw - 1rem);
        }
        #modalDetalleDepartamento .modal-content {
            max-height: 95vh;
        }
    }

</style>


<div class="d-flex justify-content-between align-items-center mb-1">
    <h4 class="mb-0">Organización</h4>
    <button type="button" class="btn btn-primary" id="btnAccionOrganizacion" onclick="abrirModalNuevoDepartamento()" style="display: none;">
        <i class="fa fa-plus-circle me-2"></i>Nueva Área
    </button>
</div>
<p class="mb-4 text-muted">Departamentos agrupados por país. Haz clic en un acordeón para expandir o colapsar.</p>

    <div class="accordion" id="departamentosAccordion">
        <div class="text-center py-5">
            <i class="fa fa-spinner fa-spin fa-2x text-muted"></i>
            <p class="text-muted mt-2">Cargando organización...</p>
        </div>
    </div>


<!-- Modal para agregar departamento -->
<div class="modal fade" id="addDepartamentoModal" tabindex="-1" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-simple modal-add-new-cc">
        <div class="modal-content">
            <div class="modal-body">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="text-center mb-4">
                    <h4 class="mb-2">Agregar nueva área</h4>
                    <p class="text-muted mb-0">Selecciona el país y escribe el nombre del área.</p>
                </div>
                <form id="addDepartamentoForm" class="row g-4" onsubmit="return false" novalidate="novalidate">
                    <input type="hidden" id="addDepartamentoModo" value="departamento">
                    <input type="hidden" id="addDepartamentoEmpresaId" value="">
                    <input type="hidden" id="addDepartamentoContextPaisId" value="">
                    <input type="hidden" id="addDepartamentoContextDireccionId" value="">
                    <input type="hidden" id="addDepartamentoContextOrgId" value="">
                    <div class="col-12">
                        <label class="form-label w-100" for="addDepartamentoEmpresaSelect">Empresa *</label>
                        <select id="addDepartamentoEmpresaSelect" class="form-select">
                            <option value="">-- Selecciona una empresa --</option>
                        </select>
                        <div class="invalid-feedback" id="errorEmpresa" style="display: none;"></div>
                    </div>
                    <div class="col-12">
                        <label class="form-label w-100" for="addDepartamentoPaisId">País *</label>
                        <select id="addDepartamentoPaisId" class="form-select" required>
                            <option value="">-- Selecciona un país --</option>
                        </select>
                        <div class="invalid-feedback" id="errorPais" style="display: none;"></div>
                    </div>
                    <div class="col-12">
                        <label class="form-label w-100" for="addDepartamentoOrganizacionalId">Área *</label>
                        <select id="addDepartamentoOrganizacionalId" class="form-select" required>
                            <option value="">-- Selecciona un área --</option>
                        </select>
                        <div class="invalid-feedback" id="errorDepartamentoOrganizacional" style="display: none;"></div>
                    </div>
                    <div class="col-12">
                        <label class="form-label w-100" for="modalNombreDepartamento">Nombre del Área *</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text">
                                <i class="fa fa-building"></i>
                            </span>
                            <input 
                                id="modalNombreDepartamento" 
                                name="modalNombreDepartamento" 
                                class="form-control" 
                                type="text" 
                                placeholder="Ej. Cobranza, Comercial, Administración de Finanzas..."
                                required
                                maxlength="30"
                                oninput="sanitizarInputNombre(this)"
                                onblur="this.value = toTitleCase(this.value.trim())">
                        </div>
                        <div class="invalid-feedback" id="errorNombre" style="display: none;"></div>
                    </div>
                    
                    <div class="col-12 text-center">
                        <button type="submit" class="btn btn-primary me-sm-3 me-1">
                            <i class="fa fa-save me-2"></i>Guardar
                        </button>
                        <button type="reset" class="btn btn-label-secondary btn-reset" data-bs-dismiss="modal" aria-label="Close">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

    <!-- Modal Detalle Área -->
    <div class="modal fade" id="modalDetalleDepartamento" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">

                <!-- Header -->
                <div class="modal-header align-items-start flex-row">
                    <div class="w-100 text-center order-1">

                        <!-- Título editable -->
                        <div class="d-flex justify-content-center align-items-center">
                            <div class="titulo-departamento-container">
                                <h4 class="mb-1"
                                    id="tituloDepartamento"
                                    contenteditable="false"
                                    data-departamento-id=""
                                    maxlength="30"
                                    onfocus="inicioEdicionTitulo(this)"
                                    onblur="guardarTituloDepartamento(this)">
                                    Call Center
                                </h4>
                                <i class="fa fa-pencil editar-titulo"
                                   onclick="forzarEdicionTitulo(this)"></i>
                            </div>
                        </div>

                        <input type="hidden"
                               id="id_departamento"
                               class="form-control"
                               placeholder="dep">


                        <p class="text-muted mb-0">
                            Puestos registrados en el área
                        </p>
                    </div>

                    <button type="button" class="btn-close order-2 ms-0 ms-sm-auto" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>


                <!-- Body -->
                <div class="modal-body">
                    <div class="card">
                        <div class="card-body">

                            <!-- Header lista -->
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h6 class="mb-0 fw-semibold">Listado de puestos</h6>
                                    <p class="mb-0 mt-1 small text-muted fst-italic">Orden jerárquico por nivel de cargo (escala de mando, de mayor a menor rango)</p>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                        onclick="mostrarInputNuevoPuesto()">
                                    <i class="fa fa-plus-circle"> </i>&emsp;Nuevo puesto
                                </button>
                            </div>

                            <!-- Input nuevo puesto -->
                            <div id="nuevoPuestoContainer" class="d-none mb-4">
                                <div class="input-group input-group-sm">
                                    <input type="text"
                                           id="inputNuevoPuesto"
                                           class="form-control"
                                           placeholder="Nombre del puesto"
                                           maxlength="30"
                                           oninput="sanitizarInputNombre(this)"
                                           onblur="this.value = toTitleCase(this.value.trim())">
                                    <button type="button" class="btn btn-primary"
                                            onclick="guardarNuevoPuesto(); return false;">
                                        <i class="fa fa-save"></i>
                                    </button>
                                </div>
                            </div>

                            
                            <!-- Lista de puestos (arrastrable para reordenar) -->
                            <ul class="list-group list-group-flush p-0 m-0 drag-list" id="listaPuestos">

                                <!-- Puesto -->
                                <li class="d-flex mb-4 align-items-center puesto-item">
                                    <div class="avatar flex-shrink-0 me-4">
                  <span class="avatar-initial rounded bg-label-primary">
                    <i class="fa fa-phone"></i>
                  </span>
                                    </div>

                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-2">
                                            <h6 class="mb-0 fw-normal puesto-nombre"
                                                contenteditable="false">
                                                Asesor Telefónico
                                            </h6>
                                            <i class="fa fa-pencil editar-puesto"
                                               onclick="editarPuesto(this)"></i>
                                        </div>
                                        <p class="text-muted mb-0">
                                            Atención y seguimiento a clientes
                                        </p>
                                    </div>
                                </li>

                                <!-- Puesto -->
                                <li class="d-flex mb-4 align-items-center puesto-item">
                                    <div class="avatar flex-shrink-0 me-4">
                  <span class="avatar-initial rounded bg-label-success">
                    <i class="fa fa-user icon-lg"></i>
                  </span>
                                    </div>

                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-2">
                                            <h6 class="mb-0 fw-normal puesto-nombre"
                                                contenteditable="false">
                                                Supervisor de Call Center
                                            </h6>
                                            <i class="fa fa-pencil editar-puesto"
                                               onclick="editarPuesto(this)"></i>
                                        </div>
                                        <p class="text-muted mb-0">
                                            Monitoreo y control de calidad
                                        </p>
                                    </div>
                                </li>

                            </ul>

                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="modal-footer d-flex justify-content-between align-items-center flex-wrap">
                    <small class="text-muted me-2 flex-grow-1">
                        Arrastre los puestos para ordenarlos según el nivel jerárquico que considere correcto.<br>
                        Los cambios se guardan automáticamente.
                    </small>
                    <button type="button" class="btn btn-secondary ms-auto" data-bs-dismiss="modal">
                        Cerrar
                    </button>
                </div>

            </div>
        </div>
    </div>

<script>
/**
 * ==========================================
 * FUNCIÓN TO TITLE CASE
 * ==========================================
 * Convierte texto a formato Title Case
 * (Primera letra de cada palabra en mayúscula)
 */
function toTitleCase(str) {
    if (!str) return '';
    return str
        .split(/\s+/)
        .filter(Boolean)
        .map(function(word) {
            if (/^[A-ZÁÉÍÓÚÑ]{2,4}$/.test(word)) return word;
            return word.toLowerCase().replace(/^\w/, function(char) {
                return char.toUpperCase();
            });
        })
        .join(' ');
}
</script>
