<?php

namespace Controllers;

use Core\Controller;
use Models\Gestiones as ViaticosDAO;

class EstadoCuenta extends Controller
{

    public function Consulta()
    {
        $fecha_actual_iso = date("Y-m-d");
        $error = null;

        // ------------------ POST ------------------
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $nombre_busqueda = trim($_POST['nombre'] ?? '');
            $id_credito_form = trim($_POST['idCredito'] ?? '');
            $fecha_corte = trim($_POST['fechaCorte'] ?? $fecha_actual_iso);

            // Validación de fecha
            if (!\DateTime::createFromFormat('Y-m-d', $fecha_corte)) {
                $this->set("error", "Fecha inválida");
                $this->set("fecha_actual_iso", $fecha_corte);
                $this->render("index");
                return;
            }

            // ------------------ Búsqueda ------------------
            try {
                $id_credito = null;

                if ($nombre_busqueda) {
                    $resultados = $this->buscarCreditoPorNombre($nombre_busqueda);

                    if (!$resultados) {
                        $this->set("error", "No se encontraron créditos con ese nombre");
                        $this->set("fecha_actual_iso", $fecha_corte);
                        $this->render("index");
                        return;
                    }

                    if (count($resultados) > 1) {
                        $this->set("resultados", $resultados);
                        $this->set("fecha_actual_iso", $fecha_corte);
                        $this->render("index");
                        return;
                    }

                    $id_credito = $resultados[0]['id_credito'];

                } elseif ($id_credito_form) {

                    if (!is_numeric($id_credito_form)) {
                        $this->set("error", "ID de crédito inválido");
                        $this->set("fecha_actual_iso", $fecha_corte);
                        $this->render("index");
                        return;
                    }

                    $id_credito = (int)$id_credito_form;
                } else {
                    $this->set("error", "Debes proporcionar nombre o ID de crédito");
                    $this->set("fecha_actual_iso", $fecha_corte);
                    $this->render("index");
                    return;
                }
            } catch (\Exception $e) {
                $this->set("error", "Error buscando crédito");
                $this->set("fecha_actual_iso", $fecha_corte);
                $this->render("index");
                return;
            }

            // ------------------ Llamada API externa ------------------
            $payload = json_encode([
                "idCredito" => $id_credito,
                "fechaCorte" => $fecha_corte
            ]);

            // AQUÍ ya no marca error porque TOKEN está definido en config.php
            $headers = [
                "Token: " . TOKEN,
                "Content-Type: application/json"
            ];

            try {
                $ch = curl_init(ENDPOINT);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                curl_setopt($ch, CURLOPT_TIMEOUT, 15);

                $res = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                $data = json_decode($res, true);
            } catch (\Exception $e) {

                $this->auditarEstadoCuenta(
                    $_SESSION['usuario']['username'],
                    $id_credito,
                    $fecha_corte,
                    0,
                    "Respuesta no válida del servidor"
                );

                $this->set("error", "Respuesta no válida del servidor");
                $this->render("resultado");
                return;
            }

            if ($http_code != 200 || !isset($data['estadoCuenta'])) {
                $mensaje = $data['mensaje'][0] ?? "No se encontraron datos para este crédito";

                $this->auditarEstadoCuenta(
                    $_SESSION['usuario']['username'],
                    $id_credito,
                    $fecha_corte,
                    0,
                    $mensaje
                );

                $this->set("error", $mensaje);
                $this->render("resultado");
                return;
            }

            $__SPARTA_SECRET_REDACTED__ = $data['estadoCuenta'];

            if (empty($__SPARTA_SECRET_REDACTED__['idCredito']) &&
                empty($__SPARTA_SECRET_REDACTED__['datosCliente']) &&
                empty($__SPARTA_SECRET_REDACTED__['datosCargos']) &&
                empty($__SPARTA_SECRET_REDACTED__['datosPagos'])) {

                $this->auditarEstadoCuenta(
                    $_SESSION['usuario']['username'],
                    $id_credito,
                    $fecha_corte,
                    0,
                    "Crédito vacío"
                );

                $this->set("usuario_no_existe", true);
                $this->render("resultado");
                return;
            }

            // ------------------ Datos de referencias ------------------
            try {
                $datos_referencias = $this->obtenerDatosCliente($id_credito);
                $__SPARTA_SECRET_REDACTED__['datosReferencias'] = $datos_referencias ?? [];
            } catch (\Exception $e) {
                $__SPARTA_SECRET_REDACTED__['datosReferencias'] = [];
            }

            try {
                $this->auditarEstadoCuenta(
                    $_SESSION['usuario']['username'],
                    $id_credito,
                    $fecha_corte,
                    1,
                    null
                );

                $tabla = $this->procesarEstadoCuenta($__SPARTA_SECRET_REDACTED__);

            } catch (\Exception $e) {
                $this->set("error", "Error procesando estado de cuenta");
                $this->render("resultado");
                return;
            }

            $this->set("datos", $__SPARTA_SECRET_REDACTED__);
            $this->set("resultado", $tabla);
            $this->render("resultado");
            return;
        }

        // ------------------ GET ------------------
        $this->set("fecha_actual_iso", $fecha_actual_iso);
        $this->render("__SPARTA_SECRET_REDACTED___consulta");
    }

    // Métodos auxiliares
    private function buscarCreditoPorNombre($nombre)
    {
        $res = ClienteDAO::buscarCreditoPorNombre($nombre);

        if (!$res['success']) {
            return [];
        }

        return $res['datos'] ?? [];
    }
    private function obtenerDatosCliente($id_credito) {}
    private function procesarEstadoCuenta($__SPARTA_SECRET_REDACTED__) {}
    private function auditarEstadoCuenta($usuario, $id_credito, $fecha_corte, $exito, $mensaje) {}

    public function Documentacion()
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
        self::render("consulta_documentos");
    }



}
