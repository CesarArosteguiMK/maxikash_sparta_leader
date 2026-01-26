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
                    <input type="text" id="add_nombres" class="form-control">
                </div>

                <div class="mb-2">
                    <label class="form-label">Apellido Paterno *</label>
                    <input type="text" id="add_apellidop" class="form-control">
                </div>

                <div class="mb-2">
                    <label class="form-label">Apellido Materno *</label>
                    <input type="text" id="add_apellidom" class="form-control">
                </div>

                <div class="mb-2">
                    <label class="form-label">Teléfono *</label>
                    <input type="text" id="add_telefono" class="form-control phone-mask">
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

                <div class="mb-2">
                    <label class="form-label">Usuario *</label>
                    <input type="text" id="add_usuario" class="form-control">
                </div>

                <div class="mb-7">
                    <label class="form-label">Contraseña *</label>
                    <input type="text" id="add_contrasena" class="form-control">
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

                    <input
                            class="form-control"
                            type="file"
                            id="archivoPDF"
                            accept=".pdf"
                            multiple
                    >
                    <small class="text-muted">
                        Puedes subir múltiples archivos PDF.
                    </small>
                    <ul class="list-group mt-2" id="listaArchivos"></ul>
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
                    <input type="text" id="edit_nombres" class="form-control">
                </div>

                <div class="mb-2">
                    <label class="form-label">Apellido Paterno *</label>
                    <input type="text" id="edit_apellidop" class="form-control">
                </div>

                <div class="mb-2">
                    <label class="form-label">Apellido Materno *</label>
                    <input type="text" id="edit_apellidom" class="form-control">
                </div>

                <div class="mb-2">
                    <label class="form-label">Teléfono *</label>
                    <input type="text" id="edit_telefono" class="form-control phone-mask">
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
                    <input type="text" id="edit_contrasena" class="form-control">
                </div>

                <button type="button" class="btn btn-primary me-3" onclick="UpdateGestor()" > Guardar </button>
                <button type="button" class="btn btn-label-danger" data-bs-dismiss="offcanvas" > Cancelar </button>
            </form>
        </div>
    </div>

    <!-- =======================
      OFFCANVAS - EDITAR PERFIL PERMISOS
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
                <input type="text"
                       id="edit_perfil_nombres"
                       class="form-control"
                       readonly>
            </div>

            <!-- Tabs -->
            <ul class="nav nav-tabs mb-3" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active"
                            data-bs-toggle="tab"
                            data-bs-target="#tabPerfil"
                            type="button"
                            role="tab">
                        <i class="bi bi-person me-1"></i> Perfil
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link"
                            data-bs-toggle="tab"
                            data-bs-target="#tabAccesos"
                            type="button"
                            role="tab">
                        <i class="bi bi-shield-lock me-1"></i> Accesos
                    </button>
                </li>
            </ul>


            <form id="editNewUserFormPerfil" onsubmit="return false">

                <div class="tab-content">

                    <!-- TAB PERFIL -->
                    <div class="tab-pane fade show active" id="tabPerfil" role="tabpanel">

                        <div class="mb-3 d-none">
                            <label class="form-label">Id Empleado *</label>
                            <input type="text"
                                   id="edit_perfil_id"
                                   class="form-control"
                                   disabled>
                        </div>



                    </div>

                    <!-- TAB ACCESOS -->
                    <div class="tab-pane fade" id="tabAccesos" role="tabpanel">

                        <label class="form-label fw-semibold mb-2">
                            Selecciona los accesos *
                        </label>

                        <div id="modulos-container" class="border rounded p-3">
                            <div id="modulos-form" class="row g-2">
                                <!-- Checkboxes dinámicos -->
                            </div>
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
    console.log(' Iniciando función llenarFiltros()');

    // Llamar a la API para obtener los usuarios/gestores
    http.request({
      endpoint: "/CapHum/getUsuarios",
      onSuccess: (resp) => {
        console.log('✓ Respuesta de /CapHum/getUsuarios:', resp);

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

        console.log(' Datos extraídos:', {
          departamentos: Array.from(departamentos),
          puestos: Array.from(puestos),
          estatus: Array.from(estatus)
        });

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

          console.log('UserRole (DEPARTAMENTO) llenado:', Array.from(departamentos));
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

          console.log('UserPlan (PUESTO) llenado:', Array.from(puestos));
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

          console.log(' FilterTransaction (ESTATUS) llenado:', Array.from(estatus));
        }

        console.log(' Filtros inicializados correctamente');
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
       <button class="btn btn-sm btn-primary me-1" onclick="editar(${p.id})" title="Editar">
           <i class="fa fa-edit"></i>
       </button>
       <button class="btn btn-sm btn-info me-1" onclick="verArchivo(${p.id})" title="Ver archivo">
           <i class="fa fa-file"></i>
       </button>
       <button class="btn btn-sm btn-warning me-1" onclick="registra_ausencia(${p.id})" title="Ausencias">
           <i class="fa fa-person-circle-minus"></i>
       </button>
       <button class="btn btn-sm btn-danger" onclick="baja_gestor(${p.id})" title="Dar de baja">
           <i class="fa fa-user-slash"></i>
       </button>
       <button class="btn btn-sm me-1" style="background-color: #D2D755; color: white;" onclick="edit_perfil(${p.id})" title="Permisos">
           <i class="fa fa-lock" style="color: #007bff;"></i>
       </button>`
    }));

    // Limpiar y recargar tabla
    tabla.clear().rows.add(datosFormateados).draw();

    console.log(' Tabla actualizada con', datosFormateados.length, 'registros');
  }

  // ==========================================
  // EJECUTAR AL CARGAR LA PÁGINA
  // ==========================================
  document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM cargado, esperando 800ms...');
    
    // Esperar a que DataTable esté listo
    setTimeout(() => {
      llenarFiltros();
    }, 800);
  });

</script>

