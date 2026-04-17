<?php

namespace Controllers;

use Core\Controller;
use Core\SecureUpload;
use Core\OcrIdentidad;
use Models\CapHum as CapHumDAO;
use Models\Candidatos as CandidatosDAO;
use Models\Login as LoginDao;
use Models\Notificacion;

class CapHum extends Controller
{
    /** Último error de enviarCorreo para mostrarlo en la respuesta JSON */
    private $enviarCorreoUltimoError = '';

    public function gestion()
    {
        $script = <<<'HTML'
        <script>

            const getUsuarios = (opts) => {
            opts = opts || {};
            http.request({
                endpoint: "/caphum/getUsuarios",
                showLoader: opts.showLoader !== false,
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
                    fetch('/CapHum/getPuestosParaGestor', {
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
                    actualizarEstadoForceLogoutPanel(persona);

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

            function actualizarEstadoForceLogoutPanel(persona) {
                var el = document.getElementById('forceLogoutPerfilEstado');
                var btn = document.getElementById('btnForzarLogoutUsuarioPerfil');
                if (!el || !btn) return;
                if (!persona) {
                    el.textContent = '';
                    el.className = 'small mb-0 mt-3 text-muted';
                    btn.disabled = false;
                    return;
                }
                var mid = typeof window.miUsuarioId !== 'undefined' ? Number(window.miUsuarioId) : 0;
                var pid = persona && persona.id != null ? Number(persona.id) : 0;
                var esPropio = mid > 0 && pid === mid;
                if (esPropio && Number(persona.force_logout) !== 1) {
                    el.textContent = 'Si es su propio usuario, al forzar el cierre deberá volver a iniciar sesión en cuanto el sistema aplique la solicitud.';
                    el.className = 'alert alert-info small mb-0 mt-3';
                    btn.disabled = false;
                }
                if (Number(persona.force_logout) === 1) {
                    el.textContent = 'Cierre de sesión pendiente: en la próxima validación se cerrará la sesión activa.';
                    el.className = 'alert alert-warning small mb-0 mt-3';
                    btn.disabled = true;
                } else if (!esPropio) {
                    el.textContent = '';
                    el.className = 'small mb-0 mt-3 text-muted';
                    btn.disabled = false;
                }
            }

            function forzarCierreSesionUsuarioPerfil() {
                if (!currentPersonaId) return;
                if (typeof Swal === 'undefined') return;
                var mid = typeof window.miUsuarioId !== 'undefined' ? Number(window.miUsuarioId) : 0;
                var esPropio = mid > 0 && Number(currentPersonaId) === mid;
                Swal.fire({
                    title: '¿Forzar cierre de sesión?',
                    html: esPropio
                        ? '<div class="text-start"><p class="mb-2">Se cerrará su <strong>propia sesión</strong> en la próxima validación.</p><p class="mb-0 text-muted small">Deberá iniciar sesión nuevamente.</p></div>'
                        : '<div class="text-start"><p class="mb-2">Se cerrará la sesión activa de este usuario.</p><p class="mb-0 text-muted small">La persona deberá volver a iniciar sesión.</p></div>',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, forzar cierre',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true,
                    focusCancel: true,
                    buttonsStyling: false,
                    customClass: {
                        container: 'swal-sobre-modal-perfil',
                        actions: 'd-flex gap-2 justify-content-center',
                        confirmButton: 'btn btn-danger px-4',
                        cancelButton: 'btn btn-outline-secondary px-4'
                    }
                }).then(function(r) {
                    if (!r.isConfirmed) return;
                    Swal.fire({
                        title: 'Aplicando cierre...',
                        text: 'Espere un momento.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: function() { Swal.showLoading(); },
                        customClass: { container: 'swal-sobre-modal-perfil' }
                    });
                    fetch('/CapHum/forzarCierreSesionUsuario', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify({ idPersona: currentPersonaId })
                    })
                    .then(function(res) { return res.json(); })
                    .then(function(data) {
                        if (!data.success) {
                            Swal.fire({
                                icon: 'error',
                                title: 'No se pudo forzar el cierre',
                                text: data.mensaje || 'No se pudo aplicar la solicitud.',
                                confirmButtonText: 'Entendido',
                                customClass: { container: 'swal-sobre-modal-perfil' }
                            });
                            return;
                        }
                        if (data.cerrar_sesion_inmediata) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Sesión cerrada',
                                text: data.mensaje || 'Será redirigido al inicio de sesión.',
                                timer: 1600,
                                showConfirmButton: false,
                                customClass: { container: 'swal-sobre-modal-perfil' }
                            }).then(function() {
                                window.location.href = (data.redirect && String(data.redirect)) || '/Login';
                            });
                            return;
                        }
                        Swal.fire({
                            icon: 'success',
                            title: 'Cierre solicitado',
                            text: data.mensaje || 'La sesión se cerrará en la próxima validación.',
                            timer: 2400,
                            showConfirmButton: false,
                            customClass: { container: 'swal-sobre-modal-perfil' }
                        });
                        actualizarEstadoForceLogoutPanel({ id: currentPersonaId, force_logout: 1 });
                    })
                    .catch(function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de conexión',
                            text: 'No se pudo contactar al servidor. Intente nuevamente.',
                            confirmButtonText: 'Cerrar',
                            customClass: { container: 'swal-sobre-modal-perfil' }
                        });
                    });
                });
            }

            /* =========================
               ELIMINAR CONVENIOS DE CRÉDITO (admin express)
            ========================= */
            function adminReactivarProductoConvenio() {
                var input = document.getElementById('adminReactivarIdCredito');
                var idCredito = input ? parseInt(input.value, 10) : 0;
                if (!idCredito || idCredito <= 0) {
                    Swal.fire({ icon: 'warning', title: 'ID requerido', text: 'Ingresa un ID de crédito válido.', customClass: { container: 'swal-sobre-modal-perfil' } });
                    return;
                }
                Swal.fire({
                    title: '¿Eliminar convenios del crédito ' + idCredito + '?',
                    html: '<p class="mb-1">Se <strong>eliminarán todos los convenios</strong> y su amortización para este crédito.</p><p class="text-muted small mb-0">El crédito quedará libre para generar un convenio nuevo.</p>',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true,
                    buttonsStyling: false,
                    customClass: {
                        container: 'swal-sobre-modal-perfil',
                        actions: 'd-flex gap-2 justify-content-center',
                        confirmButton: 'btn btn-danger px-4',
                        cancelButton: 'btn btn-outline-secondary px-4'
                    }
                }).then(function(res) {
                    if (!res.isConfirmed) return;
                    Swal.fire({ title: 'Eliminando...', allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false, didOpen: function() { Swal.showLoading(); }, customClass: { container: 'swal-sobre-modal-perfil' } });
                    var fd = new FormData();
                    fd.append('id_credito', idCredito);
                    fetch('/Convenios/eliminarConveniosCredito', { method: 'POST', body: fd })
                        .then(function(r) { return r.json(); })
                        .then(function(data) {
                            if (data.success) {
                                Swal.fire({ icon: 'success', title: '¡Eliminado!', text: data.mensaje || 'Convenios eliminados correctamente.', confirmButtonText: 'Cerrar', customClass: { container: 'swal-sobre-modal-perfil' } });
                                if (input) input.value = '';
                            } else {
                                Swal.fire({ icon: 'error', title: 'No se pudo eliminar', text: data.mensaje || 'Ocurrió un error.', confirmButtonText: 'Entendido', customClass: { container: 'swal-sobre-modal-perfil' } });
                            }
                        })
                        .catch(function() {
                            Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo contactar al servidor.', customClass: { container: 'swal-sobre-modal-perfil' } });
                        });
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
                        Swal.fire({ icon: 'error', title: 'Error', text: data.mensaje || 'No se pudo actualizar', customClass: { container: 'swal-sobre-modal-perfil' } });
                        return;
                    }

                    // ALERTA SEGÚN ACCIÓN - igual que módulos (z-index para verse sobre el modal)
                    Swal.fire({
                        icon: 'success',
                        title: checkbox.checked
                            ? 'Asignación correcta'
                            : 'Asignación eliminada',
                        text: checkbox.checked
                            ? 'El puesto fue asignado correctamente'
                            : 'El puesto fue deseleccionado correctamente',
                        timer: 1600,
                        showConfirmButton: false,
                        customClass: { container: 'swal-sobre-modal-perfil' }
                    });
                })
                .catch(err => {
                    console.error(err);
                    checkbox.checked = !checkbox.checked;
                    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo actualizar el puesto', customClass: { container: 'swal-sobre-modal-perfil' } });
                });
            }

            /* =========================
               MÓDULOS (lista plana duplicada eliminada; ver renderModulos agrupado más abajo)
            ========================= */

            function updateModuloCheckboxLabel(checkbox) {
                if (!checkbox) return;
                const wrap = checkbox.closest('div.form-check');
                const label = wrap ? wrap.querySelector('label.form-check-label') : null;
                if (!label) return;
                label.innerHTML = checkbox.checked
                    ? '<span class="badge bg-success rounded-pill px-3 py-1"><i class="fa fa-check me-1"></i>Asignado</span>'
                    : '<span class="badge bg-secondary rounded-pill px-3 py-1">Asignar</span>';
            }

            function updateGrupoModuloMasterLabel(master, tbody) {
                if (!master) return;
                const wrap = master.closest('.modal-perfil-modulo-master-wrap');
                const lbl = wrap ? wrap.querySelector('.modal-perfil-modulo-master-label') : null;
                if (!lbl) return;
                const tbodyEl = tbody || master.closest('section.modal-perfil-modulo-grupo')?.querySelector('tbody');
                const cbs = tbodyEl ? tbodyEl.querySelectorAll('.modal-perfil-modulo-item-cb') : [];
                const total = cbs.length;
                let n = 0;
                cbs.forEach(c => { if (c.checked) n++; });
                const gNom = (master.getAttribute('data-grupo-nombre') || 'este grupo').trim();
                const tipo = (master.getAttribute('data-grupo-tipo') || 'modulos').toLowerCase();
                const esPuestos = tipo === 'puestos';
                let text = '';
                let title = '';
                let aria = '';
                if (total === 0) {
                    text = '';
                    title = esPuestos
                        ? 'Este departamento no tiene puestos listados'
                        : 'Este grupo no tiene módulos listados';
                    aria = esPuestos
                        ? ('Departamento sin puestos: ' + gNom)
                        : ('Grupo sin módulos: ' + gNom);
                } else if (n === 0) {
                    text = 'Marcar todo';
                    title = esPuestos
                        ? ('Asignar todos los puestos de ' + gNom + ' a esta persona')
                        : ('Asignar todos los módulos de ' + gNom + ' a esta persona');
                    aria = esPuestos
                        ? ('Marcar todos los puestos: ' + gNom)
                        : ('Marcar todo el grupo ' + gNom);
                } else if (n === total) {
                    text = 'Desmarcar todo';
                    title = esPuestos
                        ? ('Quitar la asignación de todos los puestos de ' + gNom)
                        : ('Quitar la asignación de todos los módulos de ' + gNom);
                    aria = esPuestos
                        ? ('Desmarcar todos los puestos: ' + gNom)
                        : ('Desmarcar todo el grupo ' + gNom);
                } else {
                    text = 'Marcar o desmarcar todo';
                    title = 'Hay asignación parcial en ' + gNom + '. Al marcar se completan todos; si ya están todos, se desmarcan.';
                    aria = esPuestos
                        ? ('Departamento ' + gNom + ': asignación parcial de puestos')
                        : ('Grupo ' + gNom + ': asignación parcial. Marcar o desmarcar todo el grupo.');
                }
                lbl.textContent = text;
                lbl.title = title;
                master.title = title;
                master.setAttribute('aria-label', aria);
            }

            function syncGrupoModuloMaster(master, tbody) {
                if (!master || !tbody) return;
                const cbs = tbody.querySelectorAll('.modal-perfil-modulo-item-cb');
                const total = cbs.length;
                if (total === 0) {
                    master.checked = false;
                    master.indeterminate = false;
                    updateGrupoModuloMasterLabel(master, tbody);
                    return;
                }
                let n = 0;
                cbs.forEach(c => { if (c.checked) n++; });
                master.checked = n === total;
                master.indeterminate = n > 0 && n < total;
                updateGrupoModuloMasterLabel(master, tbody);
            }

            function syncGrupoModuloMasterFromChild(checkbox) {
                const section = checkbox.closest('section.modal-perfil-modulo-grupo');
                if (!section) return;
                const master = section.querySelector('.modal-perfil-modulo-master-cb');
                const tbody = section.querySelector('tbody');
                syncGrupoModuloMaster(master, tbody);
            }

            /* Íconos por modulo_id (modulos_web): pestaña Módulos del sistema y Permisos especiales */
            const iconosPermisosEspeciales = {
                21: 'fa fa-file-upload',
                22: 'fa fa-cloud-download',
                23: 'fa fa-calendar-alt',
                24: 'fa fa-file-pdf',
                '24': 'fa fa-file-pdf',
                29: 'fa fa-id-card',
                '29': 'fa fa-id-card',
                30: 'fa fa-balance-scale',
                '30': 'fa fa-balance-scale',
                35: 'fa fa-headset',
                '35': 'fa fa-headset',
                36: 'fa fa-hand-holding-usd',
                '36': 'fa fa-hand-holding-usd',
                37: 'fa fa-sticky-note',
                '37': 'fa fa-sticky-note',
                31: 'fa fa-laptop',
                '31': 'fa fa-laptop',
                32: 'fa fa-file-import',
                '32': 'fa fa-file-import',
                33: 'fa fa-envelope',
                '33': 'fa fa-envelope',
                43: 'fa fa-key',
                '43': 'fa fa-key',
                45: 'fa fa-chart-gantt',
                '45': 'fa fa-chart-gantt',
                46: 'fa fa-handshake',
                '46': 'fa fa-handshake',
                49: 'fa fa-calendar-week',
                '49': 'fa fa-calendar-week',
                50: 'fa fa-chart-line',
                '50': 'fa fa-chart-line',
                51: 'fa-solid fa-file-circle-check',
                '51': 'fa-solid fa-file-circle-check',
                52: 'fa-solid fa-handshake',
                '52': 'fa-solid fa-handshake',
                53: 'fa-solid fa-clipboard-check',
                '53': 'fa-solid fa-clipboard-check',
                54: 'fa-solid fa-list-check',
                '54': 'fa-solid fa-list-check',
                55: 'fa-solid fa-clock-rotate-left',
                '55': 'fa-solid fa-clock-rotate-left',
            };

            /** Mapa base de íconos (pestaña Módulos del sistema y filas agrupadas de permisos especiales). */
            const iconosModulosSistemaPerfil = {
                1: 'fa fa-file-invoice-dollar', 2: 'fa fa-folder-open', 3: 'fa fa-screwdriver-wrench',
                4: 'fa fa-users', 5: 'fa fa-sitemap', 6: 'fa fa-chart-bar', 7: 'fa fa-file-alt',
                10: 'fa fa-cog', 13: 'fa fa-user-minus', 14: 'fa fa-file-alt', 15: 'fa fa-hand-holding-dollar',
                16: 'fa fa-cog', 17: 'fa fa-cog',
                26: 'fa fa-list-check',
                18: 'fa-solid fa-ticket',
                19: 'fa fa-table-cells',
                25: 'fa fa-table-cells',
                27: 'fa fa-table-cells',
                20: 'fa fa-building-columns',
                21: 'fa fa-file-upload',
                34: 'fa fa-file-alt',
                29: 'fa fa-id-card',
                30: 'fa fa-balance-scale',
                35: 'fa fa-headset',
                36: 'fa fa-hand-holding-usd',
                37: 'fa fa-sticky-note',
                31: 'fa fa-laptop',
                32: 'fa fa-file-import',
                41: 'fa fa-globe', 42: 'fa fa-users', 44: 'fa fa-graduation-cap',
                43: 'fa fa-key',
                45: 'fa fa-chart-gantt',
                46: 'fa fa-handshake',
                47: 'fa fa-chart-pie',
                49: 'fa fa-calendar-week',
                50: 'fa fa-chart-line',
                51: 'fa-solid fa-file-circle-check',
                48: 'fa fa-archive'
            };

            /* =========================
               RENDER PUESTOS (rejilla 2 columnas; tarjeta por departamento con collapse / acordeón independiente)
            ========================= */
            function buildFilaPuestoSistema(puesto) {
                const tr = document.createElement('tr');
                tr.className = 'modal-perfil-modulo-fila';
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
                tdName.style.padding = '0.875rem 0.875rem 0.875rem 1.75rem';
                tdName.style.verticalAlign = 'middle';

                const nombreDiv = document.createElement('div');
                nombreDiv.style.display = 'flex';
                nombreDiv.style.alignItems = 'center';
                nombreDiv.style.gap = '0.75rem';

                const iconoBox = document.createElement('div');
                iconoBox.className = 'modulo-icon-box';
                iconoBox.style.width = '40px';
                iconoBox.style.height = '40px';
                iconoBox.style.borderRadius = '10px';
                iconoBox.style.display = 'flex';
                iconoBox.style.alignItems = 'center';
                iconoBox.style.justifyContent = 'center';
                iconoBox.style.flexShrink = '0';

                const nivel = puesto.nivel ?? 0;
                const iconoInner = document.createElement('i');
                iconoInner.style.fontSize = '1rem';
                /* Misma línea visual que Módulos: caja azul corporativa (#1A52A8), matices por nivel */
                if (nivel >= 5) {
                    iconoBox.style.background = 'rgba(26, 82, 168, 0.18)';
                    iconoBox.style.border = '1px solid rgba(26, 82, 168, 0.42)';
                    iconoInner.className = 'fa fa-crown';
                    iconoInner.style.color = '#0f3d86';
                    iconoBox.title = 'Nivel ejecutivo';
                } else if (nivel >= 3) {
                    iconoBox.style.background = 'rgba(26, 82, 168, 0.14)';
                    iconoBox.style.border = '1px solid rgba(26, 82, 168, 0.32)';
                    iconoInner.className = 'fa fa-star';
                    iconoInner.style.color = '#1A52A8';
                    iconoBox.title = 'Nivel gerencial';
                } else {
                    iconoBox.style.background = 'rgba(26, 82, 168, 0.12)';
                    iconoBox.style.border = '1px solid rgba(26, 82, 168, 0.25)';
                    iconoInner.className = 'fa fa-briefcase';
                    iconoInner.style.color = '#1A52A8';
                    iconoBox.title = 'Nivel operativo';
                }
                iconoBox.appendChild(iconoInner);

                const nombre = document.createElement('span');
                nombre.innerText = puesto.nombre_puesto || puesto.puesto_nombre || 'Puesto sin nombre';
                nombre.style.fontWeight = '600';
                nombre.style.color = '#2c3e50';
                nombre.style.fontSize = '0.95rem';

                nombreDiv.appendChild(iconoBox);
                nombreDiv.appendChild(nombre);

                const desc = document.createElement('small');
                desc.className = 'text-muted d-block mt-1';
                desc.style.fontSize = '0.75rem';
                desc.innerText = puesto.descripcion ?? '';

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
                checkbox.className = 'form-check-input modal-perfil-modulo-item-cb';
                checkbox.checked = Number(puesto.asignado_flag) === 1;
                checkbox.value = puesto.id_puesto || puesto.puesto_id || '';
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
                    updateModuloCheckboxLabel(this);
                    onPuestoChange(this);
                });

                divCheck.append(checkbox, label);
                tdCheck.appendChild(divCheck);

                tr.append(tdName, tdCheck);
                return tr;
            }

            function renderPuestos(puestos) {
                const container = document.getElementById('modal-edit-perfil-puestos-form') || document.getElementById('puestos-form');
                if (!container) return;
                container.innerHTML = '';
                container.classList.add('modal-perfil-modulos-agrupados');

                if (!puestos || puestos.length === 0) {
                    container.innerHTML = '<div class="text-muted small text-center py-4">No hay puestos disponibles</div>';
                    return;
                }

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

                Object.keys(puestosPorDepto).forEach(deptoId => {
                    puestosPorDepto[deptoId].puestos.sort((a, b) => {
                        const nivelA = a.nivel ?? 0;
                        const nivelB = b.nivel ?? 0;
                        return nivelB - nivelA;
                    });
                });

                const deptosOrdenados = Object.keys(puestosPorDepto).sort((a, b) => {
                    const nombreA = puestosPorDepto[a].nombre.toLowerCase();
                    const nombreB = puestosPorDepto[b].nombre.toLowerCase();
                    return nombreA.localeCompare(nombreB);
                });

                deptosOrdenados.forEach((deptoId, grupoIdx) => {
                    const deptoData = puestosPorDepto[deptoId];
                    const puestosDepto = deptoData.puestos;
                    const deptoNombre = deptoData.nombre;
                    const collapseId = 'puesto-card-collapse-' + grupoIdx;

                    const section = document.createElement('section');
                    section.className = 'modal-perfil-modulo-grupo modal-perfil-puesto-grupo card mb-0 shadow-sm';
                    section.style.border = '2px solid #000';
                    section.style.borderRadius = '0.5rem';
                    section.style.overflow = 'hidden';

                    const header = document.createElement('div');
                    header.className = 'modal-perfil-modulo-grupo-header modal-perfil-puesto-card-toggle px-3 py-2 d-flex align-items-center flex-wrap gap-2 fw-semibold';
                    header.style.background = 'rgba(26, 82, 168, 0.08)';
                    header.style.borderBottom = '2px solid #000';
                    header.style.cursor = 'pointer';
                    header.setAttribute('role', 'button');
                    header.setAttribute('tabindex', '0');
                    header.setAttribute('aria-expanded', 'false');
                    header.setAttribute('aria-controls', collapseId);
                    header.setAttribute('data-bs-toggle', 'collapse');
                    header.setAttribute('data-bs-target', '#' + collapseId);

                    const chevron = document.createElement('i');
                    chevron.className = 'fa fa-chevron-down modal-perfil-puesto-chevron text-primary';
                    chevron.style.flexShrink = '0';
                    chevron.style.fontSize = '0.75rem';
                    chevron.style.transition = 'transform 0.2s ease';
                    chevron.style.transform = 'rotate(-90deg)';
                    chevron.setAttribute('aria-hidden', 'true');

                    const masterWrap = document.createElement('div');
                    masterWrap.className = 'd-flex align-items-center gap-2 ms-auto flex-shrink-0 modal-perfil-modulo-master-wrap';
                    const stopToggle = (e) => { e.stopPropagation(); };
                    masterWrap.addEventListener('click', stopToggle);
                    masterWrap.addEventListener('mousedown', stopToggle);

                    const masterCb = document.createElement('input');
                    masterCb.type = 'checkbox';
                    masterCb.className = 'form-check-input modal-perfil-modulo-master-cb';
                    masterCb.id = 'modal-perfil-puesto-master-' + grupoIdx;
                    masterCb.setAttribute('data-grupo-nombre', deptoNombre);
                    masterCb.setAttribute('data-grupo-tipo', 'puestos');
                    masterCb.style.cursor = 'pointer';
                    const masterLbl = document.createElement('label');
                    masterLbl.className = 'form-check-label small text-secondary mb-0 modal-perfil-modulo-master-label';
                    masterLbl.setAttribute('for', masterCb.id);
                    masterLbl.style.cursor = 'pointer';
                    masterWrap.appendChild(masterLbl);
                    masterWrap.appendChild(masterCb);

                    const hi = document.createElement('i');
                    hi.className = 'fa fa-building me-2 text-primary';
                    hi.style.flexShrink = '0';
                    const ht = document.createElement('span');
                    ht.className = 'flex-grow-1 min-w-0';
                    ht.style.color = '#1e293b';
                    ht.textContent = deptoNombre + ' (' + puestosDepto.length + ')';

                    header.appendChild(chevron);
                    header.appendChild(hi);
                    header.appendChild(ht);
                    header.appendChild(masterWrap);

                    header.addEventListener('keydown', function(ev) {
                        if (ev.key === 'Enter' || ev.key === ' ') {
                            ev.preventDefault();
                            const el = document.getElementById(collapseId);
                            if (!el) return;
                            if (typeof bootstrap !== 'undefined' && bootstrap.Collapse) {
                                const inst = bootstrap.Collapse.getOrCreateInstance(el);
                                inst.toggle();
                            } else {
                                el.classList.toggle('show');
                                syncChevron(el.classList.contains('show'));
                                actualizarBotonExpandirPuestos();
                            }
                        }
                    });

                    const table = document.createElement('table');
                    table.className = 'table table-hover mb-0';
                    table.style.fontSize = '0.9rem';

                    const tbody = document.createElement('tbody');
                    puestosDepto.forEach(p => tbody.appendChild(buildFilaPuestoSistema(p)));
                    table.appendChild(tbody);

                    const collapse = document.createElement('div');
                    collapse.id = collapseId;
                    collapse.className = 'collapse modal-perfil-puesto-card-collapse';

                    const syncChevron = (open) => {
                        header.setAttribute('aria-expanded', open ? 'true' : 'false');
                        chevron.style.transform = open ? 'rotate(0deg)' : 'rotate(-90deg)';
                    };
                    collapse.addEventListener('shown.bs.collapse', () => {
                        syncChevron(true);
                        actualizarBotonExpandirPuestos();
                    });
                    collapse.addEventListener('hidden.bs.collapse', () => {
                        syncChevron(false);
                        actualizarBotonExpandirPuestos();
                    });

                    masterCb.addEventListener('change', function() {
                        const want = this.checked;
                        this.indeterminate = false;
                        const cbs = [...tbody.querySelectorAll('.modal-perfil-modulo-item-cb')];
                        const toToggle = cbs.filter(cb => cb.checked !== want);
                        if (toToggle.length === 0) {
                            syncGrupoModuloMaster(masterCb, tbody);
                            return;
                        }
                        masterCb.disabled = true;
                        let chain = Promise.resolve(true);
                        toToggle.forEach(cb => {
                            chain = chain.then((ok) => {
                                if (!ok) return false;
                                cb.checked = want;
                                updateModuloCheckboxLabel(cb);
                                return onPuestoChange(cb, { silent: true });
                            });
                        });
                        chain
                            .then((ok) => {
                                if (ok) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Departamento actualizado',
                                        text: 'Los puestos del departamento se guardaron correctamente.',
                                        timer: 1600,
                                        showConfirmButton: false,
                                        customClass: { container: 'swal-sobre-modal-perfil' }
                                    });
                                }
                                if (typeof getUsuarios === 'function') getUsuarios({ showLoader: false });
                            })
                            .finally(() => {
                                masterCb.disabled = false;
                                syncGrupoModuloMaster(masterCb, tbody);
                            });
                    });

                    syncGrupoModuloMaster(masterCb, tbody);

                    collapse.appendChild(table);
                    section.appendChild(header);
                    section.appendChild(collapse);
                    container.appendChild(section);
                });

                actualizarBotonExpandirPuestos();
            }

            function actualizarBotonExpandirPermisosEspeciales() {
                const form = document.getElementById('modal-edit-perfil-permisos-especiales-form') || document.getElementById('permisos-especiales-form');
                const btn = document.getElementById('btn-permisos-esp-expandir-todos');
                if (!btn) return;
                if (!form) {
                    btn.innerHTML = '<i class="fa fa-expand me-1"></i>Expandir todos';
                    return;
                }
                const cols = form.querySelectorAll('.modal-perfil-modulo-grupo-collapse');
                if (cols.length === 0) {
                    btn.innerHTML = '<i class="fa fa-expand me-1"></i>Expandir todos';
                    return;
                }
                const allExpanded = Array.from(cols).every(c => c.classList.contains('show'));
                btn.innerHTML = allExpanded
                    ? '<i class="fa fa-compress me-1"></i>Contraer todos'
                    : '<i class="fa fa-expand me-1"></i>Expandir todos';
            }

            function expandirTodosPermisosEspeciales() {
                const form = document.getElementById('modal-edit-perfil-permisos-especiales-form') || document.getElementById('permisos-especiales-form');
                if (!form) return;
                const collapses = form.querySelectorAll('.modal-perfil-modulo-grupo-collapse');
                if (collapses.length === 0) return;

                const allCollapsed = Array.from(collapses).every(c => !c.classList.contains('show'));
                const doExpand = allCollapsed;

                collapses.forEach(collapseEl => {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Collapse) {
                        const inst = bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle: false });
                        if (doExpand) {
                            inst.show();
                        } else {
                            inst.hide();
                        }
                    } else {
                        collapseEl.classList.toggle('show', doExpand);
                        const cid = collapseEl.id;
                        const hdr = form.querySelector('[aria-controls="' + cid + '"]');
                        if (hdr) {
                            hdr.setAttribute('aria-expanded', doExpand ? 'true' : 'false');
                            const ch = hdr.querySelector('.modal-perfil-modulo-grupo-chevron');
                            if (ch) {
                                ch.style.transform = doExpand ? 'rotate(0deg)' : 'rotate(-90deg)';
                            }
                        }
                    }
                });

                actualizarBotonExpandirPermisosEspeciales();
            }

            function actualizarBotonExpandirPuestos() {
                const form = document.getElementById('modal-edit-perfil-puestos-form') || document.getElementById('puestos-form');
                const btn = document.getElementById('btn-puestos-expandir-todos');
                if (!btn) return;
                if (!form) {
                    btn.innerHTML = '<i class="fa fa-expand me-1"></i>Expandir todos';
                    return;
                }
                const cols = form.querySelectorAll('.modal-perfil-puesto-card-collapse');
                if (cols.length === 0) {
                    btn.innerHTML = '<i class="fa fa-expand me-1"></i>Expandir todos';
                    return;
                }
                const allExpanded = Array.from(cols).every(c => c.classList.contains('show'));
                btn.innerHTML = allExpanded
                    ? '<i class="fa fa-compress me-1"></i>Contraer todos'
                    : '<i class="fa fa-expand me-1"></i>Expandir todos';
            }

            function onPuestoChange(checkbox, opts) {
                opts = opts || {};
                const silent = !!opts.silent;
                if (!checkbox || !currentPersonaId) {
                    return Promise.resolve(false);
                }

                const payload = {
                    idPersona: currentPersonaId,
                    idPuesto: checkbox.value,
                    asignado: checkbox.checked ? 1 : 0
                };

                return fetch('/caphum/actualizarPuestoPerfil', {
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
                        checkbox.checked = !checkbox.checked;
                        updateModuloCheckboxLabel(checkbox);
                        syncGrupoModuloMasterFromChild(checkbox);
                        Swal.fire({ icon: 'error', title: 'Error', text: data.mensaje || 'No se pudo actualizar', customClass: { container: 'swal-sobre-modal-perfil' } });
                        return false;
                    }

                    if (!silent) {
                        Swal.fire({
                            icon: 'success',
                            title: checkbox.checked
                                ? 'Asignación correcta'
                                : 'Asignación eliminada',
                            text: checkbox.checked
                                ? 'El puesto fue asignado correctamente'
                                : 'El puesto fue deseleccionado correctamente',
                            timer: 1600,
                            showConfirmButton: false,
                            customClass: { container: 'swal-sobre-modal-perfil' }
                        });
                        if (typeof getUsuarios === 'function') getUsuarios({ showLoader: false });
                    }
                    syncGrupoModuloMasterFromChild(checkbox);
                    return true;
                })
                .catch(err => {
                    console.error(err);
                    checkbox.checked = !checkbox.checked;
                    updateModuloCheckboxLabel(checkbox);
                    syncGrupoModuloMasterFromChild(checkbox);
                    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo actualizar el puesto', customClass: { container: 'swal-sobre-modal-perfil' } });
                    return false;
                });
            }

            function expandirTodosPuestos() {
                const form = document.getElementById('modal-edit-perfil-puestos-form') || document.getElementById('puestos-form');
                if (!form) return;
                const collapses = form.querySelectorAll('.modal-perfil-puesto-card-collapse');
                if (collapses.length === 0) return;

                const allCollapsed = Array.from(collapses).every(c => !c.classList.contains('show'));
                const doExpand = allCollapsed;

                collapses.forEach(collapseEl => {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Collapse) {
                        const inst = bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle: false });
                        if (doExpand) {
                            inst.show();
                        } else {
                            inst.hide();
                        }
                    } else {
                        collapseEl.classList.toggle('show', doExpand);
                        const cid = collapseEl.id;
                        const hdr = form.querySelector('[aria-controls="' + cid + '"]');
                        if (hdr) {
                            hdr.setAttribute('aria-expanded', doExpand ? 'true' : 'false');
                            const ch = hdr.querySelector('.modal-perfil-puesto-chevron');
                            if (ch) {
                                ch.style.transform = doExpand ? 'rotate(0deg)' : 'rotate(-90deg)';
                            }
                        }
                    }
                });

                actualizarBotonExpandirPuestos();
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
               RENDER MÓDULOS (agrupados como menú lateral; datos menu_* vienen del backend)
            ========================= */
            function normalizarTextoPermisoModal(s) {
                return String(s || '')
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .toLowerCase()
                    .trim();
            }

            function esGrupoCierreDeCreditoModal(grupoNombre) {
                const n = normalizarTextoPermisoModal(grupoNombre);
                return n.includes('cierre') && n.includes('credito');
            }

            /** Las 4 pestañas de Cierre de crédito en permisos especiales (Convenios, Validación, En proceso, Historial). */
            function esFilaCuatroPestanasCierreCredito(displayLabel) {
                const lab = normalizarTextoPermisoModal(displayLabel);
                if (!lab) return false;
                if (lab === 'convenios' || lab === 'convenio') return true;
                if (lab.includes('validacion') && lab.includes('cierre')) return true;
                if (lab === 'en proceso') return true;
                if (lab === 'historial') return true;
                return false;
            }

            /** Icono dentro del cuadro (misma UI que otras tarjetas de permisos especiales). */
            function iconoCuadroCierreCreditoPermisoEsp(displayLabel) {
                const lab = normalizarTextoPermisoModal(displayLabel);
                if (lab === 'convenios' || lab === 'convenio') return 'fa-solid fa-handshake';
                if (lab.includes('validacion') && lab.includes('cierre')) return 'fa-solid fa-clipboard-check';
                if (lab === 'en proceso') return 'fa-solid fa-list-check';
                if (lab === 'historial') return 'fa-solid fa-clock-rotate-left';
                return null;
            }

            function buildFilaModuloSistema(mod, displayLabel, iconosModulos, ctx) {
                ctx = ctx || {};
                const grupoNombre = ctx.grupoNombre != null ? String(ctx.grupoNombre) : '';
                const iconosCierrePorEtiqueta = !!ctx.iconosCierreCreditoPorEtiquetaPermisosEsp;

                const tr = document.createElement('tr');
                tr.className = 'modal-perfil-modulo-fila';
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
                tdName.style.padding = '0.875rem 0.875rem 0.875rem 1.75rem';
                tdName.style.verticalAlign = 'middle';

                const nombreDiv = document.createElement('div');
                nombreDiv.style.display = 'flex';
                nombreDiv.style.alignItems = 'center';
                nombreDiv.style.gap = '0.75rem';

                const modId = mod.modulo_id != null ? mod.modulo_id : mod.id;
                let iconClass = iconosModulos[modId] || iconosModulos[Number(modId)] || 'fa fa-cube';
                if (iconosCierrePorEtiqueta
                    && esGrupoCierreDeCreditoModal(grupoNombre)
                    && esFilaCuatroPestanasCierreCredito(displayLabel || mod.modulo_nombre)) {
                    const icEsp = iconoCuadroCierreCreditoPermisoEsp(displayLabel || mod.modulo_nombre);
                    if (icEsp) iconClass = icEsp;
                }
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
                iconoInner.title = displayLabel || mod.modulo_nombre || '';
                iconoModulo.appendChild(iconoInner);

                const nombre = document.createElement('span');
                nombre.innerText = displayLabel || mod.modulo_nombre || 'Módulo';
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
                checkbox.className = 'form-check-input modal-perfil-modulo-item-cb';
                checkbox.checked = Number(mod.asignado_flag) === 1;
                checkbox.value = mod.modulo_id;
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
                    updateModuloCheckboxLabel(this);
                    onModuloChange(this);
                });

                divCheck.append(checkbox, label);
                tdCheck.appendChild(divCheck);

                tr.append(tdName, tdCheck);
                return tr;
            }

            /**
             * Tarjetas agrupadas por menu_grupo (misma UI en «Módulos del sistema» y «Permisos especiales»).
             * @param {{ masterIdPrefix: string, iconosMap: Object, emptyHtml: string, collapseGrupos?: boolean, collapseIdPrefix?: string, iconosCierreCreditoPorEtiquetaPermisosEsp?: boolean }} opts
             */
            function renderAgrupadoPorMenuGrupo(perfiles, container, opts) {
                const masterIdPrefix = opts.masterIdPrefix;
                const iconosMap = opts.iconosMap;
                const emptyHtml = opts.emptyHtml;
                const useCollapseGrupos = !!opts.collapseGrupos;
                const collapseIdPrefix = opts.collapseIdPrefix || 'modal-perfil-grupo';
                const iconosCierreCreditoPorEtiquetaPermisosEsp = !!opts.iconosCierreCreditoPorEtiquetaPermisosEsp;
                if (!container) return;
                container.innerHTML = '';
                container.classList.add('modal-perfil-modulos-agrupados');

                if (!perfiles || perfiles.length === 0) {
                    container.innerHTML = emptyHtml;
                    return;
                }

                const grupos = new Map();
                const nombresGrupo = [];
                perfiles.forEach(m => {
                    const g = (m.menu_grupo != null && String(m.menu_grupo).trim() !== '') ? String(m.menu_grupo).trim() : 'Otros';
                    if (!grupos.has(g)) {
                        grupos.set(g, {
                            icono: m.menu_grupo_icono || 'fa-solid fa-folder',
                            orden: Number(m.menu_grupo_orden),
                            items: []
                        });
                        if (!Number.isFinite(grupos.get(g).orden)) grupos.get(g).orden = 999;
                        nombresGrupo.push(g);
                    }
                    grupos.get(g).items.push(m);
                });

                nombresGrupo.sort((a, b) => {
                    const oa = grupos.get(a).orden;
                    const ob = grupos.get(b).orden;
                    if (oa !== ob) return oa - ob;
                    return a.localeCompare(b, 'es');
                });

                nombresGrupo.forEach((gName, grupoIdx) => {
                    const g = grupos.get(gName);
                    g.items.sort((a, b) => {
                        const ia = Number(a.menu_item_orden);
                        const ib = Number(b.menu_item_orden);
                        if (Number.isFinite(ia) && Number.isFinite(ib) && ia !== ib) return ia - ib;
                        return (Number(a.modulo_id) || 0) - (Number(b.modulo_id) || 0);
                    });

                    const section = document.createElement('section');
                    section.className = 'modal-perfil-modulo-grupo card mb-0 shadow-sm';
                    section.style.border = '2px solid #000';
                    section.style.borderRadius = '0.5rem';
                    section.style.overflow = 'hidden';

                    const header = document.createElement('div');
                    header.className = 'modal-perfil-modulo-grupo-header px-3 py-2 d-flex align-items-center flex-wrap gap-2 fw-semibold';
                    header.style.background = 'rgba(26, 82, 168, 0.08)';
                    header.style.borderBottom = '2px solid #000';

                    const masterWrap = document.createElement('div');
                    masterWrap.className = 'd-flex align-items-center gap-2 ms-auto flex-shrink-0 modal-perfil-modulo-master-wrap';
                    const masterCb = document.createElement('input');
                    masterCb.type = 'checkbox';
                    masterCb.className = 'form-check-input modal-perfil-modulo-master-cb';
                    masterCb.id = masterIdPrefix + grupoIdx;
                    masterCb.setAttribute('data-grupo-nombre', gName);
                    masterCb.setAttribute('data-grupo-tipo', 'modulos');
                    masterCb.style.cursor = 'pointer';
                    const masterLbl = document.createElement('label');
                    masterLbl.className = 'form-check-label small text-secondary mb-0 modal-perfil-modulo-master-label';
                    masterLbl.setAttribute('for', masterCb.id);
                    masterLbl.style.cursor = 'pointer';
                    masterWrap.appendChild(masterLbl);
                    masterWrap.appendChild(masterCb);

                    const hi = document.createElement('i');
                    hi.className = (g.icono || 'fa-solid fa-folder') + ' me-2 text-primary';
                    hi.style.flexShrink = '0';
                    const ht = document.createElement('span');
                    ht.className = 'flex-grow-1 min-w-0';
                    ht.style.color = '#1e293b';
                    ht.textContent = gName + ' (' + g.items.length + ')';

                    let collapseId = '';
                    if (useCollapseGrupos) {
                        collapseId = collapseIdPrefix + '-collapse-' + grupoIdx;
                        header.classList.add('modal-perfil-modulo-grupo-toggle');
                        header.style.cursor = 'pointer';
                        header.setAttribute('role', 'button');
                        header.setAttribute('tabindex', '0');
                        header.setAttribute('aria-expanded', 'true');
                        header.setAttribute('aria-controls', collapseId);
                        header.setAttribute('data-bs-toggle', 'collapse');
                        header.setAttribute('data-bs-target', '#' + collapseId);

                        const stopToggle = (e) => { e.stopPropagation(); };
                        masterWrap.addEventListener('click', stopToggle);
                        masterWrap.addEventListener('mousedown', stopToggle);

                        const chevron = document.createElement('i');
                        chevron.className = 'fa fa-chevron-down modal-perfil-modulo-grupo-chevron text-primary';
                        chevron.style.flexShrink = '0';
                        chevron.style.fontSize = '0.75rem';
                        chevron.style.transition = 'transform 0.2s ease';
                        chevron.style.transform = 'rotate(0deg)';
                        chevron.setAttribute('aria-hidden', 'true');

                        header.appendChild(chevron);
                        header.appendChild(hi);
                        header.appendChild(ht);
                        header.appendChild(masterWrap);

                        header.addEventListener('keydown', function(ev) {
                            if (ev.key === 'Enter' || ev.key === ' ') {
                                ev.preventDefault();
                                const el = document.getElementById(collapseId);
                                if (!el) return;
                                if (typeof bootstrap !== 'undefined' && bootstrap.Collapse) {
                                    bootstrap.Collapse.getOrCreateInstance(el).toggle();
                                } else {
                                    el.classList.toggle('show');
                                    const open = el.classList.contains('show');
                                    header.setAttribute('aria-expanded', open ? 'true' : 'false');
                                    const ch = header.querySelector('.modal-perfil-modulo-grupo-chevron');
                                    if (ch) ch.style.transform = open ? 'rotate(0deg)' : 'rotate(-90deg)';
                                    if (typeof actualizarBotonExpandirPermisosEspeciales === 'function') {
                                        actualizarBotonExpandirPermisosEspeciales();
                                    }
                                }
                            }
                        });
                    } else {
                        header.appendChild(hi);
                        header.appendChild(ht);
                        header.appendChild(masterWrap);
                    }

                    const table = document.createElement('table');
                    table.className = 'table table-hover mb-0';
                    table.style.fontSize = '0.9rem';

                    const tbody = document.createElement('tbody');
                    g.items.forEach(mod => {
                        const lbl = (mod.menu_item_label != null && String(mod.menu_item_label).trim() !== '')
                            ? String(mod.menu_item_label).trim()
                            : (mod.modulo_nombre || 'Módulo');
                        tbody.appendChild(buildFilaModuloSistema(mod, lbl, iconosMap, {
                            grupoNombre: gName,
                            iconosCierreCreditoPorEtiquetaPermisosEsp: iconosCierreCreditoPorEtiquetaPermisosEsp,
                        }));
                    });

                    table.appendChild(tbody);

                    masterCb.addEventListener('change', function() {
                        const want = this.checked;
                        this.indeterminate = false;
                        const cbs = [...tbody.querySelectorAll('.modal-perfil-modulo-item-cb')];
                        const toToggle = cbs.filter(cb => cb.checked !== want);
                        if (toToggle.length === 0) {
                            syncGrupoModuloMaster(masterCb, tbody);
                            return;
                        }
                        masterCb.disabled = true;
                        let chain = Promise.resolve(true);
                        toToggle.forEach(cb => {
                            chain = chain.then((ok) => {
                                if (!ok) return false;
                                cb.checked = want;
                                updateModuloCheckboxLabel(cb);
                                return onModuloChange(cb, { silent: true });
                            });
                        });
                        chain
                            .then((ok) => {
                                if (ok) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Grupo actualizado',
                                        text: 'Los permisos del grupo se guardaron correctamente.',
                                        timer: 1600,
                                        showConfirmButton: false,
                                        customClass: { container: 'swal-sobre-modal-perfil' }
                                    });
                                }
                                if (typeof getUsuarios === 'function') getUsuarios({ showLoader: false });
                            })
                            .finally(() => {
                                masterCb.disabled = false;
                                syncGrupoModuloMaster(masterCb, tbody);
                            });
                    });

                    syncGrupoModuloMaster(masterCb, tbody);

                    if (useCollapseGrupos) {
                        const collapse = document.createElement('div');
                        collapse.id = collapseId;
                        collapse.className = 'collapse show modal-perfil-modulo-grupo-collapse';

                        const syncChevron = (open) => {
                            header.setAttribute('aria-expanded', open ? 'true' : 'false');
                            const ch = header.querySelector('.modal-perfil-modulo-grupo-chevron');
                            if (ch) ch.style.transform = open ? 'rotate(0deg)' : 'rotate(-90deg)';
                        };
                        collapse.addEventListener('shown.bs.collapse', () => {
                            syncChevron(true);
                            if (typeof actualizarBotonExpandirPermisosEspeciales === 'function') {
                                actualizarBotonExpandirPermisosEspeciales();
                            }
                        });
                        collapse.addEventListener('hidden.bs.collapse', () => {
                            syncChevron(false);
                            if (typeof actualizarBotonExpandirPermisosEspeciales === 'function') {
                                actualizarBotonExpandirPermisosEspeciales();
                            }
                        });

                        syncChevron(true);

                        collapse.appendChild(table);
                        section.appendChild(header);
                        section.appendChild(collapse);
                    } else {
                        section.appendChild(header);
                        section.appendChild(table);
                    }

                    container.appendChild(section);
                });

                if (useCollapseGrupos && typeof actualizarBotonExpandirPermisosEspeciales === 'function') {
                    actualizarBotonExpandirPermisosEspeciales();
                }
            }

            function renderPermisosEspeciales(perfiles) {
                const container = document.getElementById('modal-edit-perfil-permisos-especiales-form') || document.getElementById('permisos-especiales-form');
                const iconosMap = Object.assign({}, iconosModulosSistemaPerfil, iconosPermisosEspeciales);
                renderAgrupadoPorMenuGrupo(perfiles, container, {
                    masterIdPrefix: 'modal-perfil-perm-esp-master-',
                    iconosMap: iconosMap,
                    emptyHtml: '<p class="text-muted small mb-0">No hay permisos especiales configurados.</p>',
                    collapseGrupos: true,
                    collapseIdPrefix: 'modal-perfil-perm-esp',
                    iconosCierreCreditoPorEtiquetaPermisosEsp: true,
                });
            }

            function renderModulos(perfiles) {
                const container = document.getElementById('modal-edit-perfil-modulos-form') || document.getElementById('modulos-form');
                renderAgrupadoPorMenuGrupo(perfiles, container, {
                    masterIdPrefix: 'modal-perfil-modulo-master-',
                    iconosMap: iconosModulosSistemaPerfil,
                    emptyHtml: '<div class="text-muted small text-center py-4">No hay módulos disponibles</div>',
                });
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

                                if (typeof getUsuarios === 'function') getUsuarios();
                                if (typeof getBajas === 'function') getBajas();
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




            function onModuloChange(checkbox, opts) {
                opts = opts || {};
                const silent = !!opts.silent;
                if (!checkbox || currentPersonaId == null) {
                    return Promise.resolve(false);
                }

                const payload = {
                    idPersona: currentPersonaId,
                    modulo_id: checkbox.value,
                    asignado: checkbox.checked ? 1 : 0
                };

                return fetch('/CapHum/PerfilCheckBoxEstado', {
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
                        checkbox.checked = !checkbox.checked;
                        updateModuloCheckboxLabel(checkbox);
                        syncGrupoModuloMasterFromChild(checkbox);
                        Swal.fire({ icon: 'error', title: 'Error', text: data.mensaje || 'No se pudo actualizar', customClass: { container: 'swal-sobre-modal-perfil' } });
                        return false;
                    }

                    if (!silent) {
                        Swal.fire({
                            icon: 'success',
                            title: checkbox.checked
                                ? 'Asignación correcta'
                                : 'Asignación eliminada',
                            text: checkbox.checked
                                ? 'El módulo fue asignado correctamente'
                                : 'El módulo fue desasignado correctamente',
                            timer: 1600,
                            showConfirmButton: false,
                            customClass: { container: 'swal-sobre-modal-perfil' }
                        });
                        if (typeof getUsuarios === 'function') getUsuarios({ showLoader: false });
                    }
                    syncGrupoModuloMasterFromChild(checkbox);
                    return true;
                })
                .catch(err => {
                    console.error(err);
                    checkbox.checked = !checkbox.checked;
                    updateModuloCheckboxLabel(checkbox);
                    syncGrupoModuloMasterFromChild(checkbox);
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Error de comunicación con el servidor', customClass: { container: 'swal-sobre-modal-perfil' } });
                    return false;
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

                    if (typeof getUsuarios === 'function') getUsuarios();
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

                fetch('/CapHum/getPuestosParaGestor', {
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

    /** Vista Candidatos (Capital Humano). Misma arquitectura que Gestión: heredoc con el script, self::set("script"), self::render. */
    public function candidatos()
    {
        $script = <<<'HTML'
        <script>

            // Estado del módulo candidatos
            var candidatoEditId = null;
            var candidatoNuevoId = null;
            var candidatoNuevoEmail = null;
            var candidatoReenviarId = null;
            var candidatoReenviarEmail = null;
            var candidatoDatosEnvio = null;
            var candidatosFiltrosLlenos = false;

            // Eager Loading: Map global con todos los candidatos y sus documentos precargados
            if (!window.candidatosDataMap) {
                window.candidatosDataMap = new Map();
            }

            /* ── KPI Candidatos: contador animado (misma lógica que Gestión kpiAnimateCounter) ── */
            function kpiAnimateCounterCand(el, target, dur, delay) {
                if (!el) return;
                dur   = dur   || 600;
                delay = delay || 0;
                setTimeout(function() {
                    var start = performance.now();
                    var ease  = function(t) { return t < 0.5 ? 2*t*t : -1+(4-2*t)*t; };
                    (function tick(now) {
                        var p = Math.min((now - start) / dur, 1);
                        el.textContent = Math.round(target * ease(p));
                        if (p < 1) requestAnimationFrame(tick);
                        else el.textContent = target;
                    })(performance.now());
                }, delay);
            }

            /* ── KPI Candidatos: barra animada (misma lógica que Gestión kpiAnimateBar) ── */
            function kpiAnimateBarCand(id, pct, delay) {
                var el = document.getElementById(id);
                if (!el) return;
                el.style.width = '0%';
                setTimeout(function() { el.style.width = (pct != null ? pct : 0) + '%'; }, (delay || 0) + 80);
            }

            function actualizarIndicadoresCandidatos(datos) {
                var total = (datos && datos.length) || 0;
                var porEvaluar = (datos && datos.filter(function(c){ return (c.estatus || "") === "Por evaluar"; }).length) || 0;
                var enviadas = (datos && datos.filter(function(c){ return c.postulacion_enviada == 1; }).length) || 0;
                var elTotal = document.getElementById("kpi-total-candidatos");
                var elPorEval = document.getElementById("kpi-por-evaluar");
                var elEnviadas = document.getElementById("kpi-postulaciones-enviadas");
                if (elTotal) kpiAnimateCounterCand(elTotal, total, 600, 0);
                if (elPorEval) kpiAnimateCounterCand(elPorEval, porEvaluar, 600, 60);
                if (elEnviadas) kpiAnimateCounterCand(elEnviadas, enviadas, 600, 120);
                var pctTotal = total > 0 ? 100 : 0;
                var pctEvaluar = total > 0 ? Math.min(100, Math.round((porEvaluar / total) * 100)) : 0;
                var pctEnviadas = total > 0 ? Math.min(100, Math.round((enviadas / total) * 100)) : 0;
                kpiAnimateBarCand("kpi-bar-cand-total",    pctTotal,   80);
                kpiAnimateBarCand("kpi-bar-cand-evaluar",  pctEvaluar, 160);
                kpiAnimateBarCand("kpi-bar-cand-enviadas", pctEnviadas, 240);
                var msTotal = document.getElementById("kpi-ms-cand-total");
                var msEval = document.getElementById("kpi-ms-cand-evaluar");
                var msEnv = document.getElementById("kpi-ms-cand-enviadas");
                var dvTotal = document.getElementById("kpi-dv-cand-total");
                var dvEval = document.getElementById("kpi-dv-cand-evaluar");
                var dvEnv = document.getElementById("kpi-dv-cand-enviadas");
                if (msTotal) msTotal.textContent = total;
                if (msEval) msEval.textContent = porEvaluar;
                if (msEnv) msEnv.textContent = enviadas;
                if (dvTotal) dvTotal.textContent = total;
                if (dvEval) dvEval.textContent = porEvaluar;
                if (dvEnv) dvEnv.textContent = enviadas;
                if (kpiCandCurrentMode === "vision") {
                    kpiAnimateDonutCand("kpi-arc-cand-total", pctTotal, 80);
                    kpiAnimateDonutCand("kpi-arc-cand-evaluar", pctEvaluar, 160);
                    kpiAnimateDonutCand("kpi-arc-cand-enviadas", pctEnviadas, 240);
                }
            }

            function getCandidatos() {
                var selDepto = document.getElementById("UserRole");
                var selPuesto = document.getElementById("UserPlan");
                var selEstatus = document.getElementById("FilterTransaction");
                var data = {};
                if (selEstatus && selEstatus.value) data.estatus = selEstatus.value;
                if (selDepto && selDepto.value) data.id_departamento = selDepto.value;
                if (selPuesto && selPuesto.value) data.id_puesto = selPuesto.value;

                http.request({
                    endpoint: "/caphum/getCandidatos",
                    metodo: "GET",
                    data: data,
                    onSuccess: function(resp) {
                        try {
                            if (!resp || !resp.success || !resp.datos) {
                                console.warn('getCandidatos: respuesta vacía o sin éxito', resp);
                                return;
                            }

                            if (!Array.isArray(resp.datos)) {
                                console.error('getCandidatos: resp.datos no es un array', resp);
                                return;
                            }

                            actualizarIndicadoresCandidatos(resp.datos);

                            // ==========================================
                            // CONSOLIDAR CANDIDATOS (ESTRUCTURA IDÉNTICA A GESTIÓN)
                            // ==========================================
                            var candidatosMap = new Map();

                            resp.datos.forEach(function(candidato) {
                                var id = candidato.id;

                                if (!id) {
                                    console.warn('getCandidatos: candidato sin ID', candidato);
                                    return;
                                }

                                if (!candidatosMap.has(id)) {
                                    // Primera vez que vemos este candidato - copiar todas las propiedades manualmente
                                    var candidatoConsolidado = {
                                        id: candidato.id,
                                        nombres: candidato.nombres,
                                        segundo_nombre: candidato.segundo_nombre,
                                        apellidop: candidato.apellidop,
                                        apellidom: candidato.apellidom,
                                        email: candidato.email,
                                        telefono: candidato.telefono,
                                        id_puesto: candidato.id_puesto,
                                        nombre_puesto: candidato.nombre_puesto,
                                        nombre_departamento: candidato.nombre_departamento,
                                        id_departamento: candidato.id_departamento,
                                        estatus: candidato.estatus,
                                        postulacion_enviada: candidato.postulacion_enviada,
                                        fecha_registro: candidato.fecha_registro,
                                        fecha_actualizacion: candidato.fecha_actualizacion,
                                        documentos: candidato.documentos || [],
                                        verificacion_expediente: candidato.verificacion_expediente || null,
                                        metricas: candidato.metricas || null,
                                        puestos: [{
                                            id_puesto: candidato.id_puesto,
                                            nombre_puesto: candidato.nombre_puesto,
                                            nombre_departamento: candidato.nombre_departamento,
                                            id_departamento: candidato.id_departamento
                                        }]
                                    };
                                    candidatosMap.set(id, candidatoConsolidado);
                                } else {
                                    // Ya existe, agregar nuevo puesto (por si acaso hay duplicados)
                                    var candidatoExistente = candidatosMap.get(id);
                                    if (candidatoExistente && candidatoExistente.puestos) {
                                        var puestoExiste = candidatoExistente.puestos.some(function(p) {
                                            return p.id_puesto === candidato.id_puesto &&
                                                   p.nombre_departamento === candidato.nombre_departamento;
                                        });

                                        if (!puestoExiste) {
                                            candidatoExistente.puestos.push({
                                                id_puesto: candidato.id_puesto,
                                                nombre_puesto: candidato.nombre_puesto,
                                                nombre_departamento: candidato.nombre_departamento,
                                                id_departamento: candidato.id_departamento
                                            });
                                        }
                                    }
                                }
                            });

                            var candidatosConsolidados = Array.from(candidatosMap.values());

                            // Guardar en variable global para otros usos
                            window.candidatosData = candidatosConsolidados;

                            // Eager Loading: Guardar todos los candidatos con sus documentos en el Map global
                            if (window.candidatosDataMap) {
                                window.candidatosDataMap.clear();
                            } else {
                                window.candidatosDataMap = new Map();
                            }
                            candidatosConsolidados.forEach(function(c) {
                                if (c.id) {
                                    var docPayload = {
                                        documentos: c.documentos || [],
                                        verificacion_expediente: c.verificacion_expediente || null,
                                        metricas: c.metricas || null
                                    };
                                    window.candidatosDataMap.set(parseInt(c.id, 10), docPayload);
                                }
                            });

                            // ==========================================
                            // MAPEAR DATOS PARA LA TABLA (ESTRUCTURA IDÉNTICA A GESTIÓN)
                            // ==========================================
                            var datos = candidatosConsolidados.map(function(c) {
                                var nombre = [c.nombres, c.segundo_nombre, c.apellidop, c.apellidom].filter(Boolean).join(" ");
                                var tienePuestos = c.puestos && c.puestos.length > 1;
                                var contacto = (c.email || "") + (c.telefono ? " | " + c.telefono : "");

                                // Generar HTML para puesto/departamento
                                var puestoDeptoHTML = "";
                                if (tienePuestos) {
                                    puestoDeptoHTML = '<div class="d-flex flex-column gap-1">';
                                    c.puestos.forEach(function(puesto) {
                                        puestoDeptoHTML += '<small class="text-muted d-flex align-items-center gap-1">' +
                                            '<i class="fa fa-building"></i>' +
                                            (puesto.nombre_departamento || 'Sin departamento') +
                                            '</small>' +
                                            '<small class="text-muted d-flex align-items-center gap-1">' +
                                            '<i class="fa fa-briefcase"></i>' +
                                            (puesto.nombre_puesto || 'Sin puesto') +
                                            '</small>';
                                    });
                                    puestoDeptoHTML += '</div>';
                                } else {
                                    puestoDeptoHTML = '<small class="text-muted d-flex align-items-center gap-1">' +
                                        '<i class="fa fa-building"></i>' +
                                        (c.nombre_departamento || 'Sin departamento') +
                                        '</small>' +
                                        '<small class="text-muted d-flex align-items-center gap-1">' +
                                        '<i class="fa fa-briefcase"></i>' +
                                        (c.nombre_puesto || 'Sin puesto') +
                                        '</small>';
                                }

                                var id = (c.id || 0).toString();
                                var est = c.estatus || "Por evaluar";
                                var estBadge = est === "Validado" ? "bg-success" : (est === "Por evaluar" ? "bg-warning text-dark" : (est === "Proceso cerrado" ? "bg-dark" : "bg-secondary"));

                                var acciones = '<div class="d-flex flex-wrap gap-1 align-items-center">' +
                                    '<button type="button" class="btn btn-sm btn-primary btn-editar-candidato" data-id="' + id + '" title="Editar"><i class="fa fa-edit"></i></button>' +
                                    '<button type="button" class="btn btn-sm btn-info text-white btn-reenviar-candidato" data-id="' + id + '" title="Reenviar correo"><i class="fa fa-envelope"></i></button>' +
                                    '<button type="button" class="btn btn-sm btn-secondary btn-documentacion-candidato" data-id="' + id + '" data-nombre="' + (nombre || "").replace(/"/g, "&quot;") + '" title="Documentación"><i class="fa fa-folder-open"></i></button>' +
                                    '<button type="button" class="btn btn-sm btn-danger btn-eliminar-candidato" data-id="' + id + '" title="Eliminar"><i class="fa fa-trash"></i></button></div>';

                                return {
                                    nombre: nombre,
                                    contacto: contacto,
                                    puestoDepto: puestoDeptoHTML.trim(),
                                    estatus: '<span class="badge ' + estBadge + '">' + est + '</span>',
                                    acciones: acciones
                                };
                            });

                            // Actualizar DataTable (usando $ como en Gestión)
                            try {
                                var tabla = $('#tablaCandidatos').DataTable();
                                if (tabla) {
                                    tabla.clear().rows.add(datos).draw();
                                } else {
                                    console.error('getCandidatos: No se pudo obtener la instancia de DataTable');
                                }
                            } catch (error) {
                                console.error('getCandidatos: Error al actualizar DataTable', error);
                            }

                            // Llenar filtros solo la primera vez
                            if (!candidatosFiltrosLlenos) {
                                candidatosFiltrosLlenos = true;
                                var deptMap = {};
                                var puestoMap = {};
                                candidatosConsolidados.forEach(function(c) {
                                    if (c.id_departamento && c.nombre_departamento) deptMap[c.id_departamento] = c.nombre_departamento;
                                    if (c.id_puesto && c.nombre_puesto) puestoMap[c.id_puesto] = c.nombre_puesto;
                                });
                                var selD = document.getElementById("UserRole");
                                var selP = document.getElementById("UserPlan");
                                if (selD) {
                                    var opts = selD.querySelectorAll("option");
                                    for (var i = opts.length - 1; i >= 1; i--) opts[i].remove();
                                    for (var k in deptMap) {
                                        var o = document.createElement("option");
                                        o.value = k;
                                        o.textContent = deptMap[k];
                                        selD.appendChild(o);
                                    }
                                }
                                if (selP) {
                                    var opts = selP.querySelectorAll("option");
                                    for (var i = opts.length - 1; i >= 1; i--) opts[i].remove();
                                    for (var k in puestoMap) {
                                        var o = document.createElement("option");
                                        o.value = k;
                                        o.textContent = puestoMap[k];
                                        selP.appendChild(o);
                                    }
                                }
                            }
                        } catch (error) {
                            console.error('getCandidatos: Error en el procesamiento', error);
                            if (typeof Swal !== "undefined") {
                                Swal.fire({
                                    icon: "error",
                                    title: "Error",
                                    text: "Error al procesar los candidatos. Revisa la consola (F12)."
                                });
                            }
                        }
                    },
                    onError: function(err) {
                        console.error('getCandidatos: Error en la petición', err);
                        if (typeof Swal !== "undefined") {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: "Error al cargar los candidatos. Por favor, recarga la página."
                            });
                        }
                    }
                });
            }

            function kpiTogglePanelCandidatos() {
                var pan = document.getElementById("kpiCollapsibleCandidatos");
                var btn = document.getElementById("kpiToggleBtnCandidatos");
                var controls = document.getElementById("kpiViewControlsCand");
                var sep = document.getElementById("kpiViewControlsSepCand");
                if (!pan || !btn) return;
                var isOpen = pan.classList.toggle("open");
                btn.classList.toggle("open", isOpen);
                if (controls) controls.classList.toggle("kpi-vc-hidden", !isOpen);
                if (sep) sep.classList.toggle("kpi-sep-hidden", !isOpen);
                kpiCandPanelOpen = isOpen;
                if (typeof localStorage !== "undefined") localStorage.setItem(KPI_CAND_STORAGE_OPEN, isOpen);
            }

            var KPI_CAND_STORAGE_MODE = "kpi_cand_mode";
            var KPI_CAND_STORAGE_OPEN = "kpi_cand_open";
            var kpiCandCurrentMode = (typeof localStorage !== "undefined" && localStorage.getItem(KPI_CAND_STORAGE_MODE)) || "default";
            var kpiCandPanelOpen = (typeof localStorage !== "undefined" && localStorage.getItem(KPI_CAND_STORAGE_OPEN) !== "false");

            function kpiAnimateDonutCand(arcId, pct, delay) {
                var el = document.getElementById(arcId);
                if (!el) return;
                var CIRC = 2 * Math.PI * 36;
                el.style.strokeDasharray = "0 " + CIRC;
                setTimeout(function() {
                    var filled = (pct / 100) * CIRC;
                    el.style.strokeDasharray = filled + " " + (CIRC - filled);
                }, (delay || 0) + 100);
            }

            function kpiApplyModeCand(mode, animate) {
                var row = document.getElementById("kpiRowNewCand");
                if (!row) return;
                row.classList.remove("mode-default", "mode-vision", "mode-ministat");
                row.classList.add("mode-" + mode);
                ["default", "vision", "ministat"].forEach(function(m) {
                    var btn = document.getElementById("vbtn-cand-" + (m === "default" ? "default" : m === "vision" ? "vision" : "ministat"));
                    if (btn) btn.classList.toggle("active", m === mode);
                });
                if (animate && mode === "vision") {
                    var total = parseInt(document.getElementById("kpi-total-candidatos").textContent, 10) || 0;
                    var porEval = parseInt(document.getElementById("kpi-por-evaluar").textContent, 10) || 0;
                    var enviadas = parseInt(document.getElementById("kpi-postulaciones-enviadas").textContent, 10) || 0;
                    var pctTotal = total > 0 ? 100 : 0;
                    var pctEval = total > 0 ? Math.min(100, Math.round((porEval / total) * 100)) : 0;
                    var pctEnv = total > 0 ? Math.min(100, Math.round((enviadas / total) * 100)) : 0;
                    kpiAnimateDonutCand("kpi-arc-cand-total", pctTotal, 80);
                    kpiAnimateDonutCand("kpi-arc-cand-evaluar", pctEval, 160);
                    kpiAnimateDonutCand("kpi-arc-cand-enviadas", pctEnv, 240);
                }
            }

            function kpiSetModeCand(mode) {
                kpiCandCurrentMode = mode;
                if (typeof localStorage !== "undefined") localStorage.setItem(KPI_CAND_STORAGE_MODE, mode);
                kpiApplyModeCand(mode, true);
            }

            function kpiResetPrefsCand() {
                if (typeof localStorage !== "undefined") {
                    localStorage.removeItem(KPI_CAND_STORAGE_MODE);
                    localStorage.removeItem(KPI_CAND_STORAGE_OPEN);
                }
                location.reload();
            }

            (function applyKpiCandInitialState() {
                function run() {
                    var pan = document.getElementById("kpiCollapsibleCandidatos");
                    var btn = document.getElementById("kpiToggleBtnCandidatos");
                    var controls = document.getElementById("kpiViewControlsCand");
                    var sep = document.getElementById("kpiViewControlsSepCand");
                    if (pan) pan.classList.toggle("open", kpiCandPanelOpen);
                    if (btn) btn.classList.toggle("open", kpiCandPanelOpen);
                    if (controls) controls.classList.toggle("kpi-vc-hidden", !kpiCandPanelOpen);
                    if (sep) sep.classList.toggle("kpi-sep-hidden", !kpiCandPanelOpen);
                    kpiApplyModeCand(kpiCandCurrentMode, false);
                }
                if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", run);
                else run();
            })();

            function initFlatpickrFechaPostulacion() {
                var input = document.getElementById("candidato_fecha_postulacion");
                if (!input || typeof flatpickr === "undefined") return;
                if (input._flatpickr) return;
                var hoy = new Date().toISOString().slice(0, 10);
                flatpickr(input, { dateFormat: "Y-m-d", defaultDate: hoy, maxDate: hoy, allowInput: false, clickOpens: true, appendTo: document.body, static: false, locale: (typeof flatpickr !== "undefined" && flatpickr.l10ns && flatpickr.l10ns.es) ? flatpickr.l10ns.es : undefined });
            }

            function toggleLegionCandidato() {
                var div = document.getElementById("div_candidato_legion"); var chk = document.getElementById("candidato_asignar_legion");
                div.style.display = chk && chk.checked ? "block" : "none";
                if (!chk.checked) document.getElementById("candidato_id_legion").value = "";
            }

            function candidatosTableClick(e) {
                var target = e.target; var tabla = document.getElementById("tablaCandidatos");
                if (!tabla || !tabla.contains(target)) return;
                var btn = target.closest(".btn-editar-candidato");
                if (btn) { e.preventDefault(); e.stopPropagation(); var id = btn.getAttribute("data-id"); if (id) editarCandidato(parseInt(id, 10)); return; }
                btn = target.closest(".btn-reenviar-candidato");
                if (btn) { e.preventDefault(); e.stopPropagation(); var id = btn.getAttribute("data-id"); if (id) abrirModalReenviarPostulacion(parseInt(id, 10)); return; }
                btn = target.closest(".btn-documentacion-candidato");
                if (btn) { e.preventDefault(); e.stopPropagation(); var id = btn.getAttribute("data-id"); var nombre = btn.getAttribute("data-nombre") || ""; if (id) abrirModalDocumentacionCandidato(parseInt(id, 10), nombre); return; }
                btn = target.closest(".btn-eliminar-candidato");
                if (btn) { e.preventDefault(); e.stopPropagation(); var id = btn.getAttribute("data-id"); if (id) eliminarCandidato(parseInt(id, 10)); }
            }

            function renderMetricasDoc(bloqueMetricas, m) {
                if (!bloqueMetricas || !m) return;
                bloqueMetricas.classList.remove("d-none");
                var total = m.total_documentos != null ? m.total_documentos : 0;
                var requeridos = m.documentos_requeridos != null ? m.documentos_requeridos : 11;
                var pct = m.porcentaje != null ? m.porcentaje : (requeridos > 0 ? Math.min(100, Math.round((total / requeridos) * 100)) : 0);
                var completo = m.expediente_completo === true;
                var html = "<div class=\"card border shadow-none\"><div class=\"card-header py-2 bg-light\"><strong><i class=\"fa fa-chart-pie me-1\"></i>Métricas del expediente</strong></div><div class=\"card-body py-2 small\">";
                html += "<p class=\"text-muted mb-2\">Resumen de documentación recibida (cuántos documentos obligatorios ha subido el candidato).</p>";
                html += "<div class=\"row g-2 align-items-center\">";
                html += "<div class=\"col-6 col-md-4\"><span class=\"text-muted d-block\">Documentos subidos</span><strong class=\"text-primary\">" + total + " de " + requeridos + "</strong></div>";
                html += "<div class=\"col-6 col-md-4\"><span class=\"text-muted d-block\">Avance</span><strong>" + pct + "%</strong>";
                if (pct < 100) html += " <div class=\"progress mt-1\" style=\"height:6px;\"><div class=\"progress-bar\" role=\"progressbar\" style=\"width:" + pct + "%\" aria-valuenow=\"" + pct + "\" aria-valuemin=\"0\" aria-valuemax=\"100\"></div></div>";
                html += "</div>";
                html += "<div class=\"col-6 col-md-4\"><span class=\"text-muted d-block\">Expediente completo</span><strong class=\"" + (completo ? "text-success" : "text-secondary") + "\">" + (completo ? "Sí" : "No") + "</strong></div>";
                html += "</div></div></div>";
                bloqueMetricas.innerHTML = html;
            }

            function renderAccionesProcesoCandidato(bloqueAccionesProceso, metricas, idCandidato) {
                if (!bloqueAccionesProceso) return;
                var validados = metricas && metricas.validados != null ? parseInt(metricas.validados, 10) : 0;
                var requeridos = metricas && metricas.documentos_requeridos != null ? parseInt(metricas.documentos_requeridos, 10) : 11;
                if (validados >= requeridos && requeridos >= 11) {
                    bloqueAccionesProceso.classList.remove("d-none");
                    bloqueAccionesProceso.innerHTML = "<div class=\"card border shadow-none\"><div class=\"card-body py-3\"><p class=\"text-muted small mb-3\">Los 11 documentos han sido validados. Elige una acción:</p><div class=\"d-flex flex-wrap gap-2\"><button type=\"button\" class=\"btn btn-outline-danger btn-cerrar-proceso-candidato\" data-id=\"" + idCandidato + "\"><i class=\"fa fa-times-circle me-1\"></i>Cerrar proceso</button><button type=\"button\" class=\"btn btn-primary btn-continuar-proceso-candidato\" data-id=\"" + idCandidato + "\"><i class=\"fa fa-check-circle me-1\"></i>Continuar proceso</button></div></div></div>";
                } else {
                    bloqueAccionesProceso.classList.add("d-none");
                    bloqueAccionesProceso.innerHTML = "";
                }
            }

            function renderVerificacionDoc(bloqueVerif, v) {
                if (!bloqueVerif || !v) return;
                bloqueVerif.classList.remove("d-none");
                var scoreFrente = v.identificacion_frente_score != null ? Number(v.identificacion_frente_score) : null;
                var scoreReverso = v.identificacion_reverso_score != null ? Number(v.identificacion_reverso_score) : null;
                var checksOk = v.checks_ok != null ? parseInt(v.checks_ok, 10) : null;
                var checksTotales = v.checks_totales != null ? parseInt(v.checks_totales, 10) : null;
                var todoCoincide = v.todo_coincide === true;
                var alertas = Array.isArray(v.alertas) && v.alertas.length ? v.alertas : [];
                var confianzaNum = null;
                if (scoreFrente != null && scoreReverso != null) { confianzaNum = Math.round((scoreFrente + scoreReverso) / 2); }
                else if (scoreFrente != null) { confianzaNum = scoreFrente; }
                else if (scoreReverso != null) { confianzaNum = scoreReverso; }
                else if (checksTotales > 0 && checksOk != null) { confianzaNum = Math.round((checksOk / checksTotales) * 100); }
                var confianzaTexto = confianzaNum != null ? confianzaNum + "%" : "—";
                var confianzaClase = "text-secondary";
                if (confianzaNum != null) {
                    if (confianzaNum >= 80) confianzaClase = "text-success";
                    else if (confianzaNum >= 50) confianzaClase = "text-warning";
                    else confianzaClase = "text-danger";
                }
                var html = "<div class=\"card border shadow-none\"><div class=\"card-header py-2 bg-light\"><strong><i class=\"fa fa-shield-alt me-1\"></i>Resultado de la verificación API</strong></div><div class=\"card-body py-2 small\">";
                html += "<div class=\"row g-2 mb-2 align-items-center\">";
                html += "<div class=\"col-6 col-md\"><span class=\"text-muted d-block\">Confianza</span><strong class=\"fs-6 " + confianzaClase + "\">" + confianzaTexto + "</strong></div>";
                html += "<div class=\"col-6 col-md\"><span class=\"text-muted d-block\">Frente</span><strong class=\"text-primary\">" + (scoreFrente != null ? scoreFrente + "%" : "—") + "</strong></div>";
                html += "<div class=\"col-6 col-md\"><span class=\"text-muted d-block\">Reverso</span><strong class=\"text-primary\">" + (scoreReverso != null ? scoreReverso + "%" : "—") + "</strong></div>";
                html += "<div class=\"col-6 col-md\"><span class=\"text-muted d-block\">Checks</span><strong>" + (checksOk != null && checksTotales != null ? checksOk + "/" + checksTotales : "—") + "</strong></div>";
                html += "<div class=\"col-6 col-md\"><span class=\"text-muted d-block\">Coinciden</span><strong class=\"" + (todoCoincide ? "text-success" : (v.todo_coincide === false ? "text-danger" : "text-secondary")) + "\">" + (v.todo_coincide === true ? "Sí" : (v.todo_coincide === false ? "No" : "—")) + "</strong></div>";
                html += "</div>";
                var lineasComparaciones = [];
                if (v.comparaciones && typeof v.comparaciones === "object") {
                    var comp = v.comparaciones;
                    var labels = { "nombre_frente_vs_reverso": "Nombre en INE = Nombre en reverso", "fecha_nac_curp_vs_mrz": "Fecha nac. (CURP) = Fecha en reverso", "nombre_id_vs_curp_pdf": "Nombre en INE = Nombre en CURP PDF", "curp_vs_fiscal": "CURP = Constancia fiscal", "nombre_vs_fiscal": "Nombre = Constancia fiscal", "curp_vs_nss": "CURP = NSS", "nombre_vs_nss": "Nombre = NSS", "nombre_vs_acta": "Nombre = Acta de nacimiento", "fecha_nac_vs_acta": "Fecha nac. = Acta", "curp_id_vs_documento": "CURP en INE = Otro documento" };
                    Object.keys(comp).forEach(function(k) {
                        var c = comp[k];
                        if (!c || typeof c !== "object") return;
                        if (c.coincide !== undefined) lineasComparaciones.push((labels[k] || k) + ": " + (c.coincide ? "✔ Coincide" : "✘ No coincide"));
                        else if (c.es_reciente !== undefined) lineasComparaciones.push("CURP PDF: " + (c.es_reciente ? "Reciente" : (c.meses_antiguedad || "?") + " meses"));
                    });
                }
                var btnId = "";
                if (lineasComparaciones.length) {
                    btnId = "btnVerComparaciones_" + (Math.random().toString(36).slice(2, 9));
                    html += "<div class=\"border-top pt-2 mt-2\"><button type=\"button\" class=\"btn btn-sm btn-outline-secondary\" id=\"" + btnId + "\" title=\"Ver comparaciones entre documentos\"><i class=\"fa fa-list-ul me-1\"></i>Ver comparaciones entre documentos</button></div>";
                }
                if (alertas.length) {
                    html += "<div class=\"mt-2 pt-2 border-top\"><span class=\"text-muted d-block mb-1\"><strong>Alertas</strong></span><ul class=\"mb-0 ps-3\"><li class=\"text-warning\">" + alertas.join("</li><li class=\"text-warning\">") + "</li></ul></div>";
                }
                html += "</div></div>";
                bloqueVerif.innerHTML = html;
                if (lineasComparaciones.length && window.bootstrap && window.bootstrap.Popover) {
                    var btnComp = document.getElementById(btnId);
                    if (btnComp) {
                        var popContent = "<div class=\"text-start small\" style=\"min-width: 280px;\"><p class=\"text-muted mb-2\"><strong>Comparaciones entre documentos</strong><br><span class=\"text-muted\">(que los datos del candidato coincidan en todos)</span></p><ul class=\"list-unstyled mb-0\">" + lineasComparaciones.map(function(l) { var ok = l.indexOf("✔") !== -1 || l.indexOf("Reciente") !== -1; return "<li class=\"py-1 border-bottom border-light\"><i class=\"fa fa-" + (ok ? "check-circle text-success" : "times-circle text-danger") + " me-2\"></i>" + l + "</li>"; }).join("") + "</ul></div>";
                        new window.bootstrap.Popover(btnComp, { content: popContent, html: true, trigger: "click", placement: "bottom", container: "body" });
                    }
                }
            }

            function badgeVerificacionDoc(tipoDoc, v) {
                if (!v || !tipoDoc) return "";
                var t = (tipoDoc + "").trim().toUpperCase();
                if (t.indexOf("REVERSO") !== -1) { var r = v.identificacion_reverso_score; if (r == null) return ""; return "<span class=\"badge bg-primary ms-1\" title=\"Veracidad con el candidato\">" + r + "%</span>"; }
                if (t === "IDENTIFICACIÓN OFICIAL" || t === "IDENTIFICACION OFICIAL") { var s = v.identificacion_frente_score; if (s == null) return ""; return "<span class=\"badge bg-primary ms-1\" title=\"Veracidad con el candidato\">" + s + "%</span>"; }
                var comp = v.comparaciones || {};
                if (t.indexOf("CURP") !== -1 && t.indexOf("ACTA") === -1) { var c1 = comp.nombre_id_vs_curp_pdf || comp.curp_id_vs_documento; if (c1 && c1.coincide !== undefined) return c1.coincide ? "<span class=\"badge bg-success ms-1\" title=\"Coincide con INE\">Coincide</span>" : "<span class=\"badge bg-danger ms-1\" title=\"No coincide\">No coincide</span>"; }
                if (t.indexOf("CONSTANCIA") !== -1 || t.indexOf("FISCAL") !== -1) { var c2 = comp.curp_vs_fiscal || comp.nombre_vs_fiscal; if (c2 && c2.coincide !== undefined) return c2.coincide ? "<span class=\"badge bg-success ms-1\">Coincide</span>" : "<span class=\"badge bg-danger ms-1\">No coincide</span>"; }
                if (t.indexOf("NSS") !== -1 || t.indexOf("SEGURIDAD SOCIAL") !== -1) { var c3 = comp.curp_vs_nss || comp.nombre_vs_nss; if (c3 && c3.coincide !== undefined) return c3.coincide ? "<span class=\"badge bg-success ms-1\">Coincide</span>" : "<span class=\"badge bg-danger ms-1\">No coincide</span>"; }
                if (t.indexOf("ACTA") !== -1) { var c4 = comp.nombre_vs_acta || comp.fecha_nac_vs_acta; if (c4 && c4.coincide !== undefined) return c4.coincide ? "<span class=\"badge bg-success ms-1\">Coincide</span>" : "<span class=\"badge bg-danger ms-1\">No coincide</span>"; }
                return "";
            }

            function eliminarDocYRecargarModal(idDoc, idCandidato) {
                fetch("/caphum/eliminarDocumentoCandidato", { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded", "X-Requested-With": "XMLHttpRequest" }, body: "id=" + idDoc })
                .then(function(r){ return r.json(); }).then(function(res) {
                    if (res.success) { if (typeof Swal !== "undefined") Swal.fire({ icon: "success", title: "Eliminado", text: res.mensaje || "Documento eliminado." }); cargarDocumentosModal(idCandidato); }
                    else { if (typeof Swal !== "undefined") Swal.fire({ icon: "error", title: "Error", text: res.mensaje || "No se pudo eliminar." }); }
                }).catch(function() { if (typeof Swal !== "undefined") Swal.fire({ icon: "error", title: "Error", text: "Error de conexión." }); });
            }

            function renderListaDocumentos(lista, cargando, vacio, datos, verif, idCandidato) {
                if (!lista) return;
                if (cargando) cargando.classList.add("d-none");
                lista.innerHTML = "";
                if (!datos || datos.length === 0) { if (vacio) vacio.classList.remove("d-none"); return; }
                if (vacio) vacio.classList.add("d-none");
                function escHtml(s) { var t = String(s === null || s === undefined ? "" : s); return t.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;"); }
                datos.forEach(function(d) {
                    var item = document.createElement("div");
            item.className = "list-group-item list-group-item-action d-flex justify-content-between align-items-center";
            var fecha = d.fecha_carga ? new Date(d.fecha_carga).toLocaleDateString("es-MX") : "";
            var badge = badgeVerificacionDoc(d.tipo_documento, verif);
            var tooltipFiscalHtml = "";
            var tipoNorm = (d.tipo_documento || "").toUpperCase();
            if ((tipoNorm.indexOf("FISCAL") !== -1 || tipoNorm.indexOf("SITUACION") !== -1) && d.verificacion_fiscal && typeof d.verificacion_fiscal === "object") {
                var v = d.verificacion_fiscal;
                var filas = [
                    ["RFC", v.rfc],
                    ["CURP", v.curp],
                    ["Fecha emisión", v.fecha_emision],
                    ["Meses antigüedad", v.meses_antiguedad != null ? v.meses_antiguedad : "-"],
                    ["Vigencia ≤2 meses", v.vigencia_ok ? "Sí" : "No"],
                    ["Actividad Asalariado", v.actividad_asalariado ? "Sí" : "No"],
                    ["Régimen Sueldos y Salarios", v.regimen_sueldos_salarios ? "Sí" : "No"]
                ];
                var tableHtml = "<table class=\"table table-sm table-bordered mb-0\"><tbody>";
                filas.forEach(function(r) { tableHtml += "<tr><td class=\"text-muted\">" + escHtml(r[0]) + "</td><td>" + escHtml(r[1]) + "</td></tr>"; });
                tableHtml += "</tbody></table>";
                tooltipFiscalHtml = " <span class=\"ms-1\" data-bs-toggle=\"tooltip\" data-bs-html=\"true\" data-bs-title=\"" + tableHtml.replace(/"/g, "&quot;") + "\"><i class=\"fa fa-info-circle text-info\"></i></span>";
            }
            var tooltipIdHtml = "";
            if ((tipoNorm === "IDENTIFICACIÓN OFICIAL" || tipoNorm === "IDENTIFICACION OFICIAL") && verif && typeof verif === "object") {
                var tipoDocLabel = "—";
                if (verif.tipo_documento) {
                    var td = (verif.tipo_documento + "").toUpperCase();
                    if (td.indexOf("INE") !== -1) tipoDocLabel = "INE";
                    else if (td.indexOf("RESIDENCIA PERMANENTE") !== -1 || td === "RESIDENCIA_PERMANENTE") tipoDocLabel = "Residencia permanente";
                    else if (td.indexOf("RESIDENCIA") !== -1) tipoDocLabel = "Residencia temporal";
                    else tipoDocLabel = verif.tipo_documento;
                }
                var coincideFrenteReverso = "—";
                if (verif.comparaciones && verif.comparaciones.nombre_frente_vs_reverso && verif.comparaciones.nombre_frente_vs_reverso.coincide !== undefined) {
                    coincideFrenteReverso = verif.comparaciones.nombre_frente_vs_reverso.coincide ? "Sí" : "No";
                }
                var modalDoc = document.getElementById("modalDocumentacionCandidato");
                var nombreTicket = (modalDoc && modalDoc.dataset.nombreCandidato) ? (modalDoc.dataset.nombreCandidato + "").trim() : "";
                var mismoNombreTicket = "—";
                if (verif.nombre_ocr && nombreTicket) {
                    var norm = function(s) {
                        var t = (s + "").toUpperCase().replace(/Á/g,"A").replace(/É/g,"E").replace(/Í/g,"I").replace(/Ó/g,"O").replace(/Ú/g,"U").replace(/[^A-Z\s]/g,"").replace(/\s+/g," ").trim();
                        return t.split(" ").filter(function(w){ return w.length > 2; });
                    };
                    var n1 = norm(verif.nombre_ocr), n2 = norm(nombreTicket);
                    var comunes = n1.filter(function(w){ return n2.indexOf(w) !== -1; });
                    mismoNombreTicket = comunes.length >= 2 ? "Sí" : "No";
                } else if (verif.nombre_ocr || nombreTicket) { mismoNombreTicket = "—"; }
                var filasId = [
                    ["Nombre de la persona completo", verif.nombre_ocr || "—"],
                    ["CURP", verif.curp_definitivo || "—"],
                    ["Año de nacimiento", verif.anio_nacimiento || "—"],
                    ["Tipo documento", tipoDocLabel],
                    ["Coincide frente con reverso", coincideFrenteReverso],
                    ["Mismo nombre que el del ticket", mismoNombreTicket]
                ];
                var tableIdHtml = "<table class=\"table table-sm table-bordered mb-0\"><tbody>";
                filasId.forEach(function(r) { tableIdHtml += "<tr><td class=\"text-muted\">" + escHtml(r[0]) + "</td><td>" + escHtml(r[1]) + "</td></tr>"; });
                tableIdHtml += "</tbody></table>";
                tooltipIdHtml = " <span class=\"ms-1\" data-bs-toggle=\"tooltip\" data-bs-html=\"true\" data-bs-title=\"" + tableIdHtml.replace(/"/g, "&quot;") + "\"><i class=\"fa fa-info-circle text-info\"></i></span>";
            }
            var tooltipCalidadHtml = "";
            var vc = d.verificacion_calidad;
            var notasCalidad = (vc && typeof vc === "object" && Array.isArray(vc.notas)) ? vc.notas : [];
            if ((tipoNorm.indexOf("IDENTIFICACION") !== -1 || tipoNorm.indexOf("IDENTIFICACIÓN") !== -1) && notasCalidad.length > 0) {
                var tit = "Revisión identificación oficial (revisar manualmente):";
                var listHtml = "<div class=\"text-start\"><strong>" + escHtml(tit) + "</strong><ul class=\"mb-0 ps-3 small\">";
                notasCalidad.forEach(function(n) { listHtml += "<li>" + escHtml(typeof n === "string" ? n : String(n)) + "</li>"; });
                listHtml += "</ul></div>";
                tooltipCalidadHtml = " <span class=\"ms-1\" data-bs-toggle=\"tooltip\" data-bs-html=\"true\" data-bs-title=\"" + listHtml.replace(/"/g, "&quot;") + "\"><i class=\"fa fa-exclamation-triangle text-warning\" title=\"Notas de revisión\"></i></span>";
            }
            var tooltipEstadoCuentaHtml = "";
            if ((tipoNorm.indexOf("ESTADO") !== -1 && tipoNorm.indexOf("CUENTA") !== -1) && vc && typeof vc === "object" && (vc.banco_detectado || vc.nombre_propietario)) {
                var nombreTicketEc = "";
                try { var modalEc = document.getElementById("modalDocumentacionCandidato"); if (modalEc && modalEc.dataset.nombreCandidato) nombreTicketEc = (modalEc.dataset.nombreCandidato || "").trim(); } catch (e) {}
                var mismoNombreTicketEc = "—";
                if (vc.nombre_propietario && nombreTicketEc) {
                    var normEc = function(s) { return (s + "").toUpperCase().replace(/Á/g,"A").replace(/É/g,"E").replace(/Í/g,"I").replace(/Ó/g,"O").replace(/Ú/g,"U").replace(/[^A-Z\s]/g,"").replace(/\s+/g," ").trim().split(" ").filter(function(w){ return w.length > 2; }); };
                    var n1Ec = normEc(vc.nombre_propietario), n2Ec = normEc(nombreTicketEc);
                    var comunesEc = n1Ec.filter(function(w){ return n2Ec.indexOf(w) !== -1; });
                    mismoNombreTicketEc = comunesEc.length >= 2 ? "Sí" : "No";
                }
                var filasEc = [
                    ["Nombre del banco", vc.banco_detectado || "—"],
                    ["Nombre del propietario", vc.nombre_propietario || "—"],
                    ["Mismo nombre que el del ticket", mismoNombreTicketEc]
                ];
                var tableEcHtml = "<table class=\"table table-sm table-bordered mb-0\"><tbody>";
                filasEc.forEach(function(r) { tableEcHtml += "<tr><td class=\"text-muted\">" + escHtml(r[0]) + "</td><td>" + escHtml(r[1]) + "</td></tr>"; });
                tableEcHtml += "</tbody></table>";
                tooltipEstadoCuentaHtml = " <span class=\"ms-1\" data-bs-toggle=\"tooltip\" data-bs-html=\"true\" data-bs-title=\"" + tableEcHtml.replace(/"/g, "&quot;") + "\"><i class=\"fa fa-info-circle text-info\"></i></span>";
            }
            var tooltipNssHtml = "";
            if ((tipoNorm.indexOf("NSS") !== -1 || tipoNorm.indexOf("SEGURIDAD SOCIAL") !== -1) && vc && typeof vc === "object" && vc.nss_extraido) {
                var nombreTicketNss = "";
                try { var modalNss = document.getElementById("modalDocumentacionCandidato"); if (modalNss && modalNss.dataset.nombreCandidato) nombreTicketNss = (modalNss.dataset.nombreCandidato || "").trim(); } catch (e) {}
                var mismoNombreTicketNss = "—";
                if (vc.nombre && nombreTicketNss) {
                    var normNss = function(s) { return (s + "").toUpperCase().replace(/Á/g,"A").replace(/É/g,"E").replace(/Í/g,"I").replace(/Ó/g,"O").replace(/Ú/g,"U").replace(/[^A-Z\s]/g,"").replace(/\s+/g," ").trim().split(" ").filter(function(w){ return w.length > 2; }); };
                    var n1Nss = normNss(vc.nombre), n2Nss = normNss(nombreTicketNss);
                    var comunesNss = n1Nss.filter(function(w){ return n2Nss.indexOf(w) !== -1; });
                    mismoNombreTicketNss = comunesNss.length >= 2 ? "Sí" : "No";
                }
                var filasNss = [
                    ["Número de seguridad social", vc.nss_extraido || "—"],
                    ["Mismo nombre que el del ticket", mismoNombreTicketNss]
                ];
                var tableNssHtml = "<table class=\"table table-sm table-bordered mb-0\"><tbody>";
                filasNss.forEach(function(r) { tableNssHtml += "<tr><td class=\"text-muted\">" + escHtml(r[0]) + "</td><td>" + escHtml(r[1]) + "</td></tr>"; });
                tableNssHtml += "</tbody></table>";
                tooltipNssHtml = " <span class=\"ms-1\" data-bs-toggle=\"tooltip\" data-bs-html=\"true\" data-bs-title=\"" + tableNssHtml.replace(/"/g, "&quot;") + "\"><i class=\"fa fa-info-circle text-info\"></i></span>";
            }
            var tooltipCurpHtml = "";
            if (tipoNorm.indexOf("CURP") !== -1 && (tipoNorm.indexOf("ACTA") === -1) && vc && typeof vc === "object" && (vc.curp_extraido || vc.curp_definitivo)) {
                var curpVal = vc.curp_extraido || vc.curp_definitivo || "—";
                var nombreTicketCurp = "";
                try { var modalCurp = document.getElementById("modalDocumentacionCandidato"); if (modalCurp && modalCurp.dataset.nombreCandidato) nombreTicketCurp = (modalCurp.dataset.nombreCandidato || "").trim(); } catch (e) {}
                var mismoNombreTicketCurp = "—";
                if (vc.nombre && nombreTicketCurp) {
                    var normCurp = function(s) { return (s + "").toUpperCase().replace(/Á/g,"A").replace(/É/g,"E").replace(/Í/g,"I").replace(/Ó/g,"O").replace(/Ú/g,"U").replace(/[^A-Z\s]/g,"").replace(/\s+/g," ").trim().split(" ").filter(function(w){ return w.length > 2; }); };
                    var n1Curp = normCurp(vc.nombre), n2Curp = normCurp(nombreTicketCurp);
                    var comunesCurp = n1Curp.filter(function(w){ return n2Curp.indexOf(w) !== -1; });
                    mismoNombreTicketCurp = comunesCurp.length >= 2 ? "Sí" : "No";
                }
                var filasCurp = [
                    ["CURP", curpVal],
                    ["Mismo nombre que el del ticket", mismoNombreTicketCurp]
                ];
                var tableCurpHtml = "<table class=\"table table-sm table-bordered mb-0\"><tbody>";
                filasCurp.forEach(function(r) { tableCurpHtml += "<tr><td class=\"text-muted\">" + escHtml(r[0]) + "</td><td>" + escHtml(r[1]) + "</td></tr>"; });
                tableCurpHtml += "</tbody></table>";
                tooltipCurpHtml = " <span class=\"ms-1\" data-bs-toggle=\"tooltip\" data-bs-html=\"true\" data-bs-title=\"" + tableCurpHtml.replace(/"/g, "&quot;") + "\"><i class=\"fa fa-info-circle text-info\"></i></span>";
            }
            var esValidado = parseInt(d.validado || 0, 10) === 1;
            var btnValidarClase = esValidado ? "btn-success" : "btn-outline-success";
            var btnValidarIcon = esValidado ? "fa-check-circle" : "fa-check";
            var btnValidarTitle = esValidado ? "Documento validado" : "Marcar como validado";
            var btnValidarDisabled = esValidado ? " disabled" : "";
            if (esValidado) { item.style.borderLeft = "3px solid #198754"; item.style.background = "#f0fdf4"; }
            var btnEliminarHtml = esValidado
                ? "<span class=\"btn btn-sm btn-outline-secondary disabled\" title=\"No se puede eliminar un documento validado\"><i class=\"fa fa-trash\"></i></span>"
                : "<button type=\"button\" class=\"btn btn-sm btn-outline-danger btn-eliminar-doc-candidato\" data-id=\"" + d.id + "\" title=\"Eliminar\"><i class=\"fa fa-trash\"></i></button>";
            item.innerHTML = "<div class=\"d-flex align-items-center flex-wrap\"><div><strong>" + (d.tipo_documento || "Documento") + "</strong>" + tooltipFiscalHtml + tooltipIdHtml + tooltipCalidadHtml + tooltipEstadoCuentaHtml + tooltipNssHtml + tooltipCurpHtml + (esValidado ? " <span class=\"badge bg-success ms-1\">Validado</span>" : "") + "<br><small class=\"text-muted\">" + (d.nombre_archivo || "") + (fecha ? " · " + fecha : "") + "</small></div>" + badge + "</div>" +
                "<div class=\"d-flex gap-1 align-items-center\">" +
                "<button type=\"button\" class=\"btn btn-sm " + btnValidarClase + " btn-validar-doc-candidato\"" + btnValidarDisabled + " data-id=\"" + d.id + "\" data-validado=\"" + (esValidado ? 1 : 0) + "\" title=\"" + btnValidarTitle + "\"><i class=\"fa " + btnValidarIcon + "\"></i></button>" +
                "<a href=\"/caphum/verDocumentoCandidato/" + d.id + "\" target=\"_blank\" class=\"btn btn-sm btn-outline-primary\" title=\"Abrir\"><i class=\"fa fa-eye\"></i></a>" +
                btnEliminarHtml + "</div>";
            lista.appendChild(item);
        });
        lista.querySelectorAll("[data-bs-toggle=\"tooltip\"]").forEach(function(el) { if (typeof bootstrap !== "undefined" && bootstrap.Tooltip) { new bootstrap.Tooltip(el); } });
        lista.querySelectorAll(".btn-validar-doc-candidato").forEach(function(btn) {
            btn.addEventListener("click", function() {
                var idDoc = parseInt(btn.getAttribute("data-id"), 10);
                var actual = parseInt(btn.getAttribute("data-validado"), 10);
                var iconEl = btn.querySelector("i");
                var iconClass = iconEl ? iconEl.className : "";
                btn.disabled = true;
                if (iconEl) { iconEl.className = "fa fa-spinner fa-spin"; }
                fetch("/caphum/validarDocumentoCandidato", { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded", "X-Requested-With": "XMLHttpRequest" }, body: "id=" + idDoc + "&validado=" + (actual ? 0 : 1) })
                .then(function(r){ return r.json(); }).then(function(res) {
                    btn.disabled = false;
                    if (iconEl) iconEl.className = iconClass;
                    if (res.success) {
                        var nuevoEstado = res.datos && res.datos.validado === 1;
                        var mensajeValidacion = nuevoEstado ? "Documento validado correctamente." : "Validación retirada del documento.";
                        if (typeof Swal !== "undefined") {
                            Swal.fire({
                                icon: nuevoEstado ? "success" : "info",
                                title: nuevoEstado ? "Documento validado" : "Validación retirada",
                                text: mensajeValidacion,
                                timer: 3000,
                                showConfirmButton: false
                            });
                        }
                        cargarDocumentosModal(idCandidato);
                        if (typeof getCandidatos === "function") getCandidatos();
                        if (res.datos && res.datos.todos_validados) {
                            setTimeout(function() {
                                if (typeof Swal !== "undefined") Swal.fire({
                                    icon: "success",
                                    title: "Expediente validado",
                                    text: "Todos los documentos han sido validados. El estatus del candidato cambió a Validado.",
                                    timer: 3000,
                                    showConfirmButton: false
                                });
                            }, 3500);
                        }
                    } else { if (typeof Swal !== "undefined") Swal.fire({ icon: "error", title: "Error", text: res.mensaje || "No se pudo actualizar." }); }
                }).catch(function() { btn.disabled = false; if (iconEl) iconEl.className = iconClass; if (typeof Swal !== "undefined") Swal.fire({ icon: "error", title: "Error", text: "Error de conexión." }); });
            });
        });
        lista.querySelectorAll(".btn-eliminar-doc-candidato").forEach(function(btn) {
            btn.addEventListener("click", function() {
                var idDoc = parseInt(btn.getAttribute("data-id"), 10);
                if (typeof Swal !== "undefined") { Swal.fire({ title: "¿Eliminar documento?", text: "Se quitará del expediente.", icon: "warning", showCancelButton: true, confirmButtonText: "Sí, eliminar", cancelButtonText: "Cancelar" }).then(function(r) { if (r.isConfirmed) eliminarDocYRecargarModal(idDoc, idCandidato); }); }
                else if (confirm("¿Eliminar este documento?")) eliminarDocYRecargarModal(idDoc, idCandidato);
            });
        });
        }

            function actualizarDocumentosEnMap(idCandidato, docPayload) {
                // Actualizar el Map global con los nuevos datos del candidato
                if (window.candidatosDataMap && idCandidato) {
                    window.candidatosDataMap.set(idCandidato, docPayload);
                }
            }

            function cargarDocumentosModal(idCandidato) {
                var lista = document.getElementById("modalDocumentacionCandidatoLista");
                var cargando = document.getElementById("modalDocumentacionCandidatoCargando");
                var vacio = document.getElementById("modalDocumentacionCandidatoVacio");
                var bloqueVerif = document.getElementById("modalDocumentacionCandidatoVerificacion");
                var bloqueMetricas = document.getElementById("modalDocumentacionCandidatoMetricas");
                var bloqueAccionVerificar = document.getElementById("modalDocumentacionCandidatoAccionVerificar");
                var bloqueAccionesProceso = document.getElementById("modalDocumentacionCandidatoAccionesProceso");
                fetch("/caphum/getDocumentosCandidatoList?id_candidato=" + idCandidato).then(function(r){ return r.json(); }).then(function(res) {
                    var docs = (res.datos && res.datos.documentos) ? res.datos.documentos : (res.datos && Array.isArray(res.datos) ? res.datos : []);
                    var verif = (res.datos && res.datos.verificacion_expediente) ? res.datos.verificacion_expediente : null;
                    var metricas = (res.datos && res.datos.metricas) ? res.datos.metricas : null;

                    // Actualizar el Map global con los nuevos datos
                    actualizarDocumentosEnMap(idCandidato, {
                        documentos: docs,
                        verificacion_expediente: verif,
                        metricas: metricas
                    });

                    renderListaDocumentos(lista, cargando, vacio, docs, verif, idCandidato);
                    renderMetricasDoc(bloqueMetricas, metricas);
                    renderAccionesProcesoCandidato(bloqueAccionesProceso, metricas, idCandidato);
                    if (bloqueAccionVerificar) bloqueAccionVerificar.classList.add("d-none");
                    if (verif) {
                        if (bloqueVerif) { bloqueVerif.classList.remove("d-none"); bloqueVerif.innerHTML = "<div class=\"alert alert-success small mb-0\"><i class=\"fa fa-check-circle me-1\"></i>Expediente verificado automáticamente. Los resultados se muestran en cada documento.</div>"; }
                        renderVerificacionDoc(bloqueVerif, verif);
                    } else if (metricas && metricas.expediente_completo) {
                        if (bloqueVerif) { bloqueVerif.classList.remove("d-none"); bloqueVerif.innerHTML = "<div class=\"alert alert-info small mb-0\"><i class=\"fa fa-hourglass-half me-1\"></i>La verificación automática se está procesando en segundo plano. Los resultados aparecerán aquí cuando estén listos.</div>"; }
                    }
                }).catch(function() { renderListaDocumentos(lista, cargando, vacio, [], null, idCandidato); if (bloqueAccionesProceso) { bloqueAccionesProceso.classList.add("d-none"); bloqueAccionesProceso.innerHTML = ""; } });
            }

            function abrirModalDocumentacionCandidato(idCandidato, nombreCandidato) {
                var modal = document.getElementById("modalDocumentacionCandidato");
                var label = document.getElementById("modalDocumentacionCandidatoNombre");
                var bloqueVerif = document.getElementById("modalDocumentacionCandidatoVerificacion");
                var bloqueMetricas = document.getElementById("modalDocumentacionCandidatoMetricas");
                var bloqueAccionVerificar = document.getElementById("modalDocumentacionCandidatoAccionVerificar");
                var bloqueAccionesProceso = document.getElementById("modalDocumentacionCandidatoAccionesProceso");
                var lista = document.getElementById("modalDocumentacionCandidatoLista");
                var cargando = document.getElementById("modalDocumentacionCandidatoCargando");
                var vacio = document.getElementById("modalDocumentacionCandidatoVacio");
                if (modal) modal.dataset.nombreCandidato = (nombreCandidato != null && nombreCandidato !== undefined) ? String(nombreCandidato) : "";
                if (label) label.textContent = nombreCandidato ? "Candidato: " + nombreCandidato : "";
                if (bloqueVerif) { bloqueVerif.classList.add("d-none"); bloqueVerif.innerHTML = ""; }
                if (bloqueMetricas) { bloqueMetricas.classList.add("d-none"); bloqueMetricas.innerHTML = ""; }
                if (bloqueAccionVerificar) bloqueAccionVerificar.classList.add("d-none");
                if (bloqueAccionesProceso) { bloqueAccionesProceso.classList.add("d-none"); bloqueAccionesProceso.innerHTML = ""; }
                if (lista) lista.innerHTML = "";

                // Eager Loading: Obtener datos del Map global (sin peticiones de red - INSTANTÁNEO)
                var docPayload = window.candidatosDataMap && window.candidatosDataMap.get(idCandidato) ? window.candidatosDataMap.get(idCandidato) : null;

                if (docPayload) {
                    // Datos disponibles instantáneamente desde memoria
                    if (cargando) cargando.classList.add("d-none");
                    if (vacio) vacio.classList.add("d-none");

                    var docs = docPayload.documentos || [];
                    var verif = docPayload.verificacion_expediente || null;
                    var metricas = docPayload.metricas || null;

                    renderListaDocumentos(lista, cargando, vacio, docs, verif, idCandidato);
                    renderMetricasDoc(bloqueMetricas, metricas);
                    renderAccionesProcesoCandidato(bloqueAccionesProceso, metricas, idCandidato);
                    if (bloqueAccionVerificar) bloqueAccionVerificar.classList.add("d-none");
                    if (verif) {
                        if (bloqueVerif) { bloqueVerif.classList.remove("d-none"); bloqueVerif.innerHTML = ""; }
                        renderVerificacionDoc(bloqueVerif, verif);
                    } else if (metricas && metricas.expediente_completo && bloqueVerif) {
                        bloqueVerif.classList.remove("d-none");
                        bloqueVerif.innerHTML = "<div class=\"alert alert-info small mb-0\"><i class=\"fa fa-hourglass-half me-1\"></i>Verificación en proceso.</div>";
                    }
                } else {
                    // Si no hay datos en memoria (no debería pasar con Eager Loading), mostrar vacío
                    if (cargando) cargando.classList.add("d-none");
                    if (vacio) vacio.classList.remove("d-none");
                    if (lista) lista.innerHTML = "";
                    if (bloqueAccionesProceso) { bloqueAccionesProceso.classList.add("d-none"); bloqueAccionesProceso.innerHTML = ""; }
                }

                var bsModal = modal && window.bootstrap && window.bootstrap.Modal ? new window.bootstrap.Modal(modal) : null;
                if (bsModal) bsModal.show();
            }

            function abrirModalReenviarPostulacion(idCandidato) {
        candidatoDatosEnvio = null;

        // Intentar obtener datos desde la memoria primero (eager loading)
        var candidato = null;
        if (window.candidatosData && Array.isArray(window.candidatosData)) {
            candidato = window.candidatosData.find(function(c) {
                return c.id === idCandidato || parseInt(c.id, 10) === idCandidato;
            });
        }

        if (candidato) {
            // Usar datos de memoria
            var nombreCompleto = [candidato.nombres, candidato.segundo_nombre, candidato.apellidop, candidato.apellidom].filter(Boolean).join(" ");
            var resumenEl = document.getElementById("resumenPostulacionTexto");
            if (resumenEl) {
                resumenEl.innerHTML = buildResumenCandidatoHTML({
                    nombreCompleto: nombreCompleto || "—",
                    telefono: (candidato.telefono ? "(" + candidato.telefono + ")" : "—"),
                    email: candidato.email || "—",
                    puesto: candidato.nombre_puesto || "—",
                    departamento: candidato.nombre_departamento || "—"
                });
            }
            var btnEnviar = document.getElementById("btnEnviarPostulacion");
            if (btnEnviar) {
                btnEnviar.disabled = false;
                btnEnviar.innerHTML = "<i class='bx bx-send me-2'></i> Reenviar postulación por correo";
            }
            candidatoReenviarId = candidato.id || idCandidato;
            candidatoReenviarEmail = candidato.email || "";
            var modalEl = document.getElementById("modalResumenPostulacion");
            if (modalEl) {
                var modal = new bootstrap.Modal(modalEl);
                modal.show();
            }
            cargarLinkDocumentosCandidato(candidato.id || idCandidato);
        } else {
            // Fallback: obtener desde el servidor
            fetch("/caphum/getCandidato/" + idCandidato)
                .then(function(r) {
                    if (!r.ok) {
                        throw new Error("Error HTTP: " + r.status);
                    }
                    return r.json();
                })
                .then(function(res) {
                    if (!res || !res.success || !res.datos) {
                        if (typeof Swal !== "undefined") {
                            Swal.fire({ icon: "error", title: "Error", text: "No se encontró el candidato." });
                        }
                        return;
                    }
                    var c = res.datos;
                    var nombreCompleto = [c.nombres, c.segundo_nombre, c.apellidop, c.apellidom].filter(Boolean).join(" ");
                    var resumenEl = document.getElementById("resumenPostulacionTexto");
                    if (resumenEl) {
                        resumenEl.innerHTML = buildResumenCandidatoHTML({
                            nombreCompleto: nombreCompleto || "—",
                            telefono: (c.telefono ? "(" + c.telefono + ")" : "—"),
                            email: c.email || "—",
                            puesto: c.nombre_puesto || "—",
                            departamento: c.nombre_departamento || "—"
                        });
                    }
                    var btnEnviar = document.getElementById("btnEnviarPostulacion");
                    if (btnEnviar) {
                        btnEnviar.disabled = false;
                        btnEnviar.innerHTML = "<i class='bx bx-send me-2'></i> Reenviar postulación por correo";
                    }
                    candidatoReenviarId = c.id;
                    candidatoReenviarEmail = c.email || "";
                    var modalEl = document.getElementById("modalResumenPostulacion");
                    if (modalEl) {
                        var modal = new bootstrap.Modal(modalEl);
                        modal.show();
                    }
                    cargarLinkDocumentosCandidato(c.id);
                })
                .catch(function(err) {
                    console.error("Error al cargar candidato:", err);
                    if (typeof Swal !== "undefined") {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "No se pudo cargar el candidato. " + (err.message || "Error de conexión.")
                        });
                    }
                });
        }
        }

            function buildResumenCandidatoHTML(o) {
        var n = o.nombreCompleto || "—"; var t = o.telefono || "—"; var e = o.email || "—"; var p = o.puesto || "—"; var d = o.departamento || "—";
        return "<div class=\"resumen-row\"><span class=\"resumen-label\">Candidato</span><span class=\"resumen-value\">" + escapeHtml(n) + "</span></div>" +
            "<div class=\"resumen-row\"><span class=\"resumen-label\">Teléfono</span><span class=\"resumen-value\">" + escapeHtml(t) + "</span></div>" +
            "<div class=\"resumen-row\"><span class=\"resumen-label\">Correo</span><span class=\"resumen-value\">" + escapeHtml(e) + "</span></div>" +
            "<div class=\"resumen-row\"><span class=\"resumen-label\">Puesto</span><span class=\"resumen-value\">" + escapeHtml(p) + "</span></div>" +
            "<div class=\"resumen-row\"><span class=\"resumen-label\">Departamento</span><span class=\"resumen-value\">" + escapeHtml(d) + "</span></div>";
        }

            function escapeHtml(s) {
        if (!s) return ""; var div = document.createElement("div"); div.textContent = s; return div.innerHTML;
        }

            function cargarLinkDocumentosCandidato(idCandidato) {
        if (!idCandidato) return;
        var bloque = document.getElementById("bloqueLinkDocumentos");
        var input = document.getElementById("inputUrlDocumentos");
        if (!bloque || !input) return;
        fetch("/caphum/getTokenDocumentosCandidato", { method: "POST", headers: { "Content-Type": "application/json", "Accept": "application/json" }, body: JSON.stringify({ id: idCandidato }) })
            .then(function(r){ return r.json(); }).then(function(res){ if (res.success && res.datos && res.datos.url) { input.value = res.datos.url; input.setAttribute("title", res.datos.url); bloque.style.display = "block"; } }).catch(function(){});
        }

            function showToastUrl(msg) {
        var t = document.getElementById("toastUrlDocumentos");
        if (!t) return;
        t.textContent = msg; t.classList.add("show");
        setTimeout(function() { t.classList.remove("show"); }, 2200);
        }

            function initCopiarUrlDocumentos() {
        var btn = document.getElementById("btnCopiarUrlDocumentos");
        var input = document.getElementById("inputUrlDocumentos");
        if (!btn || !input) return;
        if (btn._copiarBound) return;
        btn._copiarBound = true;
        btn.addEventListener("click", function() {
            var url = input.value;
            if (!url) { showToastUrl("⚠ Ingresa una URL primero"); return; }
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(function() { showToastUrl("✔  URL copiada al portapapeles"); }).catch(function() { input.select(); input.setSelectionRange(0, 99999); try { document.execCommand("copy"); showToastUrl("✔  URL copiada al portapapeles"); } catch (e) { if (typeof Swal !== "undefined") Swal.fire({ icon: "success", title: "Copiado", text: "URL copiada.", timer: 1500, showConfirmButton: false }); } });
            } else { input.select(); input.setSelectionRange(0, 99999); try { document.execCommand("copy"); showToastUrl("✔  URL copiada al portapapeles"); } catch (e) { if (typeof Swal !== "undefined") Swal.fire({ icon: "success", title: "Copiado", text: "URL copiada.", timer: 1500, showConfirmButton: false }); } }
        });
        var btnAbrir = document.getElementById("btnAbrirUrlDocumentos");
        if (btnAbrir && !btnAbrir._abrirBound) {
            btnAbrir._abrirBound = true;
            btnAbrir.addEventListener("click", function() { var url = input.value; if (!url) return; if (!/^https?:\/\//i.test(url)) url = "https://" + url; window.open(url, "_blank", "noopener,noreferrer"); });
        }
        }

            function editarCandidato(id) {
        candidatoEditId = id;
        var titulo = document.getElementById("offcanvasCandidatoTitulo");
        if (titulo) titulo.textContent = "Editar Candidato";
        var btnSubmit = document.getElementById("btnSubmitCandidato");
        if (btnSubmit) { btnSubmit.innerHTML = "<i class=\"bx bx-edit-alt me-1\"></i> Actualizar"; btnSubmit.className = "btn btn-success me-2"; }
        fetch("/caphum/getCandidato/" + id).then(function(r){ return r.json(); }).then(function(res){
            if (!res.success || !res.datos) { if (typeof Swal !== "undefined") Swal.fire({ icon: "error", title: "Error", text: "No se encontró el candidato." }); return; }
            var c = res.datos;
            var form = document.getElementById("formAgregarCandidato");
            if (!form) return;
            if (form.nombres) form.nombres.value = c.nombres || "";
            if (form.segundo_nombre) form.segundo_nombre.value = c.segundo_nombre || "";
            if (form.apellidop) form.apellidop.value = c.apellidop || "";
            if (form.apellidom) form.apellidom.value = c.apellidom || "";
            if (form.telefono) form.telefono.value = c.telefono || "";
            if (form.email) form.email.value = c.email || "";
            if (form.id_pais) form.id_pais.value = c.id_pais || "";
            if (form.id_departamento) form.id_departamento.value = c.id_departamento || "";
            if (form.usuario) form.usuario.value = c.usuario || "";
            if (form.contrasena) form.contrasena.value = c.contrasena || "";
            var fpInput = document.getElementById("candidato_fecha_postulacion");
            if (fpInput && c.fecha_postulacion) fpInput.value = c.fecha_postulacion;
            var chkLegion = document.getElementById("candidato_asignar_legion");
            var divLegion = document.getElementById("div_candidato_legion");
            var selLegion = document.getElementById("candidato_id_legion");
            if (c.id_legion) { if (chkLegion) chkLegion.checked = true; if (divLegion) divLegion.style.display = "block"; if (selLegion) selLegion.value = c.id_legion; }
            else { if (chkLegion) chkLegion.checked = false; if (divLegion) divLegion.style.display = "none"; if (selLegion) selLegion.value = ""; }
            var selPuesto = document.getElementById("candidato_id_puesto");
            var selJefe = document.getElementById("candidato_id_posible_jefe");
            selPuesto.innerHTML = "<option value=''>Seleccione puesto</option>";
            selJefe.innerHTML = "<option value=''>—</option>";
            setTimeout(function() { abrirOffcanvasCandidato(); }, 0);
            if (!c.id_departamento) { selJefe.innerHTML = "<option value=''>Seleccione departamento y puesto primero</option>"; return; }
            fetch("/caphum/getPuestos", { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ id_departamento: c.id_departamento }) })
            .then(function(r){ return r.json(); })
            .then(function(rPuestos){
                if (rPuestos.success && rPuestos.datos) rPuestos.datos.forEach(function(p){ var opt = document.createElement("option"); opt.value = p.id; opt.textContent = p.nombre || ""; selPuesto.appendChild(opt); });
                selPuesto.value = c.id_puesto || "";
                return fetch("/caphum/getJefeDirecto", { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ id_departamento: c.id_departamento, id_puesto: c.id_puesto || null }) });
            })
            .then(function(r){ return r.json(); })
            .then(function(rJefes){
                if (!selJefe) return;
                selJefe.innerHTML = "<option value=''>Seleccione posible jefe</option>";
                if (rJefes && rJefes.success && rJefes.datos) rJefes.datos.forEach(function(j){ var opt = document.createElement("option"); opt.value = j.id; opt.textContent = (j.nombre_completo || "").trim() || "ID " + j.id; selJefe.appendChild(opt); });
                selJefe.value = c.id_posible_jefe || "";
            })
            .catch(function(){ if (selJefe) selJefe.innerHTML = "<option value=''>Seleccione posible jefe</option>"; });
        }).catch(function(){ if (typeof Swal !== "undefined") Swal.fire({ icon: "error", title: "Error", text: "No se pudo cargar el candidato." }); });
        }

            function abrirOffcanvasCandidato() {
        var el = document.getElementById("offcanvasAddCandidato");
        if (!el) return;
        if (el.parentNode !== document.body) document.body.appendChild(el);
        if (typeof bootstrap !== "undefined" && bootstrap.Offcanvas) { var inst = bootstrap.Offcanvas.getOrCreateInstance(el); if (inst) inst.show(); }
        else { el.classList.add("show"); el.setAttribute("aria-hidden", "false"); var back = document.createElement("div"); back.className = "offcanvas-backdrop fade show"; back.style.cssText = "position:fixed;top:0;left:0;z-index:1040;width:100vw;height:100vh;background:#000;opacity:0.5;"; back.setAttribute("data-bs-dismiss", "offcanvas"); document.body.appendChild(back); }
        }

            function guardarCandidatoEdicion() {
        var form = document.getElementById("formAgregarCandidato");
        if (!form || !form.checkValidity()) { form.reportValidity(); return; }
        var id = candidatoEditId; if (!id) return;
        var data = buildCandidatoPayloadFromForm(); data.id = id;
        fetch("/caphum/actualizarCandidato", { method: "POST", headers: { "Content-Type": "application/json", "Accept": "application/json" }, body: JSON.stringify(data) })
        .then(function(r){ return r.json(); }).then(function(res){
            if (res.success) {
                if (typeof Swal !== "undefined") Swal.fire({ icon: "success", title: "Listo", text: "Candidato actualizado correctamente." });
                candidatoEditId = null;
                document.getElementById("offcanvasCandidatoTitulo").textContent = "Nuevo Candidato";
                var btnSubmit = document.getElementById("btnSubmitCandidato");
                if (btnSubmit) { btnSubmit.innerHTML = "<i class=\"bx bx-save me-1\"></i> Guardar"; btnSubmit.className = "btn btn-primary me-2"; }
                form.reset();
                var fpInput = document.getElementById("candidato_fecha_postulacion");
                if (fpInput && fpInput._flatpickr) fpInput._flatpickr.setDate(new Date(), true);
                var inst = bootstrap.Offcanvas.getInstance(document.getElementById("offcanvasAddCandidato"));
                if (inst) inst.hide();
                getCandidatos();
            } else { if (typeof Swal !== "undefined") Swal.fire({ icon: "error", title: "Error", text: res.mensaje || "No se pudo actualizar." }); }
        }).catch(function(){ if (typeof Swal !== "undefined") Swal.fire({ icon: "error", title: "Error", text: "Error de conexión." }); });
        }

            function guardarCandidatoAbrirResumen() {
        var form = document.getElementById("formAgregarCandidato");
        if (!form || !form.checkValidity()) { form.reportValidity(); return; }
        var data = buildCandidatoPayloadFromForm();
        if (!data.nombres || !data.apellidop) { if (typeof Swal !== "undefined") Swal.fire({ icon: "warning", title: "Faltan datos", text: "Nombre y apellido paterno son obligatorios." }); return; }
        var btnSubmit = document.getElementById("btnSubmitCandidato");
        if (btnSubmit) { btnSubmit.disabled = true; }
        fetch("/caphum/guardarCandidato", { method: "POST", headers: { "Content-Type": "application/json", "Accept": "application/json" }, body: JSON.stringify(data) })
        .then(function(r){ return r.json(); }).then(function(res){
            if (btnSubmit) { btnSubmit.disabled = false; btnSubmit.innerHTML = "<i class=\"bx bx-save me-1\"></i> Guardar"; }
            if (!res.success) { if (typeof Swal !== "undefined") Swal.fire({ icon: "error", title: "Error", text: res.mensaje || res.error || "No se pudo guardar." }); return; }
            var idCand = res.datos && res.datos.id; if (!idCand) return;
            candidatoNuevoId = idCand; candidatoNuevoEmail = data.email || ""; candidatoDatosEnvio = null; candidatoReenviarId = null; candidatoReenviarEmail = null;
            var nombreCompleto = [data.nombres, data.segundo_nombre, data.apellidop, data.apellidom].filter(Boolean).join(" ");
            var puestoTexto = (document.getElementById("candidato_id_puesto") && document.getElementById("candidato_id_puesto").selectedIndex >= 0) ? document.getElementById("candidato_id_puesto").options[document.getElementById("candidato_id_puesto").selectedIndex].text : "—";
            var deptoTexto = (document.getElementById("candidato_id_departamento") && document.getElementById("candidato_id_departamento").selectedIndex >= 0) ? document.getElementById("candidato_id_departamento").options[document.getElementById("candidato_id_departamento").selectedIndex].text : "—";
            document.getElementById("resumenPostulacionTexto").innerHTML = buildResumenCandidatoHTML({ nombreCompleto: nombreCompleto || "—", telefono: (data.telefono ? "(" + data.telefono + ")" : "—"), email: data.email || "—", puesto: puestoTexto, departamento: deptoTexto });
            document.getElementById("btnEnviarPostulacion").disabled = false;
            document.getElementById("btnEnviarPostulacion").innerHTML = "<i class='bx bx-send me-2'></i> Enviar postulación al candidato";
            var bloqueLink = document.getElementById("bloqueLinkDocumentos"); var inputUrl = document.getElementById("inputUrlDocumentos");
            if (bloqueLink) bloqueLink.style.display = "none"; if (inputUrl) inputUrl.value = "";
            cargarLinkDocumentosCandidato(idCand);
            var offcanvas = document.getElementById("offcanvasAddCandidato");
            if (offcanvas && typeof bootstrap !== "undefined") bootstrap.Offcanvas.getInstance(offcanvas).hide();
            var modal = new bootstrap.Modal(document.getElementById("modalResumenPostulacion")); modal.show();
            if (form) form.reset();
            var fpInput = document.getElementById("candidato_fecha_postulacion");
            if (fpInput && fpInput._flatpickr) fpInput._flatpickr.setDate(new Date(), true);
            getCandidatos();
        }).catch(function(){ if (typeof Swal !== "undefined") Swal.close(); if (btnSubmit) { btnSubmit.disabled = false; btnSubmit.innerHTML = "<i class=\"bx bx-save me-1\"></i> Guardar"; } if (typeof Swal !== "undefined") Swal.fire({ icon: "error", title: "Error", text: "Error de conexión." }); });
        }

            function buildCandidatoPayloadFromForm() {
        var form = document.getElementById("formAgregarCandidato"); if (!form) return {};
        return {
            nombres: (form.nombres && form.nombres.value.trim()) || "",
            segundo_nombre: (form.segundo_nombre && form.segundo_nombre.value.trim()) || "",
            apellidop: (form.apellidop && form.apellidop.value.trim()) || "",
            apellidom: (form.apellidom && form.apellidom.value.trim()) || "",
            email: (form.email && form.email.value.trim()) || "",
            telefono: (form.telefono && form.telefono.value.trim()) || "",
            id_pais: (form.id_pais && form.id_pais.value) || null,
            id_departamento: (form.id_departamento && form.id_departamento.value) || null,
            id_puesto: (form.id_puesto && form.id_puesto.value) || null,
            id_posible_jefe: (form.id_posible_jefe && form.id_posible_jefe.value) || null,
            fecha_postulacion: (form.fecha_postulacion && form.fecha_postulacion.value) || null,
            id_legion: document.getElementById("candidato_asignar_legion") && document.getElementById("candidato_asignar_legion").checked && document.getElementById("candidato_id_legion") && document.getElementById("candidato_id_legion").value ? document.getElementById("candidato_id_legion").value : null,
            usuario: (form.usuario && form.usuario.value.trim()) || "",
            contrasena: (form.contrasena && form.contrasena.value.trim()) || "",
            estatus: "Por evaluar", notas: null, postulacion_enviada: 1
        };
        }

            function enviarPostulacionAlCandidato() {
        var btn = document.getElementById("btnEnviarPostulacion");
        if (btn.disabled) return;
        btn.disabled = true; btn.innerHTML = "<i class='bx bx-loader-alt bx-spin me-2'></i> Enviando...";
        if (candidatoReenviarId) {
            var urlReenviar = "/caphum/enviarPostulacionCandidato";
            fetch(urlReenviar, { method: "POST", headers: { "Content-Type": "application/json", "Accept": "application/json" }, body: JSON.stringify({ id: candidatoReenviarId, email: candidatoReenviarEmail || "" }) })
            .then(function(r){ return r.text().then(function(text) { var res; try { res = text ? JSON.parse(text) : {}; } catch (e) { res = null; } return { ok: r.ok, status: r.status, res: res, raw: text }; }); })
            .then(function(o){
                var res = o.res; candidatoReenviarId = null; candidatoReenviarEmail = null; btn.disabled = false;
                btn.innerHTML = "<i class='bx bx-send me-2'></i> Reenviar postulación por correo";
                if (!res && !o.ok) { if (typeof Swal !== "undefined") Swal.fire({ icon: "error", title: "Error", text: "El servidor respondió con " + o.status + ". Compruebe la consola (F12) o que la URL sea correcta." }); return; }
                if (res && res.success) { if (typeof Swal !== "undefined") Swal.fire({ icon: "success", title: "Listo", text: "Correo de postulación reenviado correctamente." }); getCandidatos(); setTimeout(function() { bootstrap.Modal.getInstance(document.getElementById("modalResumenPostulacion")).hide(); }, 1500); }
                else { if (typeof Swal !== "undefined") Swal.fire({ icon: "error", title: "Error", text: (res && res.mensaje) ? res.mensaje : "No se pudo enviar el correo." }); }
            }).catch(function(err){ btn.disabled = false; btn.innerHTML = "<i class='bx bx-send me-2'></i> Reenviar postulación por correo"; if (typeof Swal !== "undefined") Swal.fire({ icon: "error", title: "Error", text: (err && err.message) ? err.message : "Error de conexión." }); });
            return;
        }
        if (candidatoNuevoId) {
            var idCand = candidatoNuevoId; var email = candidatoNuevoEmail || "";
            fetch("/caphum/enviarPostulacionCandidato", { method: "POST", headers: { "Content-Type": "application/json", "Accept": "application/json" }, body: JSON.stringify({ id: idCand, email: email }) })
            .then(function(r){ return r.json(); }).then(function(resMail){
                candidatoNuevoId = null; candidatoNuevoEmail = null; btn.disabled = false;
                if (resMail && resMail.success) { btn.innerHTML = "<i class=\"bx bx-check me-2\"></i> Enviada postulación"; if (typeof Swal !== "undefined") Swal.fire({ icon: "success", title: "Listo", text: "Correo enviado. El enlace para subir documentos está en el correo y arriba." }); }
                else { btn.innerHTML = "<i class='bx bx-send me-2'></i> Enviar postulación al candidato"; if (typeof Swal !== "undefined") Swal.fire({ icon: "warning", title: "Correo no enviado", text: (resMail && resMail.mensaje) ? resMail.mensaje : "Configure [mail] en backend/config/config.ini para SMTP. Use el enlace de arriba para compartir con el candidato." }); }
                getCandidatos(); setTimeout(function() { bootstrap.Modal.getInstance(document.getElementById("modalResumenPostulacion")).hide(); }, 2500);
            }).catch(function(){ btn.disabled = false; btn.innerHTML = "<i class='bx bx-send me-2'></i> Enviar postulación al candidato"; if (typeof Swal !== "undefined") Swal.fire({ icon: "warning", title: "Error", text: "El correo no se pudo enviar. Use el enlace de arriba para compartir con el candidato." }); });
            return;
        }
        var data = candidatoDatosEnvio || buildCandidatoPayloadFromForm();
        if (!data.nombres || !data.apellidop) { btn.disabled = false; btn.innerHTML = "<i class='bx bx-send me-2'></i> Enviar postulación al candidato"; if (typeof Swal !== "undefined") Swal.fire({ icon: "error", title: "Error", text: "Nombre y apellido paterno son obligatorios." }); return; }
        var form = document.getElementById("formAgregarCandidato");
        fetch("/caphum/guardarCandidato", { method: "POST", headers: { "Content-Type": "application/json", "Accept": "application/json" }, body: JSON.stringify(data) })
        .then(function(r){ return r.json(); }).then(function(res){
            if (res.success) {
                candidatoDatosEnvio = null; var idCand = res.datos && res.datos.id; cargarLinkDocumentosCandidato(idCand);
                fetch("/caphum/enviarPostulacionCandidato", { method: "POST", headers: { "Content-Type": "application/json", "Accept": "application/json" }, body: JSON.stringify({ id: idCand, email: data.email }) })
                .then(function(r2){ return r2.json(); }).then(function(resMail){
                    btn.disabled = false; btn.innerHTML = "<i class=\"bx bx-send me-2\"></i> Enviar postulación al candidato";
                    if (resMail && resMail.success) { btn.innerHTML = "<i class=\"bx bx-check me-2\"></i> Enviada postulación"; if (typeof Swal !== "undefined") Swal.fire({ icon: "success", title: "Listo", text: "Candidato registrado y correo enviado. El enlace para subir documentos está en el correo y arriba." }); }
                    else { if (typeof Swal !== "undefined") Swal.fire({ icon: "warning", title: "Candidato guardado", text: (resMail && resMail.mensaje) ? "El correo no se envió: " + resMail.mensaje + ". Configure [mail] en backend/config/config.ini para SMTP o revise que mail() funcione." : "El correo no se pudo enviar. Use el enlace de arriba para compartir con el candidato." }); }
                    getCandidatos(); setTimeout(function() { bootstrap.Modal.getInstance(document.getElementById("modalResumenPostulacion")).hide(); }, 2500);
                }).catch(function(){ btn.disabled = false; btn.innerHTML = "<i class=\"bx bx-send me-2\"></i> Enviar postulación al candidato"; if (typeof Swal !== "undefined") Swal.fire({ icon: "warning", title: "Candidato guardado", text: "El correo no se pudo enviar (error de conexión). Use el enlace de arriba para compartir con el candidato." }); getCandidatos(); });
                if (form) form.reset(); var fpInput = document.getElementById("candidato_fecha_postulacion"); if (fpInput && fpInput._flatpickr) fpInput._flatpickr.setDate(new Date(), true);
            } else { btn.disabled = false; btn.innerHTML = "<i class='bx bx-send me-2'></i> Enviar postulación al candidato"; if (typeof Swal !== "undefined") Swal.fire({ icon: "error", title: "Error", text: res.mensaje || res.error || "No se pudo guardar." }); }
        }).catch(function(){ btn.disabled = false; btn.innerHTML = "<i class='bx bx-send me-2'></i> Enviar postulación al candidato"; if (typeof Swal !== "undefined") Swal.fire({ icon: "error", title: "Error", text: "Error de conexión." }); });
        }

            function eliminarCandidato(id) {
        if (typeof Swal === "undefined") { if (confirm("¿Eliminar candidato?")) fetch("/caphum/eliminarCandidato", { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ id: id }) }).then(function(r){ return r.json(); }).then(function(d){ if (d.success) getCandidatos(); }); return; }
        Swal.fire({ title: "¿Eliminar?", text: "Se eliminará el candidato.", icon: "warning", showCancelButton: true }).then(function(r){ if (r.isConfirmed) fetch("/caphum/eliminarCandidato", { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ id: id }) }).then(function(res){ return res.json(); }).then(function(d){ if (d.success) { Swal.fire({ icon: "success", text: d.mensaje }); getCandidatos(); } else Swal.fire({ icon: "error", text: d.mensaje || d.error }); }); });
        }


        $(document).ready(function() {
        configuraTabla("#tablaCandidatos", {
            registrosPorPagina: 10,
            columns: [
                { data: null, defaultContent: '', className: 'control', orderable: false },
                { data: 'nombre', title: 'Nombre' },
                { data: 'contacto', title: 'Contacto' },
                { data: 'puestoDepto', title: 'Puesto / Departamento' },
                { data: 'estatus', title: 'Estatus', render: function(d) { return d != null ? d : ''; } },
                { data: 'acciones', title: 'Acciones', orderable: false }
            ]
        });
        getCandidatos();
        var selDepto = document.getElementById("UserRole");
        var selPuesto = document.getElementById("UserPlan");
        var selEstatus = document.getElementById("FilterTransaction");
        if (selDepto) selDepto.addEventListener("change", function() { getCandidatos(); });
        if (selPuesto) selPuesto.addEventListener("change", function() { getCandidatos(); });
        if (selEstatus) selEstatus.addEventListener("change", function() { getCandidatos(); });
        var form = document.getElementById("formAgregarCandidato");
        if (form) { form.addEventListener("submit", function(e) { e.preventDefault(); if (candidatoEditId) guardarCandidatoEdicion(); else guardarCandidatoAbrirResumen(); }); }
        document.addEventListener("click", candidatosTableClick);
        document.addEventListener("click", function(e) {
            var btn = e.target.closest(".btn-cerrar-proceso-candidato");
            if (btn) { e.preventDefault(); e.stopPropagation(); var id = btn.getAttribute("data-id"); if (id) abrirModalCerrarProceso(parseInt(id, 10)); }
            btn = e.target.closest(".btn-continuar-proceso-candidato");
            if (btn) { e.preventDefault(); e.stopPropagation(); var id = btn.getAttribute("data-id"); if (id) ejecutarContinuarProceso(parseInt(id, 10)); }
        });
        function abrirModalCerrarProceso(idCandidato) {
            var hid = document.getElementById("cerrarProcesoIdCandidato");
            var sel = document.getElementById("cerrarProcesoMotivo");
            var ta = document.getElementById("cerrarProcesoDescripcion");
            if (hid) hid.value = idCandidato || "";
            if (sel) sel.value = "";
            if (ta) ta.value = "";
            var modalEl = document.getElementById("modalCerrarProcesoCandidato");
            if (modalEl && window.bootstrap && window.bootstrap.Modal) {
                var isDark = document.body.classList.contains("dark-mode");
                var opts = isDark ? { backdrop: false } : {};
                var m = new window.bootstrap.Modal(modalEl, opts);
                m.show();
            }
        }
        (function() {
            var modalCerrar = document.getElementById("modalCerrarProcesoCandidato");
            if (!modalCerrar) return;
            modalCerrar.addEventListener("shown.bs.modal", function() {
                if (document.body.classList.contains("dark-mode")) return;
                var backdrops = document.querySelectorAll(".modal-backdrop");
                var zScrim = "1094";
                for (var i = 0; i < backdrops.length; i++) {
                    if (i === backdrops.length - 1) {
                        backdrops[i].style.setProperty("z-index", zScrim, "important");
                    } else {
                        backdrops[i].style.setProperty("z-index", "1089", "important");
                    }
                }
                modalCerrar.style.setProperty("z-index", "10050", "important");
            });
        })();
        var btnConfirmarCerrar = document.getElementById("btnConfirmarCerrarProceso");
        if (btnConfirmarCerrar) {
            btnConfirmarCerrar.addEventListener("click", function() {
                var idInput = document.getElementById("cerrarProcesoIdCandidato");
                var idCandidato = idInput ? parseInt(idInput.value, 10) : 0;
                var motivoSelect = document.getElementById("cerrarProcesoMotivo");
                var motivo = motivoSelect ? (motivoSelect.value || "").trim() : "";
                var descInput = document.getElementById("cerrarProcesoDescripcion");
                var descripcion = descInput ? (descInput.value || "").trim() : null;
                if (idCandidato <= 0) { if (typeof Swal !== "undefined") Swal.fire({ icon: "error", title: "Error", text: "ID de candidato no válido." }); return; }
                if (!motivo) { if (typeof Swal !== "undefined") Swal.fire({ icon: "warning", title: "Requerido", text: "Selecciona un motivo para el cierre." }); return; }
                btnConfirmarCerrar.disabled = true;
                fetch("/caphum/cerrarProcesoCandidato", { method: "POST", headers: { "Content-Type": "application/json", "Accept": "application/json" }, body: JSON.stringify({ id_candidato: idCandidato, motivo: motivo, descripcion: descripcion || null }) })
                    .then(function(r){ return r.json(); })
                    .then(function(res) {
                        btnConfirmarCerrar.disabled = false;
                        if (res.success) {
                            var modalCerrar = document.getElementById("modalCerrarProcesoCandidato");
                            var modalDoc = document.getElementById("modalDocumentacionCandidato");
                            if (modalCerrar && window.bootstrap && window.bootstrap.Modal) { var inst = window.bootstrap.Modal.getInstance(modalCerrar); if (inst) inst.hide(); }
                            if (modalDoc && window.bootstrap && window.bootstrap.Modal) { var instDoc = window.bootstrap.Modal.getInstance(modalDoc); if (instDoc) instDoc.hide(); }
                            if (typeof getCandidatos === "function") getCandidatos();
                            if (typeof Swal !== "undefined") Swal.fire({ icon: "success", title: "Listo", text: res.mensaje || "Proceso cerrado correctamente." });
                        } else {
                            if (typeof Swal !== "undefined") Swal.fire({ icon: "error", title: "Error", text: res.mensaje || "No se pudo cerrar el proceso." });
                        }
                    })
                    .catch(function() { btnConfirmarCerrar.disabled = false; if (typeof Swal !== "undefined") Swal.fire({ icon: "error", title: "Error", text: "Error de conexión." }); });
            });
        }
        function ejecutarContinuarProceso(idCandidato) {
            if (typeof Swal === "undefined") {
                if (confirm("¿Ya RRHH le dio de alta a la nómina del candidato?")) {
                    fetch("/caphum/pasarCandidatoAGestion", { method: "POST", headers: { "Content-Type": "application/json", "Accept": "application/json" }, body: JSON.stringify({ id_candidato: idCandidato }) })
                        .then(function(r){ return r.json(); })
                        .then(function(res) {
                            if (res.success) {
                                var modalDoc = document.getElementById("modalDocumentacionCandidato");
                                if (modalDoc && window.bootstrap && window.bootstrap.Modal) { var instDoc = window.bootstrap.Modal.getInstance(modalDoc); if (instDoc) instDoc.hide(); }
                                if (typeof getCandidatos === "function") getCandidatos();
                                alert("Listo. Se envió el correo de bienvenida al candidato con el enlace al Onboarding.");
                            } else alert(res.mensaje || "Error al dar de alta.");
                        });
                } else {
                    alert("Para continuar el proceso el candidato debe tener alta en nómina por RRHH. Cuando ya le hayan dado de alta, vuelve a hacer clic en Continuar proceso.");
                }
                return;
            }
            Swal.fire({
                title: "¿Ya RRHH le dio de alta a la nómina del candidato?",
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Sí",
                cancelButtonText: "No",
                confirmButtonColor: "#198754",
                cancelButtonColor: "#6c757d"
            }).then(function(result) {
                if (result.isConfirmed) {
                    Swal.fire({ title: "Procesando...", text: "Dando de alta en Gestión y enviando correo de bienvenida.", allowOutsideClick: false, allowEscapeKey: false, didOpen: function() { Swal.showLoading(); } });
                    fetch("/caphum/pasarCandidatoAGestion", { method: "POST", headers: { "Content-Type": "application/json", "Accept": "application/json" }, body: JSON.stringify({ id_candidato: idCandidato }) })
                        .then(function(r){ return r.json(); })
                        .then(function(res) {
                            Swal.close();
                            if (res.success) {
                                var modalDoc = document.getElementById("modalDocumentacionCandidato");
                                if (modalDoc && window.bootstrap && window.bootstrap.Modal) { var instDoc = window.bootstrap.Modal.getInstance(modalDoc); if (instDoc) instDoc.hide(); }
                                if (typeof getCandidatos === "function") getCandidatos();
                                Swal.fire({ icon: "success", title: "¡Listo!", text: "Bienvenido a MaxiKash. Se envió el correo al candidato con el enlace para entrar al Onboarding. El colaborador ya está dado de alta en Gestión." });
                            } else {
                                Swal.fire({ icon: "error", title: "Error", text: res.mensaje || "No se pudo dar de alta en Gestión." });
                            }
                        })
                        .catch(function() {
                            Swal.close();
                            Swal.fire({ icon: "error", title: "Error", text: "Error de conexión." });
                        });
                } else {
                    Swal.fire({
                        icon: "info",
                        title: "Alta en nómina requerida",
                        text: "Para continuar el proceso el candidato debe tener alta en nómina por RRHH. Cuando ya le hayan dado de alta, vuelve a hacer clic en Continuar proceso."
                    });
                }
            });
        }
        var btnAltaNominaNo = document.getElementById("btnConfirmarAltaNominaNo");
        if (btnAltaNominaNo) {
            btnAltaNominaNo.addEventListener("click", function() {
                var modalAlta = document.getElementById("modalConfirmarAltaNomina");
                if (modalAlta && window.bootstrap && window.bootstrap.Modal) { var inst = window.bootstrap.Modal.getInstance(modalAlta); if (inst) inst.hide(); }
                if (typeof Swal !== "undefined") Swal.fire({ icon: "info", title: "Alta en nómina", text: "No puedes continuar hasta que RRHH dé de alta al candidato en nómina." });
            });
        }
        var btnAltaNominaSi = document.getElementById("btnConfirmarAltaNominaSi");
        if (btnAltaNominaSi) {
            btnAltaNominaSi.addEventListener("click", function() {
                var idInput = document.getElementById("confirmarAltaNominaIdCandidato");
                var idCandidato = idInput ? parseInt(idInput.value, 10) : 0;
                if (idCandidato <= 0) { if (typeof Swal !== "undefined") Swal.fire({ icon: "error", title: "Error", text: "ID de candidato no válido." }); return; }
                btnAltaNominaSi.disabled = true;
                fetch("/caphum/pasarCandidatoAGestion", { method: "POST", headers: { "Content-Type": "application/json", "Accept": "application/json" }, body: JSON.stringify({ id_candidato: idCandidato }) })
                    .then(function(r){ return r.json(); })
                    .then(function(res) {
                        btnAltaNominaSi.disabled = false;
                        var modalAlta = document.getElementById("modalConfirmarAltaNomina");
                        if (modalAlta && window.bootstrap && window.bootstrap.Modal) { var inst = window.bootstrap.Modal.getInstance(modalAlta); if (inst) inst.hide(); }
                        if (res.success) {
                            if (typeof getCandidatos === "function") getCandidatos();
                            if (typeof Swal !== "undefined") Swal.fire({ icon: "success", title: "¡Listo!", text: "Bienvenido a Maxikash. El colaborador ha sido dado de alta en Gestión y se le envió el correo de bienvenida. Ya tiene acceso al menú de Onboarding." });
                        } else {
                            if (typeof Swal !== "undefined") Swal.fire({ icon: "error", title: "Error", text: res.mensaje || "No se pudo dar de alta en Gestión." });
                        }
                    })
                    .catch(function() { btnAltaNominaSi.disabled = false; if (typeof Swal !== "undefined") Swal.fire({ icon: "error", title: "Error", text: "Error de conexión." }); });
            });
        }
        var offcanvasEl = document.getElementById("offcanvasAddCandidato");
        if (offcanvasEl) {
            offcanvasEl.addEventListener("show.bs.offcanvas", function() { var btnSubmit = document.getElementById("btnSubmitCandidato"); if (!btnSubmit) return; if (candidatoEditId) { btnSubmit.innerHTML = "<i class=\"bx bx-edit-alt me-1\"></i> Actualizar"; btnSubmit.className = "btn btn-success me-2"; } else { btnSubmit.innerHTML = "<i class=\"bx bx-save me-1\"></i> Guardar"; btnSubmit.className = "btn btn-primary me-2"; } });
            offcanvasEl.addEventListener("hidden.bs.offcanvas", function() {
                var form = document.getElementById("formAgregarCandidato"); if (form) { form.reset(); candidatoEditId = null; }
                var titulo = document.getElementById("offcanvasCandidatoTitulo"); if (titulo) titulo.textContent = "Nuevo Candidato";
                var btnSubmit = document.getElementById("btnSubmitCandidato"); if (btnSubmit) { btnSubmit.innerHTML = "<i class=\"bx bx-save me-1\"></i> Guardar"; btnSubmit.className = "btn btn-primary me-2"; }
                var fpInput = document.getElementById("candidato_fecha_postulacion"); if (fpInput && fpInput._flatpickr) fpInput._flatpickr.setDate(new Date(), true);
                var divLegion = document.getElementById("div_candidato_legion"); var chkLegion = document.getElementById("candidato_asignar_legion"); var selLegion = document.getElementById("candidato_id_legion");
                if (divLegion) divLegion.style.display = "none"; if (chkLegion) chkLegion.checked = false; if (selLegion) selLegion.value = "";
                var selPuesto = document.getElementById("candidato_id_puesto"); var selJefe = document.getElementById("candidato_id_posible_jefe");
                if (selPuesto) selPuesto.innerHTML = "<option value=''>Seleccione puesto</option>"; if (selJefe) selJefe.innerHTML = "<option value=''>Seleccione departamento y puesto primero</option>";
            });
        }
        var selDeptoForm = document.getElementById("candidato_id_departamento");
        if (selDeptoForm) selDeptoForm.addEventListener("change", function() {
            var idDepto = this.value;
            var selPuesto = document.getElementById("candidato_id_puesto"); var selJefe = document.getElementById("candidato_id_posible_jefe");
            if (selPuesto) selPuesto.innerHTML = "<option value=''>Seleccione puesto</option>"; if (selJefe) selJefe.innerHTML = "<option value=''>Seleccione departamento y puesto primero</option>";
            if (!idDepto) return;
            fetch("/caphum/getPuestos", { method: "POST", headers: { "Content-Type": "application/json", "Accept": "application/json" }, body: JSON.stringify({ id_departamento: idDepto }) })
                .then(function(r){ return r.json(); }).then(function(res){ if (res.success && res.datos) res.datos.forEach(function(p){ var opt = document.createElement("option"); opt.value = p.id; opt.textContent = p.nombre || p.puesto_nombre || ""; selPuesto.appendChild(opt); }); });
            if (selJefe) {
                selJefe.innerHTML = "<option value=''>—</option>";
                fetch("/caphum/getJefeDirecto", { method: "POST", headers: { "Content-Type": "application/json", "Accept": "application/json" }, body: JSON.stringify({ id_departamento: idDepto, id_puesto: null }) })
                    .then(function(r){ return r.json(); }).then(function(res){ selJefe.innerHTML = "<option value=''>Seleccione posible jefe</option>"; if (res.success && res.datos && res.datos.length) res.datos.forEach(function(j){ var opt = document.createElement("option"); opt.value = j.id; opt.textContent = (j.nombre_completo || "").trim() || "ID " + j.id; selJefe.appendChild(opt); }); }).catch(function(){ selJefe.innerHTML = "<option value=''>Seleccione posible jefe</option>"; });
            }
        });
        var selPuestoForm = document.getElementById("candidato_id_puesto");
        if (selPuestoForm) selPuestoForm.addEventListener("change", function() {
            var idPuesto = this.value; var selDepto = document.getElementById("candidato_id_departamento"); var selJefe = document.getElementById("candidato_id_posible_jefe");
            if (!selJefe || !selDepto) return;
            selJefe.innerHTML = "<option value=''>—</option>"; var idDepto = selDepto.value;
            if (!idDepto) { selJefe.innerHTML = "<option value=''>Seleccione departamento y puesto primero</option>"; return; }
            fetch("/caphum/getJefeDirecto", { method: "POST", headers: { "Content-Type": "application/json", "Accept": "application/json" }, body: JSON.stringify({ id_departamento: idDepto, id_puesto: idPuesto || null }) })
                .then(function(r){ return r.json(); }).then(function(res){ selJefe.innerHTML = "<option value=''>Seleccione posible jefe</option>"; if (res.success && res.datos && res.datos.length) res.datos.forEach(function(j){ var opt = document.createElement("option"); opt.value = j.id; opt.textContent = (j.nombre_completo || "").trim() || "ID " + j.id; selJefe.appendChild(opt); }); }).catch(function(){ selJefe.innerHTML = "<option value=''>Seleccione posible jefe</option>"; });
        });
        var btnAddCandidato = document.querySelector("[data-bs-target=\"#offcanvasAddCandidato\"]");
        if (btnAddCandidato) btnAddCandidato.addEventListener("click", function() { candidatoEditId = null; document.getElementById("offcanvasCandidatoTitulo").textContent = "Nuevo Candidato"; });
        initFlatpickrFechaPostulacion();
        initCopiarUrlDocumentos();
        });
        </script>
        HTML;

        $departamento = CapHumDAO::getConsultaDepartamentoGestor($_SESSION['usuario_id']);
        $modulos = $_SESSION['modulos'] ?? [];
        $puedeGestionarCandidatos = in_array(42, $modulos);

        self::set("titulo", "Selección de Personal");
        self::set("script", $script);
        self::set("puedeGestionarCandidatos", $puedeGestionarCandidatos);
        self::set("departamento", $departamento);
        self::set("paisesActivos", \Models\Paises::getPaisesActivos());
        self::set("listaJefes", CapHumDAO::getListaPersonasParaJefe());
        self::render("candidatos");
    }

    public function getCandidatos()
    {
        header("Content-Type: application/json");
        $estatus = isset($_GET["estatus"]) ? trim($_GET["estatus"]) : null;
        $id_departamento = isset($_GET["id_departamento"]) ? (int) $_GET["id_departamento"] : null;
        $id_puesto = isset($_GET["id_puesto"]) ? (int) $_GET["id_puesto"] : null;
        $resultado = CandidatosDAO::getAll($estatus, $id_departamento, $id_puesto);

        // Eager Loading: Incluir documentos, verificación y métricas de cada candidato
        if ($resultado['success'] && !empty($resultado['datos']) && is_array($resultado['datos'])) {
            $tiposRequeridos = [
                'SOLICITUD INTERNA' => 1, 'CV O SOLICITUD DE TRABAJO' => 2, 'ACTA DE NACIMIENTO' => 3, 'ACTA DE NACIMIENTO Certificada' => 3,
                'CURP' => 4, 'IDENTIFICACIÓN OFICIAL' => 5,
                'COMPROBANTE DE DOMICILIO' => 6, 'CONSTANCIA DE SITUACION FISCAL' => 7, 'NÚMERO DE SEGURIDAD SOCIAL' => 8,
                'HOJA DE RETENCION FONACOT O INFONAVIT' => 9, 'ESTADO DE CUENTA' => 10,
            ];
            $normalize = function ($s) {
                $s = trim(mb_strtoupper($s ?? ''));
                $s = str_replace(['Í', 'Ó', 'Ú', 'Á', 'É', 'Ñ'], ['I', 'O', 'U', 'A', 'E', 'N'], $s);
                return preg_replace('/\s+/', ' ', $s);
            };

            foreach ($resultado['datos'] as &$candidato) {
                $id_candidato = (int) ($candidato['id'] ?? 0);
                if ($id_candidato > 0) {
                    $docData = CandidatosDAO::getDocumentosYVerificacion($id_candidato);
                    $documentos = $docData['documentos'] ?? [];
                    $candidato['documentos'] = $documentos;

                    if (isset($docData['verificacion']) && $docData['verificacion'] !== null) {
                        $candidato['verificacion_expediente'] = $docData['verificacion'];
                    }

                    // Calcular métricas
                    $clavesUnicas = [];
                    foreach ($documentos as $d) {
                        $tipo = $normalize($d['tipo_documento'] ?? '');
                        foreach ($tiposRequeridos as $nombre => $num) {
                            $nombreNorm = $normalize($nombre);
                            if ($tipo === $nombreNorm || strpos($tipo, $nombreNorm) !== false || strpos($nombreNorm, $tipo) !== false) {
                                $clavesUnicas[is_string($num) ? $num : $num] = true;
                                break;
                            }
                        }
                    }
                    $totalRequeridos = 10;
                    $totalActual = count($clavesUnicas);
                    $expedienteCompleto = ($totalActual >= $totalRequeridos
                        && isset($clavesUnicas[1]) && isset($clavesUnicas[2]) && isset($clavesUnicas[3]) && isset($clavesUnicas[4])
                        && isset($clavesUnicas[5]) && isset($clavesUnicas[6]) && isset($clavesUnicas[7]) && isset($clavesUnicas[8])
                        && isset($clavesUnicas[9]) && isset($clavesUnicas[10]));
                    $conteoValidados = CandidatosDAO::contarValidados($id_candidato);
                    $candidato['metricas'] = [
                        'total_documentos' => $totalActual,
                        'documentos_requeridos' => $totalRequeridos,
                        'porcentaje' => $totalRequeridos > 0 ? min(100, (int) round(($totalActual / $totalRequeridos) * 100)) : 0,
                        'expediente_completo' => $expedienteCompleto,
                        'validados' => (int) ($conteoValidados['validados'] ?? 0),
                    ];
                } else {
                    $candidato['documentos'] = [];
                    $candidato['metricas'] = [
                        'total_documentos' => 0,
                        'documentos_requeridos' => 11,
                        'porcentaje' => 0,
                        'expediente_completo' => false,
                        'validados' => 0,
                    ];
                }
            }
            unset($candidato); // Liberar referencia
        }

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
        if ($id <= 0) {
            echo json_encode(self::respuesta(false, 'ID inválido.', null));
            exit;
        }
        $storageRoot = defined('RAIZ') ? (RAIZ . '/storage') : (__DIR__ . '/../storage');
        $resDocs = CandidatosDAO::getDocumentosCandidato($id);
        if ($resDocs['success'] && !empty($resDocs['datos'])) {
            foreach ($resDocs['datos'] as $doc) {
                $rutaRel = trim($doc['ruta_archivo'] ?? '');
                if ($rutaRel !== '') {
                    $path = $storageRoot . '/' . $rutaRel;
                    if (is_file($path)) {
                        @unlink($path);
                    }
                }
            }
            CandidatosDAO::eliminarDocumentosDeCandidato($id);
        }
        $carpetaCandidato = $storageRoot . '/candidatos/' . $id;
        if (is_dir($carpetaCandidato)) {
            self::eliminarDirectorioRecursivo($carpetaCandidato);
        }
        CandidatosDAO::eliminarTokenDocumentosCandidato($id);
        $resultado = CandidatosDAO::delete($id);
        echo json_encode($resultado);
        exit;
    }

    /**
     * Borrar un directorio y todo su contenido (archivos y subcarpetas).
     */
    private static function eliminarDirectorioRecursivo($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        $items = @scandir($dir);
        if ($items === false) {
            return;
        }
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                self::eliminarDirectorioRecursivo($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
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
            $contacto = trim($mailSection['mail_contacto'] ?? '');
        if ($contacto === '' || !filter_var($contacto, FILTER_VALIDATE_EMAIL)) {
            $contacto = trim($mailSection['smtp_user'] ?? $mailSection['mail_from'] ?? '');
        }
        if ($contacto === '' || !filter_var($contacto, FILTER_VALIDATE_EMAIL)) {
            $contacto = 'lrgonzalez033@gmail.com';
        }
        }
        if ($contacto === '' || !filter_var($contacto, FILTER_VALIDATE_EMAIL)) {
            $contacto = 'lrgonzalez033@gmail.com';
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
                if (isset($tiposNombre[$tipo])) {
                    $documentos_subidos[$tiposNombre[$tipo]] = $d;
                }
            }
        }
        $expediente_completo = isset($documentos_subidos[1]) && isset($documentos_subidos[2]) && isset($documentos_subidos[3])
            && isset($documentos_subidos[4]) && isset($documentos_subidos[5]) && isset($documentos_subidos[6])
            && isset($documentos_subidos[7]) && isset($documentos_subidos[8]) && isset($documentos_subidos[9])
            && isset($documentos_subidos[10]);
        $this->set('token', $token);
        $this->set('nombre_candidato', $nombreCompleto);
        $this->set('id_candidato', $id_candidato);
        $this->set('documentos_subidos', $documentos_subidos);
        $this->set('expediente_completo', $expediente_completo);
        $this->set('api_verificacion_base', $this->getApiVerificacionBase());
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
        $this->set('api_verificacion_base', $this->getApiVerificacionBase());
        $this->render('subir_documentos_candidato', true);
    }

    /**
     * Base URL de la API de verificación (desde config.ini [doc_verificacion] api_url).
     * Para uso en la vista subir_documentos_candidato (llamadas desde el navegador).
     */
    private function getApiVerificacionBase()
    {
        $configFile = defined('RAIZ') ? (RAIZ . '/config/config.ini') : (__DIR__ . '/../config/config.ini');
        if (!is_file($configFile)) {
            return 'http://localhost:8000/api/v1';
        }
        $config = @parse_ini_file($configFile, true);
        $apiUrl = trim($config['doc_verificacion']['api_url'] ?? '');
        if ($apiUrl === '') {
            return 'http://localhost:8000/api/v1';
        }
        $base = preg_replace('#/verificar\s*$#', '', $apiUrl);
        return $base !== '' ? $base : 'http://localhost:8000/api/v1';
    }

    /**
     * Listar documentos del expediente de un candidato (JSON). Requiere módulo Candidatos.
     * GET: id_candidato (query).
     * Optimizado: una sola conexión DB, caché 45s (file o APCu), invalidación al subir/eliminar/verificar.
     */
    public function getDocumentosCandidatoList()
    {
        header('Content-Type: application/json; charset=utf-8');
        $id_candidato = (int) ($_GET['id_candidato'] ?? 0);
        if ($id_candidato <= 0) {
            echo json_encode(self::respuesta(false, 'id_candidato inválido.'));
            return;
        }

        $cacheDir = defined('RAIZ') ? (RAIZ . '/storage/cache') : (__DIR__ . '/../storage/cache');
        $cacheKey = 'doc_candidato_' . $id_candidato;
        $ttl = 45;

        if (function_exists('apcu_fetch')) {
            $cached = @\apcu_fetch($cacheKey);
            if ($cached !== false && is_array($cached) && isset($cached['ts']) && (time() - $cached['ts']) <= $ttl && isset($cached['json'])) {
                echo $cached['json'];
                return;
            }
        } else {
            $cacheFile = $cacheDir . '/' . $cacheKey . '.json';
            if (is_file($cacheFile) && (time() - filemtime($cacheFile)) <= $ttl) {
                $raw = @file_get_contents($cacheFile);
                if ($raw !== false && $raw !== '') {
                    echo $raw;
                    return;
                }
            }
        }

        $data = CandidatosDAO::getDocumentosYVerificacion($id_candidato);
        $documentos = $data['documentos'] ?? [];
        $verificacion = $data['verificacion'] ?? null;

        $payload = ['documentos' => $documentos];
        if ($verificacion !== null) {
            $payload['verificacion_expediente'] = $verificacion;
        }
        $tiposRequeridos = [
            'SOLICITUD INTERNA' => 1, 'CV O SOLICITUD DE TRABAJO' => 2, 'ACTA DE NACIMIENTO' => 3, 'ACTA DE NACIMIENTO Certificada' => 3,
            'CURP' => 4, 'IDENTIFICACIÓN OFICIAL' => 5,
            'COMPROBANTE DE DOMICILIO' => 6, 'CONSTANCIA DE SITUACION FISCAL' => 7, 'NÚMERO DE SEGURIDAD SOCIAL' => 8,
            'HOJA DE RETENCION FONACOT O INFONAVIT' => 9, 'ESTADO DE CUENTA' => 10,
        ];
        $clavesUnicas = [];
        $normalize = function ($s) {
            $s = trim(mb_strtoupper($s ?? ''));
            $s = str_replace(['Í', 'Ó', 'Ú', 'Á', 'É', 'Ñ'], ['I', 'O', 'U', 'A', 'E', 'N'], $s);
            return preg_replace('/\s+/', ' ', $s);
        };
        foreach ($documentos as $d) {
            $tipo = $normalize($d['tipo_documento'] ?? '');
            foreach ($tiposRequeridos as $nombre => $num) {
                $nombreNorm = $normalize($nombre);
                if ($tipo === $nombreNorm || strpos($tipo, $nombreNorm) !== false || strpos($nombreNorm, $tipo) !== false) {
                    $clavesUnicas[is_string($num) ? $num : $num] = true;
                    break;
                }
            }
        }
        $totalRequeridos = 10;
        $totalActual = count($clavesUnicas);
        $expedienteCompleto = ($totalActual >= $totalRequeridos
            && isset($clavesUnicas[1]) && isset($clavesUnicas[2]) && isset($clavesUnicas[3]) && isset($clavesUnicas[4])
            && isset($clavesUnicas[5]) && isset($clavesUnicas[6]) && isset($clavesUnicas[7]) && isset($clavesUnicas[8])
            && isset($clavesUnicas[9]) && isset($clavesUnicas[10]));
        $payload['metricas'] = [
            'total_documentos' => $totalActual,
            'documentos_requeridos' => $totalRequeridos,
            'porcentaje' => $totalRequeridos > 0 ? min(100, (int) round(($totalActual / $totalRequeridos) * 100)) : 0,
            'expediente_completo' => $expedienteCompleto,
        ];
        $conteoValidados = CandidatosDAO::contarValidados($id_candidato);
        $payload['metricas']['validados'] = (int) ($conteoValidados['validados'] ?? 0);

        $json = json_encode(self::respuesta(true, 'OK', $payload));

        if (function_exists('apcu_store')) {
            @\apcu_store($cacheKey, ['ts' => time(), 'json' => $json], $ttl);
        } else {
            if (!is_dir($cacheDir)) {
                @mkdir($cacheDir, 0755, true);
            }
            $cacheFile = $cacheDir . '/' . $cacheKey . '.json';
            @file_put_contents($cacheFile, $json, LOCK_EX);
        }

        echo $json;
    }

    /**
     * Ejecutar verificación API del expediente ahora (frente + reverso + PDFs).
     * POST/GET: id_candidato. Útil cuando la verificación no se disparó al subir documentos.
     */
    public function verificarExpedienteCandidato()
    {
        set_time_limit(300);
        header('Content-Type: application/json; charset=utf-8');
        $id_candidato = (int) ($_GET['id_candidato'] ?? $_POST['id_candidato'] ?? 0);
        if ($id_candidato <= 0) {
            echo json_encode(self::respuesta(false, 'id_candidato inválido.'));
            return;
        }
        $storageRoot = defined('RAIZ') ? (RAIZ . '/storage') : (__DIR__ . '/../storage');
        $rutasParaValidar = ['identificacion_pdf' => null, 'curp' => null, 'nss' => null, 'constancia_fiscal' => null, 'acta_nacimiento' => null];
        $resDocs = CandidatosDAO::getDocumentosCandidato($id_candidato);
        if (!$resDocs['success'] || empty($resDocs['datos'])) {
            echo json_encode(self::respuesta(false, 'No hay documentos para verificar.'));
            return;
        }
        foreach ($resDocs['datos'] as $d) {
            $rutaRel = trim($d['ruta_archivo'] ?? '');
            if ($rutaRel === '' || !is_file($storageRoot . '/' . $rutaRel)) {
                continue;
            }
            $pathAbs = $storageRoot . '/' . $rutaRel;
            $tipo = trim($d['tipo_documento'] ?? '');
            if ($tipo === 'IDENTIFICACIÓN OFICIAL') {
                $rutasParaValidar['identificacion_pdf'] = $pathAbs;
            } elseif ($tipo === 'CURP') {
                $rutasParaValidar['curp'] = $pathAbs;
            } elseif ($tipo === 'NÚMERO DE SEGURIDAD SOCIAL') {
                $rutasParaValidar['nss'] = $pathAbs;
            } elseif ($tipo === 'CONSTANCIA DE SITUACION FISCAL') {
                $rutasParaValidar['constancia_fiscal'] = $pathAbs;
            } elseif ($tipo === 'ACTA DE NACIMIENTO' || $tipo === 'ACTA DE NACIMIENTO Certificada') {
                $rutasParaValidar['acta_nacimiento'] = $pathAbs;
            }
        }
        if (!$rutasParaValidar['identificacion_pdf']) {
            echo json_encode(self::respuesta(false, 'Falta el documento de identificación oficial (PDF con frente y reverso) para poder verificar.'));
            return;
        }
        $resultadoApi = $this->validarExpedienteApi($rutasParaValidar);
        if ($resultadoApi === null) {
            echo json_encode(self::respuesta(false, 'No se pudo enviar el expediente a la API.'));
            return;
        }
        if (!empty($resultadoApi['error'])) {
            echo json_encode(self::respuesta(false, $resultadoApi['error']));
            return;
        }
        $payload = [
            'todo_coincide' => $resultadoApi['todo_coincide'] ?? false,
            'foto_rechazada' => $resultadoApi['foto_rechazada'] ?? false,
            'curp_definitivo' => $resultadoApi['curp_definitivo'] ?? null,
            'checks_ok' => $resultadoApi['checks_ok'] ?? 0,
            'checks_totales' => $resultadoApi['checks_totales'] ?? 0,
            'alertas' => $resultadoApi['alertas'] ?? [],
            'identificacion_frente_score' => $resultadoApi['identificacion_frente_score'] ?? null,
            'identificacion_reverso_score' => $resultadoApi['identificacion_reverso_score'] ?? null,
            'comparaciones' => $resultadoApi['comparaciones'] ?? null,
            'nombre_ocr' => $resultadoApi['nombre_ocr'] ?? null,
            'anio_nacimiento' => $resultadoApi['anio_nacimiento'] ?? null,
            'tipo_documento' => $resultadoApi['tipo_documento'] ?? null,
        ];
        CandidatosDAO::updateVerificacionExpediente($id_candidato, json_encode($payload));
        echo json_encode(self::respuesta(true, 'Verificación ejecutada. Ya puedes ver los porcentajes y coincidencias.', ['verificacion_expediente' => $payload]));
    }

    /**
     * Abrir/descargar un documento del expediente. Requiere módulo Candidatos.
     * GET: id (id del registro candidato_documento).
     * Sirve desde disco (rápido, con caché) si existe ruta_archivo; si no, desde BD (contenido LONGBLOB).
     */
    public function verDocumentoCandidato($id = null)
    {
        $id_doc = (int) ($id ?? $_GET['id'] ?? 0);
        if ($id_doc <= 0) {
            header('HTTP/1.0 400 Bad Request');
            echo 'ID inválido';
            return;
        }

        $storageRoot = defined('RAIZ') ? (RAIZ . '/storage') : (__DIR__ . '/../storage');

        // Ruta rápida: solo nombre y ruta (sin cargar LONGBLOB). Casi siempre el archivo está en disco.
        $resRuta = CandidatosDAO::getDocumentoRutaSolo($id_doc);
        if ($resRuta['success'] && !empty($resRuta['datos']['ruta_archivo'])) {
            $rutaRel = trim($resRuta['datos']['ruta_archivo']);
            $path = $storageRoot . '/' . $rutaRel;
            if (is_file($path)) {
                $nombre = $resRuta['datos']['nombre_archivo'] ?? 'documento';
                $ext = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
                $mimes = ['pdf' => 'application/pdf', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'doc' => 'application/msword', 'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                $mime = $mimes[$ext] ?? 'application/octet-stream';
                $size = filesize($path);
                $mtime = filemtime($path);
                if (ob_get_length()) {
                    ob_clean();
                }
                header('Content-Type: ' . $mime);
                header('Content-Disposition: inline; filename="' . str_replace('"', '\\"', $nombre) . '"');
                header('Content-Length: ' . $size);
                header('Cache-Control: private, max-age=86400'); // 1 día: segunda vez sale de caché del navegador
                header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $mtime) . ' GMT');
                $etag = '"' . md5($path . $size . $mtime) . '"';
                header('ETag: ' . $etag);
                if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) === $etag) {
                    header('HTTP/1.1 304 Not Modified');
                    return;
                }
                readfile($path);
                return;
            }
        }

        // Fallback: documento guardado en BD (contenido LONGBLOB) o archivo no encontrado en disco
        $res = CandidatosDAO::getDocumentoContenidoParaVer($id_doc);
        if (!$res['success'] || empty($res['datos'])) {
            header('HTTP/1.0 404 Not Found');
            echo 'Documento no encontrado';
            return;
        }
        $doc = $res['datos'];
        $nombre = $doc['nombre_archivo'] ?? 'documento';

        if (!empty($doc['contenido'])) {
            $mime = !empty($doc['mime_type']) ? $doc['mime_type'] : 'application/octet-stream';
            if (ob_get_length()) {
                ob_clean();
            }
            header('Content-Type: ' . $mime);
            header('Content-Disposition: inline; filename="' . str_replace('"', '\\"', $nombre) . '"');
            header('Content-Length: ' . strlen($doc['contenido']));
            header('Cache-Control: private, max-age=3600');
            echo $doc['contenido'];
            return;
        }

        $rutaRel = trim($doc['ruta_archivo'] ?? '');
        $path = $storageRoot . '/' . $rutaRel;
        if (!is_file($path)) {
            header('HTTP/1.0 404 Not Found');
            echo 'Archivo no encontrado';
            return;
        }
        $ext = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
        $mimes = ['pdf' => 'application/pdf', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'doc' => 'application/msword', 'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        header('Content-Type: ' . ($mimes[$ext] ?? 'application/octet-stream'));
        header('Content-Disposition: inline; filename="' . str_replace('"', '\\"', $nombre) . '"');
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: private, max-age=86400');
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
        // Limpiar verificación al eliminar un documento (los resultados ya no son válidos)
        $idCand = (int) ($doc['id_candidato'] ?? 0);
        if ($idCand > 0) {
            CandidatosDAO::updateVerificacionExpediente($idCand, null);
            CandidatosDAO::invalidateDocumentacionCache($idCand);
        }
        echo json_encode(self::respuesta(true, 'Documento eliminado.'));
    }

    /**
     * Validar/aprobar un documento individual (toggle). Requiere módulo Candidatos.
     * POST: id (id del registro candidato_documento), validado (1|0 opcional, si no se envía se togglea).
     */
    public function validarDocumentoCandidato()
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
        $nuevoValor = isset($_POST['validado']) ? (int) $_POST['validado'] : (((int) ($doc['validado'] ?? 0)) === 0 ? 1 : 0);
        $upd = CandidatosDAO::toggleValidadoDocumento($id_doc, $nuevoValor);
        if (!$upd['success']) {
            echo json_encode(self::respuesta(false, $upd['mensaje'] ?? 'Error.'));
            return;
        }
        $idCand = (int) ($doc['id_candidato'] ?? 0);
        $conteo = CandidatosDAO::contarValidados($idCand);
        $requeridos = 11;
        $todosValidados = ($conteo['total'] >= $requeridos && $conteo['validados'] >= $requeridos);
        if ($todosValidados) {
            CandidatosDAO::updateEstatus($idCand, 'Validado');
        } else {
            CandidatosDAO::updateEstatus($idCand, 'Por evaluar');
        }
        CandidatosDAO::invalidateDocumentacionCache($idCand);
        echo json_encode(self::respuesta(true, $upd['mensaje'], [
            'validado' => $nuevoValor,
            'todos_validados' => $todosValidados,
            'validados' => $conteo['validados'],
            'total' => $conteo['total'],
        ]));
    }

    /**
     * Cerrar proceso del candidato (modal "Cerrar proceso"). Requiere módulo Candidatos.
     * POST JSON: id_candidato, motivo, descripcion (opcional).
     */
    public function cerrarProcesoCandidato()
    {
        header('Content-Type: application/json; charset=utf-8');
        $raw = file_get_contents('php://input');
        $body = json_decode($raw, true) ?: [];
        $id_candidato = (int) ($body['id_candidato'] ?? $body['id'] ?? 0);
        $motivo = trim($body['motivo'] ?? '');
        $descripcion = isset($body['descripcion']) ? trim($body['descripcion']) : null;
        if ($id_candidato <= 0) {
            echo json_encode(self::respuesta(false, 'ID de candidato inválido.'));
            return;
        }
        $motivosPermitidos = ['no_cubre_perfil', 'desistio', 'sin_info_a_tiempo', 'otro'];
        if ($motivo === '' || !in_array($motivo, $motivosPermitidos, true)) {
            echo json_encode(self::respuesta(false, 'Selecciona un motivo válido para el cierre.'));
            return;
        }
        $resultado = CandidatosDAO::cerrarProceso($id_candidato, $motivo, $descripcion);
        if ($resultado['success']) {
            CandidatosDAO::invalidateDocumentacionCache($id_candidato);
        }
        echo json_encode($resultado);
        exit;
    }

    /**
     * Continuar proceso: envía un solo correo al candidato (documentación validada) y responde success.
     * El front mostrará después el modal "¿RRHH dio de alta en nómina?" (Sí/No). Requiere módulo Candidatos.
     * POST JSON: id_candidato (o id).
     */
    public function continuarProcesoCandidato()
    {
        header('Content-Type: application/json; charset=utf-8');
        $raw = file_get_contents('php://input');
        $body = json_decode($raw, true) ?: [];
        $id_candidato = (int) ($body['id_candidato'] ?? $body['id'] ?? 0);
        if ($id_candidato <= 0) {
            echo json_encode(self::respuesta(false, 'ID de candidato inválido.'));
            return;
        }
        $candidatoRes = CandidatosDAO::getById($id_candidato);
        if (!$candidatoRes['success'] || empty($candidatoRes['datos'])) {
            echo json_encode(self::respuesta(false, 'Candidato no encontrado.'));
            return;
        }
        $c = $candidatoRes['datos'];
        $nombreCompleto = trim(implode(' ', [
            $c['nombres'] ?? '',
            $c['segundo_nombre'] ?? '',
            $c['apellidop'] ?? '',
            $c['apellidom'] ?? ''
        ]));
        $destino = trim($c['email'] ?? '');
        if ($destino === '' || !filter_var($destino, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(self::respuesta(false, 'Correo del candidato no válido.'));
            return;
        }

        $configFile = defined('RAIZ') ? (RAIZ . '/config/config.ini') : (__DIR__ . '/../config/config.ini');
        $config = is_file($configFile) ? @parse_ini_file($configFile, true) : [];
        $mailSection = is_array($config['mail'] ?? null) ? $config['mail'] : [];
        $contacto = trim($mailSection['mail_contacto'] ?? '');
        if ($contacto === '' || !filter_var($contacto, FILTER_VALIDATE_EMAIL)) {
            $contacto = trim($mailSection['smtp_user'] ?? $mailSection['mail_from'] ?? '');
        }
        if ($contacto === '' || !filter_var($contacto, FILTER_VALIDATE_EMAIL)) {
            $contacto = 'lrgonzalez033@gmail.com';
        }

        $dirPublic = defined('RAIZ') ? dirname(RAIZ) . '/public' : (__DIR__ . '/../../public');
        $base = $this->obtenerBaseUrlApp();
        $baseUrl = rtrim($base, '/');
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
        $logoSrc = $rutaLogoInline ? 'cid:logo__SPARTA_SECRET_REDACTED__' : ($baseUrl . '/assets/img/logo_correo.png');

        $nombreMayusculas = mb_strtoupper($nombreCompleto, 'UTF-8');
        $asunto = 'Tu documentación fue validada – Siguiente paso en tu proceso con Maxikash';
        $mensajeHtml = '<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Documentación validada</title>
</head>
<body style="margin:0; padding:0; background-color:#e8eef4; font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#e8eef4;">
    <tr>
      <td align="center" style="padding: 32px 16px;">
        <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="max-width:600px; width:100%; background-color:#ffffff; border-radius:8px; box-shadow: 0 2px 12px rgba(0,0,0,0.08);">
          <tr>
            <td style="background-color:#1e3a5f; padding: 24px 12px 24px 32px; border-radius: 8px 8px 0 0;">
              <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                <tr>
                  <td style="vertical-align: middle;">
                    <h1 style="margin:0; color:#ffffff; font-size: 22px; font-weight: 600;">MaxiKash — Capital Humano</h1>
                    <p style="margin: 6px 0 0 0; color: rgba(255,255,255,0.9); font-size: 14px;">Documentación validada</p>
                  </td>
                  <td style="vertical-align: middle; text-align: right; width: 160px;"><img src="' . htmlspecialchars($logoSrc) . '" alt="MaxiKash" width="160" height="auto" style="max-height: 70px; width: auto;" /></td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td style="padding: 32px;">
              <p style="margin:0 0 16px 0; color:#1a202c; font-size: 16px; line-height: 1.6;">Hola ' . htmlspecialchars($nombreMayusculas) . ',</p>
              <p style="margin:0 0 16px 0; color:#2d3748; font-size: 15px; line-height: 1.6;">Te informamos que hemos revisado y validado correctamente toda tu documentación. Estás listo(a) para continuar con el proceso.</p>
              <p style="margin:0 0 16px 0; color:#2d3748; font-size: 15px; line-height: 1.6;">En los próximos días se le indicará los siguientes pasos.</p>
              <p style="margin:0 0 24px 0; color:#2d3748; font-size: 15px; line-height: 1.6;">Si tiene algún problema con la carga, contáctenos en <a href="mailto:' . htmlspecialchars($contacto) . '" style="color:#2c5282; text-decoration:none;">' . htmlspecialchars($contacto) . '</a>.</p>
              <table role="presentation" cellspacing="0" cellpadding="0" style="margin-top: 28px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
                <tr><td><p style="margin:0 0 4px 0; color:#2d3748; font-size: 15px;">Saludos,</p><p style="margin:0; color:#1a202c; font-size: 15px; font-weight: 600;">Equipo de Capital Humano – Maxikash</p></td></tr>
              </table>
            </td>
          </tr>
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
            echo json_encode(self::respuesta(true, 'Correo enviado al candidato correctamente.', ['id_candidato' => $id_candidato]));
        } else {
            $msg = $this->enviarCorreoUltimoError ?: 'No se pudo enviar el correo.';
            echo json_encode(self::respuesta(false, $msg, null));
        }
        exit;
    }

    /**
     * Pasar candidato a Gestión: crea persona en __SPARTA_SECRET_REDACTED__, envía correo de bienvenida y actualiza candidato a Contratado.
     * Se llama desde el sistema (modal) o desde el enlace del correo (confirmarAltaNomina). Requiere módulo Candidatos si se llama por POST.
     * POST JSON: id_candidato (o id).
     */
    public function pasarCandidatoAGestion()
    {
        header('Content-Type: application/json; charset=utf-8');
        $raw = file_get_contents('php://input');
        $body = json_decode($raw, true) ?: [];
        $id_candidato = (int) ($body['id_candidato'] ?? $body['id'] ?? 0);
        if ($id_candidato <= 0) {
            echo json_encode(self::respuesta(false, 'ID de candidato inválido.'));
            return;
        }
        $result = $this->ejecutarAltaCandidatoEnGestion($id_candidato);
        if ($result['success']) {
            echo json_encode(self::respuesta(true, 'Colaborador dado de alta en Gestión correctamente. Se envió el correo de bienvenida.', ['id_persona' => $result['id_persona'] ?? 0, 'id_candidato' => $result['id_candidato']]));
        } else {
            echo json_encode(self::respuesta(false, $result['mensaje'] ?? 'Error al dar de alta en Gestión.'));
        }
        exit;
    }

    /**
     * Confirmación de alta en nómina desde el correo (enlace Sí/No). No requiere sesión.
     * GET: token, respuesta (si|no). Si respuesta=si ejecuta pasar a Gestión y muestra página de éxito.
     */
    public function confirmarAltaNomina()
    {
        header('Content-Type: text/html; charset=utf-8');
        $token = trim($_GET['token'] ?? '');
        $respuesta = strtolower(trim($_GET['respuesta'] ?? ''));
        if ($token === '') {
            $this->mostrarPaginaConfirmacionAlta(false, 'Enlace no válido.');
            return;
        }
        $resToken = CandidatosDAO::getPorTokenConfirmacionAlta($token);
        if (!$resToken['success']) {
            $this->mostrarPaginaConfirmacionAlta(false, $resToken['mensaje'] ?? 'Enlace no válido o expirado.');
            return;
        }
        $id_candidato = $resToken['datos'];
        CandidatosDAO::marcarTokenConfirmacionAltaUsado($token, $respuesta);
        if ($respuesta !== 'si') {
            $this->mostrarPaginaConfirmacionAlta(true, 'Su respuesta ha sido registrada. No se realizó el alta en Gestión en este momento.', false);
            return;
        }
        $result = $this->ejecutarAltaCandidatoEnGestion($id_candidato);
        if ($result['success']) {
            $this->mostrarPaginaConfirmacionAlta(true, 'El colaborador ha sido dado de alta en Gestión correctamente. Se envió el correo de bienvenida y ya tiene acceso al menú de Onboarding.');
        } else {
            $this->mostrarPaginaConfirmacionAlta(false, $result['mensaje'] ?? 'Error al dar de alta en Gestión.');
        }
    }

    /**
     * Página HTML de resultado para confirmación de alta en nómina (desde enlace del correo).
     * @param bool $exito
     * @param string $mensaje
     * @param bool $mostrarBoton Si false, no se muestra el botón "Ir al sistema" (p. ej. cuando la respuesta fue No).
     */
    private function mostrarPaginaConfirmacionAlta($exito, $mensaje, $mostrarBoton = true)
    {
        $titulo = $exito ? 'Listo' : 'Error';
        $clase = $exito ? 'success' : 'error';
        $base = $this->obtenerBaseUrlApp();
        echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>' . htmlspecialchars($titulo) . '</title>';
        echo '<style>body{font-family:\'Segoe UI\',Tahoma,sans-serif;background:#e8eef4;margin:0;padding:24px;display:flex;align-items:center;justify-content:center;min-height:100vh;}';
        echo '.box{max-width:480px;background:#fff;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,0.1);padding:32px;text-align:center;}';
        echo '.success .icon{color:#22c55e;font-size:48px;}.error .icon{color:#ef4444;font-size:48px;}';
        echo 'h1{font-size:1.35rem;color:#1a202c;margin:0 0 16px 0;}p{color:#4a5568;line-height:1.6;margin:0 0 24px 0;}';
        echo 'a{display:inline-block;padding:10px 20px;background:#1e3a5f;color:#fff!important;text-decoration:none;border-radius:8px;font-weight:600;}</style></head><body>';
        echo '<div class="box ' . $clase . '"><div class="icon">' . ($exito ? '✓' : '✕') . '</div>';
        echo '<h1>' . htmlspecialchars($titulo) . '</h1><p>' . nl2br(htmlspecialchars($mensaje)) . '</p>';
        if ($mostrarBoton) {
            echo '<a href="' . htmlspecialchars(rtrim($base, '/') . '/') . '">Ir al sistema</a>';
        }
        echo '</div></body></html>';
    }

    /**
     * Lógica común: alta de candidato en Gestión (persona, módulo Onboarding, correo bienvenida).
     * @return array { success, mensaje, id_persona?, id_candidato }
     */
    private function ejecutarAltaCandidatoEnGestion($id_candidato)
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0) {
            return ['success' => false, 'mensaje' => 'ID de candidato inválido.'];
        }
        $candidatoRes = CandidatosDAO::getById($id_candidato);
        if (!$candidatoRes['success'] || empty($candidatoRes['datos'])) {
            return ['success' => false, 'mensaje' => 'Candidato no encontrado.'];
        }
        $c = $candidatoRes['datos'];
        $numero_empleado = trim($c['numero_empleado'] ?? '');
        if ($numero_empleado === '') {
            $numero_empleado = 'PEND';
        }
        $fechaIngreso = (new \DateTime('now', new \DateTimeZone('America/Mexico_City')))->format('Y-m-d');
        $dataPersona = [
            'nombres'       => $c['nombres'] ?? '',
            'segundo_nombre'=> $c['segundo_nombre'] ?? '',
            'apellidop'     => $c['apellidop'] ?? '',
            'apellidom'     => $c['apellidom'] ?? '',
            'numero_empleado' => $numero_empleado,
            'correo'        => $c['email'] ?? '',
            'telefono'      => $c['telefono'] ?? '',
            'id_puesto'     => $c['id_puesto'] ?? 0,
            'id_jefe'       => !empty($c['id_posible_jefe']) ? (int) $c['id_posible_jefe'] : '',
            'usuario'       => $c['usuario'] ?? '',
            'contrasena'    => $c['contrasena'] ?? '',
            'estatus'       => 'Activo',
            'fecha_ingreso' => $fechaIngreso,
            'id_pais'       => !empty($c['id_pais']) ? (int) $c['id_pais'] : 1,
        ];
        if (!empty($c['id_legion'])) {
            $dataPersona['asignar_legion'] = true;
            $dataPersona['id_legion'] = (int) $c['id_legion'];
        }
        $resInsert = CapHumDAO::insertPersona($dataPersona);
        if (!$resInsert['success']) {
            return ['success' => false, 'mensaje' => $resInsert['mensaje'] ?? 'Error al dar de alta en Gestión.'];
        }
        $id_persona = isset($resInsert['datos']['id']) ? (int) $resInsert['datos']['id'] : 0;
        CandidatosDAO::updateEstatus($id_candidato, 'Contratado');
        $resModulo = CapHumDAO::asignarSoloModuloOnboarding($id_persona);
        if (!$resModulo['success']) {
            return ['success' => false, 'mensaje' => $resModulo['mensaje'] ?? 'Error al asignar permiso de Onboarding.'];
        }
        $nombreCompleto = trim(implode(' ', [$c['nombres'] ?? '', $c['segundo_nombre'] ?? '', $c['apellidop'] ?? '', $c['apellidom'] ?? '']));
        $destino = trim($c['email'] ?? '');
        if ($destino !== '' && filter_var($destino, FILTER_VALIDATE_EMAIL)) {
            $base = $this->obtenerBaseUrlApp();
            $dirPublic = defined('RAIZ') ? dirname(RAIZ) . '/public' : (__DIR__ . '/../../public');
            $rutaLogoInline = null;
            if (is_file($dirPublic . '/assets/img/logo_correo.png')) {
                $rutaLogoInline = realpath($dirPublic . '/assets/img/logo_correo.png');
            } elseif (is_file($dirPublic . '/assets/img/logo___SPARTA_SECRET_REDACTED__.png')) {
                $rutaLogoInline = realpath($dirPublic . '/assets/img/logo___SPARTA_SECRET_REDACTED__.png');
            }
            $logoSrc = $rutaLogoInline ? 'cid:logo__SPARTA_SECRET_REDACTED__' : (rtrim($base, '/') . '/assets/img/logo_correo.png');
            $urlPlataforma = rtrim($base, '/') . '/';
            $urlOnboarding = rtrim($base, '/') . '/onboarding/index';
            $cuerpoBienvenida = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Bienvenida</title></head><body style="margin:0; padding:0; background-color:#e8eef4; font-family: \'Segoe UI\', Tahoma, sans-serif;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#e8eef4;"><tr><td align="center" style="padding: 32px 16px;">
    <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="max-width:600px; background-color:#ffffff; border-radius:8px; box-shadow: 0 2px 12px rgba(0,0,0,0.08);">
      <tr><td style="background-color:#1e3a5f; padding: 24px 32px; border-radius: 8px 8px 0 0;"><h1 style="margin:0; color:#ffffff; font-size: 22px;">Bienvenido(a) a MaxiKash</h1><p style="margin: 6px 0 0 0; color: rgba(255,255,255,0.9); font-size: 14px;">Ya formas parte del equipo</p></td></tr>
      <tr><td style="padding: 32px;">
        <p style="margin:0 0 16px 0; color:#1a202c; font-size: 16px;">Hola <strong>' . htmlspecialchars($nombreCompleto) . '</strong>,</p>
        <p style="margin:0 0 16px 0; color:#2d3748; font-size: 15px; line-height: 1.6;">Nos da mucho gusto darte la bienvenida a Maxikash. Ya formas parte del equipo.</p>
        <p style="margin:0 0 20px 0; color:#2d3748; font-size: 15px; line-height: 1.6;">Para comenzar, entra al <strong>Onboarding</strong> donde encontrarás la información importante para tu incorporación.</p>
        <p style="margin: 24px 0 16px 0;"><a href="' . htmlspecialchars($urlOnboarding) . '" style="display:inline-block; padding: 14px 28px; background-color:#1e3a5f; color:#ffffff !important; text-decoration:none; border-radius: 8px; font-weight: 600; font-size: 16px;">Entrar al Onboarding</a></p>
        <p style="margin:0 0 8px 0; color:#718096; font-size: 14px;">También puedes <a href="' . htmlspecialchars($urlPlataforma) . '" style="color:#2c5282;">acceder a la plataforma</a> con tu usuario y contraseña.</p>
        <p style="margin: 24px 0 0 0; color:#1a202c; font-size: 15px; font-weight: 600;">¡Bienvenido(a)!<br>Equipo de Capital Humano – Maxikash</p>
      </td></tr>
      <tr><td style="padding: 16px 32px 24px; background-color:#f7fafc; border-radius: 0 0 8px 8px; border-top: 1px solid #e2e8f0;"><p style="margin:0; color:#718096; font-size: 12px;">Correo generado automáticamente.</p></td></tr>
    </table>
  </td></tr></table>
</body></html>';
            $this->enviarCorreo($destino, 'Bienvenido(a) a Maxikash', $cuerpoBienvenida, $nombreCompleto, $rutaLogoInline);
        }
        return ['success' => true, 'mensaje' => 'OK', 'id_persona' => $id_persona, 'id_candidato' => $id_candidato];
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
     * Llama a la API validar-expediente (Python).
     * Acepta: identificacion_pdf (un PDF con frente y reverso) O bien frente y reverso como imágenes.
     * @param array $rutas ['identificacion_pdf' => ruta] o ['frente' => ruta, 'reverso' => ruta], más curp, nss, etc.
     * @return array|null Respuesta JSON de la API; null si faltan archivos; ['error' => mensaje] si fallo
     */
    private function validarExpedienteApi(array $rutas)
    {
        $configFile = defined('RAIZ') ? (RAIZ . '/config/config.ini') : (__DIR__ . '/../config/config.ini');
        if (!is_file($configFile)) {
            return ['error' => 'No se encontró backend/config/config.ini.'];
        }
        $config = @parse_ini_file($configFile, true);
        $apiUrlVerificar = trim($config['doc_verificacion']['api_url'] ?? '');
        $apiKey = trim($config['doc_verificacion']['api_key'] ?? '');
        if ($apiUrlVerificar === '' || $apiKey === '') {
            return ['error' => 'En config.ini [doc_verificacion] faltan api_url o api_key.'];
        }
        $baseUrl = preg_replace('#/verificar\s*$#', '', $apiUrlVerificar);

        // Health-check rápido (3 s máximo) para no esperar 45 s si la API está apagada
        $healthUrl = rtrim($baseUrl, '/') . '/health';
        $hc = curl_init($healthUrl);
        curl_setopt_array($hc, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT_MS => 3000,
            CURLOPT_CONNECTTIMEOUT_MS => 2000,
            CURLOPT_NOBODY => false,
        ]);
        $hBody = curl_exec($hc);
        $hCode = curl_getinfo($hc, CURLINFO_HTTP_CODE);
        $hErr = curl_error($hc);
        if ($hCode !== 200 || $hBody === false) {
            return ['error' => 'La API no responde (health-check falló en 3 s). Enciéndela con: python -m uvicorn app.main:app --host 0.0.0.0 --port 8000' . ($hErr ? ' — ' . $hErr : '')];
        }

        $urlExp = rtrim($baseUrl, '/') . '/validar-expediente';
        $post = [
            'tipo_documento' => 'RESIDENCIA_TEMPORAL',
        ];

        $usaPdfId = !empty($rutas['identificacion_pdf']) && is_file($rutas['identificacion_pdf']);
        if ($usaPdfId) {
            $post['identificacion_pdf'] = new \CURLFile($rutas['identificacion_pdf'], 'application/pdf', basename($rutas['identificacion_pdf']));
        } else {
            $mimeImg = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'tiff' => 'image/tiff'];
            foreach (['frente', 'reverso'] as $key) {
                if (empty($rutas[$key]) || !is_file($rutas[$key])) {
                    return null;
                }
                $ext = strtolower(pathinfo($rutas[$key], PATHINFO_EXTENSION));
                $post[$key] = new \CURLFile($rutas[$key], $mimeImg[$ext] ?? 'application/octet-stream', basename($rutas[$key]));
            }
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
            CURLOPT_TIMEOUT => 240,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);
        $body = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        $curlErrno = curl_errno($ch);
        if ($curlErrno !== 0 || $body === false) {
            $mensaje = $curlErr ?: 'Error de conexión (código ' . $curlErrno . ').';
            return ['error' => 'No se pudo conectar con la API: ' . $mensaje];
        }
        if ($httpCode !== 200 || $body === '') {
            return ['error' => 'La API respondió con código HTTP ' . $httpCode . '. Revisa los logs de la API.'];
        }
        $data = json_decode($body, true);
        if (!is_array($data) || isset($data['error'])) {
            return ['error' => 'La API devolvió una respuesta inválida.'];
        }
        return $data;
    }

    /**
     * Llama a la API verificar-constancia-fiscal-documento (Python) con el PDF de constancia fiscal.
     * @param string $rutaPdf Ruta absoluta al PDF ya subido
     * @return array|null Respuesta JSON decodificada (valido, mensaje, rfc, curp, fecha_emision, meses_antiguedad, vigencia_ok, actividad_asalariado, regimen_sueldos_salarios); null si error de config/conexión
     */
    private function verificarConstanciaFiscalApi($rutaPdf)
    {
        $configFile = defined('RAIZ') ? (RAIZ . '/config/config.ini') : (__DIR__ . '/../config/config.ini');
        if (!is_file($configFile)) {
            return null;
        }
        $config = @parse_ini_file($configFile, true);
        $apiUrl = trim($config['doc_verificacion']['api_url'] ?? '');
        $apiKey = trim($config['doc_verificacion']['api_key'] ?? '');
        if ($apiUrl === '' || $apiKey === '') {
            return null;
        }
        $baseUrl = preg_replace('#/verificar\s*$#', '', $apiUrl);
        $url = rtrim($baseUrl, '/') . '/verificar-constancia-fiscal-documento';
        $cfile = new \CURLFile($rutaPdf, 'application/pdf', basename($rutaPdf));
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => ['documento' => $cfile],
            CURLOPT_HTTPHEADER => ['X-API-Key: ' . $apiKey],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);
        $body = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        if ($body === false || $httpCode !== 200) {
            return ['valido' => false, 'mensaje' => $curlErr ?: 'La API no respondió correctamente (HTTP ' . $httpCode . ').'];
        }
        $data = json_decode($body, true);
        return is_array($data) ? $data : null;
    }

    /**
     * Llama a la API verificar-estado-cuenta (Python) con el PDF de estado de cuenta.
     * En subida la única validación para aceptar es: banco físico (no digital).
     * Si el PDF es escaneado (sin capa de texto), la API usa RapidOCR automáticamente para analizarlo.
     * La respuesta completa (banco, propietario, tiene_datos_titular) se guarda en verificacion_calidad_json
     * y se usa después en el tooltip (nombre banco, nombre dueño, mismo nombre que el ticket).
     * @param string $rutaPdf Ruta absoluta al PDF ya subido
     * @return array|null Respuesta JSON decodificada (valido, mensaje, banco_detectado, nombre_propietario, es_banco_fisico, tiene_datos_titular); null si error de config/conexión
     */
    private function verificarEstadoCuentaApi($rutaPdf)
    {
        $configFile = defined('RAIZ') ? (RAIZ . '/config/config.ini') : (__DIR__ . '/../config/config.ini');
        if (!is_file($configFile)) {
            return null;
        }
        $config = @parse_ini_file($configFile, true);
        $apiUrl = trim($config['doc_verificacion']['api_url'] ?? '');
        $apiKey = trim($config['doc_verificacion']['api_key'] ?? '');
        if ($apiUrl === '' || $apiKey === '') {
            return null;
        }
        $baseUrl = preg_replace('#/verificar\s*$#', '', $apiUrl);
        $url = rtrim($baseUrl, '/') . '/verificar-estado-cuenta';
        $cfile = new \CURLFile($rutaPdf, 'application/pdf', basename($rutaPdf));
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => ['documento' => $cfile],
            CURLOPT_HTTPHEADER => ['X-API-Key: ' . $apiKey],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);
        $body = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        if ($body === false || $httpCode !== 200) {
            return ['valido' => false, 'mensaje' => $curlErr ?: 'La API no respondió correctamente (HTTP ' . $httpCode . ').'];
        }
        $data = json_decode($body, true);
        return is_array($data) ? $data : null;
    }

    /**
     * Llama a la API verificar-nss-documento (Python). Valida que el PDF sea vigencia de derechos
     * o constancia de NSS del IMSS (formato como NSS.pdf). En subida solo se acepta ese formato.
     * @param string $rutaPdf Ruta absoluta al PDF ya subido
     * @return array|null Respuesta (nss_extraido, valido, mensaje); null si error de config/conexión
     */
    private function verificarNssApi($rutaPdf)
    {
        $configFile = defined('RAIZ') ? (RAIZ . '/config/config.ini') : (__DIR__ . '/../config/config.ini');
        if (!is_file($configFile)) {
            return null;
        }
        $config = @parse_ini_file($configFile, true);
        $apiUrl = trim($config['doc_verificacion']['api_url'] ?? '');
        $apiKey = trim($config['doc_verificacion']['api_key'] ?? '');
        if ($apiUrl === '' || $apiKey === '') {
            return null;
        }
        $baseUrl = preg_replace('#/verificar\s*$#', '', $apiUrl);
        $url = rtrim($baseUrl, '/') . '/verificar-nss-documento';
        $cfile = new \CURLFile($rutaPdf, 'application/pdf', basename($rutaPdf));
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => ['documento' => $cfile],
            CURLOPT_HTTPHEADER => ['X-API-Key: ' . $apiKey],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 45,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);
        $body = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        if ($body === false || $httpCode !== 200) {
            return ['valido' => false, 'mensaje' => $curlErr ?: 'La API no respondió correctamente (HTTP ' . $httpCode . ').'];
        }
        $data = json_decode($body, true);
        return is_array($data) ? $data : null;
    }

    /**
     * Llama a la API verificar-curp-documento (Python). Valida que el PDF sea constancia de CURP RENAPO.
     * @param string $rutaPdf Ruta absoluta al PDF ya subido
     * @return array|null Respuesta (curp_extraido, valido, mensaje, nombre); null si error de config/conexión
     */
    private function verificarCurpApi($rutaPdf)
    {
        $configFile = defined('RAIZ') ? (RAIZ . '/config/config.ini') : (__DIR__ . '/../config/config.ini');
        if (!is_file($configFile)) {
            return null;
        }
        $config = @parse_ini_file($configFile, true);
        $apiUrl = trim($config['doc_verificacion']['api_url'] ?? '');
        $apiKey = trim($config['doc_verificacion']['api_key'] ?? '');
        if ($apiUrl === '' || $apiKey === '') {
            return null;
        }
        $baseUrl = preg_replace('#/verificar\s*$#', '', $apiUrl);
        $url = rtrim($baseUrl, '/') . '/verificar-curp-documento';
        $cfile = new \CURLFile($rutaPdf, 'application/pdf', basename($rutaPdf));
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => ['documento' => $cfile],
            CURLOPT_HTTPHEADER => ['X-API-Key: ' . $apiKey],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 45,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);
        $body = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        if ($body === false || $httpCode !== 200) {
            return ['valido' => false, 'mensaje' => $curlErr ?: 'La API no respondió correctamente (HTTP ' . $httpCode . ').'];
        }
        $data = json_decode($body, true);
        return is_array($data) ? $data : null;
    }

    /**
     * Llama a la API verificar-calidad-identificacion-pdf (Python). Siempre acepta; devuelve notas de revisión.
     * @param string $rutaPdf Ruta absoluta al PDF ya subido (identificación oficial, 1 o 2 páginas)
     * @return array|null Respuesta decodificada (aceptado, notas, detalle_frente, detalle_reverso) o null si error
     */
    private function verificarCalidadIdentificacionPdfApi($rutaPdf)
    {
        $configFile = defined('RAIZ') ? (RAIZ . '/config/config.ini') : (__DIR__ . '/../config/config.ini');
        if (!is_file($configFile)) {
            return null;
        }
        $config = @parse_ini_file($configFile, true);
        $apiUrl = trim($config['doc_verificacion']['api_url'] ?? '');
        $apiKey = trim($config['doc_verificacion']['api_key'] ?? '');
        if ($apiUrl === '' || $apiKey === '') {
            return null;
        }
        $baseUrl = preg_replace('#/verificar\s*$#', '', $apiUrl);
        $url = rtrim($baseUrl, '/') . '/verificar-calidad-identificacion-pdf';
        $cfile = new \CURLFile($rutaPdf, 'application/pdf', basename($rutaPdf));
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => ['documento' => $cfile],
            CURLOPT_HTTPHEADER => ['X-API-Key: ' . $apiKey],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 90,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);
        $body = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($body === false || $httpCode !== 200) {
            return null;
        }
        $data = json_decode($body, true);
        return is_array($data) ? $data : null;
    }

    /**
     * Llama a la API verificar-calidad (revisión ligera: brillo, borroso). Solo para imágenes.
     * @param string $rutaArchivo Ruta absoluta al archivo ya subido
     * @param string $ext Extensión (jpg, jpeg, png, etc.)
     * @return array|null ['ok' => bool, 'mensaje' => string] o null si no hay API configurada o hay error
     */
    private function verificarCalidadDocumentoApi($rutaArchivo, $ext)
    {
        $configFile = defined('RAIZ') ? (RAIZ . '/config/config.ini') : (__DIR__ . '/../config/config.ini');
        if (!is_file($configFile)) {
            return null;
        }
        $config = @parse_ini_file($configFile, true);
        $apiUrl = trim($config['doc_verificacion']['api_url'] ?? '');
        $apiKey = trim($config['doc_verificacion']['api_key'] ?? '');
        if ($apiUrl === '' || $apiKey === '') {
            return null;
        }
        $baseUrl = preg_replace('#/verificar\s*$#', '', $apiUrl);
        $url = rtrim($baseUrl, '/') . '/verificar-calidad';
        $mime = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'tiff' => 'image/tiff',
        ];
        $cfile = new \CURLFile($rutaArchivo, $mime[$ext] ?? 'application/octet-stream', basename($rutaArchivo));
        $post = ['imagen' => $cfile];
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $post,
            CURLOPT_HTTPHEADER => ['X-API-Key: ' . $apiKey],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);
        $body = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode !== 200 || $body === false || $body === '') {
            return null;
        }
        $data = json_decode($body, true);
        if (!is_array($data) || !isset($data['ok'])) {
            return null;
        }
        return [
            'ok' => (bool) $data['ok'],
            'mensaje' => $data['mensaje'] ?? 'Verificación de calidad completada',
        ];
    }

    /**
     * Llama a la API de verificación de documentos (Python). Solo para imágenes.
     * @param string $rutaArchivo Ruta absoluta al archivo ya subido
     * @param string $ext Extensión del archivo (jpg, jpeg, png, etc.)
     * @return array|null ['resultado' => 'ORIGINAL'|'REVISION_MANUAL'|'RECHAZADO', 'mensaje' => string, 'score' => int] o null si no hay API configurada o hay error (usar fallback OcrIdentidad)
     */
    private function verificarDocumentoIdentidadApi($rutaArchivo, $ext)
    {
        $configFile = defined('RAIZ') ? (RAIZ . '/config/config.ini') : (__DIR__ . '/../config/config.ini');
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
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);
        $body = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
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
        $rutasParaValidar = ['identificacion_pdf' => null, 'curp' => null, 'nss' => null, 'constancia_fiscal' => null, 'acta_nacimiento' => null];

        // Documentos ya subidos (no exigir ni permitir reemplazo)
        $yaSubidos = [];
        $listaDocsExistentes = [];
        $resDocs = CandidatosDAO::getDocumentosCandidato($id_candidato);
        if ($resDocs['success'] && !empty($resDocs['datos'])) {
            $listaDocsExistentes = $resDocs['datos'];
            foreach ($resDocs['datos'] as $d) {
                $tipo = trim($d['tipo_documento'] ?? '');
                $num = array_search($tipo, $tiposDocumento, true);
                if ($num !== false) {
                    $yaSubidos[(int) $num] = true;
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
            if ($i === 6 && $ext !== 'pdf') {
                $errores[] = 'COMPROBANTE DE DOMICILIO: solo se acepta archivo PDF.';
                continue;
            }
            if ($i === 10 && $ext !== 'pdf') {
                $errores[] = 'ESTADO DE CUENTA: solo se acepta archivo PDF.';
                continue;
            }
            if ($i === 8 && $ext !== 'pdf') {
                $errores[] = 'NÚMERO DE SEGURIDAD SOCIAL: solo se acepta el PDF de vigencia de derechos del IMSS (descargado desde imss.gob.mx).';
                continue;
            }
            if ($i === 4 && $ext !== 'pdf') {
                $errores[] = 'CURP: solo se acepta archivo PDF (constancia del RENAPO).';
                continue;
            }
            if ($i === 5 && $ext !== 'pdf') {
                $errores[] = 'IDENTIFICACIÓN OFICIAL: solo se acepta un archivo PDF (con frente y reverso).';
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

            $verificacionFiscalJson = null;
            $verificacionCalidadJson = null;
            if ($i === 7 && $ext === 'pdf') {
                $apiFiscal = $this->verificarConstanciaFiscalApi($rutaDestino);
                if ($apiFiscal === null || empty($apiFiscal['valido'])) {
                    @unlink($rutaDestino);
                    $errores[] = 'CONSTANCIA DE SITUACIÓN FISCAL: ' . ($apiFiscal['mensaje'] ?? 'No se pudo verificar el documento.');
                    continue;
                }
                $verificacionFiscalJson = json_encode($apiFiscal);
            }

            if ($i === 10 && $ext === 'pdf') {
                $apiEstadoCuenta = $this->verificarEstadoCuentaApi($rutaDestino);
                if ($apiEstadoCuenta === null || empty($apiEstadoCuenta['valido'])) {
                    @unlink($rutaDestino);
                    $errores[] = 'ESTADO DE CUENTA: ' . ($apiEstadoCuenta['mensaje'] ?? 'No se pudo verificar el documento. Solo se aceptan bancos físicos de México.');
                    continue;
                }
                $verificacionCalidadJson = json_encode($apiEstadoCuenta);
            }

            if ($i === 8 && $ext === 'pdf') {
                $apiNss = $this->verificarNssApi($rutaDestino);
                if ($apiNss === null || empty($apiNss['valido'])) {
                    @unlink($rutaDestino);
                    $errores[] = 'NÚMERO DE SEGURIDAD SOCIAL: ' . ($apiNss['mensaje'] ?? 'Solo se acepta el PDF de vigencia de derechos del IMSS (descargado desde imss.gob.mx).');
                    continue;
                }
                $verificacionCalidadJson = json_encode($apiNss);
            }

            if ($i === 4 && $ext === 'pdf') {
                $apiCurp = $this->verificarCurpApi($rutaDestino);
                if ($apiCurp === null || empty($apiCurp['valido'])) {
                    @unlink($rutaDestino);
                    $errores[] = 'CURP: ' . ($apiCurp['mensaje'] ?? 'No se pudo verificar la constancia de CURP. Sube el PDF del RENAPO.');
                    continue;
                }
                $verificacionCalidadJson = json_encode($apiCurp);
            }

            if ($i === 5 && $ext === 'pdf') {
                $apiCalidad = $this->verificarCalidadIdentificacionPdfApi($rutaDestino);
                if ($apiCalidad !== null && is_array($apiCalidad)) {
                    $verificacionCalidadJson = json_encode($apiCalidad);
                } else {
                    // API no disponible o error: guardar nota para revisión manual sin fallar la subida
                    $verificacionCalidadJson = json_encode([
                        'aceptado' => true,
                        'notas' => ['No se pudo conectar con el sistema de revisión. Revisar identificación manualmente.'],
                        'detalle_frente' => null,
                        'detalle_reverso' => null,
                    ]);
                }
            }

            // Misma estrategia que carga_documento_persona: solo guardar ruta en BD, archivo en disco (sin BLOB = rápido).
            $guardar = CandidatosDAO::guardarDocumento($id_candidato, $nombreOriginal, $rutaRelativa, $tipoNombre, null, null, $verificacionFiscalJson, $verificacionCalidadJson);
            if ($guardar['success']) {
                $guardados++;
            }

            if ($i === 5) {
                $rutasParaValidar['identificacion_pdf'] = $rutaDestino;
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
        }

        // Reconstruir rutas desde BD actual (incluye lo recién subido) para poder ejecutar la API
        $resDocsParaValidar = CandidatosDAO::getDocumentosCandidato($id_candidato);
        $listaCompletaParaValidar = ($resDocsParaValidar['success'] && !empty($resDocsParaValidar['datos'])) ? $resDocsParaValidar['datos'] : [];
        foreach ($listaCompletaParaValidar as $d) {
            $rutaRel = trim($d['ruta_archivo'] ?? '');
            if ($rutaRel === '' || !is_file($storageRoot . '/' . $rutaRel)) {
                continue;
            }
            $pathAbs = $storageRoot . '/' . $rutaRel;
            $tipo = trim($d['tipo_documento'] ?? '');
            if ($tipo === 'IDENTIFICACIÓN OFICIAL') {
                $rutasParaValidar['identificacion_pdf'] = $pathAbs;
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

        // Rellenar rutas ya existentes para validar expediente (identificacion_pdf y PDFs)
        foreach ($listaDocsExistentes as $d) {
            $rutaRel = trim($d['ruta_archivo'] ?? '');
            if ($rutaRel === '' || !is_file($storageRoot . '/' . $rutaRel)) {
                continue;
            }
            $pathAbs = $storageRoot . '/' . $rutaRel;
            $tipo = trim($d['tipo_documento'] ?? '');
            if ($tipo === 'IDENTIFICACIÓN OFICIAL' && $rutasParaValidar['identificacion_pdf'] === null) {
                $rutasParaValidar['identificacion_pdf'] = $pathAbs;
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
                    $tipoUpper = mb_strtoupper($tipo);
                    $tipoNorm = preg_replace('/\s+/', ' ', $tipoUpper);
                    if (isset($tiposNombre[$tipoNorm])) {
                        $documentosSubidosPayload[$tiposNombre[$tipoNorm]] = ['nombre_archivo' => $d['nombre_archivo'] ?? 'documento'];
                    } else {
                        foreach ($tiposNombre as $nombre => $num) {
                            $nombreNorm = mb_strtoupper($nombre);
                            if ($tipoNorm === $nombreNorm || strpos($tipoNorm, $nombreNorm) !== false || strpos($nombreNorm, $tipoNorm) !== false) {
                                $documentosSubidosPayload[$num] = ['nombre_archivo' => $d['nombre_archivo'] ?? 'documento'];
                                break;
                            }
                        }
                    }
                }
            }
            $payload['documentos_subidos'] = $documentosSubidosPayload;
            $tieneLos10 = isset($documentosSubidosPayload[1]) && isset($documentosSubidosPayload[2]) && isset($documentosSubidosPayload[3])
                && isset($documentosSubidosPayload[4]) && isset($documentosSubidosPayload[5]) && isset($documentosSubidosPayload[6])
                && isset($documentosSubidosPayload[7]) && isset($documentosSubidosPayload[8]) && isset($documentosSubidosPayload[9])
                && isset($documentosSubidosPayload[10]);
            $payload['expediente_completo'] = $tieneLos10;
            $expedienteCompleto = $tieneLos10;
            // Notificar siempre que el expediente quede completo tras una subida
            // reemplaza un documento que Capital Humano había eliminado para corrección).
            error_log('CapHum::subirDocumentos: guardados=' . $guardados . ', expedienteCompleto=' . ($expedienteCompleto ? 'SI' : 'NO') . ', docsPayload=' . count($documentosSubidosPayload) . ', keys=' . implode(',', array_keys($documentosSubidosPayload)));
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
                $mensaje = 'El candidato ' . $nombreCompleto . ' ha cargado todos los documentos requeridos. Revisa su expediente.';
                $idPersonas = Notificacion::getPersonasConModulos([42]);
                error_log('CapHum::subirDocumentos: personas modulo 42: ' . json_encode($idPersonas));
                if (empty($idPersonas)) {
                    $idPersonas = Notificacion::getPersonasConModulos([4]);
                    error_log('CapHum::subirDocumentos: personas modulo 4 (fallback): ' . json_encode($idPersonas));
                }
                if (empty($idPersonas)) {
                    error_log('CapHum: expediente completo pero ningún usuario con módulo 42 (Candidatos) ni 4 (Capital Humano). Asigne el módulo 42 a al menos un usuario para recibir notificaciones.');
                } else {
                    $ok = Notificacion::crearParaPersonas($idPersonas, 'candidato_expediente_completo', $mensaje, null);
                    error_log('CapHum::subirDocumentos: notificación enviada a ' . count($idPersonas) . ' personas.');
                }
            }
        }
        if ($guardados > 0) {
            echo json_encode(self::respuesta(true, 'Se subieron ' . $guardados . ' documento(s) correctamente.', $payload));
        } else {
            echo json_encode(self::respuesta(false, count($errores) ? implode(', ', $errores) : 'No se envió ningún archivo. Selecciona al menos un documento.'));
        }

        // Verificación automática en background: enviar respuesta al cliente y seguir procesando
        if ($guardados > 0 && $expedienteCompleto) {
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            } else {
                if (ob_get_level() > 0) ob_end_flush();
                flush();
                if (function_exists('session_write_close')) session_write_close();
            }
            ignore_user_abort(true);
            set_time_limit(300);
            $verifExistente = CandidatosDAO::getVerificacionExpediente($id_candidato);
            if (empty($verifExistente)) {
                error_log('CapHum::subirDocumentos: lanzando verificación automática background para candidato ' . $id_candidato);
                $this->ejecutarVerificacionBackground($id_candidato);
            }
        }
        exit;
    }

    /**
     * Ejecutar la verificación de expediente contra la API de forma silenciosa (background).
     */
    private function ejecutarVerificacionBackground($id_candidato)
    {
        try {
            $storageRoot = defined('RAIZ') ? (RAIZ . '/storage') : (__DIR__ . '/../storage');
            $rutasParaValidar = ['identificacion_pdf' => null, 'curp' => null, 'nss' => null, 'constancia_fiscal' => null, 'acta_nacimiento' => null];
            $resDocs = CandidatosDAO::getDocumentosCandidato($id_candidato);
            if (!$resDocs['success'] || empty($resDocs['datos'])) {
                error_log('CapHum::verificacionBackground: sin documentos para candidato ' . $id_candidato);
                return;
            }
            foreach ($resDocs['datos'] as $d) {
                $rutaRel = trim($d['ruta_archivo'] ?? '');
                if ($rutaRel === '' || !is_file($storageRoot . '/' . $rutaRel)) continue;
                $pathAbs = $storageRoot . '/' . $rutaRel;
                $tipo = trim($d['tipo_documento'] ?? '');
                if ($tipo === 'IDENTIFICACIÓN OFICIAL') $rutasParaValidar['identificacion_pdf'] = $pathAbs;
                elseif ($tipo === 'CURP') $rutasParaValidar['curp'] = $pathAbs;
                elseif ($tipo === 'NÚMERO DE SEGURIDAD SOCIAL') $rutasParaValidar['nss'] = $pathAbs;
                elseif ($tipo === 'CONSTANCIA DE SITUACION FISCAL') $rutasParaValidar['constancia_fiscal'] = $pathAbs;
                elseif ($tipo === 'ACTA DE NACIMIENTO' || $tipo === 'ACTA DE NACIMIENTO Certificada') $rutasParaValidar['acta_nacimiento'] = $pathAbs;
            }
            if (!$rutasParaValidar['identificacion_pdf']) {
                error_log('CapHum::verificacionBackground: falta identificación oficial (PDF) para candidato ' . $id_candidato);
                return;
            }
            $resultadoApi = $this->validarExpedienteApi($rutasParaValidar);
            if (is_array($resultadoApi) && !isset($resultadoApi['error'])) {
                $payload = [
                    'todo_coincide' => $resultadoApi['todo_coincide'] ?? false,
                    'foto_rechazada' => $resultadoApi['foto_rechazada'] ?? false,
                    'curp_definitivo' => $resultadoApi['curp_definitivo'] ?? null,
                    'checks_ok' => $resultadoApi['checks_ok'] ?? 0,
                    'checks_totales' => $resultadoApi['checks_totales'] ?? 0,
                    'alertas' => $resultadoApi['alertas'] ?? [],
                    'identificacion_frente_score' => $resultadoApi['identificacion_frente_score'] ?? null,
                    'identificacion_reverso_score' => $resultadoApi['identificacion_reverso_score'] ?? null,
                    'comparaciones' => $resultadoApi['comparaciones'] ?? null,
                    'nombre_ocr' => $resultadoApi['nombre_ocr'] ?? null,
                    'anio_nacimiento' => $resultadoApi['anio_nacimiento'] ?? null,
                    'tipo_documento' => $resultadoApi['tipo_documento'] ?? null,
                ];
                CandidatosDAO::updateVerificacionExpediente($id_candidato, json_encode($payload));
                error_log('CapHum::verificacionBackground: OK para candidato ' . $id_candidato);
            } else {
                $err = is_array($resultadoApi) ? ($resultadoApi['error'] ?? 'desconocido') : 'null';
                error_log('CapHum::verificacionBackground: error API para candidato ' . $id_candidato . ': ' . $err);
            }
        } catch (\Exception $e) {
            error_log('CapHum::verificacionBackground: excepción candidato ' . $id_candidato . ': ' . $e->getMessage());
        }
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
        $repoRoot = defined('RAIZ') ? dirname(RAIZ) : dirname(__DIR__, 2);
        $autoload = $repoRoot . '/vendor/autoload.php';
        if (!is_file($autoload)) {
            $this->enviarCorreoUltimoError = 'PHPMailer no encontrado (composer).';
            error_log('CapHum::enviarCorreo: vendor/autoload no encontrado: ' . $autoload);
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
            $langPath = $repoRoot . '/vendor/phpmailer/phpmailer/language/';
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

            const getUsuarios = (opts) => {
            opts = opts || {};
            http.request({
                endpoint: "/caphum/getUsuarios",
                showLoader: opts.showLoader !== false,
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
        self::set("titulo", "Control de Bajas");
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

    public function forzarCierreSesionUsuario()
    {
        if (empty($_SESSION['usuario_id'])) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Sesión no válida.']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $idPersona = isset($input['idPersona']) ? (int) $input['idPersona'] : 0;

        if ($idPersona <= 0) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'ID de persona no recibido.']);
            return;
        }

        $idSesion = (int) $_SESSION['usuario_id'];
        $res = CapHumDAO::forzarLogoutPersona($idPersona);

        if (!empty($res['success']) && $idPersona === $idSesion && $idSesion > 0) {
            $out = array_merge($res, [
                'cerrar_sesion_inmediata' => true,
                'redirect' => '/Login',
            ]);
            $_SESSION = [];
            session_unset();
            session_destroy();
            self::respuestaJSON($out);
        }

        self::respuestaJSON($res);
    }

    public function getDepartamento()
    {
        self::respuestaJSON(
            CapHumDAO::getConsultaDepartamentoGestor($_SESSION['usuario_id'])
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

            // Refresco inmediato de sesión cuando el usuario edita sus propios módulos.
            if ((int) ($_SESSION['usuario_id'] ?? 0) === (int) $idPersona) {
                $_SESSION['modulos'] = array_values(
                    array_map('intval', (array) LoginDao::getModulosUsuario((int) $idPersona))
                );
            }

            self::respuestaJSON([
                'success' => true,
                'mensaje' => $asignado
                    ? 'Módulo asignado correctamente'
                    : 'Módulo eliminado correctamente'
            ]);
        } catch (\Exception $e) {
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
        $filtrarPorPrivilegios = !empty($input['filtrar_por_privilegios']);

        if (empty($id)) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'Id de departamento requerido'
            ]);
            return;
        }

        if ($filtrarPorPrivilegios && !empty($_SESSION['usuario_id'])) {
            $resultado = CapHumDAO::getConsultaPuestosParaGestor($id, $_SESSION['usuario_id']);
        } else {
            $resultado = CapHumDAO::getConsultaPuestos($id);
        }

        self::respuestaJSON($resultado);
    }

    /**
     * Puestos de un departamento que el usuario en sesión tiene en Acceso a Puestos.
     * Usado por Gestión de Usuarios (all_gestores) para que el combo Puesto solo muestre puestos permitidos.
     */
    public function getPuestosParaGestor()
    {
        $input = json_decode(file_get_contents("php://input"), true) ?: [];
        $id = $input['id_departamento'] ?? null;

        if (empty($id)) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Id de departamento requerido']);
            return;
        }

        $idPersona = (int) ($_SESSION['usuario_id'] ?? 0);
        if ($idPersona <= 0) {
            self::respuestaJSON(['success' => true, 'mensaje' => 'Puestos encontrados.', 'datos' => []]);
            return;
        }

        $resultado = CapHumDAO::getConsultaPuestosParaGestor($id, $idPersona);
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

        // Mismo permiso que Gestiones: solo quien tiene Organización - Departamentos (módulo 10) puede editar
        $modulos = $_SESSION['modulos'] ?? [];
        if (!in_array(10, $modulos)) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'No tiene permiso para editar. Solo usuarios con acceso a Organización - Departamentos pueden modificar la información.'
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
        $puedeEditarTodos = in_array(10, $modulos); // Organización - Departamentos (igual que en Gestiones)

        self::set("titulo", "Organigrama Cobranza");
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

            $directorio = sparta_uploads_join('bajas') . DIRECTORY_SEPARATOR;
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
            $directorio = sparta_uploads_join('reingresos') . DIRECTORY_SEPARATOR;
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

            $directorio = sparta_uploads_join('bajas') . DIRECTORY_SEPARATOR;
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
                header('Location: /inicio');
                exit;
            }

            $rutaArchivo = sparta_uploads_join('bajas', basename($nombreArchivo));

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
            $directorio = sparta_uploads_join($carpeta) . DIRECTORY_SEPARATOR;
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
                header('Location: /inicio');
                exit;
            }

            $nombreArchivo = basename($nombreArchivo);
            $carpetas = [
                sparta_uploads_join('documentos') . DIRECTORY_SEPARATOR,
                sparta_uploads_join('bajas') . DIRECTORY_SEPARATOR,
                sparta_uploads_join('reingresos') . DIRECTORY_SEPARATOR,
            ];
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

    public function estadisticas()
    {
        $anio = (int) date('Y');
        $mes = (int) date('n');
        $hoy = new \DateTimeImmutable('today');
        $dow = (int) $hoy->format('N');
        $lun = $hoy->modify('-' . ($dow - 1) . ' days');
        $fiSem = $lun->format('Y-m-d');
        $ffSem = $hoy->format('Y-m-d');
        $res = \Models\CapHumEstadisticas::getDatosPanel($anio, $mes, 0, $fiSem, $ffSem);
        $datos = ($res['success'] ?? false) ? ($res['datos'] ?? []) : [];
        self::set('titulo', 'Estadísticas Capital Humano');
        self::set('anioDefault', $anio);
        self::set('mesDefault', $mes);
        self::set('semanaDefault', 0);
        self::set('chEstRangoIni', $fiSem);
        self::set('chEstRangoFin', $ffSem);
        self::set('datosInicialesJson', json_encode($datos, JSON_UNESCAPED_UNICODE));
        self::render('caphum_estadisticas');
    }

    public function getEstadisticasPanel()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            if (function_exists('set_time_limit')) {
                @set_time_limit(180);
            }
            $raw = file_get_contents('php://input');
            $body = json_decode($raw ?: '[]', true);
            if (!is_array($body)) {
                $body = [];
            }
            $anio = (int) ($body['anio'] ?? date('Y'));
            $mes = (int) ($body['mes'] ?? date('n'));
            $semana = (int) ($body['semana'] ?? 0);
            if ($anio < 2000 || $anio > 2100) {
                $anio = (int) date('Y');
            }
            if ($mes < 1 || $mes > 12) {
                $mes = (int) date('n');
            }
            if ($semana < 0 || $semana > 4) {
                $semana = 0;
            }
            $fiCal = trim((string) ($body['fecha_inicio'] ?? ''));
            $ffCal = trim((string) ($body['fecha_fin'] ?? ''));
            $usaCal = ($fiCal !== '' && $ffCal !== ''
                && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fiCal)
                && preg_match('/^\d{4}-\d{2}-\d{2}$/', $ffCal));
            if ($usaCal) {
                [$fiCal, $ffCal] = self::capFechasEstadisticasChHastaHoy($fiCal, $ffCal);
            }
            $res = $usaCal
                ? \Models\CapHumEstadisticas::getDatosPanel($anio, $mes, $semana, $fiCal, $ffCal)
                : \Models\CapHumEstadisticas::getDatosPanel($anio, $mes, $semana);
            echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'mensaje' => 'Error al generar el panel de estadísticas.',
                'error' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        }
        exit;
    }

    /** Desglose por departamento de ingresos / bajas / reingresos (mismo rango que el panel). */
    public function getEstadisticasMovimientoDetalle()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $raw = file_get_contents('php://input');
            $body = json_decode($raw ?: '[]', true);
            if (!is_array($body)) {
                $body = [];
            }
            $tipo = (string) ($body['tipo'] ?? '');
            $anio = (int) ($body['anio'] ?? date('Y'));
            $mes = (int) ($body['mes'] ?? date('n'));
            $semana = (int) ($body['semana'] ?? 0);
            if ($anio < 2000 || $anio > 2100) {
                $anio = (int) date('Y');
            }
            if ($mes < 1 || $mes > 12) {
                $mes = (int) date('n');
            }
            if ($semana < 0 || $semana > 4) {
                $semana = 0;
            }
            $fiCal = trim((string) ($body['fecha_inicio'] ?? ''));
            $ffCal = trim((string) ($body['fecha_fin'] ?? ''));
            $usaCal = ($fiCal !== '' && $ffCal !== ''
                && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fiCal)
                && preg_match('/^\d{4}-\d{2}-\d{2}$/', $ffCal));
            if ($usaCal) {
                [$fiCal, $ffCal] = self::capFechasEstadisticasChHastaHoy($fiCal, $ffCal);
            }
            $res = $usaCal
                ? \Models\CapHumEstadisticas::getMovimientoPorDepartamento($tipo, $anio, $mes, $semana, $fiCal, $ffCal)
                : \Models\CapHumEstadisticas::getMovimientoPorDepartamento($tipo, $anio, $mes, $semana);
            echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'mensaje' => 'Error al generar el desglose por departamento.',
                'error' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        }
        exit;
    }

    /**
     * Estadísticas CH: no permitir fechas posteriores a hoy (ni por API ni por desajuste de reloj).
     *
     * @return array{0: string, 1: string} [Y-m-d, Y-m-d]
     */
    private static function capFechasEstadisticasChHastaHoy(string $fi, string $ff): array
    {
        try {
            $h = new \DateTimeImmutable('today');
            $dFi = new \DateTimeImmutable($fi);
            $dFf = new \DateTimeImmutable($ff);
            if ($dFf > $h) {
                $dFf = $h;
            }
            if ($dFi > $h) {
                $dFi = $h;
            }
            if ($dFf < $dFi) {
                $dFf = $dFi;
            }

            return [$dFi->format('Y-m-d'), $dFf->format('Y-m-d')];
        } catch (\Throwable $e) {
            return [$fi, $ff];
        }
    }
}
