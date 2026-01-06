<?php

namespace Controllers;

use Core\Controller;
use Models\CapHum as CapHumDAO;
use Models\Empresa as EmpresasDAO;

class CapHumCopia extends Controller
{
    public function Gestion()
    {
        $script = <<<HTML
            <script> 
                function cargarDepartamentosPorTipo(id, seleccionado = null) {
                    fetch('/CapHum/getDepartamentosGestor', {
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
                function cargarPuestosPorDepartamentos(id_departamento, seleccionado = null) {
                    fetch('/CapHum/getPuestosDepartamento', {
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
                function cargarJefeDirecto(id_departamento, seleccionado = null) {
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
                
                function cargarJefesPorPuesto(idPuesto, seleccionado = null) {
                    fetch('/CapHum/getJefesPorPuesto', {  // Endpoint que devuelve jefes
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: idPuesto })
                    })
                    .then(res => res.json())
                    .then(data => {
                
                        if (!data.success) {
                            Swal.fire("Error", data.mensaje, "error");
                            return;
                        }
                
                        const select = document.getElementById('add_id_jefe');
                        select.innerHTML = '<option value="">Seleccione un jefe</option>';
                
                        data.datos.forEach(jefe => {
                            const option = document.createElement('option');
                            option.value = jefe.id;
                            option.textContent = jefe.nombre;
                
                            if (String(jefe.id) === String(seleccionado)) {
                                option.selected = true;
                            }
                
                            select.appendChild(option);
                        });
                    })
                    .catch(err => console.error('Error al cargar jefes:', err));
                }
                
               
                                
               

                function editar(id) {
                    console.log("ID recibido:", id);
                
                    if (!id) {
                        Swal.fire("Error", "ID inválido", "error");
                        return;
                    }
                
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
                                        
                        cargarDepartamentosPorTipo(persona.id_departamento, persona.id_departamento);
                        cargarPuestosPorDepartamentos(persona.id_departamento, persona.id_puesto);
                        cargarJefeDirecto(persona.id_departamento, persona.id_jefe);
                
                        // CAMPOS TEXTO
                        document.getElementById("edit_num_empleado").value = persona.numero_empleado;
                        document.getElementById("edit_id").value = persona.id;
                        document.getElementById("edit_nombres").value = persona.nombres ?? '';
                        document.getElementById("edit_apellidop").value = persona.apellidop ?? '';
                        document.getElementById("edit_apellidom").value = persona.apellidom ?? '';
                        document.getElementById("edit_telefono").value = persona.telefono ?? '';
                        document.getElementById("edit_usuario").value = persona.user_name ?? '';
                        document.getElementById("edit_contrasena").value = persona.password ?? '';
                
                       // SELECTS
                        document.getElementById("edit_departamento_id").value = persona.id_departamento;
                        document.getElementById("edit_id_puesto").value = persona.id_puesto;
                
                        

                
                        // MOSTRAR OFFCANVAS
                        const offcanvas = new bootstrap.Offcanvas(
                            document.getElementById('offcanvasEditUser')
                        );
                        offcanvas.show();
                    })
                    .catch(err => {
                        console.error('FETCH ERROR:', err);
                        Swal.fire("Error", "No se pudo cargar la información", "error");
                    });
                }
                
                function UpdateGestor() {
                    const payload = {
                        id: document.getElementById("edit_id").value,
                        nombres: document.getElementById("edit_nombres").value,
                        apellidop: document.getElementById("edit_apellidop").value,
                        apellidom: document.getElementById("edit_apellidom").value,
                        telefono: document.getElementById("edit_telefono").value,
                        departamento_id: document.getElementById("edit_departamento_id").value,
                        puesto_id: document.getElementById("edit_id_puesto").value,
                        jefe_id: document.getElementById("edit_id_jefe").value,
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
                
                        // Opcional: recargar tabla
                        // cargarGestores();
                    });
                }

                function baja_gestor(id) {
                    console.log("ID recibido:", id);
                
                    if (!id) {
                        Swal.fire("Error", "ID inválido", "error");
                        return;
                    }
                
                    fetch('/CapHum/getDetalles', {
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
                
                        const persona = data.datos;
                
                        // Debug rápido
                        console.log('Persona:', persona);
                
                        document.getElementById("edit_id").value = persona.id;
                        // Concatenar el nombre completo en el <p id="gestor">
                        document.getElementById("gestor").innerHTML = "<strong>Gestor:</strong> " + persona.nombres + " " + persona.apellidop + " " + persona.apellidom;
                        
                        $("#modalRFC").modal("show");
                    })
                    .catch(err => {
                        console.error('FETCH ERROR:', err);
                        Swal.fire("Error", "No se pudo cargar la información", "error");
                    });
        }
        
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
                        num_empleado,
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
            
            
            
            
            ///////////////////////////////////////////////////////////////////////////////////////////////////////
            //////////////////////////////////////////////////////////////////////////////////////////////////////
            ////////////////////////////////////////////////////////////////////////////////////// VALIDADO AL 100
            
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
            
                fetch('/CapHum/getPuestosCascadaDepartamento', {
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
                
                
                 ///////////////////////////////////////////////////////////////////////////////////////////////////////
            //////////////////////////////////////////////////////////////////////////////////////////////////////
            ////////////////////////////////////////////////////////////////////////////////////// NUEVO SCRIPT COMO DEP
                
                
                
                
                

            </script>
        HTML;
        $departamento = CapHumDAO::getConsultaDepartamentoGestor($_SESSION['usuario_id']);
        $gestores = CapHumDAO::getConsultaGestoresAll($_SESSION['usuario_id']);







        $tablaGestores = "";

        // Validar que existan datos
        if (!empty($gestores["datos"]) && is_array($gestores["datos"])) {

            foreach ($gestores["datos"] as $value) {

                $nombreCompleto = "{$value['nombres']} {$value['apellidop']} {$value['apellidom']}";

                $estatus = ($value["estatus"] === "Activo")
                    ? '<span class="badge bg-label-success">Activo</span>'
                    : '<span class="badge bg-label-danger">Inactivo</span>';

                $acciones = <<<HTML
                    <button class="btn btn-sm btn-primary me-1" onclick="editar({$value['id']})">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-info me-1" onclick="editar({$value['id']})">
                        <i class="fa fa-file"></i>
                    </button>
                    <button class="btn btn-sm btn-warning" onclick="eliminar({$value['id']})">
                        <i class="fa fa-person-circle-minus"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="baja_gestor({$value['id']})">
                        <i class="fa fa-user-slash"></i>
                    </button>
HTML;

                $tablaGestores .= <<<HTML
                    <tr>
                        <td>{$nombreCompleto}</td>
                        <td>{$value['nombre_departamento']}</td>
                        <td>{$value['nombre_puesto']}</td>
                        <td>{$estatus}</td>
                        <td>{$acciones}</td>
                    </tr>
HTML;
            }
        }

        self::set("titulo", "Organigrama");
        self::set("script", $script);
        self::set("departamento", $departamento);
        self::set("puestos", $puestos);
        self::set('tablaGestores', $tablaGestores);
        self::render("AllGestores");
    }



    public function getGestores() {
        session_start();
        // Obtenemos todos los gestores del usuario logueado
        $gestores = CapHumDAO::getConsultaGestoresAll($_SESSION['usuario_id']);

        // Transformamos los datos al formato esperado por DataTable/JS
        $datos = array_map(function($p) {
            $nombreCompleto = trim("{$p['nombres']} {$p['apellidop']} {$p['apellidom']}");
            $estatus = $p['estatus'] == 1 ? "Activo" : "Inactivo";

            return [
                '', // columna control/responsive
                $nombreCompleto,
                $p['nombre_departamento'] ?? '',
                $p['nombre_puesto'] ?? '',
                $estatus,
                "<button class='btn btn-sm btn-primary btn-editar'
                         data-id='{$p['id']}'
                         data-nombre='{$nombreCompleto}'>
                     Editar
                 </button>"
            ];
        }, $gestores);

        // Retornamos JSON
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'datos' => $datos]);
    }












































































    ////////////////////////////////////////////////////////////////////// VALIDADO AL 100
    ////////////////////////////////////////////////////////////////////// VALIDADO AL 100
    ////////////////////////////////////////////////////////////////////// VALIDADO AL 100

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






}
