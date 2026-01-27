<style>
    /* ===== INLINE EDIT LIMPIO ===== */
    [contenteditable="true"] {
        outline: none;                 /* quita línea negra */
        border-radius: 6px;
        padding: 2px 6px;              /* aire */
        background-color: #f4f5ff;     /* fondo suave */
        box-shadow: inset 0 0 0 1px #696cff40;
        transition: all 0.15s ease;
    }

    /* Hover sutil */
    [contenteditable="true"]:hover {
        background-color: #eef0ff;
    }

    /* Focus elegante */
    [contenteditable="true"]:focus {
        background-color: #ffffff;
        box-shadow: 0 0 0 2px #696cff50;
    }

    /* Evita salto de altura */
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

    /* ===== TÍTULO DEPARTAMENTO (IGUAL QUE PUESTOS) ===== */
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
        opacity: 1.8;
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
    /* Círculo gris del número de posición (siempre visible) */
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

</style>


<h4>Departamentos Registrados</h4>

    <p class="mb-6">A role provided access to predefined menus and features so that depending on assigned role an administrator can have access to what user needs.</p>
    <!-- Role cards -->

    <div id="departamentosCards" class="row g-7"></div>


<!-- Modal para agregar departamento - Estilo Sneat -->
<div class="modal fade" id="addDepartamentoModal" tabindex="-1" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-simple modal-add-new-cc">
        <div class="modal-content">
            <div class="modal-body">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="text-center mb-4">
                    <h4 class="mb-2">Agregar nuevo departamento</h4>
                    <p class="text-muted">Escribe solo el nombre del departamento</p>
                </div>
                <form id="addDepartamentoForm" class="row g-4" onsubmit="return false" novalidate="novalidate">
                    <div class="col-12">
                        <label class="form-label w-100" for="modalNombreDepartamento">Nombre del Departamento</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text">
                                <i class="fa fa-building"></i>
                            </span>
                            <input 
                                id="modalNombreDepartamento" 
                                name="modalNombreDepartamento" 
                                class="form-control" 
                                type="text" 
                                placeholder="Ej. Cobranza, Call Center, Ventas..." 
                                required
                                maxlength="30"
                                oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '').replace(/^\s+/, '').replace(/\s{2,}/g, ' ')" 
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

    <!-- Modal Detalle Departamento -->
    <div class="modal fade" id="modalDetalleDepartamento" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
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
                                    oninput="this.textContent = this.textContent.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '').replace(/^\s+/, '').replace(/\s{2,}/g, ' ')"
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
                            Puestos registrados en el departamento
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
                                <button class="btn btn-sm btn-outline-primary"
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
                                           oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '').replace(/^\s+/, '').replace(/\s{2,}/g, ' ')" 
                                           onblur="this.value = toTitleCase(this.value.trim())">
                                    <button class="btn btn-primary"
                                            onclick="guardarNuevoPuesto()">
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
                    <small class="text-muted me-2">
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
    return str.toLowerCase().replace(/\b\w/g, function(char) {
        return char.toUpperCase();
    });
}
</script>
