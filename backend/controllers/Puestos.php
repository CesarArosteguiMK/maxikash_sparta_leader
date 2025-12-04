<?php

namespace Controllers;

use Core\Controller;
use Models\Empresa as EmpresaDAO;

class Puestos extends Controller
{
    public function consulta()
    {
        $script = <<<HTML
        <script>
            const tabla = "#historialSolicitudes"

            const getSolicitudes = () => {
                
                consultaServidor("/puestos/getPuestos", { idDepartamento: null }, (respuesta) => {
                    if (!respuesta.success) return showError(respuesta.mensaje);

                   const datos = respuesta.datos.map(d => [
                        '', // columna 0
                        d.nombre ?? '',     // columna 1
                        d.departamento ?? '',     // columna 1
                        d.activo == 1 ? "Activo" : "Inactivo", // columna 2
                        `<button class="btn btn-sm btn-primary">Editar</button>` // columna 3
                    ]);

                    actualizaDatosTabla(tabla, datos)
                })
            }

            $(document).ready(() => {
                configuraTabla(tabla)
                getSolicitudes()
            });
        </script>
    HTML;

        self::set("titulo", "Solicitud de Viáticos");
        self::set("script", $script);
        self::render("puestos_all");
    }

    public function getPuestos()
    {
        // Obtener parámetro enviado por POST (o GET según tu setup)
        $idDepartamento = $_POST['idDepartamento'] ?? null;

        // Pasar el parámetro a DAO
        $puestos = EmpresaDAO::getConsultaPuestos($idDepartamento);

        self::respuestaJSON($puestos);
    }

}
