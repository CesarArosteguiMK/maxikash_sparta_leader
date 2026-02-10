<?php

namespace Controllers;

use Core\Controller;
use Models\Empresa as EmpresasDAO;
use Models\EstadoCuenta as EstadoCuentaDAO;


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
                    const divNombre = document.getElementById('divNombre');
                    const divID = document.getElementById('divID');
                    
                    // Verificar que los elementos existan antes de acceder a sus propiedades
                    if (divNombre) {
                        divNombre.style.display = modo === 'nombre' ? 'block' : 'none';
                    }
                    if (divID) {
                        divID.style.display = modo === 'id' ? 'block' : 'none';
                    }
            }
            
            document.querySelectorAll('input[name="modoBusqueda"]').forEach(el =>
                el.addEventListener('change', actualizarInputs)
            );
            
            // Solo agregar el event listener si el modal existe
            const modalDirecciones = document.getElementById('modalDirecciones');
            if (modalDirecciones) {
                modalDirecciones.addEventListener('shown.bs.modal', function() {
                    // No ejecutar actualizarInputs cuando se abre el modal de direcciones
                    // ya que ese modal no tiene los elementos divNombre y divID
                });
            }

        
            // Botón limpiar filtros
            document.getElementById("btnResetFiltros").addEventListener("click", () => {
                document.getElementById("idCredito").value = "";
                document.getElementById("nombre").value = "";
                document.getElementById("modoID").checked = true;
                actualizarInputs();
            });
        
            // Validación antes de enviar
            document.getElementById("formBusqueda").addEventListener("submit", async e => {
                e.preventDefault();
                
                const modo = document.querySelector('input[name="modoBusqueda"]:checked')?.value;
                const idCredito       = document.getElementById("idCredito").value.trim();
                const nombre          = document.getElementById("nombre").value.trim();
                const idCreditoLista  = document.getElementById("idCreditoLista").value.trim();
        
                // =========================
                // MODO ID
                // =========================
                if (modo === "id") {
            
                    if (idCredito === "") {
                        return Swal.fire({
                            icon: "warning",
                            title: "Falta el ID de crédito",
                            text: "Por favor ingresa el ID del crédito."
                        });
                    }
            
                    // Limpieza defensiva
                    document.getElementById("idCreditoLista").value = "";
                    document.getElementById("nombre").value = "";
                }
            
                // =========================
                // MODO NOMBRE
                // =========================
                if (modo === "nombre") {
            
                    if (nombre === "") {
                        return Swal.fire({
                            icon: "warning",
                            title: "Falta el nombre",
                            text: "Escribe y selecciona un cliente de la lista."
                        });
                    }
            
                    if (idCreditoLista === "") {
                        return Swal.fire({
                            icon: "warning",
                            title: "Cliente no seleccionado",
                            text: "Debes seleccionar un cliente del listado, no solo escribirlo."
                        });
                    }
            
                    // Limpieza defensiva
                    document.getElementById("idCredito").value = "";
                }
            
                // =========================
                // LOADING
                // =========================
                Swal.fire({
                    title: "Validando ID de crédito...",
                    text: "Espere un momento por favor.",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => Swal.showLoading()
                });

                // =========================
                // VALIDAR ID ANTES DE ENVIAR
                // =========================
                try {
                    const formData = new FormData();
                    if (modo === "id") {
                        formData.append("idCredito", idCredito);
                    } else {
                        formData.append("nombre", nombre);
                        formData.append("idCreditoLista", idCreditoLista);
                    }
                    formData.append("modoBusqueda", modo);

                    const response = await fetch("/EstadoCuenta/validarCredito", {
                        method: "POST",
                        body: formData
                    });

                    const result = await response.json();

                    if (!result.success) {
                        Swal.close();
                        return Swal.fire({
                            icon: "error",
                            title: "ID de crédito incorrecto",
                            html: "<div style='text-align: center; padding: 10px;'><p style='font-size: 16px; margin-bottom: 15px; color: #333;'><strong>El ID de crédito ingresado no existe o no es válido.</strong></p><p style='font-size: 14px; color: #666;'>Por favor verifícalo y vuelve a intentar.</p></div>",
                            confirmButtonText: "Entendido",
                            confirmButtonColor: "#dc3545",
                            width: "500px",
                            buttonsStyling: true,
                            customClass: {
                                popup: 'animated bounceIn',
                                title: 'text-danger',
                                confirmButton: 'btn-elegant-error'
                            },
                            showClass: {
                                popup: 'animate__animated animate__fadeInDown'
                            },
                            hideClass: {
                                popup: 'animate__animated animate__fadeOutUp'
                            }
                        }).then(() => {
                            // Agregar estilos personalizados al botón después de que se renderice
                            setTimeout(() => {
                                const confirmBtn = document.querySelector('.swal2-confirm');
                                if (confirmBtn) {
                                    confirmBtn.style.cssText = `
                                        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
                                        border: none !important;
                                        border-radius: 8px !important;
                                        padding: 12px 30px !important;
                                        font-size: 15px !important;
                                        font-weight: 600 !important;
                                        letter-spacing: 0.5px !important;
                                        box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3) !important;
                                        transition: all 0.3s ease !important;
                                        text-transform: uppercase !important;
                                    `;
                                    confirmBtn.addEventListener('mouseenter', function() {
                                        this.style.transform = 'translateY(-2px)';
                                        this.style.boxShadow = '0 6px 20px rgba(220, 53, 69, 0.4)';
                                    });
                                    confirmBtn.addEventListener('mouseleave', function() {
                                        this.style.transform = 'translateY(0)';
                                        this.style.boxShadow = '0 4px 15px rgba(220, 53, 69, 0.3)';
                                    });
                                }
                            }, 100);
                        });
                    }

                    // Si la validación es exitosa, mostrar loading y enviar el formulario
                    Swal.fire({
                        title: "Procesando solicitud...",
                        text: "Espere un momento por favor.",
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => Swal.showLoading()
                    });

                    // Enviar el formulario usando submit() nativo (no dispara el evento submit)
                    const form = e.target;
                    form.submit();

                } catch (error) {
                    Swal.close();
                    console.error("Error al validar:", error);
                    Swal.fire({
                        icon: "error",
                        title: "Error de conexión",
                        text: "No se pudo validar el ID de crédito. Por favor intenta nuevamente."
                    });
                }
            });
        
        });
        

        </script>
JS;

        // Script de error - solo se ejecuta cuando hay un error
        $script_error = <<<JS
        <script>
                document.addEventListener('DOMContentLoaded',()=>mostrarMensajeAll({tipo:'error',titulo:'Error de busqueda',mensaje:'No se encontraron resultados'}));
        </script>
JS;


        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $idCredito = $_POST['idCredito'] ?? null;
            $nombre = $_POST['nombre'] ?? null;
            $idCreditoLista = $_POST['idCreditoLista'] ?? null;
            $fechaHoy = date('Y-m-d');

            if($nombre != null && $idCreditoLista != null)
            {
                $resultado = $this->api___SPARTA_SECRET_REDACTED__($idCreditoLista, $fechaHoy);
                $respDAO = EmpresasDAO::getConsultaDireccionEstadoCuenta($idCreditoLista);
                $referencias = EmpresasDAO::getConsultaReferenciasEstadoCuenta($idCreditoLista);
                $notas = EmpresasDAO::getNotasNum($idCreditoLista);
            }
            else
            {
                $resultado =  $this->api___SPARTA_SECRET_REDACTED__($idCredito, $fechaHoy);
                $respDAO = EmpresasDAO::getConsultaDireccionEstadoCuenta($idCredito);
                $referencias = EmpresasDAO::getConsultaReferenciasEstadoCuenta($idCredito);
                $notas = EmpresasDAO::getNotasNum($idCredito);
            }




            //$GestionesAll = GestionesDao::getAllGestiones($idCredito, $nombre);
            //$resultado =  $this->api___SPARTA_SECRET_REDACTED__($idCredito, "2025-12-04");
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
            foreach ($cargos as $cargo_idx => $cargo) {

                $concepto = $cargo["concepto"] ?? "";
                $cuota_num = $this->extraer_numero_cuota($concepto);

                if ($cuota_num === null) {
                    $cuota_num = $this->safe_int($cargo["idCargo"] ?? 0);
                }

                $monto_cargo   = $this->safe_float($cargo["monto"] ?? 0);
                // Monto de la siguiente cuota (si existe): para priorizar liquidarla antes de cobrar Gasto de Cobranza.
                $siguiente_cargo = $cargos[$cargo_idx + 1] ?? null;
                $monto_siguiente_cuota = $siguiente_cargo ? $this->safe_float($siguiente_cargo["monto"] ?? 0) : 0;
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

                    // --- Aplicar monto real (remaining) a la cuota primero ---
                    $aplico_remaining_esta_cuota = false;
                    if ($monto_restante_cargo > 0 && $pago["remaining"] > 0) {

                        $remaining_al_inicio = round($pago["remaining"], 2);
                        $aplicar = min($pago["remaining"], $monto_restante_cargo);
                        // Es sobrante cuando aplicamos el "resto" del pago (chunk < cargo) o cuando el pago ya se aplicó en cuota anterior (remaining < original).
                        $es_sobrante_remaining = (($aplicar == $pago["remaining"]) && ($aplicar < $monto_cargo))
                            || ($remaining_al_inicio < round($pago["montoPagoOriginal"] ?? PHP_INT_MAX, 2));
                        // Mostrar monto real del depósito solo cuando es la primera aplicación; si ya se usó en cuota anterior, mostrar el resto que llegó.
                        $monto_mostrar = $es_sobrante_remaining ? $remaining_al_inicio : round($pago["montoPagoOriginal"] ?? $pago["remaining"], 2);

                        $aplicados[] = [
                            "idPago"       => $pago["idPago"],
                            "montoPago"    => $monto_mostrar,
                            "aplicado"     => round($aplicar, 2),
                            "fechaRegistro"=> $pago["fechaRegistro"],
                            "fechaPago"    => $fecha_venc,
                            "diasMora"     => null,
                            "extemporaneos"=> 0.0,
                            "es_sobrante"  => $es_sobrante_remaining
                        ];

                        $aplico_remaining_esta_cuota = true;
                        // restar de pago y cargo
                        $pago["remaining"]        = round($pago["remaining"] - $aplicar, 2);
                        $monto_restante_cargo     = round($monto_restante_cargo - $aplicar, 2);
                    }

                    // --- Sobrante (remaining + extemporáneos): priorizar liquidar la siguiente semana; solo cobrar Gasto de Cobranza si ya estamos al día (sobrante no alcanza para la siguiente cuota) ---
                    $sobrante_restante = round($pago["remaining"] + $pago["extemporaneos"], 2);
                    $sobrante_prioriza_siguiente = ($monto_siguiente_cuota > 0 && $sobrante_restante >= $monto_siguiente_cuota);
                    if ($sobrante_restante >= 235 && $aplico_remaining_esta_cuota && !$pago["_extemporaneo_aplicado"] && !$sobrante_prioriza_siguiente) {
                        $fecha_venc_ts = $fecha_venc ? @strtotime($fecha_venc) : 0;
                        $pago_fecha_ts = !empty($pago["fechaRegistro"]) ? @strtotime($pago["fechaRegistro"]) : 0;
                        $mora_dias = ($fecha_venc_ts && $pago_fecha_ts) ? max(0, (int) floor(($pago_fecha_ts - $fecha_venc_ts) / 86400)) : 0;
                        if ($mora_dias > 0) {
                            $monto_gasto = min(250, $sobrante_restante);
                            $aplicados[] = [
                                "idPago"          => $pago["idPago"],
                                "montoPago"       => round($monto_gasto, 2),
                                "aplicado"        => round($monto_gasto, 2),
                                "fechaRegistro"   => $pago["fechaRegistro"],
                                "fechaPago"       => $fecha_venc,
                                "diasMora"        => $mora_dias,
                                "extemporaneos"   => 0.0,
                                "es_sobrante"     => false,
                                "gasto_cobranza"  => true
                            ];
                            // Sumar el Gasto al "aplicado" de la línea anterior del mismo pago; si era sobrante, mostrar depósito original (ej. Sobrante: $1011 - Aplicado Sobrante: $1009).
                            $id_pago_gasto = $pago["idPago"];
                            $monto_orig = round($pago["montoPagoOriginal"] ?? 0, 2);
                            for ($i = count($aplicados) - 2; $i >= 0; $i--) {
                                if (isset($aplicados[$i]["idPago"]) && $aplicados[$i]["idPago"] === $id_pago_gasto && empty($aplicados[$i]["gasto_cobranza"])) {
                                    $aplicados[$i]["aplicado"] = round(($aplicados[$i]["aplicado"] ?? 0) + $monto_gasto, 2);
                                    if (!empty($aplicados[$i]["es_sobrante"])) {
                                        $aplicados[$i]["montoPago"] = $monto_orig;
                                        $aplicados[$i]["es_sobrante"] = false;
                                    }
                                    break;
                                }
                            }
                            $resto_sobrante = round($sobrante_restante - $monto_gasto, 2);
                            $pago["remaining"] = $resto_sobrante;
                            $pago["extemporaneos"] = 0;
                            $pago["_extemporaneo_aplicado"] = true;
                        } else {
                            $pago["remaining"] = $sobrante_restante;
                            $pago["extemporaneos"] = 0;
                            $pago["_extemporaneo_aplicado"] = true;
                        }
                    } else {
                        $pago["remaining"] = $sobrante_restante;
                        $pago["extemporaneos"] = 0;
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

            if (
                !isset($resultado['data']['idCredito']) ||
                $resultado['data']['idCredito'] === null ||
                $resultado['data']['idCredito'] === ''
            ) {
                self::set("titulo", "Sin resultados para solicitud");
                self::set("errorGestiones", "No se encontraron resultados");
                self::set("tabla", $tabla);
                return self::render("__SPARTA_SECRET_REDACTED___request");
            }
            if (empty($resultado["data"]["idCredito"])) {

                self::set("titulo", "Sin resultados para solicitud");
                self::set("errorGestiones", "No se encontraron resultados");
                self::set("tabla", $tabla);

                return self::render("__SPARTA_SECRET_REDACTED___request");

            } else {

                self::set("dataCliente", $cliente);
                self::set("dataEstadoCuenta", $estadoCuenta);
                self::set("dataOtrosDatos", $otrosDatos);
                self::set("direcciones", $respDAO);
                self::set("referencias", $referencias);
                self::set("notas", $notas);
                self::set("titulo", "Resultado de la solicitud");
                self::set("script", $script);
                self::set("tabla", $tabla);

                return self::render("__SPARTA_SECRET_REDACTED___request");
            }


        }

        # -----------------------------
        # GET NORMAL
        # -----------------------------
        self::set("titulo", "Estados de Cuenta");
        self::set("script", $script);
        return self::render("__SPARTA_SECRET_REDACTED___consulta");
    }
    public function getclientesEstadoCuenta()
    {
        self::respuestaJSON(EmpresasDAO::getConsultaDepartamentos($_POST));
    }

    public function validarCredito()
    {
        $idCredito = $_POST['idCredito'] ?? null;
        $idCreditoLista = $_POST['idCreditoLista'] ?? null;
        $fechaHoy = date('Y-m-d');

        // Usar idCreditoLista si viene (modo nombre), sino usar idCredito (modo ID)
        $idAValidar = $idCreditoLista ?? $idCredito;

        if (!$idAValidar) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'ID de crédito no proporcionado'
            ]);
            return;
        }

        // Validar con la API
        $resultado = $this->api___SPARTA_SECRET_REDACTED__($idAValidar, $fechaHoy);

        // Verificar si hubo error en la API
        if (!$resultado['ok']) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => $resultado['error'] ?? 'ID de crédito incorrecto'
            ]);
            return;
        }

        // Verificar si el ID de crédito existe en los datos
        if (
            !isset($resultado['data']['idCredito']) ||
            $resultado['data']['idCredito'] === null ||
            $resultado['data']['idCredito'] === '' ||
            empty($resultado['data']['idCredito'])
        ) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'ID de crédito incorrecto'
            ]);
            return;
        }

        // Si todo está bien
        self::respuestaJSON([
            'success' => true,
            'mensaje' => 'ID de crédito válido'
        ]);
    }

    public static function getclientesEstadoCuentaNombre()
    {
        // Leer JSON del POST
        $input = json_decode(file_get_contents('php://input'), true);
        $nombre = trim($input['nombre'] ?? '');

        // Llamada al DAO usando el término real
        $respDAO = EmpresasDAO::getConsultaPorNombre($nombre);

        $datos = [];

        // Normalizar respuesta del DAO al formato del autocomplete
        if (!empty($respDAO['datos'])) {
            foreach ($respDAO['datos'] as $row) {
                $datos[] = [
                    'id' => $row['Id_credito'],
                    'nombre_completo' => $row['Nombre_cliente']
                ];
            }
        }

        // Respuesta final para el frontend
        header('Content-Type: application/json');
        echo json_encode([
            'resultado' => !empty($datos),
            'datos' => $datos
        ]);
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
        $script = <<<JS
        <script>
            document.addEventListener('DOMContentLoaded', () => {

                const registroTipos = {
                    'INE': 'INE',
                    'FACTURA': 'Factura',
                    'CONTRATO': 'Validaciones',
                    'FAD_DOC': 'FAD_DOC',
                    'EVIDENCIA': 'Evidencia'
                };

                const abrirModalRegistroDocumento = (idCredito, tipoDocumento) => {
                    const modalElement = document.getElementById('modalRegistrarDocumentoCliente');
                    if (!modalElement) {
                        Swal.fire('Error', 'No se encontró el modal de registro', 'error');
                        return;
                    }

                    const idInput = document.getElementById('registroDocumentoId');
                    const tipoInput = document.getElementById('registroDocumentoTipo');
                    const tipoTexto = document.getElementById('registroDocumentoTipoTexto');
                    const archivoInput = document.getElementById('registroDocumentoArchivo');
                    const archivoUnico = document.getElementById('registroArchivoUnico');
                    const archivosINE = document.getElementById('registroArchivosINE');
                    const ineFrente = document.getElementById('registroINEFrente');
                    const ineReverso = document.getElementById('registroINEReverso');

                    if (idInput) idInput.value = idCredito || '';
                    if (tipoInput) tipoInput.value = tipoDocumento || '';
                    if (tipoTexto) tipoTexto.value = registroTipos[tipoDocumento] || tipoDocumento || '';

                    // Mostrar/ocultar campos según tipo de documento
                    if (tipoDocumento === 'INE') {
                        if (archivoUnico) archivoUnico.style.display = 'none';
                        if (archivosINE) archivosINE.style.display = 'block';
                        if (archivoInput) archivoInput.value = '';
                        if (ineFrente) ineFrente.value = '';
                        if (ineReverso) ineReverso.value = '';
                    } else {
                        if (archivoUnico) archivoUnico.style.display = 'block';
                        if (archivosINE) archivosINE.style.display = 'none';
                        if (archivoInput) archivoInput.value = '';
                        if (ineFrente) ineFrente.value = '';
                        if (ineReverso) ineReverso.value = '';
                    }

                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                };

                const formRegistrar = document.getElementById('formRegistrarDocumentoCliente');
                if (formRegistrar) {
                    formRegistrar.addEventListener('submit', async function(e) {
                        e.preventDefault();

                        const idCredito = document.getElementById('registroDocumentoId')?.value.trim();
                        const tipoDocumento = document.getElementById('registroDocumentoTipo')?.value.trim();

                        if (!idCredito || !tipoDocumento) {
                            Swal.fire('Error', 'Datos incompletos para el registro', 'error');
                            return;
                        }

                        const formData = new FormData();
                        formData.append('idCredito', idCredito);
                        formData.append('tipoDocumento', tipoDocumento);

                        // Validar archivos según tipo de documento
                        if (tipoDocumento === 'INE') {
                            const ineFrente = document.getElementById('registroINEFrente');
                            const ineReverso = document.getElementById('registroINEReverso');

                            if (!ineFrente?.files?.[0] || !ineReverso?.files?.[0]) {
                                Swal.fire('Error', 'Debes seleccionar ambos archivos: frente y reverso del INE', 'error');
                                return;
                            }

                            formData.append('ineFrente', ineFrente.files[0]);
                            formData.append('ineReverso', ineReverso.files[0]);
                        } else {
                            const archivoInput = document.getElementById('registroDocumentoArchivo');

                            if (!archivoInput?.files?.[0]) {
                                Swal.fire('Error', 'Selecciona un archivo para registrar', 'error');
                                return;
                            }

                            formData.append('archivo', archivoInput.files[0]);
                        }

                        Swal.fire({
                            title: 'Registrando documento',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: () => Swal.showLoading()
                        });

                        try {
                            const endpoint = tipoDocumento === 'INE' 
                                ? '/estadocuenta/registrarINE' 
                                : '/estadocuenta/registrarDocumentoCliente';
                            
                            const response = await fetch(endpoint, {
                                method: 'POST',
                                body: formData
                            });
                            const data = await response.json();

                            Swal.close();

                            if (!data || !data.success) {
                                Swal.fire('Error', data?.mensaje || 'No se pudo registrar el documento', 'error');
                                return;
                            }

                            const modalElement = document.getElementById('modalRegistrarDocumentoCliente');
                            if (modalElement) {
                                const modal = bootstrap.Modal.getInstance(modalElement);
                                if (modal) {
                                    modal.hide();
                                }
                            }

                            Swal.fire({
                                icon: 'success',
                                title: 'Documento registrado',
                                text: data.mensaje || 'El documento se guardo correctamente.'
                            });
                        } catch (error) {
                            Swal.close();
                            Swal.fire('Error', 'No se pudo registrar el documento', 'error');
                        }
                    });
                }
            
                // Botón limpiar filtros
                const btnResetFiltros = document.getElementById('btnResetFiltros');
                if (btnResetFiltros) {
                    btnResetFiltros.addEventListener('click', () => {
                        // Limpiar campo ID de crédito
                        const idCredito = document.getElementById('idCredito');
                        if (idCredito) {
                            idCredito.value = '';
                        }
                        // Limpiar campo nombre (si existe)
                        const nombre = document.getElementById('nombre');
                        if (nombre) {
                            nombre.value = '';
                        }
                        
                        // Resetear select de tipo de documento
                        const tipoDocumento = document.getElementById('tipoDocumento');
                        if (tipoDocumento) {
                            tipoDocumento.value = '';
                        }
                    });
                }
            
                const btnBuscar = document.getElementById('btnBuscar');
                const form = document.getElementById('formBusqueda');
            
                if (!btnBuscar || !form) {
                    console.error('Elementos del formulario no encontrados');
                    return;
                }
            
                // Escuchar clic del botón en lugar del submit del formulario
                btnBuscar.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
            
                    const idInput = document.getElementById('idCredito');
                    const tipoSelect = document.getElementById('tipoDocumento');
                    
                    if (!idInput || !tipoSelect) {
                        console.error('Campos del formulario no encontrados');
                        Swal.fire('Error', 'Error al acceder a los campos del formulario', 'error');
                        return;
                    }
            
                    const id   = idInput.value.trim();
                    const tipo = tipoSelect.value;
            
                    // Validar que el ID no esté vacío
                    if (!id) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'ID de crédito requerido',
                            text: 'Por favor introduce el ID de crédito'
                        });
                        return;
                    }
            
                    // Validar que se haya seleccionado un tipo de documento
                    if (!tipo) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Tipo de documento requerido',
                            text: 'Por favor selecciona el documento a comprobar'
                        });
                        return;
                    }
            
                    // ====== LIMPIAR CONTENEDORES ANTES DE CARGAR NUEVO DOCUMENTO ======
                    const limpiarContenedoresDocumento = function() {
                        // Limpiar canvas de PDF
                        const canvas = document.getElementById('pdfCanvas');
                        if (canvas) {
                            const ctx = canvas.getContext('2d');
                            if (ctx) {
                                ctx.clearRect(0, 0, canvas.width, canvas.height);
                            }
                            canvas.width = 0;
                            canvas.height = 0;
                        }
                        
                        // Ocultar TODOS los contenedores
                        const pdfContainer = document.getElementById('documentoPdfContainer');
                        if (pdfContainer) pdfContainer.style.display = 'none';
                        
                        const imgContainer = document.getElementById('documentoImagenContainer');
                        if (imgContainer) imgContainer.style.display = 'none';
                        
                        const embedContainer = document.getElementById('visorPdfEmbed');
                        if (embedContainer) {
                            embedContainer.style.display = 'none';
                            embedContainer.innerHTML = '';
                        }
                        
                        const visorLegacy = document.getElementById('visorDocumento');
                        if (visorLegacy) {
                            visorLegacy.style.display = 'none';
                            visorLegacy.src = '';
                        }
                        
                        // Limpiar imagen de documentos
                        const imgDocumento = document.getElementById('imgDocumento');
                        if (imgDocumento) {
                            imgDocumento.src = '';
                            imgDocumento.style.display = 'none';
                        }
                        
                        // Ocultar controles de PDF
                        const pdfControls = document.getElementById('pdfControls');
                        if (pdfControls) pdfControls.style.display = 'none';
                        
                        // Resetear variables globales de PDF si existen
                        if (typeof pdfDocFactura !== 'undefined' && pdfDocFactura) {
                            pdfDocFactura = null;
                        }
                        if (typeof pageNumFactura !== 'undefined') {
                            pageNumFactura = 1;
                        }
                    };
                    
                    // Ejecutar limpieza ANTES de buscar
                    limpiarContenedoresDocumento();
                    
                    Swal.fire({
                        title: 'Procesando',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => Swal.showLoading(),
                        didClose: () => {
                            // Limpiar overlay si se cierra
                            document.body.classList.remove('swal2-shown');
                            document.body.style.overflow = '';
                        }
                    });
            
                    fetch('/estadocuenta/descargar', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id, tipo })
                    })
                    .then(res => res.json())
                    .then(data => {
                        Swal.close();
            
                        if (!data.success) {
                            const mensaje = data.mensaje || '';
                            const esSinDocumento = mensaje.indexOf('no tiene') !== -1 && mensaje.indexOf('registrado') !== -1;
                            if (esSinDocumento) {
                                Swal.fire({
                                    icon: 'info',
                                    title: 'Documento no registrado',
                                    text: mensaje,
                                    showCancelButton: true,
                                    confirmButtonText: 'Registrar',
                                    cancelButtonText: 'Cerrar'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        abrirModalRegistroDocumento(id, tipo);
                                    }
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: mensaje
                                });
                            }
                            return;
                        }
            
                        // Si es INE, mostrar ambas imágenes (frente y reverso)
                        if (data.tipo === 'INE') {
                            const imgFrente = document.getElementById('imgINEfrente');
                            const imgReverso = document.getElementById('imgINEreverso');
                            
                            // Desactivar descarga en imágenes del INE
                            if (typeof desactivarDescargaImagen === 'function') {
                                if (imgFrente) desactivarDescargaImagen(imgFrente);
                                if (imgReverso) desactivarDescargaImagen(imgReverso);
                            }
                            
                            // Configurar imágenes con carga desde servidor (sin descarga)
                            imgFrente.src = data.frente;
                            imgReverso.src = data.reverso;
                            
                            // LIMPIAR el atributo de marcas "SIN VALOR" del overlay pdfWatermark si existe
                            // INE usa imágenes, no PDF.js, por lo que no debe tener este atributo
                            const watermark = document.getElementById('pdfWatermark');
                            if (watermark) {
                                watermark.removeAttribute('data-marcas-sin-valor');
                            }
                            
                            // Crear marcas de agua inmediatamente y después de que las imágenes se carguen
                            const crearMarcasAguaINE = function() {
                                if (typeof crearMarcasAgua === 'function') {
                                    crearMarcasAgua();
                                }
                            };
                            
                            // Crear marcas de agua inmediatamente
                            setTimeout(crearMarcasAguaINE, 100);
                            
                            imgFrente.onload = function() {
                                // Desactivar descarga después de cargar
                                if (typeof desactivarDescargaImagen === 'function') {
                                    desactivarDescargaImagen(imgFrente);
                                }
                                setTimeout(crearMarcasAguaINE, 300);
                            };
                            
                            imgReverso.onload = function() {
                                // Desactivar descarga después de cargar
                                if (typeof desactivarDescargaImagen === 'function') {
                                    desactivarDescargaImagen(imgReverso);
                                }
                                setTimeout(crearMarcasAguaINE, 300);
                            };
                            
                            // Mostrar modal de INE
                            const modalINE = document.getElementById('modalINE');
                            const bsModal = new bootstrap.Modal(modalINE);
                            bsModal.show();
                            
                            // Las marcas de agua se crearán automáticamente cuando el modal se muestre
                            // gracias al listener en documentacion_consulta.php
                        } 
                        // Para FAD_DOC, EVIDENCIA, FACTURA, CONTRATO - usar visor de imágenes con marca de agua
                        else if (data.tipo && data.url) {
                            const imgContainer = document.getElementById('documentoImagenContainer');
                            const pdfContainer = document.getElementById('documentoPdfContainer');
                            const imgDocumento = document.getElementById('imgDocumento');
                            const iframeDocumento = document.getElementById('iframeDocumento');
                            
                            if (!imgContainer || !pdfContainer) {
                                Swal.fire('Error', 'No se pudo cargar el visor de documentos', 'error');
                                return;
                            }
                            
                            // Determinar si es PDF o imagen
                            // Si tiene extension pdf o la URL contiene .pdf o esImagen es false explícitamente
                            // O si la URL es de Google Viewer (significa que es PDF)
                            const esPdf = (data.extension === 'pdf') || 
                                         (data.url && (data.url.includes('.pdf') || data.url.includes('docs.google.com/gview'))) || 
                                         (data.archivo && data.archivo.toLowerCase().endsWith('.pdf')) ||
                                         (data.esImagen === false);
                            
                            if (esPdf) {
                                // Para FACTURA, FAD_DOC, CONTRATO y EVIDENCIA, usar PDF.js con la misma función cargarPDFFactura
                                if (data.tipo === 'FACTURA' || data.tipo === 'FAD_DOC' || data.tipo === 'CONTRATO' || data.tipo === 'EVIDENCIA') {
                                    // Usar PDF.js para FACTURA, FAD_DOC, VALIDACIONES y EVIDENCIA con cargarPDFFactura
                                    const tipoNombre = data.tipo === 'FACTURA' ? 'FACTURA' : (data.tipo === 'FAD_DOC' ? 'FAD_DOC' : (data.tipo === 'EVIDENCIA' ? 'EVIDENCIA' : 'VALIDACIONES'));
                                    
                                    // Asegurar que el contenedor de visor simple esté oculto
                                    const embedContainer = document.getElementById('visorPdfEmbed');
                                    if (embedContainer) {
                                        embedContainer.style.display = 'none';
                                    }
                                    
                                    imgContainer.style.display = 'none';
                                    
                                    const visorLegacy = document.getElementById('visorDocumento');
                                    if (visorLegacy) {
                                        visorLegacy.style.display = 'none';
                                    }
                                    
                                    // Usar EXACTAMENTE la misma lógica que EVIDENCIA
                                    // IMPORTANTE: Usar directamente la URL que viene del backend (ya es el proxy local)
                                    let pdfUrl = data.url;
                                    
                                    // Solo procesar si viene de Google Viewer
                                    if (pdfUrl.includes('docs.google.com/gview')) {
                                        try {
                                            const urlParams = new URL(pdfUrl);
                                            const urlParam = urlParams.searchParams.get('url');
                                            if (urlParam) {
                                                pdfUrl = decodeURIComponent(urlParam);
                                            }
                                        } catch (e) {
                                        }
                                    }
                                    
                                    // NO reconstruir la URL si ya es una URL relativa del proxy local
                                    // El backend ya devuelve la URL correcta del proxy: /estadocuenta/verDocumento?fileName=...
                                    // Solo reconstruir si es una URL absoluta del S3 y no incluye el proxy
                                    if (pdfUrl.includes('http') && pdfUrl.includes('98.90.194.116') && pdfUrl.includes('downloadS3File')) {
                                        // Si es URL directa del S3, convertirla a proxy local para evitar descarga
                                        try {
                                            const urlParams = new URL(pdfUrl);
                                            const fileName = urlParams.searchParams.get('fileName');
                                            if (fileName) {
                                                pdfUrl = '/estadocuenta/verDocumento?fileName=' + encodeURIComponent(fileName);
                                            }
                                        } catch (e) {
                                            console.warn('No se pudo convertir URL a proxy local:', e);
                                        }
                                    }
                                    
                                    
                                    if (typeof cargarPDFFactura === 'function') {
                                        // Usar función específica para FACTURA, FAD_DOC y CONTRATO (sin fallback a iframe)
                                        cargarPDFFactura(pdfUrl);
                                    } else {
                                        console.error('PDF.js no está cargado o la función cargarPDFFactura no existe');
                                        Swal.fire('Error', 'El visor de PDF no está disponible', 'error');
                                    }
                                    
                                    // Los controles de zoom ya están en el HTML y se mostrarán automáticamente
                                    // El header se ocultará desde cargarPDFConPDFjs cuando se muestre el modal
                                }
                                // Este bloque ya no se usa - todos los tipos ahora usan PDF.js arriba
                                else if (false && (data.tipo === 'CONTRATO')) {
                                    const tipoNombre = {
                                        'FAD_DOC': 'FAD_DOC',
                                        'FACTURA': 'FACTURA',
                                        'CONTRATO': 'VALIDACIONES'
                                    };
                                    
                                    // Obtener el modal element primero
                                    const modalElement = document.getElementById('modalDocumento');
                                    if (!modalElement) {
                                        Swal.fire('Error', 'No se encontró el modal de documentos', 'error');
                                        return;
                                    }
                                    
                                    // Ocultar contenedores de imagen y PDF.js
                                    imgContainer.style.display = 'none';
                                    pdfContainer.style.display = 'none';
                                    
                                    // Obtener el contenedor del modal
                                    const modalBody = document.getElementById('documentoModalBody');
                                    if (!modalBody) {
                                        Swal.fire('Error', 'No se encontró el contenedor del modal', 'error');
                                        return;
                                    }
                                    
                                    // Ocultar el iframe legacy si existe
                                    const visorLegacy = document.getElementById('visorDocumento');
                                    if (visorLegacy) {
                                        visorLegacy.style.display = 'none';
                                    }
                                    
                                    // Crear o reutilizar un contenedor para el embed
                                    let embedContainer = document.getElementById('visorPdfEmbed');
                                    if (!embedContainer) {
                                        embedContainer = document.createElement('div');
                                        embedContainer.id = 'visorPdfEmbed';
                                        embedContainer.style.cssText = 'position: absolute; top: 0; left: 0; right: 0; bottom: 0; width: 100%; height: 100%; display: none; overflow: auto; background-color: #525252;';
                                        modalBody.appendChild(embedContainer);
                                    }
                                    
                                    // Limpiar contenido anterior
                                    embedContainer.innerHTML = '';
                                    
                                    // Asegurar que el contenedor esté visible y tenga el tamaño correcto
                                    embedContainer.style.display = 'block';
                                    embedContainer.style.position = 'absolute';
                                    embedContainer.style.top = '0';
                                    embedContainer.style.left = '0';
                                    embedContainer.style.right = '0';
                                    embedContainer.style.bottom = '0';
                                    embedContainer.style.width = '100%';
                                    embedContainer.style.height = '100%';
                                    embedContainer.style.overflow = 'auto';
                                    embedContainer.style.backgroundColor = '#525252';
                                    
                                    // Crear wrapper para el PDF
                                    // Este wrapper se ajustará cuando se haga zoom
                                    const pdfWrapper = document.createElement('div');
                                    pdfWrapper.id = 'pdfWrapperContrato';
                                    pdfWrapper.style.cssText = 'position: relative; top: 0; left: 0; width: 100%; height: 100%;';
                                    
                                    // Crear contenedor con marca de agua (similar a las imágenes)
                                    const watermarkContainer = document.createElement('div');
                                    watermarkContainer.className = 'watermark-container';
                                    watermarkContainer.style.cssText = 'position: relative; width: 100%; height: 100%; display: block;';
                                    
                                    // Crear iframe para visualizar el PDF (más control que embed)
                                    const iframePdf = document.createElement('iframe');
                                    
                                    // Para VALIDACIONES OK (CONTRATO), agregar parámetros de zoom para que se abra sin zoom excesivo
                                    // Para FAD_DOC, ocultar la barra de herramientas
                                    // Para FACTURA, MOSTRAR la barra de herramientas
                                    let pdfUrl = data.url;
                                    const separator = pdfUrl.includes('#') ? '&' : '#';
                                    
                                    if (data.tipo === 'CONTRATO') {
                                        // Agregar parámetros para controlar el zoom inicial
                                        // #view=FitH ajusta el PDF al ancho de la ventana sin zoom excesivo
                                        // También agregar #toolbar=0 para ocultar la barra de herramientas del PDF
                                        pdfUrl = pdfUrl + separator + 'view=FitH&toolbar=0';
                                    } else if (data.tipo === 'FAD_DOC') {
                                        // Para FAD_DOC, ocultar la barra de herramientas
                                        pdfUrl = pdfUrl + separator + 'toolbar=0';
                                    }
                                    
                                    iframePdf.src = pdfUrl;
                                    iframePdf.style.cssText = 'position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0; display: block; pointer-events: auto;';
                                    iframePdf.setAttribute('type', 'application/pdf');
                                    iframePdf.setAttribute('frameborder', '0');
                                    iframePdf.setAttribute('scrolling', 'auto');
                                    
                                    // Manejar errores de carga del iframe
                                    iframePdf.addEventListener('error', function(e) {
                                        Swal.fire('Error', 'No se pudo cargar el documento PDF', 'error');
                                    });
                                    
                                    // Crear overlay para marcas de agua que cubra todo el visor
                                    // Este overlay se colocará directamente en el embedContainer para cubrir todo
                                    const watermarkOverlay = document.createElement('div');
                                    watermarkOverlay.className = 'watermark-overlay';
                                    watermarkOverlay.id = 'watermarkOverlayPdf';
                                    // Permitir overflow visible y mantener las franjas rojas del CSS
                                    watermarkOverlay.style.cssText = 'position: absolute; top: 0; left: 0; right: 0; bottom: 0; width: 100%; height: 100%; pointer-events: none; z-index: 10; overflow: visible;';
                                    
                                    // Crear overlay de protección que bloquea el click derecho y el menú superior
                                    // Este overlay tiene dos partes: una para el menú superior y otra invisible para bloquear click derecho
                                    
                                    // Overlay para bloquear SOLO click derecho en el menú superior (primeros 60px)
                                    // Usar pointer-events: none para permitir clicks izquierdos en el menú
                                    const menuOverlay = document.createElement('div');
                                    menuOverlay.id = 'pdfMenuOverlay';
                                    
                                    // Para otros tipos, mantener el comportamiento original
                                    menuOverlay.style.cssText = 'position: absolute; top: 0; left: 0; width: 100%; height: 60px; z-index: 20; background: transparent; pointer-events: none;';
                                    
                                    // Solo bloquear click derecho en el área del menú (parte superior)
                                    // Capturar en fase de captura para bloquear antes de que llegue al iframe
                                    embedContainer.addEventListener('contextmenu', function(e) {
                                        // Solo bloquear si el click es en el área superior (primeros 60px)
                                        const rect = embedContainer.getBoundingClientRect();
                                        const clickY = e.clientY - rect.top;
                                        if (clickY <= 60) {
                                            e.preventDefault();
                                            e.stopPropagation();
                                            e.stopImmediatePropagation();
                                            return false;
                                        }
                                    }, true);
                                    
                                    // Bloquear SOLO click derecho en el contenedor (permite clicks izquierdos y scroll)
                                    embedContainer.addEventListener('contextmenu', function(e) {
                                        // Solo bloquear si es click derecho
                                        e.preventDefault();
                                        e.stopPropagation();
                                        e.stopImmediatePropagation();
                                        return false;
                                    }, true);
                                    
                                    watermarkContainer.addEventListener('contextmenu', function(e) {
                                        // Solo bloquear si es click derecho
                                        e.preventDefault();
                                        e.stopPropagation();
                                        e.stopImmediatePropagation();
                                        return false;
                                    }, true);
                                    
                                    // Bloquear SOLO mousedown del botón derecho en embedContainer
                                    embedContainer.addEventListener('mousedown', function(e) {
                                        if (e.button === 2) { // SOLO botón derecho
                                            e.preventDefault();
                                            e.stopPropagation();
                                            e.stopImmediatePropagation();
                                            return false;
                                        }
                                        // Permitir clicks izquierdos (button === 0) - no hacer nada
                                    }, true);
                                    
                                    // Bloquear SOLO mouseup del botón derecho en embedContainer
                                    embedContainer.addEventListener('mouseup', function(e) {
                                        if (e.button === 2) { // SOLO botón derecho
                                            e.preventDefault();
                                            e.stopPropagation();
                                            e.stopImmediatePropagation();
                                            return false;
                                        }
                                        // Permitir clicks izquierdos - no hacer nada
                                    }, true);
                                    
                                    // Overlay de protección que bloquea SOLO click derecho pero permite click izquierdo y scroll
                                    // Usamos pointer-events: none para que NO bloquee clicks izquierdos ni scroll
                                    // Pero capturamos eventos en fase de captura para bloquear SOLO el click derecho
                                    const protectionOverlay = document.createElement('div');
                                    protectionOverlay.id = 'pdfProtectionOverlay';
                                    protectionOverlay.style.cssText = 'position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 30; background: transparent; pointer-events: none;';
                                    
                                    // IMPORTANTE: Como el overlay tiene pointer-events: none, los eventos pasan a través
                                    // Por eso capturamos en el contenedor padre (embedContainer) en fase de captura
                                    // para bloquear SOLO el click derecho antes de que llegue al iframe
                                    
                                    // El menuOverlay ya no necesita listeners porque tiene pointer-events: none
                                    // Los clicks pasan a través y se bloquean en embedContainer
                                    
                                    // También capturar en el documento para mayor seguridad (máxima prioridad)
                                    const contextMenuHandler = function(e) {
                                        // Solo bloquear si el evento viene del área del PDF
                                        const target = e.target;
                                        if (embedContainer.contains(target) || 
                                            watermarkContainer.contains(target) || 
                                            pdfWrapper.contains(target) ||
                                            iframePdf.contains(target) ||
                                            protectionOverlay.contains(target) ||
                                            menuOverlay.contains(target)) {
                                            e.preventDefault();
                                            e.stopPropagation();
                                            e.stopImmediatePropagation();
                                            return false;
                                        }
                                    };
                                    // Usar capture phase y alta prioridad
                                    document.addEventListener('contextmenu', contextMenuHandler, { capture: true, passive: false });
                                    
                                    // También capturar mousedown del botón derecho a nivel de documento
                                    const mouseDownHandler = function(e) {
                                        if (e.button === 2) { // Botón derecho
                                            const target = e.target;
                                            if (embedContainer.contains(target) || 
                                                watermarkContainer.contains(target) || 
                                                pdfWrapper.contains(target) ||
                                                iframePdf.contains(target) ||
                                                protectionOverlay.contains(target) ||
                                                menuOverlay.contains(target)) {
                                                e.preventDefault();
                                                e.stopPropagation();
                                                e.stopImmediatePropagation();
                                                return false;
                                            }
                                        }
                                    };
                                    document.addEventListener('mousedown', mouseDownHandler, { capture: true, passive: false });
                                    
                                    // También bloquear a nivel de window para máxima seguridad
                                    const windowContextMenuHandler = function(e) {
                                        const target = e.target;
                                        if (embedContainer.contains(target) || 
                                            watermarkContainer.contains(target) || 
                                            pdfWrapper.contains(target) ||
                                            iframePdf.contains(target) ||
                                            protectionOverlay.contains(target) ||
                                            menuOverlay.contains(target)) {
                                            e.preventDefault();
                                            e.stopPropagation();
                                            e.stopImmediatePropagation();
                                            return false;
                                        }
                                    };
                                    window.addEventListener('contextmenu', windowContextMenuHandler, { capture: true, passive: false });
                                    
                                    // Limpiar todos los listeners cuando se cierre el modal
                                    modalElement.addEventListener('hidden.bs.modal', function() {
                                        document.removeEventListener('contextmenu', contextMenuHandler, { capture: true });
                                        document.removeEventListener('mousedown', mouseDownHandler, { capture: true });
                                        window.removeEventListener('contextmenu', windowContextMenuHandler, { capture: true });
                                    }, { once: true });
                                    
                                    // Bloquear click derecho también en el iframe directamente (ya está arriba, pero lo mantenemos)
                                    // Nota: Estos listeners ya están definidos arriba en la sección de protección reforzada
                                    
                                    // Bloquear arrastrar archivos
                                    protectionOverlay.addEventListener('dragover', function(e) {
                                        e.preventDefault();
                                        return false;
                                    }, true);
                                    
                                    protectionOverlay.addEventListener('drop', function(e) {
                                        e.preventDefault();
                                        return false;
                                    }, true);
                                    
                                    // Bloquear atajos de teclado en el contenedor
                                    embedContainer.addEventListener('keydown', function(e) {
                                        // Bloquear Ctrl+S, Ctrl+P, F12, etc.
                                        if (e.ctrlKey && (e.key === 's' || e.key === 'S' || e.key === 'p' || e.key === 'P')) {
                                            e.preventDefault();
                                            return false;
                                        }
                                        if (e.key === 'F12' || (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'J'))) {
                                            e.preventDefault();
                                            return false;
                                        }
                                    }, true);
                                    
                                    // Intentar bloquear el menú del PDF dentro del iframe cuando se carga
                                    iframePdf.addEventListener('load', function() {
                                        try {
                                            // Intentar acceder al documento del iframe para bloquear eventos
                                            const iframeDoc = iframePdf.contentDocument || iframePdf.contentWindow.document;
                                            if (iframeDoc) {
                                                // Bloquear click derecho dentro del iframe
                                                iframeDoc.addEventListener('contextmenu', function(e) {
                                                    e.preventDefault();
                                                    e.stopPropagation();
                                                    e.stopImmediatePropagation();
                                                    return false;
                                                }, true);
                                                
                                                // Bloquear mousedown del botón derecho dentro del iframe
                                                iframeDoc.addEventListener('mousedown', function(e) {
                                                    if (e.button === 2) {
                                                        e.preventDefault();
                                                        e.stopPropagation();
                                                        e.stopImmediatePropagation();
                                                        return false;
                                                    }
                                                }, true);
                                                
                                                // Bloquear otros eventos dentro del iframe
                                                iframeDoc.addEventListener('selectstart', function(e) {
                                                    e.preventDefault();
                                                    return false;
                                                }, true);
                                                
                                                // Bloquear también en el body del iframe
                                                if (iframeDoc.body) {
                                                    iframeDoc.body.addEventListener('contextmenu', function(e) {
                                                        e.preventDefault();
                                                        e.stopPropagation();
                                                        e.stopImmediatePropagation();
                                                        return false;
                                                    }, true);
                                                    
                                                    iframeDoc.body.addEventListener('mousedown', function(e) {
                                                        if (e.button === 2) {
                                                            e.preventDefault();
                                                            e.stopPropagation();
                                                            e.stopImmediatePropagation();
                                                            return false;
                                                        }
                                                    }, true);
                                                }
                                            }
                                        } catch (e) {
                                            // Error de CORS - normal cuando el iframe carga contenido de otro dominio
                                            // El overlay de protección seguirá funcionando
                                        }
                                    });
                                    
                                    // Agregar elementos al contenedor (orden importante: iframe abajo, overlays arriba)
                                    watermarkContainer.appendChild(iframePdf);
                                    pdfWrapper.appendChild(watermarkContainer);
                                    
                                    // Agregar overlays de protección al contenedor principal
                                    // IMPORTANTE: El protectionOverlay debe ir DESPUÉS del menuOverlay para tener mayor z-index
                                    pdfWrapper.appendChild(menuOverlay); // Bloquea clicks en el menú superior
                                    pdfWrapper.appendChild(protectionOverlay); // Bloquea click derecho en todo (z-index más alto)
                                    
                                    embedContainer.appendChild(pdfWrapper);
                                    
                                    // Agregar overlay de marca de agua directamente al embedContainer para que cubra todo
                                    embedContainer.appendChild(watermarkOverlay);
                                    
                                    // REFORZAR PROTECCIÓN CONTRA CLICK DERECHO - Múltiples capas de protección
                                    
                                    // 1. Bloquear en el contenedor principal (embedContainer)
                                    embedContainer.addEventListener('contextmenu', function(e) {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        e.stopImmediatePropagation();
                                        return false;
                                    }, true);
                                    
                                    // 2. Bloquear en el pdfWrapper
                                    pdfWrapper.addEventListener('contextmenu', function(e) {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        e.stopImmediatePropagation();
                                        return false;
                                    }, true);
                                    
                                    // 3. Bloquear también con mousedown para capturar antes del contextmenu (ya está arriba, no duplicar)
                                    // Los listeners de mousedown ya están definidos arriba en embedContainer
                                    
                                    // Bloquear SOLO click derecho en pdfWrapper
                                    pdfWrapper.addEventListener('mousedown', function(e) {
                                        if (e.button === 2) { // SOLO botón derecho
                                            e.preventDefault();
                                            e.stopPropagation();
                                            e.stopImmediatePropagation();
                                            return false;
                                        }
                                        // Permitir clicks izquierdos - no hacer nada
                                    }, true);
                                    
                                    // Bloquear SOLO click derecho en iframePdf
                                    iframePdf.addEventListener('mousedown', function(e) {
                                        if (e.button === 2) { // SOLO botón derecho
                                            e.preventDefault();
                                            e.stopPropagation();
                                            e.stopImmediatePropagation();
                                            return false;
                                        }
                                        // Permitir clicks izquierdos - no hacer nada
                                    }, true);
                                    
                                    // 4. Bloquear también en el overlay de marca de agua
                                    watermarkOverlay.addEventListener('contextmenu', function(e) {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        e.stopImmediatePropagation();
                                        return false;
                                    }, true);
                                    
                                    watermarkOverlay.addEventListener('mousedown', function(e) {
                                        if (e.button === 2) { // Botón derecho del mouse
                                            e.preventDefault();
                                            e.stopPropagation();
                                            e.stopImmediatePropagation();
                                            return false;
                                        }
                                    }, true);
                                    
                                    // Para VALIDACIONES OK (CONTRATO), FAD_DOC y FACTURACION OK (FACTURA), agregar controles de zoom
                                    // IMPORTANTE: Las variables embedContainer, pdfWrapper, watermarkContainer deben estar en scope
                                    if (data.tipo === 'CONTRATO' || data.tipo === 'FAD_DOC' || data.tipo === 'FACTURA') {
                                        // Variables para el zoom
                                        let currentZoom = 1.0;
                                        const minZoom = 0.5;
                                        const maxZoom = 3.0;
                                        
                                        // Asegurar que tenemos referencias a los elementos necesarios
                                        const zoomEmbedContainer = embedContainer;
                                        const zoomPdfWrapper = pdfWrapper;
                                        const zoomWatermarkContainer = watermarkContainer;
                                        
                                        // Crear contenedor de controles de zoom
                                        const zoomControls = document.createElement('div');
                                        zoomControls.id = 'zoomControls' + data.tipo; // ID único por tipo
                                        zoomControls.style.cssText = 'position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); background-color: rgba(0,0,0,0.7); padding: 10px 20px; border-radius: 25px; z-index: 1000; display: flex; align-items: center; gap: 15px;';
                                        
                                        // Botón zoom out
                                        const btnZoomOut = document.createElement('button');
                                        btnZoomOut.className = 'btn btn-sm btn-light';
                                        btnZoomOut.innerHTML = '<i class="fa fa-search-minus"></i>';
                                        btnZoomOut.style.cssText = 'min-width: 40px;';
                                        btnZoomOut.title = 'Alejar';
                                        
                                        // Indicador de zoom
                                        const zoomLevel = document.createElement('span');
                                        zoomLevel.id = 'zoomLevel' + data.tipo; // ID único por tipo
                                        zoomLevel.style.cssText = 'color: white; font-size: 0.9rem; min-width: 60px; text-align: center;';
                                        zoomLevel.textContent = '100%';
                                        
                                        // Botón zoom in
                                        const btnZoomIn = document.createElement('button');
                                        btnZoomIn.className = 'btn btn-sm btn-light';
                                        btnZoomIn.innerHTML = '<i class="fa fa-search-plus"></i>';
                                        btnZoomIn.style.cssText = 'min-width: 40px;';
                                        btnZoomIn.title = 'Acercar';
                                        
                                        // Botón reset
                                        const btnReset = document.createElement('button');
                                        btnReset.className = 'btn btn-sm btn-light';
                                        btnReset.innerHTML = '<i class="fa fa-undo"></i>';
                                        btnReset.style.cssText = 'min-width: 40px;';
                                        btnReset.title = 'Restablecer zoom';
                                        
                                        // Función para aplicar zoom
                                        const applyZoom = function() {
                                            // Usar las referencias locales para asegurar que funcionen
                                            const container = zoomEmbedContainer;
                                            const wrapper = zoomPdfWrapper;
                                            const watermarkContainer = zoomWatermarkContainer;
                                            
                                            // Obtener las dimensiones actuales del contenedor visible (el modal se mantiene igual)
                                            const containerWidth = container.clientWidth || container.offsetWidth;
                                            const containerHeight = container.clientHeight || container.offsetHeight;
                                            
                                            if (containerWidth === 0 || containerHeight === 0) {
                                                // Si las dimensiones no están disponibles aún, intentar de nuevo
                                                setTimeout(applyZoom, 100);
                                                return;
                                            }
                                            
                                            // Guardar la posición actual de scroll antes del cambio
                                            const scrollLeftBefore = container.scrollLeft;
                                            const scrollTopBefore = container.scrollTop;
                                            
                                            // Calcular el centro visual actual (posición de scroll + mitad del viewport)
                                            const centerXBefore = scrollLeftBefore + (containerWidth / 2);
                                            const centerYBefore = scrollTopBefore + (containerHeight / 2);
                                            
                                            // IMPORTANTE: El wrapper debe tener el tamaño escalado para que el scroll funcione
                                            const scaledWidth = containerWidth * currentZoom;
                                            const scaledHeight = containerHeight * currentZoom;
                                            
                                            // El wrapper necesita tener el tamaño escalado para permitir scroll correcto
                                            wrapper.style.width = scaledWidth + 'px';
                                            wrapper.style.height = scaledHeight + 'px';
                                            wrapper.style.minWidth = scaledWidth + 'px';
                                            wrapper.style.minHeight = scaledHeight + 'px';
                                            
                                            // CORRECCIÓN: El watermarkContainer NO debe escalarse
                                            // Mantiene su tamaño base sin transform
                                            watermarkContainer.style.position = 'absolute';
                                            watermarkContainer.style.top = '0';
                                            watermarkContainer.style.left = '0';
                                            watermarkContainer.style.width = containerWidth + 'px';
                                            watermarkContainer.style.height = containerHeight + 'px';
                                            watermarkContainer.style.transform = '';
                                            watermarkContainer.style.transformOrigin = '';
                                            
                                            // CORRECCIÓN: Aplicar zoom SOLO al iframe del PDF usando transform scale
                                            // Esto evita que la marca de agua se escale
                                            const iframePdf = watermarkContainer.querySelector('iframe');
                                            if (iframePdf) {
                                                // El iframe mantiene su tamaño base
                                                iframePdf.style.width = containerWidth + 'px';
                                                iframePdf.style.height = containerHeight + 'px';
                                                // Aplicar transform scale SOLO al iframe (no al contenedor)
                                                iframePdf.style.transform = `scale(${currentZoom})`;
                                                iframePdf.style.transformOrigin = 'top left';
                                            }
                                            
                                            // Asegurar que el contenedor tenga overflow para scroll
                                            container.style.overflow = 'auto';
                                            
                                            // Actualizar el indicador de zoom
                                            zoomLevel.textContent = Math.round(currentZoom * 100) + '%';
                                            
                                            // Actualizar marcas de agua después del zoom (sin escalar)
                                            setTimeout(() => {
                                                if (typeof crearMarcasAgua === 'function') {
                                                    crearMarcasAgua();
                                                }
                                            }, 100);
                                            
                                            // Ajustar la posición de scroll después del zoom para mantener el centro visual
                                            setTimeout(() => {
                                                // Calcular la nueva posición de scroll para mantener el centro visual
                                                const newScrollLeft = centerXBefore - (containerWidth / 2);
                                                const newScrollTop = centerYBefore - (containerHeight / 2);
                                                
                                                // Asegurar que no exceda los límites
                                                const maxScrollLeft = Math.max(0, container.scrollWidth - containerWidth);
                                                const maxScrollTop = Math.max(0, container.scrollHeight - containerHeight);
                                                
                                                container.scrollLeft = Math.max(0, Math.min(newScrollLeft, maxScrollLeft));
                                                container.scrollTop = Math.max(0, Math.min(newScrollTop, maxScrollTop));
                                            }, 50);
                                        };
                                        
                                        // Event listeners para los botones
                                        btnZoomOut.addEventListener('click', function(e) {
                                            e.preventDefault();
                                            e.stopPropagation();
                                            if (currentZoom > minZoom) {
                                                currentZoom = Math.max(currentZoom - 0.25, minZoom);
                                                applyZoom();
                                            }
                                        });
                                        
                                        btnZoomIn.addEventListener('click', function(e) {
                                            e.preventDefault();
                                            e.stopPropagation();
                                            if (currentZoom < maxZoom) {
                                                currentZoom = Math.min(currentZoom + 0.25, maxZoom);
                                                applyZoom();
                                            }
                                        });
                                        
                                        btnReset.addEventListener('click', function(e) {
                                            e.preventDefault();
                                            e.stopPropagation();
                                            currentZoom = 1.0;
                                            applyZoom();
                                        });
                                        
                                        // Agregar botones al contenedor
                                        zoomControls.appendChild(btnZoomOut);
                                        zoomControls.appendChild(zoomLevel);
                                        zoomControls.appendChild(btnZoomIn);
                                        zoomControls.appendChild(btnReset);
                                        
                                        // Agregar controles al modal body
                                        const modalBody = document.getElementById('documentoModalBody');
                                        if (modalBody) {
                                            modalBody.appendChild(zoomControls);
                                        }
                                        
                                        // Limpiar controles cuando se cierre el modal
                                        modalElement.addEventListener('hidden.bs.modal', function() {
                                            const controls = document.getElementById('zoomControls' + data.tipo);
                                            if (controls) {
                                                controls.remove();
                                            }
                                            // Resetear zoom completamente usando las referencias locales
                                            currentZoom = 1.0;
                                            zoomPdfWrapper.style.width = '100%';
                                            zoomPdfWrapper.style.height = '100%';
                                            zoomPdfWrapper.style.minWidth = '';
                                            zoomPdfWrapper.style.minHeight = '';
                                            zoomWatermarkContainer.style.position = 'relative';
                                            zoomWatermarkContainer.style.top = '';
                                            zoomWatermarkContainer.style.left = '';
                                            zoomWatermarkContainer.style.width = '100%';
                                            zoomWatermarkContainer.style.height = '100%';
                                            zoomWatermarkContainer.style.transform = '';
                                            zoomWatermarkContainer.style.transformOrigin = '';
                                            const iframePdf = zoomWatermarkContainer.querySelector('iframe');
                                            if (iframePdf) {
                                                iframePdf.style.width = '100%';
                                                iframePdf.style.height = '100%';
                                                iframePdf.style.transform = '';
                                                iframePdf.style.transformOrigin = '';
                                            }
                                        }, { once: true });
                                        
                                        // Zoom con rueda del mouse (Ctrl + scroll) usando la referencia local
                                        zoomEmbedContainer.addEventListener('wheel', function(e) {
                                            if (e.ctrlKey || e.metaKey) {
                                                e.preventDefault();
                                                if (e.deltaY < 0) {
                                                    // Scroll hacia arriba = zoom in
                                                    if (currentZoom < maxZoom) {
                                                        currentZoom = Math.min(currentZoom + 0.1, maxZoom);
                                                        applyZoom();
                                                    }
                                                } else {
                                                    // Scroll hacia abajo = zoom out
                                                    if (currentZoom > minZoom) {
                                                        currentZoom = Math.max(currentZoom - 0.1, minZoom);
                                                        applyZoom();
                                                    }
                                                }
                                            }
                                        }, { passive: false });
                                        
                                        // Aplicar zoom inicial después de que el iframe se cargue
                                        const applyInitialZoom = function() {
                                            // Pequeño delay para asegurar que el iframe esté completamente cargado y el DOM esté listo
                                            setTimeout(function() {
                                                applyZoom();
                                            }, 300);
                                        };
                                        
                                        // Aplicar zoom cuando el iframe se carga
                                        iframePdf.addEventListener('load', applyInitialZoom, { once: true });
                                        
                                        // También aplicar zoom inicial inmediatamente si el iframe ya está cargado
                                        if (iframePdf.complete || iframePdf.readyState === 'complete') {
                                            applyInitialZoom();
                                        }
                                    }
                                    
                                    // Asegurar que el contenedor esté visible
                                    embedContainer.style.display = 'block';
                                    
                                    // También prevenir click derecho en el contenedor
                                    embedContainer.addEventListener('contextmenu', function(e) {
                                        e.preventDefault();
                                        return false;
                                    }, true);
                                    
                                    // Crear marcas de agua después de que el iframe se cargue
                                    iframePdf.addEventListener('load', function() {
                                        setTimeout(() => {
                                            if (typeof crearMarcasAgua === 'function') {
                                                crearMarcasAgua();
                                            }
                                        }, 500);
                                    });
                                    
                                    // Actualizar título del modal según el tipo
                                    const modalTitle = document.querySelector('#modalDocumento .modal-title');
                                    if (modalTitle) {
                                        modalTitle.textContent = tipoNombre[data.tipo] || data.tipo;
                                    }
                                    
                                    // Prevenir atajos de teclado para descargar
                                    if (typeof prevenirAtajosDescarga === 'function') {
                                        prevenirAtajosDescarga('modalDocumento');
                                    }
                                    
                                    // Crear marcas de agua cuando el modal se muestre completamente
                                    modalElement.addEventListener('shown.bs.modal', function() {
                                        // Llamar múltiples veces para asegurar que se generen correctamente
                                        setTimeout(() => {
                                            if (typeof crearMarcasAgua === 'function') {
                                                crearMarcasAgua();
                                            }
                                        }, 300);
                                        setTimeout(() => {
                                            if (typeof crearMarcasAgua === 'function') {
                                                crearMarcasAgua();
                                            }
                                        }, 800);
                                        setTimeout(() => {
                                            if (typeof crearMarcasAgua === 'function') {
                                                crearMarcasAgua();
                                            }
                                        }, 1500);
                                        // Llamar una vez más después de que el iframe cargue completamente
                                        setTimeout(() => {
                                            if (typeof crearMarcasAgua === 'function') {
                                                crearMarcasAgua();
                                            }
                                        }, 2500);
                                    }, { once: true });
                                    
                                    // Limpiar cuando se cierre el modal
                                    modalElement.addEventListener('hidden.bs.modal', function() {
                                        // Limpiar el contenedor
                                        const embedContainer = document.getElementById('visorPdfEmbed');
                                        if (embedContainer) {
                                            // Limpiar el src del iframe para liberar recursos
                                            const iframe = embedContainer.querySelector('iframe');
                                            if (iframe) {
                                                iframe.src = '';
                                            }
                                            
                                            // Remover los overlays de protección
                                            const protectionOverlay = document.getElementById('pdfProtectionOverlay');
                                            if (protectionOverlay) {
                                                protectionOverlay.remove();
                                            }
                                            const menuOverlay = document.getElementById('pdfMenuOverlay');
                                            if (menuOverlay) {
                                                menuOverlay.remove();
                                            }
                                            
                                            embedContainer.innerHTML = '';
                                            embedContainer.style.display = 'none';
                                        }
                                        
                                        // Asegurar que el body no tenga clases bloqueantes
                                        document.body.classList.remove('modal-open');
                                        document.body.style.overflow = '';
                                        document.body.style.paddingRight = '';
                                        
                                        // Remover overlays de Bootstrap si existen
                                        const backdrops = document.querySelectorAll('.modal-backdrop');
                                        backdrops.forEach(backdrop => backdrop.remove());
                                    }, { once: true });
                                    
                                    // Mostrar modal después de un pequeño delay para asegurar que todo esté configurado
                                    setTimeout(() => {
                                        const modal = new bootstrap.Modal(modalElement);
                                        modal.show();
                                    }, 100);
                                    
                                } else {
                                    // No debería llegar aquí - todos los tipos de PDF usan el bloque de arriba con FACTURA
                                    console.error('Tipo de documento PDF no reconocido:', data.tipo);
                                    Swal.fire('Error', 'Tipo de documento no soportado: ' + data.tipo, 'error');
                                }
                            } else {
                                // Si no es PDF, mostrar error - todos los documentos deben ser PDF
                                Swal.fire('Error', 'El documento debe ser un archivo PDF', 'error');
                            }
                            
                            // Actualizar título del modal según el tipo
                            const modalTitle = document.querySelector('#modalDocumento .modal-title');
                            if (modalTitle) {
                                const tipoNombre = {
                                    'FAD_DOC': 'FAD_DOC',
                                    'EVIDENCIA': 'EVIDENCIA',
                                    'FACTURA': 'FACTURA',
                                    'CONTRATO': 'VALIDACIONES'
                                };
                                modalTitle.textContent = tipoNombre[data.tipo] || 'Documento';
                            }
                            
                            // El modal se muestra automáticamente desde cargarPDFFactura
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: 'Respuesta del servidor inválida. Tipo: ' + (data.tipo || 'N/A'),
                                icon: 'error'
                            });
                            console.error('Respuesta inválida:', data);
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        Swal.fire('Error', 'Error de comunicación', 'error');
                    });
                });
            
            });
            </script>

JS;





        # -----------------------------
        # GET NORMAL
        # -----------------------------
        self::set("titulo", "Documentación");
        self::set("script", $script);
        return self::render("documentacion_consulta");
    }

    public static function contentDispositionInline(string $filename): string
    {
        $q = rawurlencode($filename);
        return "inline; filename=\"$filename\"; filename*=UTF-8''$q";
    }

    public static function httpGet(string $url, int $timeout = 10)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_FOLLOWLOCATION => true
        ]);

        $data = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [$status, $data];
    }

    public static function httpPostJson(string $url, array $payload, array $headers = [])
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array_merge([
                'Content-Type: application/json'
            ], $headers),
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 10
        ]);

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [$status, json_decode($response, true)];
    }
public function descargar()
{
    try {

        header('Content-Type: application/json; charset=utf-8');

        $raw = file_get_contents('php://input');
        if (!$raw) {
            error_log("DESCARGAR - Body vacío");
            echo json_encode([
                'success' => false,
                'mensaje' => 'Body vacío'
            ]);
            exit;
        }

        $input = json_decode($raw, true);

        if (!$input) {
            error_log("DESCARGAR - JSON inválido");
            echo json_encode([
                'success' => false,
                'mensaje' => 'JSON inválido'
            ]);
            exit;
        }

        $id   = $input['id']   ?? null;
        $tipo = strtoupper($input['tipo'] ?? '');

        if (!$id || !$tipo) {
            error_log("DESCARGAR - Parámetros incompletos: ID=$id, TIPO=$tipo");
            echo json_encode([
                'success' => false,
                'mensaje' => 'Parámetros incompletos'
            ]);
            exit;
        }

        error_log("====== DESCARGAR INICIADO ======");
        error_log("BUSCANDO: ID=$id, TIPO=$tipo");

        // Nombre del documento para mensajes al usuario
        $nombresDoc = [
            'INE' => 'INE',
            'FACTURA' => 'Factura',
            'CONTRATO' => 'Validaciones',
            'FAD_DOC' => 'FAD_DOC',
            'EVIDENCIA' => 'Evidencia'
        ];
        $nombreDoc = $nombresDoc[$tipo] ?? $tipo;

        // Helper: comprobar si un archivo existe en S3 (HEAD)
        $existeEnS3 = function ($fileName) {
            $s3Url = "http://98.90.194.116/audit-app-0.0.1-SNAPSHOT_1/s3/downloadS3File?fileName=" . urlencode($fileName);
            $ch = curl_init($s3Url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_NOBODY => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 8,
            ]);
            curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            return $code === 200;
        };

        // ---------------- 3RA FORMA: SOLO PARA DOCUMENTOS EN oferta_documentos ----------------
        $consultarBDTerceraForma = function ($idCredito, $tipoDocumento) {
    try {
        error_log("3RA FORMA - Consultando BD via DAO: ID=$idCredito, TIPO=$tipoDocumento");
        
        // Mapeo correcto según tu BD
        $tiposBD = [
            'FACTURA' => 'FACTURA',      // BD: FACTURA
            'CONTRATO' => 'VALIDACIONES', // BD: VALIDACIONES  
            'FAD_DOC' => 'FAD',          // BD: FAD
            'EVIDENCIA' => 'EVIDENCIA',  // BD: EVIDENCIA
        ];
        
        $tipoBD = $tiposBD[$tipoDocumento] ?? null;
        
        if (!$tipoBD) {
            error_log("3RA FORMA - Tipo no mapeado: $tipoDocumento");
            return null;
        }
        
        // ¡IMPORTANTE! Usar el DAO como lo hace FAD_DOC y EVIDENCIA
        // Pero con una pequeña modificación para aceptar todos los tipos
        
        if ($tipoDocumento === 'FAD_DOC' || $tipoDocumento === 'EVIDENCIA') {
            // Para estos tipos, usar el DAO existente
            $res = EstadoCuentaDAO::obtenerDocumentoOferta($idCredito, $tipoDocumento);
        } else {
            // Para FACTURA y CONTRATO, usar una función similar
            // O mejor, modificar el DAO para aceptar todos los tipos
            $res = $this->consultarDocumentoGenerico($idCredito, $tipoBD);
        }
        
        if ($res['success'] && isset($res['datos']['nombre_archivo']) && !empty($res['datos']['nombre_archivo'])) {
            $nombreArchivo = $res['datos']['nombre_archivo'];
            error_log("3RA FORMA - Encontrado via DAO: " . $nombreArchivo);
            return $nombreArchivo;
        }
        
        error_log("3RA FORMA - No encontrado via DAO");
        return null;
        
    } catch (\Throwable $e) {
        error_log("3RA FORMA - Error en DAO: " . $e->getMessage());
        return null;
    }
};

        $buscarLocal = function ($idCredito, $tipoDocumento) {
            $directorioBase = __DIR__ . '/../../uploads/documentos/doc_cliente';
            
            if (!is_dir($directorioBase)) {
                error_log("1RA FORMA - Directorio NO existe: {$directorioBase}");
                return null;
            }
            
            error_log("1RA FORMA - Buscando local: ID=$idCredito, TIPO=$tipoDocumento");

            $idSeguro = preg_replace('/[^0-9]/', '', (string)$idCredito);
            $tipoSeguro = preg_replace('/[^A-Z0-9_-]/', '_', strtoupper((string)$tipoDocumento));

            if ($idSeguro === '' || $tipoSeguro === '') {
                error_log("1RA FORMA - ID o Tipo inválido");
                return null;
            }

            // Caso especial para INE: buscar frente y reverso
            if ($tipoSeguro === 'INE') {
                $patronFrente = $directorioBase . '/' . $idSeguro . '_INE_frente_*.{jpg,jpeg,png}';
                $patronReverso = $directorioBase . '/' . $idSeguro . '_INE_reverso_*.{jpg,jpeg,png}';
                
                $archivosFrente = glob($patronFrente, GLOB_BRACE);
                $archivosReverso = glob($patronReverso, GLOB_BRACE);
                
                if (!$archivosFrente || !$archivosReverso) {
                    error_log("1RA FORMA - INE no encontrado localmente");
                    return null;
                }

                usort($archivosFrente, function ($a, $b) {
                    return filemtime($b) <=> filemtime($a);
                });
                usort($archivosReverso, function ($a, $b) {
                    return filemtime($b) <=> filemtime($a);
                });

                $archivoFrente = basename($archivosFrente[0]);
                $archivoReverso = basename($archivosReverso[0]);

                error_log("1RA FORMA - INE encontrado localmente: $archivoFrente, $archivoReverso");
                
                return [
                    'esINE' => true,
                    'archivoFrente' => $archivoFrente,
                    'archivoReverso' => $archivoReverso,
                    'urlFrente' => '/estadocuenta/servirArchivoLocal?archivo=' . urlencode($archivoFrente),
                    'urlReverso' => '/estadocuenta/servirArchivoLocal?archivo=' . urlencode($archivoReverso),
                    'extension' => 'jpg',
                    'esImagen' => true
                ];
            }

            // Para otros documentos, búsqueda normal
            $patron = $directorioBase . '/' . $idSeguro . '_' . $tipoSeguro . '_*.{pdf,jpg,jpeg,png}';
            $archivos = glob($patron, GLOB_BRACE);
            
            if (!$archivos) {
                error_log("1RA FORMA - No encontrado localmente");
                return null;
            }

            usort($archivos, function ($a, $b) {
                return filemtime($b) <=> filemtime($a);
            });

            $rutaCompleta = $archivos[0];
            $archivo = basename($rutaCompleta);
            $extension = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
            $esImagen = in_array($extension, ['jpg', 'jpeg', 'png'], true);
            
            error_log("1RA FORMA - Encontrado localmente: $archivo");
            
            return [
                'archivo' => $archivo,
                'extension' => $extension,
                'esImagen' => $esImagen,
                'url' => '/estadocuenta/servirArchivoLocal?archivo=' . urlencode($archivo)
            ];
        };

        // ---------------- FACTURA ----------------
        if ($tipo === 'FACTURA') {
            error_log("=== PROCESANDO FACTURA ===");
            
            $local = $buscarLocal($id, $tipo);
            if ($local) {
                error_log("FACTURA $id - RESULTADO: 1RA FORMA (Local)");
                echo json_encode([
                    'success' => true,
                    'tipo' => $tipo,
                    'url' => $local['url'],
                    'archivo' => $local['archivo'],
                    'esImagen' => $local['esImagen'],
                    'extension' => $local['extension']
                ]);
                exit;
            }
            
            error_log("FACTURA $id - 1RA FORMA falló, probando 2DA FORMA...");
            $fileName = "FACTURA/{$id}_factura.pdf";
            if (!$existeEnS3($fileName)) {
                error_log("FACTURA $id - 2DA FORMA falló, probando 3RA FORMA...");
                
                $nombreBD = $consultarBDTerceraForma($id, 'FACTURA');
                
                if ($nombreBD) {
                    $fileNameBD = "FACTURA/" . $nombreBD;
                    if ($existeEnS3($fileNameBD)) {
                        error_log("FACTURA $id - RESULTADO: 3RA FORMA (BD + S3)");
                        $fileUrl = "/estadocuenta/verDocumento?fileName=" . urlencode($fileNameBD);
                        echo json_encode([
                            'success' => true,
                            'tipo' => $tipo,
                            'url' => $fileUrl,
                            'archivo' => $nombreBD,
                            'carpeta' => 'FACTURA',
                            'esImagen' => false,
                            'extension' => 'pdf'
                        ]);
                        exit;
                    } else {
                        error_log("FACTURA $id - 3RA FORMA: Encontrado en BD pero no en S3: $fileNameBD");
                    }
                }
                
                error_log("FACTURA $id - RESULTADO: 3 FORMAS FALLIDAS");
                echo json_encode([
                    'success' => false,
                    'mensaje' => "Este ID de crédito no tiene {$nombreDoc} registrado."
                ]);
                exit;
            }
            
            error_log("FACTURA $id - RESULTADO: 2DA FORMA (S3 estándar)");
            $fileUrl = "/estadocuenta/verDocumento?fileName=" . urlencode($fileName);
            echo json_encode([
                'success' => true,
                'tipo' => $tipo,
                'url' => $fileUrl,
                'archivo' => "{$id}_factura.pdf",
                'carpeta' => 'FACTURA',
                'esImagen' => false,
                'extension' => 'pdf'
            ]);
            exit;
        }

        // ---------------- CONTRATO ----------------
        elseif ($tipo === 'CONTRATO') {
            error_log("=== PROCESANDO CONTRATO/VALIDACIONES ===");
            
            $local = $buscarLocal($id, $tipo);
            if ($local) {
                error_log("CONTRATO $id - RESULTADO: 1RA FORMA (Local)");
                echo json_encode([
                    'success' => true,
                    'tipo' => $tipo,
                    'url' => $local['url'],
                    'archivo' => $local['archivo'],
                    'esImagen' => $local['esImagen'],
                    'extension' => $local['extension']
                ]);
                exit;
            }
            
            error_log("CONTRATO $id - 1RA FORMA falló, probando 2DA FORMA...");
            $fileName = "VALIDACIONES/{$id}_validaciones.pdf";
            if (!$existeEnS3($fileName)) {
                error_log("CONTRATO $id - 2DA FORMA falló, probando 3RA FORMA...");
                
                $nombreBD = $consultarBDTerceraForma($id, 'CONTRATO');
                
                if ($nombreBD) {
                    $fileNameBD = "VALIDACIONES/" . $nombreBD;
                    if ($existeEnS3($fileNameBD)) {
                        error_log("CONTRATO $id - RESULTADO: 3RA FORMA (BD + S3)");
                        $fileUrl = "/estadocuenta/verDocumento?fileName=" . urlencode($fileNameBD);
                        echo json_encode([
                            'success' => true,
                            'tipo' => $tipo,
                            'url' => $fileUrl,
                            'archivo' => $nombreBD,
                            'carpeta' => 'VALIDACIONES',
                            'esImagen' => false,
                            'extension' => 'pdf'
                        ]);
                        exit;
                    } else {
                        error_log("CONTRATO $id - 3RA FORMA: Encontrado en BD pero no en S3: $fileNameBD");
                    }
                }
                
                error_log("CONTRATO $id - RESULTADO: 3 FORMAS FALLIDAS");
                echo json_encode([
                    'success' => false,
                    'mensaje' => "Este ID de crédito no tiene {$nombreDoc} registrado."
                ]);
                exit;
            }
            
            error_log("CONTRATO $id - RESULTADO: 2DA FORMA (S3 estándar)");
            $fileUrl = "/estadocuenta/verDocumento?fileName=" . urlencode($fileName);
            echo json_encode([
                'success' => true,
                'tipo' => $tipo,
                'url' => $fileUrl,
                'archivo' => "{$id}_validaciones.pdf",
                'carpeta' => 'VALIDACIONES',
                'esImagen' => false,
                'extension' => 'pdf'
            ]);
            exit;
        }

        // ---------------- INE ----------------
        elseif ($tipo === 'INE') {
            error_log("=== PROCESANDO INE ===");
            
            // Buscar primero localmente
            $local = $buscarLocal($id, $tipo);
            if ($local && isset($local['esINE']) && $local['esINE'] === true) {
                error_log("INE $id - RESULTADO: 1RA FORMA (Local)");
                echo json_encode([
                    'success' => true,
                    'tipo' => 'INE',
                    'frente' => $local['urlFrente'],
                    'reverso' => $local['urlReverso'],
                    'archivoFrente' => $local['archivoFrente'],
                    'archivoReverso' => $local['archivoReverso']
                ]);
                exit;
            }

            // Si no existe local, buscar en API externo y S3 (2da forma)
            error_log("INE $id - 1RA FORMA falló, probando 2DA FORMA (API + S3)...");
            $endpoint = "https://servicios.s2movil.net/s2__SPARTA_SECRET_REDACTED__/estadocuenta";
            $token    = "__SPARTA_TOKEN_REDACTED__";

            $payload = json_encode([
                "idCredito"  => (int)$id,
                "fechaCorte" => date("Y-m-d")
            ]);

            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_HTTPHEADER     => [
                    "Content-Type: application/json",
                    "Token: {$token}"
                ],
                CURLOPT_POSTFIELDS     => $payload,
                CURLOPT_TIMEOUT        => 10
            ]);

            $response = curl_exec($ch);
            curl_close($ch);

            $data = json_decode($response, true);

            if (
                !$data ||
                !isset($data['estadoCuenta']['datosCliente']['idCliente'])
            ) {
                error_log("INE $id - 2DA FORMA falló (API), INE no tiene 3RA FORMA");
                echo json_encode([
                    'success' => false,
                    'mensaje' => 'Este ID de crédito no tiene INE registrado.'
                ]);
                exit;
            }

            $idCliente = $data['estadoCuenta']['datosCliente']['idCliente'];

            // URLs directas para frente y reverso del INE (2da forma)
            $urlFrente = "http://98.90.194.116/audit-app-0.0.1-SNAPSHOT_1/s3/downloadS3File?fileName=INE/{$idCliente}_frente.jpeg";
            $urlReverso = "http://98.90.194.116/audit-app-0.0.1-SNAPSHOT_1/s3/downloadS3File?fileName=INE/{$idCliente}_reverso.jpeg";

            // Comprobar que las imágenes INE existan en S3
            $chF = curl_init($urlFrente);
            curl_setopt_array($chF, [CURLOPT_RETURNTRANSFER => true, CURLOPT_NOBODY => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_TIMEOUT => 8]);
            curl_exec($chF);
            $codeF = (int) curl_getinfo($chF, CURLINFO_HTTP_CODE);
            curl_close($chF);
            $chR = curl_init($urlReverso);
            curl_setopt_array($chR, [CURLOPT_RETURNTRANSFER => true, CURLOPT_NOBODY => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_TIMEOUT => 8]);
            curl_exec($chR);
            $codeR = (int) curl_getinfo($chR, CURLINFO_HTTP_CODE);
            curl_close($chR);
            
            if ($codeF !== 200 || $codeR !== 200) {
                error_log("INE $id - 2DA FORMA falló (imágenes no encontradas)");
                echo json_encode([
                    'success' => false,
                    'mensaje' => 'Este ID de crédito no tiene INE registrado.'
                ]);
                exit;
            }

            error_log("INE $id - RESULTADO: 2DA FORMA (API + S3)");
            echo json_encode([
                'success' => true,
                'tipo' => 'INE',
                'frente' => $urlFrente,
                'reverso' => $urlReverso
            ]);
            exit;
        }

        // ---------------- FAD_DOC ----------------
        elseif ($tipo === 'FAD_DOC') {
            error_log("=== PROCESANDO FAD_DOC ===");
            
            $local = $buscarLocal($id, $tipo);
            if ($local) {
                error_log("FAD_DOC $id - RESULTADO: 1RA FORMA (Local)");
                echo json_encode([
                    'success' => true,
                    'tipo' => $tipo,
                    'url' => $local['url'],
                    'archivo' => $local['archivo'],
                    'esImagen' => $local['esImagen'],
                    'extension' => $local['extension']
                ]);
                exit;
            }
            
            // Primero intentar DAO (2da forma actual)
            error_log("FAD_DOC $id - 1RA FORMA falló, probando 2DA FORMA (DAO)...");
            try {
                $res = EstadoCuentaDAO::obtenerDocumentoOferta($id, $tipo);
            } catch (\Throwable $e) {
                error_log("FAD_DOC $id - Error en DAO: " . $e->getMessage());
                $res = ['success' => false];
            }

            if ($res['success'] && isset($res['datos']['nombre_archivo']) && !empty($res['datos']['nombre_archivo'])) {
                error_log("FAD_DOC $id - RESULTADO: 2DA FORMA (DAO)");
                $archivo = basename($res['datos']['nombre_archivo']);
                $archivo = str_replace(['doc_cliente/', 'doc_cliente\\'], '', $archivo);
                $archivo = basename($archivo);
                
                $carpeta = 'FAD';
                $extension = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
                $esImagen = in_array($extension, ['jpg', 'jpeg', 'png', 'gif']);
                
                $fileName = "{$carpeta}/{$archivo}";
                $fileUrl = "/estadocuenta/verDocumento?fileName=" . urlencode($fileName);
                
                echo json_encode([
                    'success' => true,
                    'tipo' => $tipo,
                    'url' => $fileUrl,
                    'archivo' => $archivo,
                    'carpeta' => $carpeta,
                    'esImagen' => $esImagen,
                    'extension' => $extension
                ]);
                exit;
            }
            
            // Si DAO falla, intentar 3RA FORMA
            error_log("FAD_DOC $id - 2DA FORMA (DAO) falló, probando 3RA FORMA...");
            $nombreBD = $consultarBDTerceraForma($id, 'FAD_DOC');
            
            if ($nombreBD) {
                $fileNameBD = "FAD/" . $nombreBD;
                if ($existeEnS3($fileNameBD)) {
                    error_log("FAD_DOC $id - RESULTADO: 3RA FORMA (BD + S3)");
                    $extension = strtolower(pathinfo($nombreBD, PATHINFO_EXTENSION));
                    $esImagen = in_array($extension, ['jpg', 'jpeg', 'png', 'gif']);
                    
                    $fileUrl = "/estadocuenta/verDocumento?fileName=" . urlencode($fileNameBD);
                    echo json_encode([
                        'success' => true,
                        'tipo' => $tipo,
                        'url' => $fileUrl,
                        'archivo' => $nombreBD,
                        'carpeta' => 'FAD',
                        'esImagen' => $esImagen,
                        'extension' => $extension
                    ]);
                    exit;
                } else {
                    error_log("FAD_DOC $id - 3RA FORMA: Encontrado en BD pero no en S3: $fileNameBD");
                }
            }
            
            error_log("FAD_DOC $id - RESULTADO: 3 FORMAS FALLIDAS");
            echo json_encode([
                'success' => false,
                'mensaje' => "Este ID de crédito no tiene {$nombreDoc} registrado en ninguna ubicación."
            ]);
            exit;
        }

        // ---------------- EVIDENCIA ----------------
        elseif ($tipo === 'EVIDENCIA') {
            error_log("=== PROCESANDO EVIDENCIA ===");
            
            $local = $buscarLocal($id, $tipo);
            if ($local) {
                error_log("EVIDENCIA $id - RESULTADO: 1RA FORMA (Local)");
                echo json_encode([
                    'success' => true,
                    'tipo' => $tipo,
                    'url' => $local['url'],
                    'archivo' => $local['archivo'],
                    'esImagen' => $local['esImagen'],
                    'extension' => $local['extension']
                ]);
                exit;
            }
            
            // Primero intentar DAO (2da forma actual)
            error_log("EVIDENCIA $id - 1RA FORMA falló, probando 2DA FORMA (DAO)...");
            try {
                $res = EstadoCuentaDAO::obtenerDocumentoOferta($id, $tipo);
            } catch (\Throwable $e) {
                error_log("EVIDENCIA $id - Error en DAO: " . $e->getMessage());
                $res = ['success' => false];
            }

            if ($res['success'] && isset($res['datos']['nombre_archivo']) && !empty($res['datos']['nombre_archivo'])) {
                error_log("EVIDENCIA $id - RESULTADO: 2DA FORMA (DAO)");
                $archivo = basename($res['datos']['nombre_archivo']);
                $archivo = str_replace(['doc_cliente/', 'doc_cliente\\'], '', $archivo);
                $archivo = basename($archivo);
                
                $carpeta = 'EVIDENCIA';
                $extension = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
                $esImagen = in_array($extension, ['jpg', 'jpeg', 'png', 'gif']);
                
                $fileName = "{$carpeta}/{$archivo}";
                $fileUrl = "/estadocuenta/verDocumento?fileName=" . urlencode($fileName);
                
                echo json_encode([
                    'success' => true,
                    'tipo' => $tipo,
                    'url' => $fileUrl,
                    'archivo' => $archivo,
                    'carpeta' => $carpeta,
                    'esImagen' => $esImagen,
                    'extension' => $extension
                ]);
                exit;
            }
            
            // Si DAO falla, intentar 3RA FORMA
            error_log("EVIDENCIA $id - 2DA FORMA (DAO) falló, probando 3RA FORMA...");
            $nombreBD = $consultarBDTerceraForma($id, 'EVIDENCIA');
            
            if ($nombreBD) {
                $fileNameBD = "EVIDENCIA/" . $nombreBD;
                if ($existeEnS3($fileNameBD)) {
                    error_log("EVIDENCIA $id - RESULTADO: 3RA FORMA (BD + S3)");
                    $extension = strtolower(pathinfo($nombreBD, PATHINFO_EXTENSION));
                    $esImagen = in_array($extension, ['jpg', 'jpeg', 'png', 'gif']);
                    
                    $fileUrl = "/estadocuenta/verDocumento?fileName=" . urlencode($fileNameBD);
                    echo json_encode([
                        'success' => true,
                        'tipo' => $tipo,
                        'url' => $fileUrl,
                        'archivo' => $nombreBD,
                        'carpeta' => 'EVIDENCIA',
                        'esImagen' => $esImagen,
                        'extension' => $extension
                    ]);
                    exit;
                } else {
                    error_log("EVIDENCIA $id - 3RA FORMA: Encontrado en BD pero no en S3: $fileNameBD");
                }
            }
            
            error_log("EVIDENCIA $id - RESULTADO: 3 FORMAS FALLIDAS");
            echo json_encode([
                'success' => false,
                'mensaje' => "Este ID de crédito no tiene {$nombreDoc} registrado en ninguna ubicación."
            ]);
            exit;
        }

        // ---------------- Tipo no válido ----------------
        else {
            error_log("TIPO NO VÁLIDO: $tipo");
            echo json_encode([
                'success' => false,
                'mensaje' => 'Tipo de documento no válido. Tipos permitidos: FACTURA, CONTRATO, INE, FAD_DOC, EVIDENCIA'
            ]);
            exit;
        }

    } catch (\Throwable $e) {
        error_log("ERROR CRÍTICO en descargar(): " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'mensaje' => 'Error interno',
            'debug'   => $e->getMessage()
        ]);
        exit;
    }
}


    public function AddNote()
    {
        $input = json_decode(file_get_contents("php://input"), true);

        $data = [
            'id_credito' => (int)($input['id_credito'] ?? 0),
            'nota'       => trim($input['nota'] ?? ''),
            'usuario'    => $_SESSION['usuario'] ?? 'Sistema',
            'usuario_id'    => $_SESSION['usuario_id'] ?? '1'
        ];

        $resultado = EstadoCuentaDAO::insertNotas($data);

        // 👇 NORMALIZAMOS RESPUESTA PARA JS
        echo json_encode([
            'success' => true,
            'mensaje' => 'Nota agregada correctamente.',
            'data' => [
                'usuario' => $_SESSION['usuario'] ?? 'Operador'
            ]
        ]);
        exit;
    }

    public function getNotasCredito()
    {
        $input = json_decode(file_get_contents("php://input"), true);

        $idCredito = $input['idCredito'] ?? null;

        if (empty($idCredito)) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'Id de credito requerido'
            ]);
            return;
        }

        $resultado = EstadoCuentaDAO::getNotasCredito($idCredito);

        self::respuestaJSON($resultado);
    }


    public function getGastosCobranza()
    {
        $input = json_decode(file_get_contents("php://input"), true);
        $idCredito = $input['idCredito'] ?? null;

        if (empty($idCredito)) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'Id de crédito requerido'
            ]);
            return;
        }

        $resultado = EstadoCuentaDAO::getGastosCobranza($idCredito);    
        self::respuestaJSON($resultado);
    }


    public function getTiposContacto()
    {
        $resultado = EstadoCuentaDAO::getTiposContacto();
        self::respuestaJSON($resultado);
    }

    public function getResultadosContacto()
    {
        $input = json_decode(file_get_contents("php://input"), true);
        $tipoContactoId = $input['tipo_contacto_id'] ?? null;

        if (empty($tipoContactoId)) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'tipo_contacto_id requerido'
            ]);
            return;
        }

        $resultado = EstadoCuentaDAO::getResultadosContacto($tipoContactoId);
        self::respuestaJSON($resultado);
    }

    public function getDictamenes()
    {
        $input = json_decode(file_get_contents("php://input"), true);
        $resultadoContactoId = $input['resultado_contacto_id'] ?? null;

        if (empty($resultadoContactoId)) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'resultado_contacto_id requerido'
            ]);
            return;
        }

        $resultado = EstadoCuentaDAO::getDictamenes($resultadoContactoId);
        self::respuestaJSON($resultado);
    }

    public function getMotivosNoPago()
    {
        $resultado = EstadoCuentaDAO::getMotivosNoPago();
        self::respuestaJSON($resultado);
    }

    public function getPlataformas()
    {
        $resultado = EstadoCuentaDAO::getPlataformas();
        self::respuestaJSON($resultado);
    }

    public function getTiposMotivoNoPago()
    {
        $resultado = EstadoCuentaDAO::getTiposMotivoNoPago();
        self::respuestaJSON($resultado);
    }

    public function getMotivosNoPagoPorTipo()
    {
        $input = json_decode(file_get_contents("php://input"), true);
        $tipoId = $input['tipo_motivo_id'] ?? null;

        if (empty($tipoId)) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'tipo_motivo_id requerido'
            ]);
            return;
        }

        $resultado = EstadoCuentaDAO::getMotivosNoPagoPorTipo($tipoId);
        self::respuestaJSON($resultado);
    }

    public function guardarDictamen()
    {
        $input = json_decode(file_get_contents("php://input"), true);

        $data = [
            'id_credito'                => (int)($input['id_credito'] ?? 0),
            'tipo_contacto_id'           => (int)($input['tipo_contacto_id'] ?? 0),
            'resultado_contacto_id'      => (int)($input['resultado_contacto_id'] ?? 0),
            'dictamen_id'                => (int)($input['dictamen_id'] ?? 0),
            'tipo_motivo_no_pago_id'     => !empty($input['tipo_motivo_no_pago_id'])
                ? (int)$input['tipo_motivo_no_pago_id']
                : null,
            'motivo_no_pago_id'          => !empty($input['motivo_no_pago_id'])
                ? (int)$input['motivo_no_pago_id']
                : null,
            'plataforma_id'              => !empty($input['plataforma_id'])
                ? (int)$input['plataforma_id']
                : null,
            'fuente_ingresos'            => trim($input['fuente_ingresos'] ?? ''),
            'comentarios'                => trim($input['comentarios'] ?? ''),
            'usuario'                    => $_SESSION['usuario'] ?? 'Sistema',
            'usuario_id'                 => $_SESSION['usuario_id'] ?? 1
        ];

        // 🔴 Validación mínima backend
        if (
            !$data['id_credito'] ||
            !$data['tipo_contacto_id'] ||
            !$data['resultado_contacto_id'] ||
            !$data['dictamen_id'] ||
            !$data['comentarios']
        ) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Datos incompletos para guardar el dictamen'
            ]);
            exit;
        }

        $resultado = EstadoCuentaDAO::insertDictamenLlamada($data);

        echo json_encode([
            'success' => $resultado['success'],
            'mensaje' => $resultado['mensaje'],
            'data'    => $resultado['data'] ?? null
        ]);
        exit;
    }

    

    public function buscarReporteDictamen()
{

    
    // DEBUG: Log de entrada
    
    
    header('Content-Type: application/json');
    
    // Verificar sesión EXPLÍCITAMENTE
    session_start(); // Asegurar que la sesión esté iniciada
    if (!isset($_SESSION['usuario_id'])) {
        error_log('ERROR: No hay usuario_id en sesión - Redirigiría a login');
        // NO redirijas aquí, devuelve JSON error
        echo json_encode([
            'success' => false, 
            'mensaje' => 'Sesión expirada',
            'code' => 'SESSION_EXPIRED'
        ]);
        return;
    }
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        error_log('ERROR: Método no permitido: ' . $_SERVER['REQUEST_METHOD']);
        echo json_encode(['success' => false, 'mensaje' => 'Método no permitido']);
        return;
    }

    header('Content-Type: application/json');
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'mensaje' => 'Método no permitido']);
        return;
    }
    
    $fechaInicio = $_POST['fechaInicio'] ?? '';
    $fechaFin = $_POST['fechaFin'] ?? '';
    
    // Validar fechas
    if (empty($fechaInicio) || empty($fechaFin)) {
        echo json_encode(['success' => false, 'mensaje' => 'Fechas requeridas']);
        return;
    }
    
    // Validar formato de fechas
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaInicio) || 
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaFin)) {
        echo json_encode(['success' => false, 'mensaje' => 'Formato de fecha inválido']);
        return;
    }
    
    // Usar tu modelo existente
    $resultado = EstadoCuentaDAO::obtenerReportesDictamenPorFecha($fechaInicio, $fechaFin);
    
    // Retornar como JSON
    if ($resultado['success']) {
        echo json_encode([
            'success' => true,
            'data' => $resultado['datos'],
            'total' => count($resultado['datos'])
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'mensaje' => $resultado['mensaje'],
            'error' => $resultado['error'] ?? null
        ]);
    }

    
}

    public function registrarDocumentoCliente()
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Metodo no permitido'
            ]);
            return;
        }

        $idCredito = $_POST['idCredito'] ?? null;
        $tipoDocumento = strtoupper(trim($_POST['tipoDocumento'] ?? ''));

        if (empty($idCredito) || empty($tipoDocumento)) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Parametros incompletos'
            ]);
            return;
        }

        if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'No se recibio ningun archivo o hubo un error en la carga'
            ]);
            return;
        }

        $archivo = $_FILES['archivo'];
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $extensionesPermitidas = ['pdf', 'jpg', 'jpeg', 'png'];

        if (!in_array($extension, $extensionesPermitidas, true)) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Extension de archivo no permitida. Solo PDF, JPG o PNG'
            ]);
            return;
        }

        if ($archivo['size'] > 10 * 1024 * 1024) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'El archivo excede el tamano maximo permitido de 10 MB'
            ]);
            return;
        }

        $directorioBase = __DIR__ . '/../../uploads/documentos/doc_cliente';
        if (!file_exists($directorioBase)) {
            mkdir($directorioBase, 0777, true);
        }

        $idSeguro = preg_replace('/[^0-9]/', '', (string)$idCredito);
        $tipoSeguro = preg_replace('/[^A-Z0-9_-]/', '_', $tipoDocumento);

        if ($idSeguro === '') {
            echo json_encode([
                'success' => false,
                'mensaje' => 'ID de credito invalido'
            ]);
            return;
        }

        $timestamp = date('Ymd_His');
        $nombreArchivo = $idSeguro . '_' . $tipoSeguro . '_' . $timestamp . '.' . $extension;
        $rutaCompleta = $directorioBase . '/' . $nombreArchivo;
        $rutaRelativa = 'uploads/documentos/doc_cliente/' . $nombreArchivo;

        if (!move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Error al guardar el archivo en el servidor'
            ]);
            return;
        }

        echo json_encode([
            'success' => true,
            'mensaje' => 'Documento guardado correctamente',
            'archivo' => $nombreArchivo,
            'ruta' => $rutaRelativa
        ]);
    }

    /**
     * Registra INE (frente y reverso) localmente
     */
    public function registrarINE()
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Metodo no permitido'
            ]);
            return;
        }

        $idCredito = $_POST['idCredito'] ?? null;
        $tipoDocumento = strtoupper(trim($_POST['tipoDocumento'] ?? ''));

        if (empty($idCredito) || $tipoDocumento !== 'INE') {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Parametros incompletos o tipo de documento incorrecto'
            ]);
            return;
        }

        // Validar que se recibieron ambos archivos
        if (!isset($_FILES['ineFrente']) || $_FILES['ineFrente']['error'] !== UPLOAD_ERR_OK ||
            !isset($_FILES['ineReverso']) || $_FILES['ineReverso']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Debes subir ambos archivos: frente y reverso del INE'
            ]);
            return;
        }

        $frente = $_FILES['ineFrente'];
        $reverso = $_FILES['ineReverso'];

        $extensionesPermitidas = ['jpg', 'jpeg', 'png'];
        $extFrente = strtolower(pathinfo($frente['name'], PATHINFO_EXTENSION));
        $extReverso = strtolower(pathinfo($reverso['name'], PATHINFO_EXTENSION));

        if (!in_array($extFrente, $extensionesPermitidas, true) || !in_array($extReverso, $extensionesPermitidas, true)) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Extension de archivo no permitida. Solo JPG o PNG para INE'
            ]);
            return;
        }

        if ($frente['size'] > 10 * 1024 * 1024 || $reverso['size'] > 10 * 1024 * 1024) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Uno de los archivos excede el tamano maximo de 10 MB'
            ]);
            return;
        }

        $directorioBase = __DIR__ . '/../../uploads/documentos/doc_cliente';
        if (!file_exists($directorioBase)) {
            mkdir($directorioBase, 0777, true);
        }

        $idSeguro = preg_replace('/[^0-9]/', '', (string)$idCredito);
        if ($idSeguro === '') {
            echo json_encode([
                'success' => false,
                'mensaje' => 'ID de credito invalido'
            ]);
            return;
        }

        $timestamp = date('Ymd_His');
        $nombreFrente = $idSeguro . '_INE_frente_' . $timestamp . '.' . $extFrente;
        $nombreReverso = $idSeguro . '_INE_reverso_' . $timestamp . '.' . $extReverso;
        
        $rutaFrente = $directorioBase . '/' . $nombreFrente;
        $rutaReverso = $directorioBase . '/' . $nombreReverso;

        // Guardar ambos archivos
        if (!move_uploaded_file($frente['tmp_name'], $rutaFrente)) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Error al guardar el archivo frente del INE'
            ]);
            return;
        }

        if (!move_uploaded_file($reverso['tmp_name'], $rutaReverso)) {
            // Si el reverso falla, eliminar el frente
            @unlink($rutaFrente);
            echo json_encode([
                'success' => false,
                'mensaje' => 'Error al guardar el archivo reverso del INE'
            ]);
            return;
        }

        echo json_encode([
            'success' => true,
            'mensaje' => 'INE guardado correctamente (frente y reverso)',
            'archivoFrente' => $nombreFrente,
            'archivoReverso' => $nombreReverso
        ]);
    }

    /**
     * Sirve archivos locales desde uploads/documentos/doc_cliente/
     * con los headers HTTP correctos
     */
    public function servirArchivoLocal()
    {
        if (!isset($_GET['archivo'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Archivo no especificado']);
            exit;
        }

        $archivo = basename($_GET['archivo']); // Sanitizar para evitar path traversal
        $rutaCompleta = __DIR__ . '/../../uploads/documentos/doc_cliente/' . $archivo;

        if (!file_exists($rutaCompleta) || !is_file($rutaCompleta)) {
            http_response_code(404);
            echo json_encode(['error' => 'Archivo no encontrado']);
            exit;
        }

        // Determinar Content-Type según extensión
        $extension = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
        ];
        $contentType = $mimeTypes[$extension] ?? 'application/octet-stream';

        // Enviar headers correctos
        header('Content-Type: ' . $contentType);
        header('Content-Length: ' . filesize($rutaCompleta));
        header('Content-Disposition: inline; filename="' . $archivo . '"');
        header('Cache-Control: public, max-age=3600');
        
        // Leer y enviar el archivo
        readfile($rutaCompleta);
        exit;
    }

    public function verDocumento()
    {
        $fileName = $_GET['fileName'] ?? '';
        
        if (empty($fileName)) {
            http_response_code(404);
            echo "Archivo no especificado";
            exit;
        }

        // Decodificar el fileName si viene codificado
        $fileName = urldecode($fileName);
        
        $s3Url = "http://98.90.194.116/audit-app-0.0.1-SNAPSHOT_1/s3/downloadS3File?fileName=" . urlencode($fileName);

        // Determinar Content-Type basado en extensión
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $contentType = 'application/octet-stream';
        
        switch ($ext) {
            case 'pdf': $contentType = 'application/pdf'; break;
            case 'jpg': 
            case 'jpeg': $contentType = 'image/jpeg'; break;
            case 'png': $contentType = 'image/png'; break;
            case 'gif': $contentType = 'image/gif'; break;
        }

        // Obtener el archivo desde S3
        $ch = curl_init($s3Url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HEADER => false, // No incluir headers en la respuesta
        ]);
        
        $data = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($httpCode !== 200 || $data === false) {
            http_response_code(404);
            echo "No se pudo recuperar el documento (Code: $httpCode" . ($error ? ", Error: $error" : "") . ")";
            exit;
        }

        // Limpiar cualquier output previo
        if (ob_get_length()) {
            ob_clean();
        }

        // Servir el archivo forzando inline para visualización
        // IMPORTANTE: Estos headers sobrescriben cualquier header del servidor S3
        header("Content-Type: $contentType");
        header("Content-Disposition: inline; filename=\"" . basename($fileName) . "\"");
        header("Content-Length: " . strlen($data));
        header("Cache-Control: public, max-age=3600");
        header("Pragma: public");
        
        // Headers CORS si es necesario
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET");
        
        echo $data;
        exit;
    }




public function descargarReporteDictamen()
{
    // Limpiar buffer para evitar que cualquier eco previo rompa el Excel
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    try {
        // Obtener parámetros de fecha del GET
        $fechaInicio = $_GET['fechaInicio'] ?? null;
        $fechaFin = $_GET['fechaFin'] ?? null;
        
        // Validar fechas
        if (empty($fechaInicio) || empty($fechaFin)) {
            die('Fechas requeridas');
        }
        
        // Validar formato de fechas
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaInicio) || 
            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaFin)) {
            die('Formato de fecha inválido. Use YYYY-MM-DD');
        }
        
        // Obtener datos del reporte
        $reportes = EstadoCuentaDAO::obtenerReportesDictamenParaDescarga($fechaInicio, $fechaFin);
        
        if (empty($reportes)) {
            die('No hay datos para descargar en el rango de fechas especificado');
        }
        
        // Preparar datos para Excel
        $data = [];
        foreach ($reportes as $reporte) {
            // Formatear fecha si existe
            $fechaRegistro = $reporte['fecha_registro'] ?? '';
            if ($fechaRegistro) {
                try {
                    $fechaRegistro = date('d/m/Y', strtotime($fechaRegistro));
                } catch (\Exception $e) {
                    // Mantener el formato original si hay error
                }
            }
            
            $data[] = [
                'id_dictamen' => $reporte['id_dictamen'] ?? '',
                'fecha_registro' => $fechaRegistro,
                'hora_registro' => $reporte['hora_registro'] ?? '',
                'id_credito' => $reporte['id_credito'] ?? '',
                'nombre_cliente' => $reporte['nombre_cliente'] ?? '',
                'tipo_contacto' => $reporte['tipo_contacto'] ?? '',
                'resultado_contacto' => $reporte['resultado_contacto'] ?? '',
                'dictamen' => $reporte['dictamen'] ?? '',
                'motivo_no_pago' => $reporte['motivo_no_pago'] ?? '',
                'tipo_motivo_no_pago' => $reporte['tipo_motivo_no_pago'] ?? '',
                'plataforma' => $reporte['plataforma'] ?? '',
                'fuente_ingresos' => $reporte['fuente_ingresos'] ?? '',
                'comentarios' => $reporte['comentarios'] ?? '',
                'agente' => $reporte['agente'] ?? ''
            ];
        }
        
        // Definir columnas para Excel
        $columnas = [
            \PHPSpreadsheet::ColumnaExcel('id_dictamen', 'ID DICTAMEN'),
            \PHPSpreadsheet::ColumnaExcel('fecha_registro', 'FECHA REGISTRO'),
            \PHPSpreadsheet::ColumnaExcel('hora_registro', 'HORA REGISTRO'),
            \PHPSpreadsheet::ColumnaExcel('id_credito', 'ID CRÉDITO'),
            \PHPSpreadsheet::ColumnaExcel('nombre_cliente', 'CLIENTE'),
            \PHPSpreadsheet::ColumnaExcel('tipo_contacto', 'TIPO CONTACTO'),
            \PHPSpreadsheet::ColumnaExcel('resultado_contacto', 'RESULTADO CONTACTO'),
            \PHPSpreadsheet::ColumnaExcel('dictamen', 'DICTAMEN'),
            \PHPSpreadsheet::ColumnaExcel('motivo_no_pago', 'MOTIVO NO PAGO'),
            \PHPSpreadsheet::ColumnaExcel('tipo_motivo_no_pago', 'TIPO MOTIVO NO PAGO'),
            \PHPSpreadsheet::ColumnaExcel('plataforma', 'PLATAFORMA'),
            \PHPSpreadsheet::ColumnaExcel('fuente_ingresos', 'FUENTE DE INGRESOS'),
            \PHPSpreadsheet::ColumnaExcel('comentarios', 'COMENTARIOS'),
            \PHPSpreadsheet::ColumnaExcel('agente', 'AGENTE')
        ];
        
        // Generar nombre del archivo
        $nombreArchivo = 'Dictamen_Llamadas_' . $fechaInicio . '_a_' . $fechaFin . '_' . date('Y-m-d');
        
        // Descargar Excel directamente usando PHPSpreadsheet
        \PHPSpreadsheet::DescargaExcel(
            $nombreArchivo,
            "Dictamen de Llamadas",
            "Dictamen Llamadas",
            $columnas,
            $data
        );
        
        // Terminar ejecución para que no se agregue nada extra
        exit;
    } catch (\Exception $e) {
        error_log('Error en descargarReporteDictamen: ' . $e->getMessage());
        die('Error al generar el archivo Excel: ' . $e->getMessage());
    }
}


    public function getDictamenLlamadas()
    {
        // Obtener filtros de fecha
        $fechaInicio = $_POST['fechaInicio'] ?? null;
        $fechaFin = $_POST['fechaFin'] ?? null;

        if (empty($fechaInicio) || empty($fechaFin)) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'Fechas requeridas'
            ]);
            return;
        }

        // Llamar al DAO con filtros
        $resultado = EstadoCuentaDAO::buscarReporteDictamen(
            $_SESSION['usuario_id'],
            $fechaInicio,
            $fechaFin
        );

        // Verificar resultado
        if (!$resultado['success']) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => $resultado['mensaje']
            ]);
            return;
        }

        // Formatear datos si es necesario
        $datos = array_map(function($item) {
            return [
                'id' => $item['id'] ?? '',
                'fecha_registro' => $item['fecha_registro'] ?? '',
                'hora_registro' => $item['hora_registro'] ?? '',
                'id_credito' => $item['id_credito'] ?? '',
                'nombre_cliente' => $item['nombre_cliente'] ?? '',
                'tipo_contacto' => $item['tipo_contacto'] ?? '',
                'resultado_contacto' => $item['resultado_contacto'] ?? '',
                'dictamen' => $item['dictamen'] ?? '',
                'motivo_no_pago' => $item['motivo_no_pago'] ?? '',
                'tipo_motivo_no_pago' => $item['tipo_motivo_no_pago'] ?? '',
                'plataforma' => $item['plataforma'] ?? '',
                'fuente_ingresos' => $item['fuente_ingresos'] ?? '',
                'comentarios' => $item['comentarios'] ?? ''
            ];
        }, $resultado['datos']);

        self::respuestaJSON([
            'success' => true,
            'datos' => $datos,
            'cantidad' => count($datos)
        ]);
    }


    public function reporteDictamen()
    {
        $script = "";

        self::set("titulo", "Dictamen de Llamadas | " );
        self::set("script", $script);
        return self::render("dictamen_llamadas");
    }

    public function confirmarCondonacionGastos()
    {
        $input = json_decode(file_get_contents("php://input"), true);

        $idCredito  = $input['idCredito'] ?? null;
        $comentario = $input['comentario'] ?? null;
        $gastos     = $input['gastos'] ?? [];
        $total      = $input['total'] ?? 0;

        if (empty($idCredito) || empty($comentario) || empty($gastos)) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'Datos incompletos'
            ]);
            return;
        }

        // Datos de sesión (usuario_id y usuario del login)
        $dataTicket = [
            'id_credito' => $idCredito,
            'comentario' => $comentario,
            'total'      => $total,
            'usuario'    => $_SESSION['usuario'] ?? 'Sistema',        // user_name del login
            'usuario_id' => $_SESSION['usuario_id'] ?? 0              // id del login
        ];

        // 1️⃣ Insertar ticket
        $ticket = EstadoCuentaDAO::insertCondonacionCobranza($dataTicket);



        if (!$ticket['success']) {
            self::respuestaJSON($ticket);
            return;
        }

        $idCondonacion = $ticket['datos']['id_condonacion'];


        // 2️⃣ Insertar detalle + marcar gastos
        foreach ($gastos as $g) {

            $detalle = EstadoCuentaDAO::insertCondonacionCobranzaDetalle([
                'id_condonacion'     => $idCondonacion,
                'id_gastos_cobranza' => (int) $g['id_gastos_cobranza'],
                'monto'              => (float) $g['monto']
            ]);

            if (!$detalle['success']) {
                self::respuestaJSON($detalle);
                return;
            }

            // 👇 SOLO si el detalle se insertó correctamente
            $marca = EstadoCuentaDAO::marcarGastoCondonado(
                (int) $g['id_gastos_cobranza']
            );

            if (!$marca['success']) {
                self::respuestaJSON($marca);
                return;
            }
        }

        self::respuestaJSON([
            'success' => true,
            'mensaje' => 'Condonación registrada correctamente',
            'data' => [
                'id_condonacion' => $idCondonacion
            ]
        ]);
    }
}