<?php

namespace Controllers;

use Core\Controller;
use Core\SecureUpload;
use Core\OcrIdentidad;
use Models\CapHum as CapHumDAO;
use Models\Candidatos as CandidatosDAO;
use Models\Notificacion;

class CapHum extends Controller
{
    /** Último error de enviarCorreo para mostrarlo en la respuesta JSON */
    private $enviarCorreoUltimoError = '';

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
                        
                        const codigoIsoPais = p.codigo_iso_pais || 'xx';
                        const nombrePais = p.nombre_pais || 'Sin país';
                        const sedeHTML = `
                            <small class="d-inline-flex align-items-center gap-1 mt-1 px-2 py-1 sede-glass-badge" title="${nombrePais}"
                                   style="background: rgba(255,255,255,0.7); backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px); border: 1px solid rgba(0,0,0,0.06); border-radius: 6px;">
                              <span class="text-muted fw-semibold" style="font-size: 0.75rem;">Sede:</span>
                              <span class="fi fi-${codigoIsoPais} fis" style="font-size: 1.1rem; border-radius: 2px; box-shadow: 0 1px 3px rgba(0,0,0,0.15);"></span>
                            </small>
                        `;

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
                                ${sedeHTML}
                                ${puestosHTML}
                                <hr class="my-2">
                                <small class="text-muted d-flex align-items-center gap-1">
                                    <i class="fa fa-user"></i>Jefe: <strong class="ms-1">${p.nombre_jefe || 'Sin jefe'}</strong>
                                </small>
                            `.trim(),
                            estatus: p.estatus,
                            acciones: (() => {
                                const puedeEditar = window.puedeEditarTodos;
                                const puedePermisos = window.puedeGestionarPermisos;
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
                                    ${puedePermisos ? `<button class="btn btn-sm" style="background-color: #D2D755; color: white;" onclick="edit_perfil(${p.id})" title="${tienePuestos ? 'Permisos (Gestionar múltiples puestos)' : 'Permisos'}">
                                        <i class="fa fa-lock" style="color: #007bff;"></i>
                                    </button>` : ''}
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
                                    <div class="fw-semibold d-flex align-items-center gap-2" style="color: #dc3545 !important;">
                                        <span class="bajas-easter-ghost-trigger" style="cursor:pointer;display:inline-flex;align-items:center;" title="Mantén pulsado 1,5 s"><i class="fa fa-ban" style="color: #dc3545 !important;"></i></span>
                                        <span style="color: #dc3545 !important;">Baja</span>
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
                    
                    // Siempre mostrar sección de múltiples puestos para poder agregar más
                    const usuarioData = usuariosData.find(u => u.id === id);
                    const labelPrincipal = document.getElementById('edit_label_principal');
                    const labelPuestoPrincipal = document.getElementById('edit_label_puesto_principal');
                    const tieneVarios = usuarioData && usuarioData.puestos && usuarioData.puestos.length > 1;
                    if (labelPrincipal) labelPrincipal.style.display = tieneVarios ? 'inline-block' : 'none';
                    if (labelPuestoPrincipal) labelPuestoPrincipal.style.display = tieneVarios ? 'inline-block' : 'none';
                    if (typeof cargarPuestosUsuario === 'function') {
                        cargarPuestosUsuario(id);
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

                    if (typeof precargarCascadaEdit === 'function') {
                         precargarCascadaEdit(
                             persona.id_pais,
                             persona.id_div_nivel1,
                             persona.id_div_nivel2
                         );
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
                if (typeof sincronizarPrincipalConListaPuestos === 'function') sincronizarPrincipalConListaPuestos();
            });
            document.getElementById('edit_departamento_id').addEventListener('change', function () {
                if (typeof sincronizarPrincipalConListaPuestos === 'function') sincronizarPrincipalConListaPuestos();
            });
                
           
            let currentPersonaId = null;
            let perfilAbortController = null;
            
            function edit_perfil(id) {
                var idPersonaRevisado = parseInt(id, 10);
                if (!idPersonaRevisado) {
                    Swal.fire("Error", "ID inválido", "error");
                    return;
                }
                currentPersonaId = idPersonaRevisado;

                if (perfilAbortController) {
                    perfilAbortController.abort();
                }
                perfilAbortController = new AbortController();
                const signal = perfilAbortController.signal;

                fetch('/CapHum/getDetallesPerfil', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ idPersona: idPersonaRevisado }),
                    signal: signal
                })
                .then(res => res.json())
                .then(data => {
                    if (perfilAbortController !== null && signal.aborted) return;
            
                    if (!data.success) {
                        Swal.fire("Error", data.mensaje, "error");
                        return;
                    }
            
                    if (!data.datos) {
                        Swal.fire("Error", "No se encontraron datos de la persona.", "error");
                        return;
                    }
            
                    const persona  = data.datos.persona || {};
                    if (Number(persona.id) !== currentPersonaId) return;
            
                    const perfiles = data.datos.perfiles || [];
                    const puestos  = data.datos.puestos  || [];
                    const asignacionActual = data.datos.asignacion_actual || {};

                    const nombreCompleto = [
                        persona.nombres,
                        persona.apellidop,
                        persona.apellidom
                    ].filter(Boolean).join(' ');

                    document.getElementById("edit_perfil_id").value = persona.id ?? '';
                    document.getElementById("edit_perfil_nombres").value = nombreCompleto;

                    // Subtítulo: usar asignación real (asigna_puesto) si existe; si no, fallback a puestos del perfil
                    let nombreDepartamento = asignacionActual.nombre_departamento || 'Sin departamento';
                    let nombrePuesto = asignacionActual.nombre_puesto || 'Sin puesto';
                    if (nombreDepartamento === 'Sin departamento' && nombrePuesto === 'Sin puesto') {
                        const puestoAsignado = puestos.find(p => Number(p.asignado_flag) === 1);
                        if (puestoAsignado) {
                            nombreDepartamento = puestoAsignado.nombre_departamento || nombreDepartamento;
                            nombrePuesto = puestoAsignado.nombre_puesto || nombrePuesto;
                        } else if (puestos.length > 0) {
                            nombreDepartamento = puestos[0].nombre_departamento || nombreDepartamento;
                            nombrePuesto = puestos[0].nombre_puesto || nombrePuesto;
                        }
                    }
                    
                    // Actualizar título del modal (solo ícono + texto fijo). Subtítulo con datos escapados para evitar HTML/script visible
                    var esc = function(s) { if (s == null || s === undefined) return ''; return ('' + s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;'); };
                    document.getElementById("modalEditPerfilLabel").innerHTML = '<i class="fa fa-user-shield me-2" style="color: #495057;"></i>Administrar puestos y módulos del usuario';
                    document.getElementById("modalEditPerfil_subtitle").innerHTML = 'Gestión de Permisos y Accesos para <strong>' + esc(nombreCompleto) + ' / ' + esc(nombreDepartamento) + ' / ' + esc(nombrePuesto) + '</strong>';
            
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
                    if (err.name === 'AbortError') return;
                    console.error(err);
                    Swal.fire("Error", "No se pudo cargar la información", "error");
                });
            }
            
            /* =========================
               PUESTOS
            ========================= */
            function renderPuestos(puestos) {

                const container = document.getElementById('modal-edit-perfil-puestos-form') || document.getElementById('puestos-form');
                if (!container) return;
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

                const container = document.getElementById('modal-edit-perfil-modulos-form') || document.getElementById('modulos-form');
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
                const container = document.getElementById('modal-edit-perfil-permisos-especiales-form') || document.getElementById('permisos-especiales-form');
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
                23: 'fa fa-calendar-alt',
                24: 'fa fa-file-pdf',
                '24': 'fa fa-file-pdf',
                43: 'fa fa-key',
                '43': 'fa fa-key'
            };
            function crearFilaModulo(mod) {
                const tr = document.createElement('tr');
                const tdName = document.createElement('td');
                tdName.className = 'fw-medium text-heading';
                tdName.style.paddingLeft = '1.5rem';
                const modId = mod.modulo_id != null ? mod.modulo_id : mod.id;
                const iconClass = iconosPermisosEspeciales[modId] || iconosPermisosEspeciales[Number(modId)];
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
                desc.style.marginLeft = iconClass ? '1.75rem' : '0';
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

            const container = document.getElementById('modal-edit-perfil-puestos-form') || document.getElementById('puestos-form');
            if (!container) return;
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
            accordion.id = 'modalEditPerfilAccordionPuestos';
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
                         data-bs-parent="#modalEditPerfilAccordionPuestos">
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
            
            // Función para expandir/colapsar todos los acordeones de puestos
            function expandirTodosPuestos() {
                const accordion = document.getElementById('modalEditPerfilAccordionPuestos') || document.getElementById('accordionPuestos');
                if (!accordion) return;

                const collapses = accordion.querySelectorAll('.accordion-collapse');
                const isAllExpanded = Array.from(collapses).every(c => c.classList.contains('show'));

                if (!isAllExpanded) {
                    collapses.forEach(collapse => {
                        collapse.removeAttribute('data-bs-parent');
                    });
                }

                collapses.forEach(collapse => {
                    const btn = document.querySelector(`[data-bs-target="#${collapse.id}"]`);
                    if (typeof bootstrap !== 'undefined' && bootstrap.Collapse) {
                        const bsCollapse = bootstrap.Collapse.getInstance(collapse) || new bootstrap.Collapse(collapse, { toggle: false });
                        if (isAllExpanded) {
                            bsCollapse.hide();
                            if (btn) { btn.classList.add('collapsed'); btn.setAttribute('aria-expanded', 'false'); }
                        } else {
                            bsCollapse.show();
                            if (btn) { btn.classList.remove('collapsed'); btn.setAttribute('aria-expanded', 'true'); }
                        }
                    } else {
                        if (isAllExpanded) {
                            collapse.classList.remove('show');
                            if (btn) { btn.classList.add('collapsed'); btn.setAttribute('aria-expanded', 'false'); }
                        } else {
                            collapse.classList.add('show');
                            if (btn) { btn.classList.remove('collapsed'); btn.setAttribute('aria-expanded', 'true'); }
                        }
                    }
                });

                if (isAllExpanded) {
                    collapses.forEach(collapse => {
                        collapse.setAttribute('data-bs-parent', '#modalEditPerfilAccordionPuestos');
                    });
                }

                const btnExpandir = document.querySelector('#modalEditPerfil #tabPuestos button[onclick="expandirTodosPuestos()"]') || document.querySelector('#tabPuestos button[onclick="expandirTodosPuestos()"]') || (typeof event !== 'undefined' && event.target);
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
                const modulosAsignar = [];
                const modulosEliminar = [];
                const puestosForm = document.getElementById('modal-edit-perfil-puestos-form') || document.getElementById('puestos-form');
                const modulosForm = document.getElementById('modal-edit-perfil-modulos-form') || document.getElementById('modulos-form');
                const checkboxesPuestos = puestosForm ? puestosForm.querySelectorAll('input[type="checkbox"]:checked') : [];
                const checkboxesModulos = modulosForm ? modulosForm.querySelectorAll('input[type="checkbox"]') : [];
                checkboxesPuestos.forEach(cb => {
                    if (cb.value) {
                        puestosSeleccionados.push(parseInt(cb.value));
                    }
                });
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
            
                const container = document.getElementById('modal-edit-perfil-modulos-form') || document.getElementById('modulos-form');
                if (!container) return;
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
            
                const iconosModulos = {
                    1: 'fa fa-file-invoice-dollar', 2: 'fa fa-folder-open', 3: 'fa fa-screwdriver-wrench',
                    4: 'fa fa-users', 5: 'fa fa-sitemap', 6: 'fa fa-chart-bar', 7: 'fa fa-file-alt',
                    10: 'fa fa-cog', 13: 'fa fa-user-minus', 14: 'fa fa-file-alt', 15: 'fa fa-hand-holding-dollar',
                    16: 'fa fa-cog', 17: 'fa fa-cog', 18: 'fa fa-dog', 19: 'fa fa-dog', 20: 'fa fa-building-columns',
                    21: 'fa fa-file-alt', 24: 'fa fa-chart-line', 25: 'fa fa-chart-line', 26: 'fa fa-chart-line',
                    27: 'fa fa-chart-line', 29: 'fa fa-chart-line', 30: 'fa fa-chart-line', 31: 'fa fa-chart-line',
                    32: 'fa fa-chart-line', 33: 'fa fa-chart-line', 34: 'fa fa-chart-line', 35: 'fa fa-chart-line',
                    36: 'fa fa-chart-line', 37: 'fa fa-chart-line', 38: 'fa fa-chart-line', 39: 'fa fa-chart-line', 40: 'fa fa-chart-line'
                };
            
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
                        
                        const modId = mod.modulo_id != null ? mod.modulo_id : mod.id;
                        const iconClass = iconosModulos[modId] || iconosModulos[Number(modId)] || 'fa fa-cube';
                        const iconoModulo = document.createElement('div');
                        iconoModulo.className = 'modulo-icon-box';
                        iconoModulo.style.width = '40px';
                        iconoModulo.style.height = '40px';
                        iconoModulo.style.borderRadius = '10px';
                        iconoModulo.style.background = 'rgba(26, 82, 168, 0.12)';
                        iconoModulo.style.border = '1px solid rgba(26, 82, 168, 0.25)';
                        iconoModulo.style.display = 'flex';
                        iconoModulo.style.alignItems = 'center';
                        iconoModulo.style.justifyContent = 'center';
                        iconoModulo.style.flexShrink = '0';
                        
                        const iconoInner = document.createElement('i');
                        iconoInner.className = iconClass;
                        iconoInner.style.color = '#1A52A8';
                        iconoInner.style.fontSize = '1rem';
                        iconoInner.title = mod.modulo_nombre ?? '';
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
                // Módulo change handler
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
            
                    //  LIMPIEZA CENTRALIZADA
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
            
                        //  Crear FormData
                        const formData = new FormData();
                        formData.append("idGestor", idGestor);
                        formData.append("motivo", motivoSelect);
                        formData.append("descripcion", descripcion);
            
                        // 📎 Archivos múltiples (AQUÍ ESTÁ EL CAMBIO)
                        archivosSeleccionados.forEach(file => {
                            formData.append('archivosPDF[]', file);
                        });
            
                        //  Enviar al controlador
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
            
                                //  Limpieza
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

                const departamento = document.getElementById("edit_departamento_id").value;  // <---- esta es la linea 2009
                const puesto       = document.getElementById("edit_id_puesto").value;
                const jefe         = document.getElementById("edit_id_jefe").value;
                const asignarLegion = document.getElementById("edit_asignar_legion") && document.getElementById("edit_asignar_legion").checked;
                const idLegion     = document.getElementById("edit_id_legion") ? document.getElementById("edit_id_legion").value : '';
                const id_div_nivel1 = document.getElementById('edit_id_div_nivel1')?.value || null;
                const id_div_nivel2 = document.getElementById('edit_id_div_nivel2')?.value || null;

                //  VALIDACIONES OBLIGATORIAS
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
                    puestos_adicionales: puestosAdicionales,
                    id_div_nivel1: id_div_nivel1 || null,
                    id_div_nivel2: id_div_nivel2 || null
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
                const id_pais = document.getElementById('add_id_pais').value;
                const id_puesto = document.getElementById('add_id_puesto').value;
                const departamento_id = document.getElementById('add_departamento_id').value;
                const id_jefe = document.getElementById('add_id_jefe').value;
                const asignarLegion = document.getElementById('add_asignar_legion').checked;
                const id_legion = document.getElementById('add_id_legion').value;
                
                const usuario = document.getElementById('add_usuario').value.trim();
                const contrasena = document.getElementById('add_contrasena').value.trim();
                const fecha_ingreso = document.getElementById('add_fecha_ingreso').value.trim() || null;

                const id_div_nivel1 = document.getElementById('add_id_div_nivel1')?.value || null;
                const id_div_nivel2 = document.getElementById('add_id_div_nivel2')?.value || null;
            
            
                //  Validaciones obligatorias (todos los campos)
                if (!nombres) return Swal.fire('Error', 'Los nombres son obligatorios', 'error');
                if (!apellidop) return Swal.fire('Error', 'El apellido paterno es obligatorio', 'error');
                if (!apellidom) return Swal.fire('Error', 'El apellido materno es obligatorio', 'error');
                if (!telefono) return Swal.fire('Error', 'El teléfono es obligatorio', 'error');
                if (!fecha_ingreso) return Swal.fire('Error', 'La fecha de ingreso es obligatoria', 'error');

                if (!id_pais) return Swal.fire('Error', 'Debe seleccionar un país (sede)', 'error');
                if (!id_puesto) return Swal.fire('Error', 'Debe seleccionar un puesto', 'error');
                if (!departamento_id) return Swal.fire('Error', 'Debe seleccionar un departamento', 'error');
            
                // ⚠️ jefe puede ser null, solo valida si viene
                if (id_jefe && isNaN(id_jefe)) {
                    return Swal.fire('Error', 'Jefe inválido', 'error');
                }
                
                //  Validar legión: si el checkbox está marcado, debe seleccionar una legión
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
                        id_pais: id_pais || 1,
                        id_div_nivel1: id_div_nivel1 || null,
                        id_div_nivel2: id_div_nivel2 || null,
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
                const ids = ['add_nombres', 'add_apellidop', 'add_apellidom', 'add_telefono', 'add_usuario', 'add_contrasena', 'add_num_telefono', 'add_id_pais'];
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
                    { data: 'estatus', title: 'Estatus', render: function(d) { return d != null ? d : ''; } },
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

        var archivosSeleccionadosReingreso = [];
        function abrirModalReingreso(idPersona, nombreCompleto) {
            document.getElementById('reingreso_id_persona').value = idPersona || '';
            document.getElementById('reingreso_gestor').innerHTML = '<strong>Gestor:</strong> ' + (nombreCompleto || 'N/A');
            document.getElementById('motivoReingreso').value = '';
            document.getElementById('reingreso_descripcion').value = '';
            document.getElementById('archivoPDFReingreso').value = '';
            archivosSeleccionadosReingreso = [];
            document.getElementById('reingreso_nombreArchivo').textContent = 'No se ha seleccionado ningún archivo';
            document.getElementById('listaArchivosReingreso').style.display = 'none';
            document.getElementById('listaArchivosReingreso').innerHTML = '';
            var modal = new bootstrap.Modal(document.getElementById('modalReingreso'));
            modal.show();
        }
        function agregarArchivosReingreso(input) {
            archivosSeleccionadosReingreso = Array.from(input.files || []);
            renderListaArchivosReingreso();
        }
        function renderListaArchivosReingreso() {
            var span = document.getElementById('reingreso_nombreArchivo');
            var list = document.getElementById('listaArchivosReingreso');
            if (!list) return;
            if (archivosSeleccionadosReingreso.length === 0) {
                span.textContent = 'No se ha seleccionado ningún archivo';
                list.style.display = 'none';
                list.innerHTML = '';
            } else {
                span.textContent = archivosSeleccionadosReingreso.length + ' archivo(s) seleccionado(s)';
                list.innerHTML = archivosSeleccionadosReingreso.map(function(f, i) {
                    return '<div class="d-flex align-items-center justify-content-between py-1 px-2 border rounded mb-1 small" style="background-color: rgba(0,0,0,0.03);">' +
                        '<span><i class="fa fa-file-pdf text-danger me-1"></i>' + (i + 1) + '. ' + (f.name || 'archivo') + '</span>' +
                        '<div class="d-flex gap-1">' +
                        '<button type="button" class="btn btn-sm btn-outline-info py-0 px-1" onclick="verArchivoReingreso(' + i + ')" title="Ver PDF"><i class="fa fa-eye"></i></button>' +
                        '<button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="quitarArchivoReingreso(' + i + ')" title="Quitar de la lista"><i class="fa fa-times"></i></button>' +
                        '</div></div>';
                }).join('');
                list.style.display = 'block';
            }
        }
        function verArchivoReingreso(index) {
            if (archivosSeleccionadosReingreso[index]) {
                var url = URL.createObjectURL(archivosSeleccionadosReingreso[index]);
                window.open(url, '_blank');
            }
        }
        function quitarArchivoReingreso(index) {
            archivosSeleccionadosReingreso.splice(index, 1);
            renderListaArchivosReingreso();
            document.getElementById('archivoPDFReingreso').value = '';
        }
        function confirmarReingreso() {
            var motivo = (document.getElementById('motivoReingreso') && document.getElementById('motivoReingreso').value) || '';
            var descripcion = (document.getElementById('reingreso_descripcion') && document.getElementById('reingreso_descripcion').value.trim()) || '';
            var idPersona = (document.getElementById('reingreso_id_persona') && document.getElementById('reingreso_id_persona').value) || '';
            if (!motivo) { Swal.fire({ icon: 'warning', title: 'Atención', text: 'Falta el motivo de reingreso. Selecciona un motivo.' }); return; }
            if (!descripcion) { Swal.fire({ icon: 'warning', title: 'Atención', text: 'Falta la descripción del reingreso. Escribe la descripción.' }); return; }
            if (!idPersona) { Swal.fire({ icon: 'warning', title: 'Atención', text: 'No se identificó al gestor. Cierra y vuelve a abrir desde el botón Reactivar.' }); return; }
            Swal.fire({ title: '¿Confirmar reingreso?', text: 'La persona pasará de Baja a Activo en la plantilla.', icon: 'question', showCancelButton: true, confirmButtonText: 'Sí, confirmar reingreso', cancelButtonText: 'Cancelar', reverseButtons: true }).then(function(result) {
                if (!result.isConfirmed) return;
                var formData = new FormData();
                formData.append('id_gestor', idPersona);
                formData.append('motivo_reingreso', motivo);
                formData.append('descripcion_reingreso', descripcion);
                archivosSeleccionadosReingreso.forEach(function(file) { formData.append('archivosPDF[]', file); });
                fetch('/CapHum/registrarReingreso', { method: 'POST', body: formData })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.success) {
                            Swal.fire({ icon: 'success', title: '¡Listo!', text: data.message || 'Reingreso registrado. La persona ha sido reactivada.' });
                            bootstrap.Modal.getInstance(document.getElementById('modalReingreso')).hide();
                            if (typeof getBajas === 'function') getBajas();
                        } else {
                            var errText = data.message || 'No se pudo registrar el reingreso.';
                            if (data.error) errText += ' ' + data.error;
                            Swal.fire({ icon: 'error', title: 'Error', text: errText });
                        }
                    })
                    .catch(function(err) { Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexión. ' + (err.message || '') }); });
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
                    'Documento Baja': 15,
                    'Documento reingreso': 16,
                    'Documento Reingreso': 16
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
                        
                        var contexto = obtenerContextoDocumento(doc.id_documento);
                        htmlTabla += `
                            <tr>
                                <td>${obtenerNombreDocumento(doc.id_documento)}</td>
                                <td>${contexto}</td>
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
                    tablaArchivos.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No hay archivos subidos</td></tr>';
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
            
            function obtenerContextoDocumento(idDocumento) {
                if (idDocumento == 15) return '<span class="badge bg-danger">Baja</span>';
                if (idDocumento == 16) return '<span class="badge bg-success">Reingreso</span>';
                return '<span class="badge bg-secondary">Gestión</span>';
            }
            function obtenerNombreDocumento(idDocumento) {
                const mapeo = {
                    8: 'CURP',
                    9: 'Identificación Oficial (INE)',
                    10: 'RFC',
                    11: 'Comprobante de Domicilio',
                    12: 'Acta de Nacimiento',
                    13: 'Certificado de Estudios',
                    14: 'Referencias Laborales',
                    15: 'Documento baja',
                    16: 'Documento reingreso'
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
            
            const mapaDocumentosIds = {
                'CURP': 8,
                'Identificación Oficial (INE)': 9,
                'RFC': 10,
                'Comprobante de Domicilio': 11,
                'Acta de Nacimiento': 12,
                'Certificado de Estudios': 13,
                'Referencias Laborales': 14,
                'Documento baja': 15,
                'Documento reingreso': 16
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
        // Easter egg "300" solo en Capital Humano → Gestión (Ctrl+Shift+3)
        $script .= "\n" . '<script src="/assets/js/gestiones-300-easter.js"></script>';
        $departamento = CapHumDAO::getConsultaDepartamentoGestor($_SESSION['usuario_id']);
        $modulos = $_SESSION['modulos'] ?? [];
        $puedeEditarTodos = in_array(10, $modulos);
        $puedeGestionarPermisos = in_array(43, $modulos);

        self::set("titulo", "Gestión de Usuarios");
        self::set("script", $script);
        self::set("departamento", $departamento);
        self::set("paisesActivos", \Models\Paises::getPaisesActivos());
        self::set("miUsuarioId", (int) $_SESSION['usuario_id']);
        self::set("puedeEditarTodos", $puedeEditarTodos);
        self::set("puedeGestionarPermisos", $puedeGestionarPermisos);
        self::render("all_gestores");
    }

    /** Vista Candidatos (Capital Humano). */
    public function candidatos()
    {
        $departamento = CapHumDAO::getConsultaDepartamentoGestor($_SESSION['usuario_id']);
        $candidatosResult = CandidatosDAO::getAll(null, null, null);
        $candidatos = (isset($candidatosResult['success']) && $candidatosResult['success'] && !empty($candidatosResult['datos']))
            ? $candidatosResult['datos']
            : [];

        self::set("titulo", "Candidatos");
        self::set("script", '');
        self::set("departamento", $departamento);
        self::set("paisesActivos", \Models\Paises::getPaisesActivos());
        self::set("listaJefes", CapHumDAO::getListaPersonasParaJefe());
        self::set("candidatos", $candidatos);
        self::set("appBasePath", rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/') ?: '');
        self::render("candidatos");
    }

    public function getCandidatos()
    {
        header("Content-Type: application/json");
        $estatus = isset($_GET["estatus"]) ? trim($_GET["estatus"]) : null;
        $id_departamento = isset($_GET["id_departamento"]) ? (int) $_GET["id_departamento"] : null;
        $id_puesto = isset($_GET["id_puesto"]) ? (int) $_GET["id_puesto"] : null;
        $resultado = CandidatosDAO::getAll($estatus, $id_departamento, $id_puesto);
        echo json_encode($resultado);
        exit;
    }

    /** Obtener un candidato por ID (para reenviar postulación). */
    public function getCandidato($id = null)
    {
        header("Content-Type: application/json");
        $id = (int) $id;
        if ($id <= 0) {
            echo json_encode(self::respuesta(false, 'ID inválido.', null));
            exit;
        }
        $resultado = CandidatosDAO::getById($id);
        echo json_encode($resultado);
        exit;
    }

    public function guardarCandidato()
    {
        header("Content-Type: application/json");
        $raw = file_get_contents("php://input");
        $data = json_decode($raw, true) ?: [];
        $resultado = CandidatosDAO::insert($data);
        if ($resultado['success'] && !empty($resultado['datos']['id'])) {
            CandidatosDAO::getOrCreateTokenDocumentos($resultado['datos']['id']);
        }
        echo json_encode($resultado);
        exit;
    }

    public function eliminarCandidato()
    {
        header("Content-Type: application/json");
        $raw = file_get_contents("php://input");
        $body = json_decode($raw, true) ?: [];
        $id = isset($body["id"]) ? (int) $body["id"] : 0;
        $resultado = CandidatosDAO::delete($id);
        echo json_encode($resultado);
        exit;
    }

    /** Actualizar candidato existente. */
    public function actualizarCandidato()
    {
        header("Content-Type: application/json");
        $raw = file_get_contents("php://input");
        $data = json_decode($raw, true) ?: [];
        $id = isset($data["id"]) ? (int) $data["id"] : 0;
        if ($id <= 0) {
            echo json_encode(self::respuesta(false, 'ID de candidato requerido.', null));
            exit;
        }
        $resultado = CandidatosDAO::update($id, $data);
        echo json_encode($resultado);
        exit;
    }

    /** Enviar correo de postulación al candidato. */
    public function enviarPostulacionCandidato()
    {
        header("Content-Type: application/json");
        $raw = file_get_contents("php://input");
        $body = json_decode($raw, true) ?: [];
        $id = isset($body["id"]) ? (int) $body["id"] : 0;
        $email = isset($body["email"]) ? trim($body["email"]) : "";
        if ($id <= 0) {
            echo json_encode(self::respuesta(false, "Datos insuficientes.", null));
            return;
        }
        $candidatoRes = CandidatosDAO::getById($id);
        if (!$candidatoRes['success'] || empty($candidatoRes['datos'])) {
            echo json_encode(self::respuesta(false, "Candidato no encontrado.", null));
            return;
        }
        $c = $candidatoRes['datos'];
        $nombreCompleto = trim(implode(' ', [
            $c['nombres'] ?? '',
            $c['segundo_nombre'] ?? '',
            $c['apellidop'] ?? '',
            $c['apellidom'] ?? ''
        ]));
        $puesto = $c['nombre_puesto'] ?? 'N/A';
        $departamento = $c['nombre_departamento'] ?? 'N/A';
        $destino = filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : ($c['email'] ?? '');
        if ($destino === '') {
            echo json_encode(self::respuesta(false, "Correo del candidato no válido.", null));
            return;
        }

        $resToken = CandidatosDAO::getOrCreateTokenDocumentos($id);
        $urlDocumentos = '';
        if ($resToken['success'] && !empty($resToken['datos'])) {
            $base = $this->obtenerBaseUrlApp();
            $urlDocumentos = rtrim($base, '/') . '/CapHum/subirDocumentosCandidato/' . $resToken['datos'];
        }

        $asunto = "Documentación requerida — Postulación de " . $nombreCompleto . " para " . $puesto;
        $diasLimite = 7;
        $contacto = '';
        $configFile = defined('RAIZ') ? (RAIZ . '/config/config.ini') : (__DIR__ . '/../config/config.ini');
        if (is_file($configFile)) {
            $full = @parse_ini_file($configFile, true);
            $mailSection = is_array($full['mail'] ?? null) ? $full['mail'] : [];
            $diasLimite = (int) ($mailSection['dias_limite_documentos'] ?? 7);
            $contacto = trim($mailSection['mail_contacto'] ?? $mailSection['mail_from'] ?? '');
        }
        if ($contacto === '') {
            $contacto = 'lazaro.gonzalez@__SPARTA_SECRET_REDACTED__.com';
        }
        $fechaLimiteObj = new \DateTime('now', new \DateTimeZone('America/Mexico_City'));
        $fechaLimiteObj->modify('+' . $diasLimite . ' days');
        $fechaLimite = $fechaLimiteObj->format('d/m/Y');
        $telefono = $c['telefono'] ?? '';
        // Ruta del logo para incrustar en el correo (cid:) — prioridad: logo_correo.png (sin fondo), luego logo___SPARTA_SECRET_REDACTED__.png
        $dirPublic = defined('RAIZ') ? dirname(RAIZ) . '/public' : (__DIR__ . '/../../public');
        $rutaLogoInline = null;
        if (is_file($dirPublic . '/assets/img/logo_correo.png')) {
            $rutaLogoInline = realpath($dirPublic . '/assets/img/logo_correo.png');
        } elseif (is_file($dirPublic . '/assets/img/logo___SPARTA_SECRET_REDACTED__.png')) {
            $rutaLogoInline = realpath($dirPublic . '/assets/img/logo___SPARTA_SECRET_REDACTED__.png');
        } elseif (is_file($dirPublic . '/assets/img/Logotipo-Maxikash-Outline.webp')) {
            $rutaLogoInline = realpath($dirPublic . '/assets/img/Logotipo-Maxikash-Outline.webp');
        } elseif (is_file($dirPublic . '/assets/img/logo.svg')) {
            $rutaLogoInline = realpath($dirPublic . '/assets/img/logo.svg');
        }
        $logoSrc = $rutaLogoInline ? 'cid:logo__SPARTA_SECRET_REDACTED__' : (rtrim($base, '/') . '/assets/img/logo_correo.png');

        $mensajeHtml = '<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Postulación MaxiKash</title>
</head>
<body style="margin:0; padding:0; background-color:#e8eef4; font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#e8eef4;">
    <tr>
      <td align="center" style="padding: 32px 16px;">
        <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="max-width:600px; width:100%; background-color:#ffffff; border-radius:8px; box-shadow: 0 2px 12px rgba(0,0,0,0.08);">
          <!-- Encabezado: título + logo -->
          <tr>
            <td style="background-color:#1e3a5f; padding: 24px 12px 24px 32px; border-radius: 8px 8px 0 0;">
              <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                <tr>
                  <td style="vertical-align: middle;">
                    <h1 style="margin:0; color:#ffffff; font-size: 22px; font-weight: 600; letter-spacing: 0.5px;">MaxiKash — Capital Humano</h1>
                    <p style="margin: 6px 0 0 0; color: rgba(255,255,255,0.9); font-size: 14px;">Postulación recibida</p>
                  </td>
                  <td style="vertical-align: middle; text-align: right; width: 160px; padding-left: 16px; padding-right: 8px;">
                    <img src="' . htmlspecialchars($logoSrc) . '" alt="MaxiKash" width="160" height="auto" style="display:block; max-height: 70px; width: auto; height: auto; margin-left: auto; margin-right: 0;" />
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <!-- Cuerpo -->
          <tr>
            <td style="padding: 32px;">
              <p style="margin:0 0 16px 0; color:#1a202c; font-size: 16px; line-height: 1.6;">Estimado/a <strong>' . htmlspecialchars($nombreCompleto) . '</strong>,</p>
              <p style="margin:0 0 16px 0; color:#2d3748; font-size: 15px; line-height: 1.6;">Gracias por postular al puesto de <strong>' . htmlspecialchars($puesto) . '</strong> en el departamento de <strong>' . htmlspecialchars($departamento) . '</strong>. Hemos recibido su solicitud; su número telefónico registrado es: <strong>' . htmlspecialchars($telefono) . '</strong>.</p>
              <p style="margin:0 0 12px 0; color:#2d3748; font-size: 15px; line-height: 1.6;">Para completar su postulación, acceda al siguiente enlace y cargue los documentos solicitados:</p>
              <p style="margin:0 0 24px 0;">
                <a href="' . htmlspecialchars($urlDocumentos) . '" style="display:inline-block; padding: 12px 24px; background-color:#2c5282; color:#ffffff !important; text-decoration:none; font-weight: 600; font-size: 14px; border-radius: 6px;">Abrir enlace para subir documentos</a>
              </p>
              <p style="margin:0 0 24px 0; color:#2d3748; font-size: 15px; line-height: 1.6;">Por favor suba la documentación a más tardar el <strong>' . htmlspecialchars($fechaLimite) . '</strong>. Si tiene algún problema con la carga, contáctenos en <a href="mailto:' . htmlspecialchars($contacto) . '" style="color:#2c5282; text-decoration:none;">' . htmlspecialchars($contacto) . '</a>.</p>
              <!-- Firma -->
              <table role="presentation" cellspacing="0" cellpadding="0" style="margin-top: 28px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
                <tr>
                  <td>
                    <p style="margin:0 0 4px 0; color:#2d3748; font-size: 15px;">Atentamente,</p>
                    <p style="margin:0 0 2px 0; color:#1a202c; font-size: 15px; font-weight: 600;">Capital Humano, Cobranza</p>
                    <p style="margin:0; color:#2c5282; font-size: 15px; font-weight: 600;">MaxiKash</p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <!-- Pie: no responder -->
          <tr>
            <td style="padding: 16px 32px 24px 32px; background-color:#f7fafc; border-radius: 0 0 8px 8px; border-top: 1px solid #e2e8f0;">
              <p style="margin:0; color:#718096; font-size: 12px; line-height: 1.5;">Este correo fue generado automáticamente. Por favor <strong>no responda directamente a este mensaje</strong>; para cualquier duda o aclaración utilice el correo de contacto indicado en el mensaje.</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>';

        $enviado = $this->enviarCorreo($destino, $asunto, $mensajeHtml, $nombreCompleto, $rutaLogoInline);
        if ($enviado) {
            echo json_encode(self::respuesta(true, "Postulación enviada por correo correctamente.", null));
        } else {
            $msg = $this->enviarCorreoUltimoError ?: "No se pudo enviar el correo. Configure SMTP en backend/config/config.ini, sección [mail] (smtp_host, smtp_user, smtp_pass).";
            echo json_encode(self::respuesta(false, $msg, null));
        }
        exit;
    }

    /**
     * Obtener o crear token para link de subida de documentos del candidato (JSON).
     * Requiere sesión. Retorna { success, token, url }.
     */
    public function getTokenDocumentosCandidato()
    {
        header("Content-Type: application/json");
        $raw = file_get_contents("php://input");
        $body = json_decode($raw, true) ?: [];
        $id = isset($body["id"]) ? (int) $body["id"] : 0;
        if ($id <= 0) {
            echo json_encode(self::respuesta(false, "ID de candidato requerido.", null));
            return;
        }
        $res = CandidatosDAO::getOrCreateTokenDocumentos($id);
        if (!$res['success']) {
            echo json_encode($res);
            return;
        }
        $base = $this->obtenerBaseUrlApp();
        $url = rtrim($base, '/') . '/CapHum/subirDocumentosCandidato/' . $res['datos'];
        echo json_encode(self::respuesta(true, 'OK', ['token' => $res['datos'], 'url' => $url]));
        exit;
    }

    /**
     * Vista pública para subir documentos del candidato (acceso por token en URL).
     * No requiere login. GET: muestra formulario; POST: recibe archivos.
     */
    public function subirDocumentosCandidato($token = null)
    {
        $token = $token ?? (isset($_GET['token']) ? trim($_GET['token']) : '');
        if ($token === '') {
            $this->subirDocumentosCandidatoError('Enlace no válido.');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->subirDocumentosCandidatoProcesar($token);
            return;
        }

        $res = CandidatosDAO::getCandidatoPorToken($token);
        if (!$res['success'] || empty($res['datos'])) {
            $this->subirDocumentosCandidatoError($res['mensaje'] ?? 'Enlace no válido o expirado.');
            return;
        }
        $candidato = $res['datos'];
        $id_candidato = (int) $candidato['id_candidato'];
        $nombreCompleto = trim(($candidato['nombres'] ?? '') . ' ' . ($candidato['apellidop'] ?? '') . ' ' . ($candidato['apellidom'] ?? ''));
        $tiposNombre = [
            'SOLICITUD INTERNA' => 1, 'CV O SOLICITUD DE TRABAJO' => 2, 'ACTA DE NACIMIENTO' => 3, 'ACTA DE NACIMIENTO Certificada' => 3, 'CURP' => 4,
            'IDENTIFICACIÓN OFICIAL' => 5, 'COMPROBANTE DE DOMICILIO' => 6, 'CONSTANCIA DE SITUACION FISCAL' => 7,
            'NÚMERO DE SEGURIDAD SOCIAL' => 8, 'HOJA DE RETENCION FONACOT O INFONAVIT' => 9, 'ESTADO DE CUENTA' => 10,
        ];
        $documentos_subidos = [];
        $resDocs = CandidatosDAO::getDocumentosCandidato($id_candidato);
        if ($resDocs['success'] && !empty($resDocs['datos'])) {
            foreach ($resDocs['datos'] as $d) {
                $tipo = trim($d['tipo_documento'] ?? '');
                if ($tipo === 'IDENTIFICACIÓN OFICIAL (REVERSO)') {
                    $documentos_subidos['5_reverso'] = $d;
                } elseif (isset($tiposNombre[$tipo])) {
                    $documentos_subidos[$tiposNombre[$tipo]] = $d;
                }
            }
        }
        $expediente_completo = (count($documentos_subidos) >= 11
            && isset($documentos_subidos['5_reverso'])
            && isset($documentos_subidos[1]) && isset($documentos_subidos[2]) && isset($documentos_subidos[3])
            && isset($documentos_subidos[4]) && isset($documentos_subidos[5]) && isset($documentos_subidos[6])
            && isset($documentos_subidos[7]) && isset($documentos_subidos[8]) && isset($documentos_subidos[9])
            && isset($documentos_subidos[10]));
        $this->set('token', $token);
        $this->set('nombre_candidato', $nombreCompleto);
        $this->set('id_candidato', $id_candidato);
        $this->set('documentos_subidos', $documentos_subidos);
        $this->set('expediente_completo', $expediente_completo);
        $this->render('subir_documentos_candidato', true);
    }

    /**
     * Llenar solicitud en línea: muestra el formulario HTML (solicitud___SPARTA_SECRET_REDACTED___v3) con datos del candidato prellenados.
     * No requiere login. URL: /CapHum/llenarSolicitudEnLinea/{token}
     */
    public function llenarSolicitudEnLinea($token = null)
    {
        $token = trim($token ?? '');
        if ($token === '') {
            header('HTTP/1.0 400 Bad Request');
            echo 'Enlace no válido.';
            return;
        }
        $res = CandidatosDAO::getCandidatoPorToken($token);
        if (!$res['success'] || empty($res['datos'])) {
            header('HTTP/1.0 404 Not Found');
            echo 'Enlace no válido o expirado.';
            return;
        }
        $id_candidato = (int) $res['datos']['id_candidato'];
        $candidatoRes = CandidatosDAO::getById($id_candidato);
        $c = ($candidatoRes['success'] && !empty($candidatoRes['datos'])) ? $candidatoRes['datos'] : $res['datos'];
        $fechaYmd = date('Y-m-d');
        $datosParaForm = [
            'fecha'      => $fechaYmd,
            'puesto'     => trim($c['nombre_puesto'] ?? ''),
            'ap_paterno' => trim($c['apellidop'] ?? ''),
            'ap_materno' => trim($c['apellidom'] ?? ''),
            'nombres'    => trim($c['nombres'] ?? '') . (trim($c['segundo_nombre'] ?? '') !== '' ? ' ' . trim($c['segundo_nombre']) : ''),
            'telefono'   => trim($c['telefono'] ?? ''),
            'correo'     => trim($c['email'] ?? ''),
            'departamento' => trim($c['nombre_departamento'] ?? ''),
        ];
        $dirPlantillas = defined('RAIZ') ? (RAIZ . '/storage/plantillas_candidatos') : (__DIR__ . '/../storage/plantillas_candidatos');
        $htmlPath = $dirPlantillas . '/solicitud_llenar.html';
        if (!is_file($htmlPath)) {
            header('HTTP/1.0 404 Not Found');
            echo 'Formulario no disponible. Contacte al área de Recursos Humanos.';
            return;
        }
        $html = file_get_contents($htmlPath);
        $script = '<script>window.__CANDIDATO__=' . json_encode($datosParaForm) . ';document.addEventListener("DOMContentLoaded",function(){var d=window.__CANDIDATO__||{};["fecha","puesto","ap_paterno","ap_materno","nombres","telefono","correo"].forEach(function(id){var el=document.getElementById(id);if(el&&d[id]!==undefined)el.value=d[id]||"";});});</script>';
        $html = str_replace('</body>', $script . "\n</body>", $html);
        header('Content-Type: text/html; charset=UTF-8');
        echo $html;
    }

    /**
     * Sirve la plantilla PDF con AcroForm (curp_1..curp_18) para que el formulario "Llenar solicitud en línea" la use al generar el documento.
     * No requiere login. URL: /CapHum/obtenerPlantillaSolicitudPdf
     */
    public function obtenerPlantillaSolicitudPdf()
    {
        $dirPlantillas = defined('RAIZ') ? (RAIZ . '/storage/plantillas_candidatos') : (__DIR__ . '/../storage/plantillas_candidatos');
        $archivo = $dirPlantillas . '/solicitud_interna___SPARTA_SECRET_REDACTED___AcroForm.pdf';
        if (!is_file($archivo)) {
            header('HTTP/1.0 404 Not Found');
            echo 'Plantilla no disponible.';
            return;
        }
        header('Content-Type: application/pdf');
        header('Cache-Control: public, max-age=3600');
        readfile($archivo);
    }

    /**
     * Descarga un documento para el candidato (carta no adeudo o solicitud interna prellenada).
     * No requiere login. URL: /CapHum/descargarDocumentoCandidato/{token}/{tipo}
     * tipo = carta_no_adeudo | solicitud_interna | solicitud_llenar
     * - solicitud_interna: descarga la plantilla en blanco (solicitud_interna___SPARTA_SECRET_REDACTED__.pdf, sin AcroForm)
     *   para que el candidato la llene como quiera (a mano o en computadora).
     * - solicitud_llenar: abre en el navegador el PDF con AcroForm para llenar en línea (legacy).
     */
    public function descargarDocumentoCandidato($token = null, $tipo = null)
    {
        $token = trim($token ?? '');
        $tipo = strtolower(trim($tipo ?? ''));
        if ($token === '' || !in_array($tipo, ['carta_no_adeudo', 'solicitud_interna', 'solicitud_llenar'], true)) {
            header('HTTP/1.0 400 Bad Request');
            echo 'Enlace no válido.';
            return;
        }
        $res = CandidatosDAO::getCandidatoPorToken($token);
        if (!$res['success'] || empty($res['datos'])) {
            header('HTTP/1.0 404 Not Found');
            echo 'Enlace no válido o expirado.';
            return;
        }
        $id_candidato = (int) $res['datos']['id_candidato'];
        $dirPlantillas = defined('RAIZ') ? (RAIZ . '/storage/plantillas_candidatos') : (__DIR__ . '/../storage/plantillas_candidatos');

        if ($tipo === 'carta_no_adeudo') {
            $archivo = $dirPlantillas . '/carta_no_adeudo_infonavit_fonacot.pdf';
            if (!is_file($archivo)) {
                header('HTTP/1.0 404 Not Found');
                echo 'Documento no disponible. Contacte al área de Recursos Humanos.';
                return;
            }
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="Carta_No_Adeudo_INFONAVIT_FONACOT.pdf"');
            readfile($archivo);
            return;
        }

        // PDF con AcroForm para llenar en línea en el navegador (solicitud_interna___SPARTA_SECRET_REDACTED___AcroForm.pdf)
        if ($tipo === 'solicitud_llenar') {
            $archivo = $dirPlantillas . '/solicitud_interna___SPARTA_SECRET_REDACTED___AcroForm.pdf';
            if (!is_file($archivo)) {
                header('HTTP/1.0 404 Not Found');
                echo 'Documento no disponible. Contacte al área de Recursos Humanos.';
                return;
            }
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="Solicitud_Interna_Maxikash_llenar.pdf"');
            readfile($archivo);
            return;
        }

        if ($tipo === 'solicitud_interna') {
            // Plantilla en blanco (sin AcroForm): el candidato la llena como quiera (a mano o en computadora)
            $archivo = $dirPlantillas . '/solicitud_interna___SPARTA_SECRET_REDACTED__.pdf';
            if (!is_file($archivo)) {
                header('HTTP/1.0 404 Not Found');
                echo 'Documento no disponible. Contacte al área de Recursos Humanos.';
                return;
            }
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="Solicitud_Interna_Maxikash.pdf"');
            readfile($archivo);
            return;
        }
    }

    /**
     * Datos del candidato para rellenar la solicitud PDF (todos los que tengamos).
     */
    private function datosCandidatoParaSolicitudPdf($c)
    {
        $nombreCompleto = trim(implode(' ', [
            $c['nombres'] ?? '',
            $c['segundo_nombre'] ?? '',
            $c['apellidop'] ?? '',
            $c['apellidom'] ?? ''
        ]));
        return [
            'nombre_completo' => $nombreCompleto,
            'nombres'         => trim($c['nombres'] ?? ''),
            'segundo_nombre'  => trim($c['segundo_nombre'] ?? ''),
            'apellidop'       => trim($c['apellidop'] ?? ''),
            'apellidom'       => trim($c['apellidom'] ?? ''),
            'email'           => trim($c['email'] ?? ''),
            'telefono'        => trim($c['telefono'] ?? ''),
            'puesto'          => trim($c['nombre_puesto'] ?? ''),
            'departamento'    => trim($c['nombre_departamento'] ?? ''),
            'fecha'           => date('d/m/Y'),
            'curp'            => trim($c['curp'] ?? ''),
        ];
    }

    /**
     * Rellena el PDF con AcroForm usando los datos del candidato.
     * Usa pdftk si está disponible y config/campos_solicitud_pdf.ini para mapear nombres de campo.
     * @param string $rutaPdf Ruta absoluta al PDF con formulario (ej. solicitud_interna___SPARTA_SECRET_REDACTED___AcroForm.pdf)
     * @param array $datosCandidato Array de clave => valor (nombre_completo, email, etc.)
     * @param string $dirConfig Directorio donde está config (para campos_solicitud_pdf.ini)
     * @return string|null PDF binario rellenado o null si falla o pdftk no disponible
     */
    private function rellenarPdfAcroFormConDatos($rutaPdf, array $datosCandidato, $dirConfig)
    {
        $configFile = $dirConfig . '/config.ini';
        $pdftkPath = null;
        if (is_file($configFile)) {
            $cfg = @parse_ini_file($configFile, true);
            $pdftkPath = isset($cfg['pdf']['pdftk_path']) ? trim($cfg['pdf']['pdftk_path']) : null;
        }
        if ($pdftkPath === null || $pdftkPath === '') {
            $pdftkPath = 'pdftk';
            // En Windows pdftk suele no estar en PATH; intentar rutas habituales
            if (DIRECTORY_SEPARATOR === '\\' || (defined('PHP_OS_FAMILY') && strtoupper(substr(PHP_OS_FAMILY, 0, 3)) === 'WIN')) {
                $rutasWindows = [
                    'C:\\Program Files (x86)\\PDFtk Server\\bin\\pdftk.exe',
                    'C:\\Program Files\\PDFtk Server\\bin\\pdftk.exe',
                    'C:\\pdftk\\bin\\pdftk.exe',
                ];
                foreach ($rutasWindows as $r) {
                    if (is_file($r)) {
                        $pdftkPath = $r;
                        break;
                    }
                }
            }
        }
        $rutaPdf = str_replace('\\', '/', realpath($rutaPdf));
        if ($rutaPdf === false || !is_file($rutaPdf)) {
            return null;
        }

        // Mapeo: nombre_campo_pdf => clave en $datosCandidato
        $mapeo = $this->mapeoCamposSolicitudPdf($dirConfig);
        $fieldValues = [];
        foreach ($mapeo as $nombreCampoPdf => $claveDato) {
            if (array_key_exists($claveDato, $datosCandidato)) {
                $v = $datosCandidato[$claveDato];
                if ($v !== '' && $v !== null) {
                    $fieldValues[$nombreCampoPdf] = $v;
                }
            }
        }
        // CURP: repartir cada carácter en curp_1, curp_2, ... curp_18 (cuadros del PDF)
        if (!empty($datosCandidato['curp'])) {
            $curp = strtoupper(preg_replace('/[^A-Z0-9]/', '', (string) $datosCandidato['curp']));
            $len = min(18, strlen($curp));
            for ($i = 1; $i <= 18; $i++) {
                $fieldValues['curp_' . $i] = ($i <= $len) ? $curp[$i - 1] : '';
            }
        }
        if (empty($fieldValues)) {
            return null;
        }

        $fdf = $this->generarFdfCampos($fieldValues);
        if ($fdf === '') {
            return null;
        }

        $tempDir = defined('RAIZ') ? (RAIZ . '/storage/tmp_mpdf') : (__DIR__ . '/../storage/tmp_mpdf');
        if (!is_dir($tempDir)) {
            @mkdir($tempDir, 0755, true);
        }
        if (!is_dir($tempDir)) {
            $tempDir = sys_get_temp_dir();
        }
        $fdfFile = $tempDir . '/solicitud_' . uniqid('', true) . '.fdf';
        $outFile = $tempDir . '/solicitud_' . uniqid('', true) . '.pdf';
        $written = @file_put_contents($fdfFile, $fdf);
        if ($written === false) {
            return null;
        }

        $cmd = sprintf(
            '%s %s fill_form %s output %s 2>&1',
            escapeshellarg($pdftkPath),
            escapeshellarg($rutaPdf),
            escapeshellarg($fdfFile),
            escapeshellarg($outFile)
        );
        $output = [];
        $ret = 0;
        @exec($cmd, $output, $ret);
        @unlink($fdfFile);
        if ($ret !== 0 || !is_file($outFile)) {
            if (is_file($outFile)) {
                @unlink($outFile);
            }
            error_log('CapHum::rellenarPdfAcroFormConDatos pdftk failed: ret=' . $ret . ' cmd=' . $cmd . ' output=' . implode(' ', $output));
            return null;
        }
        $pdfContent = @file_get_contents($outFile);
        @unlink($outFile);
        return $pdfContent !== false ? $pdfContent : null;
    }

    /**
     * Mapeo nombre_campo_pdf => clave_dato_candidato.
     * Si existe config/campos_solicitud_pdf.ini [campos] se usa; si no, mapeo por defecto.
     */
    private function mapeoCamposSolicitudPdf($dirConfig)
    {
        $ini = $dirConfig . '/campos_solicitud_pdf.ini';
        if (is_file($ini)) {
            $parsed = @parse_ini_file($ini, true);
            if (!empty($parsed['campos']) && is_array($parsed['campos'])) {
                return $parsed['campos'];
            }
        }
        return [
            'nombre_completo' => 'nombre_completo',
            'email'           => 'email',
            'telefono'        => 'telefono',
            'puesto'          => 'puesto',
            'departamento'    => 'departamento',
            'fecha'           => 'fecha',
        ];
    }

    /**
     * Genera contenido FDF para rellenar campos del PDF (sintaxis FDF 1.2).
     * Escapa correctamente los valores para el formato ( \ ( ) ).
     */
    private function generarFdfCampos(array $fieldValues)
    {
        $entries = [];
        foreach ($fieldValues as $name => $value) {
            $nameEscaped = $this->escapeFdfString((string) $name);
            $valueEscaped = $this->escapeFdfString((string) $value);
            $entries[] = "<< /T ($nameEscaped) /V ($valueEscaped) >>";
        }
        $fields = "[\n" . implode("\n", $entries) . "\n]";
        return "%FDF-1.2\n1 0 obj\n<< /FDF << /Fields $fields >> >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF";
    }

    private function escapeFdfString($s)
    {
        return str_replace([ '\\', '(', ')' ], [ '\\\\', '\\(', '\\)' ], $s);
    }

    private function subirDocumentosCandidatoError($mensaje)
    {
        $this->set('error_mensaje', $mensaje);
        $this->set('token', '');
        $this->render('subir_documentos_candidato', true);
    }

    /**
     * Listar documentos del expediente de un candidato (JSON). Requiere módulo Candidatos.
     * GET: id_candidato (query).
     */
    public function getDocumentosCandidatoList()
    {
        header('Content-Type: application/json; charset=utf-8');
        $id_candidato = (int) ($_GET['id_candidato'] ?? 0);
        if ($id_candidato <= 0) {
            echo json_encode(self::respuesta(false, 'id_candidato inválido.'));
            return;
        }
        $res = CandidatosDAO::getDocumentosCandidato($id_candidato);
        if (!$res['success']) {
            echo json_encode(self::respuesta(false, $res['mensaje'] ?? 'Error.', $res['datos'] ?? []));
            return;
        }
        $documentos = $res['datos'] ?? [];
        $verificacion = CandidatosDAO::getVerificacionExpediente($id_candidato);
        $payload = ['documentos' => $documentos];
        if ($verificacion !== null) {
            $payload['verificacion_expediente'] = $verificacion;
        }
        echo json_encode(self::respuesta(true, 'OK', $payload));
    }

    /**
     * Abrir/descargar un documento del expediente. Requiere módulo Candidatos.
     * GET: id (id del registro candidato_documento).
     */
    public function verDocumentoCandidato($id = null)
    {
        $id_doc = (int) ($id ?? $_GET['id'] ?? 0);
        if ($id_doc <= 0) {
            header('HTTP/1.0 400 Bad Request');
            echo 'ID inválido';
            return;
        }
        $res = CandidatosDAO::getDocumentoById($id_doc);
        if (!$res['success'] || empty($res['datos'])) {
            header('HTTP/1.0 404 Not Found');
            echo 'Documento no encontrado';
            return;
        }
        $doc = $res['datos'];
        $storageRoot = defined('RAIZ') ? (RAIZ . '/storage') : (__DIR__ . '/../storage');
        $rutaRel = trim($doc['ruta_archivo'] ?? '');
        $path = $storageRoot . '/' . $rutaRel;
        if (!is_file($path)) {
            header('HTTP/1.0 404 Not Found');
            echo 'Archivo no encontrado';
            return;
        }
        $nombre = $doc['nombre_archivo'] ?? basename($path);
        $ext = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
        $mimes = ['pdf' => 'application/pdf', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'doc' => 'application/msword', 'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        header('Content-Type: ' . ($mimes[$ext] ?? 'application/octet-stream'));
        header('Content-Disposition: inline; filename="' . str_replace('"', '\\"', $nombre) . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
    }

    /**
     * Eliminar un documento del expediente. Requiere módulo Candidatos.
     * POST o GET: id (id del registro candidato_documento).
     */
    public function eliminarDocumentoCandidato()
    {
        header('Content-Type: application/json; charset=utf-8');
        $id_doc = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
        if ($id_doc <= 0) {
            echo json_encode(self::respuesta(false, 'ID inválido.'));
            return;
        }
        $res = CandidatosDAO::getDocumentoById($id_doc);
        if (!$res['success'] || empty($res['datos'])) {
            echo json_encode(self::respuesta(false, 'Documento no encontrado.'));
            return;
        }
        $doc = $res['datos'];
        $storageRoot = defined('RAIZ') ? (RAIZ . '/storage') : (__DIR__ . '/../storage');
        $path = $storageRoot . '/' . trim($doc['ruta_archivo'] ?? '');
        CandidatosDAO::eliminarDocumento($id_doc);
        if ($path !== '' && is_file($path)) {
            @unlink($path);
        }
        echo json_encode(self::respuesta(true, 'Documento eliminado.'));
    }

    /**
     * URL base de la aplicación (para enlaces de documentos y correos).
     * Si en config.ini [app] base_url está definida se usa; si no, la petición actual.
     */
    private function obtenerBaseUrlApp()
    {
        $configFile = defined('RAIZ') ? (RAIZ . '/config/config.ini') : (__DIR__ . '/../config/config.ini');
        if (is_file($configFile)) {
            $config = @parse_ini_file($configFile, true);
            $base = trim($config['app']['base_url'] ?? '');
            if ($base !== '') {
                return $base;
            }
        }
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
            . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    }

    /**
     * Llama a la API validar-expediente (Python) con frente, reverso y PDFs del expediente.
     * @param array $rutas ['frente' => ruta, 'reverso' => ruta, 'curp' => ruta|null, 'nss' => ruta|null, 'constancia_fiscal' => ruta|null, 'acta_nacimiento' => ruta|null]
     * @return array|null Respuesta JSON de la API o null si no configurada/error
     */
    private function validarExpedienteApi(array $rutas)
    {
        $configFile = defined('RAIZ') ? (RAIZ . '/backend/config/config.ini') : (__DIR__ . '/../config/config.ini');
        if (!is_file($configFile)) {
            return null;
        }
        $config = @parse_ini_file($configFile, true);
        $apiUrlVerificar = trim($config['doc_verificacion']['api_url'] ?? '');
        $apiKey = trim($config['doc_verificacion']['api_key'] ?? '');
        if ($apiUrlVerificar === '' || $apiKey === '') {
            return null;
        }
        $baseUrl = preg_replace('#/verificar\s*$#', '', $apiUrlVerificar);
        $urlExp = rtrim($baseUrl, '/') . '/validar-expediente';
        $post = [
            'tipo_documento' => 'RESIDENCIA_TEMPORAL',
        ];
        $mimeImg = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'tiff' => 'image/tiff'];
        foreach (['frente', 'reverso'] as $key) {
            if (empty($rutas[$key]) || !is_file($rutas[$key])) {
                return null;
            }
            $ext = strtolower(pathinfo($rutas[$key], PATHINFO_EXTENSION));
            $post[$key] = new \CURLFile($rutas[$key], $mimeImg[$ext] ?? 'application/octet-stream', basename($rutas[$key]));
        }
        $pdfKeys = ['documento_curp' => 'curp', 'documento_nss' => 'nss', 'constancia_fiscal' => 'constancia_fiscal', 'acta_nacimiento' => 'acta_nacimiento'];
        foreach ($pdfKeys as $formKey => $pathKey) {
            if (!empty($rutas[$pathKey]) && is_file($rutas[$pathKey])) {
                $post[$formKey] = new \CURLFile($rutas[$pathKey], 'application/pdf', basename($rutas[$pathKey]));
            }
        }
        $ch = curl_init($urlExp);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $post,
            CURLOPT_HTTPHEADER => ['X-API-Key: ' . $apiKey],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_CONNECTTIMEOUT => 15,
        ]);
        $body = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode !== 200 || $body === false || $body === '') {
            return null;
        }
        $data = json_decode($body, true);
        return is_array($data) ? $data : null;
    }

    /**
     * Llama a la API de verificación de documentos (Python). Solo para imágenes.
     * @param string $rutaArchivo Ruta absoluta al archivo ya subido
     * @param string $ext Extensión del archivo (jpg, jpeg, png, etc.)
     * @return array|null ['resultado' => 'ORIGINAL'|'REVISION_MANUAL'|'RECHAZADO', 'mensaje' => string, 'score' => int] o null si no hay API configurada o hay error (usar fallback OcrIdentidad)
     */
    private function verificarDocumentoIdentidadApi($rutaArchivo, $ext)
    {
        $configFile = defined('RAIZ') ? (RAIZ . '/backend/config/config.ini') : (__DIR__ . '/../config/config.ini');
        if (!is_file($configFile)) {
            return null;
        }
        $config = @parse_ini_file($configFile, true);
        $apiUrl = trim($config['doc_verificacion']['api_url'] ?? '');
        $apiKey = trim($config['doc_verificacion']['api_key'] ?? '');
        if ($apiUrl === '' || $apiKey === '') {
            return null;
        }
        $mime = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'tiff' => 'image/tiff',
        ];
        $cfile = new \CURLFile($rutaArchivo, $mime[$ext] ?? 'application/octet-stream', basename($rutaArchivo));
        $post = ['imagen' => $cfile];
        $ch = curl_init($apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $post,
            CURLOPT_HTTPHEADER => [
                'X-API-Key: ' . $apiKey,
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 45,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);
        $body = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);
        if ($err !== '' || $httpCode !== 200 || $body === false || $body === '') {
            return null;
        }
        $data = json_decode($body, true);
        if (!is_array($data) || !isset($data['resultado'])) {
            return null;
        }
        $resultado = $data['resultado'];
        $mensaje = $data['recomendacion'] ?? $data['detail'] ?? 'Verificación completada';
        $score = (int) ($data['score_autenticidad'] ?? 0);
        return [
            'resultado' => $resultado,
            'mensaje' => $mensaje,
            'score' => $score,
        ];
    }

    private function subirDocumentosCandidatoProcesar($token)
    {
        header('Content-Type: application/json; charset=utf-8');
        $res = CandidatosDAO::getCandidatoPorToken($token);
        if (!$res['success'] || empty($res['datos'])) {
            echo json_encode(self::respuesta(false, $res['mensaje'] ?? 'Enlace no válido.'));
            return;
        }
        $id_candidato = (int) $res['datos']['id_candidato'];

        $tiposDocumento = [
            1  => 'SOLICITUD INTERNA',
            2  => 'CV O SOLICITUD DE TRABAJO',
            3  => 'ACTA DE NACIMIENTO Certificada',
            4  => 'CURP',
            5  => 'IDENTIFICACIÓN OFICIAL',
            6  => 'COMPROBANTE DE DOMICILIO',
            7  => 'CONSTANCIA DE SITUACION FISCAL',
            8  => 'NÚMERO DE SEGURIDAD SOCIAL',
            9  => 'HOJA DE RETENCION FONACOT O INFONAVIT',
            10 => 'ESTADO DE CUENTA',
        ];
        $slugPorTipo = [
            1 => 'solicitud_interna', 2 => 'cv', 3 => 'acta_nacimiento', 4 => 'curp',
            5 => 'identificacion_frente', 6 => 'comprobante_domicilio', 7 => 'constancia_fiscal',
            8 => 'nss', 9 => 'hoja_retencion', 10 => '__SPARTA_SECRET_REDACTED__',
        ];

        $dirBase = defined('RAIZ') ? (RAIZ . '/storage/candidatos') : (__DIR__ . '/../storage/candidatos');
        $dirExpediente = $dirBase . '/' . $id_candidato . '/expediente';
        if (!is_dir($dirBase)) {
            @mkdir($dirBase, 0755, true);
        }
        if (!is_dir(dirname($dirExpediente))) {
            @mkdir(dirname($dirExpediente), 0755, true);
        }
        if (!is_dir($dirExpediente)) {
            @mkdir($dirExpediente, 0755, true);
        }
        $guardados = 0;
        $errores = [];
        $permitidos = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
        $rutasParaValidar = ['frente' => null, 'reverso' => null, 'curp' => null, 'nss' => null, 'constancia_fiscal' => null, 'acta_nacimiento' => null];

        // Documentos ya subidos (no exigir ni permitir reemplazo)
        $yaSubidos = [];
        $listaDocsExistentes = [];
        $resDocs = CandidatosDAO::getDocumentosCandidato($id_candidato);
        if ($resDocs['success'] && !empty($resDocs['datos'])) {
            $listaDocsExistentes = $resDocs['datos'];
            foreach ($resDocs['datos'] as $d) {
                $tipo = trim($d['tipo_documento'] ?? '');
                if ($tipo === 'IDENTIFICACIÓN OFICIAL (REVERSO)') {
                    $yaSubidos['5_reverso'] = true;
                } else {
                    $num = array_search($tipo, $tiposDocumento, true);
                    if ($num !== false) {
                        $yaSubidos[(int) $num] = true;
                    }
                }
            }
        }
        $storageRoot = defined('RAIZ') ? (RAIZ . '/storage') : (__DIR__ . '/../storage');

        // Envío parcial: solo exigir que venga al menos un archivo nuevo (el candidato puede subir el resto después)
        $tieneAlgunoNuevo = false;
        for ($i = 1; $i <= 10; $i++) {
            if (!empty($yaSubidos[$i])) {
                continue;
            }
            $tiene = (isset($_FILES['archivo_' . $i]) && $_FILES['archivo_' . $i]['error'] === UPLOAD_ERR_OK && $_FILES['archivo_' . $i]['size'] > 0)
                || (isset($_FILES['archivo_' . $i . '_foto']) && $_FILES['archivo_' . $i . '_foto']['error'] === UPLOAD_ERR_OK && $_FILES['archivo_' . $i . '_foto']['size'] > 0);
            if ($tiene) {
                $tieneAlgunoNuevo = true;
                break;
            }
            if ($i === 5 && empty($yaSubidos['5_reverso'])) {
                $tieneReverso = isset($_FILES['archivo_5_reverso']) && $_FILES['archivo_5_reverso']['error'] === UPLOAD_ERR_OK && $_FILES['archivo_5_reverso']['size'] > 0;
                if ($tieneReverso) {
                    $tieneAlgunoNuevo = true;
                    break;
                }
            }
        }
        if (!$tieneAlgunoNuevo) {
            echo json_encode(self::respuesta(false, 'Selecciona al menos un documento para subir. Puedes enviar el resto más adelante.'));
            exit;
        }

        for ($i = 1; $i <= 10; $i++) {
            $key = 'archivo_' . $i;
            $fileKey = $key;
            if (!isset($_FILES[$key]) || $_FILES[$key]['error'] !== UPLOAD_ERR_OK || $_FILES[$key]['size'] <= 0) {
                $keyFoto = 'archivo_' . $i . '_foto';
                if (isset($_FILES[$keyFoto]) && $_FILES[$keyFoto]['error'] === UPLOAD_ERR_OK && $_FILES[$keyFoto]['size'] > 0) {
                    $fileKey = $keyFoto;
                } else {
                    continue;
                }
            }
            $nombreOriginal = basename($_FILES[$fileKey]['name'] ?? '');
            $ext = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
            if (!in_array($ext, $permitidos)) {
                $errores[] = ($tiposDocumento[$i] ?? $key) . ': tipo no permitido';
                continue;
            }
            $slug = $slugPorTipo[$i] ?? ('doc_' . $i);
            $nombreArchivo = $slug . '.' . $ext;
            $rutaDestino = $dirExpediente . '/' . $nombreArchivo;
            if (!move_uploaded_file($_FILES[$fileKey]['tmp_name'], $rutaDestino)) {
                $errores[] = $tiposDocumento[$i] ?? $key;
                continue;
            }
            $rutaRelativa = 'candidatos/' . $id_candidato . '/expediente/' . $nombreArchivo;
            $tipoNombre = $tiposDocumento[$i] ?? '';

            if ($i === 5) {
                $rutasParaValidar['frente'] = $rutaDestino;
                $formatosApi = ['jpg', 'jpeg', 'png', 'webp', 'tiff'];
                $validacionApi = in_array($ext, $formatosApi, true)
                    ? $this->verificarDocumentoIdentidadApi($rutaDestino, $ext)
                    : null;
                if ($validacionApi !== null) {
                    if ($validacionApi['resultado'] === 'RECHAZADO') {
                        @unlink($rutaDestino);
                        $rutasParaValidar['frente'] = null;
                        $errores[] = 'IDENTIFICACIÓN OFICIAL: ' . $validacionApi['mensaje'];
                        continue;
                    }
                } else {
                    $ocrValidator = new OcrIdentidad(null, $dirBase);
                    $candidatoParaOcr = null;
                    $candidatoRes = CandidatosDAO::getById($id_candidato);
                    if ($candidatoRes['success'] && !empty($candidatoRes['datos'])) {
                        $c = $candidatoRes['datos'];
                        $candidatoParaOcr = [
                            'nombres' => $c['nombres'] ?? '',
                            'apellidop' => $c['apellidop'] ?? '',
                            'apellidom' => $c['apellidom'] ?? '',
                            'curp' => $c['curp'] ?? '',
                        ];
                    }
                    $validacion = $ocrValidator->validarDocumentoIdentidad($rutaDestino, $candidatoParaOcr);
                    if (!$validacion['valido']) {
                        @unlink($rutaDestino);
                        $rutasParaValidar['frente'] = null;
                        $errores[] = 'IDENTIFICACIÓN OFICIAL: ' . $validacion['mensaje'];
                        continue;
                    }
                }
            }
            if ($i === 3) {
                $rutasParaValidar['acta_nacimiento'] = $rutaDestino;
            }
            if ($i === 4) {
                $rutasParaValidar['curp'] = $rutaDestino;
            }
            if ($i === 7) {
                $rutasParaValidar['constancia_fiscal'] = $rutaDestino;
            }
            if ($i === 8) {
                $rutasParaValidar['nss'] = $rutaDestino;
            }

            $guardar = CandidatosDAO::guardarDocumento($id_candidato, $nombreOriginal, $rutaRelativa, $tipoNombre);
            if ($guardar['success']) {
                $guardados++;
            }
        }

        // Reverso de identificación oficial → expediente/identificacion_reverso.{ext}
        if (isset($_FILES['archivo_5_reverso']) && $_FILES['archivo_5_reverso']['error'] === UPLOAD_ERR_OK && $_FILES['archivo_5_reverso']['size'] > 0) {
            $nombreOriginal = basename($_FILES['archivo_5_reverso']['name'] ?? '');
            $ext = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
            if (in_array($ext, $permitidos)) {
                $nombreArchivo = 'identificacion_reverso.' . $ext;
                $rutaDestino = $dirExpediente . '/' . $nombreArchivo;
                if (move_uploaded_file($_FILES['archivo_5_reverso']['tmp_name'], $rutaDestino)) {
                    $rutasParaValidar['reverso'] = $rutaDestino;
                    $rutaRelativa = 'candidatos/' . $id_candidato . '/expediente/' . $nombreArchivo;
                    $guardar = CandidatosDAO::guardarDocumento($id_candidato, $nombreOriginal, $rutaRelativa, 'IDENTIFICACIÓN OFICIAL (REVERSO)');
                    if ($guardar['success']) {
                        $guardados++;
                    }
                }
            }
        }

        // Rellenar rutas ya existentes para validar expediente (frente/reverso/PDFs)
        foreach ($listaDocsExistentes as $d) {
            $rutaRel = trim($d['ruta_archivo'] ?? '');
            if ($rutaRel === '' || !is_file($storageRoot . '/' . $rutaRel)) {
                continue;
            }
            $pathAbs = $storageRoot . '/' . $rutaRel;
            $tipo = trim($d['tipo_documento'] ?? '');
            if ($tipo === 'IDENTIFICACIÓN OFICIAL' && $rutasParaValidar['frente'] === null) {
                $rutasParaValidar['frente'] = $pathAbs;
            } elseif ($tipo === 'IDENTIFICACIÓN OFICIAL (REVERSO)' && $rutasParaValidar['reverso'] === null) {
                $rutasParaValidar['reverso'] = $pathAbs;
            } elseif ($tipo === 'CURP' && $rutasParaValidar['curp'] === null) {
                $rutasParaValidar['curp'] = $pathAbs;
            } elseif ($tipo === 'NÚMERO DE SEGURIDAD SOCIAL' && $rutasParaValidar['nss'] === null) {
                $rutasParaValidar['nss'] = $pathAbs;
            } elseif ($tipo === 'CONSTANCIA DE SITUACION FISCAL' && $rutasParaValidar['constancia_fiscal'] === null) {
                $rutasParaValidar['constancia_fiscal'] = $pathAbs;
            } elseif (($tipo === 'ACTA DE NACIMIENTO' || $tipo === 'ACTA DE NACIMIENTO Certificada') && $rutasParaValidar['acta_nacimiento'] === null) {
                $rutasParaValidar['acta_nacimiento'] = $pathAbs;
            }
        }

        $payload = ['guardados' => $guardados];
        if ($guardados > 0) {
            $tiposNombre = [
                'SOLICITUD INTERNA' => 1, 'CV O SOLICITUD DE TRABAJO' => 2, 'ACTA DE NACIMIENTO' => 3, 'ACTA DE NACIMIENTO Certificada' => 3, 'CURP' => 4,
                'IDENTIFICACIÓN OFICIAL' => 5, 'COMPROBANTE DE DOMICILIO' => 6, 'CONSTANCIA DE SITUACION FISCAL' => 7,
                'NÚMERO DE SEGURIDAD SOCIAL' => 8, 'HOJA DE RETENCION FONACOT O INFONAVIT' => 9, 'ESTADO DE CUENTA' => 10,
            ];
            $documentosSubidosPayload = [];
            $resDocsNuevo = CandidatosDAO::getDocumentosCandidato($id_candidato);
            if ($resDocsNuevo['success'] && !empty($resDocsNuevo['datos'])) {
                foreach ($resDocsNuevo['datos'] as $d) {
                    $tipo = trim($d['tipo_documento'] ?? '');
                    if ($tipo === 'IDENTIFICACIÓN OFICIAL (REVERSO)') {
                        $documentosSubidosPayload['5_reverso'] = ['nombre_archivo' => $d['nombre_archivo'] ?? 'reverso'];
                    } elseif (isset($tiposNombre[$tipo])) {
                        $documentosSubidosPayload[$tiposNombre[$tipo]] = ['nombre_archivo' => $d['nombre_archivo'] ?? 'documento'];
                    }
                }
            }
            $payload['documentos_subidos'] = $documentosSubidosPayload;
            $expedienteCompleto = (count($documentosSubidosPayload) >= 11
                && isset($documentosSubidosPayload['5_reverso'])
                && isset($documentosSubidosPayload[1]) && isset($documentosSubidosPayload[2]) && isset($documentosSubidosPayload[3])
                && isset($documentosSubidosPayload[4]) && isset($documentosSubidosPayload[5]) && isset($documentosSubidosPayload[6])
                && isset($documentosSubidosPayload[7]) && isset($documentosSubidosPayload[8]) && isset($documentosSubidosPayload[9])
                && isset($documentosSubidosPayload[10]));
            $payload['expediente_completo'] = $expedienteCompleto;
            if ($expedienteCompleto) {
                $candidatoRes = CandidatosDAO::getById($id_candidato);
                $nombreCompleto = 'Candidato';
                if ($candidatoRes['success'] && !empty($candidatoRes['datos'])) {
                    $c = $candidatoRes['datos'];
                    $nombreCompleto = trim(($c['nombres'] ?? '') . ' ' . ($c['apellidop'] ?? '') . ' ' . ($c['apellidom'] ?? ''));
                    if ($nombreCompleto === '') {
                        $nombreCompleto = 'Candidato';
                    }
                }
                $mensaje = 'El candidato ' . $nombreCompleto . ' ha cargado todos los documentos.';
                $idPersonas = Notificacion::getPersonasConModulos([42]);
                if (empty($idPersonas)) {
                    error_log('CapHum: expediente completo pero ningún usuario con módulo 42 (Candidatos). Asigne el módulo a al menos un usuario para recibir notificaciones.');
                } else {
                    Notificacion::crearParaPersonas($idPersonas, 'candidato_expediente_completo', $mensaje, null);
                }
            }
        }
        if ($guardados > 0 && $rutasParaValidar['frente'] && $rutasParaValidar['reverso']) {
            $resultadoApi = $this->validarExpedienteApi($rutasParaValidar);
            if ($resultadoApi !== null) {
                $payload['validacion_expediente'] = [
                    'todo_coincide' => $resultadoApi['todo_coincide'] ?? false,
                    'foto_rechazada' => $resultadoApi['foto_rechazada'] ?? false,
                    'curp_definitivo' => $resultadoApi['curp_definitivo'] ?? null,
                    'checks_ok' => $resultadoApi['checks_ok'] ?? 0,
                    'checks_totales' => $resultadoApi['checks_totales'] ?? 0,
                    'alertas' => $resultadoApi['alertas'] ?? [],
                    'identificacion_frente_score' => $resultadoApi['identificacion_frente_score'] ?? null,
                    'identificacion_reverso_score' => $resultadoApi['identificacion_reverso_score'] ?? null,
                    'comparaciones' => $resultadoApi['comparaciones'] ?? null,
                ];
                CandidatosDAO::updateVerificacionExpediente($id_candidato, $payload['validacion_expediente']);
            }
        }

        if ($guardados > 0) {
            echo json_encode(self::respuesta(true, 'Se subieron ' . $guardados . ' documento(s) correctamente.', $payload));
        } else {
            echo json_encode(self::respuesta(false, count($errores) ? implode(', ', $errores) : 'No se envió ningún archivo. Selecciona al menos un documento.'));
        }
        exit;
    }

    /**
     * Lee variables MAIL_* del archivo .env en la raíz del proyecto (sin depender de getenv/putenv).
     * @return array<string, string>
     */
    private function leerEnvMail(): array
    {
        $env = [];
        $root = defined('RAIZ') ? dirname(RAIZ) : (__DIR__ . '/../..');
        $envFile = $root . '/.env';
        if (!is_file($envFile) || !is_readable($envFile)) {
            return $env;
        }
        $lines = @file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!is_array($lines)) {
            return $env;
        }
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0) {
                continue;
            }
            $eq = strpos($line, '=');
            if ($eq === false) {
                continue;
            }
            $key = trim(substr($line, 0, $eq));
            if ($key === '' || strpos($key, 'MAIL_') !== 0) {
                continue;
            }
            $value = trim(str_replace(["\r", "\n"], '', substr($line, $eq + 1)));
            if (preg_match('/^["\'](.+)["\']\s*$/s', $value, $m)) {
                $value = trim($m[1]);
            }
            $env[$key] = $value;
        }
        return $env;
    }

    /**
     * Envía un correo HTML usando PHPMailer (solo SMTP).
     * @param string $para Email del destinatario
     * @param string $asunto Asunto
     * @param string $cuerpoHtml Cuerpo en HTML
     * @param string $nombreDestinatario Nombre para el encabezado
     * @param string|null $rutaLogoInline Ruta absoluta al logo para incrustar (cid:) — si existe se adjunta y se usa cid:logo__SPARTA_SECRET_REDACTED__ en el HTML
     * @return bool
     */
    private function enviarCorreo($para, $asunto, $cuerpoHtml, $nombreDestinatario = '', $rutaLogoInline = null)
    {
        $autoload = defined('RAIZ') ? (RAIZ . '/libs/PHPMailer/vendor/autoload.php') : (__DIR__ . '/../libs/PHPMailer/vendor/autoload.php');
        if (!is_file($autoload)) {
            $this->enviarCorreoUltimoError = 'PHPMailer no encontrado.';
            error_log('CapHum::enviarCorreo: PHPMailer autoload no encontrado: ' . $autoload);
            return false;
        }
        require_once $autoload;

        // Prioridad: .env leído directo (evita fallos getenv en Windows) → config.ini [mail]
        $envMail = $this->leerEnvMail();
        $config = defined('CONFIGURACION') && is_array(CONFIGURACION) ? CONFIGURACION : [];
        $mailSection = [];
        $configFile = defined('RAIZ') ? (RAIZ . '/config/config.ini') : (__DIR__ . '/../config/config.ini');
        if (is_file($configFile)) {
            $full = @parse_ini_file($configFile, true);
            $mailSection = is_array($full['mail'] ?? null) ? $full['mail'] : [];
        }

        $get = function ($key, $iniKey, $default = '') use ($envMail, $mailSection, $config) {
            $v = $envMail[$key] ?? $mailSection[$iniKey] ?? $config[$iniKey] ?? $default;
            return is_string($v) ? $v : (string) $v;
        };

        $mailFrom     = trim($get('MAIL_FROM', 'mail_from', ''));
        $mailFromName = trim($get('MAIL_FROM_NAME', 'mail_from_name', 'Recursos Humanos'));
        $smtpHost     = trim($get('MAIL_SMTP_HOST', 'smtp_host', ''));
        $smtpUser     = trim($get('MAIL_SMTP_USER', 'smtp_user', ''));
        $smtpPassRaw  = $get('MAIL_SMTP_PASS', 'smtp_pass', '');
        // Contraseña de aplicación Gmail: 16 caracteres sin espacios; solo ASCII para evitar caracteres invisibles
        $smtpPass    = preg_replace('/\s+/', '', $smtpPassRaw);
        $smtpPass    = preg_replace('/[^\x20-\x7E]/', '', $smtpPass);
        $smtpPass    = trim($smtpPass);
        $smtpPort    = (int) ($get('MAIL_SMTP_PORT', 'smtp_port', '587') ?: 587);
        $smtpSecure  = strtolower(trim($get('MAIL_SMTP_SECURE', 'smtp_secure', 'tls')));
        $driver      = strtolower(trim($get('MAIL_DRIVER', 'mail_driver', 'smtp')));
        $fromEmail   = $mailFrom !== '' ? $mailFrom : $smtpUser;
        $fromName    = $mailFromName !== '' ? $mailFromName : 'Recursos Humanos';

        // --- SendGrid: solo API key, sin SMTP ni puertos (recomendado en Windows/XAMPP) ---
        if ($driver === 'sendgrid') {
            $apiKey = trim($get('MAIL_SENDGRID_API_KEY', 'sendgrid_api_key', ''));
            if ($apiKey === '' || $fromEmail === '') {
                $this->enviarCorreoUltimoError = 'Con MAIL_DRIVER=sendgrid configure MAIL_SENDGRID_API_KEY y MAIL_FROM en .env. Cree cuenta en sendgrid.com, verifique el remitente y genere una API key.';
                return false;
            }
            $payload = [
                'personalizations' => [['to' => [['email' => $para, 'name' => $nombreDestinatario ?: $para]]]],
                'from'             => ['email' => $fromEmail, 'name' => $fromName],
                'subject'          => $asunto,
                'content'          => [['type' => 'text/html', 'value' => $cuerpoHtml]],
            ];
            if ($rutaLogoInline !== null && is_file($rutaLogoInline)) {
                $payload['attachments'] = [[
                    'content'      => base64_encode(file_get_contents($rutaLogoInline)),
                    'type'         => (strtolower(pathinfo($rutaLogoInline, PATHINFO_EXTENSION)) === 'svg') ? 'image/svg+xml' : 'image/png',
                    'filename'     => 'logo.png',
                    'disposition'  => 'inline',
                    'content_id'   => 'logo__SPARTA_SECRET_REDACTED__',
                ]];
            }
            $ch = curl_init('https://api.sendgrid.com/v3/mail/send');
            curl_setopt_array($ch, [
                CURLOPT_POST            => true,
                CURLOPT_POSTFIELDS      => json_encode($payload),
                CURLOPT_HTTPHEADER      => [
                    'Authorization: Bearer ' . $apiKey,
                    'Content-Type: application/json',
                ],
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_TIMEOUT         => 15,
            ]);
            $response = curl_exec($ch);
            $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr  = curl_error($ch);
            curl_close($ch);
            if ($curlErr !== '') {
                $this->enviarCorreoUltimoError = 'SendGrid: ' . $curlErr;
                return false;
            }
            if ($httpCode === 202) {
                $this->enviarCorreoUltimoError = '';
                return true;
            }
            $errBody = is_string($response) ? $response : '';
            $decoded = json_decode($errBody, true);
            $msg    = isset($decoded['errors'][0]['message']) ? $decoded['errors'][0]['message'] : ($errBody ?: "HTTP {$httpCode}");
            $this->enviarCorreoUltimoError = 'SendGrid: ' . $msg;
            return false;
        }

        // --- PHP mail(): útil en servidores donde ya está configurado (no suele funcionar en XAMPP) ---
        if ($driver === 'mail') {
            if ($fromEmail === '') {
                $this->enviarCorreoUltimoError = 'Con MAIL_DRIVER=mail configure MAIL_FROM en .env.';
                return false;
            }
            $headers = "From: " . ($fromName ? "\"{$fromName}\" " : '') . "<{$fromEmail}>\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $ok = @mail($para, $asunto, $cuerpoHtml, $headers);
            if (!$ok) {
                $this->enviarCorreoUltimoError = 'mail() falló. En XAMPP/Windows suele no estar configurado; use MAIL_DRIVER=sendgrid (recomendado) o smtp.';
                return false;
            }
            $this->enviarCorreoUltimoError = '';
            return true;
        }

        // --- SMTP (PHPMailer) ---
        // Gmail: por defecto usamos 465/SSL (más fiable en Windows). Para probar 587/TLS pon MAIL_GMAIL_FORCE_587=1 en .env
        if ($smtpHost === 'smtp.gmail.com') {
            $force587 = !empty($envMail['MAIL_GMAIL_FORCE_587']) && trim($envMail['MAIL_GMAIL_FORCE_587']) !== '0';
            if (!$force587 && ($smtpPort === 587 || $smtpSecure === 'tls')) {
                $smtpPort   = 465;
                $smtpSecure = 'ssl';
            }
        }
        $smtpConfigurado = $smtpHost !== '' && $smtpUser !== '';
        if (!$smtpConfigurado) {
            $this->enviarCorreoUltimoError = 'Para enviar correos configure .env: MAIL_DRIVER=sendgrid + MAIL_SENDGRID_API_KEY (recomendado), o SMTP (smtp_host, smtp_user, smtp_pass).';
            return false;
        }
        if ($smtpPass === '') {
            $this->enviarCorreoUltimoError = 'Contraseña SMTP vacía. O use MAIL_DRIVER=sendgrid con API key para evitar SMTP.';
            return false;
        }

        // Diagnóstico: confirmar qué valores usamos (sin escribir la contraseña)
        $rootEnv     = defined('RAIZ') ? dirname(RAIZ) : (__DIR__ . '/../..');
        $envPath     = $rootEnv . '/.env';
        $fromEnv     = isset($envMail['MAIL_SMTP_USER']) ? 'sí' : 'no';
        $passLen     = strlen($smtpPass);
        $passLenRaw  = strlen(trim($smtpPassRaw));
        error_log(sprintf(
            'CapHum SMTP: env=%s exists=%s host=%s port=%s user=%s pass_len=%d pass_raw_len=%d secure=%s from_env=%s',
            $envPath,
            is_file($envPath) ? 'y' : 'n',
            $smtpHost,
            $smtpPort,
            $smtpUser,
            $passLen,
            $passLenRaw,
            $smtpSecure,
            $fromEnv
        ));

        $smtpDebugLog = '';
        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';
            $langPath = defined('RAIZ') ? (RAIZ . '/libs/PHPMailer/vendor/phpmailer/phpmailer/language/') : (__DIR__ . '/../libs/PHPMailer/vendor/phpmailer/phpmailer/language/');
            if (is_dir($langPath)) {
                $mail->setLanguage('es', $langPath);
            }
            $mail->isHTML(true);
            $mail->Subject = $asunto;
            $mail->Body    = $cuerpoHtml;
            $mail->AltBody = strip_tags(preg_replace('/<br\s*\/?>/i', "\n", $cuerpoHtml));
            $mail->addAddress($para, $nombreDestinatario ?: '');

            if ($mailFrom !== '') {
                $mail->setFrom($mailFrom, $mailFromName ?: 'Recursos Humanos');
            } else {
                $mail->setFrom($smtpUser, $mailFromName ?: 'Recursos Humanos');
            }
            if ($rutaLogoInline !== null && is_file($rutaLogoInline)) {
                $mail->addEmbeddedImage($rutaLogoInline, 'logo__SPARTA_SECRET_REDACTED__', 'logo.png');
            }

            $mail->isSMTP();
            $mail->Host       = $smtpHost;
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtpUser;
            $mail->Password   = $smtpPass;
            $mail->Port       = $smtpPort;
            if ($smtpSecure === 'ssl') {
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            }
            // Gmail: forzar AUTH LOGIN (evita CRAM-MD5 que a veces falla) y relajar verificación SSL en Windows por si el CA falla
            if ($smtpHost === 'smtp.gmail.com') {
                $mail->AuthType = 'LOGIN';
                $mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer'       => false,
                        'verify_peer_name'  => false,
                    ],
                ];
            }

            $smtpDebugLog = '';
            $mail->SMTPDebug = 2;
            $mail->Debugoutput = function ($str) use (&$smtpDebugLog) {
                $smtpDebugLog .= $str . "\n";
            };

            $mail->send();
            $this->enviarCorreoUltimoError = '';
            return true;
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            // En el log guardamos la respuesta SMTP completa para ver el 535 exacto de Gmail
            if ($smtpDebugLog !== '') {
                error_log('CapHum SMTP debug (últimas líneas): ' . trim(substr($smtpDebugLog, -800)));
                $lines = explode("\n", $smtpDebugLog);
                $lastLine = trim($lines[count($lines) - 1] ?? '');
                if (strpos($lastLine, '535') !== false || stripos($lastLine, 'auth') !== false) {
                    $msg .= "\n\nGmail rechazó usuario/contraseña. Compruebe: 1) Contraseña de aplicación (no la de la cuenta). 2) Verificación en 2 pasos activada. 3) Si es Google Workspace, el admin debe permitir contraseñas de aplicación. Se está usando puerto 465/SSL y AUTH LOGIN.";
                }
            }
            $this->enviarCorreoUltimoError = $msg;
            error_log('CapHum::enviarCorreo: ' . $e->getMessage());
            return false;
        }
    }

    public function bajas()
    {
        // Reutilizar el mismo script de gestion() pero cambiando solo la inicialización
        // Primero obtenemos el script de gestion() y lo modificamos
        $scriptGestion = <<<'HTML'
        <script>
        
            // ----- Reingreso (modal Reactivar): necesario en vista Bajas -----
            var archivosSeleccionadosReingreso = [];
            function abrirModalReingreso(idPersona, nombreCompleto) {
                document.getElementById('reingreso_id_persona').value = idPersona || '';
                document.getElementById('reingreso_gestor').innerHTML = '<strong>Gestor:</strong> ' + (nombreCompleto || 'N/A');
                document.getElementById('motivoReingreso').value = '';
                document.getElementById('reingreso_descripcion').value = '';
                document.getElementById('archivoPDFReingreso').value = '';
                archivosSeleccionadosReingreso = [];
                document.getElementById('reingreso_nombreArchivo').textContent = 'No se ha seleccionado ningún archivo';
                document.getElementById('listaArchivosReingreso').style.display = 'none';
                document.getElementById('listaArchivosReingreso').innerHTML = '';
                var modal = new bootstrap.Modal(document.getElementById('modalReingreso'));
                modal.show();
            }
            function agregarArchivosReingreso(input) {
                archivosSeleccionadosReingreso = Array.from(input.files || []);
                renderListaArchivosReingreso();
            }
            function renderListaArchivosReingreso() {
                var span = document.getElementById('reingreso_nombreArchivo');
                var list = document.getElementById('listaArchivosReingreso');
                if (!list) return;
                if (archivosSeleccionadosReingreso.length === 0) {
                    span.textContent = 'No se ha seleccionado ningún archivo';
                    list.style.display = 'none';
                    list.innerHTML = '';
                } else {
                    span.textContent = archivosSeleccionadosReingreso.length + ' archivo(s) seleccionado(s)';
                    list.innerHTML = archivosSeleccionadosReingreso.map(function(f, i) {
                        return '<div class="d-flex align-items-center justify-content-between py-1 px-2 border rounded mb-1 small" style="background-color: rgba(0,0,0,0.03);">' +
                            '<span><i class="fa fa-file-pdf text-danger me-1"></i>' + (i + 1) + '. ' + (f.name || 'archivo') + '</span>' +
                            '<div class="d-flex gap-1">' +
                            '<button type="button" class="btn btn-sm btn-outline-info py-0 px-1" onclick="verArchivoReingreso(' + i + ')" title="Ver PDF"><i class="fa fa-eye"></i></button>' +
                            '<button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="quitarArchivoReingreso(' + i + ')" title="Quitar de la lista"><i class="fa fa-times"></i></button>' +
                            '</div></div>';
                    }).join('');
                    list.style.display = 'block';
                }
            }
            function verArchivoReingreso(index) {
                if (archivosSeleccionadosReingreso[index]) {
                    var url = URL.createObjectURL(archivosSeleccionadosReingreso[index]);
                    window.open(url, '_blank');
                }
            }
            function quitarArchivoReingreso(index) {
                archivosSeleccionadosReingreso.splice(index, 1);
                renderListaArchivosReingreso();
                document.getElementById('archivoPDFReingreso').value = '';
            }
            function confirmarReingreso() {
                var motivo = (document.getElementById('motivoReingreso') && document.getElementById('motivoReingreso').value) || '';
                var descripcion = (document.getElementById('reingreso_descripcion') && document.getElementById('reingreso_descripcion').value.trim()) || '';
                var idPersona = (document.getElementById('reingreso_id_persona') && document.getElementById('reingreso_id_persona').value) || '';
                if (!motivo) { Swal.fire({ icon: 'warning', title: 'Atención', text: 'Falta el motivo de reingreso. Selecciona un motivo.' }); return; }
                if (!descripcion) { Swal.fire({ icon: 'warning', title: 'Atención', text: 'Falta la descripción del reingreso. Escribe la descripción.' }); return; }
                if (!idPersona) { Swal.fire({ icon: 'warning', title: 'Atención', text: 'No se identificó al gestor. Cierra y vuelve a abrir desde el botón Reactivar.' }); return; }
                Swal.fire({ title: '¿Confirmar reingreso?', text: 'La persona pasará de Baja a Activo en la plantilla.', icon: 'question', showCancelButton: true, confirmButtonText: 'Sí, confirmar reingreso', cancelButtonText: 'Cancelar', reverseButtons: true }).then(function(result) {
                    if (!result.isConfirmed) return;
                    var formData = new FormData();
                    formData.append('id_gestor', idPersona);
                    formData.append('motivo_reingreso', motivo);
                    formData.append('descripcion_reingreso', descripcion);
                    archivosSeleccionadosReingreso.forEach(function(file) { formData.append('archivosPDF[]', file); });
                    fetch('/CapHum/registrarReingreso', { method: 'POST', body: formData })
                        .then(function(r) { return r.json(); })
                        .then(function(data) {
                            if (data.success) {
                                Swal.fire({ icon: 'success', title: '¡Listo!', text: data.message || 'Reingreso registrado. La persona ha sido reactivada.' });
                                bootstrap.Modal.getInstance(document.getElementById('modalReingreso')).hide();
                                if (typeof getBajas === 'function') getBajas();
                            } else {
                                var errText = data.message || 'No se pudo registrar el reingreso.';
                                if (data.error) errText += ' ' + data.error;
                                Swal.fire({ icon: 'error', title: 'Error', text: errText });
                            }
                    })
                    .catch(function(err) { Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexión. ' + (err.message || '') }); });
                });
            }
            // ----- Fin reingreso -----
        
            const getUsuarios = () => {
            http.request({
                endpoint: "/caphum/getUsuarios",
                onSuccess: (resp) => {
                    const datos = resp.datos.map(p => {
                        const codisoP = p.codigo_iso_pais || 'xx';
                        const nomPaisP = p.nombre_pais || 'Sin país';
                        const sedeBadge = `
                            <small class="d-inline-flex align-items-center gap-1 mt-1 px-2 py-1 sede-glass-badge" title="${nomPaisP}"
                                   style="background: rgba(255,255,255,0.7); backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px); border: 1px solid rgba(0,0,0,0.06); border-radius: 6px;">
                              <span class="text-muted fw-semibold" style="font-size: 0.75rem;">Sede:</span>
                              <span class="fi fi-${codisoP} fis" style="font-size: 1.1rem; border-radius: 2px; box-shadow: 0 1px 3px rgba(0,0,0,0.15);"></span>
                            </small>
                        `;
                        return {
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
                            ${sedeBadge}
                            <small class="text-muted d-flex align-items-center gap-1">
                                <i class="fa fa-building"></i>
                                ${p.nombre_departamento}
                            </small>
                            <small class="text-muted d-flex align-items-center gap-1">
                                <i class="fa fa-briefcase"></i>
                                ${p.nombre_puesto}
                            </small>
                            <hr class="my-2">
                            <small class="text-muted d-flex align-items-center gap-1">
                                <i class="fa fa-user"></i>Jefe: <strong class="ms-1">${p.nombre_jefe || 'Sin jefe'}</strong>
                            </small>
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
                            ${(typeof window.puedeGestionarPermisos !== 'undefined' && window.puedeGestionarPermisos) ? `<button class="btn btn-sm" style="background-color: #D2D755; color: white;" onclick="edit_perfil(${p.id})" title="Permisos">
                                <i class="fa fa-lock" style="color: #007bff;"></i>
                            </button>` : ''}
                        </div>`
                    };
                    });
        
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
                                <div class="fw-semibold d-flex align-items-center gap-2" style="color: #dc3545 !important;">
                                    <span class="bajas-easter-ghost-trigger" style="cursor:pointer;display:inline-flex;align-items:center;" title="Mantén pulsado 1,5 s"> <i class="fa fa-ban" style="color: #dc3545 !important;"></i></span>
                                    <span style="color: #dc3545 !important;">Baja</span>
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
                                <div class="d-flex gap-2 flex-wrap align-items-center justify-content-start">
                                    <button class="btn btn-sm btn-success" onclick="abrirModalReingreso(${p.id}, '${(nombreCompleto || '').replace(/'/g, "\\'")}')" title="Reactivar (registrar reingreso)" aria-label="Reactivar">
                                        <i class="fa fa-user-check"></i>
                                    </button>
                                    <button class="btn btn-sm btn-info" onclick="cargarDocumentoBaja(this)" 
                                        data-registro-baja="${p.registro_baja ?? ''}" 
                                        data-nombre="${nombreCompleto.replace(/"/g, '&quot;')}" 
                                        title="Cargar documento" aria-label="Cargar documento">
                                        <i class="fa fa-file"></i>
                                    </button>
                                </div>
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
                
                // Ocultar panel de indicadores de Gestión y mostrar panel de Bajas
                $('#panelIndicadoresGestion').hide();
                $('#panelIndicadoresBajas').show();

                // Revelar celdas KPI ahora que el panel es visible
                if (typeof window.kpiRevealCellsB === 'function') {
                    setTimeout(window.kpiRevealCellsB, 120);
                }
                
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
                                // Recargar datos con el filtro de fecha
                                getBajas();
                            } else if (selectedDates.length === 0) {
                                rangoFechasBajas = null;
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
                        { data: 'estatus', title: 'Estatus', render: function(d) { return d != null ? d : ''; } },
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
                    'Documento Baja': 15,
                    'Documento reingreso': 16,
                    'Documento Reingreso': 16
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
                        
                        var contexto = obtenerContextoDocumento(doc.id_documento);
                        htmlTabla += `
                            <tr>
                                <td>${obtenerNombreDocumento(doc.id_documento)}</td>
                                <td>${contexto}</td>
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
                    tablaArchivos.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No hay archivos subidos</td></tr>';
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
            
            function obtenerContextoDocumento(idDocumento) {
                if (idDocumento == 15) return '<span class="badge bg-danger">Baja</span>';
                if (idDocumento == 16) return '<span class="badge bg-success">Reingreso</span>';
                return '<span class="badge bg-secondary">Gestión</span>';
            }
            function obtenerNombreDocumento(idDocumento) {
                const mapeo = {
                    8: 'CURP',
                    9: 'Identificación Oficial (INE)',
                    10: 'RFC',
                    11: 'Comprobante de Domicilio',
                    12: 'Acta de Nacimiento',
                    13: 'Certificado de Estudios',
                    14: 'Referencias Laborales',
                    15: 'Documento baja',
                    16: 'Documento reingreso'
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
            
            const mapaDocumentosIds = {
                'CURP': 8,
                'Identificación Oficial (INE)': 9,
                'RFC': 10,
                'Comprobante de Domicilio': 11,
                'Acta de Nacimiento': 12,
                'Certificado de Estudios': 13,
                'Referencias Laborales': 14,
                'Documento baja': 15,
                'Documento reingreso': 16
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

        // Easter egg Bajas: Ctrl+Shift+B -> "Adios espartano" + carabelas; mousedown en icono Baja -> boo.mp3 + fantasma
        // Código en /assets/js/bajas-easter.js para evitar "</script>" inline y error "Unexpected token '<'"
        $bajasEaster = '<style>.bajas-easter-wrap{position:fixed;inset:0;z-index:1058;pointer-events:none;overflow:hidden}.bajas-easter-caravel{position:absolute;font-size:3.2rem;opacity:0.95;pointer-events:none;top:12%;left:2%;animation:bajasCaravelSail 4.5s linear 0s forwards}.bajas-easter-caravel:nth-child(2){top:26%;left:2%;animation-delay:0.3s}.bajas-easter-caravel:nth-child(3){top:42%;left:2%;animation-delay:0.1s}.bajas-easter-caravel:nth-child(4){top:58%;left:2%;animation-delay:0.5s}.bajas-easter-caravel:nth-child(5){top:74%;left:2%;animation-delay:0.2s}.bajas-easter-caravel:nth-child(6){top:18%;left:2%;animation-delay:0.7s}.bajas-easter-caravel:nth-child(7){top:52%;left:2%;animation-delay:0.4s}.bajas-easter-caravel:nth-child(8){top:34%;left:2%;animation-delay:0.15s}@keyframes bajasCaravelSail{0%{transform:translateX(0) rotate(-5deg);opacity:0.92}100%{transform:translateX(100vw) rotate(5deg);opacity:0.88}}.bajas-easter-toast{position:fixed;left:50%;top:50%;transform:translate(-50%,-50%);z-index:1060;background:linear-gradient(135deg,#1e293b 0%,#334155 100%);color:#fbbf24;padding:24px 48px;border-radius:16px;font-size:1.2rem;font-weight:700;box-shadow:0 16px 48px rgba(0,0,0,0.4);border:2px solid #b45309;opacity:0;animation:bajasEasterIn .35s ease forwards;pointer-events:none;text-align:center}.bajas-easter-toast .bajas-easter-emoji{font-size:2.5rem;display:block;margin-bottom:8px}@keyframes bajasEasterIn{0%{opacity:0;transform:translate(-50%,-50%) scale(0.8)}100%{opacity:1;transform:translate(-50%,-50%) scale(1)}}@keyframes bajasEasterOut{0%{opacity:1;transform:translate(-50%,-50%) scale(1)}100%{opacity:0;transform:translate(-50%,-50%) scale(0.9)}}.bajas-ghost-float{position:fixed;z-index:1062;pointer-events:none;font-size:2rem;opacity:0;animation:bajasGhostRise 3.2s ease-out forwards}.bajas-ghost-float .bajas-ghost-emoji{display:block;text-shadow:0 0 20px rgba(255,255,255,0.6);filter:drop-shadow(0 0 8px rgba(255,255,255,0.4))}@keyframes bajasGhostRise{0%{opacity:0.95;transform:translate(-50%,-50%) scale(0.4)}15%{opacity:1;transform:translate(-50%,-60px) scale(1.2)}40%{opacity:1;transform:translate(-50%,-140px) scale(2.8)}70%{opacity:0.9;transform:translate(-50%,-260px) scale(4.5)}100%{opacity:0;transform:translate(-50%,-400px) scale(8)}}</style>';
        $bajasEaster .= '<script src="/assets/js/bajas-easter.js"></script>';
        $scriptGestion .= "\n" . $bajasEaster;

        $modulos = $_SESSION['modulos'] ?? [];
        self::set("titulo", "Personas dadas de baja");
        self::set("script", $scriptGestion);
        self::set("departamento", ['datos' => []]); // Array vacío para no romper la vista
        self::set("puedeEditarTodos", in_array(10, $modulos));
        self::set("puedeGestionarPermisos", in_array(43, $modulos));
        self::set("miUsuarioId", (int) ($_SESSION['usuario_id'] ?? 0));
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
                'id_puesto' => $p['id_puesto'] ?? null,
                'id_departamento' => $p['id_departamento'] ?? null,
                'estatus' => $p['estatus'] ?? '',
                'usuario' => $p['usuario'] ?? '',
                'id_pais' => $p['id_pais'] ?? 0,
                'nombre_pais' => $p['nombre_pais'] ?? 'Sin país',
                'codigo_iso_pais' => $p['codigo_iso_pais'] ?? 'xx',
                'fecha_ingreso' => $p['fecha_ingreso'] ?? null,
                'fecha_registro' => $p['fecha_registro'] ?? null,
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
                        'id' => $p['id'] ?? $p['numero_empleado'] ?? null,
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
        // Siempre usar el id de la persona que se está revisando (fila en Gestión), nunca el de la sesión
        $idPersona = isset($input['idPersona']) ? (int) $input['idPersona'] : null;

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

    public function getEstados()
{
    $input   = json_decode(file_get_contents("php://input"), true);
    $id_pais = $input['id_pais'] ?? null;

    if (empty($id_pais)) {
        self::respuestaJSON([
            'success' => false,
            'mensaje' => 'ID de país requerido'
        ]);
        return;
    }

    self::respuestaJSON(
        \Models\CapHum::getEstadosPorPais($id_pais)
    );
}

public function getMunicipios()
{
    $input     = json_decode(file_get_contents("php://input"), true);
    $id_estado = $input['id_estado'] ?? null;

    if (empty($id_estado)) {
        self::respuestaJSON([
            'success' => false,
            'mensaje' => 'ID de estado requerido'
        ]);
        return;
    }

    self::respuestaJSON(
        \Models\CapHum::getMunicipiosPorEstado($id_estado)
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

        // Deduplicar por id de persona (quien tiene dos cargos no debe salir dos veces)
        $deduplicarPorPersona = function ($lista) {
            if (!is_array($lista) || empty($lista)) return $lista;
            $vistos = [];
            $out = [];
            foreach ($lista as $row) {
                $id = isset($row['id']) ? (int) $row['id'] : 0;
                if ($id && !isset($vistos[$id])) {
                    $vistos[$id] = true;
                    $out[] = $row;
                }
            }
            return array_values($out);
        };

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
                $datos = $deduplicarPorPersona($datos);
                self::respuestaJSON(['success' => true, 'mensaje' => 'Jefes encontrados.', 'datos' => $datos]);
                return;
            }
        }

        // 2) Jefes por es_jefe=1 en el departamento (o puesto id 8)
        $detalles = CapHumDAO::getConsultaJefe($idDepartamento);
        if ($detalles['success'] && !empty($detalles['datos'])) {
            $datos = $deduplicarPorPersona($detalles['datos']);
            self::respuestaJSON(['success' => true, 'mensaje' => $detalles['mensaje'] ?? 'Jefes encontrados.', 'datos' => $datos]);
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
            $datos = $deduplicarPorPersona($datos);
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
        session_start(); //  IMPORTANTE
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
            exit; //  CLAVE
        }

        $resultado = CapHumDAO::UpdatePersona($input);

        echo json_encode($resultado);
        exit; //  CLAVE
    }

    ///////
    public function Organigrama()
    {
        $script = <<<HTML
            <script>
               
            </script>
        HTML;

        // Organigrama: mostrar todos los departamentos para que cualquier usuario pueda elegir uno
        $departamentos = CapHumDAO::getTodosDepartamentos();

        $getDepartamentos = '<option disabled selected>Seleccione una opción</option>';

        if (!empty($departamentos['datos'])) {
            foreach ($departamentos['datos'] as $val2) {
                $getDepartamentos .= '<option value="' . (int)$val2['id'] . '">' . htmlspecialchars($val2['nombre'], ENT_QUOTES, 'UTF-8') . '</option>';
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

    /** Puestos de una persona (para organigrama). Si idDepartamento se envía, solo puestos de ese departamento. */
    public function getPuestosPorPersona()
    {
        $input = json_decode(file_get_contents("php://input"), true);
        $idPersona = (int) ($input['idPersona'] ?? 0);
        $idDepartamento = (int) ($input['idDepartamento'] ?? 0);
        if ($idPersona <= 0) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'ID de persona requerido', 'datos' => []]);
            return;
        }
        $resp = CapHumDAO::getPuestosPorPersona($idPersona, $idDepartamento);
        self::respuestaJSON($resp);
    }

    /// ESTA NO SE MUEVE 
    public function nivelJerarquicoColaborador($persona_id)
    {
        $persona_id = (int) ($persona_id ?? 0);
        if ($persona_id <= 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'mensaje' => 'ID de persona inválido']);
            exit;
        }
        $id_departamento = isset($_GET['id_departamento']) ? (int) $_GET['id_departamento'] : 0;

        // 1️⃣ Obtener organigrama desde la DAO (solo personas con puesto en el departamento si se envía)
        $personas = CapHumDAO::getConsultaPersonasJerarquia($persona_id, $id_departamento);
        
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
        
        $primeraFila = $personas["datos"][0] ?? [];
        $organigramaJson = $primeraFila["organigrama_json"] ?? $primeraFila["ORGANIGRAMA_JSON"] ?? null;

        if ($organigramaJson === null || $organigramaJson === '') {
            header('Content-Type: application/json');
            echo json_encode([
                "success" => false,
                "mensaje" => "organigrama_json está vacío",
                "debug" => $personas["datos"]
            ]);
            exit;
        }
        
        $organigrama = json_decode($organigramaJson, true);
        if (!is_array($organigrama)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'mensaje' => 'Datos del organigrama no válidos']);
            exit;
        }

        // 2️⃣ Construir filas para el OrgChart (siempre al menos la raíz)
        $rows = [];
        $this->idsYaAgregados = [];
        $id_puesto = isset($_GET['id_puesto']) ? (int) $_GET['id_puesto'] : 0;
        $nombrePuestoRaiz = $organigrama["nombre_puesto"] ?? '';
        if ($id_puesto > 0) {
            $nombrePuestoRaiz = CapHumDAO::getNombrePuesto($id_puesto) ?: $nombrePuestoRaiz;
        }

        $idRaiz = (string)($organigrama["id_jefe"] ?? $persona_id);
        $this->idsYaAgregados[$idRaiz] = true;

        // Raíz del organigrama (evitar null para que el gráfico no falle)
        $rows[] = [
            "id"     => $idRaiz,
            "nombre" => $organigrama["nombre_jefe"] ?? '',
            "puesto" => $nombrePuestoRaiz,
            "jefe"   => null
        ];

        // Subordinados de la raíz (recorrerArbol evita duplicados por id)
        if (!empty($organigrama["subordinados"])) {
            foreach ($organigrama["subordinados"] as $sub) {
                $this->recorrerArbol($sub, $rows, $idRaiz);
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
    private $idsYaAgregados = [];

    private function recorrerArbol($nodo, &$rows, $jefeId = null) {
        $id = (string)($nodo["id"] ?? '');
        if ($id === '') return;
        /* Evitar duplicados por id (misma persona no debe aparecer dos veces en el organigrama) */
        if (isset($this->idsYaAgregados[$id])) return;
        $this->idsYaAgregados[$id] = true;

        $rows[] = [
            "id"     => $id,
            "nombre" => $nodo["nombre"] ?? '',
            "puesto" => $nodo["nombre_puesto"] ?? '',
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

        //  Validaciones obligatorias
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

        // 📎 MANEJO DE MÚLTIPLES PDFs (MIME real, nombre seguro, 0755)
        $rutasPDF = [];

        if (!empty($_FILES['archivosPDF']['name'][0])) {

            $directorio = __DIR__ . '/../uploads/bajas/';
            SecureUpload::ensureDir($directorio);

            foreach ($_FILES['archivosPDF']['tmp_name'] as $i => $tmp) {

                if ($_FILES['archivosPDF']['error'][$i] !== UPLOAD_ERR_OK) {
                    continue;
                }

                if (!SecureUpload::validateMime($tmp, SecureUpload::MIME_PDF)) {
                    continue;
                }

                $nombreFinal = SecureUpload::generateSafeFilename('pdf');
                $rutaFinal   = $directorio . $nombreFinal;

                if (move_uploaded_file($tmp, $rutaFinal)) {
                    $rutasPDF[] = $nombreFinal;
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

        //  Llamar al modelo / DAO
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

    /**
     * Registrar reingreso: persona que estaba de baja vuelve a la plantilla (estatus Activo).
     * POST: id_gestor, motivo_reingreso, descripcion_reingreso, archivosPDF[]
     * La fecha de reingreso se asigna en el backend (momento del registro).
     */
    public function registrarReingreso()
    {
        header('Content-Type: application/json; charset=utf-8');

        $idGestor = $_POST['id_gestor'] ?? null;
        $motivo = trim($_POST['motivo_reingreso'] ?? '');
        $descripcion = trim($_POST['descripcion_reingreso'] ?? '');

        if (empty($idGestor)) {
            echo json_encode(['success' => false, 'message' => 'Falta el ID del gestor']);
            return;
        }
        if (empty($motivo)) {
            echo json_encode(['success' => false, 'message' => 'Debes seleccionar un motivo de reingreso']);
            return;
        }
        if (empty($descripcion)) {
            echo json_encode(['success' => false, 'message' => 'Debes escribir la descripción del reingreso']);
            return;
        }

        $rutasPDF = [];
        // Aceptar tanto 'archivosPDF' como 'archivosPDF[]' (según cómo PHP reciba el FormData)
        $files = $_FILES['archivosPDF'] ?? $_FILES['archivosPDF[]'] ?? null;
        if ($files && !empty($files['name'])) {
            $directorio = __DIR__ . '/../uploads/reingresos/';
            SecureUpload::ensureDir($directorio);
            $names = is_array($files['name']) ? $files['name'] : [$files['name']];
            $tmpNames = is_array($files['tmp_name']) ? $files['tmp_name'] : [$files['tmp_name']];
            $errors = is_array($files['error']) ? $files['error'] : [$files['error']];
            foreach ($names as $i => $name) {
                if (empty($name) || ($errors[$i] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) continue;
                $tmp = $tmpNames[$i] ?? '';
                if (!is_uploaded_file($tmp) || !SecureUpload::validateMime($tmp, SecureUpload::MIME_PDF)) continue;
                $nombreFinal = SecureUpload::generateSafeFilename('pdf');
                $rutaFinal = $directorio . $nombreFinal;
                if (move_uploaded_file($tmp, $rutaFinal)) {
                    $rutasPDF[] = $nombreFinal;
                }
            }
        }

        // Fecha de reingreso en hora CDMX (America/Mexico_City)
        $tz = new \DateTimeZone('America/Mexico_City');
        $now = new \DateTime('now', $tz);
        $fechaReingresoSql = $now->format('Y-m-d H:i:s');

        $data = [
            'id_gestor' => $idGestor,
            'motivo_reingreso' => $motivo,
            'descripcion_reingreso' => $descripcion,
            'fecha_reingreso' => $fechaReingresoSql,
            'usuario_reingreso' => (string)($_SESSION['usuario_id'] ?? $_SESSION['usuario'] ?? 'sistema'),
            'archivos' => $rutasPDF
        ];

        $resultado = CapHumDAO::registrarReingresoGestor($data);

        if ($resultado['success']) {
            echo json_encode(['success' => true, 'message' => $resultado['mensaje'] ?? 'Reingreso registrado correctamente']);
        } else {
            $msg = $resultado['mensaje'] ?? 'Error al registrar el reingreso';
            if (!empty($resultado['error'])) {
                $msg .= ' Detalle: ' . $resultado['error'];
            }
            echo json_encode([
                'success' => false,
                'message' => $msg,
                'error' => $resultado['error'] ?? null
            ]);
        }
    }

    /**
     * Elimina por completo un usuario/persona del sistema (y sus datos relacionados).
     */
    /**
     * Consultar dependencias de una persona antes de eliminarla
     * Útil para saber qué tablas tienen referencias a esta persona
     */
    public function consultarDependenciasPersona()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = isset($input['idPersona']) ? (int) $input['idPersona'] : 0;
        if ($id < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'ID de persona inválido.']);
            return;
        }
        // Usar el método de consulta (confirmar = false)
        $resultado = CapHumDAO::eliminarPersonaSeguro($id, false);
        self::respuestaJSON($resultado);
    }

    /**
     * Eliminar persona de forma segura (elimina todas las dependencias primero)
     */
    public function eliminarPersonaCompleto()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = isset($input['idPersona']) ? (int) $input['idPersona'] : 0;
        if ($id < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'ID de persona inválido.']);
            return;
        }
        $resultado = CapHumDAO::eliminarPersonaCompleto($id);
        if ($resultado['success']) {
            self::respuestaJSON(['success' => true, 'mensaje' => $resultado['mensaje'] ?? 'Usuario eliminado del sistema.']);
        } else {
            self::respuestaJSON(['success' => false, 'mensaje' => $resultado['mensaje'] ?? 'Error al eliminar.', 'error' => $resultado['error'] ?? null]);
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
     * GET documentos de un reingreso (por registro_reingreso).
     */
    public function getDocumentosReingreso()
    {
        try {
            $registro_reingreso = $_GET['registro_reingreso'] ?? null;
            if (!$registro_reingreso) {
                self::respuestaJSON(['success' => false, 'mensaje' => 'Registro de reingreso requerido', 'datos' => []]);
                return;
            }
            $resultado = CapHumDAO::getDocumentosReingreso($registro_reingreso);
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
            
            $directorio = __DIR__ . '/../uploads/bajas/';
            SecureUpload::ensureDir($directorio);

            $archivosGuardados = [];

            foreach ($_FILES['archivosPDF']['tmp_name'] as $i => $tmp) {
                if ($_FILES['archivosPDF']['error'][$i] !== UPLOAD_ERR_OK) {
                    continue;
                }
                if (!SecureUpload::validateMime($tmp, SecureUpload::MIME_PDF)) {
                    continue;
                }
                $nombreFinal = SecureUpload::generateSafeFilename('pdf');
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
            $carpeta = ($id_documento === 15) ? 'bajas' : (($id_documento === 16) ? 'reingresos' : 'documentos');
            $directorio = __DIR__ . '/../uploads/' . $carpeta . '/';
            SecureUpload::ensureDir($directorio);

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
                if (!SecureUpload::validateMime($tmp, SecureUpload::MIME_PDF)) {
                    continue;
                }
                $nombreFinal = SecureUpload::generateSafeFilename('pdf');
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
            $carpetas = [__DIR__ . '/../uploads/documentos/', __DIR__ . '/../uploads/bajas/', __DIR__ . '/../uploads/reingresos/'];
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