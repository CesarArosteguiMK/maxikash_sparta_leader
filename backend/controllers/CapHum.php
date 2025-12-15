<?php

namespace Controllers;

use Core\Controller;
use Models\CapHum as CapHumDAO;
use Models\Empresa as EmpresaDAO;
use Models\Empresa as EmpresasDAO;

class CapHum extends Controller
{

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

    public function getDepartamentos()
    {
        self::respuestaJSON(EmpresasDAO::getConsultaDepartamentos($_POST));
    }

    public function getObtenerPersonasPorDepartamento()
    {
        // Obtener parámetro enviado por POST (o GET según tu setup)
        $idDepartamento = $_POST['idDepartamento'] ?? null;

        // Pasar el parámetro a DAO
        $puestos = CapHumDAO::getPersonasMayorRangoPorDepartamento($idDepartamento, 0, $_SESSION['usuario_id']);

        self::respuestaJSON($puestos);
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

    public function Gestion()
    {
        $script = <<<HTML
            <script>
               function darbaja(id) {
                console.log("ID seleccionado:", id);
                // Abrir modal
                $("#modalRFC").modal("show");
                
            }
            
            function editar(id) {
                console.log("ID seleccionado:", id);
                // Abrir modal
                $("#modalPersonaEdita").modal("show");
                
            }
            
           
            </script>
        HTML;

        $gestores = CapHumDAO::getConsultaGestores($_SESSION['usuario_id']);

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
            <button class="btn btn-sm btn-danger" onclick="darbaja({$value['id']})">
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
        self::set('tablaGestores', $tablaGestores);
        self::render("AllGestores");
    }

}
