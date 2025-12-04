<?php

namespace controllers;

use Core\Controller;
use Models\Empresa as EmpresasDAO;

class Usuarios extends Controller
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

                    consultaServidor("/Empresas/getEmpresas", parametros, (respuesta) => {
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
        self::render("usuarios_all");
    }

    public function detalles($usuario)
    {
        $script = <<<HTML
            <script>
                const tabla = "#historialSolicitudes"
                const getSolicitudes = () => {
                    
                    const parametros = {
                        usuario: $_SESSION[usuario_id]
                    }

                    consultaServidor("/Empresas/getEmpresas", parametros, (respuesta) => {
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
        self::render("usuario_detalle");
    }

    public function getEmpresas()
    {
        self::respuestaJSON(EmpresasDAO::getConsultaEmpresas($_POST));
    }
}
