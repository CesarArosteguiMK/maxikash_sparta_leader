<style>
    #loaderTabla {
        text-align: center;
        padding: 40px;
    }

    #modulos-container table {
        width: 100%;
        table-layout: fixed;
    }

    #modulos-container td:first-child {
        white-space: normal;
        word-break: break-word;
    }

    #modulos-container td:last-child {
        width: 110px;
        text-align: right;
        white-space: nowrap;
    }

    #modulos-container small {
        white-space: normal;
        word-break: break-word;
    }
    #modulos-container table.border-top {
        border-top: none !important;
    }

    #modulos-container tbody tr:last-child td {
        border-bottom: none !important;
    }

    #offcanvasEditPerfil .tab-content:not(.doc-example-content) {
        padding: .25rem 0;
    }

    /* Estilos para el Modal de Permisos */
    #modalEditPerfil .modal-content {
        border-radius: 1rem;
        overflow: hidden;
    }

    #modalEditPerfil .btn-outline-secondary:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 8px rgba(108, 117, 125, 0.25) !important;
    }

    #modalEditPerfil .nav-tabs-custom .nav-link {
        border: none;
        border-bottom: 3px solid transparent;
        color: #6c757d;
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    #modalEditPerfil .nav-tabs-custom .nav-link:hover {
        color: #495057;
        background-color: #f8f9fa;
    }

    #modalEditPerfil .nav-tabs-custom .nav-link.active {
        color: #212529;
        border-bottom-color: #495057;
        background-color: transparent;
        font-weight: 600;
    }

    #modalEditPerfil .accordion-item {
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
    }

    #modalEditPerfil .accordion-item:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    }

    #modalEditPerfil .accordion-button {
        font-size: 0.95rem;
        transition: all 0.3s ease;
    }

    #modalEditPerfil .accordion-button:not(.collapsed) {
        background: #f8f9fa;
        color: #212529;
    }

    #modalEditPerfil .table-hover tbody tr {
        transition: all 0.2s ease;
    }

    #modalEditPerfil .table-hover tbody tr:hover {
        transform: translateX(4px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    #puestos-container::-webkit-scrollbar,
    #modulos-container::-webkit-scrollbar {
        width: 8px;
    }

    #puestos-container::-webkit-scrollbar-track,
    #modulos-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    #puestos-container::-webkit-scrollbar-thumb,
    #modulos-container::-webkit-scrollbar-thumb {
        background: #adb5bd;
        border-radius: 10px;
    }

    #puestos-container::-webkit-scrollbar-thumb:hover,
    #modulos-container::-webkit-scrollbar-thumb:hover {
        background: #6c757d;
    }

</style>
<div class="content-wrapper">

    <!-- =======================
         CARD PRINCIPAL
    ======================== -->
    <div class="card">

        <!-- =======================
             FILTROS
        ======================== -->
        <div class="card-header border-bottom">
            <h5 class="card-title mb-0">Filtros de búsqueda</h5>

            <div class="row pt-4 g-6">
                <div class="col-md-4">
                    <select id="UserRole" class="form-select text-capitalize">
                        <option value="">Selecciona Departamento</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <select id="UserPlan" class="form-select text-capitalize">
                        <option value="">Selecciona Puesto</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <select id="FilterTransaction" class="form-select text-capitalize">
                        <option value="">Selecciona Estatus</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- =======================
             BOTÓN AGREGAR
        ======================== -->
        <div class="row justify-content-between m-4">
            <div class="col-8"></div>

            <div class="col-4 d-flex align-items-end justify-content-end">
                <button
                        type="button"
                        class="btn btn-primary add-new"
                        data-bs-toggle="offcanvas"
                        data-bs-target="#offcanvasAddUser"
                >
                    <i class="icon-base bx bx-plus icon-sm me-2"></i>
                    <span class="d-none d-sm-inline-block">Agregar Usuario</span>
                </button>
            </div>
        </div>

        <!-- =======================
             FILTRO DE FECHAS PARA BAJAS (oculto por defecto)
        ======================== -->
        <div id="filtroFechaBajas" style="display: none;" class="row m-4">
            <div class="col-12">
                <div class="card border">
                    <div class="card-body">
                        <div class="row align-items-end g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Rango de Fechas</label>
                                <input 
                                    type="text" 
                                    id="flatpickr-range-bajas" 
                                    class="form-control" 
                                    placeholder="Selecciona un rango de fechas"
                                />
                            </div>
                            <div class="col-md-8 d-flex gap-2 align-items-end">
                                <button 
                                    type="button" 
                                    id="btnLimpiarFiltroBajas" 
                                    class="btn text-white"
                                    style="background-color: #d2d755; border-color: #d2d755; min-width: 150px;"
                                >
                                    <i class="fa fa-times me-2"></i>Limpiar Filtro
                                </button>
                                <button 
                                    type="button" 
                                    id="btnDescargarBajas" 
                                    class="btn text-white"
                                    style="background-color: #0047bb; border-color: #0047bb; min-width: 150px;"
                                >
                                    <i class="fa fa-download me-2"></i>Descargar Excel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =======================
             TABLA
        ======================== -->
        <div class="card-datatable table-responsive">
            <table id="historialUsuarios" class="dt-responsive table border-top">
                <thead>
                <tr>
                    <th></th> <!-- control responsive -->
                    <th>Nombre</th>
                    <th>Departamento</th>
                    <th>Estatus</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

    </div>

    <!-- =======================
         OFFCANVAS - AGREGAR
    ======================== -->
    <div class="offcanvas offcanvas-end" id="offcanvasAddUser">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title">Registrar Nuevo Usuario</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>

        <div class="offcanvas-body p-6">
            <form id="addNewUserForm" onsubmit="return false">

                <div class="col-md-5 d-none">
                    <div class="mb-2">
                        <label class="form-label">Número de Empleado *</label>
                        <input type="text" id="add_num_telefono" class="form-control phone-mask">
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label">Nombres *</label>
                    <input type="text" id="add_nombres" class="form-control" oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '').replace(/^\s+/, '').replace(/\s{2,}/g, ' ').toUpperCase()" onblur="this.value = this.value.trim()" style="text-transform: uppercase;">
                </div>

                <div class="mb-2">
                    <label class="form-label">Apellido Paterno *</label>
                    <input type="text" id="add_apellidop" class="form-control" oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '').replace(/^\s+/, '').replace(/\s{2,}/g, ' ').toUpperCase()" onblur="this.value = this.value.trim()" style="text-transform: uppercase;">
                </div>

                <div class="mb-2">
                    <label class="form-label">Apellido Materno *</label>
                    <input type="text" id="add_apellidom" class="form-control" oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '').replace(/^\s+/, '').replace(/\s{2,}/g, ' ').toUpperCase()" onblur="this.value = this.value.trim()" style="text-transform: uppercase;">
                </div>

                <div class="mb-2">
                    <label class="form-label">Teléfono *</label>
                    <input type="text" id="add_telefono" class="form-control phone-mask" oninput="this.value = this.value.replace(/[^0-9]/g, '')" maxlength="10">
                </div>

                <div class="mb-2">
                    <label class="form-label">Departamento *</label>
                    <select id="add_departamento_id" class="form-select">
                        <option value="">Seleccione un departamento</option>
                        <?php foreach ($departamento['datos'] as $dep): ?>
                            <option value="<?= htmlspecialchars($dep['id']) ?>">
                                <?= htmlspecialchars($dep['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-2">
                    <label class="form-label">Puesto *</label>
                    <select id="add_id_puesto" class="form-select" disabled>
                        <option value="">Seleccione un puesto</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label class="form-label">Jefe *</label>
                    <select id="add_id_jefe" class="form-select" disabled>
                        <option value="">Seleccione un jefe</option>
                    </select>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="add_asignar_legion" onchange="toggleSelectLegion()">
                        <label class="form-check-label" for="add_asignar_legion">
                            Asignar legion
                        </label>
                    </div>
                </div>

                <div class="mb-2" id="div_select_legion" style="display: none;">
                    <label class="form-label">Legión *</label>
                    <select id="add_id_legion" class="form-select">
                        <option value="">Seleccione una legión</option>
                        <option value="1">Sabueso</option>
                        <option value="2">Heraldo</option>
                        <option value="3">Centinela</option>
                        <option value="4">Senturiones</option>
                        <option value="5">Espartano</option>
                    </select>
                </div>

                <div class="mb-2">
                    <label class="form-label">Usuario *</label>
                    <input type="text" id="add_usuario" class="form-control" maxlength="10" oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '').replace(/^\s+/, '').replace(/\s{2,}/g, ' ').toUpperCase()" onblur="this.value = this.value.trim()" style="text-transform: uppercase;">
                </div>

                <div class="mb-7">
                    <label class="form-label">Contraseña *</label>
                    <input type="text" id="add_contrasena" class="form-control" maxlength="15" oninput="this.value = this.value.replace(/^\s+/, '').replace(/\s{2,}/g, ' ')" onblur="this.value = this.value.trim()">
                </div>

                <button class="btn btn-primary me-3" onclick="guardarGestor()">Guardar</button>
                <button class="btn btn-label-danger" data-bs-dismiss="offcanvas">Cancelar</button>

            </form>
        </div>
    </div>

    <!-- =======================
           OFFCANVAS -
      ======================== -->
    <!-- Modal RFC -->
    <div class="modal fade" id="modalBajas" tabindex="-1" aria-labelledby="modalRFCLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalRFCLabel">Registro de Baja</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <p id="gestor"><strong>Gestor:</strong> </p>

                    <div class="mb-3" style="display: none;">
                        <label for="id" class="form-label"><strong>Id</strong></label>
                    </div>

                    <div class="mb-3">
                        <label for="motivoBaja" class="form-label"><strong>Motivo de baja: </strong></label>
                        <select class="form-select" id="motivoBaja">
                            <option value="">-- Selecciona un motivo --</option>
                            <option value="Renuncia voluntaria">Renuncia voluntaria</option>
                            <option value="Incumplimiento de objetivos">Incumplimiento de objetivos</option>
                            <option value="Falta de asistencia">Falta de asistencia</option>
                            <option value="Mutuo acuerdo">Mutuo acuerdo</option>
                            <option value="Desempeño insuficiente">Desempeño insuficiente</option>
                            <option value="Falta de puntualidad">Falta de puntualidad</option>
                            <option value="Problemas disciplinarios">Problemas disciplinarios</option>
                            <option value="Conflictos internos">Conflictos internos</option>
                            <option value="Cambio de puesto">Cambio de puesto</option>
                            <option value="Traslado a otra sucursal">Traslado a otra sucursal</option>
                            <option value="Renuncia por motivos personales">Renuncia por motivos personales</option>
                            <option value="Baja por salud">Baja por salud</option>
                            <option value="Baja administrativa">Baja administrativa</option>
                            <option value="Falta de capacitación">Falta de capacitación</option>
                            <option value="Empleado duplicado">Empleado Duplicado</option>
                            <option value="Otros">Otros</option>
                        </select>
                    </div>

                    <!-- 🆕 Motivo de baja -->
                    <div class="mb-3">
                        <label for="motivoBajaDescripcion" class="form-label"><strong>Descripción de la baja:</strong></label>
                        <textarea class="form-control" id="motivoBajaDescripcion" rows="3" placeholder="Escribe el motivo..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>Documento de baja (PDF):</strong></label>
                        <div class="d-flex gap-2 align-items-center mb-2">
                            <input
                                type="file"
                                id="archivoPDF"
                                class="form-control d-none"
                                accept=".pdf"
                                multiple
                            />
                            <button
                                type="button"
                                class="btn btn-outline-primary"
                                onclick="seleccionarArchivoBajaModal()"
                            >
                                <i class="fa fa-paperclip me-2"></i>Elegir archivos
                            </button>
                            <span id="bajaModal_nombreArchivo" class="text-muted small">No se ha seleccionado ningún archivo</span>
                        </div>
                        <small class="text-muted">Puedes subir múltiples archivos PDF.</small>
                    </div>
                    <div id="listaArchivos" class="mt-2" style="display: none;"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>

                    <!-- 🆕 Botón para confirmar baja -->
                    <button type="button" class="btn btn-danger" onclick="confirmarBaja()">
                        Confirmar Baja
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- =======================
         MODAL - CARGAR DOCUMENTO BAJA
    ======================== -->
    <div class="modal fade" id="modalCargarDocumentoBaja" tabindex="-1" aria-labelledby="modalCargarDocLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCargarDocLabel">Cargar Documento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <p id="cargarDoc_nombrePersona" class="mb-3"><strong>Persona:</strong></p>
                    <input type="hidden" id="cargarDoc_registroBaja" value="">

                    <div class="mb-3">
                        <label for="cargarDoc_tipoDocumento" class="form-label"><strong>Tipo de Documento: </strong></label>
                        <select class="form-select" id="cargarDoc_tipoDocumento">
                            <option value="Documento Baja">Documento Baja</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>Archivo:</strong></label>
                        <div class="d-flex gap-2 align-items-center mb-2">
                            <input 
                                type="file" 
                                id="cargarDoc_archivo" 
                                class="form-control d-none" 
                                onchange="agregarArchivoLista(this)"
                                accept=".pdf"
                                multiple
                            />
                            <button 
                                type="button" 
                                class="btn btn-outline-primary"
                                onclick="seleccionarArchivoDocumento()"
                            >
                                <i class="fa fa-paperclip me-2"></i>Elegir archivos
                            </button>
                            <span id="cargarDoc_nombreArchivo" class="text-muted small">No se ha seleccionado ningún archivo</span>
                        </div>
                        <small class="text-muted">Puedes subir múltiples archivos PDF.</small>
                    </div>

                    <!-- Lista de archivos nuevos seleccionados (antes de subir) - ARRIBA DE LA TABLA -->
                    <div id="cargarDoc_listaArchivos" class="mb-4" style="display: none;">
                        <h6 class="mb-3"><strong>Archivos Nuevos Seleccionados</strong></h6>
                        <!-- Los archivos nuevos se agregarán aquí dinámicamente -->
                    </div>

                    <!-- Tabla de archivos subidos -->
                    <div class="mt-4">
                        <h6 class="mb-3"><strong>Archivos Subidos</strong></h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Documento</th>
                                        <th>Archivo</th>
                                        <th>Fecha de carga</th>
                                        <th>Válido</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="cargarDoc_tablaArchivos">
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No hay archivos subidos</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="subirDocumentoBaja()">
                        <i class="fa fa-upload me-2"></i>Subir Archivo
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- =======================
         MODAL - CARGAR DOCUMENTO PERSONA (GESTIÓN)
    ======================== -->
    <div class="modal fade" id="modalCargarDocumentoPersona" tabindex="-1" aria-labelledby="modalCargarDocPersonaLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCargarDocPersonaLabel">Cargar Documento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <p id="cargarDocPersona_nombrePersona" class="mb-3"><strong>Persona:</strong></p>
                    <input type="hidden" id="cargarDocPersona_idPersona" value="">

                    <div class="mb-3">
                        <label for="cargarDocPersona_tipoDocumento" class="form-label"><strong>Tipo de Documento: </strong></label>
                        <select class="form-select" id="cargarDocPersona_tipoDocumento">
                            <option value="">Seleccione un tipo de documento</option>
                            <option value="Acta de Nacimiento">Acta de Nacimiento</option>
                            <option value="Certificado de Estudios">Certificado de Estudios</option>
                            <option value="Comprobante de Domicilio">Comprobante de Domicilio</option>
                            <option value="CURP">CURP</option>
                            <option value="Documento baja">Documento baja</option>
                            <option value="Identificación Oficial (INE)">Identificación Oficial (INE)</option>
                            <option value="Referencias Laborales">Referencias Laborales</option>
                            <option value="RFC">RFC</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>Archivo:</strong></label>
                        <div class="d-flex gap-2 align-items-center mb-2">
                            <input 
                                type="file" 
                                id="cargarDocPersona_archivo" 
                                class="form-control d-none" 
                                onchange="agregarArchivoListaPersona(this)"
                                accept=".pdf"
                            />
                            <button 
                                type="button" 
                                class="btn btn-outline-primary"
                                onclick="seleccionarArchivoDocumentoPersona()"
                            >
                                <i class="fa fa-paperclip me-2"></i>Elegir archivos
                            </button>
                            <span id="cargarDocPersona_nombreArchivo" class="text-muted small">No se ha seleccionado ningún archivo</span>
                        </div>
                        <small class="text-muted">Puedes subir múltiples archivos PDF.</small>
                    </div>

                    <!-- Lista de archivos nuevos seleccionados (antes de subir) - ARRIBA DE LA TABLA -->
                    <div id="cargarDocPersona_listaArchivos" class="mb-4" style="display: none;">
                        <h6 class="mb-3"><strong>Archivos Nuevos Seleccionados</strong></h6>
                        <!-- Los archivos nuevos se agregarán aquí dinámicamente -->
                    </div>

                    <!-- Tabla de archivos subidos -->
                    <div class="mt-4">
                        <h6 class="mb-3"><strong>Archivos Subidos</strong></h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Documento</th>
                                        <th>Archivo</th>
                                        <th>Fecha de carga</th>
                                        <th>Válido</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="cargarDocPersona_tablaArchivos">
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No hay archivos subidos</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="subirDocumentoPersona()">
                        <i class="fa fa-upload me-2"></i>Subir Archivo
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalAuscencia" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Registro de Ausencias</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">



                    <!-- ================== DATOS DEL GESTOR ================== -->
                    <p id="gestor_ausencia" class="mb-3"><strong>Gestor:</strong></p>
                    <input type="hidden" id="edit_id_ausencia">

                    <hr>

                    <!-- ================== FORMULARIO AUSENCIA ================== -->
                    <div class="row g-3">

                        <input type="hidden" id="id_ausencia">

                        <div class="col-md-4">
                            <label class="form-label"><strong>Razón de ausencia</strong></label>
                            <select class="form-select" id="razonAusencia">
                                <option value="">-- Selecciona --</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label"><strong>Fecha inicio</strong></label>
                            <input type="datetime-local" class="form-control" id="fechaInicio">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label"><strong>Fecha fin</strong></label>
                            <input type="datetime-local" class="form-control" id="fechaFin">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label"><strong>Descripción</strong></label>
                            <textarea class="form-control" id="descripcionAusencia" rows="2"></textarea>
                        </div>

                    </div>

                    <div class="mt-3">
                        <button class="btn btn-primary"  id="btnGuardarAusencia" onclick="guardarAusencia()">
                            Registrar ausencia
                        </button>
                    </div>

                    <hr>

                    <!-- ================== TABLA DE AUSENCIAS ================== -->
                    <h6 class="mb-2"><strong>Historial de ausencias</strong></h6>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-striped">
                            <thead class="table-dark">
                            <tr>
                                <th>Razón</th>
                                <th>Inicio</th>
                                <th>Fin</th>
                                <th>Descripción</th>
                                <th>Activo</th>
                                <th>Acciones</th>
                            </tr>
                            </thead>
                            <tbody id="tablaAusencias">
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    Sin registros
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                </div>


                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>

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

                <div class="mb-2" style="display: none">
                    <label class="form-label">Id Empleado *</label>
                    <input ype="text" id="edit_id" class="form-control phone-mask"disabled>
                </div>

                <div class="mb-2">
                    <label class="form-label">Número de Empleado *</label>
                    <input ype="text" id="edit_num_empleado" class="form-control phone-mask"disabled>
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
                    <input type="text" id="edit_telefono" class="form-control phone-mask" oninput="this.value = this.value.replace(/[^0-9]/g, '')" maxlength="10">
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

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="edit_asignar_legion" onchange="toggleSelectLegionEdit()">
                        <label class="form-check-label" for="edit_asignar_legion">
                            Asignar legion
                        </label>
                    </div>
                </div>

                <div class="mb-2" id="edit_div_select_legion" style="display: none;">
                    <label class="form-label">Legión *</label>
                    <select id="edit_id_legion" class="form-select">
                        <option value="">Seleccione una legión</option>
                        <option value="1">Sabueso</option>
                        <option value="2">Heraldo</option>
                        <option value="3">Centinela</option>
                        <option value="4">Senturiones</option>
                        <option value="5">Espartano</option>
                    </select>
                </div>

                <div class="mb-2">
                    <label class="form-label">Usuario *</label>
                    <input type="text" id="edit_usuario" class="form-control" readonly oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '').toUpperCase()" style="text-transform: uppercase;">
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

    <!-- =======================
      MODAL - GESTIÓN DE PERMISOS Y PUESTOS
 ======================== -->
    <div class="modal fade" id="modalEditPerfil" tabindex="-1" aria-labelledby="modalEditPerfilLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" style="border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.12);">
                <div class="modal-header" style="background: #f8f9fa; border-bottom: 2px solid #e9ecef; padding: 1.5rem;">
                    <div class="d-flex align-items-center w-100">
                        <div class="flex-grow-1">
                            <h5 class="modal-title fw-bold mb-1" id="modalEditPerfilLabel" style="color: #2c3e50;">
                                <i class="fa fa-user-shield me-2" style="color: #495057;"></i>Gestión de Permisos y Accesos
                            </h5>
                            <p class="text-muted mb-0 small" id="modalEditPerfil_subtitle">Administrar puestos y módulos del usuario</p>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle p-2" data-bs-dismiss="modal" aria-label="Cerrar" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border: 2px solid #6c757d; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(108, 117, 125, 0.15);" onmouseover="this.style.backgroundColor='#6c757d'; this.querySelector('i').style.color='#fff';" onmouseout="this.style.backgroundColor='transparent'; this.querySelector('i').style.color='#6c757d';">
                            <i class="fa fa-times" style="font-size: 1.1rem; color: #6c757d; transition: color 0.3s ease;"></i>
                        </button>
                    </div>
                </div>

                <div class="modal-body p-0">
                    <!-- Tabs -->
                    <ul class="nav nav-tabs nav-tabs-custom px-4 pt-3" role="tablist" style="border-bottom: 2px solid #e9ecef;">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tabModulos-tab" data-bs-toggle="tab" data-bs-target="#tabModulos" type="button" role="tab">
                                <i class="fa fa-shield-alt me-2" style="color: #6c757d;"></i>Módulos del Sistema
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tabPuestos-tab" data-bs-toggle="tab" data-bs-target="#tabPuestos" type="button" role="tab">
                                <i class="fa fa-briefcase me-2" style="color: #6c757d;"></i>Acceso a Puestos
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content p-4">
                        <!-- TAB MÓDULOS -->
                        <div class="tab-pane fade show active" id="tabModulos" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h6 class="mb-1 fw-bold">Módulos del Sistema</h6>
                                    <small class="text-muted">Gestiona los accesos a los diferentes módulos</small>
                                </div>
                            </div>

                            <div id="modulos-container" style="overflow-y: visible;">
                                <div id="modulos-form"></div>
                            </div>
                        </div>

                        <!-- TAB PUESTOS -->
                        <div class="tab-pane fade" id="tabPuestos" role="tabpanel">
                            <input type="hidden" id="edit_perfil_id">
                            
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h6 class="mb-1 fw-bold">Puestos Disponibles</h6>
                                    <small class="text-muted">Selecciona los puestos a los que tendrá acceso este usuario</small>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="expandirTodosPuestos()">
                                    <i class="fa fa-expand me-1"></i>Expandir Departamentos
                                </button>
                            </div>

                            <div id="puestos-container" style="max-height: 500px; overflow-y: auto;">
                                <div id="puestos-form"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- =======================
      OFFCANVAS - EDITAR PERFIL PERMISOS (LEGACY - MANTENER POR COMPATIBILIDAD)
 ======================== -->
    <div class="offcanvas offcanvas-end" id="offcanvasEditPerfil">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title fw-semibold">
                <i class="bi bi-person-gear me-1"></i> Editar Perfil
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>

        <div class="offcanvas-body p-4">

            <div class="mb-3">
                <label class="form-label fw-semibold">Nombres *</label>
                <input type="text" id="edit_perfil_nombres" class="form-control" readonly>
            </div>

            <ul class="nav nav-tabs mb-3">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabPerfil">
                        <i class="bi bi-person me-1"></i> Acceso a Puestos
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabAccesos">
                        <i class="bi bi-shield-lock me-1"></i> Módulos
                    </button>
                </li>
            </ul>

            <form onsubmit="return false">
                <div class="tab-content">

                    <!-- PERFIL -->
                    <div class="tab-pane fade show active" id="tabPerfil">
                        <input type="hidden" id="edit_perfil_id">

                        <label class="form-label fw-semibold mb-2">
                            Puestos asignados
                        </label>

                        <div id="puestos-container" class="border rounded p-2">
                            <div id="puestos-form"></div>
                        </div>
                    </div>

                    <!-- ACCESOS -->
                    <div class="tab-pane fade" id="tabAccesos">

                        <label class="form-label fw-semibold mb-2">
                            Selecciona los accesos
                        </label>

                        <div id="modulos-container" class="border rounded p-2">
                            <div id="modulos-form"></div>
                        </div>

                    </div>

                </div>
            </form>
        </div>
    </div>


</div>

<!-- =========================
     JS
========================== -->

<script>
  /**
   * ==========================================
   * LLENAR FILTROS DINÁMICAMENTE
   * ==========================================
   * UserRole = Departamento
   * UserPlan = Puesto
   * FilterTransaction = Estatus
   *
   * Datos provenientes de: /CapHum/getUsuarios
   */

  // Variable global para almacenar todos los usuarios
  let usuariosData = [];

  function llenarFiltros() {
    // Llamar a la API para obtener los usuarios/gestores
    http.request({
      endpoint: "/CapHum/getUsuarios",
      onSuccess: (resp) => {

        if (!resp.success || !resp.datos || resp.datos.length === 0) {
          console.warn(' No hay datos disponibles');
          return;
        }

        // Guardar los datos globalmente
        usuariosData = resp.datos;

        // CONJUNTOS para almacenar valores únicos (evita duplicados)
        const departamentos = new Set();
        const puestos = new Set();
        const estatus = new Set();

        // Iterar los datos y extraer valores únicos
        resp.datos.forEach(persona => {
          // DEPARTAMENTO
          if (persona.nombre_departamento && persona.nombre_departamento !== 'Sin departamento') {
            departamentos.add(persona.nombre_departamento);
          }

          // PUESTO
          if (persona.nombre_puesto && persona.nombre_puesto !== 'Sin puesto') {
            puestos.add(persona.nombre_puesto);
          }

          // ESTATUS
          if (persona.estatus) {
            estatus.add(persona.estatus);
          }
        });

        //  FORZAR "Inactivo" en los estatus

        estatus.add('Activo');

        // ==========================================
        // LLENAR SELECT DEPARTAMENTO (UserRole)
        // ==========================================
        const selectDepartamento = document.getElementById('UserRole');
        if (selectDepartamento) {
          const opciones = selectDepartamento.querySelectorAll('option');
          opciones.forEach((opt, index) => {
            if (index > 0) opt.remove();
          });

          departamentos.forEach(dep => {
            const option = document.createElement('option');
            option.value = dep;
            option.textContent = dep;
            selectDepartamento.appendChild(option);
          });

          //  Agregar listener para ACTUALIZAR PUESTOS cuando cambia departamento
          selectDepartamento.addEventListener('change', (e) => {
            actualizarPuestosSegunDepartamento(e.target.value);
            aplicarFiltros();
          });
        }

        // ==========================================
        // LLENAR SELECT PUESTO (UserPlan) - INICIAL
        // ==========================================
        const selectPuesto = document.getElementById('UserPlan');
        if (selectPuesto) {
          const opciones = selectPuesto.querySelectorAll('option');
          opciones.forEach((opt, index) => {
            if (index > 0) opt.remove();
          });

          // Mostrar TODOS los puestos al inicio (sin filtro)
          puestos.forEach(puesto => {
            const option = document.createElement('option');
            option.value = puesto;
            option.textContent = puesto;
            selectPuesto.appendChild(option);
          });

          // Agregar listener para filtrar en tiempo real
          selectPuesto.addEventListener('change', aplicarFiltros);
        }

        // ==========================================
        // LLENAR SELECT ESTATUS (FilterTransaction)
        // ==========================================
        const selectEstatus = document.getElementById('FilterTransaction');
        if (selectEstatus) {
          const opciones = selectEstatus.querySelectorAll('option');
          opciones.forEach((opt, index) => {
            if (index > 0) opt.remove();
          });

          estatus.forEach(est => {
            const option = document.createElement('option');
            option.value = est;
            option.textContent = est;
            selectEstatus.appendChild(option);
          });

          //  Agregar listener para filtrar en tiempo real
          selectEstatus.addEventListener('change', aplicarFiltros);
        }
      },
      onError: (err) => {
        console.error(' Error al cargar datos:', err);
      }
    });
  }

  /**
   * ==========================================
   * ACTUALIZAR PUESTOS SEGÚN DEPARTAMENTO
   * ==========================================
   * Cuando el usuario selecciona un departamento,
   * este filtro muestra solo los puestos de ese departamento
   */
  function actualizarPuestosSegunDepartamento(departamentoSeleccionado) {
    console.log('Actualizando puestos para departamento:', departamentoSeleccionado);

    const selectPuesto = document.getElementById('UserPlan');

    if (!selectPuesto) {
      console.warn('Select UserPlan no encontrado');
      return;
    }

    // Si no hay departamento seleccionado, mostrar TODOS los puestos
    if (!departamentoSeleccionado) {
      console.log('📌 Sin departamento seleccionado, mostrando todos los puestos');

      // Extraer todos los puestos únicos
      const todosPuestos = new Set();
      usuariosData.forEach(persona => {
        if (persona.nombre_puesto && persona.nombre_puesto !== 'Sin puesto') {
          todosPuestos.add(persona.nombre_puesto);
        }
      });

      // Limpiar opciones previas (excepto la primera)
      const opciones = selectPuesto.querySelectorAll('option');
      opciones.forEach((opt, index) => {
        if (index > 0) opt.remove();
      });

      // Agregar todos los puestos
      todosPuestos.forEach(puesto => {
        const option = document.createElement('option');
        option.value = puesto;
        option.textContent = puesto;
        selectPuesto.appendChild(option);
      });

      // Resetear el select
      selectPuesto.value = '';

      console.log('Se muestran todos los puestos:', Array.from(todosPuestos));
      return;
    }

    // Extraer SOLO los puestos del departamento seleccionado
    const puestosDelDepartamento = new Set();
    usuariosData.forEach(persona => {
      if (persona.nombre_departamento === departamentoSeleccionado &&
          persona.nombre_puesto &&
          persona.nombre_puesto !== 'Sin puesto') {
        puestosDelDepartamento.add(persona.nombre_puesto);
      }
    });

    console.log('Puestos encontrados en', departamentoSeleccionado + ':', Array.from(puestosDelDepartamento));

    // Limpiar opciones previas (excepto la primera)
    const opciones = selectPuesto.querySelectorAll('option');
    opciones.forEach((opt, index) => {
      if (index > 0) opt.remove();
    });

    // Agregar nuevos puestos
    puestosDelDepartamento.forEach(puesto => {
      const option = document.createElement('option');
      option.value = puesto;
      option.textContent = puesto;
      selectPuesto.appendChild(option);
    });

    // Resetear el select de puestos
    selectPuesto.value = '';

    console.log('UserPlan actualizado con', puestosDelDepartamento.size, 'puestos de', departamentoSeleccionado);
  }

  /**
   * ==========================================
   * APLICAR FILTROS EN TIEMPO REAL
   * ==========================================
   * Filtra la tabla según los valores seleccionados
   */
  function aplicarFiltros() {
    console.log(' Aplicando filtros...');

    // Obtener valores seleccionados
    const departamentoSeleccionado = document.getElementById('UserRole').value;
    const puestoSeleccionado = document.getElementById('UserPlan').value;
    const estatusSeleccionado = document.getElementById('FilterTransaction').value;

    console.log('Filtros activos:', {
      departamento: departamentoSeleccionado || 'Todos',
      puesto: puestoSeleccionado || 'Todos',
      estatus: estatusSeleccionado || 'Todos'
    });

    // Filtrar datos
    const datosFiltrados = usuariosData.filter(persona => {
      // Filtro DEPARTAMENTO
      if (departamentoSeleccionado && persona.nombre_departamento !== departamentoSeleccionado) {
        return false;
      }

      // Filtro PUESTO
      if (puestoSeleccionado && persona.nombre_puesto !== puestoSeleccionado) {
        return false;
      }

      // Filtro ESTATUS
      if (estatusSeleccionado && persona.estatus !== estatusSeleccionado) {
        return false;
      }

      return true;
    });

    console.log('Resultados filtrados:', datosFiltrados.length, 'registros de', usuariosData.length);

    // Actualizar tabla con datos filtrados
    actualizarTabla(datosFiltrados);
  }

  /**
   * ==========================================
   * ACTUALIZAR TABLA CON DATOS FILTRADOS
   * ==========================================
   */
  function actualizarTabla(datos) {
    // Si estamos usando DataTables
    const tabla = $('#historialUsuarios').DataTable();
    
    if (!tabla) {
      console.warn(' DataTable no inicializado');
      return;
    }

    // Mapear datos como lo hace getUsuarios()
    const datosFormateados = datos.map(p => ({
      nombre: `
          <div class="fw-semibold">
             # ${p.numero_empleado}
          </div>
          <div class="fw-semibold">
              ${p.nombres} ${p.apellidop} ${p.apellidom}
          </div>
          <small class="text-muted d-flex align-items-center gap-1">
              <i class="fa fa-key"></i>
              ${p.usuario}
          </small>
      `.trim(),
      departamento:`
          <small class="text-muted d-flex align-items-center gap-1">
              <i class="fa fa-building"></i>
              ${p.nombre_departamento}
          </small>
          <small class="text-muted d-flex align-items-center gap-1">
              <i class="fa fa-briefcase"></i>
              ${p.nombre_puesto}
          </small>
          <hr>
          <small class="text-muted d-flex align-items-center gap-1">
              <i class="fa fa-user"> </i>Nombre del jefe:<br>
              
          </small><small>${p.nombre_jefe} </small>
      `.trim(),
      estatus: p.estatus,
      acciones: `
       <div class="d-flex flex-wrap gap-1" style="min-width: fit-content;">
           <button class="btn btn-sm btn-primary" onclick="editar(${p.id})" title="Editar">
               <i class="fa fa-edit"></i>
           </button>
           <button class="btn btn-sm btn-info" onclick="cargarDocumentoPersona(this)" data-id-persona="${p.id}" data-nombre="${(p.nombres + ' ' + p.apellidop + ' ' + p.apellidom).replace(/"/g, '&quot;')}" title="Cargar documento">
               <i class="fa fa-file"></i>
           </button>
           <button class="btn btn-sm btn-warning" onclick="registra_ausencia(${p.id})" title="Ausencias">
               <i class="fa fa-person-circle-minus"></i>
           </button>
           <button class="btn btn-sm btn-danger" onclick="baja_gestor(${p.id})" title="Dar de baja">
               <i class="fa fa-user-slash"></i>
           </button>
           <button class="btn btn-sm" style="background-color: #D2D755; color: white;" onclick="edit_perfil(${p.id})" title="Permisos">
               <i class="fa fa-lock" style="color: #007bff;"></i>
           </button>
       </div>`
    }));

    // Limpiar y recargar tabla
    tabla.clear().rows.add(datosFormateados).draw();
  }

  // ==========================================
  // EJECUTAR AL CARGAR LA PÁGINA
  // ==========================================
  document.addEventListener('DOMContentLoaded', () => {
    // Esperar a que DataTable esté listo
    setTimeout(() => {
      llenarFiltros();
    }, 800);
  });

</script>

