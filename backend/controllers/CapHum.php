<?php

namespace Controllers;

use Core\Controller;
use Models\CapHum as CapHumDAO;

class CapHum extends Controller
{
    public function gestion()
    {
        $script = <<<'HTML'
        <script>
        
            const getUsuarios = () => {
            http.request({
                endpoint: "/caphum/getUsuarios",
                onSuccess: (resp) => {
                    // Mapear datos como objeto para usar 'columns.data' en DataTables
                    const datos = resp.datos.map(p => ({
                        nombre: `
                            <div class="fw-semibold">
                                ${p.nombres} ${p.apellidop} ${p.apellidom}
                            </div>
                            <small class="text-muted d-flex align-items-center gap-1">
                                <i class="fa fa-key"></i>
                                ${p.usuario}
                            </small>
                        `.trim(),
                        departamento: p.nombre_departamento,
                        puesto: p.nombre_puesto,
                        estatus: p.estatus,
                       acciones: `
                        <button class="btn btn-sm btn-primary me-1" onclick="editar(${p.id})" title="Editar">
                            <i class="fa fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-info me-1" onclick="verArchivo(${p.id})" title="Ver archivo">
                            <i class="fa fa-file"></i>
                        </button>
                        <button class="btn btn-sm btn-warning me-1" onclick="eliminar(${p.id})" title="Eliminar">
                            <i class="fa fa-person-circle-minus"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="baja_gestor(${p.id})" title="Dar de baja">
                            <i class="fa fa-user-slash"></i>
                        </button>
                        <button class="btn btn-sm me-1" style="background-color: #D2D755; color: white;" onclick="edit_perfil(${p.id})" title="Permisos">
                            <i class="fa fa-lock" style="color: #007bff;"></i>
                        </button>`
                    }));
        
                    // Actualizar DataTable
                    const tabla = $('#historialUsuarios').DataTable();
                    tabla.clear().rows.add(datos).draw();
                }
            });
        };
            
            function editar(id) {
            
                if (!id) {
                    Swal.fire("Error", "ID inválido", "error");
                    return;
                }
            
                resetEditCombos();
            
                fetch('/CapHum/getDetalles', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ idPersona: id })
                })
                .then(res => res.json())
                .then(data => {
            
                    if (!data.success) {
                        Swal.fire("Error", data.mensaje, "error");
                        return;
                    }
            
                    const persona = data.datos;
            
                    // CAMPOS TEXTO
                    document.getElementById("edit_num_empleado").value = persona.numero_empleado ?? '';
                    document.getElementById("edit_id").value = persona.id ?? '';
                    document.getElementById("edit_nombres").value = persona.nombres ?? '';
                    document.getElementById("edit_apellidop").value = persona.apellidop ?? '';
                    document.getElementById("edit_apellidom").value = persona.apellidom ?? '';
                    document.getElementById("edit_telefono").value = persona.telefono ?? '';
                    document.getElementById("edit_usuario").value = persona.user_name ?? '';
                    document.getElementById("edit_contrasena").value = persona.password ?? '';
            
                    // 1️⃣ DEPARTAMENTOS (SIEMPRE)
                    cargarDepartamentosCombo(null, persona.id_departamento);
            
                    // 2️⃣ PUESTOS (SOLO SI HAY DEPARTAMENTO)
                    if (persona.id_departamento) {
                        cargarPuestosCombo(persona.id_departamento, persona.id_puesto);
            
                        // 3️⃣ JEFE (SOLO SI HAY DEPARTAMENTO)
                        cargarComboJefeDirecto(persona.id_departamento, persona.id_jefe);
                    }
            
                    // MOSTRAR OFFCANVAS
                    const offcanvas = new bootstrap.Offcanvas(
                        document.getElementById('offcanvasEditUser')
                    );
                    offcanvas.show();
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire("Error", "No se pudo cargar la información", "error");
                });
            }

            function resetEditCombos() {
            const dep = document.getElementById('edit_departamento_id');
            const puesto = document.getElementById('edit_id_puesto');
            const jefe = document.getElementById('edit_id_jefe');
        
            dep.innerHTML = '<option value="">Seleccione un departamento</option>';
        
            puesto.innerHTML = '<option value="">Seleccione un puesto</option>';
            puesto.disabled = true;
        
            jefe.innerHTML = '<option value="">Seleccione un jefe</option>';
            jefe.disabled = true;
        }
        
            function cargarDepartamentosCombo(id, seleccionado = null) {
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
                
            function cargarPuestosCombo(id_departamento, seleccionado = null) {
                    fetch('/CapHum/getPuestos', {
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
                        select.disabled = false;
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
                
            function cargarComboJefeDirecto(id_departamento, seleccionado = null) {
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
                        select.prop('disabled', false);
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
                
            document.getElementById('edit_departamento_id').addEventListener('change', function () {
                const idDepartamento = this.value;
                const puesto = document.getElementById('edit_id_puesto');
                const jefe = document.getElementById('edit_id_jefe');
            
                puesto.innerHTML = '<option value="">Seleccione un puesto</option>';
                puesto.disabled = true;
            
                jefe.innerHTML = '<option value="">Seleccione un jefe</option>';
                jefe.disabled = true;
            
                if (!idDepartamento) return;
            
                cargarPuestosCombo(idDepartamento);
                cargarComboJefeDirecto(idDepartamento);
            });
                
            function edit_perfil(id) {
                console.log("ID recibido:", id);
                
                 currentPersonaId = id; 
            
                if (!id) {
                    Swal.fire("Error", "ID inválido", "error");
                    return;
                }
            
                fetch('/CapHum/getDetallesPerfil', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ idPersona: id })
                })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        Swal.fire("Error", data.mensaje, "error");
                        return;
                    }
            
                    if (!data.datos) {
                        Swal.fire("Error", "No se encontraron datos de la persona.", "error");
                        return;
                    }
            
                    const persona = data.datos.persona || {};
                    const perfiles = data.datos.perfiles || [];
            
                    const nombreCompleto = [
                        persona.nombres,
                        persona.apellidop,
                        persona.apellidom
                    ].filter(Boolean).join(' ');

                    
                    // CAMPOS TEXTO
                    const inputId = document.getElementById("edit_perfil_id");
                    const inputNombres = document.getElementById("edit_perfil_nombres");
                    if (inputId) inputId.value = persona.id ?? '';
                    if (inputNombres) inputNombres.value = nombreCompleto;
            
             
            
                    // Contenedor de módulos
                    const container = document.getElementById('modulos-form');
                    if (!container) {
                        console.error('No se encontró el contenedor modulos-form');
                        return;
                    }
            
                    container.innerHTML = ''; // Limpiamos
            
                    // Agrupar módulos por pestana
                    const modulosPorPestana = {};
                    perfiles.forEach(mod => {
                        if (!modulosPorPestana[mod.pestana]) modulosPorPestana[mod.pestana] = [];
                        modulosPorPestana[mod.pestana].push(mod);
                    });
            
                    // Crear tabla
                    const table = document.createElement('table');
                    table.classList.add('table', 'table-flush-spacing', 'mb-0', 'border-top');
            
                    const tbody = document.createElement('tbody');
            
                    Object.keys(modulosPorPestana).forEach(pestana => {
                        const mods = modulosPorPestana[pestana];
            
                        mods.forEach(mod => {
                            const tr = document.createElement('tr');
            
                            // Columna con nombre del módulo + descripción
                            const tdName = document.createElement('td');
                            tdName.classList.add('text-nowrap', 'fw-medium', 'text-heading');
            
                            const nombreDiv = document.createElement('div');
                            nombreDiv.innerText = mod.modulo_nombre;
            
                            const descSmall = document.createElement('small');
                            descSmall.classList.add('text-muted', 'd-block', 'fs-7'); // letra más pequeña
                            descSmall.innerText = mod.descripcion ?? '';
            
                            tdName.appendChild(nombreDiv);
                            tdName.appendChild(descSmall);
            
                            // Columna con checkbox
                            const tdCheck = document.createElement('td');
                            const divFlex = document.createElement('div');
                            divFlex.classList.add('d-flex', 'justify-content-end');
            
                            const divCheck = document.createElement('div');
                            divCheck.classList.add('form-check', 'mb-0');
            
                            const checkbox = document.createElement('input');
                            checkbox.type = 'checkbox';
                            checkbox.classList.add('form-check-input');
                            checkbox.id = `modulo_${mod.modulo_id}`;
                            checkbox.name = 'modulos[]';
                            checkbox.value = mod.modulo_id;
                            checkbox.checked = Number(mod.asignado_flag) === 1;
                            
                            // EVENTO NUEVO
                            checkbox.addEventListener('change', function () {
                                onModuloChange(this);
                            });
            
                            const label = document.createElement('label');
                            label.classList.add('form-check-label');
                            label.htmlFor = checkbox.id;
                            label.innerText = 'Asignar';
            
                            divCheck.appendChild(checkbox);
                            divCheck.appendChild(label);
                            divFlex.appendChild(divCheck);
                            tdCheck.appendChild(divFlex);
            
                            tr.appendChild(tdName);
                            tr.appendChild(tdCheck);
                            tbody.appendChild(tr);
                        });
                    });
            
                    table.appendChild(tbody);
                    container.appendChild(table);
            
                    // Inicializar tooltips de Bootstrap (opcional)
                    const tooltipTriggerList = [].slice.call(container.querySelectorAll('[data-bs-toggle="tooltip"]'));
                    tooltipTriggerList.map(function (tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl);
                    });
            
                    // MOSTRAR OFFCANVAS
                    const offcanvasEl = document.getElementById('offcanvasEditPerfil');
                    if (offcanvasEl) {
                        const offcanvas = new bootstrap.Offcanvas(offcanvasEl);
                        offcanvas.show();
                    }
                })
                .catch(err => {
                    console.error('FETCH ERROR:', err);
                    Swal.fire("Error", "No se pudo cargar la información", "error");
                });
            }
            
            function baja_gestor(id) {
                    console.log("ID recibido:", id);
                
                    if (!id) {
                        Swal.fire("Error", "ID inválido", "error");
                        return;
                    }
                
                    fetch('/CapHum/getDetallesPerfil', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            idPersona: id
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (!data.success) {
                            Swal.fire("Error", data.mensaje, "error");
                            return;
                        }
                
                       const persona = data.datos.persona; 
                        // Debug rápido
                        console.log('Persona:', persona);
                
                        document.getElementById("edit_id").value = persona.id;
                        // Concatenar el nombre completo en el <p id="gestor">
                        document.getElementById("gestor").innerHTML = "<strong>Gestor:</strong> " + persona.nombres + " " + persona.apellidop + " " + persona.apellidom;
                        
                        $("#modalBajas").modal("show");
                    })
                    .catch(err => {
                        console.error('FETCH ERROR:', err);
                        Swal.fire("Error", "No se pudo cargar la información", "error");
                    });
        }
        
            function confirmarBaja() {
                const idGestor = document.getElementById("edit_id").value;
                const motivoSelect = document.getElementById("motivoBaja").value;
                const descripcion = document.getElementById("motivoBajaDescripcion").value;
            
                // Validaciones
                if (!motivoSelect) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atención',
                        text: 'Debes seleccionar un motivo de baja.'
                    });
                    return;
                }
            
                if (descripcion.trim() === "") {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atención',
                        text: 'Debes escribir la descripción de la baja.'
                    });
                    return;
                }
            
                // Confirmación antes de enviar
                Swal.fire({
                    title: '¿Confirmar baja?',
                    text: "Esta acción no se puede deshacer.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, dar de baja',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
            
                        // 🧠 Crear FormData
                        const formData = new FormData();
                        formData.append("idGestor", idGestor);
                        formData.append("motivo", motivoSelect);
                        formData.append("descripcion", descripcion);
            
                        // 📎 Archivos múltiples (AQUÍ ESTÁ EL CAMBIO)
                        archivosSeleccionados.forEach(file => {
                            formData.append('archivosPDF[]', file);
                        });
            
                        // 🚀 Enviar al controlador
                        fetch('/CapHum/registrarBaja', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Listo!',
                                    text: 'La baja se registró correctamente.'
                                });
            
                                $("#modalBajas").modal("hide");
            
                                // 🧹 Limpieza opcional
                                archivosSeleccionados = [];
                                document.getElementById("listaArchivos").innerHTML = "";
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: data.message || 'Ocurrió un error al registrar la baja.'
                                });
                            }
                        })
                        .catch(error => {
                            console.error("Error:", error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Ocurrió un error al procesar la baja.'
                            });
                        });
                    }
                });
            }

            
            let currentPersonaId = null;
            function onModuloChange(checkbox) {
                if (!checkbox || currentPersonaId === null) return;
            
                const payload = {
                    idPersona: currentPersonaId,
                    modulo_id: checkbox.value,
                    asignado: checkbox.checked ? 1 : 0
                };
            
                fetch('/CapHum/PerfilCheckBoxEstado', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                })
                .then(res => res.json())
                .then(data => {
            
                    if (!data.success) {
                        // Revertimos el checkbox si falla
                        checkbox.checked = !checkbox.checked;
            
                        Swal.fire("Error", data.mensaje || "No se pudo actualizar", "error");
                        return;
                    }
            
                    // ALERTA SEGÚN ACCIÓN
                    Swal.fire({
                        icon: 'success',
                        title: checkbox.checked 
                            ? 'Asignación correcta'
                            : 'Asignación eliminada',
                        text: checkbox.checked
                            ? 'El módulo fue asignado correctamente'
                            : 'El módulo fue desasignado correctamente',
                        timer: 1600,
                        showConfirmButton: false
                    });
                })
                .catch(err => {
                    console.error(err);
            
                    // Revertimos el checkbox si hay error
                    checkbox.checked = !checkbox.checked;
            
                    Swal.fire("Error", "Error de comunicación con el servidor", "error");
                });
            }
            
            function UpdateGestor() {

                const departamento = document.getElementById("edit_departamento_id").value;
                const puesto       = document.getElementById("edit_id_puesto").value;
                const jefe         = document.getElementById("edit_id_jefe").value;
            
                // 🔴 VALIDACIONES OBLIGATORIAS
                if (!departamento) {
                    Swal.fire("Falta información", "Debes seleccionar un departamento", "warning");
                    return;
                }
            
                if (!puesto) {
                    Swal.fire("Falta información", "Debes seleccionar un puesto", "warning");
                    return;
                }
            
                if (!jefe) {
                    Swal.fire("Falta información", "Debes seleccionar un jefe", "warning");
                    return;
                }
            
                // 🔹 Payload
                const payload = {
                    id: document.getElementById("edit_id").value,
                    nombres: document.getElementById("edit_nombres").value,
                    apellidop: document.getElementById("edit_apellidop").value,
                    apellidom: document.getElementById("edit_apellidom").value,
                    telefono: document.getElementById("edit_telefono").value,
                    departamento_id: departamento,
                    puesto_id: puesto,
                    jefe_id: jefe,
                    usuario: document.getElementById("edit_usuario").value,
                    contrasena: document.getElementById("edit_contrasena").value
                };
            
                fetch('/CapHum/updateGestorF', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        Swal.fire("Error", data.mensaje, "error");
                        return;
                    }
            
                    Swal.fire("Éxito", "Gestor actualizado correctamente", "success");
            
                    bootstrap.Offcanvas.getInstance(
                        document.getElementById('offcanvasEditUser')
                    ).hide();
            
                    // cargarGestores(); // opcional
                });
            }

                
            document.getElementById('add_departamento_id').addEventListener('change', function () {

                const idDepartamento = this.value;
                const selectPuesto = document.getElementById('add_id_puesto');
                const selectJefe   = document.getElementById('add_id_jefe');
                // Reset
                selectPuesto.innerHTML = '<option value="">Seleccione un puesto</option>';
                selectPuesto.disabled = true;
                
                selectJefe.innerHTML = '<option value="">Seleccione un jefe</option>';
                selectJefe.disabled = true;
            
                if (!idDepartamento) return;
            
                fetch('/CapHum/getPuestos', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id_departamento: idDepartamento
                    })
                })
                .then(res => res.json())
                .then(data => {
            
                    if (!data.success) {
                        Swal.fire("Error", data.mensaje, "error");
                        return;
                    }
            
                    data.datos.forEach(puesto => {
                        const option = document.createElement('option');
                        option.value = puesto.id;
                        option.textContent = puesto.nombre;
                        selectPuesto.appendChild(option);
                    });
            
                    selectPuesto.disabled = false;
                })
                .catch(() => {
                    Swal.fire("Error", "No se pudieron cargar los puestos", "error");
                });
            });
            
            document.getElementById('add_id_puesto').addEventListener('change', function () {
                    const idPuesto = this.value;
                    const selectJefe = document.getElementById('add_id_jefe');
                
                    // Reset
                    selectJefe.innerHTML = '<option value="">Seleccione un jefe</option>';
                    selectJefe.disabled = true;
                
                    if (!idPuesto) return;
                
                    fetch('/CapHum/getGestoresPorPuesto', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id_puesto: idPuesto
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                
                        if (!data.success) {
                            Swal.fire("Error", data.mensaje, "error");
                            return;
                        }
                
                        data.datos.forEach(jefe => {
                            const option = document.createElement('option');
                            option.value = jefe.id;
                            option.textContent = jefe.nombre_completo;
                            selectJefe.appendChild(option);
                        });
                
                        selectJefe.disabled = false;
                    })
                    .catch(() => {
                        Swal.fire("Error", "No se pudieron cargar los jefes", "error");
                    });
                }); 
            
            function guardarGestor() {
                const nombres = document.getElementById('add_nombres').value.trim();
                const apellidop = document.getElementById('add_apellidop').value.trim();
                const apellidom = document.getElementById('add_apellidom').value.trim();
                const telefono = document.getElementById('add_telefono').value.trim();
                const id_puesto = document.getElementById('add_id_puesto').value;
                const departamento_id = document.getElementById('add_departamento_id').value;
                const id_jefe = document.getElementById('add_id_jefe').value;
                
                const usuario = document.getElementById('add_usuario').value.trim();
                const contrasena = document.getElementById('add_contrasena').value.trim();
                
            
                // 🔴 Validaciones obligatorias
                if (!nombres) return Swal.fire('Error', 'Los nombres son obligatorios', 'error');
                if (!apellidop) return Swal.fire('Error', 'El apellido paterno es obligatorio', 'error');
                if (!apellidom) return Swal.fire('Error', 'El apellido paterno es obligatorio', 'error');
            
            
                // 🔴 Validar relaciones
                if (!id_puesto) return Swal.fire('Error', 'Debe seleccionar un puesto', 'error');
                if (!departamento_id) return Swal.fire('Error', 'Debe seleccionar un departamento', 'error');
            
                // ⚠️ jefe puede ser null, solo valida si viene
                if (id_jefe && isNaN(id_jefe)) {
                    return Swal.fire('Error', 'Jefe inválido', 'error');
                }
                
                if (!usuario) return Swal.fire('Error', 'Usuario obligatorio', 'error');
                if (!contrasena) return Swal.fire('Error', 'Ingresa una contraseña', 'error');
            
            
                fetch('/CapHum/getInsertarGestor', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        
                        nombres,
                        apellidop,
                        apellidom,
                        telefono,
                        id_puesto,
                        departamento_id,
                        id_jefe: id_jefe || null,
                        usuario,
                        contrasena
                    })
                })
                .then(res => {
                    if (!res.ok) throw new Error('Error HTTP');
                    return res.json();
                })
                .then(data => {
                    if (!data.success) {
                        return Swal.fire('Error', data.mensaje, 'error');
                    }
            
                    Swal.fire('Éxito', data.mensaje, 'success')
                        .then(() => location.reload());
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire('Error', 'No se pudo registrar el gestor', 'error');
                });
            }
            
            $(document).ready(() => {
            // Inicializar DataTable con columnas explícitas
            $(document).ready(() => {
                configuraTabla("#historialUsuarios", { registrosPorPagina: 6 });
                getUsuarios();
            });
        
            getUsuarios();
        });
            
            
            let archivosSeleccionados = [];

            document.getElementById("archivoPDF").addEventListener("change", function (e) {
                const nuevosArchivos = Array.from(e.target.files);
            
                nuevosArchivos.forEach(file => {
                    if (file.type !== "application/pdf") return;
            
                    archivosSeleccionados.push(file);
                });
            
                renderArchivos();
                this.value = ""; // reset input
            });
            
            function renderArchivos() {
                const lista = document.getElementById("listaArchivos");
                lista.innerHTML = "";
            
                archivosSeleccionados.forEach((file, index) => {
                    const li = document.createElement("li");
                    li.className = "list-group-item d-flex justify-content-between align-items-center";
            
                    li.innerHTML = `
                        <div>
                            <i class="bi bi-file-earmark-pdf text-danger me-2"></i>
                            ${file.name}
                        </div>
                        <div>
                            <i class="bi bi-check-circle-fill text-success me-3"></i>
                            <i class="bi bi-x-circle-fill text-danger"
                               style="cursor:pointer"
                               onclick="eliminarArchivo(${index})"></i>
                        </div>
                    `;
            
                    lista.appendChild(li);
                });
            }
            
            function eliminarArchivo(index) {
                archivosSeleccionados.splice(index, 1);
                renderArchivos();
            }

            
        </script>
        HTML;
        $departamento = CapHumDAO::getConsultaDepartamentoGestor($_SESSION['usuario_id']);

        self::set("titulo", "Consulta Usuarios");
        self::set("script", $script);
        self::set("departamento", $departamento);
        self::render("all_gestores");
    }
    public function getUsuarios()
    {
        $resultado = CapHumDAO::getConsultaGestoresAll($_SESSION['usuario_id']);
        $usuarios = $resultado['datos'] ?? [];


        // Preparar array compatible con frontend
        $datos = array_map(function($p) {
            return [
                'id' => $p['id'] ?? '',
                'nombres' => $p['nombres'] ?? '',
                'apellidop' => $p['apellidop'] ?? '',
                'apellidom' => $p['apellidom'] ?? '',
                'nombre_departamento' => $p['nombre_departamento'] ?? '',
                'nombre_puesto' => $p['nombre_puesto'] ?? '',
                'estatus' => $p['estatus'] ?? '',
                'usuario' => $p['usuario'] ?? '',
            ];
        }, $usuarios);

        // Usar respuestaJSON para enviar el JSON correctamente
        self::respuestaJSON([
            'success' => true,
            'datos' => $datos
        ]);
    }

    public function getDetalles()
    {
        $input = json_decode(file_get_contents("php://input"), true);
        $idPersona = $input['idPersona'] ?? null;

        if (!$idPersona) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'ID de persona no recibido'
            ]);
            return;
        }

        $detalles = CapHumDAO::getPersonaDetalle($idPersona);

        self::respuestaJSON($detalles);
    }

    public function getDetallesPerfil()
    {
        $input = json_decode(file_get_contents("php://input"), true);
        $idPersona = $input['idPersona'] ?? null;

        if (!$idPersona) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'ID de persona no recibido'
            ]);
            return;
        }

        $detalles = CapHumDAO::getPersonaDetallePerfil($idPersona);

        self::respuestaJSON($detalles);
    }

    public function getDepartamento()
    {
        $input = json_decode(file_get_contents("php://input"), true);
        $id = $input['id'] ?? null;


        self::respuestaJSON(
            CapHumDAO::getComboDepartamentos($id)
        );
    }

    public function PerfilCheckBoxEstado()
    {
        $input = json_decode(file_get_contents("php://input"), true);

        $idPersona = $input['idPersona'] ?? null;
        $moduloId  = $input['modulo_id'] ?? null;
        $asignado  = $input['asignado'] ?? null;

        if (!$idPersona || !$moduloId || $asignado === null) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'Datos incompletos'
            ]);
            return;
        }

        try {
            $resultado = CapHumDAO::actualizarModuloPerfil(
                $idPersona,
                $moduloId,
                (int)$asignado
            );

            self::respuestaJSON([
                'success' => true,
                'mensaje' => $asignado
                    ? 'Módulo asignado correctamente'
                    : 'Módulo eliminado correctamente'
            ]);
        } catch (Exception $e) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'Error al actualizar el módulo'
            ]);
        }
    }

    public function getPuestos()
    {
        $input = json_decode(file_get_contents("php://input"), true);
        $id = $input['id_departamento'] ?? null;

        if (empty($id)) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'Id de departamento requerido'
            ]);
            return;
        }

        // EL DAO YA REGRESA success, mensaje y datos
        $resultado = CapHumDAO::getConsultaPuestos($id);

        self::respuestaJSON($resultado);
    }

    public function getJefeDirecto()
    {
        $input = json_decode(file_get_contents("php://input"), true);
        $idDepartamento = $input['id_departamento'] ?? null;

        if (!$idDepartamento) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'ID de persona no recibido'
            ]);
            return;
        }

        $detalles = CapHumDAO::getConsultaJefe($idDepartamento);

        self::respuestaJSON($detalles);
    }

    public function updateGestorF()
    {
        session_start(); // 🔴 IMPORTANTE
        header('Content-Type: application/json; charset=utf-8');

        // Leer JSON
        $input = json_decode(file_get_contents("php://input"), true);

        if (!$input || empty($input['id'])) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Datos incompletos'
            ]);
            exit; // 🔴 CLAVE
        }

        $resultado = CapHumDAO::UpdatePersona($input);

        echo json_encode($resultado);
        exit; // 🔴 CLAVE
    }

    ///////
    public function Organigrama()
    {
        $script = <<<HTML
            <script>
               
            </script>
        HTML;

        $departamentos = CapHumDAO::getConsultaDepartamentoGestor($_SESSION['usuario_id']);


        $getDepartamentos = '<option disabled selected>Seleeccione una opción</option>';

        if (!empty($departamentos['datos'])) {
            foreach ($departamentos['datos'] as $val2) {
                $getDepartamentos .= '<option value="' . $val2['id'] . '">' . htmlspecialchars($val2['nombre'], ENT_QUOTES, 'UTF-8') . '</option>';
            }
        }

        self::set("titulo", "Organigrama");
        self::set("script", $script);
        self::set("Departamentos", $getDepartamentos);
        self::render("organigrama");
    }

    public function getPersonasOrganigrama()
    {
        // Obtener parámetro enviado por POST (o GET según tu setup)
        $idDepartamento = $_POST['idDepartamento'] ?? null;

        // Pasar el parámetro a DAO
        $puestos = CapHumDAO::getPersonasOrganigrama($idDepartamento, $_SESSION['usuario_id']);

        self::respuestaJSON($puestos);
    }

    public function nivelJerarquicoColaborador($persona_id)
    {
        // 1️⃣ Obtener organigrama desde la DAO
        $personas = CapHumDAO::getConsultaPersonasJerarquia($persona_id);
        $organigramaJson = $personas["datos"][0]["organigrama_json"];
        $organigrama = json_decode($organigramaJson, true);

        // 2️⃣ Construir filas para el OrgChart
        $rows = [];

        // Raíz del organigrama
        $rows[] = [
            "id"     => (string)$organigrama["id_jefe"],    // ID como string
            "nombre" => $organigrama["nombre_jefe"],        // Nombre
            "jefe"   => null                                // Sin jefe
        ];

        // Subordinados de la raíz
        if (!empty($organigrama["subordinados"])) {
            foreach ($organigrama["subordinados"] as $sub) {
                $this->recorrerArbol($sub, $rows, (string)$organigrama["id_jefe"]);
            }
        }

        // 3️⃣ Devolver JSON
        header('Content-Type: application/json');
        echo json_encode([
            "success" => true,
            "rows"    => $rows
        ]);
        exit;
    }

    private function recorrerArbol($nodo, &$rows, $jefeId = null) {
        $rows[] = [
            "id"     => (string)$nodo["id"],
            "nombre" => $nodo["nombre"],
            "jefe"   => $jefeId
        ];

        if (!empty($nodo["subordinados"])) {
            foreach ($nodo["subordinados"] as $sub) {
                $this->recorrerArbol($sub, $rows, (string)$nodo["id"]);
            }
        }
    }

    public static function getGestoresPorPuesto()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['id_puesto'])) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Puesto inválido'
            ]);
            return;
        }

        $resp = CapHumDAO::getConsultaGestoresPorPuesto($data['id_puesto']);

        if (!$resp['success']) {
            echo json_encode($resp);
            return;
        }

        echo json_encode([
            'success' => true,
            'datos'   => $resp['datos']
        ]);
    }

    public function getInsertarGestor()
    {
        // Obtener el JSON enviado desde fetch
        $inputJSON = file_get_contents('php://input');
        $data = json_decode($inputJSON, true);

        // Validaciones básicas
        $requiredFields = ['nombres', 'apellidop', 'apellidom', 'telefono', 'id_puesto', 'departamento_id', 'usuario', 'contrasena'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                echo json_encode([
                    'success' => false,
                    'mensaje' => "El campo $field es obligatorio"
                ]);
                return;
            }
        }

        // Preparar datos
        $data['contrasena'] = $data['contrasena'];
        $data['id_jefe'] = isset($data['id_jefe']) ? $data['id_jefe'] : null;

        // Llamar al DAO
        $inserted = CapHumDAO::insertPersona($data);

        if ($inserted['success']) {
            echo json_encode(['success' => true, 'mensaje' => 'Gestor insertado correctamente']);
        } else {
            echo json_encode(['success' => false, 'mensaje' => 'Error al insertar gestor', 'error' => $inserted['error']]);
        }
    }
    public function registrarBaja()
    {
        // ⚠️ Al usar FormData NO se usa php://input
        $idGestor    = $_POST['idGestor'] ?? null;
        $motivo      = $_POST['motivo'] ?? null;
        $descripcion = $_POST['descripcion'] ?? null;

        // ✅ Validaciones obligatorias
        if (empty($idGestor)) {
            echo json_encode([
                'success' => false,
                'message' => 'ID del gestor es obligatorio'
            ]);
            return;
        }

        if (empty($motivo)) {
            echo json_encode([
                'success' => false,
                'message' => 'El motivo de baja es obligatorio'
            ]);
            return;
        }

        if (empty(trim($descripcion))) {
            echo json_encode([
                'success' => false,
                'message' => 'La descripción de la baja es obligatoria'
            ]);
            return;
        }

        // 📎 MANEJO DE MÚLTIPLES PDFs
        $rutasPDF = [];

        if (!empty($_FILES['archivosPDF']['name'][0])) {

            $directorio = __DIR__ . '/../uploads/bajas/';
            if (!is_dir($directorio)) {
                mkdir($directorio, 0777, true);
            }

            foreach ($_FILES['archivosPDF']['tmp_name'] as $i => $tmp) {

                if ($_FILES['archivosPDF']['error'][$i] !== UPLOAD_ERR_OK) {
                    continue;
                }

                $nombreOrig = $_FILES['archivosPDF']['name'][$i];
                $extension  = strtolower(pathinfo($nombreOrig, PATHINFO_EXTENSION));

                if ($extension !== 'pdf') {
                    continue;
                }

                $nombreFinal = 'baja_' . $idGestor . '_' . time() . '_' . $i . '.pdf';
                $rutaFinal   = $directorio . $nombreFinal;

                if (move_uploaded_file($tmp, $rutaFinal)) {
                    $rutasPDF[] = $nombreFinal; // 👈 guardamos solo el nombre
                }
            }
        }

        // 📦 Preparar datos para el DAO
        $data = [
            'id_gestor'   => $idGestor,
            'motivo'      => $motivo,
            'descripcion' => $descripcion,
            'archivos'    => $rutasPDF, // 👈 ahora es un arreglo
            'fecha_baja'  => date('Y-m-d H:i:s')
        ];

        // 🧠 Llamar al modelo / DAO
        $resultado = CapHumDAO::registrarBajaGestor($data);

        if ($resultado['success']) {
            echo json_encode([
                'success' => true,
                'message' => 'La baja se registró correctamente'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al registrar la baja',
                'error'   => $resultado['error'] ?? null
            ]);
        }
    }

    public function getPuestosDepartamento()
    {
        $input = json_decode(file_get_contents("php://input"), true);
        $id = $input['id_departamento'] ?? null;

        if (!$id) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'Tipo de departamento requerido'
            ]);
            return;
        }

        self::respuestaJSON(
            CapHumDAO::getConsultaDepartamentoGestorOrganigrama($id)
        );
    }





}
