<?php

namespace Controllers;

use Core\Controller;
use Models\Empresa as EmpresasDAO;
use Models\Gestiones as GestionesDAO;
use Models\Gestiones as ViaticosDAO;

class EstadoCuenta extends Controller
{

    // ---------------- SAFE FLOAT ----------------
    private function safe_float($value, $default = 0.0) {
        if ($value === null || $value === "") return $default;
        if (is_numeric($value)) return floatval($value);
        return $default;
    }

    // ---------------- SAFE INT ----------------
    private function safe_int($value, $default = 0) {
        if ($value === null || $value === "") return $default;
        if (is_numeric($value)) return intval($value);
        return $default;
    }

    // ---------------- PARSEAR CUOTAS ----------------
    private function parse_cuotas_field($value) {
        if (!$value) return [];

        $value = trim($value);

        if (strpos($value, ",") !== false) {
            return array_map('intval', explode(",", $value));
        }

        if (strpos($value, "-") !== false) {
            list($start, $end) = explode("-", $value);
            return range(intval($start), intval($end));
        }

        return [intval($value)];
    }

    // ---------------- EXTRAER NUMERO DE CUOTA ----------------
    private function extraer_numero_cuota($concepto) {
        if (!$concepto) return null;
        if (preg_match('/(\d+)/', $concepto, $m)) {
            return intval($m[1]);
        }
        return null;
    }

    public function Consulta()
    {
        // --- JS COMPLETO EN EL CONTROLADOR ---
        $script = <<<JS
        <script>
        
        document.addEventListener("DOMContentLoaded", () => {
        
            // Cambiar entre ID y Nombre
            function actualizarInputs() {
                    const modo = document.querySelector('input[name="modoBusqueda"]:checked')?.value;
                    document.getElementById('divNombre').style.display = modo === 'nombre' ? 'block' : 'none';
                    document.getElementById('divID').style.display = modo === 'id' ? 'block' : 'none';
            }
        
            document.querySelectorAll('input[name="modoBusqueda"]').forEach(el =>
                el.addEventListener('change', actualizarInputs)
            );
            actualizarInputs();
        
            // Botón limpiar filtros
            document.getElementById("btnResetFiltros").addEventListener("click", () => {
                document.getElementById("idCredito").value = "";
                document.getElementById("nombre").value = "";
                document.getElementById("modoID").checked = true;
                actualizarInputs();
            });
        
            // Validación antes de enviar
            document.getElementById("formBusqueda").addEventListener("submit", e => {
                const idCredito = document.getElementById("idCredito").value.trim();
                const modo = document.querySelector('input[name="modoBusqueda"]:checked')?.value;
        
                if (modo === "id" && idCredito === "") {
                    e.preventDefault();
                    return Swal.fire({
                        icon: "warning",
                        title: "Falta el ID Crédito",
                        text: "Por favor ingresa el ID del crédito."
                    });
                }
        
                // Loading
                Swal.fire({
                    title: "Procesando solicitud...",
                    text: "Espere un momento por favor.",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => Swal.showLoading()
                });
            });
        
        });
        

        </script>
JS;
        $script_error = <<<JS
        <script>
                document.addEventListener('DOMContentLoaded',()=>mostrarMensajeAll({tipo:'error',titulo:'Error de busqueda',mensaje:'No se encontraron resultados'}));
        </script>
JS;

        # -----------------------------
        # PETICIÓN POST
        # -----------------------------
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $idCredito = $_POST['idCredito'] ?? null;
            $nombre = $_POST['nombre'] ?? null;

            if($nombre != null)
            {
                $this->$getclientesEstadoCuenta($nombre);
            }
            else
            {

            }



            //$GestionesAll = GestionesDao::getAllGestiones($idCredito, $nombre);
            $resultado =  $this->api___SPARTA_SECRET_REDACTED__(1600, "2025-12-04");
            // variables para guardar por bloques los arrreglos
            // ---------------------------------------------------------------
            // 1. Extraemos las secciones del JSON original
            // ---------------------------------------------------------------
            $cliente = $resultado['data']['datosCliente'];
            $estadoCuenta = $resultado['data'];
            $otrosDatos = $resultado['data']['datosSaldos'];
            //$datosCargos = $resultado['data']['datosCargos'];
            //$datosPagos = $resultado['data']['datosPagos'];

            // ---------------------------------------------------------------
            // 2. Crear estructura base para cada cuota con la información del cargo
            // ---------------------------------------------------------------
            $cargos = $estadoCuenta["datosCargos"] ?? [];
            if (!is_array($cargos)) $cargos = [];

            $pagos = $estadoCuenta["datosPagos"] ?? [];
            if (!is_array($pagos)) $pagos = [];

            $pagos_list = [];

            // -------------------------
            // PREPARAR PAGOS
            // -------------------------
            foreach ($pagos as $p) {

                $montoPago      = $this->safe_float($p["montoPago"] ?? 0);
                $extemporaneos  = $this->safe_float($p["extemporaneos"] ?? 0);
                $monto_real     = max($montoPago - $extemporaneos, 0);

                $cuotas = $this->parse_cuotas_field($p["numeroCuotaSemanal"] ?? null);

                $pagos_list[] = [
                    "idPago"              => $p["idPago"] ?? null,
                    "remaining"           => round($monto_real, 2),
                    "cuotas"              => $cuotas,
                    "fechaValor"          => $p["fechaValor"] ?? null,
                    // *** fechaRegistro usa fechaDeposito si viene ***
                    "fechaRegistro"       => $p["fechaDeposito"] ?? ($p["fechaRegistro"] ?? null),
                    "montoPagoOriginal"   => $montoPago,
                    "extemporaneos"       => $extemporaneos,
                    "_extemporaneo_aplicado" => false
                ];
            }

            // -------------------------
            // ORDENAR CARGOS POR idCargo
            // -------------------------
            usort($cargos, function($a, $b){
                return $this->safe_int($a["idCargo"] ?? 0) <=> $this->safe_int($b["idCargo"] ?? 0);
            });

            $tabla = [];

            // -------------------------
            // PROCESAR CARGOS
            // -------------------------
            foreach ($cargos as $cargo) {

                $concepto = $cargo["concepto"] ?? "";
                $cuota_num = $this->extraer_numero_cuota($concepto);

                if ($cuota_num === null) {
                    $cuota_num = $this->safe_int($cargo["idCargo"] ?? 0);
                }

                $monto_cargo   = $this->safe_float($cargo["monto"] ?? 0);
                $capital       = $this->safe_float($cargo["capital"] ?? 0);
                $interes       = $this->safe_float($cargo["interes"] ?? 0);
                $seguro_total  = $this->safe_float($cargo["seguroBienes"] ?? 0)
                    + $this->safe_float($cargo["seguroVida"] ?? 0)
                    + $this->safe_float($cargo["seguroDesempleo"] ?? 0);

                $fecha_venc = $cargo["fechaVencimiento"] ?? ($cargo["fechaVenc"] ?? null);

                $monto_restante_cargo = $monto_cargo;
                $aplicados = [];

                // -------------------------
                // APLICAR PAGOS A ESTE CARGO
                // -------------------------
                foreach ($pagos_list as &$pago) {

                    // ¿Este pago aplica a esta cuota?
                    if (!in_array($cuota_num, $pago["cuotas"])) {
                        continue;
                    }

                    // --- Aplicar monto real (remaining) ---
                    if ($monto_restante_cargo > 0 && $pago["remaining"] > 0) {

                        $aplicar = min($pago["remaining"], $monto_restante_cargo);

                        $aplicados[] = [
                            "idPago"       => $pago["idPago"],
                            "montoPago"    => round($pago["remaining"], 2), // cuánto quedaba antes de aplicar
                            "aplicado"     => round($aplicar, 2),
                            "fechaRegistro"=> $pago["fechaRegistro"],
                            "fechaPago"    => $fecha_venc,
                            "diasMora"     => null,
                            "extemporaneos"=> 0.0
                        ];

                        // restar de pago y cargo
                        $pago["remaining"]        = round($pago["remaining"] - $aplicar, 2);
                        $monto_restante_cargo     = round($monto_restante_cargo - $aplicar, 2);
                    }

                    // --- Aplicar extemporáneo solo una vez ---
                    if ($pago["extemporaneos"] > 0 && !$pago["_extemporaneo_aplicado"]) {

                        $aplicados[] = [
                            "idPago"       => $pago["idPago"],
                            "montoPago"    => round($pago["extemporaneos"], 2),
                            "aplicado"     => round($pago["extemporaneos"], 2),
                            "fechaRegistro"=> $pago["fechaRegistro"],
                            "fechaPago"    => $fecha_venc,
                            "diasMora"     => null,
                            "extemporaneos"=> round($pago["extemporaneos"], 2)
                        ];

                        $pago["_extemporaneo_aplicado"] = true;
                    }
                }

                // -------------------------
                // CÁLCULOS FINALES
                // -------------------------
                $total_aplicado = round($monto_cargo - $monto_restante_cargo, 2);
                $pendiente      = round(max($monto_cargo - $total_aplicado, 0), 2);
                $excedente      = round(max($total_aplicado - $monto_cargo, 0), 2);

                $tabla[] = [
                    "cuota"         => $cuota_num,
                    "fecha"         => $fecha_venc,
                    "monto_cargo"   => round($monto_cargo, 2),
                    "capital"       => round($capital, 2),
                    "interes"       => round($interes, 2),
                    "seguro"        => round($seguro_total, 2),
                    "aplicados"     => $aplicados,
                    "total_pagado"  => $total_aplicado,
                    "pendiente"     => $pendiente,
                    "excedente"     => $excedente,
                    "raw_cargo"     => $cargo
                ];
            }


            if (empty($resultado["data"]["idCredito"])) {
                self::set("titulo", "Sin resultados para solicitud");
                self::set("errorGestiones", "No se encontraron resultados");
                $script_completo = $script . "\n" . $script_error;
                self::set("script", $script_completo);
                self::set("tabla", $tabla);
                return self::render("__SPARTA_SECRET_REDACTED___request");
            }
            else
            {
                self::set("dataCliente", $cliente);
                self::set("dataEstadoCuenta", $estadoCuenta);
                self::set("dataOtrosDatos", $otrosDatos);


                self::set("titulo", "Resultado de la solicitud");
                self::set("script", $script);
                self::set("tabla", $tabla);
                return self::render("__SPARTA_SECRET_REDACTED___request");

            }


        }

        # -----------------------------
        # GET NORMAL
        # -----------------------------
        self::set("titulo", "Busqueda Gestiones SKY");
        self::set("script", $script);
        return self::render("__SPARTA_SECRET_REDACTED___consulta");
    }

    public function nombre() {

        header('Content-Type: application/json');

        $q = $_GET['q'] ?? '';

        if (strlen($q) < 2) {
            echo json_encode([]);
            return;
        }

        $resultados = self::respuestaJSON(EmpresasDAO::getConsultaPorNombre($_POST));

        echo json_encode($resultados);

    }

    public function getclientesEstadoCuenta()
    {
        self::respuestaJSON(EmpresasDAO::getConsultaDepartamentos($_POST));
    }

    function api___SPARTA_SECRET_REDACTED__($idCredito, $fechaCorte) {

        $url = "https://servicios.s2movil.net/s2__SPARTA_SECRET_REDACTED__/estadocuenta";

        // --- Construir el JSON que la API externa espera ---
        $payload = json_encode([
            "idCredito" => intval($idCredito),
            "fechaCorte" => $fechaCorte
        ]);

        // --- Headers obligatorios ---
        $headers = [
            "Token: __SPARTA_TOKEN_REDACTED__",
            "Content-Type: application/json"
        ];

        // --- Preparar CURL ---
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        // --- Ejecutar ---
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // --- Manejo de errores de CURL ---
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);

            return [
                "ok" => false,
                "status" => 0,
                "error" => "Error al conectar con servidor: $error",
                "data" => null
            ];
        }

        curl_close($ch);

        // --- Convertir JSON en arreglo ---
        $json = json_decode($response, true);

        // --- Si el JSON viene roto ---
        if ($json === null) {
            return [
                "ok" => false,
                "status" => $httpCode,
                "error" => "Respuesta no válida del servidor (JSON inválido)",
                "data" => null
            ];
        }

        // --- Si la API respondió error ---
        if ($httpCode !== 200) {
            return [
                "ok" => false,
                "status" => $httpCode,
                "error" => $json["mensaje"][0] ?? "No hay conexión con S2",
                "data" => $json
            ];
        }

        // --- Si no trae estadoCuenta ---
        if (!isset($json["estadoCuenta"])) {
            return [
                "ok" => false,
                "status" => $httpCode,
                "error" => "No se encontraron datos en la API",
                "data" => $json
            ];
        }

        // --- Todo OK ---
        return [
            "ok" => true,
            "status" => 200,
            "error" => null,
            "data" => $json["estadoCuenta"]
        ];
    }
    ///////////////////////////////////////

    public function documentacion()
    {
        // --- JS COMPLETO EN EL CONTROLADOR ---
        $script = <<<JS
        <script>
        
        document.addEventListener("DOMContentLoaded", () => {
        
            // Cambiar entre ID y Nombre
            function actualizarInputs() {
                    const modo = document.querySelector('input[name="modoBusqueda"]:checked')?.value;
                    document.getElementById('divNombre').style.display = modo === 'nombre' ? 'block' : 'none';
                    document.getElementById('divID').style.display = modo === 'id' ? 'block' : 'none';
            }
        
            document.querySelectorAll('input[name="modoBusqueda"]').forEach(el =>
                el.addEventListener('change', actualizarInputs)
            );
            actualizarInputs();
        
            // Botón limpiar filtros
            document.getElementById("btnResetFiltros").addEventListener("click", () => {
                document.getElementById("idCredito").value = "";
                document.getElementById("nombre").value = "";
                document.getElementById("modoID").checked = true;
                actualizarInputs();
            });
        
            // Validación antes de enviar
            document.getElementById("formBusqueda").addEventListener("submit", e => {
                const idCredito = document.getElementById("idCredito").value.trim();
                const modo = document.querySelector('input[name="modoBusqueda"]:checked')?.value;
        
                if (modo === "id" && idCredito === "") {
                    e.preventDefault();
                    return Swal.fire({
                        icon: "warning",
                        title: "Falta el ID Crédito",
                        text: "Por favor ingresa el ID del crédito."
                    });
                }
        
                // Loading
                Swal.fire({
                    title: "Procesando solicitud...",
                    text: "Espere un momento por favor.",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => Swal.showLoading()
                });
            });
        
        });
        

        </script>
JS;
        $script_error = <<<JS
        <script>
                document.addEventListener('DOMContentLoaded',()=>mostrarMensajeAll({tipo:'error',titulo:'Error de busqueda',mensaje:'No se encontraron resultados'}));
        </script>
JS;

        # -----------------------------
        # PETICIÓN POST
        # -----------------------------
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $idCredito = $_POST['idCredito'] ?? null;
            $nombre = $_POST['nombre'] ?? null;



            //$GestionesAll = GestionesDao::getAllGestiones($idCredito, $nombre);
            $resultado =  $this->api___SPARTA_SECRET_REDACTED__($idCredito, "2025-12-04");


            if (empty($resultado["data"]["idCredito"])) {
                self::set("titulo", "Sin resultados para solicitud");
                self::set("errorGestiones", "No se encontraron resultados");
                $script_completo = $script . "\n" . $script_error;
                self::set("script", $script_completo);
                return self::render("documentacion_consulta");
            }
            else
            {


                self::set("titulo", "Resultado de la solicitud");
                self::set("script", $script);
                return self::render("documentacion_consulta");
            }


        }

        # -----------------------------
        # GET NORMAL
        # -----------------------------
        self::set("titulo", "Busqueda Gestiones SKY");
        self::set("script", $script);
        return self::render("documentacion_consulta");
    }






}
