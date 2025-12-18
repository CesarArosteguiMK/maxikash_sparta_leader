<?php

namespace Controllers;

use Core\Controller;
use Models\CapHum as CapHumDAO;
use Models\Empresa as EmpresasDAO;

class CapHum extends Controller
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

            </script>
        HTML;
        $departamento = EmpresasDAO::getConsultaDepartamentoGestor($_SESSION['usuario_id']);
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
    public function Organigrama()
    {
        $script = <<<HTML
            <script>
               
            </script>
        HTML;

        $departamentos = EmpresasDAO::getConsultaDepartamentoGestor($_SESSION['departamento']);

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

    private function recorrerArbol($nodo, &$rows, $jefeNombre = null) {
        $nombre = $nodo["nombre"];

        $rows[] = [$nombre, $jefeNombre];

        if (isset($nodo["subordinados"]) && is_array($nodo["subordinados"])) {
            foreach ($nodo["subordinados"] as $sub) {
                $this->recorrerArbol($sub, $rows, $nombre);
            }
        }
    }

    public function nivelJerarquicoColaborador($persona_id)
    {
        // 1️⃣ Obtener el organigrama desde la DAO
        $personas = CapHumDAO::getConsultaPersonasJerarquia($persona_id);
        $organigramaJson = $personas["datos"][0]["organigrama_json"];
        $organigrama = json_decode($organigramaJson, true);

        // 2️⃣ Construir filas para el OrgChart
        $rows = [];

        // Recorrer los subordinados del nodo raíz
        if (!empty($organigrama["subordinados"])) {
            foreach ($organigrama["subordinados"] as $sub) {
                // Llamada a función recursiva que llena $rows
                $this->recorrerArbol($sub, $rows, "JEFE " . $organigrama["id_jefe"]);
            }
        }

        // Agregar la raíz del organigrama al inicio
        array_unshift($rows, [
            "JEFE " . $organigrama["id_jefe"], // Nombre del jefe raíz
            null                                // La raíz no tiene jefe
        ]);

        // 3️⃣ Devolver JSON para que el JS lo procese
        header('Content-Type: application/json');
        echo json_encode([
            "success" => true,
            "rows"    => $rows
        ]);
        exit;
    }

    public static function getPuestosCascadaDepartamento()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['id_departamento'])) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Departamento inválido'
            ]);
            return;
        }

        $puestos = EmpresasDAO::getConsultaPuestos($data['id_departamento']);

        echo json_encode([
            'success' => true,
            'datos' => $puestos['datos']
        ]);
    }
    public function getDepartamentos()
    {
        self::respuestaJSON(EmpresasDAO::getConsultaDepartamentos($_POST));
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

        $detalles = EmpresasDAO::getPersonasDetalle($idPersona);

        self::respuestaJSON($detalles);
    }


    public function getJefeDirecto()
    {
        $input = json_decode(file_get_contents("php://input"), true);
        $idPuesto = $input['id_departamento'] ?? null;

        if (!$idPuesto) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'ID de persona no recibido'
            ]);
            return;
        }

        $detalles = CapHumDAO::getConsultaGestoresDepartamento($idPuesto);

        self::respuestaJSON($detalles);
    }

    public function getInsertarGestor()
    {
        // Obtener el JSON enviado desde fetch
        $inputJSON = file_get_contents('php://input');
        $data = json_decode($inputJSON, true);

        // Validaciones básicas
        $requiredFields = ['nombres', 'apellidop', 'apellidom', 'telefono', 'puesto_id', 'jefe_id', 'departamento_id', 'usuario', 'contrasena'];
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
        $data['contrasena'] =$data['contrasena']; // hash de seguridad
        $data['id_jefe'] = isset($data['id_jefe']) ? $data['id_jefe'] : null;

        // Llamar al DAO
        $inserted = CapHumDAO::insertPersona($data);

        if ($inserted['success']) {
            echo json_encode(['success' => true, 'mensaje' => 'Gestor insertado correctamente']);
        } else {
            echo json_encode(['success' => false, 'mensaje' => 'Error al insertar gestor', 'error' => $inserted['error']]);
        }
    }

    public function updateGestorF()
    {
        $inputJSON = file_get_contents('php://input');
        $data = json_decode($inputJSON, true);

        // Validaciones básicas
        $requiredFields = ['nombres', 'apellidop', 'apellidom', 'puesto_id', 'departamento_id', 'jefe_id','usuario'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                echo json_encode(['success' => false, 'mensaje' => "El campo $field es obligatorio"]);
                return;
            }
        }

        // Solo hash si se envía contraseña
        if (!empty($data['contrasena'])) {
            $data['contrasena'] = $data['contrasena'];
        }

        $data['id_jefe'] = $data['id_jefe'] ?? null;

        // Llamada al DAO
        $updated = CapHumDAO::UpdatePersona($data);

        if ($updated['success']) {
            echo json_encode(['success' => true, 'mensaje' => 'Gestor actualizado correctamente']);
        } else {
            echo json_encode(['success' => false, 'mensaje' => 'Error al actualizar gestor', 'error' => $updated['error']]);
        }
    }


    public function getDepartamentosGestor()
    {
        $input = json_decode(file_get_contents("php://input"), true);
        $id = 3;

        if (!$id) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'Tipo de departamento requerido'
            ]);
            return;
        }

        self::respuestaJSON(
            EmpresasDAO::getConsultaDepartamentoGestor($id)
        );
    }

    public function getPuestosDepartamento()
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
        $resultado = EmpresasDAO::getConsultaPuestos($id);

        self::respuestaJSON($resultado);
    }

    public function getObtenerPersonasPorDepartamento()
    {
        // Obtener parámetro enviado por POST (o GET según tu setup)
        $idDepartamento = $_POST['idDepartamento'] ?? null;

        // Pasar el parámetro a DAO
        $puestos = CapHumDAO::getPersonasMayorRangoPorDepartamento($idDepartamento, 0, $_SESSION['usuario_id']);

        self::respuestaJSON($puestos);
    }




    ////////////////////////////////////////////////////////////////////// VALIDADO AL 100
    ////////////////////////////////////////////////////////////////////// VALIDADO AL 100
    ////////////////////////////////////////////////////////////////////// VALIDADO AL 100
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

        // 👇 OJO AQUÍ
        if (!$resp['success']) {
            echo json_encode($resp);
            return;
        }

        echo json_encode([
            'success' => true,
            'datos'   => $resp['datos']
        ]);
    }



}
