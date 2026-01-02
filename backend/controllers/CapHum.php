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
                        nombre: `${p.nombres} ${p.apellidop} ${p.apellidom}`.trim(),
                        departamento: p.nombre_departamento,
                        puesto: p.nombre_puesto,
                        estatus: p.estatus,
                        acciones: `
                        <button class="btn btn-sm btn-primary me-1" onclick="editar(${p.id})">
                            <i class="fa fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-info me-1" onclick="verArchivo(${p.id})">
                            <i class="fa fa-file"></i>
                        </button>
                        <button class="btn btn-sm btn-warning me-1" onclick="eliminar(${p.id})">
                            <i class="fa fa-person-circle-minus"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="baja_gestor(${p.id})">
                            <i class="fa fa-user-slash"></i>
                        </button>`
                    }));
        
                    // Actualizar DataTable
                    const tabla = $('#historialUsuarios').DataTable();
                    tabla.clear().rows.add(datos).draw();
                }
            });
        };
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
                        
                        cargarDepartamentosCombo(persona.id_departamento, persona.id_departamento);
                        cargarPuestosCombo(persona.id_departamento, persona.id_puesto);
                        cargarComboJefeDirecto(persona.id_departamento, persona.id_jefe);
                
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
                
                
                
            
            
            $(document).ready(() => {
            // Inicializar DataTable con columnas explícitas
            $(document).ready(() => {
                configuraTabla("#historialUsuarios", { registrosPorPagina: 6 });
                getUsuarios();
            });
        
            getUsuarios();
        });
            
            

        </script>
        HTML;

        self::set("titulo", "Consulta Usuarios");
        self::set("script", $script);
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
                'estatus' => ($p['estatus'] ?? 0) == 1 ? 'Activo' : 'Inactivo'
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
    public function getDepartamento()
    {
        $input = json_decode(file_get_contents("php://input"), true);
        $id = $input['id'] ?? null;

        if (!$id) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'Tipo de departamento requerido'
            ]);
            return;
        }

        self::respuestaJSON(
            CapHumDAO::getComboDepartamentos($id)
        );
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
        $idPuesto = $input['id_departamento'] ?? null;

        if (!$idPuesto) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'ID de persona no recibido'
            ]);
            return;
        }

        $detalles = CapHumDAO::getConsultaJefe($idPuesto);

        self::respuestaJSON($detalles);
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




}
