<?php

namespace Controllers;

use Core\Controller;
use Models\Empresa as EmpresasDAO;

class Empresas extends Controller
{
    public function existentes()
    {
        $script = <<<HTML
            <script>
                const tabla = "#historialSolicitudes"
                const getSolicitudes = () => {
                    
                    const parametros = {
                        usuario: $_SESSION[usuario_id]
                    }

                    consultaServidor("/Empresas/ConsultaDepartamentos", parametros, (respuesta) => {
                        if (!respuesta.success) return showError(respuesta.mensaje)  
                        
                        const datos = respuesta.datos.map(empresas => {
                            return [
                                 null,
                                empresas.NOMBRE,
                                empresas.RFC,
                                empresas.RAZON_SOCIAL,
                                empresas.ESTATUS
                            ]
                        });

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
        self::render("empresas_all");
    }

    public function getDepartamentos()
    {
        self::respuestaJSON(EmpresasDAO::getConsultaDepartamentos($_POST));
    }

}
