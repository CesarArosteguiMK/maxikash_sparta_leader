<?php

namespace controllers;

use Core\Controller;
use Models\Empresa as EmpresaDAO;

class Departamentos extends Controller
{
    public function consulta()
    {
        $script = <<<HTML
        <script>
            const tabla = "#historialSolicitudes"

            const getSolicitudes = () => {
                
                consultaServidor("/departamentos/getPuestosPorDepartamento", { idDepartamento: 5 }, (respuesta) => {
                    if (!respuesta.success) return showError(respuesta.mensaje);

                   const datos = respuesta.datos.map(d => [
                        '', // columna 0
                        d.nombre ?? '',     // columna 1
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
        self::render("departamentos_all");
    }

    public function getDepartamentos()
    {
        self::respuestaJSON(EmpresaDAO::getConsultaDepartamentos());
    }

}
