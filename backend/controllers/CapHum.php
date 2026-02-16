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
                    // ==========================================
                    // CONSOLIDAR USUARIOS CON MÚLTIPLES PUESTOS
                    // ==========================================
                    const usuariosMap = new Map();
                    
                    resp.datos.forEach(usuario => {
                        const id = usuario.id;
                        
                        if (!usuariosMap.has(id)) {
                            // Primera vez que vemos este usuario
                            usuariosMap.set(id, {
                                ...usuario,
                                puestos: [{
                                    id_puesto: usuario.id_puesto,
                                    nombre_puesto: usuario.nombre_puesto,
                                    nombre_departamento: usuario.nombre_departamento,
                                    id_departamento: usuario.id_departamento
                                }]
                            });
                        } else {
                            // Ya existe, agregar nuevo puesto
                            const usuarioExistente = usuariosMap.get(id);
                            const puestoExiste = usuarioExistente.puestos.some(p => 
                                p.id_puesto === usuario.id_puesto && 
                                p.nombre_departamento === usuario.nombre_departamento
                            );
                            
                            if (!puestoExiste) {
                                usuarioExistente.puestos.push({
                                    id_puesto: usuario.id_puesto,
                                    nombre_puesto: usuario.nombre_puesto,
                                    nombre_departamento: usuario.nombre_departamento,
                                    id_departamento: usuario.id_departamento
                                });
                            }
                        }
                    });
                    
                    const usuariosConsolidados = Array.from(usuariosMap.values());
                    
                    // Guardar en variable global para otros usos
                    window.usuariosData = usuariosConsolidados;
                    
                    console.log('📊 Usuarios consolidados:', usuariosConsolidados.length, 'de', resp.datos.length, 'registros');
                    
                    // ==========================================
                    // MAPEAR DATOS CON SOPORTE PARA MÚLTIPLES PUESTOS
                    // ==========================================
                    const datos = usuariosConsolidados.map(p => {
                        const nombreCompleto = [p.nombres, p.segundo_nombre, p.apellidop, p.apellidom].filter(x => x).join(' ');
                        const tienePuestos = p.puestos && p.puestos.length > 1;
                        
                        // Generar badges para múltiples puestos con departamentos
                        let puestosHTML = '';
                        if (tienePuestos) {
                            puestosHTML = '<div class="d-flex flex-column gap-2">';
                            p.puestos.forEach((puesto, index) => {
                                const colorBadge = obtenerColorDepartamento(puesto.nombre_departamento);
                                puestosHTML += `
                                    <div class="d-flex flex-column" style="gap: 0.25rem;">
                                        <small class="text-muted fw-semibold" style="font-size: 0.7rem;">
                                            <i class="fa fa-building me-1"></i>${puesto.nombre_departamento}
                                        </small>
                                        <span class="badge badge-puesto-multiple" 
                                              style="background: ${colorBadge}; font-size: 0.75rem; width: fit-content;" 
                                              title="${puesto.nombre_puesto}">
                                            <i class="fa fa-briefcase me-1"></i>${puesto.nombre_puesto}
                                        </span>
                                    </div>
                                `;
                            });
                            puestosHTML += '</div>';
                        } else {
                            // Un solo puesto
                            puestosHTML = `
                                <small class="text-muted d-flex align-items-center gap-1">
                                    <i class="fa fa-building"></i>
                                    ${p.nombre_departamento || 'Sin departamento'}
                                </small>
                                <small class="text-muted d-flex align-items-center gap-1">
                                    <i class="fa fa-briefcase"></i>
                                    ${p.nombre_puesto || 'Sin puesto'}
                                </small>
                            `;
                        }
                        
                        return {
                            nombre: `
                                <div class="fw-semibold">
                                   # ${p.numero_empleado}
                                </div>
                                <div class="fw-semibold">
                                    ${nombreCompleto}
                                </div>
                                <small class="text-muted d-flex align-items-center gap-1">
                                    <i class="fa fa-key"></i>
                                    ${p.usuario}
                                </small>
                                ${tienePuestos ? '<span class="badge bg-info mt-1" style="font-size: 0.65rem;"><i class="fa fa-layer-group me-1"></i>Múltiples puestos</span>' : ''}
                            `.trim(),
                            departamento: `
                                ${puestosHTML}
                                <hr class="my-2">
                                <small class="text-muted d-flex align-items-center gap-1">
                                    <i class="fa fa-user"></i>Jefe: <strong class="ms-1">${p.nombre_jefe || 'Sin jefe'}</strong>
                                </small>
                            `.trim(),
                            estatus: p.estatus,
                            acciones: (() => {
                                const puedeEditar = window.puedeEditarTodos;
                                return `
                                <div class="d-flex flex-wrap gap-1" style="min-width: fit-content;">
                                    ${puedeEditar
                                        ? `<button class="btn btn-sm btn-primary ${tienePuestos ? 'btn-with-indicator' : ''}" onclick="editar(${p.id})" title="${tienePuestos ? 'Editar (Múltiples puestos)' : 'Editar'}">
                                        ${tienePuestos ? '<span class="indicator-multiples-puestos">' + p.puestos.length + '</span>' : ''}
                                        <i class="fa fa-edit"></i>
                                    </button>`
                                        : `<button class="btn btn-sm btn-outline-secondary" onclick="visualizar(${p.id})" title="Visualizar">
                                        <i class="fa fa-eye"></i>
                                    </button>`
                                    }
                                    <button class="btn btn-sm btn-info" onclick="cargarDocumentoPersona(this)" data-id-persona="${p.id}" data-nombre="${nombreCompleto.replace(/"/g, '&quot;')}" title="Cargar documento">
                                        <i class="fa fa-file"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning" onclick="registra_ausencia(${p.id})" title="Ausencias">
                                        <i class="fa fa-person-circle-minus"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="baja_gestor(${p.id})" title="Dar de baja">
                                        <i class="fa fa-user-slash"></i>
                                    </button>
                                    <button class="btn btn-sm" style="background-color: #D2D755; color: white;" onclick="edit_perfil(${p.id})" title="${tienePuestos ? 'Permisos (Gestionar múltiples puestos)' : 'Permisos'}">
                                        <i class="fa fa-lock" style="color: #007bff;"></i>
                                    </button>
                                </div>`;
                            })()
                        };
                    });
        
                    // Actualizar DataTable
                    const tabla = $('#historialUsuarios').DataTable();
                    tabla.clear().rows.add(datos).draw();
                }
            });
        };
        
            const getBajas = () => {
                // Preparar parámetros con filtro de fecha si existe
                const params = {};
                if (rangoFechasBajas) {
                    params.fecha_inicio = rangoFechasBajas.inicio;
                    params.fecha_fin = rangoFechasBajas.fin;
                }
                
                http.request({
                    endpoint: "/caphum/getBajas",
                    data: params,
                    metodo: "POST",
                    onSuccess: (resp) => {
                        // Mapear datos con el nuevo formato de columnas
                        const datos = resp.datos.map(p => {
                            const nombreCompleto = [p.nombres, p.segundo_nombre, p.apellidop, p.apellidom].filter(x => x).join(' ');
                            
                            return {
                                nombres: `
                                    <div class="fw-semibold d-flex align-items-center gap-2">
                                        <i class="fa fa-hashtag" style="font-size: 0.85em; color: #333;"></i>
                                        <span>${p.external_id ?? ''}</span>
                                    </div>
                                    <div class="fw-semibold d-flex align-items-center gap-2 mt-2">
                                        <i class="fa fa-user" style="font-size: 0.85em; color: #333;"></i>
                                        <span>${nombreCompleto}</span>
                                    </div>
                                    <div class="text-muted small d-flex align-items-center gap-2 mt-1">
                                        <i class="fa fa-id-card" style="font-size: 0.85em; color: #666;"></i>
                                        <span># ${p.numero_empleado ?? ''}</span>
                                    </div>
                                `.trim(),
                                puesto: `
                                    <div class="text-muted small d-flex align-items-center gap-2">
                                        <i class="fa fa-building" style="font-size: 0.85em; color: #333;"></i>
                                        <span>${p.departamento ?? 'N/A'}</span>
                                    </div>
                                    <div class="text-muted small d-flex align-items-center gap-2 mt-1">
                                        <i class="fa fa-briefcase" style="font-size: 0.85em; color: #333;"></i>
                                        <span>${p.nombre_puesto ?? 'N/A'}</span>
                                    </div>
                                `.trim(),
                                estatus: `
                                    <div class="fw-semibold text-danger d-flex align-items-center gap-2">
                                        <i class="fa fa-ban"></i>
                                        <span>Baja</span>
                                    </div>
                                    <div class="text-muted small d-flex align-items-center gap-2 mt-1">
                                        <i class="fa fa-calendar" style="font-size: 0.85em; color: #666;"></i>
                                        <span>${p.fecha_baja ? new Date(p.fecha_baja).toLocaleDateString('es-MX') : 'N/A'}</span>
                                    </div>
                                    <div class="text-muted small d-flex align-items-center gap-2 mt-1">
                                        <i class="fa fa-file-alt" style="font-size: 0.85em; color: #666;"></i>
                                        <span>Registro: ${p.registro_baja ?? 'N/A'}</span>
                                    </div>
                                `.trim(),
                                motivos: `
                                    <div class="fw-semibold d-flex align-items-center gap-2">
                                        <i class="fa fa-exclamation-triangle" style="font-size: 0.85em; color: #333;"></i>
                                        <span>${p.motivo ?? 'N/A'}</span>
                                    </div>
                                    <div class="text-muted small d-flex align-items-center gap-2 mt-1">
                                        <i class="fa fa-clipboard" style="font-size: 0.85em; color: #666;"></i>
                                        <span>${p.descripcion ?? 'Sin descripción'}</span>
                                    </div>
                                `.trim(),
                                usuario: p.user_name ?? 'N/A',
                                acciones: `
                                    <button class="btn btn-sm btn-primary" onclick="editarBaja(${p.registro_baja ?? ''})" title="Editar">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                `.trim()
                            };
                        });
            
                        // Actualizar DataTable
                        const tabla = $('#historialUsuarios').DataTable();
                        tabla.clear().rows.add(datos).draw();
                    },
                    onError: (err) => {
                        console.error('Error al cargar bajas:', err);
                        Swal.fire("Error", "No se pudieron cargar las bajas", "error");
                    }
                });
            };
            
            function setModoEdicion() {
                const rowContrasena = document.getElementById('edit_row_contrasena');
                const titulo = document.getElementById('offcanvasEditUserTitle');
                const btnGuardar = document.getElementById('edit_btn_guardar');
                const form = document.getElementById('editNewUserForm');
                if (rowContrasena) rowContrasena.style.display = '';
                if (titulo) titulo.textContent = 'Editar Gestor';
                if (btnGuardar) btnGuardar.style.display = '';
                if (form) {
                    form.querySelectorAll('input, select, button[type="button"]').forEach(el => { el.disabled = false; });
                }
            }
            function setModoVisualizar() {
                const rowContrasena = document.getElementById('edit_row_contrasena');
                const titulo = document.getElementById('offcanvasEditUserTitle');
                const btnGuardar = document.getElementById('edit_btn_guardar');
                const form = document.getElementById('editNewUserForm');
                if (rowContrasena) rowContrasena.style.display = 'none';
                if (titulo) titulo.textContent = 'Visualizar Gestor';
                if (btnGuardar) btnGuardar.style.display = 'none';
                if (form) {
                    form.querySelectorAll('input, select').forEach(el => { el.disabled = true; });
                }
            }
            function editar(id) {
            
                if (!id) {
                    Swal.fire("Error", "ID inválido", "error");
                    return;
                }
            
                resetEditCombos();
                setModoEdicion();
            
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
                    
                    // Buscar si este usuario tiene múltiples puestos
                    const usuarioData = usuariosData.find(u => u.id === id);
                    const tienePuestos = usuarioData && usuarioData.puestos && usuarioData.puestos.length > 1;
                    
                    // Mostrar/ocultar alerta y lista de múltiples puestos
                    const alertaMultiples = document.getElementById('edit_alerta_multiples_puestos');
                    const contenedorMultiples = document.getElementById('edit_contenedor_multiples_puestos');
                    const labelPrincipal = document.getElementById('edit_label_principal');
                    const labelPuestoPrincipal = document.getElementById('edit_label_puesto_principal');
                    
                    if (tienePuestos) {
                        // Mostrar alerta y contenedor
                        if (alertaMultiples) alertaMultiples.classList.remove('d-none');
                        if (contenedorMultiples) contenedorMultiples.classList.remove('d-none');
                        if (labelPrincipal) labelPrincipal.style.display = 'inline-block';
                        if (labelPuestoPrincipal) labelPuestoPrincipal.style.display = 'inline-block';
                        
                        // Cargar gestión completa de puestos (nueva funcionalidad)
                        if (typeof cargarPuestosUsuario === 'function') {
                            cargarPuestosUsuario(id);
                        } else {
                            // Fallback: Generar lista simple de puestos
                            const listaPuestos = document.getElementById('edit_lista_puestos');
                            if (listaPuestos) {
                                let html = '<div class="d-flex flex-column gap-2">';
                                usuarioData.puestos.forEach((puesto, index) => {
                                    const colorBadge = obtenerColorDepartamento(puesto.nombre_departamento);
                                    const esPrincipal = index === 0;
                                    html += `
                                        <div class="d-flex align-items-center justify-content-between p-2 rounded" style="background: white;">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="fa fa-briefcase text-muted"></i>
                                                <div>
                                                    <div class="fw-semibold">${puesto.nombre_puesto}</div>
                                                    <small class="text-muted">${puesto.nombre_departamento}</small>
                                                </div>
                                            </div>
                                            ${esPrincipal ? '<span class="badge bg-primary">Principal</span>' : '<span class="badge bg-secondary">Secundario</span>'}
                                        </div>
                                    `;
                                });
                                html += '</div>';
                                listaPuestos.innerHTML = html;
                            }
                        }
                    } else {
                        // Ocultar alerta y contenedor
                        if (alertaMultiples) alertaMultiples.classList.add('d-none');
                        if (contenedorMultiples) contenedorMultiples.classList.add('d-none');
                        if (labelPrincipal) labelPrincipal.style.display = 'none';
                        if (labelPuestoPrincipal) labelPuestoPrincipal.style.display = 'none';
                    }
            
                    // CAMPOS TEXTO
                    document.getElementById("edit_num_empleado").value = persona.numero_empleado ?? '';
                    document.getElementById("edit_id").value = persona.id ?? '';
                    document.getElementById("edit_nombres").value = persona.nombres ?? '';
                    document.getElementById("edit_segundo_nombre").value = persona.segundo_nombre ?? '';
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
            
                        // 3️⃣ JEFE (SOLO SI HAY DEPARTAMENTO; con puesto para Legal/Abogado etc.)
                        cargarComboJefeDirecto(persona.id_departamento, persona.id_jefe, persona.id_puesto);
                    }
            
                    // 4️⃣ LEGIÓN
                    const checkLegion = document.getElementById('edit_asignar_legion');
                    const divLegion = document.getElementById('edit_div_select_legion');
                    const selectLegion = document.getElementById('edit_id_legion');
                    const idLegion = persona.id_legion ? String(persona.id_legion) : '';
                    checkLegion.checked = !!idLegion;
                    selectLegion.value = idLegion || '';
                    divLegion.style.display = checkLegion.checked ? 'block' : 'none';
            
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

            function visualizar(id) {
                if (!id) {
                    Swal.fire("Error", "ID inválido", "error");
                    return;
                }
                resetEditCombos();
                setModoVisualizar();
                fetch('/CapHum/getDetalles', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ idPersona: id })
                })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        Swal.fire("Error", data.mensaje, "error");
                        return;
                    }
                    const persona = data.datos;
                    document.getElementById("edit_num_empleado").value = persona.numero_empleado ?? '';
                    document.getElementById("edit_id").value = persona.id ?? '';
                    document.getElementById("edit_nombres").value = persona.nombres ?? '';
                    document.getElementById("edit_segundo_nombre").value = persona.segundo_nombre ?? '';
                    document.getElementById("edit_apellidop").value = persona.apellidop ?? '';
                    document.getElementById("edit_apellidom").value = persona.apellidom ?? '';
                    document.getElementById("edit_telefono").value = persona.telefono ?? '';
                    document.getElementById("edit_usuario").value = persona.user_name ?? '';
                    document.getElementById("edit_contrasena").value = '';
                    cargarDepartamentosCombo(null, persona.id_departamento);
                    if (persona.id_departamento) {
                        cargarPuestosCombo(persona.id_departamento, persona.id_puesto);
                        cargarComboJefeDirecto(persona.id_departamento, persona.id_jefe, persona.id_puesto);
                    }
                    const checkLegion = document.getElementById('edit_asignar_legion');
                    const divLegion = document.getElementById('edit_div_select_legion');
                    const selectLegion = document.getElementById('edit_id_legion');
                    const idLegion = persona.id_legion ? String(persona.id_legion) : '';
                    checkLegion.checked = !!idLegion;
                    selectLegion.value = idLegion || '';
                    divLegion.style.display = checkLegion.checked ? 'block' : 'none';
                    document.getElementById('edit_alerta_multiples_puestos').classList.add('d-none');
                    document.getElementById('edit_contenedor_multiples_puestos').classList.add('d-none');
                    const offcanvas = new bootstrap.Offcanvas(document.getElementById('offcanvasEditUser'));
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
        
            const checkLegion = document.getElementById('edit_asignar_legion');
            const divLegion = document.getElementById('edit_div_select_legion');
            const selectLegion = document.getElementById('edit_id_legion');
            if (checkLegion) checkLegion.checked = false;
            if (selectLegion) selectLegion.value = '';
            if (divLegion) divLegion.style.display = 'none';
        }
        
            function toggleSelectLegionEdit() {
                const checkbox = document.getElementById('edit_asignar_legion');
                const divSelect = document.getElementById('edit_div_select_legion');
                const selectLegion = document.getElementById('edit_id_legion');
                if (checkbox && divSelect && selectLegion) {
                    divSelect.style.display = checkbox.checked ? 'block' : 'none';
                    if (!checkbox.checked) selectLegion.value = '';
                }
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
                
            function cargarComboJefeDirecto(id_departamento, seleccionado = null, id_puesto = null) {
                 fetch('/CapHum/getJefeDirecto', 
                    {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id_departamento: id_departamento, id_puesto: id_puesto || undefined })
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
                cargarComboJefeDirecto(idDepartamento, null, null);
            });

            document.getElementById('edit_id_puesto').addEventListener('change', function () {
                const idPuesto = this.value;
                const idDepartamento = document.getElementById('edit_departamento_id').value;
                if (!idDepartamento) return;
                cargarComboJefeDirecto(idDepartamento, null, idPuesto || null);
            });
                
           
            let currentPersonaId = null;
            
            function edit_perfil(id) {
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
            
                    const persona  = data.datos.persona || {};
                    const perfiles = data.datos.perfiles || [];
                    const puestos  = data.datos.puestos  || [];
            
                    const nombreCompleto = [
                        persona.nombres,
                        persona.apellidop,
                        persona.apellidom
                    ].filter(Boolean).join(' ');
            
                    document.getElementById("edit_perfil_id").value = persona.id ?? '';
                    document.getElementById("edit_perfil_nombres").value = nombreCompleto;
                    
                    // Obtener departamento y puesto del primer puesto asignado, o del primer puesto disponible
                    let nombreDepartamento = 'Sin departamento';
                    let nombrePuesto = 'Sin puesto';
                    const puestoAsignado = puestos.find(p => Number(p.asignado_flag) === 1);
                    if (puestoAsignado) {
                        if (puestoAsignado.nombre_departamento) {
                            nombreDepartamento = puestoAsignado.nombre_departamento;
                        }
                        if (puestoAsignado.nombre_puesto) {
                            nombrePuesto = puestoAsignado.nombre_puesto;
                        }
                    } else if (puestos.length > 0) {
                        if (puestos[0].nombre_departamento) {
                            nombreDepartamento = puestos[0].nombre_departamento;
                        }
                        if (puestos[0].nombre_puesto) {
                            nombrePuesto = puestos[0].nombre_puesto;
                        }
                    }
                    
                    // Actualizar título del modal - título arriba: "Administrar puestos y módulos del usuario", subtítulo abajo con nombre/departamento/puesto en negrita
                    document.getElementById("modalEditPerfilLabel").innerHTML = `
                        <i class="fa fa-user-shield me-2" style="color: #495057;"></i>Administrar puestos y módulos del usuario
                    `;
                    document.getElementById("modalEditPerfil_subtitle").innerHTML = `Gestión de Permisos y Accesos para <strong>${nombreCompleto} / ${nombreDepartamento} / ${nombrePuesto}</strong>`;
            
                    renderPuestos(puestos);
                    renderModulos(perfiles.filter(m => (m.pestana || '') !== 'Permisos especiales'));
                    renderPermisosEspeciales(perfiles.filter(m => (m.pestana || '') === 'Permisos especiales'));
            
                    // Abrir modal en lugar de offcanvas
                    const modalEl = document.getElementById('modalEditPerfil');
                    const modal = new bootstrap.Modal(modalEl);
                    
                    // Prevenir el warning de aria-hidden removiendo el focus del botón de cerrar antes de mostrar
                    modalEl.addEventListener('shown.bs.modal', function() {
                        // Mover el focus al primer elemento interactivo dentro del modal body
                        const firstInput = modalEl.querySelector('.modal-body input, .modal-body button, .modal-body select');
                        if (firstInput) {
                            setTimeout(() => firstInput.focus(), 100);
                        }
                    }, { once: true });
                    
                    modal.show();
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire("Error", "No se pudo cargar la información", "error");
                });
            }
            
            /* =========================
               PUESTOS
            ========================= */
            function renderPuestos(puestos) {
            
                const container = document.getElementById('puestos-form');
                container.innerHTML = '';
            
                if (!puestos.length) {
                    container.innerHTML = `<div class="text-muted small">No hay puestos asignados</div>`;
                    return;
                }
            
                const table = document.createElement('table');
                table.className = 'table table-flush-spacing mb-0 border-top';
            
                const tbody = document.createElement('tbody');
            
                puestos.forEach(puesto => {
            
                    const tr = document.createElement('tr');
            
                    // Nombre + descripción
                    const tdName = document.createElement('td');
                    tdName.className = 'fw-medium text-heading';
            
                    const nombre = document.createElement('div');
                    nombre.innerText =
                        puesto.nombre_puesto ||
                        puesto.puesto_nombre ||
                        'Puesto sin nombre';
            
                    const desc = document.createElement('small');
                    desc.className = 'text-muted d-block fs-7';
                    desc.innerText = puesto.descripcion ?? '';
            
                    tdName.append(nombre, desc);
            
                    // Checkbox
                    const tdCheck = document.createElement('td');
                    tdCheck.className = 'text-end';
            
                    const divCheck = document.createElement('div');
                    divCheck.className = 'form-check mb-0';
            
                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.className = 'form-check-input';
                    checkbox.checked = Number(puesto.asignado_flag) === 1;
                    checkbox.value =
                        puesto.id_puesto ||
                        puesto.puesto_id ||
                        '';
            
                    checkbox.onchange = () => onPuestoChange(checkbox);
            
                    const label = document.createElement('label');
                    label.className = 'form-check-label';
                    label.innerText = 'Asignar';
            
                    divCheck.append(checkbox, label);
                    tdCheck.appendChild(divCheck);
            
                    tr.append(tdName, tdCheck);
                    tbody.appendChild(tr);
                });
            
                table.appendChild(tbody);
                container.appendChild(table);
            }
            
            function onPuestoChange(checkbox) {
                if (!checkbox || !currentPersonaId) return;
            
                const payload = {
                    idPersona: currentPersonaId,
                    idPuesto: checkbox.value,
                    asignado: checkbox.checked ? 1 : 0
                };
            
                fetch('/caphum/actualizarPuestoPerfil', {
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
            
                    // ALERTA SEGÚN ACCIÓN - igual que módulos
                    Swal.fire({
                        icon: 'success',
                        title: checkbox.checked 
                            ? 'Asignación correcta'
                            : 'Asignación eliminada',
                        text: checkbox.checked
                            ? 'El puesto fue asignado correctamente'
                            : 'El puesto fue deseleccionado correctamente',
                        timer: 1600,
                        showConfirmButton: false
                    });
                })
                .catch(err => {
                    console.error(err);
                    checkbox.checked = !checkbox.checked;
                    Swal.fire("Error", "No se pudo actualizar el puesto", "error");
                });
            }
            
            /* =========================
               MÓDULOS
            ========================= */
            function renderModulos(perfiles) {
            
                const container = document.getElementById('modulos-form');
                if (!container) return;
                container.innerHTML = '';
            
                const modulosPorPestana = {};
                perfiles.forEach(m => {
                    if (!modulosPorPestana[m.pestana]) modulosPorPestana[m.pestana] = [];
                    modulosPorPestana[m.pestana].push(m);
                });
            
                const table = document.createElement('table');
                table.className = 'table table-flush-spacing mb-0 border-top';
                const tbody = document.createElement('tbody');
                Object.keys(modulosPorPestana).forEach(pestana => {
                    modulosPorPestana[pestana].forEach(mod => {
                        tbody.appendChild(crearFilaModulo(mod));
                    });
                });
                table.appendChild(tbody);
                container.appendChild(table);
            }
            
            function renderPermisosEspeciales(perfiles) {
                const container = document.getElementById('permisos-especiales-form');
                if (!container) return;
                container.innerHTML = '';
                if (!perfiles || perfiles.length === 0) {
                    container.innerHTML = '<p class="text-muted small mb-0">No hay permisos especiales configurados.</p>';
                    return;
                }
                const table = document.createElement('table');
                table.className = 'table table-flush-spacing mb-0 border-top';
                const tbody = document.createElement('tbody');
                perfiles.forEach(mod => {
                    tbody.appendChild(crearFilaModulo(mod));
                });
                table.appendChild(tbody);
                container.appendChild(table);
            }
            
            const iconosPermisosEspeciales = {
                21: 'fa fa-file-upload',
                22: 'fa fa-cloud-download',
                23: 'fa fa-calendar-alt'
            };
            function crearFilaModulo(mod) {
                const tr = document.createElement('tr');
                const tdName = document.createElement('td');
                tdName.className = 'fw-medium text-heading';
                tdName.style.paddingLeft = '1.5rem';
                const iconClass = iconosPermisosEspeciales[mod.modulo_id];
                if (iconClass) {
                    const icon = document.createElement('i');
                    icon.className = iconClass + ' me-2';
                    icon.style.color = '#6c757d';
                    icon.title = mod.modulo_nombre ?? '';
                    const nombre = document.createElement('div');
                    nombre.style.display = 'inline';
                    nombre.innerText = mod.modulo_nombre ?? 'Módulo';
                    const wrap = document.createElement('div');
                    wrap.append(icon, nombre);
                    tdName.appendChild(wrap);
                } else {
                    const nombre = document.createElement('div');
                    nombre.innerText = mod.modulo_nombre ?? 'Módulo';
                    tdName.appendChild(nombre);
                }
                const desc = document.createElement('small');
                desc.className = 'text-muted d-block fs-7 mt-1';
                desc.style.marginLeft = iconosPermisosEspeciales[mod.modulo_id] ? '1.75rem' : '0';
                desc.innerText = mod.descripcion ?? '';
                tdName.appendChild(desc);
                const tdCheck = document.createElement('td');
                tdCheck.className = 'text-end';
                const divCheck = document.createElement('div');
                divCheck.className = 'form-check mb-0';
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.className = 'form-check-input';
                checkbox.checked = Number(mod.asignado_flag) === 1;
                checkbox.value = mod.modulo_id;
                checkbox.onchange = () => onModuloChange(checkbox);
                const label = document.createElement('label');
                label.className = 'form-check-label';
                label.innerText = 'Asignar';
                divCheck.append(checkbox, label);
                tdCheck.appendChild(divCheck);
                tr.append(tdName, tdCheck);
                return tr;
            }
            
            function onModuloChange(checkbox) {
                if (!checkbox || !currentPersonaId) return;
                const payload = {
                    idPersona: currentPersonaId,
                    modulo_id: checkbox.value,
                    asignado: checkbox.checked ? 1 : 0
                };
                fetch('/CapHum/PerfilCheckBoxEstado', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(payload)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (typeof Swal !== 'undefined') Swal.fire({ icon: 'success', title: 'Guardado', text: data.mensaje || 'Permiso actualizado', timer: 1500, showConfirmButton: false });
                    } else {
                        if (typeof Swal !== 'undefined') Swal.fire('Error', data.mensaje || 'No se pudo actualizar', 'error');
                        checkbox.checked = !checkbox.checked;
                    }
                })
                .catch(err => {
                    console.error(err);
                    if (typeof Swal !== 'undefined') Swal.fire('Error', 'Error de conexión', 'error');
                    checkbox.checked = !checkbox.checked;
                });
            }


            
            /* =========================
               RENDER PUESTOS
            ========================= */
           function renderPuestos(puestos) {

            const container = document.getElementById('puestos-form');
            container.innerHTML = '';
        
            if (!puestos || puestos.length === 0) {
                container.innerHTML = `<div class="text-muted small text-center py-3">No hay puestos disponibles</div>`;
                return;
            }
        
            /* =========================
               AGRUPAR POR ID_DEPARTAMENTO Y ORDENAR
            ========================= */
            const puestosPorDepto = {};
        
            puestos.forEach(puesto => {
                const deptoId = puesto.id_departamento ?? 0;
                if (!puestosPorDepto[deptoId]) {
                    puestosPorDepto[deptoId] = {
                        nombre: puesto.nombre_departamento ?? `Departamento ${deptoId}`,
                        puestos: []
                    };
                }
                puestosPorDepto[deptoId].puestos.push(puesto);
            });
            
            // Ordenar puestos dentro de cada departamento por nivel (mayor a menor)
            Object.keys(puestosPorDepto).forEach(deptoId => {
                puestosPorDepto[deptoId].puestos.sort((a, b) => {
                    const nivelA = a.nivel ?? 0;
                    const nivelB = b.nivel ?? 0;
                    return nivelB - nivelA; // Mayor nivel primero
                });
            });
            
            // Ordenar departamentos alfabéticamente
            const deptosOrdenados = Object.keys(puestosPorDepto).sort((a, b) => {
                const nombreA = puestosPorDepto[a].nombre.toLowerCase();
                const nombreB = puestosPorDepto[b].nombre.toLowerCase();
                return nombreA.localeCompare(nombreB);
            });
        
            /* =========================
               CREAR ACORDEÓN MEJORADO
            ========================= */
            const accordion = document.createElement('div');
            accordion.className = 'accordion';
            accordion.id = 'accordionPuestos';
            accordion.style.cssText = '--bs-accordion-border-radius: 0.5rem;';
        
            let index = 0;
        
            deptosOrdenados.forEach(deptoId => {
        
                const deptoData = puestosPorDepto[deptoId];
                const puestosDepto = deptoData.puestos;
                const deptoNombre = deptoData.nombre;
        
                const accId = `depto_${deptoId}`;
        
                const item = document.createElement('div');
                item.className = 'accordion-item';
                item.style.border = '1px solid #dee2e6';
                item.style.marginBottom = '0.75rem';
                item.style.borderRadius = '0.5rem';
                item.style.overflow = 'hidden';
        
                item.innerHTML = `
                    <h2 class="accordion-header" id="heading_${accId}">
                        <button class="accordion-button collapsed"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapse_${accId}"
                                aria-expanded="false"
                                style="background: #f8f9fa; font-weight: 600; padding: 1rem 1.5rem; border: none;">
                            <div class="d-flex align-items-center w-100">
                                <i class="fa fa-building me-3" style="color: #6c757d; font-size: 1.1rem;"></i>
                                <div class="flex-grow-1 text-start">
                                    <strong style="color: #212529;">${deptoNombre}</strong>
                                </div>
                                <span class="badge rounded-pill bg-secondary" style="font-size: 0.75rem; padding: 0.35rem 0.75rem;">
                                    ${puestosDepto.length} puesto${puestosDepto.length !== 1 ? 's' : ''}
                                </span>
                            </div>
                        </button>
                    </h2>
                    <div id="collapse_${accId}"
                         class="accordion-collapse collapse"
                         data-bs-parent="#accordionPuestos">
                        <div class="accordion-body p-3" style="background-color: #ffffff; max-height: 450px; overflow-y: auto;"></div>
                    </div>
                `;
        
                /* =========================
                   TABLA MEJORADA CON MEJOR ESTRUCTURA
                ========================= */
                const body = item.querySelector('.accordion-body');
        
                const table = document.createElement('table');
                table.className = 'table table-hover mb-0';
                table.style.fontSize = '0.9rem';
                table.style.borderCollapse = 'separate';
                table.style.borderSpacing = '0';
        
                const tbody = document.createElement('tbody');
        
                puestosDepto.forEach((puesto, puestoIndex) => {
        
                    const tr = document.createElement('tr');
                    tr.style.borderBottom = puestoIndex < puestosDepto.length - 1 ? '1px solid #e9ecef' : 'none';
                    tr.style.transition = 'all 0.3s ease';
                    tr.style.cursor = 'pointer';
                    tr.style.borderLeft = '3px solid transparent';
                    
                    tr.onmouseenter = () => {
                        tr.style.backgroundColor = '#f8f9fa';
                        tr.style.borderLeftColor = '#495057';
                        tr.style.transform = 'translateX(4px)';
                    };
                    tr.onmouseleave = () => {
                        tr.style.backgroundColor = '';
                        tr.style.borderLeftColor = 'transparent';
                        tr.style.transform = 'translateX(0)';
                    };
        
                    const tdName = document.createElement('td');
                    tdName.className = 'fw-medium';
                    tdName.style.padding = '0.75rem';
                    tdName.style.verticalAlign = 'middle';
        
                    const nombreDiv = document.createElement('div');
                    nombreDiv.style.display = 'flex';
                    nombreDiv.style.alignItems = 'center';
                    nombreDiv.style.gap = '0.5rem';
                    
                    // Icono según nivel - diseño en blanco y negro (aún más pequeño)
                    const icono = document.createElement('div');
                    icono.style.width = '18px';
                    icono.style.height = '18px';
                    icono.style.borderRadius = '50%';
                    icono.style.display = 'flex';
                    icono.style.alignItems = 'center';
                    icono.style.justifyContent = 'center';
                    icono.style.flexShrink = '0';
                    icono.style.border = '1.5px solid #dee2e6';
                    
                    const nivel = puesto.nivel ?? 0;
                    const iconoInner = document.createElement('i');
                    if (nivel >= 5) {
                        icono.style.background = '#212529';
                        icono.style.borderColor = '#212529';
                        iconoInner.className = 'fa fa-crown';
                        iconoInner.style.color = '#fff';
                        iconoInner.style.fontSize = '0.5rem';
                        icono.title = 'Nivel Ejecutivo';
                    } else if (nivel >= 3) {
                        icono.style.background = '#495057';
                        icono.style.borderColor = '#495057';
                        iconoInner.className = 'fa fa-star';
                        iconoInner.style.color = '#fff';
                        iconoInner.style.fontSize = '0.45rem';
                        icono.title = 'Nivel Gerencial';
                    } else {
                        icono.style.background = '#e9ecef';
                        icono.style.borderColor = '#adb5bd';
                        iconoInner.className = 'fa fa-circle';
                        iconoInner.style.color = '#6c757d';
                        iconoInner.style.fontSize = '0.3rem';
                        icono.title = 'Nivel Operativo';
                    }
                    icono.appendChild(iconoInner);
                    
                    const nombre = document.createElement('span');
                    nombre.innerText = puesto.nombre_puesto || puesto.puesto_nombre || 'Puesto sin nombre';
                    nombre.style.fontWeight = '600';
                    nombre.style.color = '#2c3e50';
                    nombre.style.fontSize = '0.95rem';
                    
                    nombreDiv.append(icono, nombre);
        
                    const desc = document.createElement('small');
                    desc.className = 'text-muted d-block mt-1';
                    desc.style.fontSize = '0.75rem';
                    desc.innerText = puesto.descripcion ?? '';
        
                    tdName.append(nombreDiv, desc);
        
                    const tdCheck = document.createElement('td');
                    tdCheck.className = 'text-end';
                    tdCheck.style.padding = '0.75rem';
                    tdCheck.style.verticalAlign = 'middle';
                    tdCheck.style.width = '130px';
        
                    const divCheck = document.createElement('div');
                    divCheck.className = 'form-check mb-0';
                    divCheck.style.display = 'flex';
                    divCheck.style.alignItems = 'center';
                    divCheck.style.justifyContent = 'flex-end';
                    divCheck.style.gap = '0.5rem';
        
                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.className = 'form-check-input';
                    checkbox.value = puesto.id_puesto || puesto.puesto_id || '';
                    checkbox.checked = Number(puesto.asignado_flag) === 1;
                    checkbox.onchange = () => onPuestoChange(checkbox);
                    checkbox.style.cursor = 'pointer';
                    checkbox.style.width = '1.1em';
                    checkbox.style.height = '1.1em';
        
                    const label = document.createElement('label');
                    label.className = 'form-check-label';
                    label.innerHTML = checkbox.checked 
                        ? '<span class="badge bg-success rounded-pill px-3 py-1"><i class="fa fa-check me-1"></i>Asignado</span>'
                        : '<span class="badge bg-secondary rounded-pill px-3 py-1">Asignar</span>';
                    label.style.cursor = 'pointer';
                    label.style.userSelect = 'none';
                    
                    // Actualizar label cuando cambia el checkbox
                    checkbox.addEventListener('change', function() {
                        label.innerHTML = this.checked 
                            ? '<span class="badge bg-success rounded-pill px-3 py-1"><i class="fa fa-check me-1"></i>Asignado</span>'
                            : '<span class="badge bg-secondary rounded-pill px-3 py-1">Asignar</span>';
                    });
        
                    divCheck.append(checkbox, label);
                    tdCheck.appendChild(divCheck);
        
                    tr.append(tdName, tdCheck);
                    tbody.appendChild(tr);
                });
        
                table.appendChild(tbody);
                body.appendChild(table);
        
                accordion.appendChild(item);
                index++;
            });
        
            container.appendChild(accordion);
        }


            
            function onPuestoChange(checkbox) {
                if (!checkbox || !currentPersonaId) return;
            
                const payload = {
                    idPersona: currentPersonaId,
                    idPuesto: checkbox.value,
                    asignado: checkbox.checked ? 1 : 0
                };
            
                fetch('/caphum/actualizarPuestoPerfil', {
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
            
                    // ALERTA SEGÚN ACCIÓN - igual que módulos
                    Swal.fire({
                        icon: 'success',
                        title: checkbox.checked 
                            ? 'Asignación correcta'
                            : 'Asignación eliminada',
                        text: checkbox.checked
                            ? 'El puesto fue asignado correctamente'
                            : 'El puesto fue deseleccionado correctamente',
                        timer: 1600,
                        showConfirmButton: false
                    });
                })
                .catch(err => {
                    console.error(err);
                    checkbox.checked = !checkbox.checked;
                    Swal.fire("Error", "No se pudo actualizar el puesto", "error");
                });
            }
            
            // Función para expandir todos los acordeones
            function expandirTodosPuestos() {
                const accordion = document.getElementById('accordionPuestos');
                if (!accordion) return;
                
                const collapses = accordion.querySelectorAll('.accordion-collapse');
                const isAllExpanded = Array.from(collapses).every(c => c.classList.contains('show'));
                
                collapses.forEach(collapse => {
                    if (isAllExpanded) {
                        collapse.classList.remove('show');
                        const button = collapse.previousElementSibling?.querySelector('.accordion-button');
                        if (button) button.classList.add('collapsed');
                    } else {
                        collapse.classList.add('show');
                        const button = collapse.previousElementSibling?.querySelector('.accordion-button');
                        if (button) button.classList.remove('collapsed');
                    }
                });
                
                const btnExpandir = event?.target || document.querySelector('[onclick="expandirTodosPuestos()"]');
                if (btnExpandir) {
                    btnExpandir.innerHTML = isAllExpanded 
                        ? '<i class="fa fa-expand me-1"></i>Expandir Departamentos'
                        : '<i class="fa fa-compress me-1"></i>Colapsar Departamentos';
                }
            }
            
            // Función para guardar permisos
            function guardarPermisos() {
                const personaId = document.getElementById('edit_perfil_id').value;
                
                if (!personaId) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se encontró el ID de la persona'
                    });
                    return;
                }
                
                // Recopilar puestos seleccionados
                const puestosSeleccionados = [];
                const checkboxesPuestos = document.querySelectorAll('#puestos-form input[type="checkbox"]:checked');
                checkboxesPuestos.forEach(cb => {
                    if (cb.value) {
                        puestosSeleccionados.push(parseInt(cb.value));
                    }
                });
                
                // Recopilar módulos seleccionados y no seleccionados
                const modulosAsignar = [];
                const modulosEliminar = [];
                const checkboxesModulos = document.querySelectorAll('#modulos-form input[type="checkbox"]');
                checkboxesModulos.forEach(cb => {
                    if (cb.value) {
                        if (cb.checked) {
                            modulosAsignar.push(parseInt(cb.value));
                        } else {
                            modulosEliminar.push(parseInt(cb.value));
                        }
                    }
                });
                
                Swal.fire({
                    title: 'Guardando cambios...',
                    text: 'Por favor espera',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Guardar puestos
                fetch('/caphum/guardarPermisosPuestos', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        idPersona: parseInt(personaId),
                        puestos: puestosSeleccionados
                    })
                })
                .then(res => res.json())
                .then(respPuestos => {
                    if (!respPuestos.success) {
                        throw new Error(respPuestos.mensaje || 'Error al guardar puestos');
                    }
                    
                    // Guardar módulos (asignar y eliminar)
                    const promesasModulos = [];
                    
                    // Asignar módulos
                    modulosAsignar.forEach(moduloId => {
                        promesasModulos.push(
                            fetch('/caphum/PerfilCheckBoxEstado', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    idPersona: parseInt(personaId),
                                    modulo_id: moduloId,
                                    asignado: 1
                                })
                            }).then(res => res.json())
                        );
                    });
                    
                    // Eliminar módulos
                    modulosEliminar.forEach(moduloId => {
                        promesasModulos.push(
                            fetch('/caphum/PerfilCheckBoxEstado', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    idPersona: parseInt(personaId),
                                    modulo_id: moduloId,
                                    asignado: 0
                                })
                            }).then(res => res.json())
                        );
                    });
                    
                    return Promise.all(promesasModulos);
                })
                .then(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Cambios guardados',
                        text: `Se guardaron ${puestosSeleccionados.length} puesto(s) y ${modulosAsignar.length} módulo(s) correctamente`,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        const modalEl = document.getElementById('modalEditPerfil');
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();
                        
                        // Recargar la tabla si es necesario
                        if (typeof getUsuarios === 'function') {
                            getUsuarios();
                        }
                    });
                })
                .catch(err => {
                    console.error('Error:', err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al guardar',
                        text: err.message || 'Ocurrió un error al guardar los cambios'
                    });
                });
            }
            
            /* =========================
               RENDER MÓDULOS
            ========================= */
            function renderModulos(perfiles) {
            
                const container = document.getElementById('modulos-form');
                container.innerHTML = '';
            
                if (!perfiles || perfiles.length === 0) {
                    container.innerHTML = `<div class="text-muted small text-center py-4">No hay módulos disponibles</div>`;
                    return;
                }
            
                const modulosPorPestana = {};
                perfiles.forEach(m => {
                    if (!modulosPorPestana[m.pestana]) modulosPorPestana[m.pestana] = [];
                    modulosPorPestana[m.pestana].push(m);
                });
            
                const table = document.createElement('table');
                table.className = 'table table-hover mb-0';
                table.style.fontSize = '0.9rem';
            
                const tbody = document.createElement('tbody');
            
                Object.keys(modulosPorPestana).forEach(pestana => {
                    modulosPorPestana[pestana].forEach((mod, modIndex) => {
            
                        const tr = document.createElement('tr');
                        tr.style.transition = 'all 0.3s ease';
                        tr.style.cursor = 'pointer';
                        tr.style.borderLeft = '3px solid transparent';
                        tr.style.borderBottom = '1px solid #e9ecef';
                        
                        tr.onmouseenter = () => {
                            tr.style.backgroundColor = '#f8f9fa';
                            tr.style.borderLeftColor = '#495057';
                            tr.style.transform = 'translateX(4px)';
                        };
                        tr.onmouseleave = () => {
                            tr.style.backgroundColor = '';
                            tr.style.borderLeftColor = 'transparent';
                            tr.style.transform = 'translateX(0)';
                        };
            
                        const tdName = document.createElement('td');
                        tdName.className = 'fw-medium';
                        tdName.style.padding = '0.875rem';
                        tdName.style.verticalAlign = 'middle';
            
                        const nombreDiv = document.createElement('div');
                        nombreDiv.style.display = 'flex';
                        nombreDiv.style.alignItems = 'center';
                        nombreDiv.style.gap = '0.75rem';
                        
                        // Icono del módulo - diseño en blanco y negro
                        const iconoModulo = document.createElement('div');
                        iconoModulo.style.width = '36px';
                        iconoModulo.style.height = '36px';
                        iconoModulo.style.borderRadius = '8px';
                        iconoModulo.style.background = '#e9ecef';
                        iconoModulo.style.border = '2px solid #dee2e6';
                        iconoModulo.style.display = 'flex';
                        iconoModulo.style.alignItems = 'center';
                        iconoModulo.style.justifyContent = 'center';
                        iconoModulo.style.flexShrink = '0';
                        
                        const iconoInner = document.createElement('i');
                        iconoInner.className = 'fa fa-shield-alt';
                        iconoInner.style.color = '#495057';
                        iconoInner.style.fontSize = '0.9rem';
                        iconoModulo.appendChild(iconoInner);
            
                        const nombre = document.createElement('span');
                        nombre.innerText = mod.modulo_nombre ?? 'Módulo';
                        nombre.style.fontWeight = '600';
                        nombre.style.color = '#2c3e50';
                        nombre.style.fontSize = '0.95rem';
                        
                        nombreDiv.appendChild(iconoModulo);
                        nombreDiv.appendChild(nombre);
            
                        const desc = document.createElement('small');
                        desc.className = 'text-muted d-block mt-1';
                        desc.style.fontSize = '0.75rem';
                        desc.innerText = mod.descripcion ?? '';
            
                        tdName.append(nombreDiv, desc);
            
                        const tdCheck = document.createElement('td');
                        tdCheck.className = 'text-end';
                        tdCheck.style.padding = '0.875rem';
                        tdCheck.style.verticalAlign = 'middle';
                        tdCheck.style.width = '130px';
            
                        const divCheck = document.createElement('div');
                        divCheck.className = 'form-check mb-0';
                        divCheck.style.display = 'flex';
                        divCheck.style.alignItems = 'center';
                        divCheck.style.justifyContent = 'flex-end';
                        divCheck.style.gap = '0.5rem';
            
                        const checkbox = document.createElement('input');
                        checkbox.type = 'checkbox';
                        checkbox.className = 'form-check-input';
                        checkbox.checked = Number(mod.asignado_flag) === 1;
                        checkbox.value = mod.modulo_id;
                        checkbox.onchange = () => onModuloChange(checkbox);
                        checkbox.style.cursor = 'pointer';
                        checkbox.style.width = '1.1em';
                        checkbox.style.height = '1.1em';
            
                        const label = document.createElement('label');
                        label.className = 'form-check-label';
                        label.innerHTML = checkbox.checked 
                            ? '<span class="badge bg-success rounded-pill px-3 py-1"><i class="fa fa-check me-1"></i>Asignado</span>'
                            : '<span class="badge bg-secondary rounded-pill px-3 py-1">Asignar</span>';
                        label.style.cursor = 'pointer';
                        label.style.userSelect = 'none';
                        
                        checkbox.addEventListener('change', function() {
                            label.innerHTML = this.checked 
                                ? '<span class="badge bg-success rounded-pill px-3 py-1"><i class="fa fa-check me-1"></i>Asignado</span>'
                                : '<span class="badge bg-secondary rounded-pill px-3 py-1">Asignar</span>';
                        });
            
                        divCheck.append(checkbox, label);
                        tdCheck.appendChild(divCheck);
            
                        tr.append(tdName, tdCheck);
                        tbody.appendChild(tr);
                    });
                });
            
                table.appendChild(tbody);
                container.appendChild(table);
            }
            
            function onModuloChange(checkbox) {
                console.log('Módulo:', checkbox.value, checkbox.checked);
            }
            
            function baja_gestor(id) {
                    if (!id) {
                        Swal.fire("Error", "ID inválido", "error");
                        return;
                    }
                
                    // Limpiar campos del modal antes de cargar nueva información
                    document.getElementById("motivoBaja").value = "";
                    document.getElementById("motivoBajaDescripcion").value = "";
                    document.getElementById("archivoPDF").value = "";
                    document.getElementById("listaArchivos").innerHTML = "";
                    document.getElementById("listaArchivos").style.display = "none";
                    const spanBaja = document.getElementById("bajaModal_nombreArchivo");
                    if (spanBaja) spanBaja.textContent = "No se ha seleccionado ningún archivo";
                    archivosSeleccionados = [];
                    document.getElementById("gestor").innerHTML = "<strong>Gestor:</strong> ";
                
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
        
            function registra_ausencia(id) {            
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
                .then(resp => {
            
                    if (!resp.success) {
                        Swal.fire("Error", resp.mensaje, "error");
                        return;
                    }
            
                    const persona = resp.datos.persona;
            
                    console.log('Persona:', persona);
            
                    // ID oculto
                    document.getElementById("edit_id_ausencia").value = persona.id;
            
                    // Nombre del gestor
                    document.getElementById("gestor_ausencia").innerHTML =
                        `<strong>Gestor:</strong> ${persona.nombres} ${persona.apellidop} ${persona.apellidom}`;
            
                    // Limpiar formulario
                    document.getElementById("razonAusencia").value = "";
                    document.getElementById("fechaInicio").value = "";
                    document.getElementById("fechaFin").value = "";
                    document.getElementById("descripcionAusencia").value = "";
            
                    // Cargar catálogo y tabla
                    cargarRazones();
                    cargarAusencias(persona.id);
            
                    // Mostrar modal correcto
                    $("#modalAuscencia").modal("show");
                })
                .catch(err => {
                    console.error('FETCH ERROR:', err);
                    Swal.fire("Error", "No se pudo cargar la información", "error");
                });
            }
            
            function initFlatpickrAusencia() {
                var inpInicio = document.getElementById("fechaInicio");
                var inpFin = document.getElementById("fechaFin");
                if (!inpInicio || !inpFin || typeof flatpickr === "undefined") return;
                if (inpInicio._flatpickr) return;
                var opts = {
                    enableTime: true,
                    dateFormat: "Y-m-d H:i",
                    time_24hr: true,
                    allowInput: false,
                    appendTo: document.body,
                    locale: "es",
                    static: false
                };
                flatpickr(inpInicio, opts);
                flatpickr(inpFin, opts);
            }
            
            function cargarAusencias(idPersona) {
            fetch('/CapHum/getAusenciasPersona', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    idPersona: idPersona
                })
            })
            .then(res => res.json())
            .then(resp => {
        
                if (!resp.success) {
                    Swal.fire("Error", resp.mensaje, "error");
                    return;
                }
        
                const tbody = document.getElementById("tablaAusencias");
                tbody.innerHTML = "";
        
                const data = resp.datos;
        
                if (!Array.isArray(data) || data.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                Sin registros
                            </td>
                        </tr>`;
                    return;
                }
        
                data.forEach(a => {
                    tbody.innerHTML += `
                        <tr>
                            <td>${a.razon}</td>
                            <td>${a.fecha_inicio}</td>
                            <td>${a.fecha_fin}</td>
                            <td>${a.descripcion ?? ''}</td>
                            <td>${a.activo == 1 ? 'Sí' : 'No'}</td>
                             <td class="text-center">
                                <button class="btn btn-sm btn-warning"
                                    onclick="editarAusencia(${a.id})"
                                    title="Editar / Documentos">
                                    <i class="fa fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
            })
            .catch(err => {
                console.error("ERROR cargarAusencias:", err);
            });
        }
        
           function cargarRazones() {

                fetch('/CapHum/getRazonesAusencia')
                    .then(res => res.json())
                    .then(resp => {
            
                        if (!resp.success) {
                            Swal.fire("Error", resp.mensaje, "error");
                            return;
                        }
            
                        if (!Array.isArray(resp.datos)) {
                            console.error("resp.datos no es array", resp);
                            return;
                        }
            
                        const select = document.getElementById('razonAusencia');
                        select.innerHTML = '<option value="">-- Selecciona --</option>';
            
                        resp.datos.forEach(r => {
                            select.innerHTML += `
                                <option value="${r.id}">${r.nombre}</option>
                            `;
                        });
                    })
                    .catch(err => {
                        console.error("ERROR cargarRazones:", err);
                    });
            }
            
           function editarAusencia(idAusencia) {
                
                console.log("ID AUSENCIA AL EDITAR:",idAusencia);

                if (!idAusencia) {
                    Swal.fire("Error", "Id de ausencia inválido", "error");
                    return;
                }
            
                fetch('/CapHum/getAusenciaById', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ idAusencia: idAusencia })
                })
                .then(res => res.json())
                .then(resp => {
            
                    if (!resp.success) {
                        Swal.fire("Error", resp.mensaje, "error");
                        return;
                    }
            
                    const a = resp.datos;
            
                    // 🔹 Modo edición
                    document.getElementById("id_ausencia").value = a.id;
                    document.getElementById("razonAusencia").value = a.id_razon;
            
                    // 🔹 Fechas formato Flatpickr (Y-m-d H:i)
                    var fi = (a.fecha_inicio || '').toString().substring(0, 16);
                    var ff = (a.fecha_fin || '').toString().substring(0, 16);
                    document.getElementById("fechaInicio").value = fi;
                    document.getElementById("fechaFin").value = ff;
                    if (document.getElementById("fechaInicio")._flatpickr) document.getElementById("fechaInicio")._flatpickr.setDate(fi, false);
                    if (document.getElementById("fechaFin")._flatpickr) document.getElementById("fechaFin")._flatpickr.setDate(ff, false);
            
                    document.getElementById("descripcionAusencia").value = a.descripcion ?? '';
            
                    // 🔹 Cambiar texto del botón
                    document.getElementById("btnGuardarAusencia").innerText = "Actualizar ausencia";
            
                    // 🔹 Cargar documentos asociados
                    //cargarDocumentosAusencia(a.id);
            
                    // 🔹 Mostrar modal
                    $("#modalAusencia").modal("show");
                })
                .catch(err => {
                    console.error("ERROR editarAusencia:", err);
                    Swal.fire("Error", "No se pudo cargar la ausencia", "error");
                });
            }

            function limpiarFormularioAusencia() {
                document.getElementById("id_ausencia").value = '';
                document.getElementById("razonAusencia").value = '';
                var elInicio = document.getElementById("fechaInicio");
                var elFin = document.getElementById("fechaFin");
                if (elInicio) { elInicio.value = ''; if (elInicio._flatpickr) elInicio._flatpickr.clear(); }
                if (elFin) { elFin.value = ''; if (elFin._flatpickr) elFin._flatpickr.clear(); }
                document.getElementById("descripcionAusencia").value = '';
            
                // Texto del botón
                document.getElementById("btnGuardarAusencia").innerText = "Guardar ausencia";
            
                // Texto del gestor (opcional)
                const gestor = document.getElementById("gestor");
                if (gestor) {
                    gestor.innerHTML = "<strong>Gestor:</strong>";
                }
            }

            
           function guardarAusencia() {

                const idAusencia = document.getElementById("id_ausencia").value;
                const idPersona   = document.getElementById("edit_id_ausencia").value;
                const idRazon     = document.getElementById("razonAusencia").value;
                const fechaInicio = document.getElementById("fechaInicio").value;
                const fechaFin    = document.getElementById("fechaFin").value;
                const descripcion = document.getElementById("descripcionAusencia").value;
            
                const payload = {
                    idPersona,
                    idRazon,
                    fechaInicio,
                    fechaFin,
                    descripcion,
                    idAusencia
                };
            
            
                fetch('/CapHum/guardarAusencia', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                })
                .then(res => res.json())
                .then(resp => {
            
                    if (!resp.success) {
                        Swal.fire("Error", resp.mensaje, "error");
                        return;
                    }
            
                    Swal.fire("Éxito", resp.mensaje, "success");
            
                    $("#modalAusencia").modal("hide");
            
                    // Limpieza
                    document.getElementById("id_ausencia").value = '';
                    document.getElementById("btnGuardarAusencia").innerText = "Guardar ausencia";
            
                    // ✅ LIMPIEZA CENTRALIZADA
                    limpiarFormularioAusencia();
                    // Refrescar tabla
                    cargarAusencias(idPersona);
                })
                .catch(err => {
                    console.error("ERROR guardarAusencia:", err);
                    Swal.fire("Error", "No se pudo guardar la ausencia", "error");
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
            
                                // 🧹 Limpieza
                                archivosSeleccionados = [];
                                const listEl = document.getElementById("listaArchivos");
                                if (listEl) { listEl.innerHTML = ""; listEl.style.display = "none"; }
                                const spanBajaOk = document.getElementById("bajaModal_nombreArchivo");
                                if (spanBajaOk) spanBajaOk.textContent = "No se ha seleccionado ningún archivo";
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
                const asignarLegion = document.getElementById("edit_asignar_legion") && document.getElementById("edit_asignar_legion").checked;
                const idLegion     = document.getElementById("edit_id_legion") ? document.getElementById("edit_id_legion").value : '';
            
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
            
                if (asignarLegion && !idLegion) {
                    Swal.fire("Falta información", "Debes seleccionar una legión", "warning");
                    return;
                }
            
                // 🔹 Obtener puestos adicionales si hay panel de múltiples puestos
                let puestosAdicionales = [];
                if (typeof obtenerPuestosParaGuardar === 'function') {
                    puestosAdicionales = obtenerPuestosParaGuardar();
                    console.log('🔍 CONTROLLER - puestosAdicionales obtenidos:', puestosAdicionales);
                }
            
                // 🔹 Payload
                const payload = {
                    id: document.getElementById("edit_id").value,
                    nombres: document.getElementById("edit_nombres").value,
                    segundo_nombre: document.getElementById("edit_segundo_nombre").value,
                    apellidop: document.getElementById("edit_apellidop").value,
                    apellidom: document.getElementById("edit_apellidom").value,
                    telefono: document.getElementById("edit_telefono").value,
                    departamento_id: departamento,
                    puesto_id: puesto,
                    jefe_id: jefe,
                    asignar_legion: asignarLegion,
                    id_legion: asignarLegion ? idLegion : null,
                    usuario: document.getElementById("edit_usuario").value,
                    contrasena: document.getElementById("edit_contrasena").value,
                    puestos_adicionales: puestosAdicionales  // 🆕 Incluir múltiples puestos
                };
                
                console.log('📤 CONTROLLER - Payload completo a enviar:', payload);
            
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
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id_departamento: idDepartamento })
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
                    // Cargar jefes del departamento (sin puesto aún: por es_jefe o todas las personas del depto)
                    cargarJefeComboAdd(idDepartamento, null, selectJefe);
                })
                .catch(() => {
                    Swal.fire("Error", "No se pudieron cargar los puestos", "error");
                });
            });
            
            function cargarJefeComboAdd(idDepartamento, idPuesto, selectJefe) {
                if (!selectJefe) selectJefe = document.getElementById('add_id_jefe');
                selectJefe.innerHTML = '<option value="">Seleccione un jefe</option>';
                selectJefe.disabled = true;
                fetch('/CapHum/getJefeDirecto', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id_departamento: idDepartamento, id_puesto: idPuesto || undefined })
                })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        selectJefe.disabled = false;
                        return;
                    }
                    (data.datos || []).forEach(jefe => {
                        const option = document.createElement('option');
                        option.value = jefe.id;
                        option.textContent = jefe.nombre_completo || (jefe.nombre_puesto ? jefe.nombre_completo + ' - ' + jefe.nombre_puesto : jefe.nombre_completo);
                        selectJefe.appendChild(option);
                    });
                    selectJefe.disabled = false;
                })
                .catch(() => {
                    selectJefe.disabled = false;
                    Swal.fire("Error", "No se pudieron cargar los jefes", "error");
                });
            }
            
            document.getElementById('add_id_puesto').addEventListener('change', function () {
                const idPuesto = this.value;
                const idDepartamento = document.getElementById('add_departamento_id').value;
                const selectJefe = document.getElementById('add_id_jefe');
                if (!idDepartamento) return;
                cargarJefeComboAdd(idDepartamento, idPuesto || null, selectJefe);
            }); 
            
            // Función para mostrar/ocultar el select de legión
            function toggleSelectLegion() {
                const checkbox = document.getElementById('add_asignar_legion');
                const divSelect = document.getElementById('div_select_legion');
                const selectLegion = document.getElementById('add_id_legion');
                
                if (checkbox.checked) {
                    divSelect.style.display = 'block';
                } else {
                    divSelect.style.display = 'none';
                    selectLegion.value = ''; // Limpiar selección si se desmarca
                }
            }
            
            // Variable para almacenar los event listeners y poder removerlos
            let fechaInputClickHandler = null;
            let fechaWrapperClickHandler = null;
            
            function configurarMaxFechaIngreso() {
                // Esperar un poco para asegurar que el input esté disponible
                setTimeout(() => {
                    const wrapper = document.getElementById('fecha_acta_wrapper');
                    if (!wrapper) {
                        console.warn('Wrapper de fecha no encontrado');
                        return;
                    }
                    
                    let oldInput = document.getElementById('add_fecha_ingreso');
                    if (!oldInput) {
                        console.warn('Input de fecha no encontrado');
                        return;
                    }
                    
                    // Remover event listeners anteriores si existen
                    if (fechaInputClickHandler && oldInput) {
                        oldInput.removeEventListener('click', fechaInputClickHandler);
                        fechaInputClickHandler = null;
                    }
                    
                    if (wrapper && fechaWrapperClickHandler) {
                        wrapper.removeEventListener('click', fechaWrapperClickHandler);
                        fechaWrapperClickHandler = null;
                    }
                    
                    // Intentar destruir instancia anterior de forma segura
                    try {
                        if (oldInput._flatpickr) {
                            const instance = oldInput._flatpickr;
                            if (instance && typeof instance.destroy === 'function') {
                                instance.destroy();
                            }
                        }
                        // También intentar con getInstance
                        if (typeof flatpickr !== 'undefined' && typeof flatpickr.getInstance === 'function') {
                            const existingInstance = flatpickr.getInstance(oldInput);
                            if (existingInstance && typeof existingInstance.destroy === 'function') {
                                existingInstance.destroy();
                            }
                        }
                    } catch (e) {
                        // Ignorar errores al destruir
                    }
                    
                    // Guardar el valor actual si existe
                    const currentValue = oldInput.value || '';
                    
                    // Crear un nuevo input completamente limpio
                    const newInput = document.createElement('input');
                    newInput.type = 'text';
                    newInput.id = 'add_fecha_ingreso';
                    newInput.className = 'form-control';
                    newInput.placeholder = 'YYYY-MM-DD';
                    newInput.value = currentValue;
                    
                    // Reemplazar el input viejo con el nuevo
                    oldInput.parentNode.replaceChild(newInput, oldInput);
                    
                    // Pequeño delay para asegurar que el DOM se actualice
                    setTimeout(() => {
                        const input = document.getElementById('add_fecha_ingreso');
                        if (!input) {
                            console.warn('Nuevo input no encontrado después del reemplazo');
                            return;
                        }
                        
                        // Calcular fecha máxima (hoy + 1 día)
                        const hoy = new Date();
                        hoy.setDate(hoy.getDate() + 1);
                        const fechaMax = hoy.toISOString().split('T')[0];
                        
                        // Inicializar Flatpickr si está disponible
                        if (typeof flatpickr !== 'undefined') {
                            try {
                                // Renderizar el calendario directamente en el body para evitar problemas de overflow
                                const fp = flatpickr(input, {
                                    dateFormat: 'Y-m-d',
                                    maxDate: fechaMax,
                                    allowInput: false,
                                    clickOpens: true,
                                    defaultDate: null,
                                    appendTo: document.body,
                                    static: false
                                });
                                
                                // Asegurar que el calendario tenga z-index alto después de renderizarse
                                setTimeout(() => {
                                    if (fp && fp.calendarContainer) {
                                        fp.calendarContainer.style.zIndex = '99999';
                                        fp.calendarContainer.style.display = '';
                                        fp.calendarContainer.style.visibility = 'visible';
                                        fp.calendarContainer.style.opacity = '1';
                                    }
                                }, 100);
                                
                                // Crear handler para el click en el input
                                fechaInputClickHandler = function(e) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    setTimeout(() => {
                                        if (fp && typeof fp.open === 'function') {
                                            fp.open();
                                            // Asegurar visibilidad después de abrir
                                            if (fp.calendarContainer) {
                                                fp.calendarContainer.style.zIndex = '99999';
                                                fp.calendarContainer.style.display = '';
                                                fp.calendarContainer.style.visibility = 'visible';
                                                fp.calendarContainer.style.opacity = '1';
                                            }
                                        }
                                    }, 10);
                                };
                                
                                input.addEventListener('click', fechaInputClickHandler);
                                
                                // También en el wrapper
                                if (wrapper) {
                                    fechaWrapperClickHandler = function(e) {
                                        if (e.target === wrapper || e.target === input) {
                                            e.preventDefault();
                                            e.stopPropagation();
                                            setTimeout(() => {
                                                if (fp && typeof fp.open === 'function') {
                                                    fp.open();
                                                    // Asegurar visibilidad después de abrir
                                                    if (fp.calendarContainer) {
                                                        fp.calendarContainer.style.zIndex = '99999';
                                                        fp.calendarContainer.style.display = '';
                                                        fp.calendarContainer.style.visibility = 'visible';
                                                        fp.calendarContainer.style.opacity = '1';
                                                    }
                                                }
                                            }, 10);
                                        }
                                    };
                                    wrapper.addEventListener('click', fechaWrapperClickHandler);
                                }
                                
                                
                            } catch (error) {
                                console.error('Error al inicializar Flatpickr:', error);
                                // Fallback si hay error
                                input.type = 'date';
                                input.max = fechaMax;
                            }
                        } else {
                            // Fallback si Flatpickr no está disponible
                            console.warn('Flatpickr no está disponible, usando input nativo');
                            input.type = 'date';
                            input.max = fechaMax;
                        }
                    }, 50); // Delay corto para asegurar que el DOM se actualice
                }, 200);
            }
            
            function guardarGestor() {
                const nombres = document.getElementById('add_nombres').value.trim();
                const segundo_nombre = document.getElementById('add_segundo_nombre').value.trim();
                const apellidop = document.getElementById('add_apellidop').value.trim();
                const apellidom = document.getElementById('add_apellidom').value.trim();
                const telefono = document.getElementById('add_telefono').value.trim();
                const id_puesto = document.getElementById('add_id_puesto').value;
                const departamento_id = document.getElementById('add_departamento_id').value;
                const id_jefe = document.getElementById('add_id_jefe').value;
                const asignarLegion = document.getElementById('add_asignar_legion').checked;
                const id_legion = document.getElementById('add_id_legion').value;
                
                const usuario = document.getElementById('add_usuario').value.trim();
                const contrasena = document.getElementById('add_contrasena').value.trim();
                const fecha_ingreso = document.getElementById('add_fecha_ingreso').value.trim() || null;
            
            
                // 🔴 Validaciones obligatorias (todos los campos)
                if (!nombres) return Swal.fire('Error', 'Los nombres son obligatorios', 'error');
                if (!apellidop) return Swal.fire('Error', 'El apellido paterno es obligatorio', 'error');
                if (!apellidom) return Swal.fire('Error', 'El apellido materno es obligatorio', 'error');
                if (!telefono) return Swal.fire('Error', 'El teléfono es obligatorio', 'error');
                if (!fecha_ingreso) return Swal.fire('Error', 'La fecha de ingreso es obligatoria', 'error');

                // 🔴 Validar relaciones
                if (!id_puesto) return Swal.fire('Error', 'Debe seleccionar un puesto', 'error');
                if (!departamento_id) return Swal.fire('Error', 'Debe seleccionar un departamento', 'error');
            
                // ⚠️ jefe puede ser null, solo valida si viene
                if (id_jefe && isNaN(id_jefe)) {
                    return Swal.fire('Error', 'Jefe inválido', 'error');
                }
                
                // 🔴 Validar legión: si el checkbox está marcado, debe seleccionar una legión
                if (asignarLegion && !id_legion) {
                    return Swal.fire('Error', 'Debe seleccionar una legión', 'error');
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
                        segundo_nombre,
                        apellidop,
                        apellidom,
                        telefono,
                        fecha_ingreso,
                        id_puesto,
                        departamento_id,
                        id_jefe: id_jefe || null,
                        asignar_legion: asignarLegion,
                        id_legion: asignarLegion ? id_legion : null,
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

            // Limpiar formulario Agregar Usuario al cerrar el offcanvas (Cancelar o X)
            function limpiarFormularioAgregarUsuario() {
                const ids = ['add_nombres', 'add_apellidop', 'add_apellidom', 'add_telefono', 'add_usuario', 'add_contrasena', 'add_num_telefono'];
                ids.forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.value = '';
                });
                // Limpiar campo de fecha (Flatpickr)
                const fechaInput = document.getElementById('add_fecha_ingreso');
                if (fechaInput) {
                    if (fechaInput._flatpickr) {
                        fechaInput._flatpickr.clear();
                    } else {
                        fechaInput.value = '';
                    }
                }
                const addDepartamento = document.getElementById('add_departamento_id');
                if (addDepartamento) addDepartamento.value = '';
                const addPuesto = document.getElementById('add_id_puesto');
                if (addPuesto) { addPuesto.value = ''; addPuesto.disabled = true; addPuesto.innerHTML = '<option value="">Seleccione un puesto</option>'; }
                const addJefe = document.getElementById('add_id_jefe');
                if (addJefe) { addJefe.value = ''; addJefe.disabled = true; addJefe.innerHTML = '<option value="">Seleccione un jefe</option>'; }
                const addLegion = document.getElementById('add_id_legion');
                if (addLegion) addLegion.value = '';
                const addAsignarLegion = document.getElementById('add_asignar_legion');
                if (addAsignarLegion) addAsignarLegion.checked = false;
                const divLegion = document.getElementById('div_select_legion');
                if (divLegion) divLegion.style.display = 'none';
            }
            
            $(document).ready(() => {
                // Inicializar DataTable con columnas explícitas
                configuraTabla("#historialUsuarios", { registrosPorPagina: 10 });
                getUsuarios();

                // Flatpickr fecha+hora en modal de ausencias (calendario moderno)
                $("#modalAuscencia").on("shown.bs.modal", function() { initFlatpickrAusencia(); });

                // Configurar Flatpickr para fecha de ingreso (máximo: hoy + 1 día) cuando se abre el offcanvas
                const offcanvasAdd = document.getElementById('offcanvasAddUser');
                if (offcanvasAdd) {
                    offcanvasAdd.addEventListener('shown.bs.offcanvas', function() {
                        // Inicializar Flatpickr cuando se abre el offcanvas
                        configurarMaxFechaIngreso();
                    });
                    offcanvasAdd.addEventListener('hidden.bs.offcanvas', limpiarFormularioAgregarUsuario);
                }
            });
        
        // Función para inicializar vista de bajas (se llama desde bajas())
        function inicializarBajas() {
            // Ocultar filtros y botón agregar
            $('.card-header.border-bottom').hide();
            $('.row.justify-content-between.m-4').hide();
            
            // Inicializar DataTable con las nuevas columnas
            const tabla = configuraTabla("#historialUsuarios", { 
                registrosPorPagina: 10,
                columns: [
                    { data: null, defaultContent: '', className: 'control', orderable: false },
                    { data: 'nombres', title: 'Nombres' },
                    { data: 'puesto', title: 'Puesto' },
                    { data: 'estatus', title: 'Estatus' },
                    { data: 'motivos', title: 'Motivos de baja' },
                    { data: 'usuario', title: 'Usuario' },
                    { data: 'acciones', title: 'Acciones', orderable: false }
                ]
            });
            
            // Asegurar que el select muestre el valor correcto después de la inicialización
            // Usamos drawCallback para asegurar que se ejecute después del renderizado
            tabla.on('draw.dt', function() {
                const select = $('select[name="historialUsuarios_length"]');
                if (select.length && select.val() !== '10') {
                    select.val('10');
                }
            });
            
            // También lo establecemos inmediatamente después de la inicialización
            setTimeout(function() {
                const select = $('select[name="historialUsuarios_length"]');
                if (select.length) {
                    select.val('10');
                }
            }, 50);
            
            getBajas();
        }
        
        // Función placeholder para editar baja (por ahora no hace nada)
        function editarBaja(registroBaja) {
            Swal.fire({
                icon: 'warning',
                title: '¡Espera!',
                html: `
                    <p style="font-size: 18px;">No me toques por favor 😅</p>
                    <p style="font-size: 16px; margin-top: 10px;">Soy mucho botón para tu click, jejej</p>
                    <p style="font-size: 14px; margin-top: 10px; color: #666;">Por ahora solo soy decorativo</p>
                `,
                confirmButtonText: 'Ok, entendido',
                confirmButtonColor: '#3085d6'
            });
        }
            
            
            let archivosSeleccionados = [];

            function seleccionarArchivoBajaModal() {
                document.getElementById("archivoPDF").click();
            }
            
            function verArchivoCargadoBajaModal(index) {
                const file = archivosSeleccionados[index];
                if (file) {
                    const url = URL.createObjectURL(file);
                    window.open(url, "_blank");
                }
            }
            
            document.getElementById("archivoPDF").addEventListener("change", function (e) {
                const nuevosArchivos = Array.from(e.target.files || []);
                nuevosArchivos.forEach(file => {
                    if (file.type !== "application/pdf") return;
                    archivosSeleccionados.push(file);
                });
                const spanBaja = document.getElementById("bajaModal_nombreArchivo");
                if (spanBaja) spanBaja.textContent = archivosSeleccionados.length > 0 ? archivosSeleccionados.length + " archivo(s) seleccionado(s)" : "No se ha seleccionado ningún archivo";
                renderArchivos();
                this.value = "";
            });
            
            function renderArchivos() {
                const lista = document.getElementById("listaArchivos");
                const spanBaja = document.getElementById("bajaModal_nombreArchivo");
                if (!lista) return;
                lista.innerHTML = "";
                if (archivosSeleccionados.length === 0) {
                    lista.style.display = "none";
                    if (spanBaja) spanBaja.textContent = "No se ha seleccionado ningún archivo";
                    return;
                }
                lista.style.display = "block";
                if (spanBaja) spanBaja.textContent = archivosSeleccionados.length + " archivo(s) seleccionado(s)";
                archivosSeleccionados.forEach((file, index) => {
                    lista.innerHTML += `
                        <div class="d-flex align-items-center justify-content-between p-2 mb-2 border rounded" style="background-color: #f8f9fa;">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fa fa-file-pdf text-danger"></i>
                                <span>${file.name}</span>
                                <span class="badge bg-success rounded-pill"><i class="fa fa-check"></i></span>
                            </div>
                            <div class="d-flex gap-1">
                                <button type="button" class="btn btn-sm btn-info p-1" onclick="verArchivoCargadoBajaModal(${index})" title="Ver archivo" style="width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fa fa-eye" style="font-size: 12px;"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger p-1" onclick="eliminarArchivo(${index})" title="Eliminar archivo" style="width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fa fa-times" style="font-size: 12px;"></i>
                                </button>
                            </div>
                        </div>
                    `;
                });
            }
            
            function eliminarArchivo(index) {
                archivosSeleccionados.splice(index, 1);
                renderArchivos();
            }

            // ==========================================
            // FUNCIONES PARA CARGAR DOCUMENTOS DE PERSONA (GESTIÓN)
            // ==========================================
            
            // Array para almacenar archivos seleccionados (Gestión)
            let archivosSeleccionadosPersona = [];
            let archivosSubidosPersona = [];
            
            // Alias para el botón "Ver archivo" de la tabla (recibe id de persona)
            function verArchivo(idPersona) {
                cargarDocumentoPersona(idPersona);
            }
            
            // Función para abrir modal de cargar documento de persona
            function cargarDocumentoPersona(button) {
                let idPersona, nombreCompleto;
                const esIdDirecto = typeof button === 'number' || (typeof button === 'string' && button !== '' && !isNaN(Number(button)));
                
                if (esIdDirecto) {
                    idPersona = String(button);
                    nombreCompleto = 'N/A';
                } else {
                    let btnElement = button;
                    if (!button || typeof button.getAttribute !== 'function') {
                        if (typeof event !== 'undefined' && event && event.target) {
                            btnElement = event.target.closest('button');
                        } else if (typeof button === 'string' || typeof button === 'number') {
                            btnElement = document.querySelector(`[data-id-persona="${button}"]`);
                        }
                        if (!btnElement || typeof btnElement.getAttribute !== 'function') {
                            console.error('No se pudo obtener el elemento del botón:', button);
                            return;
                        }
                    }
                    idPersona = btnElement.getAttribute('data-id-persona');
                    nombreCompleto = btnElement.getAttribute('data-nombre') || '';
                    if (!idPersona) {
                        console.error('No se encontró el ID de persona en el botón');
                        return;
                    }
                }
                
                // Guardar el ID de persona en un campo oculto del modal
                document.getElementById('cargarDocPersona_idPersona').value = idPersona || '';
                document.getElementById('cargarDocPersona_nombrePersona').textContent = 'Persona: ' + (nombreCompleto || 'N/A');
                
                // Limpiar el select y el input de archivo
                const selectTipo = document.getElementById('cargarDocPersona_tipoDocumento');
                selectTipo.value = '';
                document.getElementById('cargarDocPersona_archivo').value = '';
                document.getElementById('cargarDocPersona_nombreArchivo').textContent = 'No se ha seleccionado ningún archivo';
                
                // Limpiar la lista de archivos nuevos
                archivosSeleccionadosPersona = [];
                document.getElementById('cargarDocPersona_listaArchivos').style.display = 'none';
                
                // Primero resetear todas las opciones del select para que sean visibles
                Array.from(selectTipo.options).forEach(option => {
                    option.style.display = 'block';
                    option.disabled = false;
                });
                
                // Cargar archivos existentes (esto actualizará el select automáticamente ocultando los únicos ya subidos)
                if (idPersona) {
                    cargarArchivosExistentesPersona(idPersona);
                } else {
                    // Si no hay ID, al menos actualizar el select para mostrar todas las opciones
                    actualizarSelectDocumentos();
                }
                
                // Actualizar el atributo multiple del input file según el tipo de documento
                const inputFile = document.getElementById('cargarDocPersona_archivo');
                
                // Remover listeners anteriores si existen para evitar duplicados
                const nuevoSelectTipo = selectTipo.cloneNode(true);
                selectTipo.parentNode.replaceChild(nuevoSelectTipo, selectTipo);
                
                // Actualizar la referencia al nuevo elemento
                const selectTipoActualizado = document.getElementById('cargarDocPersona_tipoDocumento');
                
                selectTipoActualizado.addEventListener('change', function() {
                    const tipoDoc = this.value;
                    if (tipoDoc && !permiteMultiplesArchivos(tipoDoc)) {
                        inputFile.setAttribute('multiple', 'false');
                        inputFile.removeAttribute('multiple');
                    } else {
                        inputFile.setAttribute('multiple', 'multiple');
                    }
                    
                    // Limpiar archivos seleccionados cuando cambia el tipo
                    archivosSeleccionadosPersona = [];
                    inputFile.value = '';
                    document.getElementById('cargarDocPersona_nombreArchivo').textContent = 'No se ha seleccionado ningún archivo';
                    document.getElementById('cargarDocPersona_listaArchivos').style.display = 'none';
                });
                
                // Mostrar modal
                const modal = new bootstrap.Modal(document.getElementById('modalCargarDocumentoPersona'));
                modal.show();
            }
            
            // Función para seleccionar archivo
            function seleccionarArchivoDocumentoPersona() {
                document.getElementById('cargarDocPersona_archivo').click();
            }
            
            // Tipos de documentos que solo permiten un archivo
            const documentosUnicos = [
                'Acta de Nacimiento',
                'Certificado de Estudios',
                'Comprobante de Domicilio',
                'CURP',
                'Identificación Oficial (INE)',
                'RFC'
            ];
            
            // Función para verificar si un tipo de documento permite múltiples archivos
            function permiteMultiplesArchivos(tipoDocumento) {
                return !documentosUnicos.includes(tipoDocumento);
            }
            
            // Función para agregar archivo a la lista
            function agregarArchivoListaPersona(input) {
                const tipoDocumento = document.getElementById('cargarDocPersona_tipoDocumento').value;
                
                if (!tipoDocumento) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atención',
                        text: 'Por favor selecciona primero un tipo de documento'
                    });
                    input.value = '';
                    return;
                }
                
                // Verificar si ya existe un documento de este tipo subido
                const esUnico = !permiteMultiplesArchivos(tipoDocumento);
                if (esUnico) {
                    // Verificar si ya hay un archivo de este tipo en los archivos subidos
                    const idDocumento = obtenerIdDocumentoPorNombre(tipoDocumento);
                    const existeDocumento = archivosSubidosPersona.some(doc => doc.id_documento === idDocumento);
                    
                    if (existeDocumento) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Documento ya existe',
                            text: 'Este tipo de documento solo permite un archivo. Por favor elimina el existente antes de subir uno nuevo.'
                        });
                        input.value = '';
                        return;
                    }
                    
                    // Verificar si ya hay un archivo seleccionado de este tipo
                    if (archivosSeleccionadosPersona.length > 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Solo un archivo permitido',
                            text: 'Este tipo de documento solo permite un archivo. Por favor elimina el archivo seleccionado antes de agregar otro.'
                        });
                        input.value = '';
                        return;
                    }
                }
                
                if (input.files && input.files.length > 0) {
                    const archivosValidos = [];
                    
                    Array.from(input.files).forEach(file => {
                        if (file.type === 'application/pdf') {
                            // Si es documento único, solo permitir un archivo
                            if (esUnico && archivosSeleccionadosPersona.length >= 1) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Solo un archivo permitido',
                                    text: 'Este tipo de documento solo permite un archivo.'
                                });
                                return;
                            }
                            archivosValidos.push(file);
                        }
                    });
                    
                    // Agregar archivos válidos
                    archivosValidos.forEach(file => {
                        archivosSeleccionadosPersona.push(file);
                    });
                    
                    // Actualizar contador
                    const count = archivosSeleccionadosPersona.length;
                    document.getElementById('cargarDocPersona_nombreArchivo').textContent = 
                        count > 0 ? `${count} archivo(s) seleccionado(s)` : 'No se ha seleccionado ningún archivo';
                    
                    // Renderizar lista
                    renderArchivosSubidosPersona();
                }
            }
            
            // Función auxiliar para obtener ID de documento (usando IDs reales de la BD)
            function obtenerIdDocumentoPorNombre(nombre) {
                const mapaDocumentos = {
                    'CURP': 8,
                    'Identificación Oficial (INE)': 9,
                    'RFC': 10,
                    'Comprobante de Domicilio': 11,
                    'Acta de Nacimiento': 12,
                    'Certificado de Estudios': 13,
                    'Referencias Laborales': 14,
                    'Documento baja': 15,
                    'Documento Baja': 15  // Compatibilidad con mayúscula
                };
                return mapaDocumentos[nombre] || null;
            }
            
            // Función para renderizar archivos
            function renderArchivosSubidosPersona() {
                const listaArchivos = document.getElementById('cargarDocPersona_listaArchivos');
                const tablaArchivos = document.getElementById('cargarDocPersona_tablaArchivos');
                
                // Renderizar tabla de archivos subidos
                if (archivosSubidosPersona.length > 0) {
                    let htmlTabla = '';
                    archivosSubidosPersona.forEach(doc => {
                        const fechaFormateada = doc.fecha_carga || 'N/A';
                        const archivoEscapado = (doc.archivo || '').replace(/'/g, "\\'");
                        
                        htmlTabla += `
                            <tr>
                                <td>${obtenerNombreDocumento(doc.id_documento)}</td>
                                <td>${doc.archivo || 'N/A'}</td>
                                <td>${fechaFormateada}</td>
                                <td>
                                    <span class="badge bg-success">Sí</span>
                                </td>
                                <td>
                                    <button 
                                        type="button" 
                                        class="btn btn-sm btn-info me-1" 
                                        onclick="verArchivoSubidoPersona('${archivoEscapado}')" 
                                        title="Ver archivo"
                                    >
                                        Ver
                                    </button>
                                    <button 
                                        type="button" 
                                        class="btn btn-sm btn-danger" 
                                        onclick="eliminarArchivoSubidoPersona(${doc.id}, '${archivoEscapado}')" 
                                        title="Eliminar archivo"
                                    >
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                    tablaArchivos.innerHTML = htmlTabla;
                } else {
                    tablaArchivos.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No hay archivos subidos</td></tr>';
                }
                
                // Renderizar lista de archivos nuevos seleccionados (antes de subir)
                if (archivosSeleccionadosPersona.length > 0) {
                    listaArchivos.style.display = 'block';
                    let htmlLista = '';
                    archivosSeleccionadosPersona.forEach((file, index) => {
                        htmlLista += `
                            <div class="d-flex align-items-center justify-content-between p-2 mb-2 border rounded" style="background-color: #f8f9fa;">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fa fa-file-pdf text-danger"></i>
                                    <span>${file.name}</span>
                                    <span class="badge bg-success rounded-pill">
                                        <i class="fa fa-check"></i>
                                    </span>
                                </div>
                                <div class="d-flex gap-1">
                                    <button 
                                        type="button" 
                                        class="btn btn-sm btn-info p-1" 
                                        onclick="verArchivoCargadoPersona(${index})" 
                                        title="Ver archivo"
                                        style="width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;"
                                    >
                                        <i class="fa fa-eye" style="font-size: 12px;"></i>
                                    </button>
                                    <button 
                                        type="button" 
                                        class="btn btn-sm btn-danger p-1" 
                                        onclick="eliminarArchivoCargadoPersona(${index})" 
                                        title="Eliminar archivo"
                                        style="width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;"
                                    >
                                        <i class="fa fa-times" style="font-size: 12px;"></i>
                                    </button>
                                </div>
                            </div>
                        `;
                    });
                    listaArchivos.innerHTML = htmlLista;
                } else {
                    listaArchivos.style.display = 'none';
                }
            }
            
            // Función para obtener nombre del documento por ID (IDs reales de la BD)
            function obtenerNombreDocumento(idDocumento) {
                const mapeo = {
                    8: 'CURP',
                    9: 'Identificación Oficial (INE)',
                    10: 'RFC',
                    11: 'Comprobante de Domicilio',
                    12: 'Acta de Nacimiento',
                    13: 'Certificado de Estudios',
                    14: 'Referencias Laborales',
                    15: 'Documento baja'
                };
                return mapeo[idDocumento] || 'Documento';
            }
            
            // Función para actualizar el select de documentos, ocultando los únicos que ya están subidos
            function actualizarSelectDocumentos() {
                const selectTipo = document.getElementById('cargarDocPersona_tipoDocumento');
                if (!selectTipo) return;
                
                // Obtener todos los IDs de documentos únicos que ya están subidos
                const documentosUnicosSubidos = new Set();
                archivosSubidosPersona.forEach(doc => {
                    const idDoc = doc.id_documento;
                    // Verificar si este ID corresponde a un documento único
                    const nombreDoc = obtenerNombreDocumento(idDoc);
                    if (nombreDoc && !permiteMultiplesArchivos(nombreDoc)) {
                        documentosUnicosSubidos.add(nombreDoc);
                    }
                });
                
                // Verificar si el valor actual del select es un documento único ya subido
                const valorActual = selectTipo.value;
                if (valorActual && !permiteMultiplesArchivos(valorActual) && documentosUnicosSubidos.has(valorActual)) {
                    // Si el documento seleccionado es único y ya está subido, limpiar el select
                    selectTipo.value = '';
                }
                
                // Recorrer todas las opciones del select
                Array.from(selectTipo.options).forEach(option => {
                    const valor = option.value;
                    // Si es un documento único y ya está subido, ocultarlo
                    if (valor && !permiteMultiplesArchivos(valor) && documentosUnicosSubidos.has(valor)) {
                        option.style.display = 'none';
                        option.disabled = true;
                    } else {
                        option.style.display = 'block';
                        option.disabled = false;
                    }
                });
            }
            
            // Función para cargar archivos existentes
            function cargarArchivosExistentesPersona(idPersona) {
                fetch('/caphum/getDocumentosPersona?id_persona=' + idPersona)
                    .then(res => res.json())
                    .then(resp => {
                        if (resp.success && resp.datos) {
                            archivosSubidosPersona = resp.datos;
                            renderArchivosSubidosPersona();
                            // Actualizar el select después de cargar los archivos
                            actualizarSelectDocumentos();
                        } else {
                            archivosSubidosPersona = [];
                            renderArchivosSubidosPersona();
                            actualizarSelectDocumentos();
                        }
                    })
                    .catch(err => {
                        console.error('Error al cargar archivos:', err);
                        archivosSubidosPersona = [];
                        renderArchivosSubidosPersona();
                        actualizarSelectDocumentos();
                    });
            }
            
            // Función para eliminar archivo cargado (nuevo, antes de subir)
            function eliminarArchivoCargadoPersona(index) {
                archivosSeleccionadosPersona.splice(index, 1);
                const count = archivosSeleccionadosPersona.length;
                document.getElementById('cargarDocPersona_nombreArchivo').textContent = 
                    count > 0 ? `${count} archivo(s) seleccionado(s)` : 'No se ha seleccionado ningún archivo';
                renderArchivosSubidosPersona();
            }
            
            // Función para eliminar archivo subido (ya en BD)
            function eliminarArchivoSubidoPersona(idDocumento, nombreArchivo) {
                Swal.fire({
                    title: '¿Eliminar archivo?',
                    text: '¿Estás seguro de que deseas eliminar "' + nombreArchivo + '"?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = new FormData();
                        formData.append('id_documento', idDocumento);
                        
                        fetch('/caphum/eliminarDocumentoPersona', {
                            method: 'POST',
                            body: formData
                        })
                        .then(res => res.json())
                        .then(resp => {
                            if (resp.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Archivo eliminado',
                                    text: 'El archivo fue eliminado correctamente',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                
                                // Recargar lista (esto también actualizará el select)
                                const idPersona = document.getElementById('cargarDocPersona_idPersona').value;
                                if (idPersona) {
                                    cargarArchivosExistentesPersona(idPersona);
                                }
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: resp.mensaje || 'No se pudo eliminar el archivo'
                                });
                            }
                        })
                        .catch(err => {
                            console.error('Error:', err);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Ocurrió un error al eliminar el archivo'
                            });
                        });
                    }
                });
            }
            
            // Función para ver un archivo nuevo (no subido)
            function verArchivoCargadoPersona(index) {
                const file = archivosSeleccionadosPersona[index];
                if (file) {
                    const url = URL.createObjectURL(file);
                    window.open(url, '_blank');
                }
            }
            
            // Función para ver un archivo ya subido
            function verArchivoSubidoPersona(nombreArchivo) {
                const url = '/caphum/verDocumentoPersona?archivo=' + encodeURIComponent(nombreArchivo);
                window.open(url, '_blank');
            }
            
            // Mapeo directo de nombres de documentos a IDs (igual que en Bajas)
            const mapaDocumentosIds = {
                'CURP': 8,
                'Identificación Oficial (INE)': 9,
                'RFC': 10,
                'Comprobante de Domicilio': 11,
                'Acta de Nacimiento': 12,
                'Certificado de Estudios': 13,
                'Referencias Laborales': 14,
                'Documento baja': 15
            };
            
            // Función para subir documento de persona
            function subirDocumentoPersona() {
                const tipoDocumento = document.getElementById('cargarDocPersona_tipoDocumento').value;
                const idPersona = document.getElementById('cargarDocPersona_idPersona').value;
                
                if (!tipoDocumento) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atención',
                        text: 'Por favor selecciona un tipo de documento'
                    });
                    return;
                }
                
                // Obtener ID del documento usando el mapeo directo
                const idDocumento = mapaDocumentosIds[tipoDocumento];
                if (!idDocumento) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Tipo de documento no válido: ' + tipoDocumento
                    });
                    return;
                }
                
                if (archivosSeleccionadosPersona.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atención',
                        text: 'Por favor selecciona al menos un archivo'
                    });
                    return;
                }
                
                if (!idPersona) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se encontró el ID de la persona'
                    });
                    return;
                }
                
                // Mostrar loading
                Swal.fire({
                    title: 'Subiendo archivos...',
                    text: 'Por favor espera',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Crear FormData
                const formData = new FormData();
                formData.append('id_persona', idPersona);
                formData.append('id_documento', idDocumento);  // Enviar ID directamente
                
                // Agregar archivos
                archivosSeleccionadosPersona.forEach((file) => {
                    formData.append('archivosPDF[]', file);
                });
                
                // Enviar al servidor
                fetch('/caphum/subirDocumentosPersona', {
                    method: 'POST',
                    body: formData
                })
                .then(res => {
                    // Verificar si la respuesta es JSON válido
                    const contentType = res.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        return res.json();
                    } else {
                        // Si no es JSON, leer como texto para ver el error
                        return res.text().then(text => {
                            console.error('Respuesta no JSON:', text);
                            throw new Error('El servidor devolvió una respuesta no válida. Ver consola para más detalles.');
                        });
                    }
                })
                .then(resp => {
                    Swal.close();
                    
                    if (resp.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Archivos subidos',
                            text: 'Se subieron ' + archivosSeleccionadosPersona.length + ' archivo(s) correctamente',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        
                        // Limpiar archivos seleccionados
                        archivosSeleccionadosPersona = [];
                        document.getElementById('cargarDocPersona_archivo').value = '';
                        document.getElementById('cargarDocPersona_nombreArchivo').textContent = 'No se ha seleccionado ningún archivo';
                        document.getElementById('cargarDocPersona_listaArchivos').style.display = 'none';
                        
                        // Si es un documento único, limpiar el select de tipo de documento
                        // (Referencias Laborales y Documento baja permiten múltiples, así que no se limpian)
                        if (!permiteMultiplesArchivos(tipoDocumento)) {
                            const selectTipoDoc = document.getElementById('cargarDocPersona_tipoDocumento');
                            if (selectTipoDoc) {
                                selectTipoDoc.value = '';
                            }
                        }
                        
                        // Recargar lista de archivos (esto también actualizará el select ocultando los únicos ya subidos)
                        cargarArchivosExistentesPersona(idPersona);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: resp.mensaje || 'No se pudieron subir los archivos'
                        });
                    }
                })
                .catch(err => {
                    Swal.close();
                    console.error('Error al subir archivos:', err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrió un error al subir los archivos: ' + (err.message || 'Error desconocido')
                    });
                });
            }
            
        </script>
        HTML;
        $departamento = CapHumDAO::getConsultaDepartamentoGestor($_SESSION['usuario_id']);
        $modulos = $_SESSION['modulos'] ?? [];
        $puedeEditarTodos = in_array(10, $modulos);

        self::set("titulo", "Gestión de Usuarios");
        self::set("script", $script);
        self::set("departamento", $departamento);
        self::set("miUsuarioId", (int) $_SESSION['usuario_id']);
        self::set("puedeEditarTodos", $puedeEditarTodos);
        self::render("all_gestores");
    }
    public function bajas()
    {
        // Reutilizar el mismo script de gestion() pero cambiando solo la inicialización
        // Primero obtenemos el script de gestion() y lo modificamos
        $scriptGestion = <<<'HTML'
        <script>
        
            const getUsuarios = () => {
            http.request({
                endpoint: "/caphum/getUsuarios",
                onSuccess: (resp) => {
                    // Mapear datos como objeto para usar 'columns.data' en DataTables
                    const datos = resp.datos.map(p => ({
                        nombre: `
                            <div class="fw-semibold">
                               # ${p.numero_empleado}
                            </div>
                            <div class="fw-semibold">
                                ${[p.nombres, p.segundo_nombre, p.apellidop, p.apellidom].filter(x => x).join(' ')}
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
                            <button class="btn btn-sm btn-info" onclick="verArchivo(${p.id})" title="Ver archivo">
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
        
                    // Actualizar DataTable
                    const tabla = $('#historialUsuarios').DataTable();
                    tabla.clear().rows.add(datos).draw();
                }
            });
        };
        
            // Variable global para almacenar el rango de fechas seleccionado
            let rangoFechasBajas = null;
            
            const getBajas = () => {
                // Preparar parámetros con filtro de fecha si existe
                const params = {};
                if (rangoFechasBajas) {
                    params.fecha_inicio = rangoFechasBajas.inicio;
                    params.fecha_fin = rangoFechasBajas.fin;
                }
                
                console.log('Enviando parámetros de fecha:', params);
                
                // Enviar como JSON usando fetch directamente
                fetch('/caphum/getBajas', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(params)
                })
                .then(res => {
                    if (!res.ok) {
                        return res.text().then(text => {
                            console.error('Error HTTP:', res.status, text);
                            throw new Error(`HTTP error! status: ${res.status}`);
                        });
                    }
                    return res.json();
                })
                .then(resp => {
                    console.log('Respuesta recibida:', resp);
                    
                    // Si la respuesta no tiene success o es false, mostrar error
                    if (!resp || resp.success === false) {
                        const mensajeError = resp?.mensaje || resp?.error || "No se pudieron cargar las bajas";
                        console.error('Error en respuesta:', mensajeError);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: mensajeError,
                            confirmButtonText: 'Aceptar'
                        });
                        return;
                    }
                    
                    // Verificar que resp.datos existe y es un array
                    if (!resp.datos || !Array.isArray(resp.datos)) {
                        console.error('resp.datos no es un array:', resp.datos);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: "Formato de respuesta inválido",
                            confirmButtonText: 'Aceptar'
                        });
                        return;
                    }
                    
                    // Mapear datos con el nuevo formato de columnas
                    const datos = resp.datos.map(p => {
                        const nombreCompleto = [p.nombres, p.segundo_nombre, p.apellidop, p.apellidom].filter(x => x).join(' ');
                        
                        return {
                            nombres: `
                                <div class="fw-semibold d-flex align-items-center gap-2">
                                    <i class="fa fa-hashtag" style="font-size: 0.85em; color: #333;"></i>
                                    <span>${p.external_id ?? ''}</span>
                                </div>
                                <div class="fw-semibold d-flex align-items-center gap-2 mt-2">
                                    <i class="fa fa-user" style="font-size: 0.85em; color: #333;"></i>
                                    <span>${nombreCompleto}</span>
                                </div>
                                <div class="text-muted small d-flex align-items-center gap-2 mt-1">
                                    <i class="fa fa-id-card" style="font-size: 0.85em; color: #666;"></i>
                                    <span># ${p.numero_empleado ?? ''}</span>
                                </div>
                            `.trim(),
                            puesto: `
                                <div class="text-muted small d-flex align-items-center gap-2">
                                    <i class="fa fa-building" style="font-size: 0.85em; color: #333;"></i>
                                    <span>${p.departamento ?? 'N/A'}</span>
                                </div>
                                <div class="text-muted small d-flex align-items-center gap-2 mt-1">
                                    <i class="fa fa-briefcase" style="font-size: 0.85em; color: #333;"></i>
                                    <span>${p.nombre_puesto ?? 'N/A'}</span>
                                </div>
                            `.trim(),
                            estatus: `
                                <div class="fw-semibold text-danger d-flex align-items-center gap-2">
                                    <i class="fa fa-ban"></i>
                                    <span>Baja</span>
                                </div>
                                <div class="text-muted small d-flex align-items-center gap-2 mt-1">
                                    <i class="fa fa-calendar" style="font-size: 0.85em; color: #666;"></i>
                                    <span>${p.fecha_baja ? new Date(p.fecha_baja).toLocaleDateString('es-MX') : 'N/A'}</span>
                                </div>
                                <div class="text-muted small d-flex align-items-center gap-2 mt-1">
                                    <i class="fa fa-file-alt" style="font-size: 0.85em; color: #666;"></i>
                                    <span>Registro: ${p.registro_baja ?? 'N/A'}</span>
                                </div>
                            `.trim(),
                            motivos: `
                                <div class="fw-semibold d-flex align-items-center gap-2">
                                    <i class="fa fa-exclamation-triangle" style="font-size: 0.85em; color: #333;"></i>
                                    <span>${p.motivo ?? 'N/A'}</span>
                                </div>
                                <div class="text-muted small d-flex align-items-center gap-2 mt-1">
                                    <i class="fa fa-clipboard" style="font-size: 0.85em; color: #666;"></i>
                                    <span>${p.descripcion ?? 'Sin descripción'}</span>
                                </div>
                            `.trim(),
                            usuario: p.user_name ?? 'N/A',
                            acciones: `
                                <button class="btn btn-sm btn-info me-1" onclick="cargarDocumentoBaja(this)" 
                                    data-registro-baja="${p.registro_baja ?? ''}" 
                                    data-nombre="${nombreCompleto.replace(/"/g, '&quot;')}" 
                                    title="Cargar documento">
                                    <i class="fa fa-file"></i>
                                </button>
                            `.trim()
                        };
                    });
                    
                    // Actualizar KPIs de Bajas
                    actualizarIndicadoresBajas(resp.datos, rangoFechasBajas !== null);
        
                    // Actualizar DataTable
                    const tabla = $('#historialUsuarios').DataTable();
                    if (tabla) {
                        tabla.clear();
                        if (datos.length > 0) {
                            tabla.rows.add(datos).draw();
                        } else {
                            tabla.draw();
                            Swal.fire({
                                icon: 'info',
                                title: 'Sin resultados',
                                text: 'No se encontraron bajas en el rango de fechas seleccionado',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                    }
                })
                .catch(err => {
                    console.error('Error al cargar bajas:', err);
                    Swal.fire("Error", "No se pudieron cargar las bajas: " + err.message, "error");
                });
            };
            
            // Función para limpiar el filtro de fecha
            function limpiarFiltroBajas() {
                // Limpiar la variable global
                rangoFechasBajas = null;
                
                // Limpiar el input de flatpickr
                const flatpickrInput = document.getElementById('flatpickr-range-bajas');
                if (flatpickrInput) {
                    if (flatpickrInput._flatpickr) {
                        flatpickrInput._flatpickr.clear();
                    }
                    // También limpiar el valor del input directamente
                    flatpickrInput.value = '';
                }
                
                // Remover clase active de todos los botones de filtro rápido
                document.querySelectorAll('.btn-filtro-rapido').forEach(btn => {
                    btn.classList.remove('active');
                });
                
                console.log('Filtro limpiado, recargando todas las bajas...');
                // Recargar todas las bajas sin filtro
                getBajas();
            }
            
            // Función para descargar las bajas en Excel
            function descargarBajasExcel() {
                // Preparar parámetros con filtro de fecha si existe (igual que getBajas)
                const params = {};
                if (rangoFechasBajas) {
                    params.fecha_inicio = rangoFechasBajas.inicio;
                    params.fecha_fin = rangoFechasBajas.fin;
                }
                
                // Construir URL con parámetros
                let url = '/caphum/descargarBajasExcel';
                const queryParams = new URLSearchParams();
                if (params.fecha_inicio) queryParams.append('fecha_inicio', params.fecha_inicio);
                if (params.fecha_fin) queryParams.append('fecha_fin', params.fecha_fin);
                
                if (queryParams.toString()) {
                    url += '?' + queryParams.toString();
                }
                
                // Mostrar mensaje de carga
                Swal.fire({
                    title: 'Generando Excel...',
                    text: 'Por favor espera mientras se genera el archivo',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Descargar el archivo
                window.location.href = url;
                
                // Cerrar el mensaje después de un momento
                setTimeout(() => {
                    Swal.close();
                }, 2000);
            }
            
            // Función para inicializar vista de bajas
            function inicializarBajas() {
                // Ocultar filtros y botón agregar
                $('.card-header.border-bottom').hide();
                $('.row.justify-content-between.m-4').hide();
                
                // Ocultar panel de indicadores normal y mostrar panel de indicadores de Bajas
                $('.row.m-4.mb-3').hide();  // Ocultar el panel de KPIs original
                $('#panelIndicadoresBajas').show();  // Mostrar el panel de KPIs de Bajas
                
                // Mostrar filtro de fecha
                $('#filtroFechaBajas').show();
                
                // Inicializar flatpickr para rango de fechas
                const flatpickrInput = document.getElementById('flatpickr-range-bajas');
                if (flatpickrInput && typeof flatpickr !== 'undefined') {
                    // Obtener fecha de hoy para limitar el máximo
                    const hoy = new Date();
                    hoy.setHours(23, 59, 59, 999); // Fin del día de hoy
                    
                    flatpickr(flatpickrInput, {
                        mode: 'range',
                        dateFormat: 'Y-m-d',
                        maxDate: hoy, // No permitir fechas futuras
                        locale: {
                            firstDayOfWeek: 1,
                            weekdays: {
                                shorthand: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                                longhand: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado']
                            },
                            months: {
                                shorthand: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                                longhand: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']
                            },
                            rangeSeparator: ' a '
                        },
                        onChange: function(selectedDates, dateStr, instance) {
                            if (selectedDates.length === 2) {
                                // Remover clase active de todos los botones (uso manual)
                                document.querySelectorAll('.btn-filtro-rapido').forEach(btn => {
                                    btn.classList.remove('active');
                                });
                                
                                rangoFechasBajas = {
                                    inicio: selectedDates[0].toISOString().split('T')[0],
                                    fin: selectedDates[1].toISOString().split('T')[0]
                                };
                                console.log('Rango de fechas seleccionado:', rangoFechasBajas);
                                // Recargar datos con el filtro de fecha
                                getBajas();
                            } else if (selectedDates.length === 0) {
                                rangoFechasBajas = null;
                                console.log('Filtro de fecha limpiado');
                                // Recargar datos sin filtro
                                getBajas();
                            }
                        }
                    });
                }
                
                // Agregar evento al botón limpiar
                const btnLimpiar = document.getElementById('btnLimpiarFiltroBajas');
                if (btnLimpiar) {
                    btnLimpiar.addEventListener('click', limpiarFiltroBajas);
                }
                
                // Agregar evento al botón descargar
                const btnDescargar = document.getElementById('btnDescargarBajas');
                if (btnDescargar) {
                    btnDescargar.addEventListener('click', descargarBajasExcel);
                }
                
                // Inicializar DataTable con las nuevas columnas
                const tabla = configuraTabla("#historialUsuarios", { 
                    registrosPorPagina: 10,
                    columns: [
                        { data: null, defaultContent: '', className: 'control', orderable: false },
                        { data: 'nombres', title: 'Nombres' },
                        { data: 'puesto', title: 'Puesto' },
                        { data: 'estatus', title: 'Estatus' },
                        { data: 'motivos', title: 'Motivos de baja' },
                        { data: 'usuario', title: 'Usuario' },
                        { data: 'acciones', title: 'Acciones', orderable: false }
                    ]
                });
                
                // Buscador de nombres desactivado por ahora
                
                // Asegurar que el select muestre el valor correcto después de la inicialización
                tabla.on('draw.dt', function() {
                    const select = $('select[name="historialUsuarios_length"]');
                    if (select.length && select.val() !== '10') {
                        select.val('10');
                    }
                });
                
                setTimeout(function() {
                    const select = $('select[name="historialUsuarios_length"]');
                    if (select.length) {
                        select.val('10');
                    }
                }, 50);
                
                // Agregar event listeners para los KPIs de Bajas
                const kpiDepartamentos = document.getElementById('kpi-bajas-departamentos');
                if (kpiDepartamentos) {
                    kpiDepartamentos.addEventListener('click', function() {
                        abrirModalBajas('departamentos');
                    });
                }
                
                const kpiPuestos = document.getElementById('kpi-bajas-puestos');
                if (kpiPuestos) {
                    kpiPuestos.addEventListener('click', function() {
                        abrirModalBajas('puestos');
                    });
                }
                
                // Agregar event listeners para los filtros rápidos
                const botonesFiltroRapido = document.querySelectorAll('.btn-filtro-rapido');
                botonesFiltroRapido.forEach(boton => {
                    boton.addEventListener('click', function() {
                        aplicarFiltroRapido(this.getAttribute('data-periodo'));
                    });
                });
                
                getBajas();
            }
            
            // Función para aplicar filtros rápidos
            function aplicarFiltroRapido(periodo) {
                const hoy = new Date();
                let fechaInicio, fechaFin;
                
                // Remover clase active de todos los botones
                document.querySelectorAll('.btn-filtro-rapido').forEach(btn => {
                    btn.classList.remove('active');
                });
                
                // Agregar clase active al botón seleccionado
                const botonActivo = document.querySelector(`[data-periodo="${periodo}"]`);
                if (botonActivo) {
                    botonActivo.classList.add('active');
                }
                
                switch(periodo) {
                    case 'ultimo-mes':
                        fechaInicio = new Date(hoy.getFullYear(), hoy.getMonth() - 1, hoy.getDate());
                        fechaFin = hoy;
                        break;
                    case 'ultimos-3-meses':
                        fechaInicio = new Date(hoy.getFullYear(), hoy.getMonth() - 3, hoy.getDate());
                        fechaFin = hoy;
                        break;
                    case 'ultimos-6-meses':
                        fechaInicio = new Date(hoy.getFullYear(), hoy.getMonth() - 6, hoy.getDate());
                        fechaFin = hoy;
                        break;
                    case 'ano-actual':
                        fechaInicio = new Date(hoy.getFullYear(), 0, 1);
                        fechaFin = hoy;
                        break;
                    case 'todo':
                        rangoFechasBajas = null;
                        const flatpickrInput = document.getElementById('flatpickr-range-bajas');
                        if (flatpickrInput && flatpickrInput._flatpickr) {
                            flatpickrInput._flatpickr.clear();
                        }
                        getBajas();
                        return;
                }
                
                // Formatear fechas
                const formatoFecha = (fecha) => {
                    const year = fecha.getFullYear();
                    const month = String(fecha.getMonth() + 1).padStart(2, '0');
                    const day = String(fecha.getDate()).padStart(2, '0');
                    return `${year}-${month}-${day}`;
                };
                
                rangoFechasBajas = {
                    inicio: formatoFecha(fechaInicio),
                    fin: formatoFecha(fechaFin)
                };
                
                // Actualizar el flatpickr visualmente
                const flatpickrInput = document.getElementById('flatpickr-range-bajas');
                if (flatpickrInput && flatpickrInput._flatpickr) {
                    flatpickrInput._flatpickr.setDate([fechaInicio, fechaFin], false);
                }
                
                console.log('Filtro rápido aplicado:', periodo, rangoFechasBajas);
                
                // Recargar datos con el filtro
                getBajas();
            }
            
            // Array para almacenar archivos ya subidos
            let archivosSubidos = [];
            
            // Función para abrir modal de cargar documento
            function cargarDocumentoBaja(button) {
                // Obtener datos del botón
                const registroBaja = button.getAttribute('data-registro-baja');
                const nombreCompleto = button.getAttribute('data-nombre');
                
                // Guardar el registro de baja en un campo oculto del modal
                document.getElementById('cargarDoc_registroBaja').value = registroBaja || '';
                document.getElementById('cargarDoc_nombrePersona').textContent = 'Persona: ' + (nombreCompleto || 'N/A');
                
                // Limpiar el select y el input de archivo
                document.getElementById('cargarDoc_tipoDocumento').value = 'Documento Baja';
                document.getElementById('cargarDoc_archivo').value = '';
                document.getElementById('cargarDoc_nombreArchivo').textContent = 'No se ha seleccionado ningún archivo';
                
                // Limpiar la lista de archivos nuevos
                archivosSeleccionados = [];
                document.getElementById('cargarDoc_listaArchivos').style.display = 'none';
                
                // Cargar archivos existentes
                if (registroBaja) {
                    cargarArchivosExistentes(registroBaja);
                } else {
                    // Si no hay registro, mostrar tabla vacía
                    document.getElementById('cargarDoc_tablaArchivos').innerHTML = `
                        <tr>
                            <td colspan="5" class="text-center text-muted">No hay archivos subidos</td>
                        </tr>
                    `;
                }
                
                // Abrir el modal
                $('#modalCargarDocumentoBaja').modal('show');
            }
            
            // Función para cargar archivos existentes
            function cargarArchivosExistentes(registroBaja) {
                fetch('/caphum/getDocumentosBaja?registro_baja=' + registroBaja)
                    .then(res => res.json())                
                    .then(resp => {
                        if (resp.success && resp.datos) {
                            archivosSubidos = resp.datos;                                                                                                       
                            renderizarArchivosSubidos();
                        } else {
                            archivosSubidos = [];
                            renderizarArchivosSubidos();
                        }
                    })
                    .catch(err => {
                        console.error('Error al cargar archivos:', err);
                        archivosSubidos = [];
                        renderizarArchivosSubidos();
                    });
            }
            
            // Función para formatear fecha
            function formatearFecha(fechaStr) {
                if (!fechaStr) return 'N/A';
                try {
                    const fecha = new Date(fechaStr);
                    const año = fecha.getFullYear();
                    const mes = String(fecha.getMonth() + 1).padStart(2, '0');
                    const dia = String(fecha.getDate()).padStart(2, '0');
                    const horas = String(fecha.getHours()).padStart(2, '0');
                    const minutos = String(fecha.getMinutes()).padStart(2, '0');
                    return `${año}-${mes}-${dia} ${horas}:${minutos}`;
                } catch (e) {
                    return fechaStr;
                }
            }
            
            // Función para renderizar archivos ya subidos en tabla
            function renderizarArchivosSubidos() {
                const tablaArchivos = document.getElementById('cargarDoc_tablaArchivos');
                const listaArchivos = document.getElementById('cargarDoc_listaArchivos');
                
                // Renderizar tabla de archivos subidos
                if (archivosSubidos.length === 0) {
                    tablaArchivos.innerHTML = `
                        <tr>
                            <td colspan="5" class="text-center text-muted">No hay archivos subidos</td>
                        </tr>
                    `;
                } else {
                    let htmlTabla = '';
                    archivosSubidos.forEach((doc) => {
                        const fechaFormateada = formatearFecha(doc.fecha_carga);
                        // Escapar el nombre del archivo para evitar problemas con comillas
                        const archivoEscapado = (doc.archivo || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
                        htmlTabla += `
                            <tr>
                                <td>Documento Baja</td>
                                <td>${doc.archivo || 'N/A'}</td>
                                <td>${fechaFormateada}</td>
                                <td>
                                    <span class="badge bg-success">Sí</span>
                                </td>
                                <td>
                                    <button 
                                        type="button" 
                                        class="btn btn-sm btn-info me-1" 
                                        onclick="verArchivoSubido('${archivoEscapado}')" 
                                        title="Ver archivo"
                                    >
                                        Ver
                                    </button>
                                    <button 
                                        type="button" 
                                        class="btn btn-sm btn-danger" 
                                        onclick="eliminarArchivoSubido(${doc.id}, '${archivoEscapado}')" 
                                        title="Eliminar archivo"
                                    >
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                    tablaArchivos.innerHTML = htmlTabla;
                }
                
                // Renderizar lista de archivos nuevos seleccionados (antes de subir)
                if (archivosSeleccionados.length > 0) {
                    listaArchivos.style.display = 'block';
                    let htmlLista = '';
                    archivosSeleccionados.forEach((file, index) => {
                        htmlLista += `
                            <div class="d-flex align-items-center justify-content-between p-2 border rounded mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-danger">PDF</span>
                                    <span class="text-truncate" style="max-width: 300px;">${file.name}</span>
                                    <span class="badge bg-success rounded-circle" style="width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; padding: 0;">
                                        <i class="fa fa-check" style="font-size: 10px; color: white;"></i>
                                    </span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <button 
                                        type="button" 
                                        class="btn btn-sm btn-info p-1" 
                                        onclick="verArchivoCargado(${index})" 
                                        title="Ver archivo"
                                        style="width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;"
                                    >
                                        <i class="fa fa-eye" style="font-size: 12px;"></i>
                                    </button>
                                    <button 
                                        type="button" 
                                        class="btn btn-sm btn-danger p-1" 
                                        onclick="eliminarArchivoCargado(${index})" 
                                        title="Eliminar archivo"
                                        style="width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;"
                                    >
                                        <i class="fa fa-times" style="font-size: 12px;"></i>
                                    </button>
                                </div>
                            </div>
                        `;
                    });
                    listaArchivos.innerHTML = '<h6 class="mb-3"><strong>Archivos Nuevos Seleccionados</strong></h6>' + htmlLista;
                } else {
                    listaArchivos.style.display = 'none';
                    listaArchivos.innerHTML = '';
                }
            }
            
            // Array para almacenar los archivos seleccionados
            let archivosSeleccionados = [];
            
            // Función para seleccionar archivo
            function seleccionarArchivoDocumento() {
                document.getElementById('cargarDoc_archivo').click();
            }
            
            // Función para agregar archivos a la lista
            function agregarArchivoLista(input) {
                const listaArchivos = document.getElementById('cargarDoc_listaArchivos');
                const nombreArchivoSpan = document.getElementById('cargarDoc_nombreArchivo');
                
                if (input.files && input.files.length > 0) {
                    // Agregar nuevos archivos al array
                    for (let i = 0; i < input.files.length; i++) {
                        const file = input.files[i];
                        // Verificar si el archivo ya existe
                        const existe = archivosSeleccionados.some(f => f.name === file.name && f.size === file.size);
                        if (!existe) {
                            archivosSeleccionados.push(file);
                        }
                    }
                    
                    // Actualizar el texto
                    if (archivosSeleccionados.length > 0) {
                        nombreArchivoSpan.textContent = archivosSeleccionados.length + ' archivo(s) seleccionado(s)';
                    } else {
                        nombreArchivoSpan.textContent = 'No se ha seleccionado ningún archivo';
                    }
                    
                    // Renderizar la lista
                    renderizarArchivosSubidos();
                } else {
                    nombreArchivoSpan.textContent = 'No se ha seleccionado ningún archivo';
                }
            }
            
            
            // Función para eliminar un archivo de la lista (nuevo, no subido)
            function eliminarArchivoCargado(index) {
                archivosSeleccionados.splice(index, 1);
                renderizarArchivosSubidos();
                
                const nombreArchivoSpan = document.getElementById('cargarDoc_nombreArchivo');
                if (archivosSeleccionados.length > 0) {
                    nombreArchivoSpan.textContent = archivosSeleccionados.length + ' archivo(s) seleccionado(s)';
                } else {
                    nombreArchivoSpan.textContent = 'No se ha seleccionado ningún archivo';
                }
                
                // Limpiar el input file
                document.getElementById('cargarDoc_archivo').value = '';
            }
            
            // Función para eliminar un archivo ya subido
            function eliminarArchivoSubido(idDocumento, nombreArchivo) {
                Swal.fire({
                    title: '¿Eliminar archivo?',
                    text: '¿Estás seguro de que deseas eliminar "' + nombreArchivo + '"?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = new FormData();
                        formData.append('id_documento', idDocumento);
                        
                        fetch('/caphum/eliminarDocumentoBaja', {
                            method: 'POST',
                            body: formData
                        })
                        .then(res => res.json())
                        .then(resp => {
                            if (resp.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Archivo eliminado',
                                    text: 'El archivo ha sido eliminado correctamente',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                
                                // Recargar la lista de archivos
                                const registroBaja = document.getElementById('cargarDoc_registroBaja').value;
                                if (registroBaja) {
                                    cargarArchivosExistentes(registroBaja);
                                } else {
                                    // Si no hay registro, mostrar tabla vacía
                                    document.getElementById('cargarDoc_tablaArchivos').innerHTML = `
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">No hay archivos subidos</td>
                                        </tr>
                                    `;
                                }
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: resp.mensaje || 'No se pudo eliminar el archivo'
                                });
                            }
                        })
                        .catch(err => {
                            console.error('Error:', err);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Ocurrió un error al eliminar el archivo'
                            });
                        });
                    }
                });
            }
            
            // Función para ver un archivo nuevo (no subido)
            function verArchivoCargado(index) {
                const file = archivosSeleccionados[index];
                if (file) {
                    // Crear una URL temporal para el archivo
                    const url = URL.createObjectURL(file);
                    // Abrir en nueva ventana
                    window.open(url, '_blank');
                }
            }
            
            // Función para ver un archivo ya subido
            function verArchivoSubido(nombreArchivo) {
                const url = '/caphum/verDocumentoBaja?archivo=' + encodeURIComponent(nombreArchivo);
                window.open(url, '_blank');
            }
            
            // Función para marcar archivo como verificado
            function marcarArchivoVerificado(index) {
                const file = archivosSeleccionados[index];
                if (file) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Archivo verificado',
                        text: 'El archivo "' + file.name + '" ha sido marcado como verificado',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            }
            
            // Función para subir documento
            function subirDocumentoBaja() {
                const tipoDocumento = document.getElementById('cargarDoc_tipoDocumento').value;
                const registroBaja = document.getElementById('cargarDoc_registroBaja').value;
                
                if (!tipoDocumento) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atención',
                        text: 'Por favor selecciona un tipo de documento'
                    });
                    return;
                }
                
                if (archivosSeleccionados.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atención',
                        text: 'Por favor selecciona al menos un archivo'
                    });
                    return;
                }
                
                if (!registroBaja) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se encontró el registro de baja'
                    });
                    return;
                }
                
                // Mostrar loading
                Swal.fire({
                    title: 'Subiendo archivos...',
                    text: 'Por favor espera',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Crear FormData
                const formData = new FormData();
                formData.append('registro_baja', registroBaja);
                
                // Agregar archivos
                archivosSeleccionados.forEach((file, index) => {
                    formData.append('archivosPDF[]', file);
                });
                
                // Enviar al servidor
                fetch('/caphum/subirDocumentosBaja', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(resp => {
                    Swal.close();
                    
                    if (resp.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Archivos subidos',
                            text: 'Se subieron ' + archivosSeleccionados.length + ' archivo(s) correctamente',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        
                        // Limpiar archivos seleccionados
                        archivosSeleccionados = [];
                        document.getElementById('cargarDoc_archivo').value = '';
                        document.getElementById('cargarDoc_nombreArchivo').textContent = 'No se ha seleccionado ningún archivo';
                        
                        // Recargar lista de archivos
                        cargarArchivosExistentes(registroBaja);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: resp.mensaje || 'No se pudieron subir los archivos'
                        });
                    }
                })
                .catch(err => {
                    Swal.close();
                    console.error('Error:', err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrió un error al subir los archivos'
                    });
                });
            }
            
            $(document).ready(() => {
                inicializarBajas();
            });
            
            // ==========================================
            // FUNCIONES PARA CARGAR DOCUMENTOS DE PERSONA (GESTIÓN)
            // ==========================================
            
            // Array para almacenar archivos seleccionados (Gestión)
            let archivosSeleccionadosPersona = [];
            let archivosSubidosPersona = [];
            
            // Alias para el botón "Ver archivo" de la tabla (recibe id de persona)
            function verArchivo(idPersona) {
                cargarDocumentoPersona(idPersona);
            }
            
            // Función para abrir modal de cargar documento de persona
            function cargarDocumentoPersona(button) {
                let idPersona, nombreCompleto;
                const esIdDirecto = typeof button === 'number' || (typeof button === 'string' && button !== '' && !isNaN(Number(button)));
                
                if (esIdDirecto) {
                    idPersona = String(button);
                    nombreCompleto = 'N/A';
                } else {
                    let btnElement = button;
                    if (!button || typeof button.getAttribute !== 'function') {
                        if (typeof event !== 'undefined' && event && event.target) {
                            btnElement = event.target.closest('button');
                        } else if (typeof button === 'string' || typeof button === 'number') {
                            btnElement = document.querySelector(`[data-id-persona="${button}"]`);
                        }
                        if (!btnElement || typeof btnElement.getAttribute !== 'function') {
                            console.error('No se pudo obtener el elemento del botón:', button);
                            return;
                        }
                    }
                    idPersona = btnElement.getAttribute('data-id-persona');
                    nombreCompleto = btnElement.getAttribute('data-nombre') || '';
                    if (!idPersona) {
                        console.error('No se encontró el ID de persona en el botón');
                        return;
                    }
                }
                
                // Guardar el ID de persona en un campo oculto del modal
                document.getElementById('cargarDocPersona_idPersona').value = idPersona || '';
                document.getElementById('cargarDocPersona_nombrePersona').textContent = 'Persona: ' + (nombreCompleto || 'N/A');
                
                // Limpiar el select y el input de archivo
                const selectTipo = document.getElementById('cargarDocPersona_tipoDocumento');
                selectTipo.value = '';
                document.getElementById('cargarDocPersona_archivo').value = '';
                document.getElementById('cargarDocPersona_nombreArchivo').textContent = 'No se ha seleccionado ningún archivo';
                
                // Limpiar la lista de archivos nuevos
                archivosSeleccionadosPersona = [];
                document.getElementById('cargarDocPersona_listaArchivos').style.display = 'none';
                
                // Primero resetear todas las opciones del select para que sean visibles
                Array.from(selectTipo.options).forEach(option => {
                    option.style.display = 'block';
                    option.disabled = false;
                });
                
                // Cargar archivos existentes (esto actualizará el select automáticamente ocultando los únicos ya subidos)
                if (idPersona) {
                    cargarArchivosExistentesPersona(idPersona);
                } else {
                    // Si no hay ID, al menos actualizar el select para mostrar todas las opciones
                    actualizarSelectDocumentos();
                }
                
                // Actualizar el atributo multiple del input file según el tipo de documento
                const inputFile = document.getElementById('cargarDocPersona_archivo');
                
                // Remover listeners anteriores si existen para evitar duplicados
                const nuevoSelectTipo = selectTipo.cloneNode(true);
                selectTipo.parentNode.replaceChild(nuevoSelectTipo, selectTipo);
                
                // Actualizar la referencia al nuevo elemento
                const selectTipoActualizado = document.getElementById('cargarDocPersona_tipoDocumento');
                
                selectTipoActualizado.addEventListener('change', function() {
                    const tipoDoc = this.value;
                    if (tipoDoc && !permiteMultiplesArchivos(tipoDoc)) {
                        inputFile.setAttribute('multiple', 'false');
                        inputFile.removeAttribute('multiple');
                    } else {
                        inputFile.setAttribute('multiple', 'multiple');
                    }
                    
                    // Limpiar archivos seleccionados cuando cambia el tipo
                    archivosSeleccionadosPersona = [];
                    inputFile.value = '';
                    document.getElementById('cargarDocPersona_nombreArchivo').textContent = 'No se ha seleccionado ningún archivo';
                    document.getElementById('cargarDocPersona_listaArchivos').style.display = 'none';
                });
                
                // Mostrar modal
                const modal = new bootstrap.Modal(document.getElementById('modalCargarDocumentoPersona'));
                modal.show();
            }
            
            // Función para seleccionar archivo
            function seleccionarArchivoDocumentoPersona() {
                document.getElementById('cargarDocPersona_archivo').click();
            }
            
            // Tipos de documentos que solo permiten un archivo
            const documentosUnicos = [
                'Acta de Nacimiento',
                'Certificado de Estudios',
                'Comprobante de Domicilio',
                'CURP',
                'Identificación Oficial (INE)',
                'RFC'
            ];
            
            // Función para verificar si un tipo de documento permite múltiples archivos
            function permiteMultiplesArchivos(tipoDocumento) {
                return !documentosUnicos.includes(tipoDocumento);
            }
            
            // Función para agregar archivo a la lista
            function agregarArchivoListaPersona(input) {
                const tipoDocumento = document.getElementById('cargarDocPersona_tipoDocumento').value;
                
                if (!tipoDocumento) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atención',
                        text: 'Por favor selecciona primero un tipo de documento'
                    });
                    input.value = '';
                    return;
                }
                
                // Verificar si ya existe un documento de este tipo subido
                const esUnico = !permiteMultiplesArchivos(tipoDocumento);
                if (esUnico) {
                    // Verificar si ya hay un archivo de este tipo en los archivos subidos
                    const idDocumento = obtenerIdDocumentoPorNombre(tipoDocumento);
                    const existeDocumento = archivosSubidosPersona.some(doc => doc.id_documento === idDocumento);
                    
                    if (existeDocumento) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Documento ya existe',
                            text: 'Este tipo de documento solo permite un archivo. Por favor elimina el existente antes de subir uno nuevo.'
                        });
                        input.value = '';
                        return;
                    }
                    
                    // Verificar si ya hay un archivo seleccionado de este tipo
                    if (archivosSeleccionadosPersona.length > 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Solo un archivo permitido',
                            text: 'Este tipo de documento solo permite un archivo. Por favor elimina el archivo seleccionado antes de agregar otro.'
                        });
                        input.value = '';
                        return;
                    }
                }
                
                if (input.files && input.files.length > 0) {
                    const archivosValidos = [];
                    
                    Array.from(input.files).forEach(file => {
                        if (file.type === 'application/pdf') {
                            // Si es documento único, solo permitir un archivo
                            if (esUnico && archivosSeleccionadosPersona.length >= 1) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Solo un archivo permitido',
                                    text: 'Este tipo de documento solo permite un archivo.'
                                });
                                return;
                            }
                            archivosValidos.push(file);
                        }
                    });
                    
                    // Agregar archivos válidos
                    archivosValidos.forEach(file => {
                        archivosSeleccionadosPersona.push(file);
                    });
                    
                    // Actualizar contador
                    const count = archivosSeleccionadosPersona.length;
                    document.getElementById('cargarDocPersona_nombreArchivo').textContent = 
                        count > 0 ? `${count} archivo(s) seleccionado(s)` : 'No se ha seleccionado ningún archivo';
                    
                    // Renderizar lista
                    renderArchivosSubidosPersona();
                }
            }
            
            // Función auxiliar para obtener ID de documento (usando IDs reales de la BD)
            function obtenerIdDocumentoPorNombre(nombre) {
                const mapaDocumentos = {
                    'CURP': 8,
                    'Identificación Oficial (INE)': 9,
                    'RFC': 10,
                    'Comprobante de Domicilio': 11,
                    'Acta de Nacimiento': 12,
                    'Certificado de Estudios': 13,
                    'Referencias Laborales': 14,
                    'Documento baja': 15,
                    'Documento Baja': 15  // Compatibilidad con mayúscula
                };
                return mapaDocumentos[nombre] || null;
            }
            
            // Función para renderizar archivos
            function renderArchivosSubidosPersona() {
                const listaArchivos = document.getElementById('cargarDocPersona_listaArchivos');
                const tablaArchivos = document.getElementById('cargarDocPersona_tablaArchivos');
                
                // Renderizar tabla de archivos subidos
                if (archivosSubidosPersona.length > 0) {
                    let htmlTabla = '';
                    archivosSubidosPersona.forEach(doc => {
                        const fechaFormateada = doc.fecha_carga || 'N/A';
                        const archivoEscapado = (doc.archivo || '').replace(/'/g, "\\'");
                        
                        htmlTabla += `
                            <tr>
                                <td>${obtenerNombreDocumento(doc.id_documento)}</td>
                                <td>${doc.archivo || 'N/A'}</td>
                                <td>${fechaFormateada}</td>
                                <td>
                                    <span class="badge bg-success">Sí</span>
                                </td>
                                <td>
                                    <button 
                                        type="button" 
                                        class="btn btn-sm btn-info me-1" 
                                        onclick="verArchivoSubidoPersona('${archivoEscapado}')" 
                                        title="Ver archivo"
                                    >
                                        Ver
                                    </button>
                                    <button 
                                        type="button" 
                                        class="btn btn-sm btn-danger" 
                                        onclick="eliminarArchivoSubidoPersona(${doc.id}, '${archivoEscapado}')" 
                                        title="Eliminar archivo"
                                    >
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                    tablaArchivos.innerHTML = htmlTabla;
                } else {
                    tablaArchivos.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No hay archivos subidos</td></tr>';
                }
                
                // Renderizar lista de archivos nuevos seleccionados (antes de subir)
                if (archivosSeleccionadosPersona.length > 0) {
                    listaArchivos.style.display = 'block';
                    let htmlLista = '';
                    archivosSeleccionadosPersona.forEach((file, index) => {
                        htmlLista += `
                            <div class="d-flex align-items-center justify-content-between p-2 mb-2 border rounded" style="background-color: #f8f9fa;">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fa fa-file-pdf text-danger"></i>
                                    <span>${file.name}</span>
                                    <span class="badge bg-success rounded-pill">
                                        <i class="fa fa-check"></i>
                                    </span>
                                </div>
                                <div class="d-flex gap-1">
                                    <button 
                                        type="button" 
                                        class="btn btn-sm btn-info p-1" 
                                        onclick="verArchivoCargadoPersona(${index})" 
                                        title="Ver archivo"
                                        style="width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;"
                                    >
                                        <i class="fa fa-eye" style="font-size: 12px;"></i>
                                    </button>
                                    <button 
                                        type="button" 
                                        class="btn btn-sm btn-danger p-1" 
                                        onclick="eliminarArchivoCargadoPersona(${index})" 
                                        title="Eliminar archivo"
                                        style="width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;"
                                    >
                                        <i class="fa fa-times" style="font-size: 12px;"></i>
                                    </button>
                                </div>
                            </div>
                        `;
                    });
                    listaArchivos.innerHTML = htmlLista;
                } else {
                    listaArchivos.style.display = 'none';
                }
            }
            
            // Función para obtener nombre del documento por ID (IDs reales de la BD)
            function obtenerNombreDocumento(idDocumento) {
                const mapeo = {
                    8: 'CURP',
                    9: 'Identificación Oficial (INE)',
                    10: 'RFC',
                    11: 'Comprobante de Domicilio',
                    12: 'Acta de Nacimiento',
                    13: 'Certificado de Estudios',
                    14: 'Referencias Laborales',
                    15: 'Documento baja'
                };
                return mapeo[idDocumento] || 'Documento';
            }
            
            // Función para actualizar el select de documentos, ocultando los únicos que ya están subidos
            function actualizarSelectDocumentos() {
                const selectTipo = document.getElementById('cargarDocPersona_tipoDocumento');
                if (!selectTipo) return;
                
                // Obtener todos los IDs de documentos únicos que ya están subidos
                const documentosUnicosSubidos = new Set();
                archivosSubidosPersona.forEach(doc => {
                    const idDoc = doc.id_documento;
                    // Verificar si este ID corresponde a un documento único
                    const nombreDoc = obtenerNombreDocumento(idDoc);
                    if (nombreDoc && !permiteMultiplesArchivos(nombreDoc)) {
                        documentosUnicosSubidos.add(nombreDoc);
                    }
                });
                
                // Verificar si el valor actual del select es un documento único ya subido
                const valorActual = selectTipo.value;
                if (valorActual && !permiteMultiplesArchivos(valorActual) && documentosUnicosSubidos.has(valorActual)) {
                    // Si el documento seleccionado es único y ya está subido, limpiar el select
                    selectTipo.value = '';
                }
                
                // Recorrer todas las opciones del select
                Array.from(selectTipo.options).forEach(option => {
                    const valor = option.value;
                    // Si es un documento único y ya está subido, ocultarlo
                    if (valor && !permiteMultiplesArchivos(valor) && documentosUnicosSubidos.has(valor)) {
                        option.style.display = 'none';
                        option.disabled = true;
                    } else {
                        option.style.display = 'block';
                        option.disabled = false;
                    }
                });
            }
            
            // Función para cargar archivos existentes
            function cargarArchivosExistentesPersona(idPersona) {
                fetch('/caphum/getDocumentosPersona?id_persona=' + idPersona)
                    .then(res => res.json())
                    .then(resp => {
                        if (resp.success && resp.datos) {
                            archivosSubidosPersona = resp.datos;
                            renderArchivosSubidosPersona();
                            // Actualizar el select después de cargar los archivos
                            actualizarSelectDocumentos();
                        } else {
                            archivosSubidosPersona = [];
                            renderArchivosSubidosPersona();
                            actualizarSelectDocumentos();
                        }
                    })
                    .catch(err => {
                        console.error('Error al cargar archivos:', err);
                        archivosSubidosPersona = [];
                        renderArchivosSubidosPersona();
                        actualizarSelectDocumentos();
                    });
            }
            
            // Función para eliminar archivo cargado (nuevo, antes de subir)
            function eliminarArchivoCargadoPersona(index) {
                archivosSeleccionadosPersona.splice(index, 1);
                const count = archivosSeleccionadosPersona.length;
                document.getElementById('cargarDocPersona_nombreArchivo').textContent = 
                    count > 0 ? `${count} archivo(s) seleccionado(s)` : 'No se ha seleccionado ningún archivo';
                renderArchivosSubidosPersona();
            }
            
            // Función para eliminar archivo subido (ya en BD)
            function eliminarArchivoSubidoPersona(idDocumento, nombreArchivo) {
                Swal.fire({
                    title: '¿Eliminar archivo?',
                    text: '¿Estás seguro de que deseas eliminar "' + nombreArchivo + '"?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = new FormData();
                        formData.append('id_documento', idDocumento);
                        
                        fetch('/caphum/eliminarDocumentoPersona', {
                            method: 'POST',
                            body: formData
                        })
                        .then(res => res.json())
                        .then(resp => {
                            if (resp.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Archivo eliminado',
                                    text: 'El archivo fue eliminado correctamente',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                
                                // Recargar lista (esto también actualizará el select)
                                const idPersona = document.getElementById('cargarDocPersona_idPersona').value;
                                if (idPersona) {
                                    cargarArchivosExistentesPersona(idPersona);
                                }
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: resp.mensaje || 'No se pudo eliminar el archivo'
                                });
                            }
                        })
                        .catch(err => {
                            console.error('Error:', err);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Ocurrió un error al eliminar el archivo'
                            });
                        });
                    }
                });
            }
            
            // Función para ver un archivo nuevo (no subido)
            function verArchivoCargadoPersona(index) {
                const file = archivosSeleccionadosPersona[index];
                if (file) {
                    const url = URL.createObjectURL(file);
                    window.open(url, '_blank');
                }
            }
            
            // Función para ver un archivo ya subido
            function verArchivoSubidoPersona(nombreArchivo) {
                const url = '/caphum/verDocumentoPersona?archivo=' + encodeURIComponent(nombreArchivo);
                window.open(url, '_blank');
            }
            
            // Mapeo directo de nombres de documentos a IDs (igual que en Bajas)
            const mapaDocumentosIds = {
                'CURP': 8,
                'Identificación Oficial (INE)': 9,
                'RFC': 10,
                'Comprobante de Domicilio': 11,
                'Acta de Nacimiento': 12,
                'Certificado de Estudios': 13,
                'Referencias Laborales': 14,
                'Documento baja': 15
            };
            
            // Función para subir documento de persona
            function subirDocumentoPersona() {
                const tipoDocumento = document.getElementById('cargarDocPersona_tipoDocumento').value;
                const idPersona = document.getElementById('cargarDocPersona_idPersona').value;
                
                if (!tipoDocumento) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atención',
                        text: 'Por favor selecciona un tipo de documento'
                    });
                    return;
                }
                
                // Obtener ID del documento usando el mapeo directo
                const idDocumento = mapaDocumentosIds[tipoDocumento];
                if (!idDocumento) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Tipo de documento no válido: ' + tipoDocumento
                    });
                    return;
                }
                
                if (archivosSeleccionadosPersona.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atención',
                        text: 'Por favor selecciona al menos un archivo'
                    });
                    return;
                }
                
                if (!idPersona) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se encontró el ID de la persona'
                    });
                    return;
                }
                
                // Mostrar loading
                Swal.fire({
                    title: 'Subiendo archivos...',
                    text: 'Por favor espera',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Crear FormData
                const formData = new FormData();
                formData.append('id_persona', idPersona);
                formData.append('id_documento', idDocumento);  // Enviar ID directamente
                
                // Agregar archivos
                archivosSeleccionadosPersona.forEach((file) => {
                    formData.append('archivosPDF[]', file);
                });
                
                // Enviar al servidor
                fetch('/caphum/subirDocumentosPersona', {
                    method: 'POST',
                    body: formData
                })
                .then(res => {
                    // Verificar si la respuesta es JSON válido
                    const contentType = res.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        return res.json();
                    } else {
                        // Si no es JSON, leer como texto para ver el error
                        return res.text().then(text => {
                            console.error('Respuesta no JSON:', text);
                            throw new Error('El servidor devolvió una respuesta no válida. Ver consola para más detalles.');
                        });
                    }
                })
                .then(resp => {
                    Swal.close();
                    
                    if (resp.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Archivos subidos',
                            text: 'Se subieron ' + archivosSeleccionadosPersona.length + ' archivo(s) correctamente',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        
                        // Limpiar archivos seleccionados
                        archivosSeleccionadosPersona = [];
                        document.getElementById('cargarDocPersona_archivo').value = '';
                        document.getElementById('cargarDocPersona_nombreArchivo').textContent = 'No se ha seleccionado ningún archivo';
                        document.getElementById('cargarDocPersona_listaArchivos').style.display = 'none';
                        
                        // Si es un documento único, limpiar el select de tipo de documento
                        // (Referencias Laborales y Documento baja permiten múltiples, así que no se limpian)
                        if (!permiteMultiplesArchivos(tipoDocumento)) {
                            const selectTipoDoc = document.getElementById('cargarDocPersona_tipoDocumento');
                            if (selectTipoDoc) {
                                selectTipoDoc.value = '';
                            }
                        }
                        
                        // Recargar lista de archivos (esto también actualizará el select ocultando los únicos ya subidos)
                        cargarArchivosExistentesPersona(idPersona);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: resp.mensaje || 'No se pudieron subir los archivos'
                        });
                    }
                })
                .catch(err => {
                    Swal.close();
                    console.error('Error al subir archivos:', err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrió un error al subir los archivos: ' + (err.message || 'Error desconocido')
                    });
                });
            }
        </script>
        HTML;

        self::set("titulo", "Personas dadas de baja");
        self::set("script", $scriptGestion);
        self::set("departamento", ['datos' => []]); // Array vacío para no romper la vista
        self::render("all_gestores");
    }

    public function getUsuarios()
    {
        $tieneDepartamento = in_array(10, $_SESSION['modulos'] ?? []);
        $resultado = CapHumDAO::getConsultaGestoresAll($_SESSION['usuario_id'], $tieneDepartamento);
        $usuarios = $resultado['datos'] ?? [];


        // Preparar array compatible con frontend
        $datos = array_map(function($p) {
            return [
                'id' => $p['id'] ?? '',
                'numero_empleado' => $p['numero_empleado'] ?? '',
                'nombre_jefe' => $p['nombre_jefe'] ?? '',
                'nombres' => $p['nombres'] ?? '',
                'segundo_nombre' => $p['segundo_nombre'] ?? '',
                'apellidop' => $p['apellidop'] ?? '',
                'apellidom' => $p['apellidom'] ?? '',
                'nombre_departamento' => $p['nombre_departamento'] ?? '',
                'nombre_puesto' => $p['nombre_puesto'] ?? '',
                'id_puesto' => $p['id_puesto'] ?? null,  // 🔧 FIX: Agregar ID del puesto
                'id_departamento' => $p['id_departamento'] ?? null,  // 🔧 FIX: Agregar ID del departamento
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

    public function getBajas()
    {
        try {
            // Obtener parámetros de fecha del POST
            $input = json_decode(file_get_contents("php://input"), true);
            $fecha_inicio = $input['fecha_inicio'] ?? null;
            $fecha_fin = $input['fecha_fin'] ?? null;
            
            // Validar formato de fechas si se proporcionan
            if ($fecha_inicio && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_inicio)) {
                self::respuestaJSON([
                    'success' => false,
                    'mensaje' => 'Formato de fecha de inicio inválido'
                ]);
                return;
            }
            
            if ($fecha_fin && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_fin)) {
                self::respuestaJSON([
                    'success' => false,
                    'mensaje' => 'Formato de fecha de fin inválido'
                ]);
                return;
            }
            
            $resultado = CapHumDAO::getConsultaBajas($fecha_inicio, $fecha_fin);
            
            // Verificar si hubo error en el DAO
            if (!$resultado) {
                self::respuestaJSON([
                    'success' => false,
                    'mensaje' => 'Error al consultar las bajas: respuesta vacía del modelo',
                    'datos' => []
                ]);
                return;
            }
            
            // El método resultado devuelve ['success' => bool, 'mensaje' => string, 'datos' => array]
            if (!isset($resultado['success']) || $resultado['success'] === false) {
                self::respuestaJSON([
                    'success' => false,
                    'mensaje' => $resultado['mensaje'] ?? 'Error al consultar las bajas',
                    'error' => $resultado['error'] ?? null,
                    'datos' => []
                ]);
                return;
            }
            
            $bajas = $resultado['datos'] ?? [];

            // Preparar array compatible con frontend con todos los nuevos campos
            $datos = [];
            if (is_array($bajas) && count($bajas) > 0) {
                $datos = array_map(function($p) {
                    return [
                        'nombres' => $p['nombres'] ?? '',
                        'segundo_nombre' => $p['segundo_nombre'] ?? '',
                        'apellidop' => $p['apellidop'] ?? '',
                        'apellidom' => $p['apellidom'] ?? '',
                        'numero_empleado' => $p['numero_empleado'] ?? '',
                        'external_id' => $p['external_id'] ?? '',
                        'departamento' => $p['departamento'] ?? '',
                        'nombre_puesto' => $p['nombre_puesto'] ?? '',
                        'fecha_baja' => $p['fecha_baja'] ?? '',
                        'registro_baja' => $p['registro_baja'] ?? '',
                        'motivo' => $p['motivo'] ?? '',
                        'descripcion' => $p['descripcion'] ?? '',
                        'user_name' => $p['user_name'] ?? '',
                    ];
                }, $bajas);
            }

            // Usar respuestaJSON para enviar el JSON correctamente
            self::respuestaJSON([
                'success' => true,
                'datos' => $datos,
                'mensaje' => count($datos) > 0 ? 'Bajas encontradas' : 'No se encontraron bajas en el rango seleccionado'
            ]);
        } catch (\Exception $e) {
            error_log('Error en getBajas: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'Error al procesar la solicitud: ' . $e->getMessage(),
                'error' => $e->getMessage(),
                'datos' => []
            ]);
        }
    }

    

    public function descargarBajasExcel()
    {
        // Limpiar buffer para evitar que cualquier eco previo rompa el Excel
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        try {
            // Obtener parámetros de fecha del GET
            $fecha_inicio = $_GET['fecha_inicio'] ?? null;
            $fecha_fin = $_GET['fecha_fin'] ?? null;
            
            // Validar formato de fechas si se proporcionan
            if ($fecha_inicio && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_inicio)) {
                die('Formato de fecha de inicio inválido');
            }
            
            if ($fecha_fin && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_fin)) {
                die('Formato de fecha de fin inválido');
            }
            
            // Obtener las bajas con los mismos filtros
            $resultado = CapHumDAO::getConsultaBajas($fecha_inicio, $fecha_fin);
            
            // Verificar si hubo error
            if (!$resultado || !isset($resultado['success']) || !$resultado['success']) {
                die('Error al obtener las bajas: ' . ($resultado['mensaje'] ?? 'Error desconocido'));
            }
            
            $bajas = $resultado['datos'] ?? [];
            
            if (empty($bajas)) {
                die('No hay bajas para descargar');
            }
            
            // Preparar datos para Excel
            $data = [];
            foreach ($bajas as $baja) {
                $nombreCompleto = trim(($baja['nombres'] ?? '') . ' ' . ($baja['apellidop'] ?? '') . ' ' . ($baja['apellidom'] ?? ''));
                $fechaBaja = $baja['fecha_baja'] ?? '';
                if ($fechaBaja) {
                    try {
                        $fechaBaja = date('d/m/Y', strtotime($fechaBaja));
                    } catch (\Exception $e) {
                        // Mantener el formato original si hay error
                    }
                }
                
                $data[] = [
                    'external_id' => $baja['external_id'] ?? '',
                    'numero_empleado' => $baja['numero_empleado'] ?? '',
                    'nombre_completo' => $nombreCompleto,
                    'departamento' => $baja['departamento'] ?? 'N/A',
                    'nombre_puesto' => $baja['nombre_puesto'] ?? 'N/A',
                    'fecha_baja' => $fechaBaja,
                    'registro_baja' => $baja['registro_baja'] ?? '',
                    'motivo' => $baja['motivo'] ?? 'N/A',
                    'descripcion' => $baja['descripcion'] ?? 'Sin descripción',
                    'user_name' => $baja['user_name'] ?? 'N/A'
                ];
            }
            
            // Definir columnas para Excel
            $columnas = [
                \PHPSpreadsheet::ColumnaExcel('external_id', 'External ID'),
                \PHPSpreadsheet::ColumnaExcel('numero_empleado', 'NÚMERO DE EMPLEADO'),
                \PHPSpreadsheet::ColumnaExcel('nombre_completo', 'NOMBRE COMPLETO'),
                \PHPSpreadsheet::ColumnaExcel('departamento', 'DEPARTAMENTO'),
                \PHPSpreadsheet::ColumnaExcel('nombre_puesto', 'PUESTO'),
                \PHPSpreadsheet::ColumnaExcel('fecha_baja', 'FECHA DE BAJA'),
                \PHPSpreadsheet::ColumnaExcel('registro_baja', 'REGISTRO DE BAJA'),
                \PHPSpreadsheet::ColumnaExcel('motivo', 'MOTIVO'),
                \PHPSpreadsheet::ColumnaExcel('descripcion', 'DESCRIPCIÓN'),
                \PHPSpreadsheet::ColumnaExcel('user_name', 'USUARIO')
            ];
            
            // Generar nombre del archivo
            $nombreArchivo = 'Bajas';
            if ($fecha_inicio && $fecha_fin) {
                $nombreArchivo .= '_' . $fecha_inicio . '_a_' . $fecha_fin;
            } elseif ($fecha_inicio) {
                $nombreArchivo .= '_desde_' . $fecha_inicio;
            } elseif ($fecha_fin) {
                $nombreArchivo .= '_hasta_' . $fecha_fin;
            }
            $nombreArchivo .= '_' . date('Y-m-d');
            
            // Descargar Excel directamente
            \PHPSpreadsheet::DescargaExcel(
                $nombreArchivo,
                "Bajas de Personal",
                "Bajas",
                $columnas,
                $data
            );
            
            // Terminar ejecución para que no se agregue nada extra
            exit;
        } catch (\Exception $e) {
            error_log('Error en descargarBajasExcel: ' . $e->getMessage());
            die('Error al generar el archivo Excel: ' . $e->getMessage());
        }
    }

    public function getAusenciasPersona()
    {
        $input = json_decode(file_get_contents("php://input"), true);
        $idPersona = $input['idPersona'] ?? null;

        if (empty($idPersona)) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'Id de persona requerido'
            ]);
            return;
        }

        $resultado = CapHumDAO::getAusenciasPersona($idPersona);
        self::respuestaJSON($resultado);
    }

    public function getAusenciaById()
    {
        $input = json_decode(file_get_contents("php://input"), true);

        $idAusencia = $input['idAusencia'] ?? null;

        if (empty($idAusencia)) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'Id de ausencia requerido'
            ]);
            return;
        }

        $resultado = CapHumDAO::getAusenciaById($idAusencia);

        self::respuestaJSON($resultado);
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
    public function getRazonesAusencia()
    {
        // El DAO ya regresa success, mensaje y datos
        $resultado = CapHumDAO::getRazonesAusencia();

        self::respuestaJSON($resultado);
    }
    public function guardarAusencia()
    {
        // 🔹 Recibimos JSON
        $data = json_decode(file_get_contents("php://input"), true);

        // 🔹 Normalizamos idAusencia (puede venir o no)
        $data['idAusencia'] = $data['idAusencia'] ?? null;

        // 🔹 Validaciones obligatorias (NO incluye idAusencia)
        if (
            empty($data['idPersona']) ||
            empty($data['idRazon']) ||
            empty($data['fechaInicio']) ||
            empty($data['fechaFin'])
        ) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'Datos incompletos'
            ]);
            return;
        }

        // 🔹 DAO decide si INSERT o UPDATE
        $resultado = CapHumDAO::guardarAusencia($data);

        self::respuestaJSON($resultado);
    }


    public function getJefeDirecto()
    {
        $input = json_decode(file_get_contents("php://input"), true);
        $idDepartamento = $input['id_departamento'] ?? null;
        $idPuesto = $input['id_puesto'] ?? null;

        if (!$idDepartamento) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'ID de departamento no recibido'
            ]);
            return;
        }

        // 1) Si hay puesto seleccionado: jefes por nivel (mismo departamento, nivel superior)
        if ($idPuesto) {
            $porPuesto = CapHumDAO::getConsultaGestoresPorPuesto($idPuesto);
            if ($porPuesto['success'] && !empty($porPuesto['datos'])) {
                $datos = array_map(function ($row) {
                    return [
                        'id' => $row['id'],
                        'nombre_completo' => $row['nombre_completo'] ?? '',
                        'nombre_puesto' => $row['puesto'] ?? $row['nombre_puesto'] ?? ''
                    ];
                }, $porPuesto['datos']);
                self::respuestaJSON(['success' => true, 'mensaje' => 'Jefes encontrados.', 'datos' => $datos]);
                return;
            }
        }

        // 2) Jefes por es_jefe=1 en el departamento (o puesto id 8)
        $detalles = CapHumDAO::getConsultaJefe($idDepartamento);
        if ($detalles['success'] && !empty($detalles['datos'])) {
            self::respuestaJSON($detalles);
            return;
        }

        // 3) Fallback: todas las personas del departamento (ej. Legal/Abogado sin es_jefe ni nivel superior)
        $porDepto = CapHumDAO::getPersonasPorDepartamento($idDepartamento);
        if ($porDepto['success'] && !empty($porDepto['datos'])) {
            $datos = array_map(function ($row) {
                return [
                    'id' => $row['id'],
                    'nombre_completo' => $row['nombre_completo'] ?? '',
                    'nombre_puesto' => $row['nombre_puesto'] ?? $row['puesto'] ?? ''
                ];
            }, $porDepto['datos']);
            self::respuestaJSON(['success' => true, 'mensaje' => 'Personas del departamento.', 'datos' => $datos]);
            return;
        }

        // 4) Si todo devuelve vacío: mostrar siempre JONNATHAN MARLON FLORES RODRIGUEZ como opción
        $jefeDefault = CapHumDAO::getJefeDefault();
        if ($jefeDefault['success'] && !empty($jefeDefault['datos'])) {
            $datos = array_map(function ($row) {
                return [
                    'id' => $row['id'],
                    'nombre_completo' => $row['nombre_completo'] ?? '',
                    'nombre_puesto' => $row['nombre_puesto'] ?? ''
                ];
            }, $jefeDefault['datos']);
            self::respuestaJSON(['success' => true, 'mensaje' => 'Jefe por defecto.', 'datos' => $datos]);
            return;
        }

        self::respuestaJSON($detalles);
    }
    public function updateGestorF()
    {
        session_start(); // 🔴 IMPORTANTE
        header('Content-Type: application/json; charset=utf-8');

        // Mismo permiso que Gestiones: solo quien tiene Configuración - Departamentos (módulo 10) puede editar
        $modulos = $_SESSION['modulos'] ?? [];
        if (!in_array(10, $modulos)) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'No tiene permiso para editar. Solo usuarios con acceso a Configuración - Departamentos pueden modificar la información.'
            ]);
            exit;
        }

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


        $getDepartamentos = '<option disabled selected>Seleccione una opción</option>';

        if (!empty($departamentos['datos'])) {
            foreach ($departamentos['datos'] as $val2) {
                $getDepartamentos .= '<option value="' . $val2['id'] . '">' . htmlspecialchars($val2['nombre'], ENT_QUOTES, 'UTF-8') . '</option>';
            }
        }

        $modulos = $_SESSION['modulos'] ?? [];
        $puedeEditarTodos = in_array(10, $modulos); // Configuración - Departamentos (igual que en Gestiones)

        self::set("titulo", "Organigrama");
        self::set("script", $script);
        self::set("Departamentos", $getDepartamentos);
        self::set("puedeEditarTodos", $puedeEditarTodos);
        self::render("organigrama");
    }
    public function getPersonasOrganigrama()
    {
        // Obtener parámetro enviado por POST como JSON
        $input = json_decode(file_get_contents("php://input"), true);
        $idDepartamento = $input['idDepartamento'] ?? null;

        if (empty($idDepartamento)) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'ID de departamento requerido'
            ]);
            return;
        }

        // Pasar el parámetro a DAO
        $puestos = CapHumDAO::getPersonasOrganigrama($idDepartamento, $_SESSION['usuario_id']);

        self::respuestaJSON($puestos);
    }

    /// ESTA NO SE MUEVE 
    public function nivelJerarquicoColaborador($persona_id)
    {
        // 1️⃣ Obtener organigrama desde la DAO
        $personas = CapHumDAO::getConsultaPersonasJerarquia($persona_id);
        
        // 🔍 Debug: verificar qué se está devolviendo
        if (!$personas['success'] || empty($personas["datos"])) {
            header('Content-Type: application/json');
            echo json_encode([
                "success" => false,
                "mensaje" => "No se encontraron datos del organigrama",
                "debug" => $personas
            ]);
            exit;
        }
        
        $organigramaJson = $personas["datos"][0]["organigrama_json"] ?? null;
        
        if (!$organigramaJson) {
            header('Content-Type: application/json');
            echo json_encode([
                "success" => false,
                "mensaje" => "organigrama_json está vacío",
                "debug" => $personas["datos"]
            ]);
            exit;
        }
        
        $organigrama = json_decode($organigramaJson, true);

        // 2️⃣ Construir filas para el OrgChart
        $rows = [];

        // Raíz del organigrama
        $rows[] = [
            "id"     => (string)($organigrama["id_jefe"] ?? ''),    // ID como string
            "nombre" => $organigrama["nombre_jefe"] ?? null,        // Nombre
            "puesto" => $organigrama["nombre_puesto"] ?? null, // 👈
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
            "puesto" => $nodo["nombre_puesto"] ?? null, // 👈 YA VIENE DEL SQL
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

        // Validaciones básicas (todos los campos de registro obligatorios; id_jefe puede estar vacío si es máximo rango)
        $requiredFields = ['nombres', 'apellidop', 'apellidom', 'telefono', 'fecha_ingreso', 'id_puesto', 'departamento_id', 'usuario', 'contrasena'];
        $nombresCampos = [
            'nombres' => 'Nombres',
            'apellidop' => 'Apellido paterno',
            'apellidom' => 'Apellido materno',
            'telefono' => 'Teléfono',
            'fecha_ingreso' => 'Fecha de ingreso',
            'id_puesto' => 'Puesto',
            'departamento_id' => 'Departamento',
            'usuario' => 'Usuario',
            'contrasena' => 'Contraseña'
        ];
        foreach ($requiredFields as $field) {
            if ($data[$field] === '' || $data[$field] === null || (is_string($data[$field]) && trim($data[$field]) === '')) {
                $nombre = $nombresCampos[$field] ?? $field;
                echo json_encode([
                    'success' => false,
                    'mensaje' => "El campo \"$nombre\" es obligatorio"
                ]);
                return;
            }
        }

        // Preparar datos
        $data['contrasena'] = $data['contrasena'];
        $data['id_jefe'] = isset($data['id_jefe']) ? $data['id_jefe'] : null;
        $data['fecha_ingreso'] = $data['fecha_ingreso'];
        $data['asignar_legion'] = isset($data['asignar_legion']) ? (bool)$data['asignar_legion'] : false;
        $data['id_legion'] = isset($data['id_legion']) && !empty($data['id_legion']) ? (int)$data['id_legion'] : null;

        // Validar que si asignar_legion es true, id_legion debe estar presente
        if ($data['asignar_legion'] && !$data['id_legion']) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Debe seleccionar una legión si marca la opción de asignar legión'
            ]);
            return;
        }

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
            'fecha_baja'  => date('Y-m-d H:i:s'),
            'usuario_baja' => $_SESSION['usuario_id']
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

    /**
     * Obtener documentos de una baja
     */
    public function getDocumentosBaja()
    {
        try {
            $registro_baja = $_GET['registro_baja'] ?? null;
            
            if (!$registro_baja) {
                self::respuestaJSON([
                    'success' => false,
                    'mensaje' => 'Registro de baja requerido',
                    'datos' => []
                ]);
                return;
            }
            
            $resultado = CapHumDAO::getDocumentosBaja($registro_baja);
            self::respuestaJSON($resultado);
            
        } catch (\Exception $e) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'Error al obtener documentos: ' . $e->getMessage(),
                'datos' => []
            ]);
        }
    }

    /**
     * Subir documentos de una baja
     */
    public function subirDocumentosBaja()
    {
        try {
            $registro_baja = $_POST['registro_baja'] ?? null;
            
            if (!$registro_baja) {
                self::respuestaJSON([
                    'success' => false,
                    'mensaje' => 'Registro de baja requerido'
                ]);
                return;
            }
            
            if (empty($_FILES['archivosPDF']['name'][0])) {
                self::respuestaJSON([
                    'success' => false,
                    'mensaje' => 'No se seleccionaron archivos'
                ]);
                return;
            }
            
            // Crear directorio si no existe
            $directorio = __DIR__ . '/../uploads/bajas/';
            if (!is_dir($directorio)) {
                mkdir($directorio, 0777, true);
            }
            
            $archivosGuardados = [];
            
            // Procesar cada archivo
            foreach ($_FILES['archivosPDF']['tmp_name'] as $i => $tmp) {
                if ($_FILES['archivosPDF']['error'][$i] !== UPLOAD_ERR_OK) {
                    continue;
                }
                
                $nombreOrig = $_FILES['archivosPDF']['name'][$i];
                $extension = strtolower(pathinfo($nombreOrig, PATHINFO_EXTENSION));
                
                if ($extension !== 'pdf') {
                    continue;
                }
                
                // Generar nombre único
                $nombreFinal = 'baja_' . $registro_baja . '_' . time() . '_' . $i . '.pdf';
                $rutaFinal = $directorio . $nombreFinal;
                
                if (move_uploaded_file($tmp, $rutaFinal)) {
                    $archivosGuardados[] = $nombreFinal;
                }
            }
            
            if (empty($archivosGuardados)) {
                self::respuestaJSON([
                    'success' => false,
                    'mensaje' => 'No se pudieron guardar los archivos'
                ]);
                return;
            }
            
            // Guardar en base de datos
            $resultado = CapHumDAO::guardarDocumentosBaja($registro_baja, $archivosGuardados);
            self::respuestaJSON($resultado);
            
        } catch (\Exception $e) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'Error al subir documentos: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Eliminar documento de una baja
     */
    public function eliminarDocumentoBaja()
    {
        try {
            $id_documento = $_POST['id_documento'] ?? null;
            
            if (!$id_documento) {
                self::respuestaJSON([
                    'success' => false,
                    'mensaje' => 'ID de documento requerido'
                ]);
                return;
            }
            
            $resultado = CapHumDAO::eliminarDocumentoBaja($id_documento);
            self::respuestaJSON($resultado);
            
        } catch (\Exception $e) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'Error al eliminar documento: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Ver documento PDF de una baja
     */
    public function verDocumentoBaja()
    {
        try {
            $nombreArchivo = $_GET['archivo'] ?? null;
            
            if (!$nombreArchivo) {
                http_response_code(400);
                echo 'Nombre de archivo requerido';
                exit;
            }
            
            // Validar que el nombre del archivo no contenga rutas relativas (seguridad)
            if (strpos($nombreArchivo, '..') !== false || strpos($nombreArchivo, '/') !== false) {
                http_response_code(403);
                echo 'Nombre de archivo inválido';
                exit;
            }
            
            $rutaArchivo = __DIR__ . '/../uploads/bajas/' . basename($nombreArchivo);
            
            if (!file_exists($rutaArchivo)) {
                http_response_code(404);
                echo 'Archivo no encontrado';
                exit;
            }
            
            // Limpiar buffer
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            // Establecer headers para PDF
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . basename($nombreArchivo) . '"');
            header('Content-Length: ' . filesize($rutaArchivo));
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            
            // Leer y enviar el archivo
            readfile($rutaArchivo);
            exit;
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo 'Error al cargar el archivo: ' . $e->getMessage();
            exit;
        }
    }

    /**
     * Obtener documentos de una persona (Gestión)
     */
    public function getDocumentosPersona()
    {
        try {
            $id_persona = $_GET['id_persona'] ?? null;

            if (!$id_persona) {
                self::respuestaJSON([
                    'success' => false,
                    'mensaje' => 'ID de persona requerido',
                    'datos' => []
                ]);
                return;
            }

            $resultado = CapHumDAO::getDocumentosPersona($id_persona);
            self::respuestaJSON($resultado);
        } catch (\Exception $e) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'Error al obtener documentos: ' . $e->getMessage(),
                'datos' => []
            ]);
        }
    }

    /**
     * Subir documentos de una persona (Gestión)
     */
    public function subirDocumentosPersona()
    {
        try {
            $id_persona = $_POST['id_persona'] ?? null;
            $id_documento = $_POST['id_documento'] ?? null;

            if (!$id_persona || !$id_documento) {
                self::respuestaJSON([
                    'success' => false,
                    'mensaje' => 'ID de persona e ID de documento requeridos'
                ]);
                return;
            }

            $archivos = $_FILES['archivosPDF'] ?? null;
            if (!$archivos || empty($archivos['name'][0])) {
                self::respuestaJSON([
                    'success' => false,
                    'mensaje' => 'No se seleccionaron archivos'
                ]);
                return;
            }

            $id_documento = (int) $id_documento;
            $carpeta = ($id_documento === 15) ? 'bajas' : 'documentos';
            $directorio = __DIR__ . '/../uploads/' . $carpeta . '/';
            if (!is_dir($directorio)) {
                mkdir($directorio, 0777, true);
            }

            $archivosGuardados = [];
            $nombres = is_array($archivos['name']) ? $archivos['name'] : [$archivos['name']];
            $tmpNames = is_array($archivos['tmp_name']) ? $archivos['tmp_name'] : [$archivos['tmp_name']];

            $errors = is_array($archivos['error']) ? $archivos['error'] : [$archivos['error']];
            foreach ($nombres as $i => $nombreOrig) {
                $tmp = $tmpNames[$i] ?? null;
                $err = $errors[$i] ?? UPLOAD_ERR_OK;
                if (!$tmp || $err !== UPLOAD_ERR_OK) {
                    continue;
                }
                $extension = strtolower(pathinfo($nombreOrig, PATHINFO_EXTENSION));
                if ($extension !== 'pdf') {
                    continue;
                }
                $nombreFinal = 'doc_' . $id_persona . '_' . $id_documento . '_' . time() . '_' . $i . '.pdf';
                $rutaFinal = $directorio . $nombreFinal;
                if (move_uploaded_file($tmp, $rutaFinal)) {
                    $archivosGuardados[] = $nombreFinal;
                }
            }

            if (empty($archivosGuardados)) {
                self::respuestaJSON([
                    'success' => false,
                    'mensaje' => 'No se pudieron guardar los archivos'
                ]);
                return;
            }

            $resultado = CapHumDAO::guardarDocumentosPersona($id_persona, $id_documento, $archivosGuardados);
            self::respuestaJSON($resultado);
        } catch (\Exception $e) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'Error al subir documentos: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Eliminar documento de una persona (Gestión)
     */
    public function eliminarDocumentoPersona()
    {
        try {
            $id_documento = $_POST['id_documento'] ?? null;

            if (!$id_documento) {
                self::respuestaJSON([
                    'success' => false,
                    'mensaje' => 'ID de documento requerido'
                ]);
                return;
            }

            $resultado = CapHumDAO::eliminarDocumentoPersona($id_documento);
            self::respuestaJSON($resultado);
        } catch (\Exception $e) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'Error al eliminar documento: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Ver documento PDF de una persona (Gestión)
     */
    public function verDocumentoPersona()
    {
        try {
            $nombreArchivo = $_GET['archivo'] ?? null;

            if (!$nombreArchivo) {
                http_response_code(400);
                echo 'Nombre de archivo requerido';
                exit;
            }

            if (strpos($nombreArchivo, '..') !== false || strpos($nombreArchivo, '/') !== false) {
                http_response_code(403);
                echo 'Nombre de archivo inválido';
                exit;
            }

            $nombreArchivo = basename($nombreArchivo);
            $carpetas = [__DIR__ . '/../uploads/documentos/', __DIR__ . '/../uploads/bajas/'];
            $rutaArchivo = null;
            foreach ($carpetas as $dir) {
                $ruta = $dir . $nombreArchivo;
                if (file_exists($ruta)) {
                    $rutaArchivo = $ruta;
                    break;
                }
            }

            if (!$rutaArchivo) {
                http_response_code(404);
                echo 'Archivo no encontrado';
                exit;
            }

            while (ob_get_level()) {
                ob_end_clean();
            }
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . $nombreArchivo . '"');
            header('Content-Length: ' . filesize($rutaArchivo));
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            readfile($rutaArchivo);
            exit;
        } catch (\Exception $e) {
            http_response_code(500);
            echo 'Error al cargar el archivo: ' . $e->getMessage();
            exit;
        }
    }

    /**
     * Guardar permisos de puestos
     */
    public function guardarPermisosPuestos()
    {
        try {
            $input = json_decode(file_get_contents("php://input"), true);
            
            $idPersona = $input['idPersona'] ?? null;
            $puestos = $input['puestos'] ?? [];
            
            if (!$idPersona) {
                self::respuestaJSON([
                    'success' => false,
                    'mensaje' => 'ID de persona requerido'
                ]);
                return;
            }
            
            $db = new \Core\Database();
            
            try {
                $db->beginTransaction();
                
                // Eliminar permisos actuales
                $idPersonaEsc = addslashes($idPersona);
                $db->queryOne("
                    DELETE FROM privilegios_departamento
                    WHERE idPersona = $idPersonaEsc
                ");
                
                // Insertar nuevos permisos
                if (!empty($puestos)) {
                    foreach ($puestos as $idPuesto) {
                        $idPuestoEsc = addslashes($idPuesto);
                        $db->queryOne("
                            INSERT INTO privilegios_departamento (idPersona, idPuesto)
                            VALUES ($idPersonaEsc, $idPuestoEsc)
                        ");
                    }
                }
                
                $db->commit();
                
                self::respuestaJSON([
                    'success' => true,
                    'mensaje' => 'Permisos guardados correctamente'
                ]);
                
            } catch (\Exception $e) {
                $db->rollback();
                throw $e;
            }
            
        } catch (\Exception $e) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'Error al guardar permisos: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Actualizar puesto individual de perfil
     */
    public function actualizarPuestoPerfil()
    {
        try {
            $input = json_decode(file_get_contents("php://input"), true);
            
            $idPersona = $input['idPersona'] ?? null;
            $idPuesto = $input['idPuesto'] ?? null;
            $asignado = $input['asignado'] ?? 0;
            
            if (!$idPersona || !$idPuesto) {
                self::respuestaJSON([
                    'success' => false,
                    'mensaje' => 'Datos incompletos'
                ]);
                return;
            }
            
            $db = new \Core\Database();
            
            if ($asignado === 1) {
                // Verificar si ya existe
                $idPersonaEsc = addslashes($idPersona);
                $idPuestoEsc = addslashes($idPuesto);
                
                $existe = $db->queryOne("
                    SELECT id
                    FROM privilegios_departamento
                    WHERE idPersona = $idPersonaEsc
                      AND idPuesto = $idPuestoEsc
                    LIMIT 1
                ");
                
                if (!$existe) {
                    // Insertar si no existe
                    $db->queryOne("
                        INSERT INTO privilegios_departamento (idPersona, idPuesto)
                        VALUES ($idPersonaEsc, $idPuestoEsc)
                    ");
                }
                
                self::respuestaJSON([
                    'success' => true,
                    'mensaje' => 'Puesto asignado correctamente'
                ]);
                
            } else {
                // Eliminar asignación
                $idPersonaEsc = addslashes($idPersona);
                $idPuestoEsc = addslashes($idPuesto);
                
                $db->queryOne("
                    DELETE FROM privilegios_departamento
                    WHERE idPersona = $idPersonaEsc
                      AND idPuesto = $idPuestoEsc
                ");
                
                self::respuestaJSON([
                    'success' => true,
                    'mensaje' => 'Puesto eliminado correctamente'
                ]);
            }
            
        } catch (\Exception $e) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'Error al actualizar puesto: ' . $e->getMessage()
            ]);
        }
    }
}